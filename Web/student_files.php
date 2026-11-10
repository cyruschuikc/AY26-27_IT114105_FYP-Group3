<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($conn) || !$conn) {
    die('Database connection failed. Please check db.php.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

// =========================================================
// 0. Helper functions (must be defined before any calls)
// =========================================================
if (!function_exists('formatBytes')) {
    function formatBytes($bytes) {
        if ($bytes < 1024)               return $bytes . ' B';
        if ($bytes < 1024 * 1024)        return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 1) . ' MB';
        return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
    }
}

if (!function_exists('parseSizeToBytes')) {
    function parseSizeToBytes($size) {
        if (empty($size)) return 0;
        if (is_numeric($size)) return (int)$size;
        $size = strtoupper(trim($size));
        $num  = (float)$size;
        if (strpos($size, 'GB') !== false) return (int)($num * 1024 * 1024 * 1024);
        if (strpos($size, 'MB') !== false) return (int)($num * 1024 * 1024);
        if (strpos($size, 'KB') !== false) return (int)($num * 1024);
        if (strpos($size, 'B')  !== false) return (int)$num;
        return (int)$num;
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (empty($datetime)) return '—';
        $ts   = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60)         return 'Just now';
        if ($diff < 3600)       return floor($diff / 60) . 'm ago';
        if ($diff < 86400)      return floor($diff / 3600) . 'h ago';
        if ($diff < 86400 * 2)  return 'Yesterday';
        if ($diff < 86400 * 7)  return floor($diff / 86400) . 'd ago';
        return date('d M Y', $ts);
    }
}

if (!function_exists('statusClassFile')) {
    function statusClassFile($status) {
        switch ($status) {
            case 'Accepted': return 'status-accepted';
            case 'Rejected': return 'status-rejected';
            default:         return 'status-unviewed';
        }
    }
}

if (!function_exists('statusLabelFile')) {
    function statusLabelFile($status) {
        switch ($status) {
            case 'Accepted': return 'Accepted';
            case 'Rejected': return 'Rejected';
            default:         return 'Unviewed';
        }
    }
}

if (!function_exists('renderFileItem')) {
    function renderFileItem($f) {
        $ext = strtoupper($f['file_type'] ?: pathinfo($f['file_name'], PATHINFO_EXTENSION));
        $ext = $ext ?: 'FILE';

        $statusClass  = statusClassFile($f['status']);
        $statusLabel  = statusLabelFile($f['status']);

        // Prefer display_name if set
        $shownName    = !empty($f['display_name']) ? $f['display_name'] : $f['file_name'];
        $safeName     = htmlspecialchars($shownName);
        $safeFile     = htmlspecialchars($f['file_name']);
        $safeNotes    = !empty($f['notes']) ? htmlspecialchars($f['notes']) : '';
        $safeExt      = htmlspecialchars($ext);
        $safeUploader = htmlspecialchars($f['uploader_name'] ?: '—');
        $safeSize     = htmlspecialchars($f['file_size'] ?: '—');
        $ago          = timeAgo($f['uploaded_at']);
        $fileID       = (int)$f['id'];

        echo '<article class="file-item">';
        echo   '<div class="file-icon">' . $safeExt . '</div>';
        echo   '<div class="file-information">';
        echo     '<h3>' . $safeName . '</h3>';

        if ($shownName !== $f['file_name']) {
            echo '<p class="orig-name">' . $safeFile . '</p>';
        }

        echo     '<p>Uploaded by ' . $safeUploader . ' · ' . $safeSize . '</p>';

        if ($safeNotes !== '') {
            echo '<p class="file-notes">' . $safeNotes . '</p>';
        }

        echo   '</div>';
        echo   '<div class="file-detail"><h4>Uploaded</h4><p>' . $ago . '</p></div>';
        echo   '<div class="file-detail hide-md"><h4>Status</h4><span class="status-pill ' . $statusClass . '">' . $statusLabel . '</span></div>';
        echo   '<div class="file-actions">';
        echo     '<a href="open_file.php?id=' . $fileID . '" target="_blank" class="text-link">Open</a>';
        echo     '<a href="download_file.php?id=' . $fileID . '" class="text-link">Download</a>';
        echo   '</div>';
        echo '</article>';
    }
}

// =========================================================
// 1. Unread notifications + preview
// =========================================================
$userID = (int) $_SESSION['userID'];

