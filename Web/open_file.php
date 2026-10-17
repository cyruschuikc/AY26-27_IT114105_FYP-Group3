<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$fileID = (int) (isset($_GET['id']) ? $_GET['id'] : 0);

if ($fileID === 0) {
    http_response_code(400);
    exit('Invalid file ID.');
}

// 取 student_id / teacher_id
$studentID = 0;
$teacherID = 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
if ($rc) $studentID = (int)$rc['id'];

$stmt = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
if ($rc) $teacherID = (int)$rc['id'];

// 取 file
$stmt = mysqli_prepare($conn, "
    SELECT id, file_name, stored_name, file_type, project_id, group_id, uploader_id
    FROM `files` WHERE id = ? LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $fileID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

// 權限
$allowed = false;

if ($studentID > 0 && !empty($file['group_id'])) {
    $stmt = mysqli_prepare($conn, "
        SELECT 1 FROM `group_members`
        WHERE group_id = ? AND student_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $file['group_id'], $studentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($rs)) $allowed = true;
    mysqli_stmt_close($stmt);
}

if (!$allowed && $teacherID > 0 && !empty($file['project_id'])) {
    $stmt = mysqli_prepare($conn, "
        SELECT 1 FROM `projects`
        WHERE id = ? AND supervisor_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $file['project_id'], $teacherID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($rs)) $allowed = true;
    mysqli_stmt_close($stmt);
}

if (!$allowed) {
    http_response_code(403);
    exit('You do not have permission to open this file.');
}

// 搵實體檔案
$realPath = '';
if (!empty($file['stored_name'])) {
    $candidate = __DIR__ . '/uploads/chat_files/' . $file['stored_name'];
    if (is_file($candidate)) {
        $realPath = $candidate;
    }
}

if ($realPath === '' || !is_file($realPath)) {
    http_response_code(404);
    exit('File is missing on the server.');
}

// MIME
$ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

$mimeTypes = array(
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'pdf'  => 'application/pdf',
    'txt'  => 'text/plain',
);
$mime = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';

$isImg = in_array($ext, array('png', 'jpg', 'jpeg', 'gif', 'webp'));

$downloadName = str_replace(array("\r", "\n"), '', $file['file_name']);

if (ob_get_level()) ob_end_clean();

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($realPath));

if ($isImg) {
    header('Content-Disposition: inline; filename="' . $downloadName . '"');
} else {
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
}

readfile($realPath);
exit;
