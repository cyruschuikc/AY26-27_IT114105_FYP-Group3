<?php
$active = 'projects';

// ===== session + 数据库 =====
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


// =========================================================
// 1. Active Projects（未归档的）
// =========================================================
$activeProjects = array();

$sql = "
    SELECT
        p.id,
        p.name,
        p.category,
        p.status,
        p.is_draft,
        p.progress,
        p.period_end,
        p.period_start,
        cl.class_code,
        u.username AS supervisor_name,
        (SELECT COUNT(*) FROM `Groups` g
            WHERE g.project_id = p.id) AS team_count,
        (SELECT GROUP_CONCAT(g.name SEPARATOR ', ')
            FROM `Groups` g
            WHERE g.project_id = p.id) AS team_names
    FROM `Projects` p
    LEFT JOIN `Teachers` t ON t.id = p.supervisor_id
    LEFT JOIN `Users`    u ON u.id = t.user_id
    LEFT JOIN `classes`  cl ON cl.id = p.class_id
    WHERE p.status <> 'Archived'
      AND p.supervisor_id = ?
    ORDER BY p.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $activeProjects[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 2. Archived Projects
// =========================================================
$archivedProjects = array();

$sql = "
    SELECT
        p.id,
        p.name,
        p.category,
        p.is_draft,
        p.progress,
        p.period_end,
        cl.class_code,
        u.username AS supervisor_name,
        (SELECT GROUP_CONCAT(g.name SEPARATOR ', ')
            FROM `Groups` g
            WHERE g.project_id = p.id) AS team_names
    FROM `Projects` p
    LEFT JOIN `Teachers` t ON t.id = p.supervisor_id
    LEFT JOIN `Users`    u ON u.id = t.user_id
    LEFT JOIN `classes`  cl ON cl.id = p.class_id
    WHERE p.status = 'Archived'
      AND p.supervisor_id = ?
    ORDER BY p.id DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $archivedProjects[] = $row;
}
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261020d">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <div class="page-head-simple">
            <div>
                <h1>Projects</h1>
                <p class="subtitle">Final year projects under your supervision · 2026/27</p>
            </div>
            <a href="teacher_project_create.php" class="create-project-btn">+ Create Project</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'draft'): ?>
            <div class="project-flash success">Project saved as a draft. Students cannot see it until you publish it.</div>
        <?php endif; ?>

        <!-- Active Projects -->
        <h2>Active Projects</h2>
        <div class="card">
            <table>
                <tr>
                    <th style="width: 22%;">Project Name</th>
                    <th style="width: 9%;">Class</th>
                    <th style="width: 10%;">Category</th>
                    <th style="width: 14%;">Team</th>
                    <th style="width: 9%;">Progress</th>
                    <th style="width: 12%;">Deadline</th>
                    <th style="width: 16%;">Status</th>
                    <th style="width: 90px;"></th>
                </tr>

                <?php if (count($activeProjects) === 0): ?>
                    <tr>
                        <td colspan="8" class="muted" style="text-align: center; padding: 30px;">
                            No active projects.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($activeProjects as $p): ?>
                    <tr>
                        <td title="<?php echo htmlspecialchars($p['name']); ?>"><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo !empty($p['class_code']) ? htmlspecialchars($p['class_code']) : '<span class="muted">—</span>'; ?></td>
                        <td title="<?php echo htmlspecialchars($p['category']); ?>"><?php echo htmlspecialchars($p['category']); ?></td>
                        <td>
                            <?php if (!empty($p['team_names'])): ?>
                                <span title="<?php echo htmlspecialchars($p['team_names']); ?>"><?php echo htmlspecialchars($p['team_names']); ?></span>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo (int)$p['progress']; ?>%</td>
                        <td class="muted">
                            <?php echo $p['period_end'] ? date('d M Y', strtotime($p['period_end'])) : '—'; ?>
                        </td>
                        <td>
                            <?php if (!empty($p['is_draft'])): ?>
                                <span class="pill pill-draft">Draft</span>
                            <?php else: ?>
                                <span class="pill"><?php echo htmlspecialchars($p['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="project_detail.php?id=<?php echo (int)$p['id']; ?>" class="link">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Archived Projects -->
        <h2>Archived Projects</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Project Name</th>
                    <th>Class</th>
                    <th>Team</th>
                    <th>Progress</th>
                    <th>Completed</th>
                    <th style="width: 70px;"></th>
                </tr>

                <?php if (count($archivedProjects) === 0): ?>
                    <tr>
                        <td colspan="6" class="muted" style="text-align: center; padding: 30px;">
                            No archived projects.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($archivedProjects as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo !empty($p['class_code']) ? htmlspecialchars($p['class_code']) : '<span class="muted">—</span>'; ?></td>
                        <td>
                            <?php if (!empty($p['team_names'])): ?>
                                <?php echo htmlspecialchars($p['team_names']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo (int)$p['progress']; ?>%</td>
                        <td class="muted">
                            <?php echo $p['period_end'] ? date('M Y', strtotime($p['period_end'])) : '—'; ?>
                        </td>
                        <td>
                            <a href="project_detail.php?id=<?php echo (int)$p['id']; ?>" class="link">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</main>

<style>
.page-head-simple {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 8px;
}
.page-head-simple .subtitle { margin-bottom: 34px; }
.create-project-btn {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 8px;
    background: #175196;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}
.create-project-btn:hover { background: #123f76; }
.pill-draft { background: #fdf3e2; color: #9a6a13; }
.project-flash {
    padding: 11px 15px;
    border-radius: 8px;
    font-size: 12.5px;
    margin-bottom: 20px;
}
.project-flash.success {
    background: #e8f6ee;
    border: 1px solid #bfe4cd;
    color: #256b3f;
}
@media (max-width: 700px) {
    .page-head-simple { flex-direction: column; gap: 6px; }
    .page-head-simple .subtitle { margin-bottom: 18px; }
}
</style>

</body>
</html>
