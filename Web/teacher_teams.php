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
    header('Location: login.php');
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
        (SELECT MIN(t4.due_date)
            FROM `tasks` t4
            WHERE t4.group_id = g.id
              AND t4.status <> 'Completed'
              AND t4.due_date IS NOT NULL
              AND t4.due_date >= CURDATE()) AS next_deadline,
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

// =========================================================
// 团队成员名单（用于头像堆叠）
// =========================================================
$teamMembers = array();
$stmt = mysqli_prepare($conn, "
    SELECT gm.group_id, u.username AS name, gm.role
    FROM `group_members` gm
    INNER JOIN `groups`   g ON g.id = gm.group_id
    INNER JOIN `projects` p ON p.id = g.project_id
    INNER JOIN `students` s ON s.id = gm.student_id
    INNER JOIN `users`    u ON u.id = s.user_id
    WHERE p.supervisor_id = ?
    ORDER BY gm.group_id, (gm.role = 'leader') DESC, u.username
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($memberRow = mysqli_fetch_assoc($rs)) {
    $gid = (int) $memberRow['group_id'];
    if (!isset($teamMembers[$gid])) {
        $teamMembers[$gid] = array();
    }
    $teamMembers[$gid][] = $memberRow['name'];
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
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'attention';

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
if (!in_array($sort, array('attention', 'project', 'team', 'overdue', 'recent'), true)) {
    $sort = 'attention';
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
    if ($sort === 'attention') {
        $aP = (int) $a['priority'];
        $bP = (int) $b['priority'];
        if ($aP !== $bP) return $aP < $bP ? -1 : 1;
        $aO = (int) $a['overdue_count'];
        $bO = (int) $b['overdue_count'];
        if ($aO !== $bO) return $aO > $bO ? -1 : 1;
    }
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
        'overdue'    => 'Overdue',
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
        || $categoryFilter !== 'all' || $sort !== 'attention');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_teams.css?v=<?= filemtime('assets/css/teacher_teams.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Teams</h1>

        <div class="teams-kpi">
            <div class="kpi-item">
                <span class="kpi-label">Total Teams</span>
                <strong class="kpi-value"><?php echo (int) $totalTeams; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Awaiting Approval</span>
                <strong class="kpi-value"><?php echo (int) $awaitingApproval; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Active Teams</span>
                <strong class="kpi-value"><?php echo (int) $activeTeams; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Overdue Teams</span>
                <strong class="kpi-value"><?php echo (int) $overdueTeams; ?></strong>
            </div>
        </div>

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
                <div class="class-filter sort-filter">
                    <label for="sortFilter" class="sr-only">Sort teams</label>
                    <select id="sortFilter" name="sort" onchange="this.form.submit()">
                        <option value="attention" <?= $sort === 'attention' ? 'selected' : '' ?>>Needs attention</option>
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
                       class="<?= $status === $key ? 'active' : '' ?> <?= $key === 'overdue' ? 'overdue-chip' : '' ?>"
                            <?php echo $status === $key ? 'aria-current="page"' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                        <span><?php echo (int) $statusCounts[$key]; ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
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

                    <div class="team-cards">
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

                        $statusClass = 'status-neutral';
                        if ($pending > 0) {
                            $statusClass = 'status-pending';
                        } elseif ($team['status'] === 'Voting') {
                            $statusClass = 'status-voting';
                        } elseif ($team['status'] === 'Executing') {
                            $statusClass = 'status-executing';
                        } elseif ($team['status'] === 'Completed') {
                            $statusClass = 'status-completed';
                        } elseif ($team['status'] === 'Waiting_Send') {
                            $statusClass = 'status-waiting';
                        }

                        $displayTopic = $team['topic_mode'] === 'Fixed'
                                ? $team['fixed_topic_title']
                                : $team['topic_title'];
                        $canMessage = ($pending === 0
                                && in_array($team['status'], array('Voting', 'Executing'), true));
                        ?>
                        <article class="team-card <?= $overdue > 0 ? 'attention-overdue' : '' ?>">
                            <div class="card-top">
                                <span class="team-name"><?= htmlspecialchars($team['name']) ?></span>
                                <span class="card-topic"><?= htmlspecialchars($displayTopic ?: ($team['topic_mode'] === 'Self' ? 'Awaiting proposal' : 'Not selected')) ?></span>
                            </div>

                            <div class="card-progress">
                                <div class="progress-line">
                                    <strong><?= $total > 0 ? $done . ' / ' . $total : 'No tasks' ?></strong>
                                </div>
                                <div class="progress-track" aria-label="Task progress">
                                    <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                                </div>
                                <div class="activity-line">
                                    <?php if ($overdue > 0): ?>
                                        <span class="danger"><?= $overdue ?> overdue</span>
                                    <?php endif; ?>
                                    <?php if (!empty($team['next_deadline'])): ?>
                                        <?php if ($overdue > 0): ?> · <?php endif; ?>Due <?= date('d M', strtotime($team['next_deadline'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-extra">
                                <?php
                                $members = isset($teamMembers[$team['id']]) ? $teamMembers[$team['id']] : array();
                                $shownMembers = array_slice($members, 0, 4);
                                $extraMembers = count($members) - count($shownMembers);
                                ?>
                                <div class="member-stack">
                                    <?php foreach ($shownMembers as $memberName): ?>
                                        <?php
                                        $memberParts = preg_split('/\s+/', trim((string) $memberName));
                                        $memberInitials = count($memberParts) >= 2
                                                ? strtoupper(substr($memberParts[0], 0, 1) . substr($memberParts[count($memberParts) - 1], 0, 1))
                                                : strtoupper(substr((string) $memberName, 0, 2));
                                        ?>
                                        <span class="member-avatar" title="<?php echo htmlspecialchars($memberName); ?>"><?php echo htmlspecialchars($memberInitials); ?></span>
                                    <?php endforeach; ?>
                                    <?php if ($extraMembers > 0): ?>
                                        <span class="member-avatar member-more">+<?php echo (int) $extraMembers; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-leader"><?= htmlspecialchars($team['leader_name'] ?: 'No leader') ?></div>
                            </div>

                            <div class="card-footer">
                                <div class="card-actions">
                                    <a href="team_detail.php?id=<?= (int) $team['id'] ?>" class="team-action primary">More</a>
                                    <?php if ($pending > 0): ?>
                                        <a href="teacher_review.php#requests" class="team-action">Review</a>
                                    <?php elseif ($canMessage): ?>
                                        <a href="teacher_messages.php?group_id=<?= (int) $team['id'] ?>" class="team-action">Message</a>
                                    <?php endif; ?>
                                </div>
                                <span class="team-status-inline <?php echo $statusClass; ?>"><?= htmlspecialchars($displayStatus) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
<script>
(function () {
    var cards = document.querySelectorAll('.team-card');
    cards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('a, button, summary, select, input')) return;
            card.classList.toggle('open');
        });
    });
})();
</script>
</body>
</html>
