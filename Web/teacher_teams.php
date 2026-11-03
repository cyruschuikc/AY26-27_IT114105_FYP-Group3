<?php
$active = 'teams';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/topic_helpers.php';
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
        p.topic_mode,
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
              AND r.status = 'Pending') AS pending_requests,
        (SELECT tp.topic_title
            FROM `team_topic_proposals` tp
            WHERE tp.group_id = g.id
            ORDER BY tp.version DESC, tp.id DESC
            LIMIT 1) AS topic_title,
        (SELECT tp.status
            FROM `team_topic_proposals` tp
            WHERE tp.group_id = g.id
            ORDER BY tp.version DESC, tp.id DESC
            LIMIT 1) AS topic_status,
        (SELECT pt.title
            FROM `project_topics` pt
            WHERE pt.project_id = p.id AND pt.is_active = 1
            ORDER BY pt.position, pt.id
            LIMIT 1) AS fixed_topic_title
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
$overdueTeams = 0;

$statusCounts = array(
        'all'        => $totalTeams,
        'pending'    => 0,
        'Voting'     => 0,
        'Executing'  => 0,
        'Completed'  => 0,
        'overdue'    => 0,
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
    if ($overdue > 0) {
        $overdueTeams++;
        $statusCounts['overdue']++;
    }

    $reasons  = array();
    $priority = 5;

    if ($pending > 0) {
        // The row status already says "Awaiting Approval".
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
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'project';

$validStatuses = array('all', 'pending', 'Voting', 'Executing', 'Completed', 'overdue');
if (!in_array($status, $validStatuses, true)) {
    $status = 'all';
}
if ($classFilter !== 'all' && !in_array($classFilter, $teacherClasses, true)) {
    $classFilter = 'all';
}
if ($categoryFilter !== 'all' && !in_array($categoryFilter, $teacherCategories, true)) {
    $categoryFilter = 'all';
}
if (!in_array($sort, array('project', 'team', 'overdue', 'recent'), true)) {
    $sort = 'project';
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
        if ((int) $team['overdue_count'] > 0) {
            $statusCounts['overdue']++;
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
    if ($status === 'overdue' && (int) $team['overdue_count'] <= 0) {
        continue;
    }

    if ($search !== '') {
        $haystack = $team['name'] . ' ' . $team['project_name'] . ' '
                . $team['project_category'] . ' ' . (string) $team['leader_name'] . ' '
                . (string) $team['topic_title'] . ' ' . (string) $team['fixed_topic_title'];
        if (stripos($haystack, $search) === false) {
            continue;
        }
    }

    $filteredTeams[] = $team;
}

// ★ 修正 1 & 2：<=> 係 PHP 7.0+，改用 if/else 比較
usort($filteredTeams, function ($a, $b) use ($sort) {
    if ($sort === 'team') {
        return strnatcasecmp($a['name'], $b['name']);
    }
    if ($sort === 'overdue') {
        $aVal = (int) $a['overdue_count'];
        $bVal = (int) $b['overdue_count'];
        if ($aVal < $bVal) return 1;
        if ($aVal > $bVal) return -1;
    }
    if ($sort === 'recent') {
        $aTs = strtotime((string) $a['last_activity']);
        $bTs = strtotime((string) $b['last_activity']);
        if ($aTs < $bTs) return 1;
        if ($aTs > $bTs) return -1;
    }
    $classCompare = strnatcasecmp($a['class_code'] ?: '', $b['class_code'] ?: '');
    if ($classCompare !== 0) return $classCompare;
    $projectCompare = strcasecmp($a['project_name'], $b['project_name']);
    if ($projectCompare !== 0) return $projectCompare;
    return strnatcasecmp($a['name'], $b['name']);
});

$teamStatusLabels = array(
        'Waiting_Send' => 'Ready to Submit',
        'Voting'       => 'Choosing Leader',
        'Executing'    => 'Active',
        'Completed'    => 'Completed',
);

$chipDefs = array(
        'all'        => 'All',
        'pending'    => 'Awaiting Approval',
        'Voting'     => $teamStatusLabels['Voting'],
        'Executing'  => $teamStatusLabels['Executing'],
        'Completed'  => $teamStatusLabels['Completed'],
);

$groupedTeams = array();
foreach ($filteredTeams as $team) {
    $projectKey = (int) $team['project_id'];
    if (!isset($groupedTeams[$projectKey])) {
        $groupedTeams[$projectKey] = array(
                'project_id' => $projectKey,
                'project_name' => $team['project_name'],
                'project_category' => $team['project_category'],
                'class_code' => $team['class_code'],
                'teams' => array(),
                'awaiting' => 0,
                'overdue_teams' => 0,
        );
    }
    $groupedTeams[$projectKey]['teams'][] = $team;
    if ((int) $team['pending_requests'] > 0) {
        $groupedTeams[$projectKey]['awaiting']++;
    }
    if ((int) $team['overdue_count'] > 0) {
        $groupedTeams[$projectKey]['overdue_teams']++;
    }
}
$hasFilters = ($search !== '' || $status !== 'all' || $classFilter !== 'all'
        || $categoryFilter !== 'all' || $sort !== 'project');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261028b">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=20261028b">
    <style>
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
            box-shadow: 0 3px 12px rgba(22, 32, 45, 0.04);
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
        .sort-filter { flex-basis: 128px; }
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
            flex: 1 1 auto;
            min-width: 0;
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
        .overdue-filter {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 5px;
            padding: 7px 9px;
            border: 1px solid #efcccc;
            border-radius: 6px;
            background: #fff7f7;
            color: #a23535;
            font-size: 10.5px;
            font-weight: 700;
            text-decoration: none;
        }
        .overdue-filter span {
            color: #b42318;
            font-variant-numeric: tabular-nums;
        }
        .overdue-filter:hover,
        .overdue-filter.active {
            border-color: #c95c5c;
            background: #fdecec;
        }

        .project-groups { display: grid; gap: 12px; }
        .project-group {
            overflow: hidden;
            border: 1px solid #e1e5ea;
            border-radius: 9px;
            background: #fff;
        }
        .project-group-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 11px 14px;
            border-bottom: 1px solid #edf0f3;
            background: #f8fafc;
            cursor: pointer;
            list-style: none;
        }
        .project-group-head::-webkit-details-marker { display: none; }
        .project-group-head::after {
            flex: 0 0 auto;
            color: #8e959e;
            font-size: 17px;
            content: "›";
            transform: rotate(90deg);
            transition: transform 0.15s ease;
        }
        .project-group[open] .project-group-head::after { transform: rotate(-90deg); }
        .project-group-title { min-width: 0; }
        .project-group-title > span {
            display: block;
            margin-bottom: 2px;
            color: #8b929b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .project-group-title a {
            display: block;
            overflow: hidden;
            color: #2d343d;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .project-group-title a:hover { color: #175196; text-decoration: underline; }
        .project-group-meta {
            margin-left: auto;
            color: #7e858e;
            font-size: 10px;
            white-space: nowrap;
        }
        .danger-text { color: #b42318; font-weight: 600; }

        .team-table-head,
        .team-row {
            display: grid;
            grid-template-columns: minmax(120px, 1fr) minmax(190px, 1.55fr) minmax(130px, 0.9fr) 100px 74px;
            gap: 10px;
            align-items: center;
        }
        .team-table-head {
            padding: 8px 14px;
            border-bottom: 1px solid #e5e9ed;
            background: #f7f9fb;
            color: #858b94;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .team-row {
            position: relative;
            min-height: 64px;
            padding: 9px 13px;
            border-bottom: 1px solid #edf0f3;
            transition: background 0.14s;
        }
        .team-row:last-child { border-bottom: none; }
        .team-row:hover { background: #fafbfc; }
        .team-row.attention-overdue { background: #fffdfd; }

        .team-identity,
        .project-title-cell,
        .team-owner-cell,
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
            font-size: 10px;
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
        .project-title-cell strong,
        .team-owner-cell strong {
            display: block;
            overflow: hidden;
            color: #384150;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .project-title-cell span,
        .team-owner-cell span {
            display: block;
            overflow: hidden;
            margin-top: 2px;
            color: #858b94;
            font-size: 10px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .team-assignment-line {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .team-assignment-line > span {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .team-meta-separator {
            flex: 0 0 auto;
            color: #b2b8c0;
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
            font-size: 10px;
        }
        .activity-line.danger { color: #b42318; font-weight: 600; }
        .progress-cell.is-overdue {
            margin: -5px;
            padding: 5px;
            border-radius: 6px;
            background: #fff5f5;
        }

        .team-actions-cell {
            display: flex;
            justify-content: flex-end;
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
            border-color: #175196;
            background: #175196;
            color: #fff;
        }
        .team-action.primary:hover { border-color: #183d61; background: #183d61; }

        .row-actions-menu {
            position: relative;
            flex: 0 0 auto;
        }
        .row-actions-menu > summary {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border: 1px solid #d8dee5;
            border-radius: 5px;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            list-style: none;
            font-size: 16px;
            line-height: 1;
        }
        .row-actions-menu > summary::-webkit-details-marker { display: none; }
        .row-actions-menu[open] > summary {
            border-color: #9eb0c1;
            background: #f5f8fb;
            color: #263447;
        }
        .row-actions-popover {
            position: absolute;
            top: 35px;
            right: 0;
            z-index: 12;
            min-width: 132px;
            padding: 5px;
            border: 1px solid #dce1e7;
            border-radius: 7px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(20, 32, 46, 0.14);
        }
        .row-actions-popover a {
            display: block;
            padding: 7px 9px;
            border-radius: 4px;
            color: #47515d;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }
        .row-actions-popover a:hover { background: #f1f5f9; color: #175196; }

        .teams-empty {
            display: flex;
            align-items: center;
            flex-direction: column;
            gap: 7px;
            padding: 36px 20px;
            color: #7f858e;
            font-size: 12px;
            text-align: center;
        }
        .teams-empty strong { color: #4d5661; font-size: 13px; }
        .teams-empty span { font-size: 11px; }

        @media (max-width: 900px) {
            .teams-control-bar {
                position: static;
                align-items: stretch;
                flex-direction: column;
            }
            .project-group-head {
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .project-group-meta {
                order: 3;
                width: 100%;
                margin-left: 0;
            }
            .team-table-head { display: none; }
            .team-row {
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "identity identity"
                    "project project"
                    "owner owner"
                    "progress actions";
                gap: 9px 14px;
                padding: 12px 14px;
            }
            .team-identity { grid-area: identity; }
            .project-title-cell { grid-area: project; }
            .team-owner-cell { grid-area: owner; }
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

        <div class="teams-control-bar">
            <form method="get" action="teacher_teams.php" class="team-search">
                <label for="teamSearch" class="sr-only">Search teams</label>
                <input type="search" id="teamSearch" name="q"
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search team, project, topic or leader">
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
                <div class="class-filter sort-filter">
                    <label for="sortFilter" class="sr-only">Sort teams</label>
                    <select id="sortFilter" name="sort" onchange="this.form.submit()">
                        <option value="project" <?= $sort === 'project' ? 'selected' : '' ?>>Project</option>
                        <option value="team" <?= $sort === 'team' ? 'selected' : '' ?>>Team name</option>
                        <option value="overdue" <?= $sort === 'overdue' ? 'selected' : '' ?>>Most overdue</option>
                        <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Recently active</option>
                    </select>
                </div>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <?php if ($hasFilters): ?>
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
                                    'sort'   => $sort,
                            ));
                    ?>
                    <a href="<?php echo htmlspecialchars($chipUrl); ?>"
                       class="<?= $status === $key ? 'active' : '' ?>"
                            <?php echo $status === $key ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                        <span><?php echo (int) $statusCounts[$key]; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <?php
            $overdueUrl = 'teacher_teams.php?' . http_build_query(array(
                            'status' => 'overdue',
                            'q' => $search,
                            'class' => $classFilter,
                            'category' => $categoryFilter,
                            'sort' => $sort,
                    ));
            ?>
            <a href="<?= htmlspecialchars($overdueUrl) ?>"
               class="overdue-filter <?= $status === 'overdue' ? 'active' : '' ?>">
                Overdue teams
                <span><?= (int) $statusCounts['overdue'] ?></span>
            </a>
        </div>

        <?php if (count($filteredTeams) === 0): ?>
            <div class="teams-empty">
                <strong>No teams match your filters.</strong>
                <span>Try another search term or clear the current filters.</span>
                <a href="teacher_teams.php" class="team-action">Clear filters</a>
            </div>
        <?php endif; ?>

        <div class="project-groups">
            <?php foreach ($groupedTeams as $group): ?>
                <details class="project-group" open>
                    <summary class="project-group-head">
                        <div class="project-group-title">
                            <span>
                                <?= htmlspecialchars($group['class_code'] ?: 'No class') ?>
                                · <?= htmlspecialchars($group['project_category'] ?: 'No category') ?>
                            </span>
                            <a href="project_detail.php?id=<?= (int) $group['project_id'] ?>">
                                <?= htmlspecialchars($group['project_name']) ?>
                            </a>
                        </div>
                        <div class="project-group-meta">
                            <?= count($group['teams']) ?> team<?= count($group['teams']) === 1 ? '' : 's' ?>
                            <?php if ($group['awaiting'] > 0): ?>
                                · <?= (int) $group['awaiting'] ?> awaiting approval
                            <?php endif; ?>
                            <?php if ($group['overdue_teams'] > 0): ?>
                                · <span class="danger-text"><?= (int) $group['overdue_teams'] ?> overdue</span>
                            <?php endif; ?>
                        </div>
                    </summary>

                    <div class="team-table-head" aria-hidden="true">
                        <span>Team</span>
                        <span>Project title</span>
                        <span>Leader / members</span>
                        <span>Progress</span>
                        <span>Actions</span>
                    </div>

                    <?php foreach ($group['teams'] as $team): ?>
                        <?php
                        $pending = (int) $team['pending_requests'];
                        $overdue = (int) $team['overdue_count'];
                        $done = (int) $team['completed_tasks'];
                        $total = (int) $team['total_tasks'];
                        $pct = $total > 0 ? (int) round($done / $total * 100) : 0;

                        // ★ 修正 3：?? 係 PHP 7.0+，改用 isset() ? :
                        $displayStatus = $pending > 0
                                ? 'Awaiting Approval'
                                : (isset($teamStatusLabels[$team['status']])
                                        ? $teamStatusLabels[$team['status']]
                                        : $team['status']);

                        $displayTopic = $team['topic_mode'] === 'Fixed'
                                ? $team['fixed_topic_title']
                                : $team['topic_title'];
                        $canMessage = ($pending === 0
                                && in_array($team['status'], array('Voting', 'Executing'), true));
                        ?>
                        <div class="team-row <?= $overdue > 0 ? 'attention-overdue' : '' ?>">
                            <div class="team-identity">
                                <a href="team_detail.php?id=<?= (int) $team['id'] ?>" class="team-name">
                                    <?= htmlspecialchars($team['name']) ?>
                                </a>
                                <span class="team-subline">
                                    <span class="team-status-inline"><?= htmlspecialchars($displayStatus) ?></span>
                                </span>
                            </div>

                            <div class="project-title-cell">
                                <strong>
                                    <?= htmlspecialchars($displayTopic ?: ($team['topic_mode'] === 'Self' ? 'Awaiting proposal' : 'Not selected')) ?>
                                </strong>
                                <?php if ($team['topic_mode'] !== 'Fixed' && $team['topic_status']): ?>
                                    <span><?= htmlspecialchars(topicProposalStatusLabel($team['topic_status'])) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="team-owner-cell">
                                <strong><?= htmlspecialchars($team['leader_name'] ?: 'No leader') ?></strong>
                                <span><?= (int) $team['member_count'] ?> / <?= (int) $team['max_members'] ?> members</span>
                            </div>

                            <div class="progress-cell <?= $overdue > 0 ? 'is-overdue' : '' ?>">
                                <div class="progress-line">
                                    <strong><?= $total > 0 ? $done . ' / ' . $total : 'No tasks' ?></strong>
                                    <span><?= $pct ?>%</span>
                                </div>
                                <div class="progress-track" aria-label="Task progress">
                                    <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                                </div>
                                <div class="activity-line <?= $overdue > 0 ? 'danger' : '' ?>">
                                    <?= $overdue > 0 ? $overdue . ' overdue' : htmlspecialchars(timeAgo($team['last_activity'])) ?>
                                </div>
                            </div>

                            <div class="team-actions-cell">
                                <?php if ($pending > 0): ?>
                                    <a href="teacher_review.php#requests" class="team-action primary">Review</a>
                                    <details class="row-actions-menu">
                                        <summary aria-label="More actions for <?= htmlspecialchars($team['name']) ?>">⋯</summary>
                                        <div class="row-actions-popover">
                                            <a href="team_detail.php?id=<?= (int) $team['id'] ?>">Open team</a>
                                        </div>
                                    </details>
                                <?php else: ?>
                                    <a href="team_detail.php?id=<?= (int) $team['id'] ?>" class="team-action primary">Open</a>
                                    <?php if ($canMessage): ?>
                                        <details class="row-actions-menu">
                                            <summary aria-label="More actions for <?= htmlspecialchars($team['name']) ?>">⋯</summary>
                                            <div class="row-actions-popover">
                                                <a href="teacher_messages.php?group_id=<?= (int) $team['id'] ?>">Message team</a>
                                            </div>
                                        </details>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </details>
            <?php endforeach; ?>
        </div>

    </div>
</main>

</body>
</html>
