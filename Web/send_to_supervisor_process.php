<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['userID']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$userID = (int)$_SESSION['userID'];
$groupID = isset($_POST['groupID']) ? (int)$_POST['groupID'] : 0;
if ($groupID <= 0) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userID);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$student) {
    header('Location: logout.php');
    exit;
}
$studentID = (int)$student['id'];

$stmt = mysqli_prepare($conn, "
    SELECT g.id, g.name, g.project_id, g.creator_id, g.status, g.max_members,
           p.name AS project_name, p.supervisor_id, p.post_deadline_action,
           t.user_id AS teacher_user_id,
           (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count
    FROM `groups` g
    JOIN `projects` p ON p.id = g.project_id
    JOIN `teachers` t ON t.id = p.supervisor_id
    WHERE g.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $groupID);
mysqli_stmt_execute($stmt);
$group = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$redirect = 'student_create_group.php?group_id=' . $groupID . '&msg=';
if (!$group || (int)$group['creator_id'] !== $studentID) {
    header('Location: ' . $redirect . 'notcreator');
    exit;
}
if ($group['status'] !== 'Waiting_Send') {
    header('Location: ' . $redirect . 'notwaiting');
    exit;
}
if ((int)$group['member_count'] < (int)$group['max_members']) {
    header('Location: ' . $redirect . 'notfull');
    exit;
}

mysqli_begin_transaction($conn);
try {
    if ($group['post_deadline_action'] === 'Lock') {
        // Teacher approval required: keep group out of Voting until teacher accepts.
        $stmt = mysqli_prepare($conn, "
            SELECT id FROM `group_requests`
            WHERE project_id = ? AND group_name = ? AND status = 'Pending'
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'is', $group['project_id'], $group['name']);
        mysqli_stmt_execute($stmt);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if ($existing) {
            mysqli_rollback($conn);
            header('Location: ' . $redirect . 'alreadyrequested');
            exit;
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO `group_requests` (project_id, group_name, submitted_by, status, submitted_at)
            VALUES (?, ?, ?, 'Pending', NOW())
        ");
        mysqli_stmt_bind_param($stmt, 'isi', $group['project_id'], $group['name'], $studentID);
        if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
        $requestID = (int)mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "
            INSERT INTO `request_members` (request_id, student_id, is_leader)
            SELECT ?, gm.student_id, (gm.student_id = ?)
            FROM `group_members` gm WHERE gm.group_id = ?
        ");
        mysqli_stmt_bind_param($stmt, 'iii', $requestID, $studentID, $groupID);
        if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);

        $title = 'Team approval required';
        $message = $group['name'] . ' submitted a team formation request for ' . $group['project_name'] . '.';
        $link = 'teacher_review.php#requests';
        $stmt = mysqli_prepare($conn, "INSERT INTO `notifications` (user_id,type,title,message,link,is_read) VALUES (?,'system',?,?,?,0)");
        mysqli_stmt_bind_param($stmt, 'isss', $group['teacher_user_id'], $title, $message, $link);
        if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);
        header('Location: ' . $redirect . 'approvalpending');
        exit;
    }

    // Notify-only rule: no approval gate. Start voting immediately.
    $stmt = mysqli_prepare($conn, "UPDATE `groups` SET status='Voting', formed_at=COALESCE(formed_at,NOW()) WHERE id=? AND status='Waiting_Send'");
    mysqli_stmt_bind_param($stmt, 'i', $groupID);
    if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    $teacherTitle = 'Team formed — voting started';
    $teacherMessage = $group['name'] . ' was formed for ' . $group['project_name'] . '. Leader voting is now open.';
    $teacherLink = 'teacher_review.php#requests';
    $stmt = mysqli_prepare($conn, "INSERT INTO `notifications` (user_id,type,title,message,link,is_read) VALUES (?,'system',?,?,?,0)");
    mysqli_stmt_bind_param($stmt, 'isss', $group['teacher_user_id'], $teacherTitle, $teacherMessage, $teacherLink);
    if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    $memberTitle = 'Group leader voting is open';
    $memberMessage = 'Your team is complete. Vote for a group leader within 48 hours.';
    $stmt = mysqli_prepare($conn, "
        INSERT INTO `notifications` (user_id,type,title,message,link,is_read)
        SELECT s.user_id, 'system', ?, ?, CONCAT('student_create_group.php?group_id=', ?), 0
        FROM `group_members` gm JOIN `students` s ON s.id=gm.student_id
        WHERE gm.group_id=?
    ");
    mysqli_stmt_bind_param($stmt, 'ssii', $memberTitle, $memberMessage, $groupID, $groupID);
    if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);
    header('Location: ' . $redirect . 'sent');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($conn);
    header('Location: ' . $redirect . 'error');
    exit;
}
