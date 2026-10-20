<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/teacher_chat_helpers.php';
/** @var mysqli $conn */

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(array('error' => 'Not authenticated'));
    exit;
}

$userID = (int) $_SESSION['userID'];

$stmt = mysqli_prepare($conn, "
    SELECT t.id
    FROM `teachers` t
    INNER JOIN `users` u ON u.id = t.user_id
    WHERE t.user_id = ? AND u.role = 'teacher'
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$teacher) {
    http_response_code(403);
    echo json_encode(array('error' => 'Teacher account required'));
    exit;
}
$teacherId = (int) $teacher['id'];

$groupId = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
$afterId = isset($_GET['after_id']) ? (int) $_GET['after_id'] : 0;
$visible = isset($_GET['visible']) ? (int) $_GET['visible'] : 1;

if ($groupId <= 0 || !teacherChatAccess($conn, $groupId, $teacherId)) {
    http_response_code(403);
    echo json_encode(array('error' => 'No access to this group'));
    exit;
}

$palette = array('#dbeafe', '#e9d5ff', '#fce7f3', '#d1fae5', '#fef3c7');
$rows    = teacherChatMessages($conn, $groupId, $afterId, 100);
$out     = array();
$maxId   = $afterId;

foreach ($rows as $m) {
    $ts         = strtotime($m['created_at']);
    $senderName = $m['sender_name'] ?: 'Unknown';

    $roleLabel = 'Member';
    if ($m['sender_role'] === 'teacher') {
        $roleLabel = 'Teacher';
    } elseif ($m['sender_role'] === 'leader') {
        $roleLabel = 'Team Leader';
    }

    $out[] = array(
        'id'              => (int) $m['id'],
        'content'         => (string) $m['content'],
        'sender_name'     => $senderName,
        'role_label'      => $roleLabel,
        'is_me'           => ((int) $m['sender_user_id'] === $userID),
        'initials'        => teacherChatInitials($senderName),
        'avatar_bg'       => teacherChatAvatarBg($senderName, $palette),
        'time_label'      => date('H:i', $ts),
        'day'             => date('Y-m-d', $ts),
        'day_label'       => date('d M Y', $ts),
        'attachment_id'   => (int) $m['attachment_id'],
        'attachment_name' => (string) $m['attachment_name'],
    );

    if ((int) $m['id'] > $maxId) {
        $maxId = (int) $m['id'];
    }
}

if ($visible && $maxId > $afterId) {
    teacherChatMarkRead($conn, $userID, $groupId, $maxId);
}

echo json_encode(array(
    'messages' => $out,
    'last_id'  => $maxId,
));
