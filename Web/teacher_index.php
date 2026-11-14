<?php
$active = 'dashboard';
//111
// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */


// =========================================================
// 当前老师的 Teachers.id
// =========================================================
$teacherId = 0;
if (isset($_SESSION['userID'])) {
    $stmtT = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtT, "i", $_SESSION['userID']);
    mysqli_stmt_execute($stmtT);
    $rsT = mysqli_stmt_get_result($stmtT);
    $rowT = mysqli_fetch_assoc($rsT);
    mysqli_stmt_close($stmtT);
    if ($rowT) { $teacherId = (int)$rowT['id']; }
}


// =========================================================
// 2. Teams（只看我负责项目下的组）
// =========================================================
$teams = array();

$sql = "
    SELECT
        g.id,
        g.name,
        g.status,
        g.formed_at,
        g.max_members,
        p.id AS project_id,
        p.name AS project_name,
        p.category AS project_category,
        cl.class_code,
        u.username AS leader_name,
        (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count,
        (SELECT COUNT(*) FROM `tasks` t
            WHERE t.group_id = g.id
              AND t.status <> 'Completed'
              AND t.due_date < CURDATE()) AS overdue_count,
        (SELECT COUNT(*) FROM `tasks` t2
            WHERE t2.group_id = g.id
              AND t2.status = 'Completed') AS completed_tasks,
        (SELECT COUNT(*) FROM `tasks` t3
            WHERE t3.group_id = g.id) AS total_tasks,
        (SELECT MAX(a.created_at) FROM `activities` a WHERE a.group_id = g.id) AS last_activity,
        (SELECT COUNT(*) FROM `group_requests` r
            WHERE r.project_id = g.project_id
              AND r.group_name = g.name
              AND r.status = 'Pending') AS pending_requests
    FROM `groups` g
    INNER JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `classes` cl ON cl.id = p.class_id
    LEFT JOIN `students` s ON s.id = g.leader_id
    LEFT JOIN `users`    u ON u.id = s.user_id
    WHERE p.supervisor_id = ?
      AND (
          g.status <> 'Recruiting'
          OR EXISTS (
              SELECT 1
              FROM `group_requests` submitted
              WHERE submitted.project_id = g.project_id
                AND submitted.group_name = g.name
                AND submitted.status = 'Pending'
          )
      )
    ORDER BY g.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $teams[] = $row;
}
mysqli_stmt_close($stmt);

$totalTeams = count($teams);
$formationCounts = array(
        'Awaiting Approval' => 0,
        'Choosing Leader'   => 0,
        'Active'            => 0,
        'Completed'         => 0,
);
$overdueTeamCount = 0;
$projectTeamHealth = array();

foreach ($teams as $t) {
    if ((int) $t['pending_requests'] > 0) {
        $formationCounts['Awaiting Approval']++;
        $state = 'Awaiting Approval';
    } elseif ($t['status'] === 'Voting') {
        $formationCounts['Choosing Leader']++;
        $state = 'Choosing Leader';
    } elseif ($t['status'] === 'Executing') {
        $formationCounts['Active']++;
        $state = 'Active';
    } elseif ($t['status'] === 'Completed') {
        $formationCounts['Completed']++;
        $state = 'Completed';
    } else {
        $formationCounts['Awaiting Approval']++;
        $state = 'Awaiting Approval';
    }
    if ((int) $t['overdue_count'] > 0) $overdueTeamCount++;

    $projectKey = (int) $t['project_id'];
    if (!isset($projectTeamHealth[$projectKey])) {
        $projectTeamHealth[$projectKey] = array(
                'project_id' => $projectKey,
                'project_name' => $t['project_name'],
                'class_code' => $t['class_code'],
                'total' => 0,
                'awaiting' => 0,
                'choosing' => 0,
                'active' => 0,
                'completed' => 0,
                'overdue' => 0,
        );
    }
    $projectTeamHealth[$projectKey]['total']++;
    if ($state === 'Awaiting Approval') $projectTeamHealth[$projectKey]['awaiting']++;
    if ($state === 'Choosing Leader') $projectTeamHealth[$projectKey]['choosing']++;
    if ($state === 'Active') $projectTeamHealth[$projectKey]['active']++;
    if ($state === 'Completed') $projectTeamHealth[$projectKey]['completed']++;
    if ((int) $t['overdue_count'] > 0) $projectTeamHealth[$projectKey]['overdue']++;
}

