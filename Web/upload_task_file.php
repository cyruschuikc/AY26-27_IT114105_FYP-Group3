<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

// =========================================================
// random_bytes fallback（兼容 PHP 5.x）
// =========================================================
if (!function_exists('random_bytes')) {
    function random_bytes($length) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($bytes !== false && $strong === true) {
                return $bytes;
            }
        }

        if (function_exists('mcrypt_create_iv')) {
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($bytes !== false) {
                return $bytes;
            }
        }

        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
}

if (!isset($conn) || !$conn) {
    die('Database connection not available. Check db.php.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// 取 student_id
$stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id
    FROM `students` s
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
$studentID = (int) $rc['student_id'];

// 表單資料
$taskID  = (int) (isset($_POST['taskID'])  ? $_POST['taskID']  : 0);
$groupID = (int) (isset($_POST['groupID']) ? $_POST['groupID'] : 0);

if ($taskID === 0 || $groupID === 0) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 確認 task 屬於呢個 group，而且自己係該 group 成員
$stmt = mysqli_prepare($conn, "
    SELECT t.id, t.project_id, t.group_id, t.title
    FROM `tasks` t
    INNER JOIN `group_members` gm ON gm.group_id = t.group_id
    WHERE t.id = ? AND t.group_id = ? AND gm.student_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "iii", $taskID, $groupID, $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$task) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 檔案檢查
if (!isset($_FILES['taskFile']) || $_FILES['taskFile']['error'] !== UPLOAD_ERR_OK) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=nofile');
    exit;
}

$file     = $_FILES['taskFile'];
$origName = $file['name'];
$fileSize = $file['size'];
$tmpPath  = $file['tmp_name'];

// 允許嘅 extension
$allowedExt = array(
    'pdf', 'doc', 'docx', 'ppt', 'pptx',
    'xls', 'xlsx', 'png', 'jpg', 'jpeg',
    'txt', 'zip', 'fig', 'sql', 'ipynb'
);
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=badext');
    exit;
}

// 上傳目錄
$uploadDir = __DIR__ . '/uploads/task_files/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 隨機檔名（random_bytes 已經有 fallback，兼容 PHP 5.x）
$storedName = 'task' . $taskID . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $uploadDir . $storedName;

if (!move_uploaded_file($tmpPath, $targetPath)) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=uploadfail');
    exit;
}

// 格式化 size
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

// INSERT 到 files
$stmt = mysqli_prepare($conn, "
    INSERT INTO `files`
        (project_id, group_id, task_id, uploader_id, file_name, file_size, file_type, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Unviewed')
");
$projectId = (int) $task['project_id'];

mysqli_stmt_bind_param(
    $stmt, "iiiisss",
    $projectId, $groupID, $taskID, $studentID,
    $origName, $sizeText, $fileType
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    @unlink($targetPath);
    header('Location: student_task.php?group_id=' . $groupID . '&msg=uploadfail');
    exit;
}
mysqli_stmt_close($stmt);

// 寫 activity
$stmt = mysqli_prepare($conn, "
    INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
    VALUES (?, ?, ?, 'uploaded', ?)
");
$detail = 'uploaded ' . $origName . ' to task "' . $task['title'] . '"';
mysqli_stmt_bind_param($stmt, "iiis", $projectId, $groupID, $userID, $detail);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header('Location: student_task.php?group_id=' . $groupID . '&msg=uploaded');
exit;