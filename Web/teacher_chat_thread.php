<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/teacher_chat_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
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
    header('Location: login.php');
    exit;
}
$teacherId = (int) $teacher['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: teacher_messages.php');
    exit;
}

$groupId = isset($_POST['group_id']) ? (int) $_POST['group_id'] : 0;
$action  = isset($_POST['action']) ? $_POST['action'] : '';
$content = trim(isset($_POST['content']) ? $_POST['content'] : '');

$back = 'teacher_messages.php' . ($groupId > 0 ? '?group_id=' . $groupId : '');

if ($groupId <= 0) {
    header('Location: ' . $back);
    exit;
}

if (!teacherChatAccess($conn, $groupId, $teacherId)) {
    http_response_code(403);
    exit('You do not have permission to post in this team.');
}

// Unpin an announcement
if ($action === 'unpin') {
    $messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;
    if ($messageId > 0) {
        $stmt = mysqli_prepare($conn, "
            UPDATE `messages`
            SET is_pinned = 0, pinned_at = NULL, pinned_by_user_id = NULL
            WHERE id = ? AND group_id = ? AND is_pinned = 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $messageId, $groupId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: ' . $back);
    exit;
}

if ($content === '') {
    header('Location: ' . $back);
    exit;
}

$length = function_exists('mb_strlen') ? mb_strlen($content) : strlen($content);
if ($length > 1000) {
    $content = function_exists('mb_substr') ? mb_substr($content, 0, 1000) : substr($content, 0, 1000);
}

$pin      = isset($_POST['pin']) ? 1 : 0;
$pinnedAt = $pin ? date('Y-m-d H:i:s') : null;
$pinnedBy = $pin ? $userID : null;

$stmt = mysqli_prepare($conn, "
    INSERT INTO `messages`
        (group_id, sender_id, sender_user_id, recipient_id, content,
         is_pinned, pinned_at, pinned_by_user_id)
    VALUES (?, NULL, ?, NULL, ?, ?, ?, ?)
");
mysqli_stmt_bind_param($stmt, "iisisi", $groupId, $userID, $content, $pin, $pinnedAt, $pinnedBy);
$ok = mysqli_stmt_execute($stmt);
$messageId = (int) mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

if ($ok && $messageId > 0) {
    teacherChatMarkRead($conn, $userID, $groupId, $messageId);
}

header('Location: ' . $back);
exit;
