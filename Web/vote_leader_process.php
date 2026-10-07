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

$groupID     = (int) (isset($_POST['groupID'])     ? $_POST['groupID']     : 0);
$candidateID = (int) (isset($_POST['candidateID']) ? $_POST['candidateID'] : 0);

if ($groupID === 0 || $candidateID === 0) {
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

// 检查 group 状态
$stmt = mysqli_prepare($conn, "SELECT id, status FROM `groups` WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $groupID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$grp = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$grp || $grp['status'] !== 'Voting') {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 我是不是组员
$stmt = mysqli_prepare($conn, "
    SELECT 1 FROM `group_members`
    WHERE group_id = ? AND student_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $myStudentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$isMember = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$isMember) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 候选人是不是组员
$stmt = mysqli_prepare($conn, "
    SELECT 1 FROM `group_members`
    WHERE group_id = ? AND student_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $candidateID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$isCandidate = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$isCandidate) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 有没有投过
$stmt = mysqli_prepare($conn, "
    SELECT id FROM `leader_votes`
    WHERE group_id = ? AND voter_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $myStudentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$alreadyVoted = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($alreadyVoted) {
    header('Location: student_create_group.php?msg=error');
    exit;
}

// 写票
$stmt = mysqli_prepare($conn, "
    INSERT INTO `leader_votes` (group_id, voter_id, candidate_id)
    VALUES (?, ?, ?)
");
mysqli_stmt_bind_param($stmt, "iii", $groupID, $myStudentID, $candidateID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 全部投完？
$stmt = mysqli_prepare($conn, "
    SELECT
        (SELECT COUNT(*) FROM `group_members` WHERE group_id = ?) AS total_members,
        (SELECT COUNT(*) FROM `leader_votes`   WHERE group_id = ?) AS total_votes
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $groupID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$counts = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

$allVoted = ((int)$counts['total_members'] === (int)$counts['total_votes']);

if ($allVoted) {
    // 找最高票（同票用最早加入者）
    $stmt = mysqli_prepare($conn, "
        SELECT
            v.candidate_id,
            COUNT(*) AS vote_count,
            gm.joined_at
        FROM `leader_votes` v
        JOIN `group_members` gm
          ON gm.group_id = v.group_id
         AND gm.student_id = v.candidate_id
        WHERE v.group_id = ?
        GROUP BY v.candidate_id, gm.joined_at
        ORDER BY vote_count DESC, gm.joined_at ASC
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "i", $groupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $winner = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if ($winner) {
        $leaderID = (int) $winner['candidate_id'];

        // 全员 role = member
        $stmt = mysqli_prepare($conn, "
            UPDATE `group_members`
            SET role = 'member'
            WHERE group_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $groupID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // 当选者 role = leader
        $stmt = mysqli_prepare($conn, "
            UPDATE `group_members`
            SET role = 'leader'
            WHERE group_id = ? AND student_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $groupID, $leaderID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Groups 更新
        $stmt = mysqli_prepare($conn, "
            UPDATE `groups`
            SET leader_id = ?,
                status = 'Executing'
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $leaderID, $groupID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header('Location: student_create_group.php?msg=voted');
exit;