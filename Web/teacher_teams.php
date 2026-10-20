<?php
$active = 'teams';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$teacherId = 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
if ($row) {
    $teacherId = (int) $row['id'];
}

if ($teacherId <= 0) {
    header('Location: teacher_login.php');
    exit;
}

// =========================================================
// All supervised teams
// =========================================================
$allTeams = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        g.id,
        g.name,
        g.status,
        g.formed_at,
        g.max_members,
        g.project_id,
        p.name AS project_name,
        u.username AS leader_name,
        (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count,
        (SELECT COUNT(*) FROM `tasks` t
            WHERE t.group_id = g.id
              AND t.status <> 'Completed'
              AND t.due_date < CURDATE()) AS overdue_count,
        (SELECT COUNT(*) FROM `tasks` t2
            WHERE t2.group_id = g.id AND t2.status = 'Completed') AS completed_tasks,
        (SELECT COUNT(*) FROM `tasks` t3 WHERE t3.group_id = g.id) AS total_tasks,
        (SELECT MAX(a.created_at) FROM `activities` a WHERE a.group_id = g.id) AS last_activity,
        (SELECT COUNT(*) FROM `group_requests` r
            WHERE r.project_id = g.project_id
              AND r.group_name = g.name
              AND r.status = 'Pending') AS pending_requests
    FROM `groups` g
    INNER JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `students` s ON s.id = g.leader_id
    LEFT JOIN `users` u ON u.id = s.user_id
    WHERE p.supervisor_id = ?
    ORDER BY g.id
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($team = mysqli_fetch_assoc($rs)) {
    $allTeams[] = $team;
}
mysqli_stmt_close($stmt);

// =========================================================
// Summary + status counts + attention reasons
// =========================================================
$totalTeams = count($allTeams);
$awaitingApproval = 0;
$activeTeams = 0;
$overdueTasks = 0;

$statusCounts = array(
    'all'        => $totalTeams,
    'pending'    => 0,
    'Recruiting' => 0,
    'Voting'     => 0,
    'Executing'  => 0,
    'Completed'  => 0,
);

foreach ($allTeams as &$team) {
    $pending = (int) $team['pending_requests'];
    $overdue = (int) $team['overdue_count'];

    if ($pending > 0) {
        $awaitingApproval++;
        $statusCounts['pending']++;
    } else {
        if ($team['status'] === 'Voting' || $team['status'] === 'Executing') {
            $activeTeams++;
        }
        if (isset($statusCounts[$team['status']])) {
            $statusCounts[$team['status']]++;
        }
    }

    $overdueTasks += $overdue;

    $reasons  = array();
    $priority = 5;

    if ($pending > 0) {
        // The status pill already says "Pending Approval".
        $priority = min($priority, 1);
    }
    if ($overdue > 0) {
        $reasons[] = $overdue . ' overdue task' . ($overdue === 1 ? '' : 's');
        $priority  = min($priority, 2);
    }
    if ($pending === 0 && $team['status'] === 'Recruiting') {
        $priority = min($priority, 3);
    }
    if ($pending === 0 && $team['status'] === 'Waiting_Send') {
        $priority = min($priority, 3);
    }
    if (empty($team['last_activity'])
        || strtotime($team['last_activity']) < strtotime('-7 days')) {
        $reasons[] = 'No activity for 7+ days';
        $priority  = min($priority, 4);
    }

    $team['reasons']  = $reasons;
    $team['priority'] = $priority;
}
unset($team);

// =========================================================
// Search / status filter
// =========================================================
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : 'all';

$validStatuses = array('all', 'pending', 'Recruiting', 'Voting', 'Executing', 'Completed');
if (!in_array($status, $validStatuses, true)) {
    $status = 'all';
}

$filteredTeams = array();
foreach ($allTeams as $team) {
    $pending = (int) $team['pending_requests'];

    if ($status === 'pending' && $pending <= 0) {
        continue;
    }
    if (in_array($status, array('Recruiting', 'Voting', 'Executing', 'Completed'), true)) {
        if ($pending > 0 || $team['status'] !== $status) {
            continue;
        }
    }

    if ($search !== '') {
        $haystack = $team['name'] . ' ' . $team['project_name'] . ' ' . (string) $team['leader_name'];
        if (stripos($haystack, $search) === false) {
            continue;
        }
    }

    $filteredTeams[] = $team;
}

usort($filteredTeams, function ($a, $b) {
    if ($a['priority'] !== $b['priority']) {
        return $a['priority'] <=> $b['priority'];
    }
    if ((int) $a['overdue_count'] !== (int) $b['overdue_count']) {
        return (int) $b['overdue_count'] <=> (int) $a['overdue_count'];
    }
    return strcmp($a['name'], $b['name']);
});

if (!function_exists('teamCardInitials')) {
    function teamCardInitials($name)
    {
        if (preg_match('/^Team\s*0*(\d+)/i', trim($name), $m)) {
            return 'T' . $m[1];
        }
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}

$chipDefs = array(
    'all'        => 'All',
    'pending'    => 'Pending Approval',
    'Recruiting' => 'Recruiting',
    'Voting'     => 'Voting',
    'Executing'  => 'Executing',
    'Completed'  => 'Completed',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261020d">
    <style>
        .team-toolbar {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .team-toolbar .field-search { flex: 1 1 260px; }
        .team-toolbar .field-search input {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #dfe2e6;
            border-radius: 7px;
            font-size: 12.5px;
        }
        .team-toolbar .btn-small { height: 36px; }

        .status-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 24px;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border: 1px solid #e2e4e8;
            border-radius: 999px;
            background: #fff;
            color: #6b6f78;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }
        .status-chip span {
            color: #9aa0a8;
            font-size: 10px;
        }
        .status-chip:hover { border-color: #b9cfe9; color: #175196; }
        .status-chip.active {
            background: #175196;
            border-color: #175196;
            color: #fff;
        }
        .status-chip.active span { color: #cfe0f5; }

        .team-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 18px 20px;
            margin-bottom: 14px;
            border: 1px solid #e6e8eb;
            border-left: 4px solid transparent;
            border-radius: 12px;
            background: #fff;
            flex-wrap: wrap;
        }
        .team-card.attention-pending { border-left-color: #e0a030; }
        .team-card.attention-overdue { border-left-color: #d32f2f; }

        .team-main {
            display: flex;
            gap: 13px;
            flex: 1.4 1 280px;
            min-width: 0;
        }
        .team-avatar {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8eef7;
            color: #175196;
            font-size: 12px;
            font-weight: 700;
        }
        .team-info { min-width: 0; }
        .team-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .team-title-row h3 {
            margin: 0;
            color: #2c2f36;
            font-size: 14px;
            font-weight: 600;
        }
        .team-meta {
            margin-top: 3px;
            color: #8b8f98;
            font-size: 11px;
            line-height: 1.5;
        }
        .team-meta a { color: #175196; text-decoration: none; }
        .team-meta a:hover { text-decoration: underline; }

        .team-reasons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 7px;
        }
        .reason-chip {
            padding: 2px 7px;
            border-radius: 999px;
            background: #f4f6f9;
            color: #4b5058;
            font-size: 9.5px;
            font-weight: 600;
        }
        .reason-chip.danger { background: #fdecec; color: #b02a2a; }
        .reason-chip.warning { background: #fdf3e2; color: #9a6a13; }

        .status-label {
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }
        .status-pending    { background: #fdf3e2; color: #9a6a13; }
        .status-recruiting { background: #eef2f7; color: #4b5058; }
        .status-voting     { background: #f1ecfb; color: #6a4fb3; }
        .status-executing  { background: #e8f0fa; color: #175196; }
        .status-completed  { background: #e8f6ee; color: #256b3f; }
        .status-waiting    { background: #fdf3e2; color: #9a6a13; }

        .team-progress {
            flex: 1 1 190px;
            max-width: 300px;
        }
        .progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
            color: #8b8f98;
            font-size: 10.5px;
        }
        .progress-head strong { color: #4b5058; font-size: 11px; }
        .progress-track {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: #eef0f3;
        }
        .progress-fill {
            height: 100%;
            border-radius: 999px;
            background: #175196;
        }
        .team-activity {
            margin-top: 6px;
            color: #9aa0a8;
            font-size: 10px;
        }

        .team-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
            justify-content: flex-end;
            flex: 0 1 auto;
        }
        .btn-small {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 11px;
            border: 1px solid #d8e2ee;
            border-radius: 7px;
            background: #fff;
            color: #175196;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-small.primary {
            background: #175196;
            border-color: #175196;
            color: #fff;
        }
        .btn-small:hover { background: #f4f8fd; }
        .btn-small.primary:hover { background: #123f76; }

        .teams-empty {
            padding: 36px 20px;
            border: 1px solid #e6e8eb;
            border-radius: 12px;
            background: #fff;
            color: #8b8f98;
            font-size: 12.5px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .team-card { align-items: flex-start; }
            .team-progress { flex-basis: 100%; max-width: none; }
            .team-actions { justify-content: flex-start; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Teams</h1>
        <p class="subtitle">Project teams under your supervision · 2026/27</p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Teams</div>
                <div class="value"><?php echo $totalTeams; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Awaiting Approval</div>
                <div class="value"><?php echo $awaitingApproval; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Active Teams</div>
                <div class="value"><?php echo $activeTeams; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Overdue Tasks</div>
                <div class="value"><?php echo $overdueTasks; ?></div>
            </div>
        </div>

        <form method="get" action="teacher_teams.php" class="team-toolbar">
            <div class="field field-search">
                <label for="teamSearch">Search</label>
                <input type="search" id="teamSearch" name="q"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search team, project or leader">
            </div>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
            <button type="submit" class="btn-small primary">Search</button>
            <?php if ($search !== '' || $status !== 'all'): ?>
                <a href="teacher_teams.php" class="btn-small">Reset</a>
            <?php endif; ?>
        </form>

        <div class="status-chips">
            <?php foreach ($chipDefs as $key => $label): ?>
                <?php
                $chipUrl = 'teacher_teams.php?' . http_build_query(array(
                    'status' => $key,
                    'q'      => $search,
                ));
                ?>
                <a href="<?php echo htmlspecialchars($chipUrl); ?>"
                   class="status-chip <?php echo $status === $key ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($label); ?>
                    <span><?php echo (int) $statusCounts[$key]; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (count($filteredTeams) === 0): ?>
            <div class="teams-empty">No teams match your search or filter.</div>
        <?php endif; ?>

        <?php foreach ($filteredTeams as $team): ?>
            <?php
            $pending  = (int) $team['pending_requests'];
            $overdue  = (int) $team['overdue_count'];
            $done     = (int) $team['completed_tasks'];
            $total    = (int) $team['total_tasks'];
            $pct      = $total > 0 ? (int) round($done / $total * 100) : 0;

            if ($pending > 0) {
                $displayStatus = 'Pending Approval';
                $statusClass   = 'pending';
            } elseif ($team['status'] === 'Waiting_Send') {
                $displayStatus = 'Waiting to Submit';
                $statusClass   = 'waiting';
            } else {
                $displayStatus = $team['status'];
                $statusClass   = strtolower($team['status']);
            }

            $attentionClass = '';
            if ($overdue > 0) {
                $attentionClass = 'attention-overdue';
            } elseif ($pending > 0) {
                $attentionClass = 'attention-pending';
            }

            $canMessage = ($pending === 0
                && in_array($team['status'], array('Voting', 'Executing'), true));
            ?>
            <article class="team-card <?php echo $attentionClass; ?>">
                <div class="team-main">
                    <div class="team-avatar"><?php echo htmlspecialchars(teamCardInitials($team['name'])); ?></div>
                    <div class="team-info">
                        <div class="team-title-row">
                            <h3><?php echo htmlspecialchars($team['name']); ?></h3>
                            <span class="status-label status-<?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($displayStatus); ?>
                            </span>
                        </div>
                        <div class="team-meta">
                            <a href="project_detail.php?id=<?php echo (int) $team['project_id']; ?>">
                                <?php echo htmlspecialchars($team['project_name']); ?>
                            </a>
                            · Leader <?php echo htmlspecialchars($team['leader_name'] ?: '—'); ?>
                            · <?php echo (int) $team['member_count']; ?> / <?php echo (int) $team['max_members']; ?> members
                        </div>
                        <?php if (count($team['reasons']) > 0): ?>
                            <div class="team-reasons">
                                <?php foreach ($team['reasons'] as $reason): ?>
                                    <?php
                                    $reasonClass = '';
                                    if (stripos($reason, 'overdue') !== false) {
                                        $reasonClass = 'danger';
                                    } elseif (stripos($reason, 'Pending') !== false
                                        || stripos($reason, 'Waiting') !== false
                                        || stripos($reason, 'No activity') !== false
                                        || stripos($reason, 'Recruiting') !== false) {
                                        $reasonClass = 'warning';
                                    }
                                    ?>
                                    <span class="reason-chip <?php echo $reasonClass; ?>">
                                        <?php echo htmlspecialchars($reason); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="team-progress">
                    <div class="progress-head">
                        <span>Task progress</span>
                        <strong>
                            <?php echo $total > 0 ? $done . ' / ' . $total : 'No tasks yet'; ?>
                        </strong>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: <?php echo $pct; ?>%;"></div>
                    </div>
                    <div class="team-activity">
                        Last activity: <?php echo htmlspecialchars(timeAgo($team['last_activity'])); ?>
                    </div>
                </div>

                <div class="team-actions">
                    <a href="team_detail.php?id=<?php echo (int) $team['id']; ?>" class="btn-small primary">View</a>
                    <?php if ($pending > 0): ?>
                        <a href="teacher_review.php#requests" class="btn-small">Review request</a>
                    <?php elseif ($canMessage): ?>
                        <a href="teacher_messages.php?group_id=<?php echo (int) $team['id']; ?>" class="btn-small">Message team</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

    </div>
</main>

</body>
</html>
