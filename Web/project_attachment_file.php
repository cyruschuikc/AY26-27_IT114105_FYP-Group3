<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$attachmentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($attachmentId <= 0) {
    http_response_code(404);
    exit('File not found.');
}

$stmt = mysqli_prepare($conn, "
    SELECT a.file_name, a.display_name, a.stored_name,
           p.id AS project_id, p.course_id, p.visibility, p.is_draft, p.supervisor_id
    FROM `project_attachments` a
    INNER JOIN `projects` p ON p.id = a.project_id
    WHERE a.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $attachmentId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

$userID = (int) $_SESSION['userID'];
$allowed = false;

// Teacher who supervises the project
$stmt = mysqli_prepare($conn, "
    SELECT 1
    FROM `teachers`
    WHERE user_id = ? AND id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $userID, $file['supervisor_id']);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($rs)) {
    $allowed = true;
}
mysqli_stmt_close($stmt);

// Students: normally only when the project is published and visible to them
if (!$allowed && empty($file['is_draft'])) {
    if ($file['visibility'] === 'All') {
        $stmt = mysqli_prepare($conn, "
            SELECT 1
            FROM `students`
            WHERE user_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $userID);
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT 1
            FROM `students` s
            INNER JOIN `course_enrollments` ce ON ce.student_id = s.id
            WHERE s.user_id = ? AND ce.course_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $userID, $file['course_id']);
    }
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if (mysqli_fetch_assoc($rs)) {
        $allowed = true;
    }
    mysqli_stmt_close($stmt);
}

if (!$allowed) {
    http_response_code(403);
    exit('You do not have permission to download this file.');
}

$path = __DIR__ . '/uploads/project_attachments/' . basename($file['stored_name']);
if (!is_file($path)) {
    http_response_code(404);
    exit('File is missing on the server.');
}

$downloadName = $file['display_name'] ?: $file['file_name'];
$downloadName = str_replace(array('"', "\r", "\n"), '', $downloadName);

header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('X-Content-Type-Options: nosniff');

readfile($path);
exit;
