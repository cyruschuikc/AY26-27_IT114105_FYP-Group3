<?php
$active = 'students';

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
    header('Location: login.php');
    exit;
}

// =========================================================
// Students assigned to this teacher
// =========================================================
$students = array();

$stmt = mysqli_prepare($conn, "
    SELECT DISTINCT
        s.id,
        s.student_number,
        s.major,
        s.grade_level,
        u.username AS name,
        c.class_code
    FROM `teacher_student` ts
    INNER JOIN `students` s ON s.id = ts.student_id
    INNER JOIN `users` u    ON u.id = s.user_id
    INNER JOIN `classes` c  ON c.id = s.class_id
    WHERE ts.teacher_id = ?
    ORDER BY u.username
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($student = mysqli_fetch_assoc($rs)) {
    $students[$student['id']] = $student;
}
mysqli_stmt_close($stmt);

// =========================================================
// Team memberships under this teacher
// =========================================================
$memberships = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        gm.student_id,
        gm.role,
        g.id   AS team_id,
        g.name AS team_name,
        g.status AS team_status,
        g.max_members,
        p.id   AS project_id,
        p.name AS project_name,
        p.team_mode,
        p.team_deadline
    FROM `group_members` gm
    INNER JOIN `groups` g   ON g.id = gm.group_id
    INNER JOIN `projects` p ON p.id = g.project_id
    WHERE p.supervisor_id = ?
      AND p.is_draft = 0
    ORDER BY p.name, g.name
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($membership = mysqli_fetch_assoc($rs)) {
    $sid = (int) $membership['student_id'];
    $memberships[$sid][] = $membership;
}
mysqli_stmt_close($stmt);

// =========================================================
// Team formation settings for the teacher's projects
// =========================================================
$formationProjects = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        p.id,
        p.name,
        p.course_id,
        p.team_mode,
        p.team_deadline
    FROM `projects` p
    WHERE p.supervisor_id = ?
      AND p.is_draft = 0
    ORDER BY p.name
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($project = mysqli_fetch_assoc($rs)) {
    $formationProjects[] = $project;
}
mysqli_stmt_close($stmt);

$formationDeadlineItems = array();
$hasPassedFormationDeadline = false;
$today = date('Y-m-d');

foreach ($formationProjects as $project) {
    if (empty($project['team_deadline'])) {
        continue;
    }

    $passed = $today > $project['team_deadline'];
    $hasPassedFormationDeadline = $hasPassedFormationDeadline || $passed;
    $formationDeadlineItems[] = array(
        'project_name' => $project['name'],
        'deadline'     => $project['team_deadline'],
        'passed'       => $passed,
    );
}

usort($formationDeadlineItems, function ($a, $b) {
    return strcmp($a['deadline'], $b['deadline']);
});

// =========================================================
// Task metrics for each student within each team
// =========================================================
$metrics = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        t.group_id,
        t.assigned_to AS student_id,
        COUNT(DISTINCT t.id) AS total_tasks,
        COUNT(DISTINCT CASE WHEN t.status = 'Completed' THEN t.id END) AS done_tasks,
        COUNT(DISTINCT CASE
            WHEN t.status <> 'Completed'
             AND t.due_date IS NOT NULL
             AND t.due_date < CURDATE()
            THEN t.id END) AS overdue_tasks
    FROM `tasks` t
    INNER JOIN `groups` g   ON g.id = t.group_id
    INNER JOIN `projects` p ON p.id = g.project_id
    WHERE p.supervisor_id = ?
      AND p.is_draft = 0
      AND t.assigned_to IS NOT NULL
    GROUP BY t.group_id, t.assigned_to
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($metric = mysqli_fetch_assoc($rs)) {
    $metrics[(int) $metric['group_id']][(int) $metric['student_id']] = $metric;
}
mysqli_stmt_close($stmt);

$totalStudents = count($students);
$assignedStudents = 0;
$unassignedStudents = 0;
$atRiskStudents = 0;
$directoryStudents = array();

foreach ($students as &$student) {
    $sid = (int) $student['id'];
    $student['teams'] = isset($memberships[$sid]) ? $memberships[$sid] : array();
    $student['is_assigned'] = count($student['teams']) > 0;
    $studentAtRisk = false;
    $doneTasks = 0;
    $totalTasks = 0;
    $overdueTasks = 0;

    usort($student['teams'], function ($a, $b) {
        return strnatcasecmp($a['team_name'], $b['team_name']);
    });

    $searchParts = array(
        $student['name'],
        $student['student_number'],
        formatClass($student['major'], $student['grade_level'], $student['class_code']),
    );
    foreach ($student['teams'] as $team) {
        $searchParts[] = $team['team_name'];
        $searchParts[] = $team['project_name'];
        $teamId = (int) $team['team_id'];
        $metric = isset($metrics[$teamId][$sid]) ? $metrics[$teamId][$sid] : null;
        if ($metric) {
            $doneTasks += (int) $metric['done_tasks'];
            $totalTasks += (int) $metric['total_tasks'];
            $overdueTasks += (int) $metric['overdue_tasks'];
        }
        if ($overdueTasks > 0) {
            $studentAtRisk = true;
        }
    }
    $student['search_text'] = strtolower(implode(' ', $searchParts));
    $student['done_tasks'] = $doneTasks;
    $student['total_tasks'] = $totalTasks;
    $student['overdue_tasks'] = $overdueTasks;

    $primaryTeam = null;
    foreach ($student['teams'] as $team) {
        if ($primaryTeam === null) {
            $primaryTeam = $team;
        }
        if ($team['role'] === 'leader') {
            $primaryTeam = $team;
            break;
        }
    }
    $student['primary_team_name'] = $primaryTeam ? $primaryTeam['team_name'] : '';
    $student['primary_role'] = $primaryTeam ? $primaryTeam['role'] : '';

    if ($student['is_assigned']) {
        $assignedStudents++;
    } else {
        $unassignedStudents++;
    }
    if ($studentAtRisk) {
        $atRiskStudents++;
    }

    $directoryStudents[] = $student;
}
unset($student);

// Flat directory order: primary team, leader first, then student name.
// Unassigned students remain at the end.
usort($directoryStudents, function ($a, $b) {
    if ($a['is_assigned'] !== $b['is_assigned']) {
        return $a['is_assigned'] ? -1 : 1;
    }
    if (!$a['is_assigned']) {
        return strcasecmp($a['name'], $b['name']);
    }

    $teamCompare = strnatcasecmp($a['primary_team_name'], $b['primary_team_name']);
    if ($teamCompare !== 0) {
        return $teamCompare;
    }

    $aPriority = $a['primary_role'] === 'leader' ? 0 : 1;
    $bPriority = $b['primary_role'] === 'leader' ? 0 : 1;
    if ($aPriority !== $bPriority) {
        return $aPriority - $bPriority;
    }

    return strcasecmp($a['name'], $b['name']);
});

$filterCounts = array(
    'all'        => $totalStudents,
    'assigned'   => $assignedStudents,
    'unassigned' => $unassignedStudents,
    'risk'       => $atRiskStudents,
);

if (!function_exists('studentInitials')) {
    function studentInitials($name)
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr((string) $name, 0, 2));
    }
}

