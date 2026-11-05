<?php
$active = 'projects';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/topic_helpers.php';
/** @var mysqli $conn */

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$lateOnly  = isset($_GET['late']) && $_GET['late'] === '1';

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
        p.topic_mode,
        p.assessment_scope,
        p.assessment_weight,
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
        (SELECT GROUP_CONCAT(cl2.class_code ORDER BY cl2.class_code SEPARATOR ', ')
            FROM `project_cross_classes` pcc
            INNER JOIN `classes` cl2 ON cl2.id = pcc.class_id
            WHERE pcc.project_id = p.id) AS cross_class_codes,
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
$projectTopics = activeProjectTopics($conn, $projectId);
foreach ($projectTopics as &$topicOption) {
    $topicOption['selection_summary'] = null;
    if ($project['topic_mode'] === 'Multi') {
        $topicOption['selection_summary'] = topicSelectionSummary(
                $conn,
                $projectId,
                (int) $topicOption['id']
        );
    }
}
unset($topicOption);


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
            WHERE t2.group_id = g.id) AS total_tasks,
        (SELECT COUNT(*) FROM `Tasks` t3
            WHERE t3.group_id = g.id
              AND t3.status <> 'Completed'
              AND t3.due_date < CURDATE()) AS overdue_tasks,
        (SELECT tp.topic_title
            FROM `team_topic_proposals` tp
            WHERE tp.group_id = g.id
            ORDER BY tp.version DESC, tp.id DESC
            LIMIT 1) AS topic_title,
        (SELECT tp.status
            FROM `team_topic_proposals` tp
            WHERE tp.group_id = g.id
            ORDER BY tp.version DESC, tp.id DESC
            LIMIT 1) AS topic_status
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
    $row['overdue_tasks'] = (int) $row['overdue_tasks'];
    $teams[] = $row;
}
mysqli_stmt_close($stmt);

