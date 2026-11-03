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
    <link rel="stylesheet" href="teacher.css?v=20261028b">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=20261028b">
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
                            <span class="project-status <?php echo $isDraft ? 'status-draft' : ''; ?>"
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
                            <a href="project_detail.php?id=<?php echo (int) $p['id']; ?>" class="project-open">
                                Open Project
                            </a>
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

<style>
    .page-head-simple {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 16px;
    }
    .page-head-simple .subtitle { margin-bottom: 0; }
    .create-project-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 14px;
        border: 1px solid #175196;
        border-radius: 6px;
        background: #175196;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .create-project-btn:hover { background: #183d61; border-color: #183d61; }
    .create-project-btn:focus-visible,
    .project-title-block a:focus-visible,
    .project-open:focus-visible,
    .archived-row a:focus-visible {
        outline: 2px solid #6da5de;
        outline-offset: 2px;
    }

    .projects-kpi {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        overflow: hidden;
        margin-bottom: 16px;
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
        display: block;
        overflow: hidden;
        color: #abb1ba;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .kpi-value {
        display: block;
        margin-top: 4px;
        color: #fff;
        font-size: 25px;
        font-variant-numeric: tabular-nums;
        font-weight: 650;
        line-height: 1.1;
    }

    .section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 9px;
    }
    .section-heading h2 {
        margin: 0;
        color: #343b45;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .section-heading span {
        color: #858b94;
        font-size: 10.5px;
    }
    .archived-heading { margin-top: 24px; }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .project-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 15px;
        border: 1px solid #e1e5ea;
        border-radius: 9px;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .project-card:hover {
        border-color: #c9d2dc;
        box-shadow: 0 5px 18px rgba(30, 41, 59, 0.06);
    }
    .project-card.is-draft { background: #fbfcfd; }
    .project-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .project-title-block { min-width: 0; }
    .project-id {
        display: block;
        margin-bottom: 3px;
        color: #858b94;
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 10px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .project-title-block h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.35;
    }
    .project-title-block a {
        display: block;
        overflow: hidden;
        color: #22262d;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .project-title-block a:hover { color: #175196; text-decoration: underline; }
    .project-status {
        flex-shrink: 0;
        padding: 3px 7px;
        border: 1px solid #e1e5ea;
        border-radius: 4px;
        background: #f7f8fa;
        color: #59616c;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }
    .status-draft {
        border-color: #d8dde4;
        background: #f2f4f6;
        color: #6b7280;
    }
    .project-context {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        margin-top: 8px;
        overflow: hidden;
        color: #7f858e;
        font-size: 10px;
        white-space: nowrap;
    }
    .class-tag {
        flex: 0 0 auto;
        color: #4f5965;
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 10px;
        font-weight: 600;
    }
    .meta-separator {
        flex: 0 0 auto;
        color: #b1b7bf;
    }
    .weight-inline {
        flex: 0 0 auto;
        color: #59616c;
        font-weight: 600;
    }
    .project-overview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: 16px;
    }
    .project-metrics { min-width: 0; }
    .project-metrics span {
        display: block;
        color: #858b94;
        font-size: 10.5px;
    }
    .project-metrics strong {
        display: block;
        margin-top: 3px;
        color: #303741;
        font-size: 17px;
        font-variant-numeric: tabular-nums;
        font-weight: 650;
    }
    .progress-cluster {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .progress-ring {
        position: relative;
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: conic-gradient(
                #2f6fa8 calc(var(--progress) * 1%),
                #e9edf1 0
        );
    }
    .progress-ring::before {
        position: absolute;
        inset: 5px;
        border-radius: 50%;
        background: #fff;
        content: "";
    }
    .progress-ring span {
        position: relative;
        color: #303741;
        font-size: 11px;
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }
    .progress-caption {
        color: #858b94;
        font-size: 10.5px;
    }
    .project-card-footer {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        margin-top: 17px;
        padding-top: 12px;
        border-top: 1px solid #eef1f4;
    }
    .project-deadline span {
        display: block;
        color: #858b94;
        font-size: 10px;
    }
    .project-deadline strong {
        display: block;
        margin-top: 2px;
        color: #424a55;
        font-size: 11.5px;
        font-variant-numeric: tabular-nums;
    }
    .project-deadline small {
        display: block;
        margin-top: 2px;
        color: #8b9199;
        font-size: 10px;
    }
    .project-deadline.danger strong,
    .project-deadline.danger small { color: #b42318; }
    .project-deadline.warning small { color: #8a651d; }
    .project-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 31px;
        padding: 6px 10px;
        border: 1px solid #175196;
        border-radius: 5px;
        background: #175196;
        color: #fff;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }
    .project-open:hover { border-color: #183d61; background: #183d61; }

    .archived-empty {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px;
        border: 1px solid #e1e5ea;
        border-radius: 9px;
        background: #fff;
    }
    .archived-empty strong {
        color: #4b5563;
        font-size: 12px;
        font-weight: 600;
    }
    .archived-empty span { color: #858b94; font-size: 10.5px; }
    .archived-list {
        overflow: hidden;
        border: 1px solid #e1e5ea;
        border-radius: 9px;
        background: #fff;
    }
    .archived-row {
        display: grid;
        grid-template-columns: minmax(220px, 1.4fr) 110px 100px 50px;
        gap: 12px;
        align-items: center;
        padding: 12px 14px;
        border-bottom: 1px solid #edf0f3;
        color: #6b7280;
        font-size: 10px;
    }
    .archived-row:last-child { border-bottom: none; }
    .archived-row > div:first-child a {
        display: block;
        color: #303741;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
    }
    .archived-row > div:first-child span {
        display: block;
        margin-top: 2px;
        color: #858b94;
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 9px;
    }
    .archived-row > a {
        color: #175196;
        font-weight: 600;
        text-decoration: none;
    }
    .archived-row > a:hover { text-decoration: underline; }

    @media (max-width: 900px) {
        .page-head-simple { flex-direction: column; gap: 6px; }
        .projects-kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .kpi-item:nth-child(2) { border-right: none; }
        .kpi-item:nth-child(-n+2) { border-bottom: 1px solid #2b3038; }
        .projects-grid { grid-template-columns: 1fr; }
        .archived-empty { align-items: flex-start; flex-direction: column; gap: 5px; }
        .archived-row {
            grid-template-columns: 1fr auto;
            gap: 6px 12px;
        }
        .archived-row > div:nth-child(2),
        .archived-row > div:nth-child(3) { display: none; }
    }
</style>

<script src="teacher_ui.js?v=20261028b"></script>
<script>
    window.TEACHER_FLASH = <?= json_encode(
            $flashMessage !== '' ? array('message' => $flashMessage, 'type' => $flashType) : null,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
</script>

</body>
</html>
