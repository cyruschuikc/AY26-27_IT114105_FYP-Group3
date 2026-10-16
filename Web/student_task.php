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
// 5. Flash message（POST 結果 / GET msg）
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
            $flashMsg  = 'Something went wrong.';
            $flashType = 'error';
            break;
    }
}

// =========================================================
// 6. 處理 POST
// =========================================================

// -------- 6.1 Create Task --------
if (isset($_POST['createTask']) && $myGroup) {

    $title       = trim(isset($_POST['taskTitle'])       ? $_POST['taskTitle']       : '');
    $description = trim(isset($_POST['taskDescription']) ? $_POST['taskDescription'] : '');
    $assignedTo  = isset($_POST['assignedTo']) ? $_POST['assignedTo'] : '';
    $deadline    = trim(isset($_POST['taskDeadline'])    ? $_POST['taskDeadline']    : '');
    $priority    = trim(isset($_POST['taskPriority'])    ? $_POST['taskPriority']    : 'Medium');

    // assignedTo = 'self' → 用自己 studentID
    if ($assignedTo === 'self') {
        $assignedToID = $studentID;
    } else {
        $assignedToID = (int) $assignedTo;
    }

    if ($title === '' || $description === '' || $deadline === '' || $assignedToID === 0) {
        $flashMsg  = 'Please fill in all required fields.';
        $flashType = 'error';
    } elseif (!in_array($priority, array('Low', 'Medium', 'High'), true)) {
        $priority = 'Medium';
    } else {
        // 檢查 assigned_to 係唔係同組成員
        $validAssignee = false;

        $stmt = mysqli_prepare($conn, "
            SELECT 1 FROM `group_members`
            WHERE group_id = ? AND student_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $activeGroupID, $assignedToID);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_assoc($rs)) {
            $validAssignee = true;
        }
        mysqli_stmt_close($stmt);

        if (!$validAssignee) {
            $flashMsg  = 'Please select a valid team member.';
            $flashType = 'error';
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO `tasks`
                    (project_id, group_id, title, description, assigned_to, created_by, status, priority, due_date)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?)
            ");
            $projectId = (int) $myGroup['project_id'];

            mysqli_stmt_bind_param(
                    $stmt, "iissiiss",
                    $projectId, $activeGroupID, $title, $description,
                    $assignedToID, $userID, $priority, $deadline
            );

            if (mysqli_stmt_execute($stmt)) {
                $newTaskID = (int) mysqli_insert_id($conn);

                // 寫 activity
                $stmt2 = mysqli_prepare($conn, "
                    INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
                    VALUES (?, ?, ?, 'created', ?)
                ");
                $detail = 'created task "' . $title . '"';
                mysqli_stmt_bind_param($stmt2, "iiis", $projectId, $activeGroupID, $userID, $detail);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                // 通知被指派者（如果唔係自己）
                if ($assignedToID !== $studentID) {
                    $stmt3 = mysqli_prepare($conn, "
                        SELECT user_id FROM `students` WHERE id = ? LIMIT 1
                    ");
                    mysqli_stmt_bind_param($stmt3, "i", $assignedToID);
                    mysqli_stmt_execute($stmt3);
                    $rs3 = mysqli_stmt_get_result($stmt3);
                    $rc3 = mysqli_fetch_assoc($rs3);
                    mysqli_stmt_close($stmt3);

                    if ($rc3) {
                        $targetUserID = (int) $rc3['user_id'];
                        $notifyTitle = 'New Task Assigned';
                        $notifyMsg   = 'You have been assigned "' . $title . '".';
                        $notifyLink  = 'student_task.php?group_id=' . $activeGroupID;

                        $stmt4 = mysqli_prepare($conn, "
                            INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
                            VALUES (?, 'task', ?, ?, ?, 0)
                        ");
                        mysqli_stmt_bind_param($stmt4, "isss", $targetUserID, $notifyTitle, $notifyMsg, $notifyLink);
                        mysqli_stmt_execute($stmt4);
                        mysqli_stmt_close($stmt4);
                    }
                }

                $flashMsg  = 'Task created successfully.';
                $flashType = 'success';
            } else {
                $flashMsg  = 'Failed to create task: ' . mysqli_error($conn);
                $flashType = 'error';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -------- 6.2 Mark My Progress --------
if (isset($_POST['updateProgress']) && $myGroup) {

    $taskID = (int) (isset($_POST['taskID']) ? $_POST['taskID'] : 0);
    $status = trim(isset($_POST['status']) ? $_POST['status'] : '');

    if ($taskID > 0 && in_array($status, array('Pending', 'In Progress', 'Completed'), true)) {
        $stmt = mysqli_prepare($conn, "
            UPDATE `tasks`
            SET status = ?
            WHERE id = ? AND group_id = ? AND assigned_to = ?
        ");
        mysqli_stmt_bind_param($stmt, "siii", $status, $taskID, $activeGroupID, $studentID);

        if (mysqli_stmt_execute($stmt)) {
            $flashMsg  = 'Your task progress was updated.';
            $flashType = 'success';
        } else {
            $flashMsg  = 'Update failed: ' . mysqli_error($conn);
            $flashType = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

// -------- 6.3 Log My Contribution --------
if (isset($_POST['logContribution']) && $myGroup) {

    $title  = trim(isset($_POST['contributionTitle']) ? $_POST['contributionTitle'] : '');
    $type   = trim(isset($_POST['contributionType'])  ? $_POST['contributionType']  : '');
    $date   = trim(isset($_POST['contributionDate'])  ? $_POST['contributionDate']  : '');
    $notes  = trim(isset($_POST['contributionNotes']) ? $_POST['contributionNotes'] : '');

    if ($title === '' || $type === '' || $date === '') {
        $flashMsg  = 'Please fill in all required contribution fields.';
        $flashType = 'error';
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `contributions`
                (group_id, student_id, title, type, notes, contribution_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param(
                $stmt, "iissss",
                $activeGroupID, $studentID, $title, $type, $notes, $date
        );

        if (mysqli_stmt_execute($stmt)) {
            $projectId = (int) $myGroup['project_id'];
            $stmt2 = mysqli_prepare($conn, "
                INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
                VALUES (?, ?, ?, 'contributed', ?)
            ");
            $detail = 'logged contribution "' . $title . '"';
            mysqli_stmt_bind_param($stmt2, "iiis", $projectId, $activeGroupID, $userID, $detail);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            $flashMsg  = 'Contribution logged successfully.';
            $flashType = 'success';
        } else {
            $flashMsg  = 'Failed to log contribution: ' . mysqli_error($conn);
            $flashType = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

// =========================================================
// 7. 拎 group 資料（members / tasks / contributions / task files / progress）
// =========================================================
$groupMembers    = array();
$groupTasks      = array();
$myContributions = array();
$taskFiles       = array();   // task_id => [files]
$progress        = array('total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'pct' => 0);

if ($myGroup) {

    // -------- 7.1 Members --------
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            s.student_number,
            u.username      AS name,
            gm.role
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

    // -------- 7.2 Tasks --------
    $stmt = mysqli_prepare($conn, "
        SELECT
            t.id,
            t.title,
            t.description,
            t.status,
            t.priority,
            t.due_date,
            t.assigned_to,
            t.completed_at,
            au.username AS owner_name,
            cu.username AS creator_name
        FROM `tasks` t
        LEFT JOIN `students` s  ON s.id = t.assigned_to
        LEFT JOIN `users`    au ON au.id = s.user_id
        LEFT JOIN `users`    cu ON cu.id = t.created_by
        WHERE t.group_id = ?
        ORDER BY
            CASE t.status
                WHEN 'In Progress' THEN 1
                WHEN 'Pending'     THEN 2
                WHEN 'Completed'   THEN 3
                ELSE 4
            END,
            t.due_date
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $groupTasks[] = $row;

        $progress['total']++;
        if ($row['status'] === 'Pending')          $progress['pending']++;
        elseif ($row['status'] === 'In Progress')  $progress['in_progress']++;
        elseif ($row['status'] === 'Completed')    $progress['completed']++;
    }
    mysqli_stmt_close($stmt);

    if ($progress['total'] > 0) {
        $progress['pct'] = (int) round($progress['completed'] / $progress['total'] * 100);
    }

    // -------- 7.3 Task Files（每個 task 嘅 attached files）--------
    if (count($groupTasks) > 0) {
        $taskIds = array();
        foreach ($groupTasks as $t) {
            $taskIds[] = (int)$t['id'];
        }

        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $types = str_repeat('i', count($taskIds));

        $sql = "
            SELECT
                f.id,
                f.task_id,
                f.file_name,
                f.file_size,
                f.file_type,
                f.uploaded_at,
                f.status,
                u.username AS uploader_name
            FROM `files` f
            LEFT JOIN `students` s ON s.id = f.uploader_id
            LEFT JOIN `users`    u ON u.id = s.user_id
            WHERE f.task_id IN ($placeholders)
            ORDER BY f.uploaded_at DESC
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$taskIds);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $taskFiles[(int)$row['task_id']][] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    // -------- 7.4 My Contributions --------
    $stmt = mysqli_prepare($conn, "
        SELECT id, title, type, notes, contribution_date, created_at
        FROM `contributions`
        WHERE group_id = ? AND student_id = ?
        ORDER BY contribution_date DESC, id DESC
        LIMIT 10
    ");
    mysqli_stmt_bind_param($stmt, "ii", $activeGroupID, $studentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $myContributions[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 8. Helper
// =========================================================
if (!function_exists('statusClass')) {
    function statusClass($status) {
        switch ($status) {
            case 'Completed':   return 'status-completed';
            case 'In Progress': return 'status-progress';
            default:            return 'status-pending';
        }
    }
}

if (!function_exists('priorityClass')) {
    function priorityClass($priority) {
        switch ($priority) {
            case 'High':   return 'priority-high';
            case 'Low':    return 'priority-low';
            default:       return 'priority-medium';
        }
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | TeamBox</title>

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
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; flex-wrap: wrap;
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .role-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
        .role-badge {
            display: inline-block; padding: 6px 12px;
            border-radius: 18px; font-size: 12px; font-weight: bold;
        }
        .badge-leader { background: black; color: white; }
        .badge-teacher { background: #e5f0ff; color: #245889; }
        .badge-groupmate { background: #d9f5df; color: #176b2c; }

        .create-task-btn {
            min-width: 160px; height: 48px; padding: 0 20px;
            background: black; color: white;
            border: 2px solid black; border-radius: 9px;
            font-size: 15px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .create-task-btn:hover { background: #333; }
        .create-task-btn:active { transform: scale(0.98); }

        /* Group switcher */
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
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 14px; cursor: pointer; outline: none;
        }
        .group-switcher select:focus { border-color: black; }

        /* Empty state */
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

        /* Alert */
        .alert {
            padding: 14px 18px; margin-bottom: 20px;
            border-radius: 8px; font-size: 14px;
            border-left: 4px solid;
        }
        .alert-success { background: #eaf7ee; color: #176b2c; border-color: #2e9d4d; }
        .alert-error   { background: #fdeeee; color: #a21b1b; border-color: #d32f2f; }

        /* Summary */
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
        .summary-number { color: black; font-size: 38px; font-weight: bold; }
        .summary-description { margin-top: 7px; color: #777; font-size: 13px; }

        /* Log Contribution */
        .log-contribution-section {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            border-left: 6px solid #245889;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .log-contribution-section h2 {
            padding-bottom: 15px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }
        .log-contribution-section .section-subtitle {
            margin-top: -10px; margin-bottom: 20px;
            color: #666; font-size: 14px; line-height: 1.6;
        }
        .voluntary-badge {
            display: inline-block; padding: 5px 10px; margin-left: 8px;
            background: #e5f0ff; color: #245889; border-radius: 15px;
            font-size: 11px; font-weight: bold;
        }

        /* Form */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group { margin-bottom: 19px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            color: #333; font-size: 14px; font-weight: bold;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 0 13px;
            background: white; color: #222;
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 14px; outline: none; transition: 0.3s;
        }
        .form-group input, .form-group select { height: 47px; }
        .form-group textarea { min-height: 100px; padding-top: 12px; resize: vertical; }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { border-color: black; }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; }
        .cancel-btn {
            min-width: 130px; height: 46px; padding: 0 16px;
            background: white; color: black;
            border: 2px solid black; border-radius: 8px;
            font-size: 14px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .cancel-btn:hover { background: black; color: white; }
        .submit-btn {
            min-width: 150px; height: 46px; padding: 0 16px;
            background: black; color: white;
            border: 2px solid black; border-radius: 8px;
            font-size: 14px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .submit-btn:hover { background: #333; }

        /* Task section */
        .task-section {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .section-heading {
            padding-bottom: 18px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee;
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px;
        }
        .section-heading h2 { font-size: 24px; }

        .task-list { display: flex; flex-direction: column; gap: 18px; }
        .task-card {
            padding: 22px;
            background: #fafafa;
            border: 1px solid #dddddd; border-left: 5px solid black;
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .task-card:hover { transform: translateY(-3px); box-shadow: 0 5px 12px rgba(0, 0, 0, 0.1); }
        .task-top {
            margin-bottom: 16px;
            display: flex; justify-content: space-between;
            align-items: flex-start; gap: 20px;
        }
        .task-title { flex: 1; min-width: 0; }
        .task-title h3 { margin-bottom: 7px; font-size: 20px; }
        .task-title p { color: #666; font-size: 14px; line-height: 1.6; }
        .task-status {
            padding: 7px 12px; border-radius: 20px;
            font-size: 13px; font-weight: bold; white-space: nowrap;
        }
        .status-pending   { background: #eeeeee; color: #555; }
        .status-progress  { background: #fff1c2; color: #765900; }
        .status-completed { background: #d9f5df; color: #176b2c; }

        .task-details {
            padding-top: 16px; border-top: 1px solid #e4e4e4;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
        }
        .detail-box h4 { margin-bottom: 6px; color: #777; font-size: 13px; }
        .detail-box p { color: #222; font-size: 14px; font-weight: bold; }
        .priority {
            display: inline-block; padding: 5px 9px; border-radius: 15px;
            font-size: 12px; font-weight: bold;
        }
        .priority-high   { background: #ffe1e1; color: #a21b1b; }
        .priority-medium { background: #fff1c2; color: #765900; }
        .priority-low    { background: #e5f0ff; color: #245889; }

        /* =========================================================
           Task Files（新）
           ========================================================= */
        .task-files {
            margin-top: 18px;
            padding: 16px;
            background: white;
            border: 2px solid #eeeeee;
            border-radius: 10px;
        }
        .task-files-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .task-files-head h4 {
            font-size: 14px;
            color: #555;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .file-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: black;
            color: white;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        .upload-form { margin: 0; }
        .upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 130px;
            height: 36px;
            padding: 0 14px;
            background: white;
            color: black;
            border: 2px solid black;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .upload-btn:hover { background: black; color: white; }
        .upload-btn input[type="file"] { display: none; }

        .task-file-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .task-file-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #fafafa;
            border-radius: 8px;
            font-size: 13px;
            flex-wrap: wrap;
        }
        .file-type {
            display: inline-block;
            padding: 3px 8px;
            background: black;
            color: white;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.04em;
        }
        .file-name { flex: 1; min-width: 120px; }
        .file-link { color: #175196; font-weight: 500; text-decoration: none; }
        .file-link:hover { text-decoration: underline; }
        .file-meta { color: #8b8f98; font-size: 12px; white-space: nowrap; }

        .muted.small { font-size: 12px; color: #8b8f98; }

        /* My progress */
        .my-progress {
            margin-top: 18px; padding: 16px;
            background: white;
            border: 2px dashed #cccccc; border-radius: 10px;
        }
        .my-progress h4 { margin-bottom: 12px; font-size: 14px; color: #555; }
        .progress-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .progress-btn {
            min-width: 120px; height: 40px; padding: 0 14px;
            background: white; color: #555;
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 13px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .progress-btn:hover { border-color: black; color: black; }
        .progress-btn.active-pending   { background: #eeeeee; color: #333; border-color: #999999; }
        .progress-btn.active-progress  { background: #fff1c2; color: #765900; border-color: #e0c060; }
        .progress-btn.active-completed { background: #d9f5df; color: #176b2c; border-color: #8bc79a; }

        /* Team progress */
        .team-progress-section {
            padding: 28px; background: white;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        .team-progress-section h2 {
            padding-bottom: 15px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }
        .progress-info {
            margin-bottom: 12px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .progress-info strong { font-size: 17px; }
        .progress-info span { font-size: 17px; font-weight: bold; }
        .progress-bar {
            width: 100%; height: 18px;
            background: #dddddd; border-radius: 10px; overflow: hidden;
        }
        .progress-value { height: 100%; background: black; border-radius: 10px; }
        .progress-note { margin-top: 12px; color: #777; font-size: 14px; }

        /* Contributions list */
        .contrib-item {
            padding: 14px 0; border-bottom: 1px solid #eeeeee;
            display: flex; justify-content: space-between; gap: 16px;
        }
        .contrib-item:last-child { border-bottom: none; }
        .contrib-item h4 { margin-bottom: 4px; font-size: 15px; }
        .contrib-item p { color: #777; font-size: 13px; }
        .contrib-type {
            padding: 4px 10px; border-radius: 14px;
            font-size: 11px; font-weight: bold;
            background: #e5f0ff; color: #245889;
            white-space: nowrap;
        }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none; justify-content: center; align-items: center;
            padding: 20px; z-index: 1000; overflow-y: auto;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            width: 100%; max-width: 700px;
            background: white; border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-height: 90vh; overflow-y: auto;
            animation: modalFadeIn 0.25s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            padding: 22px 28px;
            border-bottom: 2px solid #eeeeee;
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h2 { font-size: 22px; }
        .modal-close {
            width: 38px; height: 38px;
            background: white; color: #555;
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 20px; font-weight: bold; cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            transition: 0.3s;
        }
        .modal-close:hover { background: #c62828; color: white; border-color: #c62828; }
        .modal-body { padding: 28px; }
        .modal-footer {
            padding: 20px 28px;
            border-top: 2px solid #eeeeee;
            display: flex; justify-content: flex-end; gap: 12px;
        }

        .muted { color: #777; font-size: 14px; }

        @media (max-width: 1000px) {
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
            .section-heading { align-items: flex-start; flex-direction: column; }
            .task-details { grid-template-columns: repeat(2, 1fr); }
            .form-row { grid-template-columns: 1fr; gap: 0; }
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
            .page-heading { flex-direction: column; align-items: flex-start; }
            .create-task-btn { width: 100%; }
            .summary-grid { grid-template-columns: 1fr; }
            .task-top { flex-direction: column; }
            .task-details { grid-template-columns: 1fr; }
            .form-actions, .modal-footer { flex-direction: column; }
            .cancel-btn, .submit-btn { width: 100%; }
            .progress-options { flex-direction: column; }
            .progress-btn { width: 100%; }
            .group-switcher { flex-direction: column; align-items: stretch; }
            .group-switcher select { min-width: 100%; }
            .task-files-head { flex-direction: column; align-items: flex-start; }
            .upload-btn { width: 100%; }
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
        <a href="student_task.php" class="nav-link active">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <?php if (count($myGroups) === 0): ?>

        <section class="page-heading">
            <div>
                <h1>My Tasks</h1>
                <p>Create tasks, assign them to team members, update your own progress.</p>
            </div>
        </section>

        <div class="empty-state">
            <h2>You don't have an active team yet</h2>
            <p>
                Tasks are only available after your team has been submitted
                to your supervisor. Create or manage your group first.
            </p>
            <a href="student_create_group.php" class="create-group-btn">
                Go to Create Group
            </a>
        </div>

    <?php else: ?>

    <?php $isLeader = ((int)$myGroup['leader_id'] === $studentID); ?>

        <section class="page-heading">
            <div>
                <div class="role-badges">
                    <?php if ($isLeader): ?>
                        <span class="role-badge badge-leader">Team Leader</span>
                    <?php endif; ?>
                    <span class="role-badge badge-groupmate">Groupmate</span>
                </div>
                <h1>My Tasks</h1>
                <p>
                    Create tasks, assign them to team members, update your
                    own progress, upload files and log contributions.
                </p>
            </div>

            <button type="button" class="create-task-btn" onclick="openCreateTaskModal()">
                + Create Task
            </button>
        </section>

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
                                . $g['project_name']
                                . ' / ' . $g['group_name']
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <script>
            function switchGroup(groupId) {
                window.location.href = 'student_task.php?group_id=' + groupId;
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
                <h2>Total Tasks</h2>
                <p class="summary-number"><?php echo $progress['total']; ?></p>
                <p class="summary-description">All tasks in this team</p>
            </div>
            <div class="summary-card">
                <h2>Pending Tasks</h2>
                <p class="summary-number"><?php echo $progress['pending']; ?></p>
                <p class="summary-description">Tasks waiting to be started</p>
            </div>
            <div class="summary-card">
                <h2>In Progress</h2>
                <p class="summary-number"><?php echo $progress['in_progress']; ?></p>
                <p class="summary-description">Tasks currently being completed</p>
            </div>
            <div class="summary-card">
                <h2>Completed Tasks</h2>
                <p class="summary-number"><?php echo $progress['completed']; ?></p>
                <p class="summary-description">Tasks successfully completed</p>
            </div>
        </section>

        <!-- Log My Contribution -->
        <section class="log-contribution-section">
            <h2>
                Log My Contribution
                <span class="voluntary-badge">Voluntary</span>
            </h2>

            <p class="section-subtitle">
                Did something useful for the team? Record it here. No deadline
                needed. Your entry will be counted towards your contribution score.
            </p>

            <form method="post" action="student_task.php?group_id=<?php echo $activeGroupID; ?>">
                <div class="form-group">
                    <label for="contributionTitle">What did you do?</label>
                    <input type="text" id="contributionTitle" name="contributionTitle"
                           placeholder="e.g. Fixed login bug, wrote meeting minutes, helped teammate with UI"
                           required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contributionType">Type</label>
                        <select id="contributionType" name="contributionType" required>
                            <option value="">-- Select Type --</option>
                            <option value="Development">Development</option>
                            <option value="Documentation">Documentation</option>
                            <option value="Design">Design</option>
                            <option value="Testing">Testing</option>
                            <option value="Discussion">Discussion</option>
                            <option value="Helping Teammate">Helping Teammate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contributionDate">Date</label>
                        <input type="date" id="contributionDate" name="contributionDate"
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contributionNotes">Notes (optional)</label>
                    <textarea id="contributionNotes" name="contributionNotes"
                              placeholder="Any extra details you want to add"></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="cancel-btn">Clear</button>
                    <button type="submit" name="logContribution" class="submit-btn">
                        Log Contribution
                    </button>
                </div>
            </form>

            <?php if (count($myContributions) > 0): ?>
                <div style="margin-top: 28px; padding-top: 22px; border-top: 2px solid #eeeeee;">
                    <h3 style="margin-bottom: 12px; font-size: 18px;">My Recent Contributions</h3>
                    <?php foreach ($myContributions as $c): ?>
                        <div class="contrib-item">
                            <div>
                                <h4><?php echo htmlspecialchars($c['title']); ?></h4>
                                <p>
                                    <?php echo htmlspecialchars($c['contribution_date']); ?>
                                    <?php if (!empty($c['notes'])): ?>
                                        · <?php echo htmlspecialchars($c['notes']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="contrib-type">
                                <?php echo htmlspecialchars($c['type']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Task List -->
        <section class="task-section">
            <div class="section-heading">
                <h2>Assigned Task List</h2>
            </div>

            <?php if (count($groupTasks) === 0): ?>
                <p class="muted">No tasks yet. Click <strong>+ Create Task</strong> to add one.</p>
            <?php endif; ?>

            <div class="task-list">
                <?php foreach ($groupTasks as $t): ?>
                    <?php
                    $taskId   = (int)$t['id'];
                    $isMyTask = ((int)$t['assigned_to'] === $studentID);
                    $filesForTask = isset($taskFiles[$taskId]) ? $taskFiles[$taskId] : array();
                    $fileCount = count($filesForTask);
                    ?>
                    <article class="task-card">
                        <div class="task-top">
                            <div class="task-title">
                                <h3><?php echo htmlspecialchars($t['title']); ?></h3>
                                <p><?php echo htmlspecialchars($t['description'] ?: '—'); ?></p>
                            </div>
                            <span class="task-status <?php echo statusClass($t['status']); ?>">
                                <?php echo htmlspecialchars($t['status']); ?>
                            </span>
                        </div>

                        <div class="task-details">
                            <div class="detail-box">
                                <h4>Assigned To</h4>
                                <p><?php echo htmlspecialchars($t['owner_name'] ?: '—'); ?></p>
                            </div>
                            <div class="detail-box">
                                <h4>Created By</h4>
                                <p><?php echo htmlspecialchars($t['creator_name'] ?: '—'); ?></p>
                            </div>
                            <div class="detail-box">
                                <h4>Deadline</h4>
                                <p><?php echo $t['due_date'] ? date('d M Y', strtotime($t['due_date'])) : '—'; ?></p>
                            </div>
                            <div class="detail-box">
                                <h4>Priority</h4>
                                <span class="priority <?php echo priorityClass($t['priority']); ?>">
                                    <?php echo htmlspecialchars($t['priority']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- ===== Task Files ===== -->
                        <div class="task-files">
                            <div class="task-files-head">
                                <h4>
                                    Attached Files
                                    <?php if ($fileCount > 0): ?>
                                        <span class="file-count"><?php echo $fileCount; ?></span>
                                    <?php endif; ?>
                                </h4>

                                <form method="post"
                                      action="upload_task_file.php"
                                      enctype="multipart/form-data"
                                      class="upload-form">
                                    <input type="hidden" name="taskID"  value="<?php echo $taskId; ?>">
                                    <input type="hidden" name="groupID" value="<?php echo $activeGroupID; ?>">

                                    <label class="upload-btn">
                                        + Upload File
                                        <input type="file" name="taskFile"
                                               onchange="this.form.submit()"
                                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.txt,.zip,.fig,.sql,.ipynb">
                                    </label>
                                </form>
                            </div>

                            <?php if ($fileCount === 0): ?>
                                <p class="muted small">No files attached yet.</p>
                            <?php else: ?>
                                <ul class="task-file-list">
                                    <?php foreach ($filesForTask as $f): ?>
                                        <li>
                                            <span class="file-type"><?php echo htmlspecialchars($f['file_type']); ?></span>
                                            <span class="file-name">
                                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="file-link">
                                                    <?php echo htmlspecialchars($f['file_name']); ?>
                                                </a>
                                            </span>
                                            <span class="file-meta">
                                                <?php echo htmlspecialchars($f['file_size']); ?> ·
                                                <?php echo htmlspecialchars($f['uploader_name']); ?> ·
                                                <?php echo timeAgo($f['uploaded_at']); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <?php if ($isMyTask): ?>
                            <div class="my-progress">
                                <h4>Mark My Progress</h4>
                                <form method="post"
                                      action="student_task.php?group_id=<?php echo $activeGroupID; ?>"
                                      class="progress-options">
                                    <input type="hidden" name="taskID" value="<?php echo $taskId; ?>">
                                    <input type="hidden" name="updateProgress" value="1">

                                    <button type="submit" name="status" value="Pending"
                                            class="progress-btn <?php echo $t['status']==='Pending' ? 'active-pending' : ''; ?>">
                                        Not Started
                                    </button>

                                    <button type="submit" name="status" value="In Progress"
                                            class="progress-btn <?php echo $t['status']==='In Progress' ? 'active-progress' : ''; ?>">
                                        Working On It
                                    </button>

                                    <button type="submit" name="status" value="Completed"
                                            class="progress-btn <?php echo $t['status']==='Completed' ? 'active-completed' : ''; ?>">
                                        I Completed It
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Team Progress -->
        <section class="team-progress-section">
            <h2>Team Progress</h2>

            <div class="progress-info">
                <strong>Overall Completion</strong>
                <span><?php echo $progress['pct']; ?>% (<?php echo $progress['completed']; ?> / <?php echo $progress['total']; ?> tasks)</span>
            </div>

            <div class="progress-bar">
                <div class="progress-value" style="width: <?php echo $progress['pct']; ?>%;"></div>
            </div>

            <p class="progress-note">
                The progress bar updates automatically when tasks are marked
                as completed by assigned members.
            </p>
        </section>

    <?php endif; ?>

</main>

<?php if ($myGroup): ?>
    <div class="modal-overlay" id="createTaskModal">
        <div class="modal-box">

            <div class="modal-header">
                <h2>Create New Task</h2>
                <button type="button" class="modal-close" onclick="closeCreateTaskModal()">✕</button>
            </div>

            <form method="post" action="student_task.php?group_id=<?php echo $activeGroupID; ?>">
                <div class="modal-body">

                    <div class="form-group">
                        <label for="taskTitle">Task Title</label>
                        <input type="text" id="taskTitle" name="taskTitle"
                               placeholder="e.g. Complete Project Proposal Draft" required>
                    </div>

                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea id="taskDescription" name="taskDescription"
                                  placeholder="Describe what needs to be done" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="assignedTo">Assign To</label>
                            <select id="assignedTo" name="assignedTo" required>
                                <option value="">-- Select Member --</option>
                                <option value="self">Myself (<?php echo htmlspecialchars($studentName); ?>)</option>
                                <?php foreach ($groupMembers as $m): ?>
                                    <?php if ((int)$m['student_id'] !== $studentID): ?>
                                        <option value="<?php echo (int)$m['student_id']; ?>">
                                            <?php echo htmlspecialchars($m['name']); ?>
                                            (<?php echo htmlspecialchars($m['student_number']); ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="taskDeadline">Deadline</label>
                            <input type="date" id="taskDeadline" name="taskDeadline" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskPriority">Priority</label>
                            <select id="taskPriority" name="taskPriority" required>
                                <option value="High">High</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="cancel-btn" onclick="closeCreateTaskModal()">Cancel</button>
                    <button type="submit" name="createTask" class="submit-btn">Create Task</button>
                </div>
            </form>

        </div>
    </div>
<?php endif; ?>

<script>
    function openCreateTaskModal() {
        const modal = document.getElementById('createTaskModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeCreateTaskModal() {
        const modal = document.getElementById('createTaskModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    const modalEl = document.getElementById('createTaskModal');
    if (modalEl) {
        modalEl.addEventListener('click', function (e) {
            if (e.target === this) closeCreateTaskModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeCreateTaskModal();
    });
</script>

</body>
</html>