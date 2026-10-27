<?php
$active = 'files';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */


// =========================================================
// 当前老师的 Teachers.id
// =========================================================
$teacherId = 0;
if (isset($_SESSION['userID'])) {
    $stmtT = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtT, "i", $_SESSION['userID']);
    mysqli_stmt_execute($stmtT);
    $rsT = mysqli_stmt_get_result($stmtT);
    $rowT = mysqli_fetch_assoc($rsT);
    mysqli_stmt_close($stmtT);
    if ($rowT) { $teacherId = (int)$rowT['id']; }
}


// =========================================================
// 1. 汇总数字（只看我负责项目的文件）
// =========================================================

// Total uploads (raw rows)
$totalUploads = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE p.supervisor_id = ?
      AND f.task_id IS NOT NULL
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$totalUploads = (int)$rc['c'];
mysqli_stmt_close($stmt);

// Unique file groups: name + team + uploader + task
$totalFiles = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM (
        SELECT 1
        FROM `files` f
        INNER JOIN `projects` p ON p.id = f.project_id
        WHERE p.supervisor_id = ?
          AND f.task_id IS NOT NULL
        GROUP BY LOWER(TRIM(f.file_name)), f.group_id, f.uploader_id, COALESCE(f.task_id, 0)
    ) grouped
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$totalFiles = (int)$rc['c'];
mysqli_stmt_close($stmt);

// This Week
$thisWeek = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE p.supervisor_id = ?
      AND f.task_id IS NOT NULL
      AND f.uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$thisWeek = (int)$rc['c'];
mysqli_stmt_close($stmt);

// Teams Active（有文件的小组数）
$teamsActive = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(DISTINCT f.group_id) AS c
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE p.supervisor_id = ?
      AND f.task_id IS NOT NULL
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$teamsActive = (int)$rc['c'];
mysqli_stmt_close($stmt);

// =========================================================
// 2. Recent Uploads（按 文件+小组+上传者+任务 分组，最近 10 组）
// =========================================================
$recentUploads = array();

$sql = "
    SELECT
        latest.id,
        latest.file_name,
        latest.file_size,
        latest.uploaded_at,
        latest.status,
        latest.task_id,
        latest.group_id,
        latest.uploader_id,
        grp.name_key,
        grp.task_key,
        grp.version_count,
        grp.rejected_count,
        grp.last_rejected_at,
        g.name AS team_name,
        u.username AS uploader_name,
        t.title AS task_title
    FROM (
        SELECT
            LOWER(TRIM(f.file_name)) AS name_key,
            f.group_id,
            f.uploader_id,
            COALESCE(f.task_id, 0) AS task_key,
            COUNT(*) AS version_count,
            SUM(CASE WHEN f.status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count,
            MAX(CASE WHEN f.status = 'Rejected' THEN f.reviewed_at END) AS last_rejected_at,
            MAX(f.id) AS latest_id
        FROM `files` f
        INNER JOIN `projects` p ON p.id = f.project_id
        WHERE p.supervisor_id = ?
          AND f.task_id IS NOT NULL
        GROUP BY name_key, f.group_id, f.uploader_id, task_key
    ) grp
    INNER JOIN `files` latest ON latest.id = grp.latest_id
    LEFT JOIN `groups`   g ON g.id = latest.group_id
    LEFT JOIN `students` s ON s.id = latest.uploader_id
    LEFT JOIN `users`    u ON u.id = s.user_id
    LEFT JOIN `tasks`    t ON t.id = latest.task_id
    ORDER BY latest.uploaded_at DESC, latest.id DESC
    LIMIT 10
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $recentUploads[] = $row; }
mysqli_stmt_close($stmt);

