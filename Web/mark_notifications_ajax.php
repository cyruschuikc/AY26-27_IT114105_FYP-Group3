<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(array('ok' => false, 'error' => 'Not logged in'));
    exit;
}

$userID = (int) $_SESSION['userID'];
$rawIds = isset($_POST['markIds']) ? $_POST['markIds'] : '';

if ($rawIds === '') {
    echo json_encode(array('ok' => false, 'error' => 'No ids'));
    exit;
}

// 過濾成整數陣列
$ids = array();
foreach (explode(',', $rawIds) as $id) {
    $id = (int) $id;
    if ($id > 0) {
        $ids[] = $id;
    }
}

if (count($ids) === 0) {
    echo json_encode(array('ok' => false, 'error' => 'Invalid ids'));
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types        = str_repeat('i', count($ids)) . 'i'; // N 個 id + userID

$params   = $ids;
$params[] = $userID;

$sql = "UPDATE `notifications`
        SET is_read = 1
        WHERE id IN ($placeholders) AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(array('ok' => false, 'error' => 'Prepare failed'));
    exit;
}
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

echo json_encode(array('ok' => true, 'marked' => count($ids), 'affected' => $affected));
exit;
