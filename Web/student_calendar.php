<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($month < 1 || $month > 12) $month = (int)date('n');
if ($year < 1970 || $year > 2100) $year = (int)date('Y');

$firstTimestamp = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstTimestamp);
$firstWeekday = (int)date('w', $firstTimestamp);
$monthTitle = date('F Y', $firstTimestamp);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$todayDay = (int)date('j');
$todayMonth = (int)date('n');
$todayYear = (int)date('Y');

// Month names shown on the previous and next navigation buttons.
$prevMonthName = date('F', mktime(0, 0, 0, $prevMonth, 1, $prevYear));
$nextMonthName = date('F', mktime(0, 0, 0, $nextMonth, 1, $nextYear));

// Current student and groups for calendar events.
$userID = (int)$_SESSION['userID'];
$studentID = 0;
$studentGroups = array();
$calendarEvents = array();
$taskDeadlines = array();
$eventFlash = '';
$eventFlashType = '';

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
if ($studentRow = mysqli_fetch_assoc($rs)) $studentID = (int)$studentRow['id'];
mysqli_stmt_close($stmt);

if ($studentID > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT g.id, g.name, p.name AS project_name
        FROM `group_members` gm
        JOIN `groups` g ON g.id = gm.group_id
        JOIN `projects` p ON p.id = g.project_id
        WHERE gm.student_id = ?
        ORDER BY g.name
    ");
    mysqli_stmt_bind_param($stmt, "i", $studentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($groupRow = mysqli_fetch_assoc($rs)) $studentGroups[] = $groupRow;
    mysqli_stmt_close($stmt);
}

// Create event. Personal events are visible only to the creator.
// Group events are visible to the creator and all current members of that group.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createCalendarEvent'])) {
    $eventName = trim(isset($_POST['event_name']) ? $_POST['event_name'] : '');
    $eventDate = trim(isset($_POST['event_date']) ? $_POST['event_date'] : '');
    $eventType = isset($_POST['event_type']) ? $_POST['event_type'] : 'personal';
    $startTime = trim(isset($_POST['start_time']) ? $_POST['start_time'] : '');
    $endTime = trim(isset($_POST['end_time']) ? $_POST['end_time'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $groupID = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
    $validDate = DateTime::createFromFormat('Y-m-d', $eventDate);
    $dateOkay = $validDate && $validDate->format('Y-m-d') === $eventDate;

    if ($eventName === '' || !$dateOkay || !in_array($eventType, array('personal','group'), true)) {
        $eventFlash = 'Please enter a valid event name, date and event type.';
        $eventFlashType = 'error';
    } else {
        $allowedGroupID = 0;
        if ($eventType === 'group') {
            foreach ($studentGroups as $studentGroup) {
                if ((int)$studentGroup['id'] === $groupID) { $allowedGroupID = $groupID; break; }
            }
            if ($allowedGroupID <= 0) {
                $eventFlash = 'Please select one of your groups.';
                $eventFlashType = 'error';
            }
        }

        if ($eventFlash === '') {
            if ($eventType === 'personal') {
                $stmt = mysqli_prepare($conn, "
                    INSERT INTO `calendar_events`
                        (creator_user_id, group_id, event_type, event_name, event_date, start_time, end_time, description)
                    VALUES (?, NULL, 'personal', ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmt, "isssss", $userID, $eventName, $eventDate, $startTime, $endTime, $description);
            } else {
                $stmt = mysqli_prepare($conn, "
                    INSERT INTO `calendar_events`
                        (creator_user_id, group_id, event_type, event_name, event_date, start_time, end_time, description)
                    VALUES (?, ?, 'group', ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmt, "iisssss", $userID, $allowedGroupID, $eventName, $eventDate, $startTime, $endTime, $description);
            }
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: student_calendar.php?month=' . (int)date('n', strtotime($eventDate)) . '&year=' . (int)date('Y', strtotime($eventDate)) . '&event=created');
                exit;
            }
            $eventFlash = 'Unable to create the event.';
            $eventFlashType = 'error';
            mysqli_stmt_close($stmt);
        }
    }
}
if (isset($_GET['event']) && $_GET['event'] === 'created') {
    $eventFlash = 'Event created successfully.';
    $eventFlashType = 'success';
}

// Load personal events owned by this user and group events for the user's groups.
$monthStart = sprintf('%04d-%02d-01', $year, $month);
$monthEnd = date('Y-m-t', strtotime($monthStart));
$stmt = mysqli_prepare($conn, "
    SELECT DISTINCT ce.id, ce.event_name, ce.event_date, ce.start_time, ce.end_time, ce.event_type,
           ce.description, ce.group_id, g.name AS group_name
    FROM `calendar_events` ce
    LEFT JOIN `groups` g ON g.id = ce.group_id
    LEFT JOIN `group_members` gm
           ON gm.group_id = ce.group_id AND gm.student_id = ?
    WHERE ce.event_date BETWEEN ? AND ?
      AND (
            (ce.event_type = 'personal' AND ce.creator_user_id = ?)
         OR (ce.event_type = 'group' AND gm.student_id IS NOT NULL)
      )
    ORDER BY ce.event_date, ce.start_time, ce.event_name
");
mysqli_stmt_bind_param($stmt, "issi", $studentID, $monthStart, $monthEnd, $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($eventRow = mysqli_fetch_assoc($rs)) {
    $eventDay = (int)date('j', strtotime($eventRow['event_date']));
    if (!isset($calendarEvents[$eventDay])) $calendarEvents[$eventDay] = array();
    $calendarEvents[$eventDay][] = $eventRow;
}
mysqli_stmt_close($stmt);

// Load incomplete task deadlines assigned to the logged-in student.
$stmt = mysqli_prepare($conn, "
    SELECT t.id, t.title, t.description, t.priority,
           t.due_date, t.due_time, g.name AS group_name
    FROM `tasks` t
    JOIN `groups` g ON g.id = t.group_id
    WHERE t.assigned_to = ?
      AND t.due_date BETWEEN ? AND ?
      AND t.status <> 'Completed'
    ORDER BY t.due_date, t.due_time, t.title
");
mysqli_stmt_bind_param($stmt, "iss", $studentID, $monthStart, $monthEnd);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($taskDeadlineRow = mysqli_fetch_assoc($rs)) {
    $deadlineDay = (int)date('j', strtotime($taskDeadlineRow['due_date']));
    if (!isset($taskDeadlines[$deadlineDay])) {
        $taskDeadlines[$deadlineDay] = array();
    }
    $taskDeadlines[$deadlineDay][] = $taskDeadlineRow;
}
mysqli_stmt_close($stmt);


// Notification bell data
$unreadNotifications = 0;
$unreadList = array();

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `notifications` WHERE user_id = ? AND is_read = 0");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
$unreadNotifications = $row ? (int)$row['c'] : 0;
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
    SELECT id, title, message, created_at
    FROM `notifications`
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 8
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $unreadList[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | TeamBox</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; font-family:Arial,sans-serif; }
        body { min-height:100vh; background:#f3f5f8; color:#222; }
        .navbar {
            width:100%;
            padding:15px 30px;
            background:#000;
            display:flex;
            flex-direction:column;
            gap:15px;
            box-shadow:0 3px 12px rgba(0,0,0,.2);
        }
        .navbar-top {
            position:relative;
            width:100%;
            min-height:65px;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .logo-area {
            display:flex;
            align-items:center;
            gap:12px;
            color:#fff;
        }
        .logo-area img { width:65px; height:65px; object-fit:contain; display:block; }
        .logo-area span { font-size:30px; font-weight:bold; }
        .top-actions {
            position:absolute;
            right:0;
            top:50%;
            transform:translateY(-50%);
            display:flex;
            align-items:center;
            gap:4px;
        }
        .top-icon-link {
            position:relative;
            width:44px;
            height:44px;
            padding:7px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:50%;
            text-decoration:none;
            transition:background .2s, transform .2s;
        }
        .top-icon-link:hover { background:rgba(255,255,255,.15); transform:scale(1.04); }
        .top-icon-link img { width:29px; height:29px; object-fit:contain; display:block; }
        .calendar-current { background:rgba(255,255,255,.12); }
        .bell-badge {
            position:absolute;
            top:1px;
            right:0;
            min-width:18px;
            height:18px;
            padding:0 5px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            background:#d93025;
            border:2px solid #000;
            border-radius:9px;
            font-size:11px;
            font-weight:bold;
        }
        .bell-wrap { position:relative; }
        .bell-preview {
            position:absolute;
            top:calc(100% + 8px);
            right:0;
            z-index:300;
            width:340px;
            max-height:420px;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            color:#222;
            background:#fff;
            border:1px solid #e0e0e0;
            border-radius:10px;
            box-shadow:0 8px 24px rgba(0,0,0,.20);
            opacity:0;
            visibility:hidden;
            transform:translateY(-6px);
            transition:.18s ease;
        }
        .bell-wrap:hover .bell-preview,
        .bell-wrap:focus-within .bell-preview { opacity:1; visibility:visible; transform:translateY(0); }
        .bell-preview-header { padding:12px 14px; background:#fafbfc; border-bottom:1px solid #eee; font-size:12px; font-weight:bold; }
        .bell-preview-list { max-height:320px; overflow-y:auto; }
        .bell-preview-item { display:block; padding:10px 14px; color:#222; border-bottom:1px solid #f2f3f5; text-decoration:none; }
        .bell-preview-item:hover { background:#f7f8fa; }
        .bell-preview-title,.bell-preview-msg,.bell-preview-time { display:block; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
        .bell-preview-title { margin-bottom:3px; font-size:13px; font-weight:600; }
        .bell-preview-msg { margin-bottom:4px; color:#777; font-size:11.5px; }
        .bell-preview-time { color:#9aa0a6; font-size:10.5px; }
        .bell-preview-footer { padding:10px 14px; color:#175196; background:#fafbfc; border-top:1px solid #eee; text-align:center; text-decoration:none; font-size:12px; font-weight:bold; }

        .function-menu {
            width:100%;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-wrap:wrap;
            gap:22px;
        }
        .nav-link {
            color:#fff;
            font-size:15px;
            text-decoration:none;
            padding:6px 2px;
            transition:color .3s;
        }
        .nav-link:hover { color:#bbb; }
        .logout-link { color:#ff8888; }
        .logout-link:hover { color:#ff4444; }
        .page { width:min(94%,900px); margin:36px auto 55px; }
        .calendar-card { background:#fff; border-radius:18px; padding:28px; box-shadow:0 12px 34px rgba(15,23,42,.12); }
        .calendar-header { display:grid; grid-template-columns:160px 1fr 160px; align-items:center; gap:14px; margin-bottom:24px; }
        .calendar-header h1 { text-align:center; font-size:28px; }
        .month-link { padding:11px 14px; color:#175196; background:#fff; border:1.5px solid #175196; border-radius:9px; text-align:center; text-decoration:none; font-size:14px; font-weight:bold; }
        .month-link:hover { color:#fff; background:#175196; }
        .weekdays,.days { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:8px; }
        .weekdays { margin-bottom:8px; }
        .weekday { padding:10px 4px; color:#68707c; text-align:center; font-size:12px; font-weight:bold; text-transform:uppercase; }
        .day { min-height:88px; padding:10px; background:#fafbfc; border:1px solid #e7eaee; border-radius:10px; font-weight:bold; cursor:pointer; transition:border-color .18s, box-shadow .18s; }
        .day:not(.empty):hover { border-color:#8eb4df; box-shadow:0 5px 14px rgba(23,81,150,.10); }
        .day.empty { background:transparent; border-color:transparent; }
        .day.today { color:#fff; background:#175196; border-color:#175196; box-shadow:0 6px 16px rgba(23,81,150,.25); }
        .note { margin-top:18px; color:#777; text-align:center; font-size:13px; }
        .calendar-actions { display:flex; justify-content:flex-end; margin:0 0 18px; }
        .create-event-btn { padding:11px 17px; color:#fff; background:#175196; border:0; border-radius:9px; font-size:14px; font-weight:bold; cursor:pointer; }
        .create-event-btn:hover { background:#0f3e76; }
        .event-flash { margin-bottom:16px; padding:11px 14px; border-radius:8px; font-size:13px; }
        .event-flash.success { color:#146c2e; background:#e2f6e8; border:1px solid #b8e4c4; }
        .event-flash.error { color:#9b1c1c; background:#fdeaea; border:1px solid #f1bcbc; }
        .day-number { display:block; margin-bottom:7px; }
        .day-events { display:flex; flex-direction:column; gap:5px; }
        .calendar-event { display:flex; align-items:flex-start; gap:6px; padding:4px 5px; color:#27313b; background:#eef4fb; border-radius:5px; font-size:10.5px; font-weight:600; line-height:1.25; overflow-wrap:anywhere; }
        .calendar-event.group { background:#e9f7ef; }
        .event-dot { width:7px; height:7px; margin-top:3px; flex:0 0 7px; border-radius:50%; background:#2563eb; }
        .calendar-event.group .event-dot { background:#16824b; }
        .task-deadline{display:flex;gap:6px;padding:4px 5px;background:#fff7e6;border-radius:5px;font-size:10.5px;font-weight:600}.deadline-dot{width:7px;height:7px;margin-top:3px;flex:0 0 7px;border-radius:50%}.deadline-high .deadline-dot{background:#d93025}.deadline-medium .deadline-dot{background:#e0a030}.deadline-low .deadline-dot{background:#2e9d4d}.deadline-time{display:block;color:#64748b;font-size:9.5px;font-weight:500}
        .event-time { display:block; color:#64748b; font-size:9.5px; font-weight:500; }
        .day.today .event-time { color:#38536d; }
        .day.today .calendar-event { color:#17324f; background:rgba(255,255,255,.88); }
        .day-detail-overlay { position:fixed; inset:0; z-index:1450; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(0,0,0,.58); backdrop-filter:blur(4px); }
        .day-detail-overlay.active { display:flex; }
        .day-detail-modal { width:min(94vw,520px); max-height:86vh; overflow-y:auto; padding:24px; background:#fff; border-radius:16px; box-shadow:0 24px 70px rgba(0,0,0,.3); }
        .day-detail-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; }
        .day-detail-header h2 { font-size:21px; }
        .day-event-list { display:flex; flex-direction:column; gap:10px; }
        .day-event-detail { padding:13px; background:#f7f9fc; border-left:4px solid #2563eb; border-radius:8px; }
        .day-event-detail.group { border-left-color:#16824b; }
        .day-event-detail h3 { margin:0 0 5px; font-size:15px; }
        .day-event-detail-meta { color:#5f6b78; font-size:12px; }
        .day-event-detail p { margin-top:8px; color:#475569; font-size:13px; white-space:pre-wrap; }
        .no-events-message { padding:20px; color:#697386; background:#f7f9fc; border-radius:8px; text-align:center; }
        .event-modal-overlay { position:fixed; inset:0; z-index:1500; display:none; align-items:center; justify-content:center; padding:20px; background:rgba(0,0,0,.58); backdrop-filter:blur(4px); }
        .event-modal-overlay.active { display:flex; }
        .event-modal { width:min(94vw,520px); max-height:90vh; overflow-y:auto; padding:24px; background:#fff; border-radius:16px; box-shadow:0 24px 70px rgba(0,0,0,.3); }
        .event-modal-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
        .event-modal-header h2 { font-size:22px; }
        .event-close-btn { width:36px; height:36px; border:1px solid #d6dae0; border-radius:8px; background:#fff; cursor:pointer; font-size:18px; }
        .event-close-btn:hover { color:#fff; background:#c62828; border-color:#c62828; }
        .event-form-group { margin-bottom:16px; }
        .event-form-group label { display:block; margin-bottom:7px; color:#333; font-size:13px; font-weight:bold; }
        .event-form-group input, .event-form-group select, .event-form-group textarea { width:100%; padding:11px 12px; border:1px solid #ccd3dc; border-radius:8px; font:inherit; outline:none; }
        .event-form-group input:focus, .event-form-group select:focus, .event-form-group textarea:focus { border-color:#175196; box-shadow:0 0 0 3px rgba(23,81,150,.12); }
        .event-form-group textarea { min-height:95px; resize:vertical; }
        .event-group-field { display:none; }
        .event-group-field.active { display:block; }
        .single-group-display { padding:11px 12px; color:#333; background:#f5f7fa; border:1px solid #dce1e7; border-radius:8px; }
        .event-form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
        .event-cancel-btn, .event-submit-btn { padding:10px 16px; border-radius:8px; font-weight:bold; cursor:pointer; }
        .event-cancel-btn { color:#333; background:#fff; border:1px solid #cfd5dc; }
        .event-submit-btn { color:#fff; background:#175196; border:1px solid #175196; }
        .event-submit-btn:hover { background:#0f3e76; }
        @media(max-width:650px){
            .navbar { padding:15px; }
            .logo-area img { width:55px; height:55px; }
            .logo-area span { font-size:26px; }
            .top-actions { right:0; }
            .top-icon-link { width:38px; height:38px; padding:7px; }
            .top-icon-link img { width:24px; height:24px; }
            .bell-preview { position:fixed; top:75px; right:12px; width:calc(100vw - 24px); }
            .function-menu { flex-direction:column; gap:12px; }
            .page { margin-top:22px; }
            .calendar-card { padding:16px; }
            .calendar-header { grid-template-columns:1fr 1fr; }
            .calendar-header h1 { grid-column:1/-1; grid-row:1; font-size:23px; }
            .month-link { grid-row:2; }
            .day { min-height:54px; padding:7px; font-size:12px; }
            .weekdays,.days { gap:4px; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-top">
        <div class="logo-area">
            <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
            <span>TeamBox</span>
        </div>
        <div class="top-actions">
            <a href="student_calendar.php" class="top-icon-link calendar-current"
               title="Calendar" aria-label="Calendar">
                <img src="cal_logo.png" alt="Calendar">
            </a>
            <div class="bell-wrap">
                <a href="student_notifications.php" class="top-icon-link"
                   title="Notifications" aria-label="Notifications">
                    <img src="bell.png" alt="Notifications">
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
                    <?php endif; ?>
                </a>
                <?php if (count($unreadList) > 0): ?>
                    <div class="bell-preview">
                        <div class="bell-preview-header">
                            Unread Notifications (<?php echo count($unreadList); ?>)
                        </div>
                        <div class="bell-preview-list">
                            <?php foreach ($unreadList as $notification): ?>
                                <a href="student_notifications.php" class="bell-preview-item">
                                    <span class="bell-preview-title"><?php echo htmlspecialchars($notification['title']); ?></span>
                                    <?php if (!empty($notification['message'])): ?>
                                        <span class="bell-preview-msg"><?php echo htmlspecialchars($notification['message']); ?></span>
                                    <?php endif; ?>
                                    <span class="bell-preview-time"><?php echo date('d M Y H:i', strtotime($notification['created_at'])); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="student_notifications.php" class="bell-preview-footer">View all notifications →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>
<main class="page">
    <section class="calendar-card">
        <div class="calendar-header">
            <a class="month-link" href="student_calendar.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">← <?php echo htmlspecialchars($prevMonthName); ?></a>
            <h1><?php echo htmlspecialchars($monthTitle); ?></h1>
            <a class="month-link" href="student_calendar.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>"><?php echo htmlspecialchars($nextMonthName); ?> →</a>
        </div>
        <div class="calendar-actions">
            <button type="button" class="create-event-btn" id="openEventModal">+ Create event</button>
        </div>
        <?php if ($eventFlash !== ''): ?>
            <div class="event-flash <?php echo htmlspecialchars($eventFlashType); ?>"><?php echo htmlspecialchars($eventFlash); ?></div>
        <?php endif; ?>
        <div class="weekdays">
            <?php foreach (array('Sun','Mon','Tue','Wed','Thu','Fri','Sat') as $weekday): ?>
                <div class="weekday"><?php echo $weekday; ?></div>
            <?php endforeach; ?>
        </div>
        <div class="days">
            <?php for ($blank = 0; $blank < $firstWeekday; $blank++): ?>
                <div class="day empty" aria-hidden="true"></div>
            <?php endfor; ?>
            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php $isToday = ($day === $todayDay && $month === $todayMonth && $year === $todayYear); ?>
                <div class="day<?php echo $isToday ? ' today' : ''; ?>"
                     data-date="<?php echo sprintf('%04d-%02d-%02d', $year, $month, $day); ?>"
                     data-date-label="<?php echo htmlspecialchars(date('j F Y', mktime(0,0,0,$month,$day,$year))); ?>"
                        <?php echo $isToday ? ' aria-current="date"' : ''; ?>>
                    <span class="day-number"><?php echo $day; ?></span>
                    <?php if (isset($calendarEvents[$day])): ?>
                        <div class="day-events">
                            <?php foreach ($calendarEvents[$day] as $calendarEvent): ?>
                                <div class="calendar-event <?php echo $calendarEvent['event_type'] === 'group' ? 'group' : 'personal'; ?>"
                                     data-name="<?php echo htmlspecialchars($calendarEvent['event_name']); ?>"
                                     data-start="<?php echo htmlspecialchars(substr($calendarEvent['start_time'],0,5)); ?>"
                                     data-end="<?php echo htmlspecialchars(substr($calendarEvent['end_time'],0,5)); ?>"
                                     data-type="<?php echo htmlspecialchars($calendarEvent['event_type']); ?>"
                                     data-group="<?php echo htmlspecialchars($calendarEvent['group_name']); ?>"
                                     data-description="<?php echo htmlspecialchars($calendarEvent['description']); ?>">
                                    <span class="event-dot"></span>
                                    <span>
                                        <?php echo htmlspecialchars($calendarEvent['event_name']); ?>
                                        <span class="event-time"><?php echo htmlspecialchars(substr($calendarEvent['start_time'],0,5) . '–' . substr($calendarEvent['end_time'],0,5)); ?></span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($taskDeadlines[$day])): ?><div class="day-events"><?php foreach($taskDeadlines[$day] as $td): ?>
                        <div class="task-deadline deadline-<?php echo strtolower(htmlspecialchars($td['priority'])); ?>" data-name="<?php echo htmlspecialchars($td['title']); ?>" data-time="<?php echo htmlspecialchars(substr((string)$td['due_time'],0,5)); ?>" data-priority="<?php echo htmlspecialchars($td['priority']); ?>" data-group="<?php echo htmlspecialchars($td['group_name']); ?>" data-description="<?php echo htmlspecialchars($td['description']); ?>"><span class="deadline-dot"></span><span><?php echo htmlspecialchars($td['title']); ?><span class="deadline-time">Deadline <?php echo htmlspecialchars(substr((string)$td['due_time'],0,5)); ?></span></span></div>
                    <?php endforeach; ?></div><?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        <p class="note">Today is highlighted in blue.</p>
    </section>
</main>
<div class="day-detail-overlay" id="dayDetailModal" aria-hidden="true">
    <div class="day-detail-modal" role="dialog" aria-modal="true" aria-labelledby="dayDetailTitle">
        <div class="day-detail-header">
            <h2 id="dayDetailTitle">Day events</h2>
            <button type="button" class="event-close-btn" id="closeDayDetail" aria-label="Close">×</button>
        </div>
        <div id="dayDetailContent"></div>
    </div>
</div>
<div class="event-modal-overlay" id="eventModal" aria-hidden="true">
    <div class="event-modal" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
        <div class="event-modal-header">
            <h2 id="eventModalTitle">Create event</h2>
            <button type="button" class="event-close-btn" id="closeEventModal" aria-label="Close">×</button>
        </div>
        <form method="post" id="eventForm">
            <div class="event-form-group">
                <label for="eventName">Event name</label>
                <input type="text" id="eventName" name="event_name" maxlength="150" required>
            </div>
            <div class="event-form-group">
                <label for="eventDate">Event date</label>
                <input type="date" id="eventDate" name="event_date" required>
            </div>
            <div class="event-form-group">
                <label>Event time</label>
                <div style="display:grid;grid-template-columns:1fr 24px 1fr;gap:8px;align-items:center;">
                    <input type="time" id="eventStartTime" name="start_time" required>
                    <span style="text-align:center;">to</span>
                    <input type="time" id="eventEndTime" name="end_time" required>
                </div>
            </div>
            <div class="event-form-group">
                <label for="eventType">Event visibility</label>
                <select id="eventType" name="event_type" required>
                    <option value="personal">Personal</option>
                    <option value="group"<?php echo count($studentGroups) === 0 ? ' disabled' : ''; ?>>Group</option>
                </select>
            </div>
            <div class="event-form-group event-group-field" id="eventGroupField">
                <label>Group</label>
                <?php if (count($studentGroups) === 1): ?>
                    <div class="single-group-display"><?php echo htmlspecialchars($studentGroups[0]['name']); ?></div>
                    <input type="hidden" name="group_id" value="<?php echo (int)$studentGroups[0]['id']; ?>">
                <?php elseif (count($studentGroups) > 1): ?>
                    <select name="group_id" id="eventGroupSelect">
                        <option value="">Select a group</option>
                        <?php foreach ($studentGroups as $studentGroup): ?>
                            <option value="<?php echo (int)$studentGroup['id']; ?>">
                                <?php echo htmlspecialchars($studentGroup['name'] . ' · ' . $studentGroup['project_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div class="single-group-display">You are not currently in a group.</div>
                <?php endif; ?>
            </div>
            <div class="event-form-group">
                <label for="eventDescription">Description (optional)</label>
                <textarea id="eventDescription" name="description" maxlength="2000"></textarea>
            </div>
            <div class="event-form-actions">
                <button type="button" class="event-cancel-btn" id="cancelEventModal">Cancel</button>
                <button type="submit" name="createCalendarEvent" value="1" class="event-submit-btn">Create event</button>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var modal = document.getElementById('eventModal');
        var openBtn = document.getElementById('openEventModal');
        var closeBtn = document.getElementById('closeEventModal');
        var cancelBtn = document.getElementById('cancelEventModal');
        var eventType = document.getElementById('eventType');
        var groupField = document.getElementById('eventGroupField');
        var groupSelect = document.getElementById('eventGroupSelect');
        var eventDate = document.getElementById('eventDate');
        var startTime = document.getElementById('eventStartTime');
        var endTime = document.getElementById('eventEndTime');
        var dayModal = document.getElementById('dayDetailModal');
        var dayTitle = document.getElementById('dayDetailTitle');
        var dayContent = document.getElementById('dayDetailContent');
        var closeDay = document.getElementById('closeDayDetail');

        function updateGroupField() {
            var showGroup = eventType.value === 'group';
            groupField.classList.toggle('active', showGroup);
            if (groupSelect) groupSelect.required = showGroup;
        }
        function openModal() {
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            if (!eventDate.value) eventDate.value = '<?php echo sprintf('%04d-%02d-01', $year, $month); ?>';
            if (!startTime.value) startTime.value = '09:00';
            if (!endTime.value) endTime.value = '10:00';
            document.getElementById('eventName').focus();
        }
        function closeModal() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        eventType.addEventListener('change', updateGroupField);
        document.querySelectorAll('.day[data-date]').forEach(function (dayBox) {
            dayBox.addEventListener('click', function () {
                dayTitle.textContent = this.getAttribute('data-date-label');
                var events = this.querySelectorAll('.calendar-event');
                var deadlines = this.querySelectorAll('.task-deadline');
                dayContent.innerHTML = '';
                if (!events.length && !deadlines.length) {
                    var empty = document.createElement('div');
                    empty.className = 'no-events-message';
                    empty.textContent = 'No event or task deadline';
                    dayContent.appendChild(empty);
                } else {
                    var list = document.createElement('div');
                    list.className = 'day-event-list';
                    events.forEach(function (eventEl) {
                        var item = document.createElement('div');
                        item.className = 'day-event-detail ' + eventEl.dataset.type;
                        var heading = document.createElement('h3');
                        heading.textContent = eventEl.dataset.name;
                        var meta = document.createElement('div');
                        meta.className = 'day-event-detail-meta';
                        meta.textContent = eventEl.dataset.start + ' to ' + eventEl.dataset.end + ' · ' +
                            (eventEl.dataset.type === 'group' ? 'Group: ' + eventEl.dataset.group : 'Personal');
                        item.appendChild(heading);
                        item.appendChild(meta);
                        if (eventEl.dataset.description) {
                            var desc = document.createElement('p');
                            desc.textContent = eventEl.dataset.description;
                            item.appendChild(desc);
                        }
                        list.appendChild(item);
                    });
                    deadlines.forEach(function (deadlineEl) {
                        var item = document.createElement('div');
                        item.className = 'day-event-detail';
                        item.style.borderLeftColor = deadlineEl.dataset.priority === 'High'
                            ? '#d93025'
                            : (deadlineEl.dataset.priority === 'Medium' ? '#e0a030' : '#2e9d4d');
                        var heading = document.createElement('h3');
                        heading.textContent = deadlineEl.dataset.name;
                        var meta = document.createElement('div');
                        meta.className = 'day-event-detail-meta';
                        meta.textContent = 'Task deadline'
                            + (deadlineEl.dataset.time ? ' at ' + deadlineEl.dataset.time : '')
                            + ' · ' + deadlineEl.dataset.priority + ' priority'
                            + ' · ' + deadlineEl.dataset.group;
                        item.appendChild(heading);
                        item.appendChild(meta);
                        if (deadlineEl.dataset.description) {
                            var desc = document.createElement('p');
                            desc.textContent = deadlineEl.dataset.description;
                            item.appendChild(desc);
                        }
                        list.appendChild(item);
                    });
                    dayContent.appendChild(list);
                }
                dayModal.classList.add('active');
                dayModal.setAttribute('aria-hidden','false');
                document.body.style.overflow = 'hidden';
            });
        });
        function closeDayModal() {
            dayModal.classList.remove('active');
            dayModal.setAttribute('aria-hidden','true');
            document.body.style.overflow = '';
        }
        closeDay.addEventListener('click', closeDayModal);
        dayModal.addEventListener('click', function(e) { if (e.target === dayModal) closeDayModal(); });
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
            if (e.key === 'Escape' && dayModal.classList.contains('active')) closeDayModal(); });
        document.getElementById('eventForm').addEventListener('submit', function (e) {
            if (endTime.value <= startTime.value) {
                e.preventDefault();
                alert('End time must be after start time.');
                endTime.focus();
            }
        });
        updateGroupField();
    })();
</script>
</body>
</html>

