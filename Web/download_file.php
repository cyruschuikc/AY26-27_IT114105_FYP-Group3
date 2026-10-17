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

$allowed = false;

if ($studentID > 0 && !empty($file['group_id'])) {
    $stmt = mysqli_prepare($conn, "
        SELECT 1 FROM `group_members`
        WHERE group_id = ? AND student_id = ? LIMIT 1
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
        WHERE id = ? AND supervisor_id = ? LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $file['project_id'], $teacherID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($rs)) $allowed = true;
    mysqli_stmt_close($stmt);
}

if (!$allowed) {
    http_response_code(403);
    exit('You do not have permission to download this file.');
}

$realPath = '';
if (!empty($file['stored_name'])) {
    $candidate = __DIR__ . '/uploads/chat_files/' . $file['stored_name'];
    if (is_file($candidate)) $realPath = $candidate;
}

if ($realPath === '' || !is_file($realPath)) {
    http_response_code(404);
    exit('File is missing on the server.');
}

$downloadName = str_replace(array("\r", "\n"), '', $file['file_name']);

if (ob_get_level()) ob_end_clean();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($realPath));

$fp = fopen($realPath, 'rb');
if ($fp) {
    while (!feof($fp)) {
        echo fread($fp, 8192);
        flush();
    }
    fclose($fp);
}
exit;