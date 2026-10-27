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
        p.category AS project_category,
        cl.class_code,
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
    LEFT JOIN `classes` cl ON cl.id = p.class_id
    LEFT JOIN `students` s ON s.id = g.leader_id
    LEFT JOIN `users` u ON u.id = s.user_id
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
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($team = mysqli_fetch_assoc($rs)) {
    $allTeams[] = $team;
}
mysqli_stmt_close($stmt);

// Classes taught by this teacher, including classes without teams yet.
$teacherClasses = array();
$stmt = mysqli_prepare($conn, "
    SELECT class_code
    FROM `classes`
    WHERE teacher_id = ?
    ORDER BY class_code
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($classRow = mysqli_fetch_assoc($rs)) {
    $teacherClasses[] = $classRow['class_code'];
}
mysqli_stmt_close($stmt);

$teacherCategories = array();
$stmt = mysqli_prepare($conn, "
    SELECT DISTINCT category
    FROM `projects`
    WHERE supervisor_id = ?
      AND is_draft = 0
      AND category IS NOT NULL
      AND category <> ''
    ORDER BY category
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($categoryRow = mysqli_fetch_assoc($rs)) {
    $teacherCategories[] = $categoryRow['category'];
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
$classFilter = isset($_GET['class']) ? trim($_GET['class']) : 'all';
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'all';

$validStatuses = array('all', 'pending', 'Voting', 'Executing', 'Completed');
if (!in_array($status, $validStatuses, true)) {
    $status = 'all';
}
if ($classFilter !== 'all' && !in_array($classFilter, $teacherClasses, true)) {
    $classFilter = 'all';
}
if ($categoryFilter !== 'all' && !in_array($categoryFilter, $teacherCategories, true)) {
    $categoryFilter = 'all';
}

// Keep status-chip counts relevant to the selected class.
if ($classFilter !== 'all') {
    foreach ($statusCounts as $key => $value) {
        $statusCounts[$key] = 0;
    }
    foreach ($allTeams as $team) {
        if ($team['class_code'] !== $classFilter) {
            continue;
        }
        $statusCounts['all']++;
        if ((int) $team['pending_requests'] > 0) {
            $statusCounts['pending']++;
        } elseif (isset($statusCounts[$team['status']])) {
            $statusCounts[$team['status']]++;
        }
    }
}

$filteredTeams = array();
foreach ($allTeams as $team) {
    $pending = (int) $team['pending_requests'];

    if ($classFilter !== 'all' && $team['class_code'] !== $classFilter) {
        continue;
    }
    if ($categoryFilter !== 'all' && $team['project_category'] !== $categoryFilter) {
        continue;
    }
    if ($status === 'pending' && $pending <= 0) {
        continue;
    }
    if (in_array($status, array('Voting', 'Executing', 'Completed'), true)) {
        if ($pending > 0 || $team['status'] !== $status) {
            continue;
        }
    }

    if ($search !== '') {
        $haystack = $team['name'] . ' ' . $team['project_name'] . ' '
            . $team['project_category'] . ' ' . (string) $team['leader_name'];
        if (stripos($haystack, $search) === false) {
            continue;
        }
    }

    $filteredTeams[] = $team;
}

usort($filteredTeams, function ($a, $b) {
    $classCompare = strnatcasecmp($a['class_code'] ?: '', $b['class_code'] ?: '');
    if ($classCompare !== 0) {
        return $classCompare;
    }
    $projectCompare = strcasecmp($a['project_name'], $b['project_name']);
    if ($projectCompare !== 0) {
        return $projectCompare;
    }
    return strnatcasecmp($a['name'], $b['name']);
});

$teamStatusLabels = array(
    'Waiting_Send' => 'Ready for Approval',
    'Voting'       => 'Choosing Leader',
    'Executing'    => 'Work in Progress',
    'Completed'    => 'Completed',
);

$chipDefs = array(
    'all'        => 'All',
    'pending'    => 'Approval Pending',
    'Voting'     => $teamStatusLabels['Voting'],
    'Executing'  => $teamStatusLabels['Executing'],
    'Completed'  => $teamStatusLabels['Completed'],
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261028a">
    <style>
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 16px;
            overflow: hidden;
            border: 1px solid #17191d;
            border-radius: 9px;
            background: #17191d;
        }
        .kpi-item {
            min-width: 0;
            padding: 13px 16px;
            border-right: 1px solid #2b3038;
        }
        .kpi-item:last-child { border-right: none; }
        .kpi-label {
            overflow: hidden;
            color: #abb1ba;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .kpi-value {
            margin-top: 4px;
            color: #fff;
            font-size: 25px;
            font-variant-numeric: tabular-nums;
            font-weight: 650;
            line-height: 1.1;
        }
        .kpi-item.alert .kpi-value { color: #ff8f8f; }

        .teams-control-bar {
            position: sticky;
            top: 12px;
            z-index: 6;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #e2e6eb;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 4px 14px rgba(22, 32, 45, 0.05);
            backdrop-filter: blur(8px);
        }
        .team-search {
            display: flex;
            flex: 1 0 100%;
            flex-wrap: wrap;
            gap: 6px;
            min-width: 0;
        }
        .team-search input {
            flex: 1 1 180px;
            width: 100%;
            height: 36px;
            padding: 0 10px;
            border: 1px solid #dce1e7;
            border-radius: 6px;
            background: #fff;
            color: #2c3138;
            font-family: inherit;
            font-size: 13px;
        }
        .team-search input::placeholder { color: #8e949d; }
        .class-filter {
            flex: 0 0 120px;
            min-width: 0;
        }
        .category-filter { flex-basis: 144px; }
        .class-filter select {
            width: 100%;
            height: 36px;
            padding: 0 8px;
            border: 1px solid #dce1e7;
            border-radius: 6px;
            background: #fff;
            color: #39414b;
            font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
            font-size: 11px;
            font-weight: 600;
        }
        .team-search input:focus,
        .class-filter select:focus,
        .segmented-filter a:focus-visible,
        .team-action:focus-visible {
            outline: 2px solid #6da5de;
            outline-offset: 2px;
        }

        .segmented-filter {
            display: flex;
            flex: 1 0 100%;
            gap: 2px;
            max-width: 100%;
            padding: 2px;
            overflow-x: auto;
            border: 1px solid #e3e6ea;
            border-radius: 6px;
            background: #f3f5f7;
            scrollbar-width: thin;
        }
        .segmented-filter a {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 8px;
            border-radius: 4px;
            color: #626872;
            font-size: 10.5px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }
        .segmented-filter a span {
            color: #949aa3;
            font-size: 10px;
            font-variant-numeric: tabular-nums;
        }
        .segmented-filter a:hover { color: #1e293b; background: #e9edf1; }
        .segmented-filter a.active {
            color: #fff;
            background: #263447;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.15);
        }
        .segmented-filter a.active span { color: #cbd5e1; }

        .teams-list-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 12px 0 7px;
            color: #7f858e;
            font-size: 10.5px;
        }
        .teams-list-meta strong {
            color: #4b5563;
            font-weight: 600;
        }
        .sort-label {
            color: #8b9199;
            font-size: 9.5px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .teams-panel {
            overflow: hidden;
            border: 1px solid #e1e5ea;
            border-radius: 9px;
            background: #fff;
        }
        .team-table-head,
        .team-row {
            display: grid;
            grid-template-columns: minmax(125px, 1fr) minmax(180px, 1.5fr) 96px minmax(122px, 1fr);
            gap: 10px;
            align-items: center;
        }
        .team-table-head {
            padding: 8px 14px;
            border-bottom: 1px solid #e5e9ed;
            background: #f7f9fb;
            color: #858b94;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .team-row {
            position: relative;
            min-height: 72px;
            padding: 11px 14px;
            border-bottom: 1px solid #edf0f3;
            box-shadow: inset 3px 0 0 transparent;
            transition: background 0.14s, box-shadow 0.14s;
        }
        .team-row:last-child { border-bottom: none; }
        .team-row:hover { background: #fafbfc; }
        .team-row.attention-overdue { box-shadow: inset 3px 0 0 #cf3f3f; }

        .team-identity,
        .project-cell,
        .progress-cell,
        .team-actions-cell { min-width: 0; }
        .team-name {
            display: block;
            overflow: hidden;
            color: #20252c;
            font-size: 13.5px;
            font-weight: 700;
            line-height: 1.35;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .team-name:hover { color: #175196; text-decoration: underline; }
        .team-class {
            display: inline;
            color: #7f858e;
            font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
            font-size: 9.5px;
            letter-spacing: 0.02em;
        }
        .team-subline {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 3px;
            color: #6b7280;
            font-size: 10px;
        }
        .team-status-inline {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #59616c;
            font-size: 10px;
            font-weight: 600;
        }
        .team-status-inline::before {
            width: 5px;
            height: 5px;
            flex: 0 0 5px;
            border-radius: 50%;
            background: #9aa1ab;
            content: "";
        }
        .team-subline-separator {
            color: #b2b8c0;
            font-size: 9px;
        }
        .project-name {
            display: block;
            overflow: hidden;
            color: #384150;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .project-name:hover { color: #175196; text-decoration: underline; }
        .team-meta-line {
            display: block;
            overflow: hidden;
            margin-top: 2px;
            color: #858b94;
            font-size: 10px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .progress-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 5px;
            color: #6f7680;
            font-size: 10.5px;
        }
        .progress-line strong {
            color: #343b45;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
        }
        .progress-track {
            height: 4px;
            overflow: hidden;
            border-radius: 999px;
            background: #e9edf1;
        }
        .progress-fill {
            height: 100%;
            border-radius: inherit;
            background: #2f6fa8;
        }
        .activity-line {
            margin-top: 4px;
            color: #969ca4;
            font-size: 9.5px;
        }
        .activity-line.danger { color: #b42318; font-weight: 600; }

        .team-actions-cell {
            display: flex;
            justify-content: flex-start;
            gap: 5px;
        }
        .team-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 6px 9px;
            border: 1px solid #d8dee5;
            border-radius: 5px;
            background: #fff;
            color: #4b5563;
            font-family: inherit;
            font-size: 10.5px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }
        .team-action:hover { border-color: #aab7c4; background: #f8fafc; }
        .team-action.primary {
            border-color: #1f4d78;
            background: #1f4d78;
            color: #fff;
        }
        .team-action.primary:hover { border-color: #183d61; background: #183d61; }

        .teams-empty {
            padding: 42px 20px;
            color: #7f858e;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .kpi-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .kpi-item:nth-child(2) { border-right: none; }
            .kpi-item:nth-child(-n+2) { border-bottom: 1px solid #2b3038; }
            .teams-control-bar {
                position: static;
                align-items: stretch;
                flex-direction: column;
            }
            .team-table-head { display: none; }
            .team-row {
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "identity identity"
                    "project project"
                    "progress actions";
                gap: 9px 14px;
                padding: 12px 14px;
            }
            .team-identity { grid-area: identity; }
            .project-cell { grid-area: project; }
            .progress-cell { grid-area: progress; }
            .team-actions-cell { grid-area: actions; justify-content: flex-start; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Teams</h1>
        <p class="subtitle">Project teams under your supervision · 2026/27</p>

        <div class="kpi-strip">
            <div class="kpi-item">
                <div class="kpi-label">Total Teams</div>
                <div class="kpi-value"><?php echo $totalTeams; ?></div>
            </div>
            <div class="kpi-item">
                <div class="kpi-label">Approval Pending</div>
                <div class="kpi-value"><?php echo $awaitingApproval; ?></div>
            </div>
            <div class="kpi-item">
                <div class="kpi-label">Active Teams</div>
                <div class="kpi-value"><?php echo $activeTeams; ?></div>
            </div>
            <div class="kpi-item alert">
                <div class="kpi-label">Overdue Tasks</div>
                <div class="kpi-value"><?php echo $overdueTasks; ?></div>
            </div>
        </div>

        <div class="teams-control-bar">
            <form method="get" action="teacher_teams.php" class="team-search">
                <label for="teamSearch" class="sr-only">Search teams</label>
                <input type="search" id="teamSearch" name="q"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search team, project or leader">
                <div class="class-filter">
                    <label for="classFilter" class="sr-only">Filter by class</label>
                    <select id="classFilter" name="class" onchange="this.form.submit()">
                        <option value="all" <?php echo $classFilter === 'all' ? 'selected' : ''; ?>>All classes</option>
                        <?php foreach ($teacherClasses as $classCode): ?>
                            <option value="<?php echo htmlspecialchars($classCode); ?>"
                                <?php echo $classFilter === $classCode ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($classCode); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="class-filter category-filter">
                    <label for="categoryFilter" class="sr-only">Filter by category</label>
                    <select id="categoryFilter" name="category" onchange="this.form.submit()">
                        <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All categories</option>
                        <?php foreach ($teacherCategories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>"
                                <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <button type="submit" class="team-action primary">Search</button>
                <?php if ($search !== '' || $status !== 'all' || $classFilter !== 'all' || $categoryFilter !== 'all'): ?>
                    <a href="teacher_teams.php" class="team-action">Reset</a>
                <?php endif; ?>
            </form>

            <nav class="segmented-filter" aria-label="Filter teams by status">
                <?php foreach ($chipDefs as $key => $label): ?>
                    <?php
                    $chipUrl = 'teacher_teams.php?' . http_build_query(array(
                        'status' => $key,
                        'q'      => $search,
                        'class'  => $classFilter,
                        'category' => $categoryFilter,
                    ));
                    ?>
                    <a href="<?php echo htmlspecialchars($chipUrl); ?>"
                       class="<?php echo $status === $key ? 'active' : ''; ?>"
                       <?php echo $status === $key ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                        <span><?php echo (int) $statusCounts[$key]; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="teams-list-meta">
            <span>
                Showing <strong><?php echo count($filteredTeams); ?></strong>
                of <?php echo $classFilter === 'all' ? $totalTeams : $statusCounts['all']; ?> teams
                <?php if ($classFilter !== 'all'): ?>
                    · <strong><?php echo htmlspecialchars($classFilter); ?></strong>
                <?php endif; ?>
            </span>
            <span class="sort-label">Class / project sorted</span>
        </div>

        <div class="teams-panel">
            <div class="team-table-head" aria-hidden="true">
                <span>Team</span>
                <span>Project / Team</span>
                <span>Progress</span>
                <span>Actions</span>
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
                    $displayStatus = 'Approval Pending';
                } elseif ($team['status'] === 'Waiting_Send') {
                    $displayStatus = $teamStatusLabels['Waiting_Send'];
                } else {
                    $displayStatus = $teamStatusLabels[$team['status']] ?? $team['status'];
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
                <div class="team-row <?php echo $attentionClass; ?>">
                    <div class="team-identity">
                        <a href="team_detail.php?id=<?php echo (int) $team['id']; ?>" class="team-name">
                            <?php echo htmlspecialchars($team['name']); ?>
                        </a>
                        <span class="team-subline">
                            <span class="team-status-inline"><?php echo htmlspecialchars($displayStatus); ?></span>
                            <span class="team-subline-separator">·</span>
                            <span class="team-class"><?php echo htmlspecialchars($team['class_code'] ?: 'NO CLASS'); ?></span>
                        </span>
                    </div>

                    <div class="project-cell">
                        <a href="project_detail.php?id=<?php echo (int) $team['project_id']; ?>" class="project-name">
                            <?php echo htmlspecialchars($team['project_name']); ?>
                        </a>
                        <span class="team-meta-line" title="Leader and member count">
                            Leader <?php echo htmlspecialchars($team['leader_name'] ?: '—'); ?> ·
                            <?php echo (int) $team['member_count']; ?> / <?php echo (int) $team['max_members']; ?> members
                        </span>
                    </div>

                    <div class="progress-cell">
                        <div class="progress-line">
                            <strong><?php echo $total > 0 ? $done . ' / ' . $total : 'No tasks'; ?></strong>
                            <span><?php echo $pct; ?>%</span>
                        </div>
                        <div class="progress-track" aria-label="Task progress">
                            <div class="progress-fill" style="width: <?php echo $pct; ?>%;"></div>
                        </div>
                        <div class="activity-line <?php echo $overdue > 0 ? 'danger' : ''; ?>">
                            <?php echo $overdue > 0
                                ? $overdue . ' overdue'
                                : htmlspecialchars(timeAgo($team['last_activity'])); ?>
                        </div>
                    </div>

                    <div class="team-actions-cell">
                        <?php if ($pending > 0): ?>
                            <a href="teacher_review.php#requests"
                               class="team-action primary"
                               aria-label="Review request for <?php echo htmlspecialchars($team['name']); ?>">Review</a>
                            <a href="team_detail.php?id=<?php echo (int) $team['id']; ?>"
                               class="team-action">Open</a>
                        <?php else: ?>
                            <a href="team_detail.php?id=<?php echo (int) $team['id']; ?>"
                               class="team-action primary"
                               aria-label="Open <?php echo htmlspecialchars($team['name']); ?>">Open</a>
                            <?php if ($canMessage): ?>
                                <a href="teacher_messages.php?group_id=<?php echo (int) $team['id']; ?>"
                                   class="team-action">Message</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>

</body>
</html>
