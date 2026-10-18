<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($conn) || !$conn) {
    die('Database connection not available. Check db.php.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// =========================================================
// 更新自己 last_active_at
// =========================================================
$stmtActive = mysqli_prepare($conn, "UPDATE `users` SET `last_active_at` = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmtActive, "i", $userID);
mysqli_stmt_execute($stmtActive);
mysqli_stmt_close($stmtActive);

// =========================================================
// 1. 取 student_id
// =========================================================
$studentID   = 0;
$studentName = '';

$stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, u.username
    FROM `students` s
    JOIN `users` u ON u.id = s.user_id
    WHERE s.user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: logout.php');
    exit;
}
$studentID   = (int) $rc['student_id'];
$studentName = $rc['username'];

// =========================================================
// 2. 未讀通知數量
// =========================================================
$unreadNotifications = 0;

$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `notifications`
    WHERE user_id = ? AND is_read = 0
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int) $rc['c'];
mysqli_stmt_close($stmt);

// =========================================================
// 3. 取我所有 group
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
// 4. 決定 active group
// =========================================================
$activeGroupID = 0;

if (isset($_GET['group_id'])) {
    $activeGroupID = (int) $_GET['group_id'];
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
    $activeGroupID = (int) $myGroup['group_id'];
}

// =========================================================
// 4.5 DM 模式（?dm=student_id）
// =========================================================
$dmStudentID = 0;
$dmStudent   = null;

if (isset($_GET['dm'])) {
    $dmStudentID = (int) $_GET['dm'];
}

if ($dmStudentID > 0 && $myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            u.username      AS name,
            gm.role
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ? AND gm.student_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $activeGroupID, $dmStudentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $dmStudent = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$dmStudent) {
        $dmStudentID = 0;
        $dmStudent   = null;
    }
}

