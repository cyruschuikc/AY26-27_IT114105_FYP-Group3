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

$inviteID = (int) (isset($_POST['inviteID']) ? $_POST['inviteID'] : 0);

if ($inviteID === 0) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 取自己的 student_id
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
$myStudentID = (int) $rc['id'];

// 检查邀请
$stmt = mysqli_prepare($conn, "
    SELECT id, status, group_id, inviter_id
    FROM `invitations`
    WHERE id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $inviteID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$inv = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$inv) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

if ((int)$inv['inviter_id'] !== $myStudentID) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

if ($inv['status'] !== 'Pending') {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 更新状态
$stmt = mysqli_prepare($conn, "
    UPDATE `invitations`
    SET status = 'Cancelled',
        responded_at = NOW()
    WHERE id = ?
");
mysqli_stmt_bind_param($stmt, "i", $inviteID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: student_create_group.php?msg=cancel');
exit;