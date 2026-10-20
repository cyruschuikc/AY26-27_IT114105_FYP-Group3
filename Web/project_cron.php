<?php
// project_cron.php
// Run every few minutes (Windows Task Scheduler or cron):
//   C:\xampp\php\php.exe C:\xampp\htdocs\...\Web\project_cron.php
// Sends scheduled announcements and daily deadline reminders.

if (PHP_SAPI !== 'cli') {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!in_array($ip, array('127.0.0.1', '::1'), true)) {
        http_response_code(403);
        exit('Forbidden.');
    }
    header('Content-Type: text/plain; charset=UTF-8');
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/project_helpers.php';
/** @var mysqli $conn */

$sentAnnouncements = 0;
$sentNotifications = 0;

// ---------- 1. Scheduled announcements ----------
$rs = mysqli_query($conn, "
    SELECT id
    FROM `announcements`
    WHERE status = 'Scheduled'
      AND scheduled_at IS NOT NULL
      AND scheduled_at <= NOW()
    ORDER BY scheduled_at
");

if ($rs) {
    $due = array();
    while ($row = mysqli_fetch_assoc($rs)) {
        $due[] = (int) $row['id'];
    }
    mysqli_free_result($rs);

    foreach ($due as $annId) {
        $result = projectDispatchAnnouncement($conn, $annId);
        $sentNotifications += (int) $result['inapp'];
        $sentAnnouncements++;
    }
}

// ---------- 2. Deadline reminders ----------
$reminders = projectSendDeadlineReminders($conn);

echo "Scheduled announcements sent: {$sentAnnouncements}\n";
echo "In-app notifications created by announcements: {$sentNotifications}\n";
echo "Deadline reminders created: {$reminders}\n";
