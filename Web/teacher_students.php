<?php
$active = 'students';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */


// =========================================================
// 1. 汇总数字
// =========================================================

// Total Students
$totalStudents = 0;
$rs = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `Students`");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $totalStudents = (int)$rc['c']; mysqli_free_result($rs); }

// Assigned to Teams（在 Group_Members 里的学生）
$assignedStudents = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(DISTINCT student_id) AS c
    FROM `Group_Members`
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $assignedStudents = (int)$rc['c']; mysqli_free_result($rs); }

// Unassigned
$unassignedStudents = $totalStudents - $assignedStudents;

// At Risk（有 overdue 任务的学生）
$atRisk = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(DISTINCT assigned_to) AS c
    FROM `Tasks`
    WHERE status <> 'Completed'
      AND due_date < CURDATE()
      AND assigned_to IS NOT NULL
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $atRisk = (int)$rc['c']; mysqli_free_result($rs); }


// =========================================================
// 2. Assigned Students（已经在某个 Group 里的学生）
// =========================================================
$assigned = array();

$sql = "
    SELECT
        s.id                AS student_id,
        s.student_number,
        s.major,
        s.grade_level,
        u.username          AS name,
        g.name              AS team_name,
        gm.role             AS team_role,
        (SELECT COUNT(*) FROM `Tasks` t
            WHERE t.assigned_to = s.id
              AND t.status = 'Completed') AS done_tasks,
        (SELECT COUNT(*) FROM `Tasks` t2
            WHERE t2.assigned_to = s.id) AS total_tasks
    FROM `Students` s
    INNER JOIN `Users`          u  ON u.id = s.user_id
    INNER JOIN `Group_Members`  gm ON gm.student_id = s.id
    INNER JOIN `Groups`         g  ON g.id = gm.group_id
    ORDER BY g.id, s.id
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $assigned[] = $row;
    }
    mysqli_free_result($rs);
}


// =========================================================
// 3. Unassigned Students（不在任何 Group 里的学生）
// =========================================================
$unassigned = array();

$sql = "
    SELECT
        s.id                AS student_id,
        s.student_number,
        s.major,
        s.grade_level,
        u.username          AS name
    FROM `Students` s
    INNER JOIN `Users` u ON u.id = s.user_id
    WHERE s.id NOT IN (SELECT DISTINCT student_id FROM `Group_Members`)
    ORDER BY s.id
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $unassigned[] = $row;
    }
    mysqli_free_result($rs);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Students</h1>
        <p class="subtitle">All students under your supervision · <?php echo $totalStudents; ?> students</p>

        <!-- Summary -->
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
                <div class="value"><?php echo $atRisk; ?></div>
            </div>
        </div>

        <!-- Assigned Students -->
        <h2>Assigned Students</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>Team</th>
                    <th>Role</th>
                    <th>Tasks</th>
                    <th></th>
                </tr>

                <?php if (count($assigned) === 0): ?>
                    <tr>
                        <td colspan="7" class="muted" style="text-align: center; padding: 30px;">
                            No assigned students yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($assigned as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo htmlspecialchars($s['student_number']); ?></td>
                        <td><?php echo htmlspecialchars(formatClass($s['major'], $s['grade_level'])); ?></td>
                        <td><?php echo htmlspecialchars($s['team_name']); ?></td>
                        <td>
                            <?php if ($s['team_role'] === 'leader'): ?>
                                <span class="pill">Leader</span>
                            <?php else: ?>
                                <span class="muted">Member</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo (int)$s['done_tasks']; ?> / <?php echo (int)$s['total_tasks']; ?>
                        </td>
                        <td><a href="#" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Unassigned Students -->
        <h2>Unassigned Students</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>Status</th>
                    <th></th>
                </tr>

                <?php if (count($unassigned) === 0): ?>
                    <tr>
                        <td colspan="5" class="muted" style="text-align: center; padding: 30px;">
                            All students are assigned to teams. 🎉
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($unassigned as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo htmlspecialchars($s['student_number']); ?></td>
                        <td><?php echo htmlspecialchars(formatClass($s['major'], $s['grade_level'])); ?></td>
                        <td><span class="pill">Unassigned</span></td>
                        <td><button class="link">Assign</button></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</main>

</body>
</html>