// =========================================================
// 4.6 標記 active group / DM 為已讀
// =========================================================
if ($myGroup) {

    if ($dmStudentID > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT MAX(id) AS max_id
            FROM `messages`
            WHERE group_id = ?
              AND (
                    (sender_id = ? AND recipient_id = ?)
                 OR (sender_id = ? AND recipient_id = ?)
              )
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiiii",
                $activeGroupID,
                $studentID, $dmStudentID,
                $dmStudentID, $studentID
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT MAX(id) AS max_id
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id IS NULL
        ");
        mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    }

    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rowMax = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    $maxReadID = $rowMax && $rowMax['max_id'] ? (int)$rowMax['max_id'] : 0;

    if ($maxReadID > 0) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `message_reads` (user_id, group_id, last_read_message_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))
        ");
        mysqli_stmt_bind_param($stmt, "iii", $userID, $activeGroupID, $maxReadID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// =========================================================
// 4.7 計算每個 group 嘅未讀數 + 每個成員嘅未讀 DM 數
// =========================================================
$unreadByGroup  = array();
$unreadByMember = array();

if (count($myGroups) > 0) {
    foreach ($myGroups as $g) {
        $gid = (int)$g['group_id'];

        $stmt = mysqli_prepare($conn, "
            SELECT last_read_message_id
            FROM `message_reads`
            WHERE user_id = ? AND group_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $userID, $gid);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $rowRead = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        $lastRead = $rowRead ? (int)$rowRead['last_read_message_id'] : 0;

        $stmt = mysqli_prepare($conn, "
            SELECT COUNT(*) AS c
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id IS NULL
              AND sender_id <> ?
              AND id > ?
        ");
        mysqli_stmt_bind_param($stmt, "iii", $gid, $studentID, $lastRead);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $rc = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        $unreadByGroup[$gid] = (int)$rc['c'];

        $stmt = mysqli_prepare($conn, "
            SELECT sender_id, COUNT(*) AS c
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id = ?
              AND sender_id <> ?
              AND id > ?
            GROUP BY sender_id
        ");
        mysqli_stmt_bind_param($stmt, "iiii", $gid, $studentID, $studentID, $lastRead);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($rowDm = mysqli_fetch_assoc($rs)) {
            $sid = (int)$rowDm['sender_id'];
            if (!isset($unreadByMember[$sid])) {
                $unreadByMember[$sid] = 0;
            }
            $unreadByMember[$sid] += (int)$rowDm['c'];
        }
        mysqli_stmt_close($stmt);
    }
}

// =========================================================
// 5. 處理 POST：發送訊息（文字 + 附件 可同時）
// =========================================================
$flashMsg  = '';
$flashType = '';

if (isset($_POST['sendMessage']) && $myGroup) {

    $messageText  = trim(isset($_POST['message']) ? $_POST['message'] : '');
    $attachmentID = null;

    $recipientID = null;
    if (isset($_POST['recipient_id']) && (int)$_POST['recipient_id'] > 0) {
        $recipientID = (int) $_POST['recipient_id'];
    }

    // -------- 5.1 有冇附件？ --------
    if (isset($_FILES['chatFile'])
            && $_FILES['chatFile']['error'] === UPLOAD_ERR_OK
            && $_FILES['chatFile']['size'] > 0) {

        $file     = $_FILES['chatFile'];
        $origName = $file['name'];
        $fileSize = $file['size'];
        $tmpPath  = $file['tmp_name'];

        $allowedExt = array(
                'pdf', 'doc', 'docx', 'ppt', 'pptx',
                'xls', 'xlsx', 'png', 'jpg', 'jpeg',
                'txt', 'zip', 'fig', 'sql', 'ipynb'
        );
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $flashMsg  = 'File type not allowed.';
            $flashType = 'error';
        } else {
            if (!function_exists('formatBytes')) {
                function formatBytes($bytes) {
                    if ($bytes < 1024)               return $bytes . ' B';
                    if ($bytes < 1024 * 1024)        return round($bytes / 1024, 1) . ' KB';
                    if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 1) . ' MB';
                    return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
                }
            }
            $sizeText = formatBytes($fileSize);
            $fileType = strtoupper($ext);

            // =========================================================
            // 【修正】先產生 storedName，搬移後一次 INSERT 寫入
            // =========================================================
            $storedName = 'chat' . $activeGroupID . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

            $uploadDir = __DIR__ . '/uploads/chat_files/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $targetPath = $uploadDir . $storedName;

            if (!move_uploaded_file($tmpPath, $targetPath)) {
                $flashMsg  = 'Failed to save uploaded file.';
                $flashType = 'error';
            } else {
                $stmt = mysqli_prepare($conn, "
                    INSERT INTO `files`
                        (project_id, group_id, task_id, uploader_id,
                         file_name, stored_name, file_size, file_type, status)
                    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'Unviewed')
                ");
                $projectId = (int) $myGroup['project_id'];

                mysqli_stmt_bind_param(
                        $stmt, "iiissss",
                        $projectId, $activeGroupID, $studentID,
                        $origName, $storedName, $sizeText, $fileType
                );

                if (mysqli_stmt_execute($stmt)) {
                    $attachmentID = (int) mysqli_insert_id($conn);
                } else {
                    $flashMsg  = 'Failed to save file: ' . mysqli_error($conn);
                    $flashType = 'error';
                    @unlink($targetPath);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // -------- 5.2 文字 / 附件最少要有一個 --------
    if ($flashType !== 'error' && ($messageText !== '' || $attachmentID !== null)) {

        $stmt = mysqli_prepare($conn, "
            INSERT INTO `messages` (group_id, sender_id, recipient_id, content, attachment_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiisi",
                $activeGroupID, $studentID, $recipientID,
                $messageText,
                $attachmentID
        );

        if (mysqli_stmt_execute($stmt)) {
            $flashMsg  = 'Message sent.';
            $flashType = 'success';
        } else {
            $flashMsg  = 'Failed to send message: ' . mysqli_error($conn);
            $flashType = 'error';
        }
        mysqli_stmt_close($stmt);

        if ($recipientID !== null) {
            header('Location: student_chat.php?group_id=' . $activeGroupID . '&dm=' . $recipientID);
        } else {
            header('Location: student_chat.php?group_id=' . $activeGroupID);
        }
        exit;
    } elseif ($flashType !== 'error') {
        $flashMsg  = 'Please type a message or attach a file.';
        $flashType = 'error';
    }
}

// =========================================================
// 6. 拎 group 資料
// =========================================================
$groupMembers = array();
$messages     = array();
$allMembers   = array();

if ($myGroup) {

    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            u.username      AS name,
            gm.role,
            u.last_active_at,
            (u.last_active_at IS NOT NULL
             AND u.last_active_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, s.id
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $groupMembers[] = $row;
    }
    mysqli_stmt_close($stmt);

    if (count($myGroups) > 0) {
        $groupIds = array();
        foreach ($myGroups as $g) {
            $groupIds[] = (int)$g['group_id'];
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $types = str_repeat('i', count($groupIds));

        $sql = "
            SELECT
                s.id            AS student_id,
                u.username      AS name,
                u.last_active_at,
                (u.last_active_at IS NOT NULL
                 AND u.last_active_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS group_names
            FROM `group_members` gm
            JOIN `students` s ON s.id = gm.student_id
            JOIN `users`    u ON u.id = s.user_id
            JOIN `groups`   g ON g.id = gm.group_id
            WHERE gm.group_id IN ($placeholders)
            GROUP BY s.id, u.username, u.last_active_at
            ORDER BY u.username
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$groupIds);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $allMembers[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    if ($dmStudentID > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.created_at,
                m.sender_id,
                m.recipient_id,
                m.attachment_id,
                u.username      AS sender_name,
                gm.role         AS sender_role,
                f.file_name     AS attachment_name,
                f.file_size     AS attachment_size,
                f.file_type     AS attachment_type
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users`    u ON u.id = s.user_id
            LEFT JOIN `group_members` gm
                   ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND (
                    (m.sender_id = ? AND m.recipient_id = ?)
                 OR (m.sender_id = ? AND m.recipient_id = ?)
              )
            ORDER BY m.created_at ASC
            LIMIT 200
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiiii",
                $activeGroupID,
                $studentID, $dmStudentID,
                $dmStudentID, $studentID
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.created_at,
                m.sender_id,
                m.recipient_id,
                m.attachment_id,
                u.username      AS sender_name,
                gm.role         AS sender_role,
                f.file_name     AS attachment_name,
                f.file_size     AS attachment_size,
                f.file_type     AS attachment_type
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users`    u ON u.id = s.user_id
            LEFT JOIN `group_members` gm
                   ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND m.recipient_id IS NULL
            ORDER BY m.created_at ASC
            LIMIT 200
        ");
        mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    }

    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 7. Helper
// =========================================================
if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Chat | TeamBox</title>

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

        .bell-link {
            position: absolute; right: 0; top: 50%;
            transform: translateY(-50%);
            display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%;
            transition: background 0.3s;
        }
        .bell-link:hover { background: rgba(255, 255, 255, 0.15); }
        .bell-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        .bell-badge {
            position: absolute; top: 2px; right: 2px;
            min-width: 18px; height: 18px; padding: 0 5px;
            background: #d93025; color: white;
            border-radius: 9px; font-size: 11px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid black;
        }

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

        .alert {
            padding: 14px 18px; margin-bottom: 20px;
            border-radius: 8px; font-size: 14px;
            border-left: 4px solid;
        }
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
        .empty-state .create-group-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 200px; height: 50px; padding: 0 24px;
            background: black; color: white;
            border: 2px solid black; border-radius: 9px;
            font-size: 16px; font-weight: bold;
            text-decoration: none; cursor: pointer; transition: 0.3s;
        }
        .empty-state .create-group-btn:hover { background: #333; }

        .chat-container {
            height: 700px;
            min-height: 500px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 290px 1fr;
            grid-template-rows: 100%;
        }

        .member-sidebar {
            background: #f7f7f7;
            border-right: 1px solid #dddddd;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        .sidebar-heading {
            padding: 15px 20px;
            border-bottom: 1px solid #dddddd;
            flex-shrink: 0;
        }
        .sidebar-heading h2 {
            margin-bottom: 4px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b6f78;
        }
        .sidebar-heading p { color: #8b8f98; font-size: 12px; }

        .group-list {
            padding: 10px 12px;
            max-height: 180px;
            overflow-y: auto;
            flex-shrink: 0;
            border-bottom: 1px solid #dddddd;
        }

        .group-item {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 6px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            cursor: pointer;
            transition: 0.2s;
        }
        .group-item:hover {
            border-color: black;
            background: #fafbfc;
        }
        .group-item.active {
            background: black;
            color: white;
            border-color: black;
        }

        .group-icon {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            background: #333;
            color: white;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            font-weight: bold;
        }
        .group-item.active .group-icon {
            background: white;
            color: black;
        }

        .group-details {
            flex: 1;
            min-width: 0;
        }
        .group-details h3 {
            font-size: 13px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .group-details p {
            font-size: 11px;
            color: #8b8f98;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .group-item.active .group-details p {
            color: #cccccc;
        }

        .member-list {
            padding: 10px 12px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }
        .member-item {
            width: 100%; padding: 10px 12px; margin-bottom: 6px;
            background: transparent; border: none; border-radius: 9px;
            display: flex; align-items: center; gap: 10px;
            text-align: left; cursor: pointer; transition: 0.3s;
        }
        .member-item:hover { background: #e8e8e8; }
        .member-item.active { background: black; color: white; }

        .member-avatar {
            width: 36px; height: 36px; flex-shrink: 0;
            background: #333; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 13px; font-weight: bold;
        }
        .member-item.active .member-avatar { background: white; color: black; }

        .member-details { flex: 1; min-width: 0; }
        .member-details h3 {
            margin-bottom: 3px;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .member-details p {
            color: #777;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .member-item.active .member-details p { color: #dddddd; }

        .member-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .member-status {
            width: 10px;
            height: 10px;
            flex-shrink: 0;
            background: #2e9d4d;
            border-radius: 50%;
        }
        .member-status.offline {
            background: #999999;
        }

        .unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            background: #d93025;
            color: white;
            border-radius: 9px;
            font-size: 10px;
            font-weight: bold;
            flex-shrink: 0;
            line-height: 1;
        }
        .group-item.active .unread-badge {
            background: white;
            color: #d93025;
        }
        .member-item.active .unread-badge {
            background: white;
            color: #d93025;
        }

        .chat-area {
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            min-height: 81px;
            padding: 18px 24px;
            border-bottom: 1px solid #dddddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }
        .chat-title h2 { margin-bottom: 5px; font-size: 22px; }
        .chat-title p { color: #777; font-size: 13px; }

        .message-area {
            flex: 1 1 auto;
            min-height: 0;
            padding: 24px;
            background: #fcfcfc;
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
            overscroll-behavior: contain;
        }

        .date-separator {
            margin: 12px 0 24px;
            display: flex; align-items: center; gap: 13px;
            color: #888; font-size: 12px;
        }
        .date-separator::before,
        .date-separator::after {
            content: ""; height: 1px; flex: 1; background: #dddddd;
        }

        .message-row {
            margin-bottom: 20px;
            display: flex; align-items: flex-end; gap: 11px;
        }
        .message-row.my-message { justify-content: flex-end; }

        .message-avatar {
            width: 38px; height: 38px; flex-shrink: 0;
            background: #333; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 12px; font-weight: bold;
        }
        .message-content { max-width: 65%; }
        .sender-name { margin-bottom: 6px; color: #666; font-size: 12px; }
        .my-message .sender-name { text-align: right; }

        .message-bubble {
            padding: 13px 16px;
            background: #eeeeee; color: #222;
            border-radius: 4px 15px 15px 15px;
            font-size: 14px; line-height: 1.55; word-break: break-word;
        }
        .my-message .message-bubble {
            background: black; color: white;
            border-radius: 15px 4px 15px 15px;
        }

        .attachment-image-wrap {
            margin-top: 6px;
            max-width: 320px;
        }
        .attachment-image-wrap > a {
            display: block;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e6e8eb;
            background: #fafbfc;
            transition: 0.2s;
        }
        .attachment-image-wrap > a:hover {
            border-color: black;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .attachment-image {
            display: block;
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .attachment-image-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            margin-top: 4px;
            font-size: 11px;
            color: #8b8f98;
        }
        .my-message .attachment-image-meta { color: #a8acb4; }

        .file-name-small {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #175196;
            text-decoration: none;
            font-weight: 500;
            font-size: 11px;
        }
        .file-name-small:hover {
            text-decoration: underline;
            color: #123f75;
        }
        .my-message .file-name-small { color: #8ec5ff; }
        .my-message .file-name-small:hover { color: #b8dbff; }

        .file-size-small { flex-shrink: 0; }

        .attachment-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
            padding: 10px 12px;
            background: #f7f8fa;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            font-size: 13px;
            max-width: 320px;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: 0.2s;
        }
        .attachment-chip:hover {
            background: white;
            border-color: black;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }
        .my-message .attachment-chip {
            background: #1f232b;
            border-color: #2a2f3a;
        }
        .my-message .attachment-chip:hover {
            background: #2a2f3a;
            border-color: white;
        }

        .attachment-icon {
            width: 34px; height: 34px;
            background: black; color: white;
            border-radius: 6px;
            display: flex; justify-content: center; align-items: center;
            font-size: 10px; font-weight: bold;
            flex-shrink: 0;
        }
        .my-message .attachment-icon {
            background: white; color: black;
        }
        .attachment-info {
            flex: 1; min-width: 0;
        }
        .attachment-info h4 {
            font-size: 13px; font-weight: 600;
            margin-bottom: 3px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            color: #175196;
        }
        .my-message .attachment-info h4 { color: #8ec5ff; }
        .attachment-chip:hover .attachment-info h4 { text-decoration: underline; }

        .attachment-info p {
            font-size: 11px; color: #8b8f98;
        }
        .my-message .attachment-info p { color: #a8acb4; }

        .message-time {
            display: block; margin-top: 6px;
            color: #888; font-size: 11px;
        }
        .my-message .message-time { text-align: right; }

        .message-input-area {
            flex-shrink: 0;
            padding: 17px 20px;
            background: white;
            border-top: 1px solid #dddddd;
            display: flex;
            align-items: flex-end;
            gap: 10px;
            position: relative;
        }

        .attach-button {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            background: white;
            color: #333;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 22px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            line-height: 1;
            position: relative;
        }
        .attach-button:hover {
            background: #f7f7f7;
            border-color: black;
        }
        .attach-button.has-file {
            background: #eaf7ee;
            border-color: #2e9d4d;
            color: #176b2c;
        }
        .attach-button input[type="file"] {
            display: none;
        }

        .selected-file {
            flex-shrink: 0;
            align-self: flex-end;
            display: none;
            align-items: center;
            gap: 6px;
            padding: 0 10px;
            height: 46px;
            background: #eaf7ee;
            border: 2px solid #2e9d4d;
            border-radius: 8px;
            font-size: 12px;
            color: #176b2c;
            font-weight: 600;
            max-width: 160px;
            overflow: hidden;
        }
        .selected-file.visible { display: flex; }
        .selected-file .file-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }
        .selected-file .remove-file {
            background: none;
            border: none;
            color: #a21b1b;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            padding: 0 2px;
        }
        .selected-file .remove-file:hover {
            color: #d32f2f;
        }

        .message-input {
            min-height: 46px;
            max-height: 110px;
            height: 46px;
            flex: 1;
            padding: 10px 14px;
            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 14px;
            line-height: 22px;
            font-family: inherit;
            outline: none;
            resize: none;
            overflow-y: auto;
            transition: border-color 0.2s, height 0.05s;
        }
        .message-input:focus { border-color: black; }

        .emoji-button {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            background: white;
            color: #333;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 22px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            line-height: 1;
        }
        .emoji-button:hover {
            background: #f7f7f7;
            border-color: black;
        }
        .emoji-button.active {
            background: black;
            color: white;
            border-color: black;
        }

        .send-button {
            min-width: 100px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            padding: 0 16px;
            background: black;
            color: white;
            border: 2px solid black;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .send-button:hover { background: #333; }
        .send-button:active { transform: scale(0.97); }
        .send-button:disabled {
            background: #cccccc;
            border-color: #cccccc;
            color: #888;
            cursor: not-allowed;
            transform: none;
        }

        .emoji-picker {
            position: absolute;
            bottom: 100%;
            right: 120px;
            margin-bottom: 10px;
            width: 340px;
            max-height: 280px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            display: none;
            z-index: 50;
            flex-direction: column;
        }
        .emoji-picker.open { display: flex; }

        .emoji-picker-header {
            padding: 10px 14px;
            border-bottom: 1px solid #eeeeee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
            font-size: 13px;
            font-weight: 600;
            color: #6b6f78;
            flex-shrink: 0;
        }
        .emoji-picker-close {
            background: none;
            border: none;
            color: #888;
            font-size: 18px;
            cursor: pointer;
            line-height: 1;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .emoji-picker-close:hover { background: #eeeeee; color: #d32f2f; }

        .emoji-categories {
            display: flex;
            gap: 2px;
            padding: 8px 10px;
            border-bottom: 1px solid #eeeeee;
            overflow-x: auto;
            flex-shrink: 0;
            scrollbar-width: thin;
        }
        .emoji-cat-btn {
            flex-shrink: 0;
            padding: 5px 8px;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }
        .emoji-cat-btn:hover { background: #f2f3f5; }
        .emoji-cat-btn.active { background: #175196; }

        .emoji-scroll {
            overflow-y: auto;
            padding: 10px;
            flex: 1;
            min-height: 0;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 2px;
        }

        .emoji-item {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-size: 22px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.15s;
            padding: 0;
            line-height: 1;
        }
        .emoji-item:hover {
            background: #f2f3f5;
            transform: scale(1.2);
        }

        .input-hint {
            flex-shrink: 0;
            padding: 8px 20px;
            background: #fafafa;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #8b8f98;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .input-hint .hint-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .input-hint .char-count { font-weight: 600; color: #6b6f78; }

        @media (max-width: 850px) {
            .chat-container {
                height: auto; min-height: auto;
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
            }
            .member-sidebar {
                border-right: none;
                border-bottom: 1px solid #dddddd;
                max-height: 420px;
            }
            .group-list {
                max-height: 150px;
            }
            .member-list {
                max-height: 240px;
            }
            .chat-area { height: 600px; min-height: 500px; }

            .emoji-picker { width: 300px; right: 10px; }
        }
        @media (max-width: 650px) {
            .navbar { padding: 15px; }
            .logo-area img { width: 55px; height: 55px; }
            .logo-area span { font-size: 26px; }
            .bell-link { width: 38px; height: 38px; }
            .bell-link img { width: 22px; height: 22px; }
            .function-menu { flex-direction: column; gap: 12px; }
            .main-content { width: 94%; margin: 25px auto; }
            .page-heading h1 { font-size: 28px; }

            .chat-header { align-items: flex-start; flex-direction: column; }
            .message-content { max-width: 80%; }

            .message-input-area { flex-wrap: wrap; }
            .message-input { min-width: 100%; }
            .attach-button,
            .emoji-button { width: 46px; height: 46px; }
            .send-button { flex: 1; width: auto; }

            .emoji-picker { width: calc(100% - 20px); right: 10px; left: 10px; }
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

        <a href="student_notifications.php" class="bell-link" title="Notifications">
            <img src="bell.png" alt="Notifications">
            <?php if ($unreadNotifications > 0): ?>
                <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link active">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <section class="page-heading">
        <h1>Group Chat</h1>
        <p>
            Communicate with your team members, discuss project tasks,
            share updates and exchange project files.
        </p>
    </section>

    <?php if (count($myGroups) === 0): ?>

        <div class="empty-state">
            <h2>You don't have an active team yet</h2>
            <p>
                Group chat is only available after your team has been submitted
                to your supervisor. Create or manage your group first.
            </p>
            <a href="student_create_group.php" class="create-group-btn">
                Go to Create Group
            </a>
        </div>

    <?php else: ?>

        <?php if ($flashMsg !== ''): ?>
            <div class="alert <?php echo $flashType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($flashMsg); ?>
            </div>
        <?php endif; ?>

        <section class="chat-container">

            <aside class="member-sidebar">

                <div class="sidebar-heading">
                    <h2>My Groups</h2>
                    <p><?php echo count($myGroups); ?> group<?php echo count($myGroups) > 1 ? 's' : ''; ?></p>
                </div>

                <div class="group-list">
                    <?php foreach ($myGroups as $g): ?>
                        <?php
                        $gid = (int)$g['group_id'];
                        $unread = isset($unreadByGroup[$gid]) ? (int)$unreadByGroup[$gid] : 0;
                        ?>
                        <button type="button"
                                class="group-item <?php echo ((int)$g['group_id'] === $activeGroupID && $dmStudentID === 0) ? 'active' : ''; ?>"
                                onclick="window.location.href='student_chat.php?group_id=<?php echo $gid; ?>'">
                            <div class="group-icon">GRP</div>
                            <div class="group-details">
                                <h3><?php echo htmlspecialchars($g['group_name']); ?></h3>
                                <p><?php echo htmlspecialchars($g['project_name']); ?></p>
                            </div>
                            <?php if ($unread > 0): ?>
                                <span class="unread-badge"><?php echo $unread > 99 ? '99+' : $unread; ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="member-list">
                    <?php foreach ($allMembers as $m): ?>
                        <?php if ((int)$m['student_id'] === $studentID) continue; ?>
                        <?php
                        $sid = (int)$m['student_id'];
                        $dmUnread = isset($unreadByMember[$sid]) ? (int)$unreadByMember[$sid] : 0;
                        ?>
                        <button type="button"
                                class="member-item <?php echo ((int)$m['student_id'] === $dmStudentID) ? 'active' : ''; ?>"
                                onclick="openDMChat(<?php echo $sid; ?>)"
                                title="Message <?php echo htmlspecialchars($m['name']); ?>">
                            <div class="member-avatar">
                                <?php echo htmlspecialchars(initials($m['name'])); ?>
                            </div>
                            <div class="member-details">
                                <h3><?php echo htmlspecialchars($m['name']); ?></h3>
                                <p><?php echo htmlspecialchars($m['group_names']); ?></p>
                            </div>
                            <div class="member-right">
                                <?php if ($dmUnread > 0): ?>
                                    <span class="unread-badge"><?php echo $dmUnread > 99 ? '99+' : $dmUnread; ?></span>
                                <?php endif; ?>
                                <span class="member-status <?php echo ((int)$m['is_online'] === 1) ? '' : 'offline'; ?>"
                                      title="<?php echo ((int)$m['is_online'] === 1) ? 'Online' : 'Offline'; ?>"></span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>

            </aside>

            <div class="chat-area">

                <header class="chat-header">
                    <div class="chat-title">
                        <?php if ($dmStudentID > 0 && $dmStudent): ?>
                            <h2>DM · <?php echo htmlspecialchars($dmStudent['name']); ?></h2>
                            <p><?php echo htmlspecialchars($myGroup['group_name']); ?> · <?php echo htmlspecialchars($myGroup['project_name']); ?></p>
                        <?php else: ?>
                            <h2><?php echo htmlspecialchars($myGroup['group_name']); ?> Group Chat</h2>
                            <p><?php echo count($groupMembers); ?> members · <?php echo htmlspecialchars($myGroup['project_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="message-area" id="messageArea">

                    <?php if (count($messages) === 0): ?>
                        <p style="text-align:center; color:#888; padding:40px 0;">
                            <?php if ($dmStudentID > 0 && $dmStudent): ?>
                                No messages yet. Start the conversation with <?php echo htmlspecialchars($dmStudent['name']); ?> 👋
                            <?php else: ?>
                                No messages yet. Say hello to your team 👋
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php
                    $lastDate = '';
                    foreach ($messages as $msg):
                        $isMe = ((int)$msg['sender_id'] === $studentID);

                        $msgDate = date('d M Y', strtotime($msg['created_at']));
                        if ($msgDate !== $lastDate):
                            $lastDate = $msgDate;
                            ?>
                            <div class="date-separator"><?php echo htmlspecialchars($msgDate); ?></div>
                        <?php endif; ?>

                        <div class="message-row <?php echo $isMe ? 'my-message' : ''; ?>">
                            <?php if (!$isMe): ?>
                                <div class="message-avatar">
                                    <?php echo htmlspecialchars(initials($msg['sender_name'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="message-content">
                                <p class="sender-name">
                                    <?php if ($isMe): ?>
                                        You
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($msg['sender_name'] ?: 'Unknown'); ?>
                                        <?php if (isset($msg['sender_role']) && $msg['sender_role'] === 'leader'): ?>
                                            · Team Leader
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>

                                <?php if (!empty($msg['content'])): ?>
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($msg['attachment_name'])): ?>
                                    <?php
                                    $attExt  = strtolower(pathinfo($msg['attachment_name'], PATHINFO_EXTENSION));
                                    $isImage = in_array($attExt, array('png', 'jpg', 'jpeg', 'gif', 'webp'));
                                    ?>

                                    <?php if ($isImage): ?>
                                        <div class="attachment-image-wrap">
                                            <a href="open_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                               target="_blank"
                                               title="Open full image">
                                                <img src="open_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                                     alt="<?php echo htmlspecialchars($msg['attachment_name']); ?>"
                                                     class="attachment-image"
                                                     loading="lazy">
                                            </a>
                                            <div class="attachment-image-meta">
                                                <a href="download_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                                   class="file-name-small"
                                                   title="Download <?php echo htmlspecialchars($msg['attachment_name']); ?>">
                                                    <?php echo htmlspecialchars($msg['attachment_name']); ?>
                                                </a>
                                                <span class="file-size-small">
                                                    <?php echo htmlspecialchars($msg['attachment_size']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <a href="download_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                           class="attachment-chip"
                                           title="Download <?php echo htmlspecialchars($msg['attachment_name']); ?>">
                                            <div class="attachment-icon">
                                                <?php echo htmlspecialchars($msg['attachment_type'] ?: 'FILE'); ?>
                                            </div>
                                            <div class="attachment-info">
                                                <h4><?php echo htmlspecialchars($msg['attachment_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($msg['attachment_size']); ?></p>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <span class="message-time">
                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <form action="student_chat.php?group_id=<?php echo $activeGroupID; ?><?php echo ($dmStudentID > 0 ? '&dm=' . $dmStudentID : ''); ?>"
                      method="post"
                      enctype="multipart/form-data"
                      class="message-input-area"
                      id="chatForm">

                    <input type="hidden" name="sendMessage" value="1">

                    <?php if ($dmStudentID > 0): ?>
                        <input type="hidden" name="recipient_id" value="<?php echo $dmStudentID; ?>">
                    <?php endif; ?>

                    <label for="chatFile" class="attach-button" id="attachBtn" title="Attach file">
                        +
                    </label>
                    <input type="file"
                           id="chatFile"
                           name="chatFile"
                           style="display:none;"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.txt,.zip,.fig,.sql,.ipynb"
                           onchange="onFileSelected()">

                    <div class="selected-file" id="selectedFile">
                        <span class="file-label" id="selectedFileName"></span>
                        <button type="button" class="remove-file" onclick="clearSelectedFile()" title="Remove">✕</button>
                    </div>

                    <textarea
                            name="message"
                            id="messageInput"
                            class="message-input"
                            placeholder="<?php echo $dmStudentID > 0 ? 'Message ' . htmlspecialchars($dmStudent['name']) . '...' : 'Type your message...'; ?> (Enter to send, Shift+Enter for new line)"
                            aria-label="Type your message"></textarea>

                    <button type="button"
                            class="emoji-button"
                            id="emojiButton"
                            title="Insert emoji">
                        😊
                    </button>

                    <button type="submit" class="send-button" id="sendButton" disabled>
                        Send
                    </button>

                    <div class="emoji-picker" id="emojiPicker">
                        <div class="emoji-picker-header">
                            <span>Emojis</span>
                            <button type="button" class="emoji-picker-close" id="emojiClose">✕</button>
                        </div>

                        <div class="emoji-categories" id="emojiCategories">
                            <button type="button" class="emoji-cat-btn active" data-cat="smileys">😀</button>
                            <button type="button" class="emoji-cat-btn" data-cat="gestures">👍</button>
                            <button type="button" class="emoji-cat-btn" data-cat="hearts">❤️</button>
                            <button type="button" class="emoji-cat-btn" data-cat="animals">🐶</button>
                            <button type="button" class="emoji-cat-btn" data-cat="food">🍔</button>
                            <button type="button" class="emoji-cat-btn" data-cat="objects">💡</button>
                            <button type="button" class="emoji-cat-btn" data-cat="symbols">✅</button>
                        </div>

                        <div class="emoji-scroll" id="emojiScroll">
                            <div class="emoji-grid" id="emojiGrid"></div>
                        </div>
                    </div>

                </form>

                <div class="input-hint">
                    <span class="hint-text">
                        Press <strong>Enter</strong> to send · <strong>Shift + Enter</strong> for new line
                    </span>
                    <span class="char-count" id="charCount">0 / 1000</span>
                </div>

            </div>
        </section>

    <?php endif; ?>

</main>

<script>
    function openDMChat(memberStudentID) {
        var groupId = <?php echo (int)$activeGroupID; ?>;
        window.location.href = 'student_chat.php?group_id=' + groupId + '&dm=' + memberStudentID;
    }

    (function () {
        var area = document.getElementById('messageArea');
        if (area) {
            area.scrollTop = area.scrollHeight;
        }
    })();

    (function () {
        var input        = document.getElementById('messageInput');
        var sendBtn      = document.getElementById('sendButton');
        var form         = document.getElementById('chatForm');
        var charCount    = document.getElementById('charCount');
        var fileInput    = document.getElementById('chatFile');
        var attachBtn    = document.getElementById('attachBtn');
        var selectedFile = document.getElementById('selectedFile');
        var selFileName  = document.getElementById('selectedFileName');

        if (!input || !sendBtn || !form) return;

        var MAX_LEN = 1000;

        function autoResize() {
            input.style.height = '46px';
            var newHeight = Math.min(input.scrollHeight, 110);
            input.style.height = newHeight + 'px';
        }

        function updateState() {
            var len = input.value.length;
            var hasText = (input.value.trim() !== '');
            var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

            sendBtn.disabled = !(hasText || hasFile);

            if (charCount) {
                charCount.textContent = len + ' / ' + MAX_LEN;
                charCount.style.color = (len > MAX_LEN) ? '#d32f2f' : '';
            }

            autoResize();
        }

        input.focus();
        input.addEventListener('input', updateState);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();

                var hasText = (input.value.trim() !== '');
                var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

                if (hasText || hasFile) {
                    form.submit();
                }
            }
        });

        window.onFileSelected = function () {
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                return;
            }

            var name = fileInput.files[0].name;

            if (selFileName) selFileName.textContent = name;
            if (selectedFile) selectedFile.classList.add('visible');
            if (attachBtn) attachBtn.classList.add('has-file');

            updateState();
            input.focus();
        };

        window.clearSelectedFile = function () {
            if (fileInput) fileInput.value = '';
            if (selectedFile) selectedFile.classList.remove('visible');
            if (attachBtn) attachBtn.classList.remove('has-file');

            updateState();
            input.focus();
        };

        updateState();
        autoResize();
    })();

    (function () {
        var emojiBtn    = document.getElementById('emojiButton');
        var picker      = document.getElementById('emojiPicker');
        var closeBtn    = document.getElementById('emojiClose');
        var grid        = document.getElementById('emojiGrid');
        var scrollArea  = document.getElementById('emojiScroll');
        var catBtns     = document.querySelectorAll('.emoji-cat-btn');
        var input       = document.getElementById('messageInput');

        if (!emojiBtn || !picker || !grid) return;

        var EMOJIS = {
            smileys: [
                '😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇',
                '🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚',
                '😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥳',
                '😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫',
                '😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳',
                '🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭',
                '🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧',
                '😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢',
                '🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹',
                '👺','🤡','💩','👻','💀','👽','🤖','😺','😸','😹'
            ],
            gestures: [
                '👍','👎','👌','✌️','🤞','🤟','🤘','🤙','👈','👉',
                '👆','👇','☝️','✋','🤚','🖐️','🖖','👋','🤝','🙏',
                '✍️','💅','🤳','💪','🦾','🦵','🦶','👂','🦻','👃',
                '👀','👁️','👅','👄','💋','🫶','🫰','🫱','🫲','🫳',
                '🤌','🤏','🫴','🫵','👏','🙌','👐','🤲','🤜','🤛',
                '✊','👊','🖕','🖐️','💪','🦿','🦴','👣','🧠','🫀'
            ],
            hearts: [
                '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔',
                '❣️','💕','💞','💓','💗','💖','💘','💝','💟','♥️',
                '💌','💋','👩‍❤️‍👨','👨‍❤️‍👨','👩‍❤️‍👩','💑','💏','👪','👨‍👩‍👧','👨‍👩‍👦'
            ],
            animals: [
                '🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯',
                '🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐒','🐔',
                '🐧','🐦','🐤','🐣','🐥','🦆','🦅','🦉','🦇','🐺',
                '🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟',
                '🦗','🕷️','🕸️','🦂','🐢','🐍','🦎','🦖','🦕','🐙',
                '🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋'
            ],
            food: [
                '🍏','🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐',
                '🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑',
                '🥦','🥬','🥒','🌶️','🫑','🌽','🥕','🫒','🧄','🧅',
                '🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳',
                '🧈','🥞','🧇','🥓','🥩','🍗','🍖','🦴','🌭','🍔',
                '🍟','🍕','🫓','🥪','🥙','🧆','🌮','🌯','🫔','🥗'
            ],
            objects: [
                '💡','🔦','🕯️','🪔','🧯','🛢️','💸','💵','💴','💶',
                '💷','🪙','💰','💳','💎','⚖️','🪜','🧰','🪛','🔧',
                '🔨','⚒️','🛠️','⛏️','🪚','🔩','⚙️','🪤','🧱','⛓️',
                '🧲','🔫','💣','🧨','🪓','🔪','🗡️','⚔️','🛡️','🚬',
                '⚰️','🪦','⚱️','🏺','🔮','📿','🧿','💈','⚗️','🔭'
            ],
            symbols: [
                '✅','❌','⭕','🚫','💯','🔴','🟠','🟡','🟢','🔵',
                '🟣','⚫','⚪','🟤','🔶','🔷','🔸','🔹','🔺','🔻',
                '💠','🔘','🔳','🔲','▪️','▫️','◾','◽','◼️','◻️',
                '🟥','🟧','🟨','🟩','🟦','🟪','⬛','⬜','🟫','🔈',
                '🔇','🔉','🔊','🔔','🔕','📣','📢','💬','💭','🗯️'
            ]
        };

        var currentCat = 'smileys';

        function renderEmojis(cat) {
            grid.innerHTML = '';
            var list = EMOJIS[cat] || [];

            list.forEach(function (emoji) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'emoji-item';
                btn.textContent = emoji;
                btn.title = emoji;

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    insertEmoji(emoji);
                });

                grid.appendChild(btn);
            });

            if (scrollArea) scrollArea.scrollTop = 0;
        }

        function insertEmoji(emoji) {
            if (!input) return;

            var start = input.selectionStart || 0;
            var end   = input.selectionEnd || 0;
            var value = input.value;

            input.value = value.slice(0, start) + emoji + value.slice(end);

            var newPos = start + emoji.length;
            input.setSelectionRange(newPos, newPos);
            input.focus();

            input.dispatchEvent(new Event('input'));
        }

        function openPicker() {
            picker.classList.add('open');
            emojiBtn.classList.add('active');
            renderEmojis(currentCat);
        }

        function closePicker() {
            picker.classList.remove('open');
            emojiBtn.classList.remove('active');
        }

        function togglePicker() {
            if (picker.classList.contains('open')) {
                closePicker();
            } else {
                openPicker();
            }
        }

        emojiBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            togglePicker();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closePicker();
            });
        }

        catBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                catBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                currentCat = btn.getAttribute('data-cat');
                renderEmojis(currentCat);
            });
        });

        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target) && e.target !== emojiBtn) {
                closePicker();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closePicker();
            }
        });

        closePicker();
    })();
</script>

</body>
</html>