// Load every version for the groups shown above.
if (count($recentUploads) > 0) {
    $stmtV = mysqli_prepare($conn, "
        SELECT id, file_name, file_size, status, uploaded_at, reviewed_at, review_note
        FROM `files`
        WHERE group_id = ?
          AND uploader_id = ?
          AND COALESCE(task_id, 0) = ?
          AND LOWER(TRIM(file_name)) = ?
        ORDER BY id ASC
    ");

    foreach ($recentUploads as &$row) {
        $versions = array();
        $groupId    = (int) $row['group_id'];
        $uploaderId = (int) $row['uploader_id'];
        $taskKey    = (int) $row['task_key'];
        $nameKey    = $row['name_key'];

        mysqli_stmt_bind_param($stmtV, "iiis", $groupId, $uploaderId, $taskKey, $nameKey);
        mysqli_stmt_execute($stmtV);
        $rsV = mysqli_stmt_get_result($stmtV);
        while ($v = mysqli_fetch_assoc($rsV)) {
            $versions[] = $v;
        }
        $row['versions'] = $versions;
    }
    unset($row);
    mysqli_stmt_close($stmtV);
}


// =========================================================
// 3. Filter by Team（只看我负责项目下的组）
// =========================================================
$teamFiles = array();

$sql = "
    SELECT
        g.id           AS group_id,
        g.name         AS team_name,
        COUNT(DISTINCT CONCAT(
            LOWER(TRIM(f.file_name)), '|', f.uploader_id, '|', COALESCE(f.task_id, 0)
        )) AS file_count,
        MAX(f.uploaded_at) AS last_upload
    FROM `groups` g
    INNER JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `files` f ON f.group_id = g.id AND f.task_id IS NOT NULL
    WHERE p.supervisor_id = ?
    GROUP BY g.id, g.name
    HAVING file_count > 0
    ORDER BY file_count DESC, g.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $teamFiles[] = $row; }
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261028a">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Files</h1>
        <p class="subtitle">Task attachments submitted by your teams</p>

        <!-- Summary -->
        <div class="summary-grid summary-grid-3">
            <div class="summary-item">
                <div class="label">Total Files</div>
                <div class="value"><?php echo $totalFiles; ?></div>
                <div class="summary-note"><?php echo $totalUploads; ?> uploads</div>
            </div>
            <div class="summary-item">
                <div class="label">This Week</div>
                <div class="value"><?php echo $thisWeek; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Teams Active</div>
                <div class="value"><?php echo $teamsActive; ?></div>
            </div>
        </div>

        <!-- Recent Uploads -->
        <h2>Recent Uploads</h2>
        <div class="card table-scroll">
            <table class="files-table">
                <tr>
                    <th style="width: 30%;">File Name</th>
                    <th style="width: 14%;">Team</th>
                    <th style="width: 15%;">Uploaded By</th>
                    <th style="width: 11%;">Size</th>
                    <th style="width: 11%;">Last Upload</th>
                    <th style="width: 11%;">Status</th>
                    <th style="width: 50px;"></th>
                </tr>

                <?php if (count($recentUploads) === 0): ?>
                    <tr>
                        <td colspan="7" class="muted" style="text-align: center; padding: 30px;">
                            No task attachments uploaded yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($recentUploads as $idx => $f): ?>
                    <?php
                    $isResubmitted = ((int) $f['rejected_count'] > 0
                        && !empty($f['last_rejected_at'])
                        && strtotime($f['uploaded_at']) > strtotime($f['last_rejected_at'])
                        && $f['status'] !== 'Rejected');
                    $versionCount = (int) $f['version_count'];
                    $rejectedCount = (int) $f['rejected_count'];
                    ?>
                    <tr class="file-group-row">
                        <td>
                            <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                               class="file-name-link"
                               title="<?php echo htmlspecialchars($f['file_name']); ?>">
                                <?php echo htmlspecialchars($f['file_name']); ?>
                            </a>
                            <?php if ($versionCount > 1): ?>
                                <a href="#" class="version-chip history-toggle"
                                   data-target="history-<?php echo (int) $idx; ?>"
                                   title="Show version history">v<?php echo $versionCount; ?> ▾</a>
                            <?php endif; ?>
                            <?php if ($isResubmitted): ?>
                                <span class="resubmit-badge">Resubmitted</span>
                            <?php endif; ?>
                            <div class="file-meta">
                                <?php if ($versionCount > 1): ?>
                                    <?php echo $versionCount; ?> versions
                                <?php endif; ?>
                                <?php if ($rejectedCount > 0): ?>
                                    <?php echo $versionCount > 1 ? '· ' : ''; ?><?php echo $rejectedCount; ?> rejected
                                <?php endif; ?>
                                <?php if (!empty($f['task_title'])): ?>
                                    <?php echo ($versionCount > 1 || $rejectedCount > 0) ? '· ' : ''; ?>Task: <?php echo htmlspecialchars($f['task_title']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($f['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($f['uploader_name']); ?></td>
                        <td class="muted"><?php echo htmlspecialchars($f['file_size']); ?></td>
                        <td class="muted"><?php echo timeAgo($f['uploaded_at']); ?></td>
                        <td>
                            <span class="pill pill-<?php echo strtolower($f['status']); ?>">
                                <?php echo htmlspecialchars($f['status']); ?>
                            </span>
                        </td>
                        <td class="file-actions">
                            <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">View</a>
                        </td>
                    </tr>

                    <?php if ($versionCount > 1): ?>
                    <tr class="history-row" id="history-<?php echo (int) $idx; ?>" hidden>
                        <td colspan="7">
                            <div class="history-panel">
                                <table class="history-table">
                                    <tr>
                                        <th style="width: 8%;">Version</th>
                                        <th style="width: 20%;">Uploaded</th>
                                        <th style="width: 10%;">Size</th>
                                        <th style="width: 12%;">Status</th>
                                        <th>Teacher note</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                    <?php foreach ($f['versions'] as $vi => $v): ?>
                                        <tr>
                                            <td>v<?php echo (int) $vi + 1; ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($v['uploaded_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($v['file_size']); ?></td>
                                            <td>
                                                <span class="pill pill-<?php echo strtolower($v['status']); ?>">
                                                    <?php echo htmlspecialchars($v['status']); ?>
                                                </span>
                                            </td>
                                            <td class="muted"><?php echo htmlspecialchars($v['review_note'] ?: '—'); ?></td>
                                            <td>
                                                <a href="file_view.php?id=<?php echo (int) $v['id']; ?>" class="link">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Filter by Team -->
        <h2>Filter by Team</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team</th>
                    <th>Task Files</th>
                    <th>Last Upload</th>
                    <th></th>
                </tr>

                <?php if (count($teamFiles) === 0): ?>
                    <tr>
                        <td colspan="4" class="muted" style="text-align: center; padding: 30px;">
                            No team has task attachments yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($teamFiles as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['team_name']); ?></td>
                        <td><?php echo (int)$t['file_count']; ?></td>
                        <td class="muted"><?php echo timeAgo($t['last_upload']); ?></td>
                        <td>
                            <a href="teacher_review.php?q=<?php echo urlencode($t['team_name']); ?>"
                               class="link">Open</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</main>

<style>
.summary-grid-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 700px) {
    .summary-grid-3 { grid-template-columns: 1fr; }
}
.table-scroll { overflow-x: auto; }
.files-table { min-width: 720px; }
.files-table td { vertical-align: top; white-space: normal; }
.files-table td.muted,
.files-table .file-actions { white-space: nowrap; }
.summary-note {
    margin-top: 2px;
    color: #8b8f98;
    font-size: 10.5px;
}
.version-chip {
    display: inline-block;
    margin-left: 5px;
    padding: 1px 6px;
    border-radius: 999px;
    background: #eef2f7;
    color: #4b5058;
    font-size: 9.5px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    vertical-align: middle;
}
.version-chip:hover { background: #e2e8f0; color: #175196; }
.version-chip.open { background: #175196; color: #fff; }
.resubmit-badge {
    display: inline-block;
    margin-left: 5px;
    padding: 1px 7px;
    border-radius: 999px;
    background: #e8f0fa;
    color: #175196;
    font-size: 9.5px;
    font-weight: 700;
    vertical-align: middle;
}
.file-meta {
    margin-top: 3px;
    color: #9aa0a8;
    font-size: 10.5px;
    line-height: 1.5;
}
.pill-unviewed { background: #fdf3e2; color: #9a6a13; }
.pill-accepted { background: #e8f6ee; color: #256b3f; }
.pill-rejected { background: #fdecec; color: #a61b1b; }
.history-row[hidden] { display: none; }
.history-row td {
    padding: 8px 16px 14px !important;
    background: #fafbfc;
}
.history-panel {
    padding-left: 12px;
    border-left: 3px solid #dbe5f1;
}
.history-table {
    width: 100%;
    table-layout: auto;
}
.history-table th {
    padding: 6px 8px;
    background: #f2f4f7;
    color: #6b6f78;
    font-size: 9.5px;
    white-space: nowrap;
}
.history-table td {
    padding: 6px 8px !important;
    border-bottom: 1px solid #eef0f2;
    background: transparent !important;
    font-size: 11px;
    white-space: normal;
}
</style>

<script>
document.addEventListener('click', function (event) {
    var toggle = event.target.closest('.history-toggle');
    if (!toggle) return;

    event.preventDefault();
    var row = document.getElementById(toggle.getAttribute('data-target'));
    if (!row) return;

    row.hidden = !row.hidden;
    toggle.classList.toggle('open', !row.hidden);
});
</script>

</body>
</html>
