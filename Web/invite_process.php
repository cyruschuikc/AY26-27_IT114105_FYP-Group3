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

$groupID         = (int) (isset($_POST['groupID'])        ? $_POST['groupID']        : 0);
$inviteStudentNo = trim(isset($_POST['inviteStudentNo']) ? $_POST['inviteStudentNo'] : '');

if ($groupID === 0 || $inviteStudentNo === '') {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// =========================================================
// 取自己的 student_id
// =========================================================
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
$inviterID = (int) $rc['id'];

// =========================================================
// 找被邀请人
// =========================================================
$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE student_number = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $inviteStudentNo);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: student_create_group.php?msg=notfound');
    exit;
}
$inviteeID = (int) $rc['id'];

if ($inviteeID === $inviterID) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// =========================================================
// 检查是否已经邀请过
// =========================================================
$stmt = mysqli_prepare($conn, "
    SELECT id FROM `invitations`
    WHERE group_id = ? AND invitee_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $inviteeID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$exists = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($exists) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// =========================================================
// 写邀请
// =========================================================
$stmt = mysqli_prepare($conn, "
    INSERT INTO `invitations` (group_id, inviter_id, invitee_id, status)
    VALUES (?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($stmt, "iii", $groupID, $inviterID, $inviteeID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: student_create_group.php?msg=invited');
exit;