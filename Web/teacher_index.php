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

usort($attentionTeams, function ($a, $b) {
    if ($a['priority'] === $b['priority']) {
        return (int) $b['overdue_count'] <=> (int) $a['overdue_count'];
    }
    return $a['priority'] <=> $b['priority'];
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

usort($upcomingDeadlines, function ($a, $b) {
    return strtotime($a['due_date']) <=> strtotime($b['due_date']);
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
    <style>
        .compact-card { margin-bottom: 0; }
        .empty-note {
            padding: 24px 16px;
            color: #8b8f98;
            font-size: 12px;
            text-align: center;
        }

        .deadline-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .deadline-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid #f2f3f5;
        }
        .deadline-list li:last-child { border-bottom: none; }
        .deadline-main { min-width: 0; }
        .deadline-title {
            overflow: hidden;
            color: #2c2f36;
            font-size: 12.5px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .deadline-meta {
            margin-top: 2px;
            overflow: hidden;
            color: #9aa0a8;
            font-size: 10.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .deadline-badge {
            flex-shrink: 0;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
        }
        .deadline-badge.overdue { background: #fdecec; color: #b02a2a; }
        .deadline-badge.today { background: #fdf3e2; color: #9a6a13; }
        .deadline-badge.soon { background: #eef2f7; color: #4b5058; }

        .team-sub {
            margin-top: 2px;
            font-size: 10.5px;
        }
        .reason-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .reason-chip {
            padding: 2px 7px;
            border-radius: 999px;
            background: #f4f6f9;
            color: #4b5058;
            font-size: 10px;
            font-weight: 600;
        }

        .dashboard-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }
        .dashboard-head h1 { margin-bottom: 4px; }
        .dashboard-head .subtitle { margin-bottom: 0; }
        .dashboard-head .btn-primary {
            flex: 0 0 auto;
            text-decoration: none;
        }

        .team-state-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }
        .team-state-card {
            display: block;
            padding: 13px 14px;
            border: 1px solid #e3e7eb;
            border-radius: 8px;
            background: #fff;
            color: inherit;
            text-decoration: none;
        }
        .team-state-card:hover {
            border-color: #b9c9d9;
            background: #fbfcfd;
        }
        .team-state-card > span {
            display: block;
            color: #7e8690;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .team-state-card strong {
            display: block;
            margin-top: 5px;
            color: #2d343d;
            font-size: 24px;
            line-height: 1;
        }
        .team-state-card small {
            display: block;
            margin-top: 5px;
            color: #969da6;
            font-size: 10px;
        }
        .team-state-card.is-active strong { color: #175196; }
        .team-state-card.is-complete strong { color: #367552; }

        .dashboard-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 9px 12px;
            border: 1px solid #efcccc;
            border-left: 4px solid #b43b3b;
            border-radius: 7px;
            background: #fff7f7;
            color: #7f3737;
            font-size: 11.5px;
            text-decoration: none;
        }
        .dashboard-alert span { margin-left: auto; font-weight: 700; }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .section-head h2 { margin-bottom: 10px; }
        .section-head > span {
            margin-bottom: 10px;
            color: #8b929b;
            font-size: 10.5px;
        }

        .project-health-card {
            overflow-x: auto;
            margin-bottom: 24px;
        }
        .project-health-table { min-width: 680px; }
        .project-health-table th,
        .project-health-table td { white-space: nowrap; }
        .project-health-table td { padding-top: 10px; padding-bottom: 10px; }
        .project-health-name {
            display: block;
            max-width: 330px;
            overflow: hidden;
            color: #33404d;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            text-overflow: ellipsis;
        }
        .project-health-name + span {
            display: block;
            margin-top: 2px;
            color: #9299a2;
            font-size: 9.5px;
        }
        .danger-number { color: #b42318; font-weight: 700; }
        .health-pill {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 700;
        }
        .health-pill.at-risk { background: #fdecec; color: #a52e2e; }
        .health-pill.attention { background: #edf4fb; color: #315f87; }
        .health-pill.on-track { background: #e8f4ec; color: #34704d; }

        .upcoming-tasks-card { margin-bottom: 24px; }
        .dashboard-details {
            margin-top: 8px;
            border-top: 1px solid #e2e6ea;
        }
        .dashboard-details > summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 2px;
            color: #4b5563;
            cursor: pointer;
            list-style: none;
            font-size: 12px;
            font-weight: 700;
        }
        .dashboard-details > summary::-webkit-details-marker { display: none; }
        .dashboard-details > summary::after {
            content: "›";
            color: #8b929b;
            font-size: 18px;
            transform: rotate(90deg);
        }
        .dashboard-details[open] > summary::after { transform: rotate(-90deg); }
        .dashboard-details > summary small {
            margin-left: auto;
            margin-right: 8px;
            color: #8b929b;
            font-size: 10px;
            font-weight: 400;
        }

        @media (max-width: 900px) {
            .team-state-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 600px) {
            .dashboard-head { flex-direction: column; }
            .team-state-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <div class="dashboard-head">
            <div>
                <h1>Instructor Dashboard</h1>
                <p class="subtitle">
                    <?= (int) $totalTeams ?> teams across <?= count($projectTeamHealth) ?> projects · 2026/27
                </p>
            </div>
            <a href="teacher_teams.php" class="btn-primary">View Teams</a>
        </div>

        <div class="team-state-grid">
            <a href="teacher_teams.php?status=pending" class="team-state-card">
                <span>Awaiting Approval</span>
                <strong><?= (int) $formationCounts['Awaiting Approval'] ?></strong>
                <small>Teacher action required</small>
            </a>
            <a href="teacher_teams.php?status=Voting" class="team-state-card">
                <span>Choosing Leader</span>
                <strong><?= (int) $formationCounts['Choosing Leader'] ?></strong>
                <small>Leader election in progress</small>
            </a>
            <a href="teacher_teams.php?status=Executing" class="team-state-card is-active">
                <span>Active</span>
                <strong><?= (int) $formationCounts['Active'] ?></strong>
                <small>Teams currently working</small>
            </a>
            <a href="teacher_teams.php?status=Completed" class="team-state-card is-complete">
                <span>Completed</span>
                <strong><?= (int) $formationCounts['Completed'] ?></strong>
                <small>Finished teams</small>
            </a>
        </div>

        <?php if ($overdueTeamCount > 0): ?>
            <a href="teacher_teams.php?status=overdue" class="dashboard-alert">
                <strong><?= (int) $overdueTeamCount ?> teams</strong> have overdue work
                <span>Review teams →</span>
            </a>
        <?php endif; ?>

        <div class="section-head">
            <h2>Project Team Health</h2>
            <span><?= count($projectTeamHealth) ?> projects</span>
        </div>
        <div class="card project-health-card">
            <table class="project-health-table">
                <tr>
                    <th>Project</th>
                    <th>Teams</th>
                    <th>Awaiting</th>
                    <th>Active</th>
                    <th>Overdue</th>
                    <th>Health</th>
                </tr>
                <?php foreach ($projectTeamHealth as $projectHealth): ?>
                    <?php
                    if ($projectHealth['overdue'] > 0) {
                        $healthLabel = 'At Risk';
                        $healthClass = 'at-risk';
                    } elseif ($projectHealth['awaiting'] > 0) {
                        $healthLabel = 'Needs Attention';
                        $healthClass = 'attention';
                    } else {
                        $healthLabel = 'On Track';
                        $healthClass = 'on-track';
                    }
                    ?>
                    <tr>
                        <td>
                            <a href="project_detail.php?id=<?= (int) $projectHealth['project_id'] ?>"
                               class="project-health-name"
                               title="<?= htmlspecialchars($projectHealth['project_name']) ?>">
                                <?= htmlspecialchars($projectHealth['project_name']) ?>
                            </a>
                            <span><?= htmlspecialchars($projectHealth['class_code'] ?: 'No class') ?></span>
                        </td>
                        <td><?= (int) $projectHealth['total'] ?></td>
                        <td><?= (int) $projectHealth['awaiting'] ?></td>
                        <td><?= (int) $projectHealth['active'] ?></td>
                        <td class="<?= $projectHealth['overdue'] > 0 ? 'danger-number' : '' ?>"><?= (int) $projectHealth['overdue'] ?></td>
                        <td><span class="health-pill <?= $healthClass ?>"><?= $healthLabel ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Teams needing attention -->
        <div class="section-head">
            <h2>Teams Needing Attention</h2>
            <span>Top <?= min(5, (int) $attentionTeamTotal) ?> of <?= (int) $attentionTeamTotal ?></span>
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
                        <td title="<?= htmlspecialchars($t['project_name']) ?>">
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

        <div class="section-head">
            <h2>Upcoming Team Tasks</h2>
            <span>Next 7 days</span>
        </div>
        <div class="card compact-card upcoming-tasks-card">
            <?php if (count($upcomingDeadlines) === 0): ?>
                <div class="empty-note">No team tasks are due in the next 7 days.</div>
            <?php else: ?>
                <ul class="deadline-list">
                    <?php foreach ($upcomingDeadlines as $deadline): ?>
                        <?php $deadlineLabel = dashboardDeadlineLabel($deadline['due_date']); ?>
                        <li>
                            <div class="deadline-main">
                                <div class="deadline-title"><?= htmlspecialchars($deadline['title']) ?></div>
                                <div class="deadline-meta">
                                    <?= htmlspecialchars($deadline['group_name']) ?>
                                    · <?= htmlspecialchars($deadline['project_name']) ?>
                                    · <?= date('d M', strtotime($deadline['due_date'])) ?>
                                </div>
                            </div>
                            <span class="deadline-badge <?= $deadlineLabel['class'] ?>">
                                <?= htmlspecialchars($deadlineLabel['label']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Activity -->
        <details class="dashboard-details">
            <summary>
                <span>Recent Activity</span>
                <small>Latest <?= count($activities) ?> updates</small>
            </summary>
            <div class="card">
                <ul>
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
        </details>

    </div>
</main>

</body>
</html>
