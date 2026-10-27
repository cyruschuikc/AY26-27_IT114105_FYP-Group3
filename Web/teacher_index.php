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
// 1. 汇总数字
// =========================================================

// Students explicitly assigned to this teacher
$totalStudents = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(DISTINCT student_id) AS c
    FROM `teacher_student`
    WHERE teacher_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$totalStudents = (int)$rc['c'];
mysqli_stmt_close($stmt);


// My Projects
$myProjects = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `projects` WHERE supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$myProjects = (int)$rc['c'];
mysqli_stmt_close($stmt);


// Phases not submitted by their deadline (project milestones)
$latePhases = 0;
$dueTodayPhases = 0;
$overduePhases = 0;
$totalPhases = 0;
$stmt = mysqli_prepare($conn, "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN m.end_date <= CURDATE() AND m.status <> 'Completed' THEN 1 ELSE 0 END) AS late_count,
        SUM(CASE WHEN m.end_date = CURDATE() AND m.status <> 'Completed' THEN 1 ELSE 0 END) AS due_today,
        SUM(CASE WHEN m.end_date < CURDATE() AND m.status <> 'Completed' THEN 1 ELSE 0 END) AS overdue
    FROM `milestones` m
    INNER JOIN `projects` p ON p.id = m.project_id
    WHERE p.supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$totalPhases    = (int) $rc['total'];
$latePhases     = (int) $rc['late_count'];
$dueTodayPhases = (int) $rc['due_today'];
$overduePhases  = (int) $rc['overdue'];
mysqli_stmt_close($stmt);


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
        p.name AS project_name,
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

// Attention counts
$pendingReviewCount = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE p.supervisor_id = ?
      AND f.status = 'Unviewed'
      AND f.task_id IS NOT NULL
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$pendingReviewCount = (int) $rc['c'];
mysqli_stmt_close($stmt);

$pendingRequestCount = 0;
$overdueTaskCount = 0;
foreach ($teams as $t) {
    $pendingRequestCount += (int) $t['pending_requests'];
    $overdueTaskCount    += (int) $t['overdue_count'];
}

// Assigned students who have never uploaded any file
$notSubmittedStudents = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(DISTINCT ts.student_id) AS c
    FROM `teacher_student` ts
    WHERE ts.teacher_id = ?
      AND ts.student_id NOT IN (SELECT DISTINCT uploader_id FROM `files`)
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$notSubmittedStudents = (int) $rc['c'];
mysqli_stmt_close($stmt);

// Formation status
$formationCounts = array(
    'Waiting Approval' => 0,
    'Voting'           => 0,
    'Executing'        => 0,
);

foreach ($teams as $t) {
    if ((int) $t['pending_requests'] > 0) {
        $formationCounts['Waiting Approval']++;
    } elseif ($t['status'] === 'Voting') {
        $formationCounts['Voting']++;
    } elseif ($t['status'] === 'Executing') {
        $formationCounts['Executing']++;
    }
}

// Teams that need attention
$attentionTeams = array();
foreach ($teams as $t) {
    $reasons  = array();
    $priority = 99;

    if ((int) $t['pending_requests'] > 0) {
        $reasons[] = 'Pending approval';
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
$attentionTeams = array_slice($attentionTeams, 0, 5);

// Upcoming deadlines: tasks + milestones (includes overdue)
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
      AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY t.due_date ASC
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $upcomingDeadlines[] = $row; }
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "
    SELECT m.name AS title, m.end_date AS due_date, NULL AS group_name,
           p.name AS project_name, 'milestone' AS item_type
    FROM `milestones` m
    INNER JOIN `projects` p ON p.id = m.project_id
    WHERE p.supervisor_id = ?
      AND m.status <> 'Completed'
      AND m.end_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY m.end_date ASC
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $upcomingDeadlines[] = $row; }
mysqli_stmt_close($stmt);

usort($upcomingDeadlines, function ($a, $b) {
    return strtotime($a['due_date']) <=> strtotime($b['due_date']);
});
$upcomingDeadlines = array_slice($upcomingDeadlines, 0, 8);

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
    <link rel="stylesheet" href="teacher.css?v=20261028a">
    <style>
        .summary-grid { grid-template-columns: repeat(3, 1fr); }
        .summary-item .summary-note {
            margin-top: 3px;
            color: #8b8f98;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .attention-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 30px;
        }
        .attention-card {
            display: block;
            padding: 16px;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
        }
        .attention-card:hover {
            border-color: #b9cfe9;
            box-shadow: 0 6px 18px rgba(22, 24, 29, 0.07);
            transform: translateY(-1px);
        }
        .attention-icon {
            width: 34px;
            height: 34px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef2f7;
            color: #175196;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .attention-number {
            color: #2c2f36;
            font-size: 28px;
            font-weight: 600;
            line-height: 1;
        }
        .attention-label {
            margin-top: 5px;
            color: #2c2f36;
            font-size: 12px;
            font-weight: 600;
        }
        .attention-sub {
            margin-top: 3px;
            color: #8b8f98;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .dashboard-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-bottom: 30px;
        }
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

        .formation-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            border-bottom: 1px solid #f2f3f5;
            color: #4b5058;
            font-size: 12.5px;
        }
        .formation-row:last-child { border-bottom: none; }
        .formation-row span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .formation-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        .formation-dot.waiting { background: #e0a030; }
        .formation-dot.voting { background: #8b6fd6; }
        .formation-dot.executing { background: #175196; }

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

        @media (max-width: 900px) {
            .attention-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-columns { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .attention-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 700px) {
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Instructor Dashboard</h1>
        <p class="subtitle">ITE4116M Final Year Project · 2026/27</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Assigned Students</div>
                <div class="value"><?php echo $totalStudents; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">My Projects</div>
                <div class="value"><?php echo $myProjects; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Phases Not Submitted</div>
                <div class="value"><?php echo $latePhases; ?></div>
                <div class="summary-note">
                    <?php echo $dueTodayPhases; ?> due today · <?php echo $overduePhases; ?> overdue
                </div>
            </div>
        </div>

        <!-- Needs Attention -->
        <h2>Needs Attention</h2>
        <div class="attention-grid">
            <a href="teacher_review.php" class="attention-card">
                <div class="attention-icon">REV</div>
                <div class="attention-number"><?php echo (int) $pendingReviewCount; ?></div>
                <div class="attention-label">Pending Reviews</div>
                <div class="attention-sub">Task attachments waiting</div>
            </a>
            <a href="teacher_review.php#requests" class="attention-card">
                <div class="attention-icon">GRP</div>
                <div class="attention-number"><?php echo (int) $pendingRequestCount; ?></div>
                <div class="attention-label">Group Requests</div>
                <div class="attention-sub">Waiting for your approval</div>
            </a>
            <a href="teacher_teams.php" class="attention-card">
                <div class="attention-icon">DUE</div>
                <div class="attention-number"><?php echo (int) $overdueTaskCount; ?></div>
                <div class="attention-label">Overdue Tasks</div>
                <div class="attention-sub">Across your project teams</div>
            </a>
            <a href="teacher_students.php" class="attention-card">
                <div class="attention-icon">STU</div>
                <div class="attention-number"><?php echo (int) $notSubmittedStudents; ?></div>
                <div class="attention-label">Students Not Submitted</div>
                  <div class="attention-sub">No uploads in your projects</div>
            </a>
        </div>

        <div class="dashboard-columns">
            <!-- Upcoming Deadlines -->
            <section>
                <h2>Upcoming Deadlines</h2>
                <div class="card compact-card">
                    <?php if (count($upcomingDeadlines) === 0): ?>
                        <div class="empty-note">No deadlines in the next 7 days.</div>
                    <?php else: ?>
                        <ul class="deadline-list">
                            <?php foreach ($upcomingDeadlines as $d): ?>
                                <?php $dl = dashboardDeadlineLabel($d['due_date']); ?>
                                <li>
                                    <div class="deadline-main">
                                        <div class="deadline-title"><?php echo htmlspecialchars($d['title']); ?></div>
                                        <div class="deadline-meta">
                                            <?php if ($d['item_type'] === 'task'): ?>
                                                <?php echo htmlspecialchars($d['group_name']); ?> ·
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($d['project_name']); ?>
                                            · <?php echo date('d M', strtotime($d['due_date'])); ?>
                                        </div>
                                    </div>
                                    <span class="deadline-badge <?php echo $dl['class']; ?>">
                                        <?php echo htmlspecialchars($dl['label']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Group Formation -->
            <section>
                <h2>Group Formation</h2>
                <div class="card compact-card">
                    <div class="formation-row">
                        <span><i class="formation-dot waiting"></i>Approval Pending</span>
                        <strong><?php echo (int) $formationCounts['Waiting Approval']; ?></strong>
                    </div>
                    <div class="formation-row">
                        <span><i class="formation-dot voting"></i>Choosing Leader</span>
                        <strong><?php echo (int) $formationCounts['Voting']; ?></strong>
                    </div>
                    <div class="formation-row">
                        <span><i class="formation-dot executing"></i>Work in Progress</span>
                        <strong><?php echo (int) $formationCounts['Executing']; ?></strong>
                    </div>
                </div>
            </section>
        </div>

        <!-- Teams needing attention -->
        <h2>Teams Needing Attention</h2>
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
                        <td><?php echo htmlspecialchars($t['project_name']); ?></td>
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

        <!-- Activity -->
        <h2>Recent Activity</h2>
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

    </div>
</main>

</body>
</html>