// Teams that need attention
$attentionTeams = array();
foreach ($teams as $t) {
    $reasons  = array();
    $priority = 99;

    if ((int) $t['pending_requests'] > 0) {
        $reasons[] = 'Awaiting approval';
        $priority  = min($priority, 1);
    }
    if ((int) $t['overdue_count'] > 0) {
        $n = (int) $t['overdue_count'];
        $reasons[] = $n . ' overdue task' . ($n === 1 ? '' : 's');
        $priority  = min($priority, 2);
    }

    if (count($reasons) > 0) {
        $t['reasons']  = $reasons;
        $t['priority'] = $priority;
        $attentionTeams[] = $t;
    }
}

// 修正：用 PHP 5.x 相容的比較，取代 <=>
usort($attentionTeams, function ($a, $b) {
    if ($a['priority'] === $b['priority']) {
        $aOverdue = (int) $a['overdue_count'];
        $bOverdue = (int) $b['overdue_count'];
        if ($aOverdue === $bOverdue) return 0;
        return ($aOverdue < $bOverdue) ? 1 : -1;
    }
    return ($a['priority'] < $b['priority']) ? -1 : 1;
});
$attentionTeamTotal = count($attentionTeams);

// Upcoming team task deadlines (future only).
$upcomingDeadlines = array();

$stmt = mysqli_prepare($conn, "
    SELECT t.title, t.due_date, g.name AS group_name, p.name AS project_name,
           'task' AS item_type
    FROM `tasks` t
    INNER JOIN `groups` g   ON g.id = t.group_id
    INNER JOIN `projects` p ON p.id = t.project_id
    WHERE p.supervisor_id = ?
      AND t.status <> 'Completed'
      AND t.due_date IS NOT NULL
      AND t.due_date >= CURDATE()
      AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY t.due_date ASC
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $upcomingDeadlines[] = $row; }
mysqli_stmt_close($stmt);

// 修正：用 PHP 5.x 相容的比較，取代 <=>
usort($upcomingDeadlines, function ($a, $b) {
    $aTime = strtotime($a['due_date']);
    $bTime = strtotime($b['due_date']);
    if ($aTime === $bTime) return 0;
    return ($aTime < $bTime) ? -1 : 1;
});
$upcomingDeadlines = array_slice($upcomingDeadlines, 0, 6);

