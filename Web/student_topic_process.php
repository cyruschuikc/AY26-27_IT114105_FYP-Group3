<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/topic_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$groupID = isset($_POST['groupID']) ? (int) $_POST['groupID'] : 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$student || $groupID <= 0) {
    header('Location: student_topic.php?msg=invalid');
    exit;
}
$studentID = (int) $student['id'];

mysqli_begin_transaction($conn);

$stmt = mysqli_prepare($conn, "
    SELECT
        g.id,
        g.status,
        g.leader_id,
        g.project_id,
        p.name AS project_name,
        p.topic_mode,
        p.supervisor_id,
        p.is_draft,
        t.user_id AS teacher_user_id
    FROM `groups` g
    INNER JOIN `group_members` gm ON gm.group_id = g.id AND gm.student_id = ?
    INNER JOIN `projects` p ON p.id = g.project_id
    INNER JOIN `teachers` t ON t.id = p.supervisor_id
    WHERE g.id = ?
    LIMIT 1
    FOR UPDATE
");
mysqli_stmt_bind_param($stmt, "ii", $studentID, $groupID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$group = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$group) {
    mysqli_rollback($conn);
    header('Location: student_topic.php?msg=invalid');
    exit;
}

if ((int) $group['leader_id'] !== $studentID) {
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=notleader');
    exit;
}

if ($group['status'] !== 'Executing' || (int) $group['is_draft'] === 1) {
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=invalid');
    exit;
}

if ($group['topic_mode'] === 'Fixed') {
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=invalid');
    exit;
}

$latest = latestTeamTopicProposal($conn, $groupID);
if ($latest && $latest['status'] === 'Pending') {
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=pending');
    exit;
}
if ($latest && $latest['status'] === 'Approved') {
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=approved');
    exit;
}

$topicID = null;
$topicTitle = '';
$topicDescription = '';

if ($group['topic_mode'] === 'Multi') {
    $topicID = isset($_POST['topicID']) ? (int) $_POST['topicID'] : 0;
    $stmt = mysqli_prepare($conn, "
        SELECT id, title, description
        FROM `project_topics`
        WHERE id = ? AND project_id = ? AND is_active = 1
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $topicID, $group['project_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $selectedTopic = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$selectedTopic) {
        mysqli_rollback($conn);
        header('Location: student_topic.php?group_id=' . $groupID . '&msg=invalid');
        exit;
    }
    $topicTitle = $selectedTopic['title'];
    $topicDescription = (string) $selectedTopic['description'];
} else {
    $topicTitle = trim(isset($_POST['topicTitle']) ? $_POST['topicTitle'] : '');
    $topicDescription = trim(isset($_POST['topicDescription']) ? $_POST['topicDescription'] : '');
    if ($topicTitle === '' || $topicDescription === '' || mb_strlen($topicTitle) > 180) {
        mysqli_rollback($conn);
        header('Location: student_topic.php?group_id=' . $groupID . '&msg=invalid');
        exit;
    }
}

$stmt = mysqli_prepare($conn, "
    SELECT COALESCE(MAX(version), 0) + 1 AS next_version
    FROM `team_topic_proposals`
    WHERE group_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $groupID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$versionRow = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
$nextVersion = (int) $versionRow['next_version'];

$stmt = mysqli_prepare($conn, "
    INSERT INTO `team_topic_proposals`
        (group_id, project_id, topic_id, version, topic_title, topic_description,
         status, submitted_by)
    VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)
");
mysqli_stmt_bind_param(
    $stmt,
    "iiiissi",
    $groupID,
    $group['project_id'],
    $topicID,
    $nextVersion,
    $topicTitle,
    $topicDescription,
    $studentID
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_rollback($conn);
    header('Location: student_topic.php?group_id=' . $groupID . '&msg=invalid');
    exit;
}
mysqli_stmt_close($stmt);

$notificationTitle = $group['topic_mode'] === 'Self'
    ? 'Project title awaiting approval'
    : 'Topic proposal awaiting approval';
$notificationMessage = $group['project_name'] . ' · ' . $topicTitle;
$teacherUserID = (int) $group['teacher_user_id'];
$stmt = mysqli_prepare($conn, "
    INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
    VALUES (?, 'system', ?, ?, 'teacher_review.php#topics', 0)
");
mysqli_stmt_bind_param($stmt, "iss", $teacherUserID, $notificationTitle, $notificationMessage);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

mysqli_commit($conn);
header('Location: student_topic.php?group_id=' . $groupID . '&msg=submitted');
exit;
