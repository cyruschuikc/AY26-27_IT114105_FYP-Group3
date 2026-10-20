<?php
$active = 'projects';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// =========================================================
// 0. Current teacher + extension review handler
// =========================================================
$teacherId = 0;
if (isset($_SESSION['userID'])) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['userID']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);
    if ($row) {
        $teacherId = (int) $row['id'];
    }
}

if ($teacherId <= 0) {
    header('Location: teacher_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['extensionAction'], $_POST['requestId'])) {

    $requestId = (int) $_POST['requestId'];
    $action    = $_POST['extensionAction'] === 'approve' ? 'approve' : 'reject';
    $note      = trim(isset($_POST['reviewNote']) ? $_POST['reviewNote'] : '');

    $stmt = mysqli_prepare($conn, "
        SELECT r.id, r.requested_end, s.user_id AS student_user_id, p.name AS project_name
        FROM `project_extension_requests` r
        INNER JOIN `projects` p ON p.id = r.project_id
        INNER JOIN `students` s ON s.id = r.student_id
        WHERE r.id = ? AND r.project_id = ? AND p.supervisor_id = ? AND r.status = 'Pending'
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "iii", $requestId, $projectId, $teacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $req = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if ($req) {
        $newStatus = $action === 'approve' ? 'Approved' : 'Rejected';

        $stmt = mysqli_prepare($conn, "
            UPDATE `project_extension_requests`
            SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_note = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "sisi", $newStatus, $teacherId, $note, $requestId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($action === 'approve') {
            $stmt = mysqli_prepare($conn, "
                UPDATE `projects`
                SET period_end = CASE
                    WHEN period_end IS NULL OR period_end < ? THEN ?
                    ELSE period_end
                END
                WHERE id = ?
            ");
            $newEnd = $req['requested_end'];
            mysqli_stmt_bind_param($stmt, "ssi", $newEnd, $newEnd, $projectId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $title = $action === 'approve'
            ? 'Extension request approved'
            : 'Extension request rejected';
        $message = $req['project_name'] . ': your requested deadline '
                 . date('d M Y', strtotime($req['requested_end'])) . ' was '
                 . ($action === 'approve' ? 'approved.' : 'rejected.')
                 . ($note !== '' ? ' Note: ' . $note : '');

        $uid = (int) $req['student_user_id'];
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
            VALUES (?, 'system', ?, ?, 'my_team.php', 0)
        ");
        mysqli_stmt_bind_param($stmt, "iss", $uid, $title, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: project_detail.php?id=' . $projectId . '&msg=extension');
    exit;
}


// =========================================================
// 1. Project 基本信息
// =========================================================
$project = null;

$sql = "
    SELECT
        p.id,
        p.name,
        p.category,
        p.status,
        p.progress,
        p.period_start,
        p.period_end,
        p.description,
        p.requirements,
        p.course_id,
        p.class_id,
        p.team_mode,
        p.team_deadline,
        p.allow_cross_class,
        p.default_max_members,
        p.leader_permissions,
        p.post_deadline_action,
        p.visibility,
        p.reminder_days,
        p.allow_extension,
        p.is_draft,
        c.code AS course_code,
        c.name AS course_name,
        cl.class_code,
        u.username AS supervisor_name
    FROM `Projects` p
    LEFT JOIN `Teachers` t ON t.id = p.supervisor_id
    LEFT JOIN `Users`    u ON u.id = t.user_id
    LEFT JOIN `Courses`  c ON c.id = p.course_id
    LEFT JOIN `classes`  cl ON cl.id = p.class_id
    WHERE p.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$project) {
    header('Location: teacher_projects.php');
    exit;
}

$project['supervisor'] = $project['supervisor_name'];


// =========================================================
// 2. Teams
// =========================================================
$teams = array();

$sql = "
    SELECT
        g.id,
        g.name,
        g.status,
        g.max_members,
        u.username AS leader_name,
        (SELECT COUNT(*) FROM `Group_Members` gm WHERE gm.group_id = g.id) AS member_count,
        (SELECT COUNT(*) FROM `Tasks` t
            WHERE t.group_id = g.id AND t.status = 'Completed') AS done_tasks,
        (SELECT COUNT(*) FROM `Tasks` t2
            WHERE t2.group_id = g.id) AS total_tasks
    FROM `Groups` g
    LEFT JOIN `Students` s ON s.id = g.leader_id
    LEFT JOIN `Users`    u ON u.id = s.user_id
    WHERE g.project_id = ?
    ORDER BY g.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $teams[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 3. Milestones
// =========================================================
$milestones = array();

$sql = "
    SELECT
        id,
        name,
        start_date,
        end_date,
        status,
        progress
    FROM `Milestones`
    WHERE project_id = ?
    ORDER BY start_date
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $milestones[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 4. Tasks
// =========================================================
$tasks = array();

$sql = "
    SELECT
        t.title,
        t.status,
        t.due_date,
        g.name     AS team_name,
        u.username AS owner_name
    FROM `Tasks` t
    LEFT JOIN `Groups`   g ON g.id = t.group_id
    LEFT JOIN `Students` s ON s.id = t.assigned_to
    LEFT JOIN `Users`    u ON u.id = s.user_id
    WHERE t.project_id = ?
    ORDER BY
        CASE t.status
            WHEN 'In Progress' THEN 1
            WHEN 'Pending'     THEN 2
            WHEN 'Completed'   THEN 3
            ELSE 4
        END,
        t.due_date
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $tasks[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 5. Files by Team
// =========================================================
$fileGroups = array();

foreach ($teams as $t) {

    $fileCount = 0;
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `Files` WHERE group_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $t['id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $fileCount = (int)$rc['c'];
    mysqli_stmt_close($stmt);

    if ($fileCount === 0) {
        continue;
    }

    $items = array();
    $stmt = mysqli_prepare($conn, "
        SELECT
            f.file_name,
            f.file_size,
            f.uploaded_at,
            u.username AS owner_name
        FROM `Files` f
        LEFT JOIN `Students` s ON s.id = f.uploader_id
        LEFT JOIN `Users`    u ON u.id = s.user_id
        WHERE f.group_id = ?
        ORDER BY f.uploaded_at DESC
        LIMIT 3
    ");
    mysqli_stmt_bind_param($stmt, "i", $t['id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $items[] = $row;
    }
    mysqli_stmt_close($stmt);

    $lastUpload = '—';
    if (count($items) > 0) {
        $lastUpload = $items[0]['file_name'] . ' · ' . timeAgo($items[0]['uploaded_at']);
    }

    $fileGroups[] = array(
            'team'        => $t['name'],
            'team_id'     => $t['id'],
            'file_count'  => $fileCount,
            'total_size'  => '—',
            'last_upload' => $lastUpload,
            'items'       => $items,
    );
}


// =========================================================
// 6. Activities
// =========================================================
$activities = array();

$sql = "
    SELECT
        a.detail,
        a.created_at,
        u.username AS actor_name
    FROM `Activities` a
    LEFT JOIN `Users` u ON u.id = a.actor_id
    WHERE a.project_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $activities[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 6b. Team formation progress
// =========================================================
$enrolledStudents  = 0;
$assignedStudents  = 0;
$groupCapacity     = 0;

if (!empty($project['class_id'])) {
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(DISTINCT s.id) AS c
        FROM `students` s
        WHERE s.class_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $project['class_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $enrolledStudents = (int) $rc['c'];
    mysqli_stmt_close($stmt);
}

$stmt = mysqli_prepare($conn, "
    SELECT COUNT(DISTINCT gm.student_id) AS c
    FROM `group_members` gm
    INNER JOIN `groups` g ON g.id = gm.group_id
    WHERE g.project_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$assignedStudents = (int) $rc['c'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
    SELECT COALESCE(SUM(max_members), 0) AS c
    FROM `groups`
    WHERE project_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$groupCapacity = (int) $rc['c'];
mysqli_stmt_close($stmt);

$unassignedStudents = max(0, $enrolledStudents - $assignedStudents);
$formationPercent   = $enrolledStudents > 0
    ? (int) round($assignedStudents / $enrolledStudents * 100)
    : 0;


// =========================================================
// 6c. Project attachments
// =========================================================
$attachments = array();

$stmt = mysqli_prepare($conn, "
    SELECT id, file_name, display_name, file_size, uploaded_at
    FROM `project_attachments`
    WHERE project_id = ?
    ORDER BY uploaded_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $attachments[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 6d. Extension requests
// =========================================================
$extensionRequests = array();

$stmt = mysqli_prepare($conn, "
    SELECT r.id, r.requested_end, r.reason, r.status, r.review_note, r.created_at,
           s.student_number, u.username AS student_name
    FROM `project_extension_requests` r
    INNER JOIN `students` s ON s.id = r.student_id
    INNER JOIN `users` u    ON u.id = s.user_id
    WHERE r.project_id = ?
    ORDER BY
        CASE r.status WHEN 'Pending' THEN 1 ELSE 2 END,
        r.created_at DESC
    LIMIT 10
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $extensionRequests[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 6e. Project announcements
// =========================================================
$projectAnnouncements = array();

$stmt = mysqli_prepare($conn, "
    SELECT id, title, message, channels, status, scheduled_at, sent_at, created_at
    FROM `announcements`
    WHERE target_project_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $projectAnnouncements[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 7. 甘特图计算
// =========================================================
$projStart = strtotime($project['period_start']);
$projEnd   = strtotime($project['period_end']);
$projSpan  = $projEnd - $projStart;
if ($projSpan <= 0) { $projSpan = 1; }

foreach ($milestones as &$m) {
    $ms = strtotime($m['start_date']);
    $me = strtotime($m['end_date']);
    $m['offset'] = (($ms - $projStart) / $projSpan) * 100;
    $m['width']  = (($me - $ms) / $projSpan) * 100;
    if ($m['width'] < 2) { $m['width'] = 2; }

    $today = strtotime(date('Y-m-d'));
    $m['is_late'] = ($today > $me && $m['status'] !== 'Completed');

    $m['start'] = $m['start_date'];
    $m['end']   = $m['end_date'];
}
unset($m);

$todayTs      = strtotime(date('Y-m-d'));
$todayPercent = (($todayTs - $projStart) / $projSpan) * 100;
if ($todayPercent < 0)   { $todayPercent = 0; }
if ($todayPercent > 100) { $todayPercent = 100; }
$todayInRange = ($todayTs >= $projStart && $todayTs <= $projEnd);

$monthTicks = array();
$cursor = strtotime(date('Y-m-01', $projStart));
$lastMonth = date('Y-m', $projEnd);

while (date('Y-m', $cursor) <= $lastMonth) {
    $monthStart = max($cursor, $projStart);
    $percent    = (($monthStart - $projStart) / $projSpan) * 100;

    $monthTicks[] = array(
            'label'   => date('M', $cursor),
            'year'    => date('Y', $cursor),
            'percent' => $percent,
    );

    $cursor = strtotime('+1 month', $cursor);
}

$lateCount = 0;
foreach ($milestones as $m) {
    if (!empty($m['is_late'])) { $lateCount++; }
}


// =========================================================
// 7b. Calendar month and events
// =========================================================
$calendarMonth = isset($_GET['month']) ? trim($_GET['month']) : '';

if (!preg_match('/^\d{4}-\d{2}$/', $calendarMonth)) {
    $calendarMonth = date('Y-m');
    $todayTsCal    = strtotime(date('Y-m-d'));

    if ($projStart && $todayTsCal < $projStart) {
        $calendarMonth = date('Y-m', $projStart);
    } elseif ($projEnd && $todayTsCal > $projEnd) {
        $calendarMonth = date('Y-m', $projEnd);
    }
}

$calFirstTs   = strtotime($calendarMonth . '-01');
$calDays      = (int) date('t', $calFirstTs);
$calLeading   = (int) date('N', $calFirstTs) - 1; // Monday-based grid
$calPrev      = date('Y-m', strtotime('-1 month', $calFirstTs));
$calNext      = date('Y-m', strtotime('+1 month', $calFirstTs));
$calToday     = date('Y-m-d');
$calEvents    = array();
$calEventCount = 0;

foreach ($milestones as $m) {
    if (empty($m['end'])) {
        continue;
    }
    $endTs = strtotime($m['end']);
    if (date('Y-m', $endTs) !== $calendarMonth) {
        continue;
    }

    $cls = 'pending';
    if ($m['status'] === 'Completed') {
        $cls = 'done';
    } elseif (!empty($m['is_late'])) {
        $cls = 'late';
    } elseif ($m['status'] === 'In Progress') {
        $cls = 'active';
    }

    $calEvents[(int) date('j', $endTs)][] = array(
        'label' => $m['name'],
        'class' => $cls,
        'title' => $m['name'] . ' · due ' . date('d M Y', $endTs),
    );
    $calEventCount++;
}

if (!empty($project['period_end'])) {
    $deadlineTs = strtotime($project['period_end']);
    if (date('Y-m', $deadlineTs) === $calendarMonth) {
        $calEvents[(int) date('j', $deadlineTs)][] = array(
            'label' => 'Project deadline',
            'class' => 'deadline',
            'title' => 'Project deadline: ' . date('d M Y', $deadlineTs),
        );
        $calEventCount++;
    }
}

if (!empty($project['period_start'])) {
    $startTs = strtotime($project['period_start']);
    if (date('Y-m', $startTs) === $calendarMonth) {
        $calEvents[(int) date('j', $startTs)][] = array(
            'label' => 'Project starts',
            'class' => 'start',
            'title' => 'Project starts: ' . date('d M Y', $startTs),
        );
        $calEventCount++;
    }
}

ksort($calEvents);


// =========================================================
// 8. 完成 / 总任务
// =========================================================
$doneTasks  = 0;
$totalTasks = 0;
foreach ($tasks as $t) {
    $totalTasks++;
    if ($t['status'] === 'Completed') { $doneTasks++; }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['name']); ?> | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261020d">

    <style>
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 34px;
            gap: 20px;
        }

        .page-head h1 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .page-head .meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .back {
            display: inline-block;
            font-size: 13px;
            color: #8b8f98;
            text-decoration: none;
            margin-bottom: 22px;
            cursor: pointer;
        }

        .back:hover { color: #2c2f36; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .progress-table td { white-space: normal; }

        .progress-table td:first-child {
            width: 40%;
            background: #175196;
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.03em;
        }

        .progress-table td:last-child { color: #2c2f36; }

        .task-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 14px;
        }

        .task-row:last-child { border-bottom: none; }

        .task-row .task-name { color: #2c2f36; }

        .task-row .task-owner {
            font-size: 13px;
            color: #8b8f98;
            margin-top: 3px;
        }

        .task-row .task-right {
            text-align: right;
            white-space: nowrap;
        }

        .task-row .task-due {
            font-size: 12px;
            color: #a8acb4;
            margin-top: 3px;
        }

        .activity-list {
            list-style: none;
            font-size: 14px;
        }

        .activity-list li {
            padding: 12px 16px;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
        }

        .activity-list li:last-child { border-bottom: none; }

        .file-groups {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .file-group {
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .file-group-head {
            padding: 16px 18px;
            background: #f7f8fa;
            border-bottom: 1px solid #e6e8eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .file-group-head h3 {
            font-size: 14px;
            font-weight: 600;
            color: #2c2f36;
        }

        .file-group-head .file-group-meta {
            font-size: 12px;
            color: #8b8f98;
        }

        .file-group-body { padding: 4px 0; }

        .file-group-body ul {
            list-style: none;
            font-size: 13px;
        }

        .file-group-body ul li {
            padding: 10px 18px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #f2f3f5;
        }

        .file-group-body ul li:last-child { border-bottom: none; }
        .file-group-body ul li span:first-child { color: #2c2f36; }

        .file-group-foot {
            padding: 10px 18px;
            background: #fafbfc;
            border-top: 1px solid #e6e8eb;
            font-size: 12px;
            color: #8b8f98;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .gantt-wrap { padding: 24px; }

        .gantt-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #8b8f98;
            margin-bottom: 12px;
            padding-left: 160px;
        }

        .gantt-header .gantt-start,
        .gantt-header .gantt-end {
            font-weight: 600;
            color: #6b6f78;
        }

        .gantt-months {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #e6e8eb;
            padding-bottom: 8px;
        }

        .gantt-months-label {
            width: 160px;
            flex-shrink: 0;
        }

        .gantt-months-track {
            position: relative;
            flex: 1;
            height: 18px;
        }

        .gantt-month {
            position: absolute;
            top: 0;
            transform: translateX(-50%);
            font-size: 11px;
            font-weight: 600;
            color: #6b6f78;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .gantt-month:first-child { transform: translateX(0); }

        .gantt-body { position: relative; }

        .gantt-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            min-height: 32px;
        }

        .gantt-row:last-child { margin-bottom: 0; }

        .gantt-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 13px;
            color: #2c2f36;
            padding-right: 12px;
        }

        .gantt-label .gantt-label-status {
            display: block;
            font-size: 11px;
            color: #8b8f98;
            margin-top: 2px;
        }

        .gantt-track {
            position: relative;
            flex: 1;
            height: 22px;
            background: #f2f3f5;
            border-radius: 6px;
            overflow: hidden;
        }

        .gantt-bar {
            position: absolute;
            top: 0;
            height: 100%;
            border-radius: 6px;
            display: flex;
            align-items: center;
            padding: 0 8px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            z-index: 2;
        }

        .gantt-bar.done    { background: #175196; }
        .gantt-bar.active  { background: #e0a030; color: #2c2f36; }
        .gantt-bar.pending { background: #c9ccd1; color: #2c2f36; }
        .gantt-bar.late    { background: #d32f2f; color: #fff; }

        .gantt-month-line {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #eef0f3;
            z-index: 1;
            pointer-events: none;
        }

        .gantt-today-line {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #d32f2f;
            z-index: 5;
            pointer-events: none;
        }

        .gantt-today-line::before {
            content: "";
            position: absolute;
            top: -4px;
            left: -3px;
            width: 8px;
            height: 8px;
            background: #d32f2f;
            border-radius: 50%;
        }

        .gantt-today-label {
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            background: #d32f2f;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .gantt-late-text {
            color: #d32f2f;
            font-weight: 600;
        }

        .gantt-legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #f2f3f5;
            font-size: 12px;
            color: #6b6f78;
        }

        .gantt-legend .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .gantt-legend .dot.done    { background: #175196; }
        .gantt-legend .dot.active  { background: #e0a030; }
        .gantt-legend .dot.pending { background: #c9ccd1; }
        .gantt-legend .dot.late    { background: #d32f2f; }
        .gantt-legend .dot.today   { background: #d32f2f; border-radius: 50%; }

        .review-cta {
            padding: 26px 28px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-left: 4px solid #175196;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .review-cta h3 {
            font-size: 16px;
            font-weight: 600;
            color: #2c2f36;
            margin-bottom: 6px;
        }

        .review-cta p {
            font-size: 13px;
            color: #8b8f98;
            line-height: 1.5;
            max-width: 620px;
        }

        .review-cta .cta-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        /* ---------- Project module ---------- */
        .project-flash {
            padding: 11px 15px;
            border-radius: 8px;
            background: #e8f6ee;
            border: 1px solid #bfe4cd;
            color: #256b3f;
            font-size: 12.5px;
            margin-bottom: 20px;
        }

        .page-head-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-secondary {
            display: inline-block;
            padding: 8px 13px;
            border: 1px solid #c9d8ea;
            border-radius: 7px;
            background: #fff;
            color: #175196;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-secondary:hover { background: #f4f8fd; }

        .pill-draft { background: #fdf3e2; color: #9a6a13; }
        .pill-pending { background: #fdf3e2; color: #9a6a13; }
        .pill-approved { background: #e8f6ee; color: #256b3f; }
        .pill-rejected { background: #fdecec; color: #a61b1b; }

        .settings-card { padding: 4px 0; }
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .settings-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 13px 18px;
            border-bottom: 1px solid #f2f3f5;
        }
        .settings-item:nth-child(odd) { border-right: 1px solid #f2f3f5; }
        .settings-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #8b8f98;
        }
        .settings-value { font-size: 12.5px; color: #2c2f36; line-height: 1.5; }

        .requirements-card {
            padding: 16px 18px;
            font-size: 12.5px;
            line-height: 1.7;
            color: #4b5058;
        }

        .board-card { padding: 18px; }
        .board-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .board-stat span {
            display: block;
            font-size: 11px;
            color: #8b8f98;
            margin-bottom: 4px;
        }
        .board-stat strong { font-size: 21px; font-weight: 600; color: #2c2f36; }
        .board-progress {
            height: 9px;
            border-radius: 999px;
            background: #eef0f3;
            overflow: hidden;
        }
        .board-progress div {
            height: 100%;
            border-radius: 999px;
            background: #175196;
        }
        .board-caption {
            margin-top: 9px;
            font-size: 11.5px;
            color: #6b6f78;
        }
        .board-warn { color: #b26a00; }

        .empty-note {
            padding: 24px 18px;
            text-align: center;
            font-size: 12.5px;
            color: #8b8f98;
        }

        .ext-list { padding: 2px 18px; }
        .ext-item {
            padding: 14px 0;
            border-bottom: 1px solid #f2f3f5;
        }
        .ext-item:last-child { border-bottom: none; }
        .ext-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }
        .ext-name { font-size: 13px; font-weight: 600; color: #2c2f36; }
        .ext-meta {
            margin-top: 3px;
            font-size: 11.5px;
            color: #8b8f98;
        }
        .ext-reason {
            margin-top: 7px;
            font-size: 12px;
            color: #4b5058;
            line-height: 1.55;
        }
        .ext-review-form {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .ext-review-form input[type=text] {
            flex: 1 1 200px;
            padding: 6px 8px;
            border: 1px solid #dfe2e6;
            border-radius: 6px;
            font-size: 11.5px;
        }
        .btn-approve, .btn-reject {
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .btn-approve { background: #175196; color: #fff; }
        .btn-reject { background: #fff; color: #b02a2a; border-color: #f0c0c0; }

        .announcements-card { padding: 0 18px; }
        .ann-row {
            padding: 14px 0;
            border-bottom: 1px solid #f2f3f5;
        }
        .ann-row:last-child { border-bottom: none; }
        .ann-row-head {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            font-size: 12.5px;
        }
        .ann-row-msg {
            margin-top: 6px;
            font-size: 12px;
            color: #4b5058;
            line-height: 1.6;
        }
        .ann-row-meta {
            margin-top: 6px;
            font-size: 10.5px;
            color: #9aa0a8;
        }

        .pf-chip {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            background: #eef2f7;
            color: #4b5058;
            font-size: 10px;
            font-weight: 600;
        }

        /* ---------- Calendar ---------- */
        .cal-card { overflow: hidden; }
        .cal-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-bottom: 1px solid #eef0f2;
        }
        .cal-title {
            flex: 1;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            color: #2c2f36;
        }
        .cal-nav {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e4e8;
            border-radius: 7px;
            color: #4b5058;
            font-size: 18px;
            line-height: 1;
            text-decoration: none;
            background: #fff;
        }
        .cal-nav:hover { background: #f4f8fd; color: #175196; }
        .cal-today-link {
            padding: 6px 11px;
            border: 1px solid #c9d8ea;
            border-radius: 7px;
            color: #175196;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            background: #fff;
        }
        .cal-today-link:hover { background: #f4f8fd; }
        .cal-scroll { overflow-x: auto; }
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            min-width: 640px;
        }
        .cal-head > div {
            padding: 8px 6px;
            border-bottom: 1px solid #eef0f2;
            color: #8b8f98;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-align: center;
            text-transform: uppercase;
        }
        .cal-day {
            min-height: 92px;
            padding: 6px 7px 8px;
            border-right: 1px solid #f2f3f5;
            border-bottom: 1px solid #f2f3f5;
        }
        .cal-day:nth-child(7n) { border-right: none; }
        .cal-day.weekend { background: #fcfcfd; }
        .cal-day.today { background: #f4f8fd; }
        .cal-day.cal-empty { background: #fafbfc; }
        .cal-daynum {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            color: #6b6f78;
            font-size: 11px;
            font-weight: 600;
        }
        .cal-day.today .cal-daynum {
            border-radius: 50%;
            background: #175196;
            color: #fff;
        }
        .cal-event {
            display: block;
            margin-top: 3px;
            padding: 2px 5px;
            overflow: hidden;
            border-radius: 4px;
            font-size: 9.5px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .cal-event.pending { background: #eef1f4; color: #4b5058; }
        .cal-event.active  { background: #fdf3e2; color: #9a6a13; }
        .cal-event.done    { background: #e8f0fa; color: #175196; }
        .cal-event.late    { background: #fdecec; color: #b02a2a; }
        .cal-event.deadline { background: #d32f2f; color: #fff; font-weight: 600; }
        .cal-event.start   { background: #e8f6ee; color: #256b3f; }
        .cal-empty-note {
            padding: 12px 18px;
            border-top: 1px solid #f2f3f5;
            color: #8b8f98;
            font-size: 11.5px;
            text-align: center;
        }
        .cal-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 18px;
            padding: 12px 18px;
            border-top: 1px solid #eef0f2;
            color: #6b6f78;
            font-size: 11px;
        }
        .cal-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: 5px;
            border-radius: 50%;
            vertical-align: middle;
        }
        .cal-dot.pending { background: #c9ccd1; }
        .cal-dot.active  { background: #e0a030; }
        .cal-dot.done    { background: #175196; }
        .cal-dot.late    { background: #d32f2f; }
        .cal-dot.deadline { background: #d32f2f; }
        .cal-dot.start   { background: #256b3f; }

        @media (max-width: 900px) {
            .file-groups { grid-template-columns: 1fr; }
            .gantt-header { padding-left: 0; }
            .gantt-months { display: none; }
            .gantt-month-line { display: none; }
            .gantt-today-line { display: none; }
            .gantt-row { flex-direction: column; align-items: stretch; }
            .gantt-label { width: 100%; padding: 0 0 6px 0; }
            .review-cta { flex-direction: column; align-items: flex-start; }
            .board-stats { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 800px) {
            .info-grid { grid-template-columns: 1fr; gap: 0; }
        }

        @media (max-width: 700px) {
            .page-head { flex-direction: column; gap: 12px; }
            .page-head-actions { justify-content: flex-start; }
            .settings-grid { grid-template-columns: 1fr; }
            .settings-item:nth-child(odd) { border-right: none; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <a href="teacher_projects.php" class="back">← Back to Projects</a>

        <?php
        $flash = '';
        if (isset($_SESSION['project_notice'])) {
            $flash = $_SESSION['project_notice'];
            unset($_SESSION['project_notice']);
        } elseif (isset($_GET['msg'])) {
            if ($_GET['msg'] === 'created')  { $flash = 'Project created and published.'; }
            if ($_GET['msg'] === 'updated')  { $flash = 'Project updated.'; }
            if ($_GET['msg'] === 'draft')    { $flash = 'Project saved as a draft. It is not visible to students yet.'; }
            if ($_GET['msg'] === 'extension'){ $flash = 'Extension request updated.'; }
        }
        ?>
        <?php if ($flash !== ''): ?>
            <div class="project-flash"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- 页面头部 -->
        <div class="page-head">
            <div>
                <h1><?php echo htmlspecialchars($project['name']); ?></h1>
                <div class="meta">
                    <?php echo htmlspecialchars($project['category']); ?> ·
                    Supervisor: <?php echo htmlspecialchars($project['supervisor']); ?>
                    <?php if ($lateCount > 0): ?>
                        · <span class="gantt-late-text"><?php echo $lateCount; ?> milestone<?php echo $lateCount > 1 ? 's' : ''; ?> late</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="page-head-actions">
                <?php if (!empty($project['is_draft'])): ?>
                    <span class="pill pill-draft">Draft</span>
                <?php else: ?>
                    <span class="pill"><?php echo htmlspecialchars($project['status']); ?></span>
                <?php endif; ?>
                <a href="teacher_project_create.php?id=<?= (int) $project['id'] ?>" class="btn-secondary">Edit Project</a>
                <a href="teacher_project_create.php?duplicate=<?= (int) $project['id'] ?>" class="btn-secondary">Duplicate</a>
            </div>
        </div>

        <!-- Overview -->
        <h2>Overview</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Category</th>
                    <th>Supervisor</th>
                    <th>Project Period</th>
                    <th>Teams</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($project['category']); ?></td>
                    <td><?php echo htmlspecialchars($project['supervisor']); ?></td>
                    <td>
                        <?php echo date('M Y', $projStart); ?>
                        –
                        <?php echo date('M Y', $projEnd); ?>
                    </td>
                    <td><?php echo count($teams); ?> teams</td>
                </tr>
            </table>
        </div>

        <p class="muted" style="margin: -10px 0 30px;">
            <?php echo htmlspecialchars($project['description']); ?>
        </p>

        <!-- Project settings -->
        <h2>Project Settings</h2>
        <div class="card settings-card">
            <div class="settings-grid">
                <div class="settings-item">
                    <span class="settings-label">Course</span>
                    <span class="settings-value">
                        <?= $project['course_code']
                            ? htmlspecialchars($project['course_code'] . ' — ' . $project['course_name'])
                            : '<span class="muted">—</span>' ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Class</span>
                    <span class="settings-value">
                        <?= !empty($project['class_code'])
                            ? htmlspecialchars($project['class_code'])
                            : '<span class="muted">—</span>' ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Student visibility</span>
                    <span class="settings-value"><?= $project['visibility'] === 'All' ? 'All students' : 'Students in this class' ?></span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Team formation</span>
                    <span class="settings-value">
                        <?php
                        $teamModeLabels = array(
                            'Free'             => 'Free — students form teams',
                            'Random'           => 'Random assignment',
                            'Teacher_Assigned' => 'Teacher assigns members',
                        );
                        echo htmlspecialchars($teamModeLabels[$project['team_mode']] ?? $project['team_mode']);
                        ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Team formation deadline</span>
                    <span class="settings-value">
                        <?= $project['team_deadline'] ? date('d M Y', strtotime($project['team_deadline'])) : '<span class="muted">—</span>' ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Maximum team size</span>
                    <span class="settings-value"><?= (int) $project['default_max_members'] ?> members</span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Cross-class teams</span>
                    <span class="settings-value"><?= !empty($project['allow_cross_class']) ? 'Allowed' : 'Not allowed' ?></span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">After team deadline</span>
                    <span class="settings-value">
                        <?php
                        $postLabels = array(
                            'Lock'   => 'Lock Team — requires teacher approval',
                            'Notify' => 'Notify teacher only',
                        );
                        echo htmlspecialchars($postLabels[$project['post_deadline_action']] ?? $project['post_deadline_action']);
                        ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Leader permissions</span>
                    <span class="settings-value">
                        <?php
                        $perms = json_decode((string) $project['leader_permissions'], true);
                        $permLabels = array(
                            'invite' => 'Invite', 'remove' => 'Remove', 'rename' => 'Rename',
                            'assign' => 'Assign tasks', 'submit' => 'Submit files',
                        );
                        if (is_array($perms) && count($perms) > 0) {
                            $out = array();
                            foreach ($perms as $p) {
                                $out[] = isset($permLabels[$p]) ? $permLabels[$p] : $p;
                            }
                            echo htmlspecialchars(implode(', ', $out));
                        } else {
                            echo '<span class="muted">No special permissions</span>';
                        }
                        ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Deadline reminder</span>
                    <span class="settings-value">
                        <?= (int) $project['reminder_days'] > 0
                            ? (int) $project['reminder_days'] . ' day(s) before the deadline'
                            : 'Disabled' ?>
                    </span>
                </div>
                <div class="settings-item">
                    <span class="settings-label">Extension requests</span>
                    <span class="settings-value"><?= !empty($project['allow_extension']) ? 'Allowed' : 'Not allowed' ?></span>
                </div>
            </div>
        </div>

        <?php if ($project['requirements']): ?>
            <h2>Requirements &amp; Deliverables</h2>
            <div class="card requirements-card">
                <?php echo nl2br(htmlspecialchars($project['requirements'])); ?>
            </div>
        <?php endif; ?>

        <!-- Team formation board -->
        <h2>Team Formation Progress</h2>
        <div class="card board-card">
            <div class="board-stats">
                <div class="board-stat">
                    <span>Enrolled students</span>
                    <strong><?= (int) $enrolledStudents ?></strong>
                </div>
                <div class="board-stat">
                    <span>Assigned to a team</span>
                    <strong><?= (int) $assignedStudents ?></strong>
                </div>
                <div class="board-stat">
                    <span>Not assigned yet</span>
                    <strong><?= (int) $unassignedStudents ?></strong>
                </div>
                <div class="board-stat">
                    <span>Teams / capacity</span>
                    <strong><?= count($teams) ?> / <?= (int) $groupCapacity ?></strong>
                </div>
            </div>
            <div class="board-progress" aria-label="Team formation progress">
                <div style="width: <?= (int) $formationPercent ?>%"></div>
            </div>
            <div class="board-caption">
                <strong><?= (int) $formationPercent ?>%</strong> of enrolled students have joined a team
                <?php if ($unassignedStudents > 0): ?>
                    · <span class="board-warn"><?= (int) $unassignedStudents ?> still unassigned</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($attachments) > 0): ?>
            <h2>Project Attachments</h2>
            <div class="card">
                <table>
                    <tr>
                        <th>File</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th></th>
                    </tr>
                    <?php foreach ($attachments as $att): ?>
                        <tr>
                            <td><?= htmlspecialchars($att['display_name'] ?: $att['file_name']) ?></td>
                            <td class="muted"><?= htmlspecialchars($att['file_size'] ?: '—') ?></td>
                            <td class="muted"><?= htmlspecialchars(timeAgo($att['uploaded_at'])) ?></td>
                            <td><a class="link" href="project_attachment_file.php?id=<?= (int) $att['id'] ?>">Download</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($project['allow_extension']) || count($extensionRequests) > 0): ?>
            <h2>Deadline Extension Requests</h2>
            <div class="card">
                <?php if (count($extensionRequests) === 0): ?>
                    <div class="empty-note">No extension requests yet.</div>
                <?php else: ?>
                    <div class="ext-list">
                        <?php foreach ($extensionRequests as $req): ?>
                            <div class="ext-item">
                                <div class="ext-top">
                                    <div>
                                        <div class="ext-name">
                                            <?= htmlspecialchars($req['student_name']) ?>
                                            <span class="muted"> · <?= htmlspecialchars($req['student_number']) ?></span>
                                        </div>
                                        <div class="ext-meta">
                                            Requested deadline: <strong><?= date('d M Y', strtotime($req['requested_end'])) ?></strong>
                                            · submitted <?= htmlspecialchars(timeAgo($req['created_at'])) ?>
                                        </div>
                                    </div>
                                    <span class="pill pill-<?= strtolower($req['status']) ?>"><?= htmlspecialchars($req['status']) ?></span>
                                </div>

                                <?php if (!empty($req['reason'])): ?>
                                    <div class="ext-reason"><?= htmlspecialchars($req['reason']) ?></div>
                                <?php endif; ?>

                                <?php if ($req['status'] === 'Pending'): ?>
                                    <form method="post" class="ext-review-form" action="project_detail.php?id=<?= (int) $projectId ?>">
                                        <input type="hidden" name="requestId" value="<?= (int) $req['id'] ?>">
                                        <input type="text" name="reviewNote" placeholder="Optional note to the student">
                                        <button type="submit" name="extensionAction" value="approve" class="btn-approve">Approve</button>
                                        <button type="submit" name="extensionAction" value="reject" class="btn-reject">Reject</button>
                                    </form>
                                <?php elseif (!empty($req['review_note'])): ?>
                                    <div class="ext-meta">Note: <?= htmlspecialchars($req['review_note']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (count($projectAnnouncements) > 0): ?>
            <h2>Project Announcements</h2>
            <div class="card announcements-card">
                <?php foreach ($projectAnnouncements as $ann): ?>
                    <div class="ann-row">
                        <div class="ann-row-head">
                            <strong><?= htmlspecialchars($ann['title']) ?></strong>
                            <span class="pf-chip"><?= htmlspecialchars($ann['status']) ?></span>
                            <span class="muted">
                                <?= $ann['status'] === 'Scheduled' && $ann['scheduled_at']
                                    ? 'Scheduled for ' . date('d M Y H:i', strtotime($ann['scheduled_at']))
                                    : htmlspecialchars(timeAgo($ann['sent_at'] ?: $ann['created_at'])) ?>
                            </span>
                        </div>
                        <div class="ann-row-msg"><?= nl2br(htmlspecialchars($ann['message'])) ?></div>
                        <div class="ann-row-meta">
                            Channels: <?= $ann['channels'] === 'email' ? 'Email' : ($ann['channels'] === 'email,inapp' || $ann['channels'] === 'inapp,email' ? 'In-app + Email' : 'In-app') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Review Center CTA -->
        <div class="review-cta">
            <div>
                <h3>Review Center</h3>
                <p>
                    Check files uploaded by this project's teams, approve or
                    reject them, and review group formation requests. Rejected
                    files will be sent back to the students for revision.
                </p>
            </div>
            <div class="cta-actions">
                <a href="teacher_review.php" class="btn-primary">Open Review Center</a>
            </div>
        </div>

        <!-- Teams -->
        <h2>Teams in this Project</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>Leader</th>
                    <th>Members</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th></th>
                </tr>

                <?php if (count($teams) === 0): ?>
                    <tr>
                        <td colspan="6" class="muted" style="text-align: center; padding: 30px;">
                            No teams in this project yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td><?php echo htmlspecialchars($t['leader_name'] ?: '—'); ?></td>
                        <td>
                            <?php echo (int)$t['member_count']; ?> / <?php echo (int)$t['max_members']; ?>
                        </td>
                        <td>
                            <?php
                            $done  = (int)$t['done_tasks'];
                            $total = (int)$t['total_tasks'];
                            echo $total > 0 ? ($done . ' / ' . $total) : '—';
                            ?>
                        </td>
                        <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                        <td><a href="team_detail.php?id=<?php echo (int)$t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Progress + Milestones -->
        <div class="info-grid">
            <div>
                <h2>Progress</h2>
                <div class="card">
                    <table class="progress-table">
                        <tr>
                            <td>Overall</td>
                            <td>
                                <span class="progress">
                                    <span class="bar">
                                        <span style="width: <?php echo (int)$project['progress']; ?>%"></span>
                                    </span>
                                    <?php echo (int)$project['progress']; ?>%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Teams Involved</td>
                            <td><?php echo count($teams); ?></td>
                        </tr>
                        <tr>
                            <td>Tasks Completed</td>
                            <td><?php echo $doneTasks; ?> / <?php echo $totalTasks; ?></td>
                        </tr>
                        <tr>
                            <td>Milestones Late</td>
                            <td>
                                <?php if ($lateCount > 0): ?>
                                    <span class="gantt-late-text"><?php echo $lateCount; ?></span>
                                <?php else: ?>
                                    0
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Start Date</td>
                            <td><?php echo date('d M Y', $projStart); ?></td>
                        </tr>
                        <tr>
                            <td>Deadline</td>
                            <td><?php echo date('d M Y', $projEnd); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div>
                <h2>Milestones</h2>
                <div class="card">
                    <table class="progress-table">
                        <?php if (count($milestones) === 0): ?>
                            <tr>
                                <td colspan="2" class="muted" style="text-align: center;">
                                    No milestones.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($milestones as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($m['status']); ?>
                                    <span class="muted">· <?php echo date('d M Y', strtotime($m['end'])); ?></span>
                                    <?php if ($m['is_late']): ?>
                                        · <span class="gantt-late-text">Late</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <h2>Project Calendar</h2>
        <div class="card cal-card">
            <div class="cal-toolbar">
                <a class="cal-nav" href="project_detail.php?id=<?= (int) $projectId ?>&amp;month=<?= htmlspecialchars($calPrev) ?>" title="Previous month">‹</a>
                <div class="cal-title"><?= date('F Y', $calFirstTs) ?></div>
                <a class="cal-nav" href="project_detail.php?id=<?= (int) $projectId ?>&amp;month=<?= htmlspecialchars($calNext) ?>" title="Next month">›</a>
                <a class="cal-today-link" href="project_detail.php?id=<?= (int) $projectId ?>&amp;month=<?= date('Y-m') ?>">Today</a>
            </div>

            <div class="cal-scroll">
                <div class="cal-grid cal-head">
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                    <div>Sun</div>
                </div>

                <div class="cal-grid">
                    <?php for ($i = 0; $i < $calLeading; $i++): ?>
                        <div class="cal-day cal-empty"></div>
                    <?php endfor; ?>

                    <?php for ($day = 1; $day <= $calDays; $day++): ?>
                        <?php
                        $dayDate   = sprintf('%s-%02d', $calendarMonth, $day);
                        $dayTs     = strtotime($dayDate);
                        $isToday   = ($dayDate === $calToday);
                        $isWeekend = ((int) date('N', $dayTs) >= 6);
                        ?>
                        <div class="cal-day <?= $isToday ? 'today' : '' ?> <?= $isWeekend ? 'weekend' : '' ?>">
                            <span class="cal-daynum"><?= $day ?></span>
                            <?php if (isset($calEvents[$day])): ?>
                                <?php foreach ($calEvents[$day] as $ev): ?>
                                    <span class="cal-event <?= htmlspecialchars($ev['class']) ?>"
                                          title="<?= htmlspecialchars($ev['title']) ?>">
                                        <?= htmlspecialchars($ev['label']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>

                    <?php $calTrailing = (7 - (($calLeading + $calDays) % 7)) % 7; ?>
                    <?php for ($i = 0; $i < $calTrailing; $i++): ?>
                        <div class="cal-day cal-empty"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <?php if ($calEventCount === 0): ?>
                <div class="cal-empty-note">No milestones or deadlines in this month.</div>
            <?php endif; ?>

            <div class="cal-legend">
                <span><i class="cal-dot pending"></i>Pending</span>
                <span><i class="cal-dot active"></i>In Progress</span>
                <span><i class="cal-dot done"></i>Completed</span>
                <span><i class="cal-dot late"></i>Late</span>
                <span><i class="cal-dot deadline"></i>Project deadline</span>
                <span><i class="cal-dot start"></i>Project start</span>
            </div>
        </div>

        <!-- Tasks -->
        <h2>Tasks</h2>
        <div class="card">
            <?php if (count($tasks) === 0): ?>
                <div class="muted" style="text-align: center; padding: 30px;">
                    No tasks yet.
                </div>
            <?php endif; ?>

            <?php foreach ($tasks as $t): ?>
                <div class="task-row">
                    <div>
                        <div class="task-name"><?php echo htmlspecialchars($t['title']); ?></div>
                        <div class="task-owner">
                            <?php echo htmlspecialchars($t['team_name'] ?: '—'); ?> ·
                            <?php echo htmlspecialchars($t['owner_name'] ?: '—'); ?>
                        </div>
                    </div>
                    <div class="task-right">
                        <div><?php echo htmlspecialchars($t['status']); ?></div>
                        <div class="task-due">
                            <?php echo $t['due_date'] ? date('d M Y', strtotime($t['due_date'])) : '—'; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Files by Team -->
        <h2>Files by Team</h2>

        <?php if (count($fileGroups) === 0): ?>
            <div class="card">
                <div class="muted" style="text-align: center; padding: 30px;">
                    No files uploaded yet.
                </div>
            </div>
        <?php endif; ?>

        <div class="file-groups">
            <?php foreach ($fileGroups as $g): ?>
                <div class="file-group">

                    <div class="file-group-head">
                        <h3><?php echo htmlspecialchars($g['team']); ?></h3>
                        <span class="file-group-meta">
                            <?php echo (int)$g['file_count']; ?> files
                        </span>
                    </div>

                    <div class="file-group-body">
                        <ul>
                            <?php foreach ($g['items'] as $f): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($f['file_name']); ?></span>
                                    <span class="muted">
                                        <?php echo htmlspecialchars($f['owner_name']); ?> ·
                                        <?php echo htmlspecialchars($f['file_size']); ?> ·
                                        <?php echo timeAgo($f['uploaded_at']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="file-group-foot">
                        <span>Last: <?php echo htmlspecialchars($g['last_upload']); ?></span>
                        <a href="teacher_files.php" class="link">View all</a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity -->
        <h2 style="margin-top: 30px;">Activity</h2>
        <div class="card">
            <ul class="activity-list">
                <?php if (count($activities) === 0): ?>
                    <li class="muted">No activity yet.</li>
                <?php endif; ?>

                <?php foreach ($activities as $a): ?>
                    <li>
                        <span>
                            <?php if (!empty($a['actor_name'])): ?>
                                <strong><?php echo htmlspecialchars($a['actor_name']); ?></strong>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($a['detail']); ?>
                        </span>
                        <span class="muted"><?php echo timeAgo($a['created_at']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</main>

</body>
</html>
