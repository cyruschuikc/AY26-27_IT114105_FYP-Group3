<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($conn) || !$conn) {
    die('Database connection not available.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_task.php');
    exit;
}

$userID  = (int) $_SESSION['userID'];
$fileID  = (int) (isset($_POST['fileID'])  ? $_POST['fileID']  : 0);
$groupID = (int) (isset($_POST['groupID']) ? $_POST['groupID'] : 0);
$taskID  = (int) (isset($_POST['taskID'])  ? $_POST['taskID']  : 0);

if ($fileID === 0 || $groupID === 0) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 1. 取 student_id 及 是否為 Leader
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
$studentID = (int) $rc['id'];

// 檢查是否為該組 Leader
$isLeader = false;
$stmt = mysqli_prepare($conn, "
    SELECT gm.role 
    FROM `group_members` gm 
    WHERE gm.group_id = ? AND gm.student_id = ? LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $groupID, $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rcMember = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($rcMember && $rcMember['role'] === 'leader') {
    $isLeader = true;
}

// 2. 取檔案資料 (包含 stored_name, uploader_id, task_id)
$stmt = mysqli_prepare($conn, "
    SELECT id, group_id, task_id, uploader_id, stored_name, file_name
    FROM `files`
    WHERE id = ? LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $fileID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$file) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 檢查檔案是否屬於該組、該任務
if ((int)$file['group_id'] !== $groupID || (int)$file['task_id'] !== $taskID) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 3. 權限檢查：只有上傳者本人 或 Leader 可以刪除
if ((int)$file['uploader_id'] !== $studentID && !$isLeader) {
    header('Location: student_task.php?group_id=' . $groupID . '&msg=error');
    exit;
}

// 4. 刪除實體檔案
if (!empty($file['stored_name'])) {
    $realPath = __DIR__ . '/uploads/chat_files/' . $file['stored_name'];
    if (is_file($realPath)) {
        @unlink($realPath);
    }
}

// 5. 刪除資料庫紀錄
$stmt = mysqli_prepare($conn, "DELETE FROM `files` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $fileID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 6. 寫入 Activity Log
$stmt = mysqli_prepare($conn, "
    INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
    SELECT project_id, group_id, ?, 'deleted', ?
    FROM `tasks` WHERE id = ?
");
$detail = 'deleted task file "' . $file['file_name'] . '"';
mysqli_stmt_bind_param($stmt, "isi", $userID, $detail, $taskID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 7. 導回原本頁面
header('Location: student_task.php?group_id=' . $groupID . '&msg=deleted');
exit;
