<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

// Fallback for random_bytes() on PHP 5.x
if (!function_exists('random_bytes')) {
    function random_bytes($length) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($bytes !== false && $strong === true) return $bytes;
        }
        $bytes = '';
        for ($i = 0; $i < $length; $i++) $bytes .= chr(mt_rand(0, 255));
        return $bytes;
    }
}

if (!isset($conn) || !$conn) {
    die('Database connection failed. Please check db.php.');
}
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// Get student_id
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
$studentID = (int)$rc['id'];

$groupID = (int) (isset($_POST['groupID']) ? $_POST['groupID'] : 0);

if ($groupID === 0) {
    header('Location: student_files.php?msg=error');
    exit;
}

// Verify membership + get project_id
$stmt = mysqli_prepare($conn, "
    SELECT g.id, g.project_id
    FROM `groups` g
    INNER JOIN `group_members` gm ON gm.group_id = g.id
    WHERE g.id = ? AND gm.student_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$grp = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$grp) {
    header('Location: student_files.php?group_id=' . $groupID . '&msg=error');
    exit;
}
$projectID = (int)$grp['project_id'];

// Check uploaded file
if (!isset($_FILES['sharedFile'])) {
    header('Location: student_files.php?group_id=' . $groupID . '&msg=nofile');
    exit;
}

if ($_FILES['sharedFile']['error'] !== UPLOAD_ERR_OK) {
    if (in_array($_FILES['sharedFile']['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), true)) {
        header('Location: student_files.php?group_id=' . $groupID . '&msg=uploadfail');
        exit;
    }
    header('Location: student_files.php?group_id=' . $groupID . '&msg=nofile');
    exit;
}

$file     = $_FILES['sharedFile'];
$origName = $file['name'];
$fileSize = $file['size'];
$tmpPath  = $file['tmp_name'];

// Display name (can be renamed) and notes
$displayName = trim(isset($_POST['displayName']) ? $_POST['displayName'] : '');
if ($displayName === '') {
    $displayName = $origName;
}

$notes = trim(isset($_POST['fileNotes']) ? $_POST['fileNotes'] : '');
if ($notes === '') {
    $notes = null;
}

// Allowed extensions
$allowedExt = array(
    'pdf', 'doc', 'docx', 'ppt', 'pptx',
    'xls', 'xlsx', 'png', 'jpg', 'jpeg',
    'txt', 'zip', 'fig', 'sql', 'ipynb'
);
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    header('Location: student_files.php?group_id=' . $groupID . '&msg=badext');
    exit;
}

// Save to uploads/chat_files/
$uploadDir = __DIR__ . '/uploads/chat_files/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        header('Location: student_files.php?group_id=' . $groupID . '&msg=uploadfail');
        exit;
    }
}

$storedName = 'shared' . $groupID . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $uploadDir . $storedName;

if (!move_uploaded_file($tmpPath, $targetPath)) {
    header('Location: student_files.php?group_id=' . $groupID . '&msg=uploadfail');
    exit;
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes) {
        if ($bytes < 1024)               return $bytes . ' B';
        if ($bytes < 1024 * 1024)        return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 1) . ' MB';
        return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
    }
}

$sizeText = formatBytes($fileSize);
$fileType = strtoupper($ext);

// Insert into files (task_id = NULL = standalone file)
$stmt = mysqli_prepare($conn, "
    INSERT INTO `files`
        (project_id, group_id, task_id, uploader_id,
         file_name, display_name, notes,
         stored_name, file_size, file_type, status)
    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, 'Unviewed')
");
mysqli_stmt_bind_param(
    $stmt, "iiissssss",
    $projectID, $groupID, $studentID,
    $origName, $displayName, $notes,
    $storedName, $sizeText, $fileType
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    @unlink($targetPath);
    header('Location: student_files.php?group_id=' . $groupID . '&msg=uploadfail');
    exit;
}
mysqli_stmt_close($stmt);

// Log activity
$stmt = mysqli_prepare($conn, "
    INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
    VALUES (?, ?, ?, 'uploaded', ?)
");
$detail = 'uploaded shared file "' . $displayName . '"';
mysqli_stmt_bind_param($stmt, "iiis", $projectID, $groupID, $userID, $detail);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: student_files.php?group_id=' . $groupID . '&msg=uploaded');
exit;