$avatarPalette = array('#dbeafe', '#e9d5ff', '#fce7f3', '#d1fae5', '#fef3c7');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_students.css?v=<?= filemtime('assets/css/teacher_students.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Students</h1>
        <p class="subtitle">Students assigned to you · <?php echo $totalStudents; ?> students</p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Students</div>
                <div class="value"><?php echo $totalStudents; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Assigned to Teams</div>
                <div class="value"><?php echo $assignedStudents; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Unassigned</div>
                <div class="value"><?php echo $unassignedStudents; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">At Risk</div>
                <div class="value"><?php echo $atRiskStudents; ?></div>
            </div>
        </div>

        <div class="student-toolbar">
            <div class="field field-search">
                <label for="studentSearch">Search</label>
                <input type="search" id="studentSearch"
                       placeholder="Search name, student ID, class, team or project">
            </div>
        </div>

        <div class="student-filters">
            <?php
            $filterDefs = array(
                'all'        => 'All',
                'assigned'   => 'Assigned',
                'unassigned' => 'Unassigned',
                'risk'       => 'At Risk',
            );
            foreach ($filterDefs as $key => $label):
            ?>
                <button type="button"
                        class="student-filter <?php echo $key === 'all' ? 'active' : ''; ?>"
                        data-filter="<?php echo $key; ?>">
                    <?php echo htmlspecialchars($label); ?>
                    <span><?php echo (int) $filterCounts[$key]; ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php if ($unassignedStudents > 0 && count($formationDeadlineItems) > 0): ?>
            <div class="formation-notice <?php echo $hasPassedFormationDeadline ? 'passed' : ''; ?>">
                <div>
                    <?php if ($hasPassedFormationDeadline): ?>
                        <strong>Team formation deadline has passed.</strong>
                        Remaining students are now the teacher's responsibility to assign randomly.
                    <?php else: ?>
                        Unassigned students will be randomly assigned by the teacher after the
                        team formation deadline.
                    <?php endif; ?>
                </div>
                <div class="formation-dates">
                    <?php foreach ($formationDeadlineItems as $deadlineItem): ?>
                        <span class="formation-date <?php echo $deadlineItem['passed'] ? 'passed' : ''; ?>">
                            <?php echo htmlspecialchars($deadlineItem['project_name']); ?> ·
                            <?php echo date('d M Y', strtotime($deadlineItem['deadline'])); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card students-card">
            <div class="student-head">
                <span>Student</span>
                <span>Class</span>
                <span>Team</span>
                <span>Role</span>
                <span>Tasks</span>
                <span>Risk</span>
            </div>

            <?php foreach ($directoryStudents as $student): ?>
                <?php
                $initials = studentInitials($student['name']);
                $avatarBg = $avatarPalette[abs(crc32($student['name'])) % count($avatarPalette)];
                $pct = $student['total_tasks'] > 0
                    ? (int) round($student['done_tasks'] / $student['total_tasks'] * 100)
                    : 0;
                $statusKey = $student['is_assigned'] ? 'assigned' : 'unassigned';
                ?>
                <div class="student-row"
                     data-status="<?php echo $statusKey; ?>"
                     data-risk="<?php echo $student['overdue_tasks'] > 0 ? '1' : '0'; ?>"
                     data-search="<?php echo htmlspecialchars($student['search_text'] . ' ' . $statusKey); ?>">
                    <div class="student-main">
                        <div class="student-avatar" style="background: <?php echo $avatarBg; ?>;">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                            <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                            <div class="student-number">
                                <?php echo htmlspecialchars($student['student_number']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="student-class">
                        <?php echo htmlspecialchars(formatClass($student['major'], $student['grade_level'], $student['class_code'])); ?>
                    </div>

                    <div class="student-team">
                        <?php if ($student['is_assigned']): ?>
                            <?php foreach ($student['teams'] as $teamIndex => $team): ?>
                                <?php if ($teamIndex > 0): ?>
                                    <span class="team-separator">·</span>
                                <?php endif; ?>
                                <a class="team-link"
                                   href="team_detail.php?id=<?php echo (int) $team['team_id']; ?>">
                                    <?php echo htmlspecialchars($team['team_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="unassigned-pill">Unassigned</span>
                        <?php endif; ?>
                    </div>

                    <div class="student-role">
                        <?php if ($student['is_assigned']): ?>
                            <?php foreach ($student['teams'] as $teamIndex => $team): ?>
                                <?php
                                $roleLabel = ucfirst($team['role']);
                                $roleTooltip = $team['team_name'] . ' - ' . $roleLabel;
                                if ($teamIndex > 0):
                                ?>
                                    <span class="team-separator">·</span>
                                <?php endif; ?>
                                <span class="role-tag <?php echo htmlspecialchars($team['role']); ?>"
                                      title="<?php echo htmlspecialchars($roleTooltip); ?>"
                                      aria-label="<?php echo htmlspecialchars($roleTooltip); ?>">
                                    <?php echo htmlspecialchars($roleLabel); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if ($student['is_assigned']): ?>
                            <span class="student-task-count">
                                <?php echo $student['done_tasks']; ?> / <?php echo $student['total_tasks']; ?>
                            </span>
                            <div class="student-task-bar">
                                <i style="width: <?php echo $pct; ?>%;"></i>
                            </div>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if ($student['overdue_tasks'] > 0): ?>
                            <span class="risk-pill">
                                <?php echo $student['overdue_tasks']; ?> overdue
                            </span>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="student-empty" id="studentEmpty" hidden>
                No students match your search or filter.
            </div>
        </div>

    </div>
</main>

<script>
(function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.student-row'));
    var buttons = Array.prototype.slice.call(document.querySelectorAll('.student-filter'));
    var search = document.getElementById('studentSearch');
    var empty = document.getElementById('studentEmpty');
    var current = 'all';

    function applyFilters() {
        var query = search.value.trim().toLowerCase();
        var visible = 0;

        rows.forEach(function (row) {
            var status = row.getAttribute('data-status');
            var risk = row.getAttribute('data-risk') === '1';
            var matchesFilter = current === 'all'
                || (current === 'risk' ? risk : status === current);
            var matchesSearch = query === ''
                || (row.getAttribute('data-search') || '').indexOf(query) !== -1;
            var show = matchesFilter && matchesSearch;

            row.hidden = !show;
            if (show) {
                row.classList.toggle('alt', visible % 2 === 1);
                visible++;
            } else {
                row.classList.remove('alt');
            }
        });

        empty.hidden = visible > 0;

        buttons.forEach(function (button) {
            var active = button.getAttribute('data-filter') === current;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            current = button.getAttribute('data-filter');
            applyFilters();
        });
    });

    search.addEventListener('input', applyFilters);
    applyFilters();
})();
</script>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
</body>
</html>