// =========================================================
// 3b. 最近的项目截止（只读查询，右栏倒计时用）
// =========================================================
$nextDeadline = null;
$stmt = mysqli_prepare($conn, "
    SELECT p.id, p.name, p.period_start, p.period_end, cl.class_code
    FROM `projects` p
    LEFT JOIN `classes` cl ON cl.id = p.class_id
    WHERE p.supervisor_id = ?
      AND p.period_end IS NOT NULL
      AND p.period_end >= CURDATE()
    ORDER BY p.period_end ASC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$nextDeadline = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

$countdown = null;
if ($nextDeadline) {
    $countdown = array(
        'id'         => (int) $nextDeadline['id'],
        'name'       => (string) $nextDeadline['name'],
        'class_code' => (string) ($nextDeadline['class_code'] !== null ? $nextDeadline['class_code'] : ''),
        'end_label'  => date('d M Y', strtotime($nextDeadline['period_end'])),
        'end_ts'     => strtotime($nextDeadline['period_end'] . ' 23:59:59'),
        'start_ts'   => 0,
    );
    $startTs = $nextDeadline['period_start'] !== null ? strtotime($nextDeadline['period_start']) : 0;
    $nowTs   = time();
    if ($startTs > 0 && $countdown['end_ts'] > $startTs) {
        $countdown['start_ts']    = $startTs;
        $countdown['elapsed_pct'] = min(100, (int) round(($nowTs - $startTs) / ($countdown['end_ts'] - $startTs) * 100));
    } else {
        $countdown['elapsed_pct'] = 0;
    }
    $remain = max(0, $countdown['end_ts'] - $nowTs);
    $countdown['days']  = (int) floor($remain / 86400);
    $countdown['hours'] = (int) floor(($remain % 86400) / 3600);
    $countdown['mins']  = (int) floor(($remain % 3600) / 60);
    $countdown['secs']  = $remain % 60;
}

if (!function_exists('dashboardDeadlineLabel')) {
    function dashboardDeadlineLabel($date)
    {
        $days = (int) round(
                (strtotime($date) - strtotime(date('Y-m-d'))) / 86400
        );

        if ($days < 0) {
            $n = abs($days);
            return array('label' => 'Overdue ' . $n . 'd', 'class' => 'overdue');
        }
        if ($days === 0) {
            return array('label' => 'Today', 'class' => 'today');
        }
        if ($days === 1) {
            return array('label' => 'Tomorrow', 'class' => 'soon');
        }
        return array('label' => 'In ' . $days . ' days', 'class' => 'soon');
    }
}


// =========================================================
// 3. Recent Activity
// =========================================================
$activities = array();

$sql = "
    SELECT a.action,
           a.detail,
           a.created_at,
           u.username AS actor_name
    FROM `activities` a
    INNER JOIN `projects` p ON p.id = a.project_id
    LEFT JOIN `users` u ON u.id = a.actor_id
    WHERE p.supervisor_id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $activities[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 4. 待审阅文件（Dashboard 第三行 Files to Review）
// =========================================================
$pendingFiles = array();
$stmt = mysqli_prepare($conn, "
    SELECT f.id, f.file_name, f.display_name, f.file_type, f.uploaded_at,
           g.name AS group_name
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    LEFT JOIN `groups` g ON g.id = f.group_id
    WHERE f.status = 'Unviewed'
      AND p.supervisor_id = ?
    ORDER BY f.uploaded_at DESC
    LIMIT 8
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $pendingFiles[] = $row; }
mysqli_stmt_close($stmt);


// =========================================================
// 5. 日历数据：当前老师项目下所有未完成任务，按截止日期分组
//    （用于日历打点、未来 7 天列表、点选日期查看）
// =========================================================
$calendarTasks = array();
$stmt = mysqli_prepare($conn, "
    SELECT t.title, t.due_date, g.name AS group_name, p.name AS project_name
    FROM `tasks` t
    INNER JOIN `projects` p ON p.id = t.project_id
    LEFT JOIN `groups` g ON g.id = t.group_id
    WHERE p.supervisor_id = ?
      AND t.due_date IS NOT NULL
      AND t.status <> 'Completed'
    ORDER BY t.due_date ASC
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $d = (string) $row['due_date'];
    if (!isset($calendarTasks[$d])) { $calendarTasks[$d] = array(); }
    $calendarTasks[$d][] = array(
        'title'   => (string) $row['title'],
        'group'   => (string) ($row['group_name'] !== null ? $row['group_name'] : ''),
        'project' => (string) ($row['project_name'] !== null ? $row['project_name'] : ''),
    );
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamBox | Instructor</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_index.css?v=<?= filemtime('assets/css/teacher_index.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/teacher_index_ds.css?v=<?= filemtime('assets/css/teacher_index_ds.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content ds-dash">

        <h1 class="sr-only">Instructor Dashboard</h1>

        <div class="ds-row ds-row-top">

            <section class="ds-hero">
                <h2>Good Afternoon, <?php echo htmlspecialchars($teacherName); ?>!</h2>
                <?php
                $heroBits = array();
                $awaiting = (int) $formationCounts['Awaiting Approval'];
                if ($awaiting > 0) {
                    $heroBits[] = $awaiting . ' team request' . ($awaiting === 1 ? '' : 's') . ' awaiting approval';
                }
                if ($overdueTeamCount > 0) {
                    $heroBits[] = $overdueTeamCount . ' team' . ($overdueTeamCount === 1 ? '' : 's') . ' with overdue tasks';
                }
                $heroText = count($heroBits) > 0 ? implode(' · ', $heroBits) : 'All teams are up to date.';
                ?>
                <p><?php echo htmlspecialchars($heroText); ?></p>
                <a class="cta" href="teacher_teams.php">View Teams</a>
                <div class="clock">
                    <div class="t" id="tbClock"><?php echo date('H:i:s'); ?></div>
                    <div class="d" id="tbClockDate"><?php echo date('l, j M Y'); ?></div>
                </div>
            </section>

            <section class="ds-card ds-projects">
                <div class="card-head"><h3>Project Overview</h3><a class="see-more" href="teacher_projects.php">see more</a></div>
                <div class="proj-list">
                    <?php if (count($projectTeamHealth) === 0): ?>
                        <div class="empty">No projects yet.</div>
                    <?php else: $projCount = 0; foreach ($projectTeamHealth as $ph): if ($projCount >= 3) break; $projCount++; ?>
                        <?php
                        $phTotal = max(1, (int) $ph['total']);
                        $phDone = (int) $ph['completed'];
                        $phPct = (int) round($phDone / $phTotal * 100);
                        ?>
                        <div class="proj-row">
                            <div class="proj-inner">
                                <span class="proj-icon">FYP</span>
                                <div class="proj-main">
                                    <div class="proj-name"><?php echo htmlspecialchars($ph['project_name']); ?></div>
                                    <div class="proj-meta"><?php echo htmlspecialchars($ph['class_code'] ?: 'No class'); ?> · <?php echo (int) $ph['total']; ?> teams</div>
                                </div>
                                <span class="proj-score"><?php echo $phDone; ?>/<?php echo (int) $ph['total']; ?></span>
                            </div>
                            <div class="proj-bar"><div class="done" style="width:<?php echo $phPct; ?>%"></div><div class="rest" style="width:<?php echo 100 - $phPct; ?>%"></div></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

        </div>

        <div class="ds-row ds-row-mid">
            <div class="ds-mid-left">

                <section class="ds-card ds-teams">
                    <div class="card-head">
                        <h3>Teams Needing Attention</h3>
                        <div class="team-pager" id="teamPager">
                            <button type="button" class="pager-btn" id="teamPrev" aria-label="Previous teams"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>
                            <button type="button" class="pager-btn" id="teamNext" aria-label="Next teams"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>
                        </div>
                    </div>
                    <div class="teams-viewport">
                        <div class="teams-track" id="teamsTrack">
                            <?php if (count($attentionTeams) === 0): ?>
                                <div class="empty">All teams are up to date.</div>
                            <?php else: foreach ($attentionTeams as $t): ?>
                                <?php
                                $iconClass = 'grey';
                                if ((int) $t['priority'] === 1) { $iconClass = 'amber'; }
                                elseif ((int) $t['priority'] === 2) { $iconClass = 'coral'; }
                                $initials = strtoupper(substr((string) $t['name'], 0, 2));
                                $taskTotal = max(0, (int) $t['total_tasks']);
                                $taskDone = max(0, (int) $t['completed_tasks']);
                                $taskPct = $taskTotal > 0 ? (int) round($taskDone / $taskTotal * 100) : 0;
                                ?>
                                <div class="team-card">
                                    <div class="team-top">
                                        <span class="team-icon <?php echo $iconClass; ?>"><?php echo htmlspecialchars($initials); ?></span>
                                        <div>
                                            <div class="team-title"><?php echo htmlspecialchars($t['name']); ?></div>
                                            <div class="team-sub"><?php echo htmlspecialchars($t['project_name']); ?> · <?php echo htmlspecialchars($t['project_category'] ?: '—'); ?></div>
                                        </div>
                                    </div>
                                    <div class="team-prog">
                                        <div class="track"><?php if ($taskTotal > 0): ?><div class="fill" style="width:<?php echo $taskPct; ?>%"></div><?php endif; ?></div>
                                        <span class="val"><?php echo $taskDone; ?>/<?php echo $taskTotal; ?></span>
                                    </div>
                                    <div class="chips">
                                        <?php foreach ($t['reasons'] as $reason): ?>
                                            <span class="chip"><?php echo htmlspecialchars($reason); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <a class="team-view" href="team_detail.php?id=<?php echo (int) $t['id']; ?>">View</a>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </section>

                <section class="ds-card ds-files">
                    <div class="card-head"><h3>Files to Review</h3><a class="see-more" href="teacher_review.php">Review Center</a></div>
                    <div class="files-list" id="filesList">
                        <?php if (count($pendingFiles) === 0): ?>
                            <div class="empty">No files waiting for review.</div>
                        <?php else: foreach ($pendingFiles as $f): ?>
                            <?php $ftype = strtoupper(substr((string) ($f['file_type'] ?: 'FILE'), 0, 3)); ?>
                            <div class="file-row">
                                <span class="file-icon"><?php echo htmlspecialchars($ftype); ?></span>
                                <div class="file-main">
                                    <div class="file-name"><?php echo htmlspecialchars($f['display_name'] ?: $f['file_name']); ?></div>
                                    <div class="file-meta"><?php echo htmlspecialchars($f['group_name'] ?: '—'); ?> · uploaded <?php echo htmlspecialchars(timeAgo($f['uploaded_at'])); ?></div>
                                </div>
                                <div class="file-acts">
                                    <a class="act dl" href="download_file.php?id=<?php echo (int) $f['id']; ?>" title="Download"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11M7 11l5 5 5-5M5 20h14"/></svg></a>
                                    <a class="act view" href="file_view.php?id=<?php echo (int) $f['id']; ?>" title="Preview"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.6"/></svg></a>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="files-foot" id="filesFoot" hidden>
                        <button type="button" class="files-toggle" id="filesToggle"></button>
                    </div>
                </section>

            </div>

            <section class="ds-card ds-cal">
                <div class="cal">
                    <div class="cal-head">
                        <button type="button" class="cal-nav" id="calPrev" aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m15 5-7 7 7 7"/></svg></button>
                        <span class="cal-title" id="calTitle"></span>
                        <button type="button" class="cal-nav" id="calNext" aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m9 5 7 7-7 7"/></svg></button>
                    </div>
                    <div class="cal-week"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thr</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                    <div class="cal-days" id="calDays"></div>
                </div>
                <div class="upcoming" id="upcomingWrap">
                    <div class="upcoming-head"><h4 id="upcomingTitle">Upcoming</h4><span id="upcomingRange">Next 7 days</span><a class="upc-back" id="upcomingBack" href="#">← 7 days</a></div>
                    <ul class="upcoming-list" id="upcomingList"></ul>
                </div>
            </section>

        </div>

    </div>
</main>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
<script>
var TEAMBOX_CAL_TASKS = <?= json_encode($calendarTasks) ?>;
var TEAMBOX_TODAY = <?= json_encode(date('Y-m-d')) ?>;
(function () {
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function dateKey(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }
    function parseKey(key) { var p = key.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    var today = parseKey(TEAMBOX_TODAY);
    var viewYear = today.getFullYear(), viewMonth = today.getMonth();
    var selectedKey = null;
    var mShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var mNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    function tasksOn(key) { return TEAMBOX_CAL_TASKS[key] || []; }

    function badgeFor(date) {
        var diff = Math.round((date - today) / 86400000);
        if (diff < 0) return Math.abs(diff) + 'd overdue';
        if (diff === 0) return 'Today';
        if (diff === 1) return 'Tomorrow';
        return 'In ' + diff + ' days';
    }

    function itemHTML(t, date) {
        var meta = [t.group, t.project].filter(Boolean).join(' · ');
        return '<li><div class="upc-main">'
            + '<div class="upc-title">' + esc(t.title) + '</div>'
            + '<div class="upc-meta">' + esc(meta) + '</div></div>'
            + '<span class="upc-badge">' + badgeFor(date) + '</span></li>';
    }

    function renderList(items, title, range, isDay) {
        var wrap = document.getElementById('upcomingWrap');
        document.getElementById('upcomingTitle').textContent = title;
        document.getElementById('upcomingRange').textContent = range;
        wrap.classList.toggle('is-day', isDay);
        var ul = document.getElementById('upcomingList');
        if (!items.length) {
            ul.innerHTML = '<li class="upc-empty">No tasks due.</li>';
            return;
        }
        ul.innerHTML = items.map(function (it) { return itemHTML(it.task, it.date); }).join('');
    }

    function showSevenDays() {
        selectedKey = null;
        var list = [];
        for (var i = 0; i < 7; i++) {
            var dt = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
            var key = dateKey(dt.getFullYear(), dt.getMonth(), dt.getDate());
            tasksOn(key).forEach(function (t) { list.push({ task: t, date: dt }); });
        }
        renderList(list, 'Upcoming', 'Next 7 days', false);
        renderCal();
    }

    function selectDate(key) {
        selectedKey = key;
        var dt = parseKey(key);
        var isToday = key === TEAMBOX_TODAY;
        var title = isToday ? 'Today' : (dt.getDate() + ' ' + mShort[dt.getMonth()]);
        var range = dayNames[dt.getDay()] + ', ' + dt.getDate() + ' ' + mShort[dt.getMonth()];
        var list = tasksOn(key).map(function (t) { return { task: t, date: dt }; });
        renderList(list, title, range, true);
        renderCal();
    }

    function renderCal() {
        document.getElementById('calTitle').textContent = mNames[viewMonth] + ' ' + viewYear;
        var first = new Date(viewYear, viewMonth, 1);
        var startOffset = (first.getDay() + 6) % 7;
        var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        var daysInPrev = new Date(viewYear, viewMonth, 0).getDate();
        var html = '';
        for (var i = 0; i < startOffset; i++) {
            html += '<span class="day dim"><span class="num">' + (daysInPrev - startOffset + 1 + i) + '</span></span>';
        }
        for (var d = 1; d <= daysInMonth; d++) {
            var key = dateKey(viewYear, viewMonth, d);
            var isToday = (key === TEAMBOX_TODAY);
            var isSelected = (key === selectedKey);
            var hasDot = tasksOn(key).length > 0;
            var dotClass = hasDot ? (key < TEAMBOX_TODAY ? ' dot overdue' : ' dot') : '';
            html += '<span class="day' + (isToday ? ' today' : '') + (isSelected ? ' selected' : '')
                + '" data-date="' + key + '"><span class="num">' + d + '</span>'
                + (hasDot ? '<i class="' + (dotClass.trim()) + '"></i>' : '') + '</span>';
        }
        document.getElementById('calDays').innerHTML = html;
    }

    document.getElementById('calDays').addEventListener('click', function (e) {
        var el = e.target.closest('.day');
        if (!el || !el.dataset.date) return;
        selectDate(el.dataset.date);
    });
    document.getElementById('upcomingBack').addEventListener('click', function (e) { e.preventDefault(); showSevenDays(); });
    document.getElementById('calPrev').addEventListener('click', function () { viewMonth--; if (viewMonth < 0) { viewMonth = 11; viewYear--; } renderCal(); });
    document.getElementById('calNext').addEventListener('click', function () { viewMonth++; if (viewMonth > 11) { viewMonth = 0; viewYear++; } renderCal(); });

    selectDate(TEAMBOX_TODAY);
})();
</script>
<script>
(function () {
    var track = document.getElementById('teamsTrack');
    if (!track) return;
    var pager = document.getElementById('teamPager');
    var prev = document.getElementById('teamPrev');
    var next = document.getElementById('teamNext');
    var cards = track.querySelectorAll('.team-card');
    if (cards.length <= 2 && pager) { pager.style.display = 'none'; }
    function step() {
        var c = cards[0];
        return c ? (c.getBoundingClientRect().width + 14) : 300;
    }
    function update() {
        if (prev) prev.disabled = track.scrollLeft <= 2;
        if (next) next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
    }
    if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
    track.addEventListener('scroll', update);
    update();
})();
</script>
<script>
(function () {
    var list = document.getElementById('filesList');
    var foot = document.getElementById('filesFoot');
    var btn = document.getElementById('filesToggle');
    if (!list || !btn || !foot) return;
    var rows = list.querySelectorAll('.file-row');
    var visibleCount = 3;
    if (rows.length <= visibleCount) return;
    var collapsed = true;
    foot.hidden = false;
    function render() {
        rows.forEach(function (r, i) {
            if (i >= visibleCount) r.classList.toggle('collapsed', collapsed);
        });
        btn.textContent = collapsed ? ('+ ' + (rows.length - visibleCount) + ' more') : 'Show less';
    }
    btn.addEventListener('click', function () { collapsed = !collapsed; render(); });
    render();
})();
</script>
</body>
</html>
