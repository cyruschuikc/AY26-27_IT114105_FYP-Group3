<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_team.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

$stmt = mysqli_prepare($conn, "SELECT id, username FROM `users` WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$user || !$student) {
    header('Location: logout.php');
    exit;
}
$studentID = (int) $student['id'];

$projectId    = isset($_POST['projectId']) ? (int) $_POST['projectId'] : 0;
$groupId      = isset($_POST['groupId']) ? (int) $_POST['groupId'] : 0;
$requestedEnd = trim(isset($_POST['requestedEnd']) ? $_POST['requestedEnd'] : '');
$reason       = trim(isset($_POST['reason']) ? $_POST['reason'] : '');

$back = 'my_team.php' . ($groupId > 0 ? '?group_id=' . $groupId . '&' : '?');

if ($projectId <= 0 || $requestedEnd === '') {
    header('Location: ' . $back . 'msg=extension_error');
    exit;
}

// Student must belong to a group in this project
$stmt = mysqli_prepare($conn, "
    SELECT 1
    FROM `group_members` gm
    INNER JOIN `groups` g ON g.id = gm.group_id
    WHERE gm.student_id = ? AND g.id = ? AND g.project_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "iii", $studentID, $groupId, $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$member) {
    header('Location: ' . $back . 'msg=extension_error');
    exit;
}

// Project settings
$stmt = mysqli_prepare($conn, "
    SELECT p.id, p.name, p.period_end, p.allow_extension, p.is_draft,
           t.user_id AS supervisor_user_id
    FROM `projects` p
    INNER JOIN `teachers` t ON t.id = p.supervisor_id
    WHERE p.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$project || empty($project['allow_extension']) || !empty($project['is_draft'])) {
    header('Location: ' . $back . 'msg=extension_error');
    exit;
}

if ($project['period_end'] !== null && strtotime($requestedEnd) <= strtotime($project['period_end'])) {
    header('Location: ' . $back . 'msg=extension_error');
    exit;
}

// One pending request at a time
$stmt = mysqli_prepare($conn, "
    SELECT 1
    FROM `project_extension_requests`
    WHERE project_id = ? AND student_id = ? AND status = 'Pending'
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $projectId, $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$already = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($already) {
    header('Location: ' . $back . 'msg=extension_pending');
    exit;
}

$stmt = mysqli_prepare($conn, "
    INSERT INTO `project_extension_requests` (project_id, student_id, requested_end, reason, status)
    VALUES (?, ?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($stmt, "iiss", $projectId, $studentID, $requestedEnd, $reason);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok && !empty($project['supervisor_user_id'])) {
    $title = 'Extension request: ' . $project['name'];
    $message = $user['username'] . ' requested a new deadline of '
             . date('d M Y', strtotime($requestedEnd))
             . ($reason !== '' ? '. Reason: ' . $reason : '.');
    $link = 'project_detail.php?id=' . $projectId;
    $supervisorUserId = (int) $project['supervisor_user_id'];

    $stmt = mysqli_prepare($conn, "
        INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
        VALUES (?, 'project', ?, ?, ?, 0)
    ");
    mysqli_stmt_bind_param($stmt, "isss", $supervisorUserId, $title, $message, $link);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: ' . $back . 'msg=extension_requested');
exit;