$lateTeams = array();
foreach ($teams as $teamRow) {
    if ((int) $teamRow['overdue_tasks'] > 0) {
        $lateTeams[] = $teamRow;
    }
}
$lateTeamCount = count($lateTeams);
$displayTeams  = $lateOnly ? $lateTeams : $teams;


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
        (t.due_date IS NOT NULL
            AND t.status <> 'Completed'
            AND t.due_date < CURDATE()) AS is_overdue,
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
    LIMIT 5
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
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/project_detail.css?v=<?= filemtime('assets/css/project_detail.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">

</head>
<body class="sg-teacher">

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
                    <?= !empty($project['is_draft']) ? 'Draft' : htmlspecialchars($project['status']) ?> ·
                    <?php echo htmlspecialchars($project['category']); ?> ·
                    <?= htmlspecialchars(topicModeLabel($project['topic_mode'])) ?> ·
                    <?= !empty($project['class_code']) ? htmlspecialchars($project['class_code']) . ' · ' : '' ?>
                    Supervisor: <?php echo htmlspecialchars($project['supervisor']); ?>
                    · <?php echo date('d M Y', $projStart); ?> – <?php echo date('d M Y', $projEnd); ?>
                    <?php if ($lateCount > 0): ?>
                        · <span class="gantt-late-text"><?php echo $lateCount; ?> milestone<?php echo $lateCount > 1 ? 's' : ''; ?> late</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="page-head-actions">
                <a href="teacher_review.php" class="btn-secondary">Review Center</a>
                <a href="teacher_project_create.php?id=<?= (int) $project['id'] ?>" class="btn-secondary">Edit Project</a>
                <a href="teacher_project_create.php?duplicate=<?= (int) $project['id'] ?>" class="btn-secondary">Duplicate</a>
            </div>
        </div>

        <?php if (!empty(trim((string) $project['description']))): ?>
            <p class="project-description"><?= htmlspecialchars($project['description']) ?></p>
        <?php endif; ?>

        <div class="status-strip" aria-label="Project status summary">
            <div class="status-metric">
                <span>Project progress</span>
                <div class="progress-summary">
                    <div class="progress-ring"
                         style="background: conic-gradient(var(--ink) 0 <?= (int) $project['progress'] ?>%, rgba(20, 20, 25, .06) <?= (int) $project['progress'] ?>% 100%);">
                        <strong><?= (int) $project['progress'] ?>%</strong>
                    </div>
                    <span class="progress-caption">Overall<br>completion</span>
                </div>
            </div>
            <div class="status-metric">
                <span>Teams</span>
                <strong><?= count($teams) ?></strong>
                <small>Total <?= (int) $assignedStudents ?> students</small>
            </div>
            <?php if ($lateTeamCount > 0): ?>
                <a class="status-metric status-link is-warning"
                   href="project_detail.php?id=<?= (int) $projectId ?>&amp;late=1#teams"
                   title="Open teams with overdue work">
                    <span>Late teams</span>
                    <strong><?= (int) $lateTeamCount ?></strong>
                    <small>Open groups with overdue work →</small>
                </a>
            <?php else: ?>
                <div class="status-metric">
                    <span>Late teams</span>
                    <strong>0</strong>
                    <small>All work is on track</small>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($project['requirements']): ?>
            <h2>Requirements &amp; Deliverables</h2>
            <div class="card requirements-card">
                <?php echo nl2br(htmlspecialchars($project['requirements'])); ?>
            </div>
        <?php endif; ?>

        <div class="section-head">
            <h2>Topic Allocation</h2>
            <span class="section-note"><?= htmlspecialchars(topicModeLabel($project['topic_mode'])) ?></span>
        </div>
        <div class="topic-allocation-card">
            <?php if ($project['topic_mode'] === 'Self'): ?>
                <div class="topic-self-note">
                    No topics are pre-defined. Each team leader submits a versioned topic proposal for teacher approval.
                </div>
            <?php elseif (count($projectTopics) === 0): ?>
                <div class="topic-self-note">No topic options have been configured for this project.</div>
            <?php else: ?>
                <div class="topic-allocation-head">
                    <strong><?= $project['topic_mode'] === 'Fixed' ? 'Teacher-defined topic' : count($projectTopics) . ' topic options' ?></strong>
                    <span><?= $project['topic_mode'] === 'Fixed' ? 'Assigned automatically' : 'Teams may share the same option' ?></span>
                </div>
                <?php foreach ($projectTopics as $topicOption): ?>
                    <div class="topic-option-row">
                        <div>
                            <strong><?= htmlspecialchars($topicOption['title']) ?></strong>
                            <?php if (!empty($topicOption['description'])): ?>
                                <small><?= htmlspecialchars($topicOption['description']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php if ($topicOption['selection_summary']): ?>
                            <div class="topic-option-counts">
                                <?= (int) $topicOption['selection_summary']['teams'] ?> teams ·
                                <?= (int) $topicOption['selection_summary']['students'] ?> students
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

        <?php if (count($extensionRequests) > 0): ?>
            <h2>Extension Requests</h2>
            <div class="card">
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
                        <div class="ann-row-meta">In-app announcement</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-head" id="teams">
            <h2><?= $lateOnly ? 'Late Teams' : 'Teams' ?></h2>
            <span class="section-note">
                <?php if ($lateOnly): ?>
                    <?= count($displayTeams) ?> team<?= count($displayTeams) === 1 ? '' : 's' ?> with overdue work
                    · <a href="project_detail.php?id=<?= (int) $projectId ?>#teams" class="link">Show all teams</a>
                <?php else: ?>
                    <?= count($teams) ?> teams · <?= (int) $assignedStudents ?> students
                <?php endif; ?>
            </span>
        </div>
        <?php if (count($displayTeams) === 0): ?>
            <div class="card">
                <div class="empty-note">
                    <?= $lateOnly ? 'No teams with overdue work.' : 'No teams in this project yet.' ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="teams-grid">
            <?php foreach ($displayTeams as $t): ?>
                <?php
                $done = (int) $t['done_tasks'];
                $total = (int) $t['total_tasks'];
                $overdue = (int) $t['overdue_tasks'];
                $teamProgress = $total > 0 ? (int) round($done / $total * 100) : 0;
                $teamNumber = trim(preg_replace('/\D+/', '', (string) $t['name']));
                $teamNumber = $teamNumber !== '' ? str_pad(substr($teamNumber, -2), 2, '0', STR_PAD_LEFT) : 'T';
                $teamStatusLabels = array(
                        'Waiting_Send' => 'Ready to Submit',
                        'Voting'       => 'Choosing Leader',
                        'Executing'    => 'Active',
                        'Completed'    => 'Completed',
                );
                // ★ 修正 1：?? → isset() ? :
                $displayTeamStatus = isset($teamStatusLabels[$t['status']])
                        ? $teamStatusLabels[$t['status']]
                        : $t['status'];
                $teamTopicTitle = $project['topic_mode'] === 'Fixed'
                        ? (count($projectTopics) > 0 ? $projectTopics[0]['title'] : '')
                        : (string) $t['topic_title'];
                ?>
                <a class="team-card <?= $overdue > 0 ? 'is-late' : '' ?>"
                   href="team_detail.php?id=<?= (int) $t['id'] ?>">
                    <div class="team-card-head">
                        <span class="team-avatar"><?= htmlspecialchars($teamNumber) ?></span>
                        <span class="team-card-title">
                            <strong><?= htmlspecialchars($t['name']) ?></strong>
                            <small>Leader · <?= htmlspecialchars($t['leader_name'] ?: 'Not assigned') ?></small>
                            <small class="team-card-topic">
                                <?= $project['topic_mode'] === 'Self' ? 'Project title' : 'Topic' ?>
                                · <?= htmlspecialchars($teamTopicTitle ?: 'Not submitted') ?>
                                <?php if ($project['topic_mode'] !== 'Fixed' && $t['topic_status']): ?>
                                    · <?= htmlspecialchars(topicProposalStatusLabel($t['topic_status'])) ?>
                                <?php endif; ?>
                            </small>
                        </span>
                        <span class="team-card-status"><?= htmlspecialchars($displayTeamStatus) ?></span>
                    </div>
                    <div class="team-card-metrics">
                        <div class="team-card-metric">
                            <span>Members</span>
                            <strong><?= (int) $t['member_count'] ?> / <?= (int) $t['max_members'] ?></strong>
                        </div>
                        <div class="team-card-metric">
                            <span>Tasks</span>
                            <strong><?= $total > 0 ? $done . ' / ' . $total : '—' ?></strong>
                        </div>
                        <div class="team-card-metric <?= $overdue > 0 ? 'is-danger' : '' ?>">
                            <span>Overdue</span>
                            <strong><?= $overdue ?></strong>
                        </div>
                    </div>
                    <div class="team-card-progress" aria-label="Task progress <?= $teamProgress ?>%">
                        <span style="width: <?= $teamProgress ?>%"></span>
                    </div>
                    <div class="team-card-foot">
                        <span><?= $teamProgress ?>% task completion</span>
                        <span class="team-card-open">Open team →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="section-head">
            <h2>Milestones</h2>
            <span class="section-note"><?= date('d M Y', $projStart) ?> – <?= date('d M Y', $projEnd) ?></span>
        </div>
        <div class="card">
            <?php if (count($milestones) === 0): ?>
                <div class="empty-note">No milestones yet.</div>
            <?php endif; ?>
            <div class="milestone-timeline">
                <?php foreach ($milestones as $m): ?>
                    <?php
                    $milestoneEnd = strtotime($m['end']);
                    $daysToEnd = (int) floor(($milestoneEnd - strtotime(date('Y-m-d'))) / 86400);
                    $isComplete = $m['status'] === 'Completed';
                    if (!empty($m['is_late'])) {
                        $ageLabel = 'Overdue ' . max(1, abs($daysToEnd)) . 'd';
                    } elseif ($isComplete) {
                        $ageLabel = 'Complete';
                    } elseif ($daysToEnd === 0) {
                        $ageLabel = 'Due today';
                    } else {
                        $ageLabel = 'Due in ' . max(0, $daysToEnd) . 'd';
                    }
                    ?>
                    <div class="milestone-item <?= !empty($m['is_late']) ? 'is-late' : ($isComplete ? 'is-complete' : '') ?>">
                        <span class="milestone-marker">
                            <?= !empty($m['is_late']) ? '!' : ($isComplete ? '✓' : '•') ?>
                        </span>
                        <div class="milestone-copy">
                            <strong><?= htmlspecialchars($m['name']) ?></strong>
                            <span>
                                <?= date('d M', strtotime($m['start'])) ?>
                                → <?= date('d M Y', $milestoneEnd) ?>
                            </span>
                        </div>
                        <div class="milestone-side">
                            <?php if (!empty($m['is_late'])): ?>
                                <span class="pill pill-rejected">Late</span>
                            <?php elseif ($isComplete): ?>
                                <span class="pill pill-approved">Completed</span>
                            <?php else: ?>
                                <span class="pill pill-<?= $m['status'] === 'In Progress' ? 'pending' : 'draft' ?>">
                                    <?= htmlspecialchars($m['status']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="milestone-age"><?= htmlspecialchars($ageLabel) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        $activeTasks = array();
        foreach ($tasks as $taskItem) {
            if ($taskItem['status'] !== 'Completed') {
                $activeTasks[] = $taskItem;
            }
        }
        ?>
        <div class="section-head">
            <h2>Active Tasks</h2>
            <span class="section-note"><?= (int) $doneTasks ?> completed tasks hidden</span>
        </div>
        <div class="card">
            <?php if (count($activeTasks) === 0): ?>
                <div class="empty-note">No active tasks. All tasks are completed.</div>
            <?php endif; ?>
            <?php foreach ($activeTasks as $t): ?>
                <?php
                $taskIsLate = !empty($t['is_overdue']);
                $teamNumber = trim(preg_replace('/\D+/', '', (string) $t['team_name']));
                $teamNumber = $teamNumber !== '' ? str_pad(substr($teamNumber, -2), 2, '0', STR_PAD_LEFT) : 'T';
                ?>
                <div class="task-row <?= $taskIsLate ? 'is-late' : '' ?>">
                    <span class="task-marker"><?= htmlspecialchars($teamNumber) ?></span>
                    <div class="task-main">
                        <div class="task-name"><?= htmlspecialchars($t['title']) ?></div>
                        <div class="task-owner">
                            <?= htmlspecialchars($t['team_name'] ?: '—') ?> ·
                            <?= htmlspecialchars($t['owner_name'] ?: '—') ?>
                        </div>
                    </div>
                    <div class="task-right">
                        <span class="task-status"><?= htmlspecialchars($t['status']) ?></span>
                        <div class="task-due">
                            <?= $t['due_date'] ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                            <?= $taskIsLate ? ' · Late' : '' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($fileGroups) > 0): ?>
            <h2>Team Files</h2>
            <div class="file-groups">
                <?php foreach ($fileGroups as $g): ?>
                    <div class="file-group">
                        <div class="file-group-head">
                            <h3><?= htmlspecialchars($g['team']) ?></h3>
                            <span class="file-group-meta"><?= (int) $g['file_count'] ?> files</span>
                        </div>
                        <div class="file-group-body">
                            <ul>
                                <?php foreach ($g['items'] as $f): ?>
                                    <li>
                                        <span><?= htmlspecialchars($f['file_name']) ?></span>
                                        <span class="muted">
                                            <?= htmlspecialchars($f['owner_name']) ?> ·
                                            <?= htmlspecialchars($f['file_size']) ?> ·
                                            <?= timeAgo($f['uploaded_at']) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="file-group-foot">
                            <span>Last: <?= htmlspecialchars($g['last_upload']) ?></span>
                            <a href="teacher_files.php" class="link">View all</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <details class="detail-section">
            <summary>
                Project Settings
                <span class="summary-note">Team rules, permissions and reminders</span>
            </summary>
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
                        <span class="settings-label">Assessment weight</span>
                        <span class="settings-value">
                            <?php if (!empty($project['assessment_scope']) && $project['assessment_weight'] !== null): ?>
                                <?= htmlspecialchars((string) (int) $project['assessment_weight'] . '% of ' . $project['assessment_scope']) ?>
                            <?php else: ?>
                                <span class="muted">Not set</span>
                            <?php endif; ?>
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
                            // ★ 修正 2：?? → isset() ? :
                            echo htmlspecialchars(isset($teamModeLabels[$project['team_mode']])
                                    ? $teamModeLabels[$project['team_mode']]
                                    : $project['team_mode']);
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
                        <span class="settings-value">
                            <?= !empty($project['allow_cross_class'])
                                    ? 'Allowed — ' . htmlspecialchars($project['cross_class_codes'] ?: 'No partner classes selected')
                                    : 'Not allowed' ?>
                        </span>
                    </div>
                    <div class="settings-item">
                        <span class="settings-label">After team deadline</span>
                        <span class="settings-value">
                            <?php
                            $postLabels = array(
                                    'Lock'   => 'Lock Team — requires teacher approval',
                                    'Notify' => 'Notify teacher only',
                            );
                            // ★ 修正 3：?? → isset() ? :
                            echo htmlspecialchars(isset($postLabels[$project['post_deadline_action']])
                                    ? $postLabels[$project['post_deadline_action']]
                                    : $project['post_deadline_action']);
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
        </details>

        <details class="detail-section" <?= isset($_GET['month']) ? 'open' : '' ?>>
            <summary>
                Project Calendar
                <span class="summary-note"><?= date('F Y', $calFirstTs) ?> · <?= (int) $calEventCount ?> scheduled items</span>
            </summary>
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
        </details>

        <details class="detail-section">
            <summary>
                Recent Activity
                <span class="summary-note">Latest 5 project updates</span>
            </summary>
            <div class="card">
                <ul class="activity-list">
                    <?php if (count($activities) === 0): ?>
                        <li class="muted">No activity yet.</li>
                    <?php endif; ?>
                    <?php foreach ($activities as $a): ?>
                        <li>
                            <span>
                                <?php if (!empty($a['actor_name'])): ?>
                                    <strong><?= htmlspecialchars($a['actor_name']) ?></strong>
                                <?php endif; ?>
                                <?= htmlspecialchars($a['detail']) ?>
                            </span>
                            <span class="muted"><?= timeAgo($a['created_at']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </details>

    </div>
</main>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
</body>
</html>
