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

// 按班级分组
$groupedStudents = array();
foreach ($directoryStudents as $student) {
    $classKey = $student['class_code'] !== null && $student['class_code'] !== ''
            ? $student['class_code']
            : 'No class';
    if (!isset($groupedStudents[$classKey])) {
        $groupedStudents[$classKey] = array(
            'class_code' => $classKey,
            'students'   => array(),
            'count'      => 0,
            'unassigned' => 0,
            'at_risk'    => 0,
        );
    }
    $groupedStudents[$classKey]['students'][] = $student;
    $groupedStudents[$classKey]['count']++;
    if (!$student['is_assigned']) {
        $groupedStudents[$classKey]['unassigned']++;
    }
    if ($student['overdue_tasks'] > 0) {
        $groupedStudents[$classKey]['at_risk']++;
    }
}
ksort($groupedStudents);

// 逾期学生（拎出来单独展示）
$atRiskList = array();
foreach ($directoryStudents as $student) {
    if ($student['overdue_tasks'] > 0) {
        $atRiskList[] = $student;
    }
}

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

        <?php if (count($atRiskList) > 0): ?>
            <div class="risk-students">
                <div class="risk-head">
                    <h2>At Risk Students</h2>
                    <span><?php echo count($atRiskList); ?> student<?php echo count($atRiskList) === 1 ? '' : 's'; ?> with overdue tasks</span>
                </div>
                <div class="risk-track">
                    <?php foreach ($atRiskList as $riskStudent): ?>
                        <?php
                        $riskInitials = studentInitials($riskStudent['name']);
                        $riskTeam = !empty($riskStudent['teams']) ? $riskStudent['teams'][0] : null;
                        $riskLink = $riskTeam ? 'team_detail.php?id=' . (int) $riskTeam['team_id'] : '#';
                        $riskTeamName = $riskTeam ? $riskTeam['team_name'] : 'No team';
                        ?>
                        <a class="risk-student-card" href="<?php echo htmlspecialchars($riskLink); ?>">
                            <div class="risk-avatar"><?php echo htmlspecialchars($riskInitials); ?></div>
                            <div class="risk-info">
                                <div class="risk-name"><?php echo htmlspecialchars($riskStudent['name']); ?></div>
                                <div class="risk-meta"><?php echo htmlspecialchars($riskTeamName); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

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

        <div class="student-groups">
            <?php foreach ($groupedStudents as $group): ?>
                <div class="student-group">
                    <button type="button" class="student-group-head" aria-expanded="false">
                        <div class="group-title">
                            <span class="group-class"><?php echo htmlspecialchars($group['class_code']); ?></span>
                            <span class="group-count"><?php echo (int) $group['count']; ?> students</span>
                        </div>
                        <div class="group-meta">
                            <?php if ($group['at_risk'] > 0): ?>
                                <span class="group-flag risk"><?php echo (int) $group['at_risk']; ?> at risk</span>
                            <?php endif; ?>
                            <?php if ($group['unassigned'] > 0): ?>
                                <span class="group-flag unassigned"><?php echo (int) $group['unassigned']; ?> unassigned</span>
                            <?php endif; ?>
                        </div>
                    </button>

                    <div class="student-head">
                        <span>Student</span>
                        <span>Team</span>
                    </div>

                    <?php foreach ($group['students'] as $studentIndex => $student): ?>
                        <?php
                        $initials = studentInitials($student['name']);
                        $pct = $student['total_tasks'] > 0
                                ? (int) round($student['done_tasks'] / $student['total_tasks'] * 100)
                                : 0;
                        $statusKey = $student['is_assigned'] ? 'assigned' : 'unassigned';
                        $rowClass = $student['overdue_tasks'] > 0
                                ? 'risk'
                                : ($student['is_assigned'] ? '' : 'unassigned');
                        ?>
                        <div class="student-row <?php echo $rowClass . ($studentIndex >= 5 ? ' extra' : ''); ?>"
                             data-status="<?php echo $statusKey; ?>"
                             data-risk="<?php echo $student['overdue_tasks'] > 0 ? '1' : '0'; ?>"
                             data-search="<?php echo htmlspecialchars($student['search_text'] . ' ' . $statusKey); ?>">
                    <div class="student-main">
                        <div class="student-avatar <?php echo $student['overdue_tasks'] > 0 ? 'risk' : ''; ?>">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                            <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                            <div class="student-number">
                                <?php echo htmlspecialchars($student['student_number']); ?>
                            </div>
                        </div>
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
                                <span class="role-tag <?php echo htmlspecialchars($team['role']); ?>"
                                      title="<?php echo htmlspecialchars($team['team_name'] . ' - ' . ucfirst($team['role'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst($team['role'])); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="unassigned-pill">Unassigned</span>
                        <?php endif; ?>
                    </div>

                        </div>
                    <?php endforeach; ?>
                    <?php if ($group['count'] > 5): ?>
                        <button type="button" class="group-expand">
                            + <?php echo (int) ($group['count'] - 5); ?> more
                        </button>
                    <?php endif; ?>
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

        document.querySelectorAll('.student-group').forEach(function (group) {
            var anyVisible = Array.prototype.some.call(
                group.querySelectorAll('.student-row'),
                function (row) { return !row.hidden; }
            );
            group.hidden = !anyVisible;
        });

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

    document.querySelectorAll('.student-group').forEach(function (group) {
        var head = group.querySelector('.student-group-head');
        var expandBtn = group.querySelector('.group-expand');
        var extraCount = group.querySelectorAll('.student-row.extra').length;

        function toggle() {
            var expanded = group.classList.toggle('expanded');
            head.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (expandBtn) {
                expandBtn.textContent = expanded ? 'Show less' : ('+ ' + extraCount + ' more');
            }
        }

        head.addEventListener('click', toggle);
        if (expandBtn) {
            expandBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                toggle();
            });
        }
    });
})();
</script>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
</body>
</html>
