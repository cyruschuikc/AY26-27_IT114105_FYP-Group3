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
$groupID = (int) (isset($_POST['groupID']) ? $_POST['groupID'] : 0);

if ($groupID === 0) {
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

// 检查是不是组成员
$stmt = mysqli_prepare($conn, "
    SELECT g.id, g.max_members, g.status,
           (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count
    FROM `groups` g
    JOIN `group_members` gm ON gm.group_id = g.id
    WHERE g.id = ? AND gm.student_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $myStudentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$grp = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$grp) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

if ($grp['status'] !== 'Recruiting') {
    header('Location: student_create_group.php?msg=error');
    exit;
}

if ((int)$grp['member_count'] < (int)$grp['max_members']) {
    header('Location: student_create_group.php?msg=notfull');
    exit;
}

// 锁定
$stmt = mysqli_prepare($conn, "
    UPDATE `groups`
    SET status = 'Voting',
        formed_at = NOW()
    WHERE id = ?
");
mysqli_stmt_bind_param($stmt, "i", $groupID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: student_create_group.php?msg=locked');
exit;