$unreadNotifications = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `notifications` WHERE user_id = ? AND is_read = 0");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int)$rc['c'];
mysqli_stmt_close($stmt);

// 未讀通知預覽（最多 8 條）
$unreadList = array();
$stmt = mysqli_prepare($conn, "
    SELECT id, type, title, message, created_at
    FROM `notifications`
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 8
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($rowN = mysqli_fetch_assoc($rs)) {
    $unreadList[] = $rowN;
}
mysqli_stmt_close($stmt);

// =========================================================
// 2. student_id
// =========================================================
$studentID = 0;
$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: logout.php');
    exit;
}
$studentID = (int)$rc['id'];

// =========================================================
// 3. All active groups of this student (Voting / Executing)
// =========================================================
$myGroups = array();
$stmt = mysqli_prepare($conn, "
    SELECT
        g.id            AS group_id,
        g.name          AS group_name,
        g.status,
        g.max_members,
        g.project_id,
        g.course_id,
        g.leader_id,
        p.name          AS project_name,
        c.code          AS course_code
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    LEFT JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `courses`  c ON c.id = g.course_id
    WHERE gm.student_id = ?
      AND g.status IN ('Voting', 'Executing')
    ORDER BY g.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $myGroups[] = $row;
}
mysqli_stmt_close($stmt);

// =========================================================
// 4. Determine active group
// =========================================================
$activeGroupID = 0;
if (isset($_GET['group_id'])) {
    $activeGroupID = (int)$_GET['group_id'];
}

$myGroup = null;
if ($activeGroupID > 0) {
    foreach ($myGroups as $g) {
        if ((int)$g['group_id'] === $activeGroupID) {
            $myGroup = $g;
            break;
        }
    }
}
if (!$myGroup && count($myGroups) > 0) {
    $myGroup = $myGroups[0];
    $activeGroupID = (int)$myGroup['group_id'];
}

// =========================================================
// 5. Flash messages
// =========================================================
$flashMsg  = '';
$flashType = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'uploaded':
            $flashMsg  = 'File uploaded successfully.';
            $flashType = 'success';
            break;
        case 'nofile':
            $flashMsg  = 'Please select a file to upload.';
            $flashType = 'error';
            break;
        case 'badext':
            $flashMsg  = 'File type not allowed.';
            $flashType = 'error';
            break;
        case 'uploadfail':
            $flashMsg  = 'Upload failed. Please try again.';
            $flashType = 'error';
            break;
        case 'error':
            $flashMsg  = 'Something went wrong. Please try again.';
            $flashType = 'error';
            break;
    }
}

// =========================================================
// 6. Filters
// =========================================================
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$filterSearch = isset($_GET['q'])      ? trim($_GET['q'])      : '';

// =========================================================
// 7. Load files (with filters)
// =========================================================
$files = array();
$fileCount    = 0;
$taskCount    = 0;
$recentWeek   = 0;
$storageBytes = 0;

