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
// 取表单
// =========================================================
$groupID         = (int) (isset($_POST['groupID'])        ? $_POST['groupID']        : 0);
$inviteStudentNo = trim(isset($_POST['inviteStudentNo']) ? $_POST['inviteStudentNo'] : '');

if ($groupID === 0 || $inviteStudentNo === '') {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// =========================================================
// 1. 取自己的 student_id + 名字
// =========================================================
$inviterID   = 0;
$inviterName = '';

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

$inviterID   = (int) $rc['student_id'];
$inviterName = $rc['username'];

// =========================================================
// 2. 找被邀请人（用 student_number 查）
// =========================================================
$inviteeID     = 0;
$inviteeUserID = 0;
$inviteeName   = '';

$stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, s.user_id, u.username
    FROM `students` s
    JOIN `users` u ON u.id = s.user_id
    WHERE s.student_number = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "s", $inviteStudentNo);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: student_create_group.php?msg=notfound');
    exit;
}

$inviteeID     = (int) $rc['student_id'];
$inviteeUserID = (int) $rc['user_id'];
$inviteeName   = $rc['username'];

// 不能邀请自己
if ($inviteeID === $inviterID) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// =========================================================
// 3. 检查是否已经邀请过
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
// 4. 检查组是否存在 + 取组名
// =========================================================
$groupName = '';

$stmt = mysqli_prepare($conn, "
    SELECT name FROM `groups`
    WHERE id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $groupID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: student_create_group.php?msg=error');
    exit;
}
$groupName = $rc['name'];

// =========================================================
// 5. 写邀请（invitations）
// =========================================================
$stmt = mysqli_prepare($conn, "
    INSERT INTO `invitations` (group_id, inviter_id, invitee_id, status)
    VALUES (?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($stmt, "iii", $groupID, $inviterID, $inviteeID);
mysqli_stmt_execute($stmt);
$newInviteID = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// =========================================================
// 6. 写通知（notifications）
// =========================================================
$notifyTitle   = 'Group Invitation';
$notifyMessage = $inviterName . ' invited you to join group "' . $groupName . '".';
$notifyLink    = 'accept_invite.php?id=' . $newInviteID;

$stmt = mysqli_prepare($conn, "
    INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
    VALUES (?, 'team', ?, ?, ?, 0)
");
mysqli_stmt_bind_param(
    $stmt,
    "isss",
    $inviteeUserID,
    $notifyTitle,
    $notifyMessage,
    $notifyLink
);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// =========================================================
// 7. 回页面
// =========================================================
header('Location: student_create_group.php?msg=invited');
exit;