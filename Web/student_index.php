<?php
session_start();

// ===== session + 数据库 =====
require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// =========================================================
// 1. 取得当前学生资料
// =========================================================
$student = array(
        'student_id'    => 0,
        'student_no'    => '',
        'full_name'     => '',
        'major'         => '',
        'grade_level'   => '',
);

$sql = "
    SELECT s.id AS student_id,
           s.student_number,
           s.major,
           s.grade_level,
           u.username,
           u.email
    FROM `students` s
    JOIN `users` u ON u.id = s.user_id
    WHERE u.id = ?
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($row) {
    $student['student_id']  = (int) $row['student_id'];
    $student['student_no']  = $row['student_number'];
    $student['full_name']   = $row['username'];
    $student['major']       = $row['major'];
    $student['grade_level'] = $row['grade_level'];
} else {
    header('Location: logout.php');
    exit;
}

$studentID = $student['student_id'];

// =========================================================
// 1.5 取得学生所有 group（Executing / Voting）
// =========================================================
$myGroups = array();

$sql = "
    SELECT
        g.id            AS group_id,
        g.name          AS group_name,
        g.status,
        g.project_id,
        p.name          AS project_name
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    LEFT JOIN `projects` p ON p.id = g.project_id
    WHERE gm.student_id = ?
      AND g.status IN ('Voting', 'Executing')
    ORDER BY g.created_at DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $myGroups[] = $row;
}
mysqli_stmt_close($stmt);

// 決定 active group（?group_id=）
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
// 2. 汇总卡片
// =========================================================
$activeProjects = 0;
$sql = "
    SELECT COUNT(DISTINCT g.project_id) AS c
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    WHERE gm.student_id = ? AND g.status IN ('Executing','Voting')
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$activeProjects = (int) $rc['c'];
mysqli_stmt_close($stmt);

$assignedTasks = 0;
$sql = "SELECT COUNT(*) AS c FROM `tasks` WHERE assigned_to = ? AND status <> 'Completed'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$assignedTasks = (int) $rc['c'];
mysqli_stmt_close($stmt);

$completedTasks = 0;
$sql = "SELECT COUNT(*) AS c FROM `tasks` WHERE assigned_to = ? AND status = 'Completed'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$completedTasks = (int) $rc['c'];
mysqli_stmt_close($stmt);

// =========================================================
// 2.5 未讀通知數量 + 預覽列表
// =========================================================
$unreadNotifications = 0;
$sql = "SELECT COUNT(*) AS c FROM `notifications` WHERE user_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int) $rc['c'];
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
// 3. Recent Tasks (filtered by active group)
// =========================================================
$recentTasks = array();

if ($myGroup) {
    $sql = "
        SELECT id, title, status, due_date
        FROM `tasks`
        WHERE assigned_to = ? AND group_id = ?
        ORDER BY created_at DESC
        LIMIT 3
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $studentID, $activeGroupID);
} else {
    $sql = "
        SELECT id, title, status, due_date
        FROM `tasks`
        WHERE assigned_to = ?
        ORDER BY created_at DESC
        LIMIT 3
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $studentID);
}

mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $recentTasks[] = $row; }
mysqli_stmt_close($stmt);

// =========================================================
// 4. Team Progress (active group)
// =========================================================
$teamProgress = 0;
$teamDone     = 0;
$teamTotal    = 0;

if ($myGroup) {
    $sql = "
        SELECT COUNT(*) AS total, SUM(status = 'Completed') AS done
        FROM `tasks`
        WHERE group_id = ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);
    if ($rc) {
        $teamTotal = (int) $rc['total'];
        $teamDone  = (int) $rc['done'];
        if ($teamTotal > 0) { $teamProgress = round($teamDone / $teamTotal * 100); }
    }
}

// =========================================================
// 5. Announcements（含目标 Team 与发送老师）
// =========================================================
$announcements = array();