if ($myGroup) {

    $where  = array("f.group_id = ?");
    $params = array($activeGroupID);
    $types  = 'i';

    if ($filterStatus !== 'all' && $filterStatus !== '') {
        $where[]  = "f.status = ?";
        $params[] = $filterStatus;
        $types   .= 's';
    }

    if ($filterSearch !== '') {
        $where[]  = "(f.file_name LIKE ? OR f.display_name LIKE ? OR t.title LIKE ?)";
        $params[] = '%' . $filterSearch . '%';
        $params[] = '%' . $filterSearch . '%';
        $params[] = '%' . $filterSearch . '%';
        $types   .= 'sss';
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $sql = "
        SELECT
            f.id,
            f.file_name,
            f.display_name,
            f.notes,
            f.file_size,
            f.file_type,
            f.status,
            f.uploaded_at,
            f.task_id,
            t.title AS task_title,
            u.username AS uploader_name
        FROM `files` f
        LEFT JOIN `tasks`    t ON t.id = f.task_id
        LEFT JOIN `students` s ON s.id = f.uploader_id
        LEFT JOIN `users`    u ON u.id = s.user_id
        $whereSql
        ORDER BY f.uploaded_at DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $files[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 8. Summary statistics
// =========================================================
if ($myGroup) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `files` WHERE group_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $fileCount = (int)$rc['c'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT COUNT(DISTINCT task_id) AS c FROM `files` WHERE group_id = ? AND task_id IS NOT NULL");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $taskCount = (int)$rc['c'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `files` WHERE group_id = ? AND uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $recentWeek = (int)$rc['c'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT file_size FROM `files` WHERE group_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $storageBytes += parseSizeToBytes($row['file_size']);
    }
    mysqli_stmt_close($stmt);
}

$storageText = formatBytes($storageBytes);
$storagePct  = min(100, round($storageBytes / (1024 * 1024 * 1024) * 100));

// =========================================================
// 9. Group files by task
// =========================================================
$groupedByTask = array();
$standalone    = array();

foreach ($files as $f) {
    if (!empty($f['task_id'])) {
        $groupedByTask[(int)$f['task_id']][] = $f;
    } else {
        $standalone[] = $f;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Files | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        .navbar {
            width: 100%; padding: 15px 30px; background: black;
            display: flex; flex-direction: column; align-items: center; gap: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-top {
            position: relative; width: 100%; min-height: 65px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-area { display: flex; justify-content: center; align-items: center; gap: 12px; }
        .logo-area img { width: 65px; height: 65px; object-fit: contain; display: block; }
        .logo-area span { color: white; font-size: 30px; font-weight: bold; }

        /* Calendar logo beside notification bell */
        .calendar-link {
            position: absolute;
            right: 52px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 7px;
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s;
        }
        .calendar-link:hover { background: rgba(255, 255, 255, 0.15); }
        .calendar-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        /* =========================================================
           鈴鐺 + 未讀通知預覽
           ========================================================= */
        .bell-wrap {
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-50%);
            z-index: 100;
        }
        .bell-link {
            position: relative;
            display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%;
            transition: background 0.3s;
            text-decoration: none;
        }
        .bell-link:hover { background: rgba(255, 255, 255, 0.15); }
        .bell-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        .bell-badge {
            position: absolute; top: 2px; right: 2px;
            min-width: 18px; height: 18px; padding: 0 5px;
            background: #d93025; color: white; border-radius: 9px;
            font-size: 11px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid black;
        }

        .bell-preview {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 340px;
            max-height: 420px;
            background: white;
            color: #222;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
            overflow: hidden;
            z-index: 200;
            display: flex;
            flex-direction: column;
        }
        .bell-wrap:hover .bell-preview {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .bell-preview::before {
            content: "";
            position: absolute;
            top: -6px;
            right: 18px;
            width: 12px;
            height: 12px;
            background: white;
            border-left: 1px solid #e0e0e0;
            border-top: 1px solid #e0e0e0;
            transform: rotate(45deg);
        }
        .bell-preview-header {
            padding: 12px 14px;
            border-bottom: 1px solid #eeeeee;
            font-size: 12px;
            font-weight: 700;
            color: #333;
            background: #fafbfc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }
        .bell-preview-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            max-height: 320px;
        }
        .bell-preview-item {
            display: block;
            padding: 10px 14px;
            border-bottom: 1px solid #f2f3f5;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
        }
        .bell-preview-item:hover { background: #f7f8fa; }
        .bell-preview-item:last-child { border-bottom: none; }
        .bell-preview-title {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #222;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bell-preview-msg {
            display: block;
            font-size: 11.5px;
            color: #777;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        .bell-preview-time {
            display: block;
            font-size: 10.5px;
            color: #9aa0a6;
        }
        .bell-preview-footer {
            padding: 10px 14px;
            text-align: center;
            background: #fafbfc;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            font-weight: 700;
            color: #175196;
            text-decoration: none;
            flex-shrink: 0;
        }
        .bell-preview-footer:hover { background: #f0f6fd; }

        .function-menu {
            width: 100%; display: flex; justify-content: center; align-items: center;
            flex-wrap: wrap; gap: 22px;
        }
        .nav-link { color: white; font-size: 15px; text-decoration: none; padding: 6px 2px; transition: color 0.3s; }
        .nav-link:hover { color: #bbbbbb; }
        .nav-link.active { color: white; font-weight: bold; border-bottom: 2px solid white; }
        .logout-link { color: #ff8888; }
        .logout-link:hover { color: #ff4444; }

        .main-content { width: 90%; max-width: 1250px; margin: 40px auto; }

        .page-heading {
            padding: 30px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .alert { padding: 14px 18px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; border-left: 4px solid; }
        .alert-success { background: #eaf7ee; color: #176b2c; border-color: #2e9d4d; }
        .alert-error   { background: #fdeeee; color: #a21b1b; border-color: #d32f2f; }

        .empty-state {
            padding: 60px 40px; background: white;
            border-radius: 15px; border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .empty-state h2 { font-size: 24px; margin-bottom: 12px; }
        .empty-state p { color: #666; font-size: 15px; line-height: 1.6; max-width: 560px; margin: 0 auto 28px; }
        .empty-state .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 200px; height: 50px; padding: 0 24px;
            background: black; color: white; border: 2px solid black;
            border-radius: 9px; font-size: 16px; font-weight: bold;
            text-decoration: none; cursor: pointer; transition: 0.3s;
        }
        .empty-state .btn:hover { background: #333; }

        .group-switcher {
            padding: 20px 24px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            border-left: 6px solid #245889;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        }
        .group-switcher label { font-size: 15px; color: #333; font-weight: bold; }
        .group-switcher select {
            flex: 1; min-width: 260px; height: 45px; padding: 0 14px;
            background: white; color: #222;
            border: 2px solid #ccc; border-radius: 8px;
            font-size: 14px; cursor: pointer; outline: none;
        }
        .group-switcher select:focus { border-color: black; }

        .summary-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 20px; margin-bottom: 25px;
        }
        .summary-card {
            min-height: 145px; padding: 24px;
            background: white; border-left: 5px solid black;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .summary-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); }
        .summary-card h2 { margin-bottom: 15px; color: #666; font-size: 16px; }
        .summary-number { color: black; font-size: 34px; font-weight: bold; }
        .summary-description { margin-top: 7px; color: #777; font-size: 13px; }

        .upload-section {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            padding-bottom: 15px; margin-bottom: 20px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }
        .upload-area {
            min-height: 170px; padding: 25px;
            background: #fafafa; border: 2px dashed #999;
            border-radius: 12px;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            text-align: center;
        }
        .upload-icon {
            width: 55px; height: 55px; margin-bottom: 15px;
            background: black; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 27px; font-weight: bold;
        }
        .upload-area h3 { margin-bottom: 7px; font-size: 19px; }
        .upload-area p { margin-bottom: 16px; color: #777; font-size: 14px; }
        .upload-form { display: flex; flex-direction: column; align-items: center; gap: 12px; width: 100%; }
        .file-input { display: none; }
        .upload-row {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: center;
        }
        .upload-btn {
            min-width: 160px; height: 45px; padding: 0 18px;
            background: white; color: black; border: 2px solid black;
            border-radius: 8px; font-size: 15px; cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            transition: 0.3s; font-weight: bold;
        }
        .upload-btn:hover { background: black; color: white; }
        .file-preview {
            display: none; padding: 8px 12px; background: #eaf7ee;
            border: 2px solid #2e9d4d; border-radius: 8px;
            font-size: 13px; color: #176b2c; font-weight: 600;
            max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .file-preview.visible { display: block; }

        /* Extra fields shown after a file is selected */
        .upload-extra {
            display: none;
            width: 100%;
            max-width: 560px;
            margin-top: 14px;
            padding: 18px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: left;
        }
        .upload-extra.visible { display: block; }
        .upload-extra .form-group { margin-bottom: 14px; }
        .upload-extra .form-group:last-child { margin-bottom: 0; }
        .upload-extra label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
        .upload-extra input[type="text"],
        .upload-extra textarea {
            width: 100%;
            padding: 10px 12px;
            background: white;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            font-family: inherit;
        }
        .upload-extra input[type="text"]:focus,
        .upload-extra textarea:focus { border-color: black; }
        .upload-extra .help-text {
            display: block;
            margin-top: 4px;
            color: #999;
            font-size: 12px;
            font-style: italic;
        }

        .submit-upload {
            min-width: 160px; height: 45px; padding: 0 18px;
            background: black; color: white; border: 2px solid black;
            border-radius: 8px; font-size: 15px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .submit-upload:hover { background: #333; }
        .submit-upload:disabled { background: #ccc; border-color: #ccc; color: #888; cursor: not-allowed; }

        .file-section {
            padding: 28px; background: white;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .file-section-header {
            padding-bottom: 18px; margin-bottom: 22px;
            border-bottom: 2px solid #eee;
            display: flex; justify-content: space-between;
            align-items: center; gap: 20px; flex-wrap: wrap;
        }
        .file-section-header h2 { font-size: 24px; }
        .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        .search-input, .filter-select {
            height: 44px; padding: 0 13px;
            background: white; color: #222; border: 2px solid #ccc;
            border-radius: 8px; font-size: 14px; outline: none;
        }
        .search-input { width: 220px; }
        .filter-select { min-width: 150px; cursor: pointer; }
        .search-input:focus, .filter-select:focus { border-color: black; }
        .filter-btn {
            height: 44px; padding: 0 16px; background: black; color: white;
            border: 2px solid black; border-radius: 8px;
            font-size: 14px; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .filter-btn:hover { background: #333; }

        .file-group-heading {
            font-size: 14px; font-weight: bold; color: #555;
            margin: 20px 0 10px; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .file-list { display: flex; flex-direction: column; gap: 12px; }

        .file-item {
            padding: 18px;
            background: #fafafa; border: 1px solid #ddd;
            border-radius: 11px;
            display: grid; grid-template-columns: 55px 2fr 1fr 1fr auto;
            align-items: center; gap: 18px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .file-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .file-icon {
            width: 50px; height: 50px; background: black; color: white;
            border-radius: 9px;
            display: flex; justify-content: center; align-items: center;
            font-size: 12px; font-weight: bold;
        }
        .file-information h3 {
            margin-bottom: 6px; font-size: 16px; word-break: break-word;
        }
        .file-information p { color: #777; font-size: 13px; }
        .file-information .orig-name {
            color: #aaa;
            font-size: 12px;
            font-style: italic;
            margin-bottom: 4px;
        }
        .file-information .file-notes {
            margin-top: 8px;
            padding: 8px 10px;
            background: #f3f5f8;
            border-left: 3px solid #245889;
            border-radius: 6px;
            color: #444;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .file-detail h4 { margin-bottom: 6px; color: #777; font-size: 12px; }
        .file-detail p { color: #222; font-size: 13px; font-weight: bold; }

        .status-pill {
            display: inline-block; padding: 4px 10px;
            border-radius: 15px; font-size: 11px; font-weight: bold;
            white-space: nowrap;
        }
        .status-unviewed { background: #f7f8fa; color: #6b6f78; border: 1px solid #e6e8eb; }
        .status-accepted { background: #eaf7ee; color: #176b2c; border: 1px solid #8bc79a; }
        .status-rejected { background: #fdeeee; color: #a21b1b; border: 1px solid #e6a0a0; }

        .file-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .text-link {
            color: black; font-size: 14px; text-decoration: underline; transition: color 0.3s;
        }
        .text-link:hover { color: #555; }

        .storage-area {
            margin-top: 25px; padding: 20px;
            background: #f7f7f7; border-radius: 10px;
        }
        .storage-heading {
            margin-bottom: 11px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .storage-heading strong { font-size: 15px; }
        .storage-heading span { color: #666; font-size: 13px; }
        .storage-bar {
            width: 100%; height: 13px;
            background: #ddd; border-radius: 10px; overflow: hidden;
        }
        .storage-value { height: 100%; background: black; border-radius: 10px; }
        .storage-note { margin-top: 9px; color: #777; font-size: 13px; }

        .file-note {
            padding: 18px; margin-top: 22px;
            background: #f7f7f7; border-left: 5px solid black;
            border-radius: 8px;
            color: #666; font-size: 14px; line-height: 1.6;
        }

        .muted { color: #777; font-size: 14px; }

        @media (max-width: 1050px) {
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
            .file-item { grid-template-columns: 55px 2fr 1fr auto; }
            .file-item .file-detail.hide-md { display: none; }
        }
        @media (max-width: 700px) {
            .navbar { padding: 15px; }
            .logo-area img { width: 55px; height: 55px; }
            .logo-area span { font-size: 26px; }
            .calendar-link { right: 44px; width: 38px; height: 38px; }
            .calendar-link img { width: 22px; height: 22px; }
            .bell-link { width: 38px; height: 38px; }
            .bell-link img { width: 22px; height: 22px; }
            .bell-preview { width: calc(100vw - 40px); right: -8px; }
            .function-menu { flex-direction: column; gap: 12px; }
            .main-content { width: 94%; margin: 25px auto; }
            .page-heading h1 { font-size: 28px; }
            .summary-grid { grid-template-columns: 1fr; }
            .toolbar { align-items: stretch; flex-direction: column; }
            .search-input, .filter-select { width: 100%; }
            .file-item { grid-template-columns: 50px 1fr; }
            .file-detail, .file-actions { grid-column: 1 / -1; }
            .group-switcher { flex-direction: column; align-items: stretch; }
            .group-switcher select { min-width: 100%; }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="navbar-top">
        <div class="logo-area">
            <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
            <span>TeamBox</span>
        </div>

        <a href="student_calendar.php" class="calendar-link" title="Calendar" aria-label="Calendar">
            <img src="cal_logo.png" alt="Calendar">
        </a>
        <div class="bell-wrap">
            <a href="student_notifications.php" class="bell-link" title="Notifications">
                <img src="bell.png" alt="Notifications">
                <?php if ($unreadNotifications > 0): ?>
                    <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
                <?php endif; ?>
            </a>

            <?php if (count($unreadList) > 0): ?>
                <div class="bell-preview">
                    <div class="bell-preview-header">
                        Unread Notifications (<?php echo count($unreadList); ?>)
                    </div>
                    <div class="bell-preview-list">
                        <?php foreach ($unreadList as $n): ?>
                            <a href="student_notifications.php" class="bell-preview-item">
                                <span class="bell-preview-title"><?php echo htmlspecialchars($n['title']); ?></span>
                                <?php if (!empty($n['message'])): ?>
                                    <span class="bell-preview-msg"><?php echo htmlspecialchars($n['message']); ?></span>
                                <?php endif; ?>
                                <span class="bell-preview-time">
                                    <?php echo date('d M Y H:i', strtotime($n['created_at'])); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="student_notifications.php" class="bell-preview-footer">
                        View all notifications →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link active">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <section class="page-heading">
        <h1>Shared Files</h1>
        <p>
            Upload, download and manage your team project files.
            View file status, uploader and recent changes.
        </p>
    </section>

    <?php if (count($myGroups) === 0): ?>

        <div class="empty-state">
            <h2>You don't have an active team yet</h2>
            <p>
                Shared files are only available after your team has been submitted
                to your supervisor and voting has started.
                Create or manage your group first.
            </p>
            <a href="student_create_group.php" class="btn">Go to Create Group</a>
        </div>

    <?php else: ?>

    <?php if (count($myGroups) > 1): ?>
        <div class="group-switcher">
            <label for="groupSwitcher">Switch Project / Group:</label>
            <select id="groupSwitcher" onchange="switchGroup(this.value)">
                <?php foreach ($myGroups as $g): ?>
                    <option value="<?php echo (int)$g['group_id']; ?>"
                            <?php echo ((int)$g['group_id'] === $activeGroupID) ? 'selected' : ''; ?>>
                        <?php
                        echo htmlspecialchars(
                                ($g['course_code'] ? $g['course_code'] . ' - ' : '')
                                . $g['project_name'] . ' / ' . $g['group_name']
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <script>
            function switchGroup(id) {
                window.location.href = 'student_files.php?group_id=' + id;
            }
        </script>
    <?php endif; ?>

    <?php if ($flashMsg !== ''): ?>
        <div class="alert <?php echo $flashType === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($flashMsg); ?>
        </div>
    <?php endif; ?>

        <!-- Summary -->
        <section class="summary-grid">
            <div class="summary-card">
                <h2>Total Files</h2>
                <p class="summary-number"><?php echo $fileCount; ?></p>
                <p class="summary-description">Files in this team workspace</p>
            </div>
            <div class="summary-card">
                <h2>Linked Tasks</h2>
                <p class="summary-number"><?php echo $taskCount; ?></p>
                <p class="summary-description">Tasks with attached files</p>
            </div>
            <div class="summary-card">
                <h2>Recent Uploads</h2>
                <p class="summary-number"><?php echo $recentWeek; ?></p>
                <p class="summary-description">Files uploaded this week</p>
            </div>
            <div class="summary-card">
                <h2>Storage Used</h2>
                <p class="summary-number"><?php echo $storagePct; ?>%</p>
                <p class="summary-description"><?php echo htmlspecialchars($storageText); ?> of 1 GB</p>
            </div>
        </section>

        <!-- Upload -->
        <section class="upload-section">
            <h2 class="section-title">Upload New File</h2>

            <div class="upload-area">
                <div class="upload-icon">+</div>
                <h3>Select a project file</h3>
                <p>Supported: PDF, DOCX, PPTX, XLSX, PNG, JPG, SQL, ZIP, FIG, IPYNB</p>

                <form action="upload_shared_file.php" method="post"
                      enctype="multipart/form-data" class="upload-form" id="uploadForm">

                    <input type="hidden" name="groupID" value="<?php echo $activeGroupID; ?>">

                    <div class="upload-row">
                        <label for="fileUpload" class="upload-btn">Choose File</label>
                        <input type="file" id="fileUpload" name="sharedFile"
                               class="file-input"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.txt,.zip,.fig,.sql,.ipynb"
                               onchange="onSharedFileSelected()">

                        <button type="submit" class="submit-upload" id="submitUpload" disabled>
                            Upload to Shared Files
                        </button>
                    </div>

                    <!-- Extra fields: shown after a file is selected -->
                    <div class="upload-extra" id="uploadExtra">

                        <div class="form-group">
                            <label for="displayName">Display Name</label>
                            <input type="text" id="displayName" name="displayName"
                                   placeholder="Enter a name for this file">

                        </div>

                        <div class="form-group">
                            <label for="fileNotes">Description / Notes (optional)</label>
                            <textarea id="fileNotes" name="fileNotes" rows="3"
                                      placeholder="Explain what this file is for, or leave a note for your teammates..."></textarea>
                        </div>

                    </div>

                    <div class="file-preview" id="filePreview"></div>
                </form>
            </div>
        </section>

        <!-- File list -->
        <section class="file-section">
            <div class="file-section-header">
                <h2>Project Files</h2>

                <form method="get" action="student_files.php" class="toolbar">
                    <input type="hidden" name="group_id" value="<?php echo $activeGroupID; ?>">

                    <input type="search" name="q" class="search-input"
                           placeholder="Search files"
                           value="<?php echo htmlspecialchars($filterSearch); ?>">

                    <select name="status" class="filter-select">
                        <option value="all"      <?php echo $filterStatus==='all'?'selected':''; ?>>All Status</option>
                        <option value="Unviewed" <?php echo $filterStatus==='Unviewed'?'selected':''; ?>>Unviewed</option>
                        <option value="Accepted" <?php echo $filterStatus==='Accepted'?'selected':''; ?>>Accepted</option>
                        <option value="Rejected" <?php echo $filterStatus==='Rejected'?'selected':''; ?>>Rejected</option>
                    </select>

                    <button type="submit" class="filter-btn">Filter</button>
                </form>
            </div>

            <?php if (count($files) === 0): ?>
                <p class="muted">No files match your filter.</p>
            <?php else: ?>

                <?php if (count($standalone) > 0): ?>

                    <div class="file-list">
                        <?php foreach ($standalone as $f): ?>
                            <?php renderFileItem($f); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($groupedByTask as $taskID => $taskFiles): ?>
                    <h4 class="file-group-heading">
                        Task: <?php echo htmlspecialchars($taskFiles[0]['task_title']); ?>
                    </h4>
                    <div class="file-list">
                        <?php foreach ($taskFiles as $f): ?>
                            <?php renderFileItem($f); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

            <div class="storage-area">
                <div class="storage-heading">
                    <strong>Team Storage</strong>
                    <span><?php echo htmlspecialchars($storageText); ?> / 1 GB</span>
                </div>
                <div class="storage-bar">
                    <div class="storage-value" style="width: <?php echo $storagePct; ?>%;"></div>
                </div>
                <p class="storage-note">
                    <?php echo $storagePct; ?>% of the team storage has been used.
                </p>
            </div>

            <div class="file-note">
                Files uploaded here are shared with your team members and your supervisor.
                Your supervisor can view, accept or reject each file.
            </div>
        </section>

    <?php endif; ?>

</main>

<script>
    function onSharedFileSelected() {
        var input     = document.getElementById('fileUpload');
        var preview   = document.getElementById('filePreview');
        var submit    = document.getElementById('submitUpload');
        var extra     = document.getElementById('uploadExtra');
        var nameInput = document.getElementById('displayName');

        if (!input || !input.files || input.files.length === 0) {
            preview.classList.remove('visible');
            preview.textContent = '';
            extra.classList.remove('visible');
            submit.disabled = true;
            return;
        }

        var fileName = input.files[0].name;

        // Show selected filename
        preview.textContent = '✔ ' + fileName;
        preview.classList.add('visible');

        // Reveal the rename + notes fields
        extra.classList.add('visible');

        // Pre-fill display name (strip extension)
        nameInput.value = fileName.replace(/\.[^/.]+$/, '');

        submit.disabled = false;
    }
</script>

</body>
</html>