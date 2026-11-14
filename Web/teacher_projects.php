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
        p.topic_mode,
        p.assessment_scope,
        p.assessment_weight,
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
        p.topic_mode,
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

$draftProjectCount = 0;
$publishedProjectCount = 0;
$activeTeamCount = 0;

foreach ($activeProjects as $project) {
    if (!empty($project['is_draft'])) {
        $draftProjectCount++;
    } else {
        $publishedProjectCount++;
    }
    $activeTeamCount += (int) $project['team_count'];
}

$projectStatusLabels = array(
        'Recruiting'   => 'Forming Team',
        'Waiting_Send' => 'Ready to Submit',
        'Voting'       => 'Choosing Leader',
        'Executing'    => 'Active',
        'Completed'    => 'Completed',
        'Archived'     => 'Archived',
);

$flashMessage = '';
$flashType = 'success';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'draft') {
        $flashMessage = 'Project saved as a draft. Students cannot see it until it is published.';
    } elseif ($_GET['msg'] === 'updated') {
        $flashMessage = 'Project updated successfully.';
    } elseif ($_GET['msg'] === 'created') {
        $flashMessage = 'Project published successfully.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_projects.css?v=<?= filemtime('assets/css/teacher_projects.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

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

        <?php if ($draftProjectCount > 0): ?>
            <div class="tb-alert tb-alert--warning">
                <span><strong><?= (int) $draftProjectCount ?> draft project<?= $draftProjectCount === 1 ? '' : 's' ?></strong> are not visible to students.</span>
            </div>
        <?php endif; ?>

        <div class="projects-kpi">
            <div class="kpi-item">
                <span class="kpi-label">Active Projects</span>
                <strong class="kpi-value"><?php echo $publishedProjectCount; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Team Groups</span>
                <strong class="kpi-value"><?php echo $activeTeamCount; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Drafts</span>
                <strong class="kpi-value"><?php echo $draftProjectCount; ?></strong>
            </div>
            <div class="kpi-item">
                <span class="kpi-label">Archived</span>
                <strong class="kpi-value"><?php echo count($archivedProjects); ?></strong>
            </div>
        </div>

        <div class="section-heading">
            <h2>Active Projects</h2>
            <span><?php echo $publishedProjectCount; ?> published · <?php echo $draftProjectCount; ?> drafts</span>
        </div>

        <?php if (count($activeProjects) === 0): ?>
            <div class="projects-empty">No active projects yet.</div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($activeProjects as $p): ?>
                    <?php
                    $isDraft = !empty($p['is_draft']);
                    $progress = max(0, min(100, (int) $p['progress']));

                    // ★ 修正：?? → isset() ? :
                    $statusLabel = $isDraft
                            ? 'Draft'
                            : (isset($projectStatusLabels[$p['status']]) ? $projectStatusLabels[$p['status']] : $p['status']);

                    $deadlineDate = $p['period_end'] ? date('d M Y', strtotime($p['period_end'])) : 'Not set';
                    $weightLabel = $p['assessment_weight'] !== null
                            ? (string) (int) $p['assessment_weight']
                            : '';
                    $deadlineMeta = '';
                    $deadlineClass = '';
                    if (!empty($p['period_end'])) {
                        $daysLeft = (int) floor((strtotime($p['period_end']) - strtotime(date('Y-m-d'))) / 86400);
                        if ($daysLeft < 0) {
                            $deadlineMeta = abs($daysLeft) . ' days overdue';
                            $deadlineClass = 'danger';
                        } elseif ($daysLeft === 0) {
                            $deadlineMeta = 'Due today';
                            $deadlineClass = 'warning';
                        } else {
                            $deadlineMeta = $daysLeft . ' days left';
                        }
                    }
                    ?>
                    <article class="project-card <?php echo $isDraft ? 'is-draft' : ''; ?>">
                        <div class="project-card-head">
                            <div class="project-title-block">
                                <span class="project-id">Project <?php echo str_pad((string) $p['id'], 2, '0', STR_PAD_LEFT); ?></span>
                                <h3>
                                    <a href="project_detail.php?id=<?php echo (int) $p['id']; ?>"
                                       data-tooltip-auto="<?= htmlspecialchars($p['name']) ?>">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </a>
                                </h3>
                            </div>
                            <span class="project-status <?php echo $isDraft ? 'status-draft' : 'status-' . strtolower((string) $p['status']); ?>"
                                  tabindex="0"
                                  data-tooltip="Current project status: <?= htmlspecialchars($statusLabel) ?>">
                                <?php echo htmlspecialchars($statusLabel); ?>
                            </span>
                        </div>

                        <div class="project-context">
                            <span class="class-tag">
                                <?php echo htmlspecialchars($p['class_code'] ?: 'No class'); ?>
                            </span>
                            <span class="meta-separator">·</span>
                            <span>
                                <?php echo htmlspecialchars($p['category'] ?: 'Uncategorised'); ?>
                            </span>
                            <span class="meta-separator">·</span>
                            <span>
                                <?= htmlspecialchars(
                                        $p['topic_mode'] === 'Multi'
                                                ? 'Multi-select topics'
                                                : ($p['topic_mode'] === 'Self' ? 'Self-selected topic' : 'Fixed topic')
                                ) ?>
                            </span>
                            <?php if (!empty($p['assessment_scope']) && $weightLabel !== ''): ?>
                                <span class="meta-separator">·</span>
                                <span class="weight-inline">
                                    <?php echo htmlspecialchars($weightLabel); ?>% of
                                    <?php echo htmlspecialchars($p['assessment_scope']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="project-overview">
                            <div class="project-metrics">
                                <span>Teams</span>
                                <strong><?php echo (int) $p['team_count']; ?></strong>
                            </div>
                            <div class="progress-cluster">
                                <div class="progress-ring"
                                     style="--progress: <?php echo $progress; ?>;"
                                     tabindex="0"
                                     data-tooltip="<?php echo (int) $progress; ?>% overall project completion"
                                     role="progressbar"
                                     aria-label="<?php echo htmlspecialchars($p['name']); ?> progress"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     aria-valuenow="<?php echo $progress; ?>">
                                    <span><?php echo $progress; ?>%</span>
                                </div>
                                <span class="progress-caption">Progress</span>
                            </div>
                        </div>

                        <div class="project-card-footer">
                            <div class="project-deadline <?php echo $deadlineClass; ?>">
                                <span>Deadline</span>
                                <strong><?php echo htmlspecialchars($deadlineDate); ?></strong>
                                <?php if ($deadlineMeta !== ''): ?>
                                    <small><?php echo htmlspecialchars($deadlineMeta); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="project-actions">
                                <a href="RandomStudents.php?id=<?php echo (int) $p['id']; ?>" class="project-random">
                                    Random
                                </a>
                                <a href="project_detail.php?id=<?php echo (int) $p['id']; ?>" class="project-open">
                                    Open Project
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="section-heading archived-heading">
            <h2>Archived Projects</h2>
            <span><?php echo count($archivedProjects); ?> archived</span>
        </div>

        <?php if (count($archivedProjects) === 0): ?>
            <div class="archived-empty">
                <strong>No archived projects</strong>
                <span>Completed projects will appear here.</span>
            </div>
        <?php else: ?>
            <div class="archived-list">
                <?php foreach ($archivedProjects as $p): ?>
                    <div class="archived-row">
                        <div>
                            <a href="project_detail.php?id=<?php echo (int) $p['id']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </a>
                            <span><?php echo htmlspecialchars($p['class_code'] ?: 'No class'); ?></span>
                        </div>
                        <div><?php echo (int) $p['progress']; ?>% complete</div>
                        <div><?php echo $p['period_end'] ? date('M Y', strtotime($p['period_end'])) : '—'; ?></div>
                        <a href="project_detail.php?id=<?php echo (int) $p['id']; ?>">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
<script>
    window.TEACHER_FLASH = <?= json_encode(
            $flashMessage !== '' ? array('message' => $flashMessage, 'type' => $flashType) : null,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
</script>

</body>
</html>