$myCourseIds = array();
$stmt = mysqli_prepare($conn, "SELECT DISTINCT course_id FROM `course_enrollments` WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $myCourseIds[] = (int) $row['course_id']; }
mysqli_stmt_close($stmt);

$myGroupIds = array();
$stmt = mysqli_prepare($conn, "SELECT DISTINCT group_id FROM `group_members` WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $myGroupIds[] = (int) $row['group_id']; }
mysqli_stmt_close($stmt);

$annConditions = array();
$annParams     = array();
$annTypes      = '';

if (count($myCourseIds) > 0) {
    $ph = implode(',', array_fill(0, count($myCourseIds), '?'));
    $annConditions[] = "(t.target_type = 'course' AND t.target_id IN ($ph))";
    foreach ($myCourseIds as $cid) { $annParams[] = $cid; $annTypes .= 'i'; }
}
if (count($myGroupIds) > 0) {
    $ph = implode(',', array_fill(0, count($myGroupIds), '?'));
    $annConditions[] = "(t.target_type = 'group' AND t.target_id IN ($ph))";
    foreach ($myGroupIds as $gid) { $annParams[] = $gid; $annTypes .= 'i'; }
}
$annConditions[] = "(t.target_type = 'student' AND t.target_id = ?)";
$annParams[]     = $studentID;
$annTypes       .= 'i';

if (count($annConditions) > 0) {
    $annWhere = implode(' OR ', $annConditions);
    $annSql = "
        SELECT DISTINCT a.id, a.title, a.message, a.created_at,
               u.username AS teacher_name,
               t.target_type, t.target_id
        FROM `announcements` a
        INNER JOIN `announcement_targets` t ON t.announcement_id = a.id
        LEFT JOIN `teachers` tc ON tc.id = a.teacher_id
        LEFT JOIN `users` u ON u.id = tc.user_id
        WHERE a.status = 'Sent'
          AND ($annWhere)
        ORDER BY a.created_at DESC
        LIMIT 10
    ";
    $stmt = mysqli_prepare($conn, $annSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $annTypes, ...$annParams);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $targetLabel = '';
            if ($row['target_type'] === 'group') {
                $gStmt = mysqli_prepare($conn, "SELECT name FROM `groups` WHERE id = ? LIMIT 1");
                if ($gStmt) {
                    $tid = (int)$row['target_id'];
                    mysqli_stmt_bind_param($gStmt, "i", $tid);
                    mysqli_stmt_execute($gStmt);
                    $gRs = mysqli_stmt_get_result($gStmt);
                    $gRow = mysqli_fetch_assoc($gRs);
                    mysqli_stmt_close($gStmt);
                    if ($gRow) $targetLabel = 'Team · ' . $gRow['name'];
                }
            } elseif ($row['target_type'] === 'course') {
                $cStmt = mysqli_prepare($conn, "SELECT code, name FROM `courses` WHERE id = ? LIMIT 1");
                if ($cStmt) {
                    $tid = (int)$row['target_id'];
                    mysqli_stmt_bind_param($cStmt, "i", $tid);
                    mysqli_stmt_execute($cStmt);
                    $cRs = mysqli_stmt_get_result($cStmt);
                    $cRow = mysqli_fetch_assoc($cRs);
                    mysqli_stmt_close($cStmt);
                    if ($cRow) $targetLabel = 'Course · ' . $cRow['code'];
                }
            } else {
                $targetLabel = 'Personal';
            }
            $row['target_label'] = $targetLabel;
            $announcements[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// 辅助函数
if (!function_exists('statusClass')) {
    function statusClass($status) {
        switch ($status) {
            case 'Completed':   return 'completed';
            case 'In Progress': return 'in-progress';
            default:            return 'pending';
        }
    }
}
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (empty($date)) return '—';
        return date('d M Y', strtotime($date));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        .navbar {
            width: 100%; padding: 15px 30px; background: black;
            display: flex; flex-direction: column; gap: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-top {
            position: relative; width: 100%; min-height: 65px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .logo-area img { width: 65px; height: 65px; object-fit: contain; display: block; }
        .logo-area span { color: white; font-size: 30px; font-weight: bold; }

        .calendar-page-link {
            position: absolute;
            right: 52px;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            padding: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            transition: background .2s ease, transform .2s ease;
        }
        .calendar-page-link:hover {
            background: rgba(255,255,255,.15);
            transform: translateY(-50%) scale(1.04);
        }
        .calendar-page-link:focus-visible {
            outline: 2px solid white;
            outline-offset: 2px;
        }
        .calendar-page-link img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            display: block;
        }
        @media (max-width: 600px) {
            .calendar-page-link { right: 44px; width: 38px; height: 38px; }
            .calendar-page-link img { width: 22px; height: 22px; }
        }

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
            background: #d93025; color: white;
            border-radius: 9px; font-size: 11px; font-weight: bold;
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

        .welcome-section {
            padding: 30px; margin-bottom: 30px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .welcome-section h1 { margin-bottom: 10px; font-size: 35px; }
        .welcome-section p { color: #666; font-size: 17px; line-height: 1.6; }

        .dashboard-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 20px; margin-bottom: 30px;
        }
        .dashboard-card {
            display: block;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            min-height: 155px; padding: 25px;
            background: white; border-left: 5px solid black;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); }
        .dashboard-card:focus-visible { outline: 3px solid #175196; outline-offset: 3px; }
        .dashboard-card h2 { margin-bottom: 18px; color: #555; font-size: 17px; }
        .dashboard-card .number { color: black; font-size: 38px; font-weight: bold; }
        .dashboard-card .description { margin-top: 8px; color: #777; font-size: 14px; }

        /* Announcement list */
        .announcement-section {
            padding: 26px 28px;
            margin-bottom: 25px;
            background: white;
            border-radius: 15px;
            border-left: 6px solid #175196;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .announcement-section h2 {
            font-size: 22px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eeeeee;
        }
        .announcement-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .announcement-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            background: #fafbfc;
            border: 1px solid #eef0f2;
            border-radius: 8px;
        }
        .announcement-left {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .ann-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c2f36;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ann-meta-line {
            font-size: 12px;
            color: #777;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .ann-meta-line .ann-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .ann-meta-line .ann-tag.team { color: #175196; }
        .ann-meta-line .ann-tag.course { color: #765900; }
        .ann-meta-line .ann-tag.personal { color: #176b2c; }
        .ann-detail-btn {
            flex-shrink: 0;
            padding: 6px 14px;
            background: white;
            color: #175196;
            border: 1.5px solid #175196;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .ann-detail-btn:hover {
            background: #175196;
            color: white;
        }
        .announcement-empty {
            padding: 18px 0;
            color: #8b8f98;
            font-size: 13px;
            text-align: center;
        }

        /* Modal */
        .ann-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .ann-modal-overlay.active { display: flex; }
        .ann-modal {
            background: white;
            border-radius: 14px;
            width: 100%;
            max-width: 620px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            animation: annFadeIn 0.2s ease;
        }
        @keyframes annFadeIn {
            from { opacity: 0; transform: translateY(-12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .ann-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #eeeeee;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }
        .ann-modal-header h3 {
            font-size: 18px;
            color: #222;
            line-height: 1.4;
            word-break: break-word;
        }
        .ann-modal-close {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            background: white;
            color: #555;
            border: 1.5px solid #cccccc;
            border-radius: 7px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .ann-modal-close:hover {
            background: #c62828;
            color: white;
            border-color: #c62828;
        }
        .ann-modal-body {
            padding: 22px 24px;
            overflow-y: auto;
            flex: 1;
        }
        .ann-modal-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #777;
        }
        .ann-modal-meta strong { color: #333; font-weight: 600; }
        .ann-modal-message {
            font-size: 14.5px;
            line-height: 1.7;
            color: #333;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .information-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 25px;
        }
        .content-box {
            padding: 28px; background: white;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .content-box h2 {
            padding-bottom: 15px; margin-bottom: 20px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }

        /* Recent Tasks header + group switcher */
        .recent-tasks-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eeeeee;
        }
        .recent-tasks-header h2 {
            padding: 0;
            margin: 0;
            border-bottom: none;
            font-size: 23px;
        }
        .task-group-switcher {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .task-group-switcher label {
            font-size: 12px;
            color: #6b6f78;
            font-weight: 600;
            white-space: nowrap;
        }
        .task-group-switcher select {
            height: 38px;
            max-width: 260px;
            padding: 0 12px;
            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }
        .task-group-switcher select:focus { border-color: black; }
        .task-group-static {
            display: inline-flex;
            align-items: center;
            height: 38px;
            max-width: 260px;
            padding: 0 12px;
            background: #f7f8fa;
            color: #4d5661;
            border: 2px solid #e6e8eb;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .task-item {
            padding: 16px 0; border-bottom: 1px solid #eeeeee;
            display: flex; justify-content: space-between;
            align-items: center; gap: 20px;
        }
        .task-item:last-of-type { border-bottom: none; }
        .task-information h3 { margin-bottom: 6px; font-size: 17px; }
        .task-information p { color: #777; font-size: 14px; }
        .task-status {
            padding: 7px 12px; border-radius: 20px;
            font-size: 13px; font-weight: bold; white-space: nowrap;
        }
        .in-progress { background: #fff1c2; color: #765900; }
        .completed { background: #d9f5df; color: #176b2c; }
        .pending { background: #eeeeee; color: #555; }

        .muted { color: #777; font-size: 14px; }

        .progress-container {
            margin-top: 25px; padding-top: 20px;
            border-top: 2px solid #eeeeee;
        }
        .progress-information {
            margin-bottom: 12px; display: flex;
            justify-content: space-between; align-items: center;
        }
        .progress-information strong { font-size: 17px; }
        .progress-information span { font-weight: bold; }
        .progress-bar {
            width: 100%; height: 15px;
            background: #dddddd; border-radius: 10px; overflow: hidden;
        }
        .progress-value { height: 100%; background: black; border-radius: 10px; }
        .progress-description { margin-top: 10px; color: #777; font-size: 14px; }

        .quick-links { display: flex; flex-direction: column; gap: 14px; }
        .text-link {
            color: black; font-size: 16px; text-decoration: underline;
            transition: color 0.3s;
        }
        .text-link:hover { color: #555555; }
        .quick-note { margin-top: 16px; color: #777; font-size: 13px; line-height: 1.5; }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
            .information-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .navbar { padding: 15px; }
            .logo-area img { width: 55px; height: 55px; }
            .logo-area span { font-size: 26px; }
            .bell-link { width: 38px; height: 38px; }
            .bell-link img { width: 22px; height: 22px; }
            .bell-preview { width: calc(100vw - 40px); right: -8px; }
            .function-menu { flex-direction: column; gap: 12px; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .main-content { width: 94%; margin: 25px auto; }
            .welcome-section h1 { font-size: 28px; }
            .task-item { align-items: flex-start; flex-direction: column; }
            .announcement-item { flex-direction: column; align-items: stretch; }
            .ann-detail-btn { align-self: flex-end; }
            .recent-tasks-header { flex-direction: column; align-items: flex-start; }
            .task-group-switcher { width: 100%; }
            .task-group-switcher select,
            .task-group-static { max-width: 100%; width: 100%; }
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

        <a href="student_calendar.php" class="calendar-page-link" title="Calendar" aria-label="Open calendar page">
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
        <a href="student_index.php" class="nav-link active">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <section class="welcome-section">
        <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?></h1>
        <p>
            This is your TeamBox student dashboard. You can view
            your current project, assigned tasks, team progress,
            shared files and recent notifications.
        </p>
    </section>

    <section class="dashboard-grid">
        <a href="my_team.php" class="dashboard-card" aria-label="Open active projects and teams">
            <h2>Active Projects</h2>
            <p class="number"><?php echo $activeProjects; ?></p>
            <p class="description">Projects currently in progress</p>
        </a>
        <a href="student_task.php?filter=assigned" class="dashboard-card" aria-label="Open assigned tasks">
            <h2>Assigned Tasks</h2>
            <p class="number"><?php echo $assignedTasks; ?></p>
            <p class="description">Tasks assigned to you</p>
        </a>
        <a href="student_task.php?filter=completed" class="dashboard-card" aria-label="Open completed tasks">
            <h2>Completed Tasks</h2>
            <p class="number"><?php echo $completedTasks; ?></p>
            <p class="description">Tasks completed by you</p>
        </a>
        <a href="student_notifications.php" class="dashboard-card" aria-label="Open notifications">
            <h2>Notifications</h2>
            <p class="number"><?php echo $unreadNotifications; ?></p>
            <p class="description">Unread notifications</p>
        </a>
    </section>

    <!-- Announcements -->
    <section class="announcement-section">
        <h2>Announcements</h2>

        <?php if (count($announcements) === 0): ?>
            <p class="announcement-empty">No announcements at the moment.</p>
        <?php else: ?>
            <ul class="announcement-list">
                <?php foreach ($announcements as $ann): ?>
                    <li class="announcement-item">
                        <div class="announcement-left">
                            <span class="ann-title" title="<?php echo htmlspecialchars($ann['title']); ?>">
                                <?php echo htmlspecialchars($ann['title']); ?>
                            </span>
                            <div class="ann-meta-line">
                                <span class="ann-tag <?php echo $ann['target_type']; ?>">
                                    <?php
                                    if ($ann['target_type'] === 'group') {
                                        echo 'Team: ' . htmlspecialchars($ann['target_label']);
                                    } elseif ($ann['target_type'] === 'course') {
                                        echo 'Course: ' . htmlspecialchars($ann['target_label']);
                                    } else {
                                        echo 'Personal';
                                    }
                                    ?>
                                </span>
                                <span class="ann-tag">
                                    Sent by: <?php echo htmlspecialchars($ann['teacher_name'] ?: 'Teacher'); ?>
                                </span>
                            </div>
                        </div>
                        <button
                                type="button"
                                class="ann-detail-btn"
                                data-ann-id="<?php echo (int) $ann['id']; ?>"
                                data-ann-title="<?php echo htmlspecialchars($ann['title']); ?>"
                                data-ann-message="<?php echo htmlspecialchars($ann['message']); ?>"
                                data-ann-teacher="<?php echo htmlspecialchars($ann['teacher_name'] ?: 'Teacher'); ?>"
                                data-ann-time="<?php echo date('d M Y H:i', strtotime($ann['created_at'])); ?>"
                                data-ann-target="<?php echo htmlspecialchars($ann['target_label']); ?>"
                        >
                            Detail
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="information-grid">

        <div class="content-box">

            <div class="recent-tasks-header">
                <h2>Recent Tasks</h2>

                <?php if (count($myGroups) > 1): ?>
                    <!-- Multiple groups: dropdown (show team name only) -->
                    <div class="task-group-switcher">
                        <label for="taskGroupSwitcher">Group:</label>
                        <select id="taskGroupSwitcher" onchange="switchTaskGroup(this.value)">
                            <?php foreach ($myGroups as $g): ?>
                                <option value="<?php echo (int)$g['group_id']; ?>"
                                        <?php echo ((int)$g['group_id'] === $activeGroupID) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['group_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php elseif (count($myGroups) === 1): ?>
                    <!-- Only one group: static label (team name only, not clickable) -->
                    <span class="task-group-static"
                          title="<?php echo htmlspecialchars($myGroups[0]['group_name']); ?>">
                        <?php echo htmlspecialchars($myGroups[0]['group_name']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (count($recentTasks) === 0): ?>
                <p class="muted">No tasks yet.</p>
            <?php endif; ?>

            <?php foreach ($recentTasks as $task): ?>
                <div class="task-item">
                    <div class="task-information">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p>Deadline: <?php echo htmlspecialchars(formatDate($task['due_date'])); ?></p>
                    </div>
                    <span class="task-status <?php echo statusClass($task['status']); ?>">
                        <?php echo htmlspecialchars($task['status']); ?>
                    </span>
                </div>
            <?php endforeach; ?>

            <div class="progress-container">
                <div class="progress-information">
                    <strong>Team Progress</strong>
                    <span><?php echo $teamProgress; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-value" style="width: <?php echo $teamProgress; ?>%;"></div>
                </div>
                <p class="progress-description">
                    <?php echo $teamDone; ?> out of <?php echo $teamTotal; ?> team tasks completed.
                </p>
            </div>
        </div>

        <div class="content-box">
            <h2>Quick Access</h2>
            <div class="quick-links">
                <a href="my_team.php" class="text-link">View My Team</a>
                <a href="student_task.php" class="text-link">View My Tasks</a>
                <a href="student_files.php" class="text-link">Open Shared Files</a>
                <a href="student_chat.php" class="text-link">Open Group Chat</a>
            </div>
            <p class="quick-note">Click any link to open the matching page.</p>
        </div>

    </section>

</main>

<!-- Announcement Detail Modal -->
<div class="ann-modal-overlay" id="annModal">
    <div class="ann-modal" role="dialog" aria-modal="true" aria-labelledby="annModalTitle">
        <div class="ann-modal-header">
            <h3 id="annModalTitle">Announcement</h3>
            <button type="button" class="ann-modal-close" id="annModalClose" aria-label="Close">✕</button>
        </div>
        <div class="ann-modal-body">
            <div class="ann-modal-meta">
                <span><strong>Target:</strong> <span id="annModalTarget">—</span></span>
                <span><strong>Teacher:</strong> <span id="annModalTeacher">—</span></span>
                <span><strong>Time:</strong> <span id="annModalTime">—</span></span>
            </div>
            <div class="ann-modal-message" id="annModalMessage">—</div>
        </div>
    </div>
</div>

<script>
    function switchTaskGroup(groupId) {
        window.location.href = 'student_index.php?group_id=' + groupId;
    }

    (function () {
        var modal    = document.getElementById('annModal');
        var closeBtn = document.getElementById('annModalClose');
        var titleEl  = document.getElementById('annModalTitle');
        var targetEl = document.getElementById('annModalTarget');
        var teacherEl= document.getElementById('annModalTeacher');
        var timeEl   = document.getElementById('annModalTime');
        var msgEl    = document.getElementById('annModalMessage');

        function openModal(btn) {
            titleEl.textContent   = btn.getAttribute('data-ann-title') || 'Announcement';
            targetEl.textContent  = btn.getAttribute('data-ann-target') || '—';
            teacherEl.textContent = btn.getAttribute('data-ann-teacher') || '—';
            timeEl.textContent    = btn.getAttribute('data-ann-time') || '—';
            msgEl.textContent     = btn.getAttribute('data-ann-message') || '—';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.ann-detail-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openModal(btn);
            });
        });

        closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    })();
</script>

</body>
</html>