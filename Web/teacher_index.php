<?php
$active = 'dashboard';

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
    if (empty($t['last_activity'])
            || strtotime($t['last_activity']) < strtotime('-7 days')) {
        $reasons[] = 'No activity for 7+ days';
        $priority  = min($priority, 4);
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
$attentionTeams = array_slice($attentionTeams, 0, 5);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamBox | Instructor</title>
    <link rel="stylesheet" href="teacher.css?v=20261028b">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <div class="dashboard-head">
            <div>
                <h1>Instructor Dashboard</h1>
                <p class="subtitle">
                    <?php echo (int) $totalTeams; ?> teams across <?php echo count($projectTeamHealth); ?> projects · 2026/27
                </p>
            </div>
            <a href="teacher_teams.php" class="btn-primary">View Teams</a>
        </div>

        <div class="team-state-grid">
            <a href="teacher_teams.php?status=pending" class="team-state-card">
                <span>Awaiting Approval</span>
                <strong><?php echo (int) $formationCounts['Awaiting Approval']; ?></strong>
                <small>Teacher action required</small>
            </a>
            <a href="teacher_teams.php?status=Voting" class="team-state-card">
                <span>Choosing Leader</span>
                <strong><?php echo (int) $formationCounts['Choosing Leader']; ?></strong>
                <small>Leader election in progress</small>
            </a>
            <a href="teacher_teams.php?status=Executing" class="team-state-card">
                <span>Active</span>
                <strong><?php echo (int) $formationCounts['Active']; ?></strong>
                <small>Teams currently working</small>
            </a>
            <a href="teacher_teams.php?status=Completed" class="team-state-card">
                <span>Completed</span>
                <strong><?php echo (int) $formationCounts['Completed']; ?></strong>
                <small>Finished teams</small>
            </a>
        </div>

        <div class="section-head">
            <h2>Project Overview</h2>
            <span><?php echo count($projectTeamHealth); ?> projects</span>
        </div>
        <div class="card project-health-card">
            <?php if (count($projectTeamHealth) === 0): ?>
                <div class="empty-note">No projects yet.</div>
            <?php else: ?>
                <?php
                $barColors = array(
                    'awaiting'  => '#d7dee8',
                    'choosing'  => '#aebccd',
                    'active'    => '#175196',
                    'completed' => '#4a5d73',
                );
                ?>
                <?php foreach ($projectTeamHealth as $projectHealth): ?>
                    <?php
                    $barTotal = max(1, (int) $projectHealth['total']);
                    $barSegments = array(
                        array('Awaiting', (int) $projectHealth['awaiting'],  $barColors['awaiting']),
                        array('Choosing',  (int) $projectHealth['choosing'],  $barColors['choosing']),
                        array('Active',    (int) $projectHealth['active'],    $barColors['active']),
                        array('Completed', (int) $projectHealth['completed'], $barColors['completed']),
                    );
                    ?>
                    <div class="ph-row">
                        <div class="ph-row-head">
                            <div>
                                <a href="project_detail.php?id=<?php echo (int) $projectHealth['project_id']; ?>"
                                   class="ph-project-name">
                                    <?php echo htmlspecialchars($projectHealth['project_name']); ?>
                                </a>
                                <span class="ph-project-class"><?php echo htmlspecialchars($projectHealth['class_code'] ?: 'No class'); ?></span>
                            </div>
                            <div class="ph-meta">
                                <span><strong><?php echo (int) $projectHealth['total']; ?></strong> teams</span>
                                <?php if ((int) $projectHealth['overdue'] > 0): ?>
                                    <span><strong><?php echo (int) $projectHealth['overdue']; ?></strong> overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ph-bar">
                            <?php foreach ($barSegments as $seg): ?>
                                <?php if ($seg[1] > 0): ?>
                                    <div class="ph-bar-seg"
                                         style="width: <?php echo round($seg[1] / $barTotal * 100, 2); ?>%; background: <?php echo $seg[2]; ?>;"
                                         title="<?php echo htmlspecialchars($seg[0] . ': ' . $seg[1]); ?>"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="ph-legend">
                            <?php foreach ($barSegments as $seg): ?>
                                <span class="ph-legend-item">
                                    <i class="ph-legend-dot" style="background: <?php echo $seg[2]; ?>;"></i>
                                    <?php echo $seg[0]; ?> <?php echo $seg[1]; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-head">
            <h2>Teams Needing Attention</h2>
            <span>Top <?php echo min(5, (int) $attentionTeamTotal); ?> of <?php echo (int) $attentionTeamTotal; ?></span>
        </div>
        <div class="card">
            <table class="attention-table">
                <tr>
                    <th style="width: 26%;">Team</th>
                    <th style="width: 24%;">Project</th>
                    <th>Needs Attention</th>
                    <th style="width: 60px;"></th>
                </tr>

                <?php if (count($attentionTeams) === 0): ?>
                    <tr>
                        <td colspan="4" class="muted" style="text-align: center; padding: 30px;">
                            All teams are up to date.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($attentionTeams as $t): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                            <div class="muted team-sub">
                                <?php echo htmlspecialchars($t['leader_name'] ?: 'No leader'); ?>
                            </div>
                        </td>
                        <td title="<?php echo htmlspecialchars($t['project_name']); ?>">
                            <?php echo htmlspecialchars($t['project_name']); ?>
                        </td>
                        <td>
                            <div class="reason-chips">
                                <?php foreach ($t['reasons'] as $reason): ?>
                                    <span class="reason-chip"><?php echo htmlspecialchars($reason); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td><a href="team_detail.php?id=<?php echo (int) $t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="dashboard-bottom">
            <div class="card">
                <div class="card-head">
                    <div class="section-head">
                        <h2>Upcoming Team Tasks</h2>
                        <span>Next 7 days</span>
                    </div>
                </div>
                <?php if (count($upcomingDeadlines) === 0): ?>
                    <div class="empty-note">No team tasks are due in the next 7 days.</div>
                <?php else: ?>
                    <ul class="deadline-list">
                        <?php foreach ($upcomingDeadlines as $deadline): ?>
                            <?php $deadlineLabel = dashboardDeadlineLabel($deadline['due_date']); ?>
                            <li>
                                <div class="deadline-main">
                                    <div class="deadline-title"><?php echo htmlspecialchars($deadline['title']); ?></div>
                                    <div class="deadline-meta">
                                        <?php echo htmlspecialchars($deadline['group_name']); ?>
                                        · <?php echo htmlspecialchars($deadline['project_name']); ?>
                                        · <?php echo date('d M', strtotime($deadline['due_date'])); ?>
                                    </div>
                                </div>
                                <span class="deadline-badge">
                                    <?php echo htmlspecialchars($deadlineLabel['label']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="section-head">
                        <h2>Recent Activity</h2>
                        <span>Latest <?php echo count($activities); ?></span>
                    </div>
                </div>
                <ul class="activity-list">
                    <?php if (count($activities) === 0): ?>
                        <li class="muted">No activity yet.</li>
                    <?php endif; ?>

                    <?php foreach ($activities as $a): ?>
                        <li>
                            <span class="act-detail">
                                <?php if (!empty($a['actor_name'])): ?>
                                    <strong><?php echo htmlspecialchars($a['actor_name']); ?></strong>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($a['detail']); ?>
                            </span>
                            <span class="muted act-time"><?php echo timeAgo($a['created_at']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>
</main>

</body>
</html>
