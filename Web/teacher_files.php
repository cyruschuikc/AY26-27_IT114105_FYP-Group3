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
// 1. 汇总数字
// =========================================================

// Total Files
$totalFiles = 0;
$rs = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `Files`");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $totalFiles = (int)$rc['c']; mysqli_free_result($rs); }

// This Week（过去 7 天）
$thisWeek = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(*) AS c FROM `Files`
    WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $thisWeek = (int)$rc['c']; mysqli_free_result($rs); }

// Teams Active（有文件的小组数）
$teamsActive = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(DISTINCT group_id) AS c FROM `Files`
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $teamsActive = (int)$rc['c']; mysqli_free_result($rs); }

// Storage Used（把 file_size 加起来 — 我们的 file_size 是字符串 "2.4 MB"，需要解析）
$storageUsedBytes = 0;
$rs = mysqli_query($conn, "SELECT file_size FROM `Files`");
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $storageUsedBytes += parseSizeToBytes($row['file_size']);
    }
    mysqli_free_result($rs);
}
$storageUsed = formatBytes($storageUsedBytes);


// =========================================================
// 2. Recent Uploads（最近 10 条）
// =========================================================
$recentUploads = array();

$sql = "
    SELECT
        f.id,
        f.file_name,
        f.file_size,
        f.uploaded_at,
        g.name     AS team_name,
        u.username AS uploader_name
    FROM `Files` f
    LEFT JOIN `Groups`   g ON g.id = f.group_id
    LEFT JOIN `Students` s ON s.id = f.uploader_id
    LEFT JOIN `Users`    u ON u.id = s.user_id
    ORDER BY f.uploaded_at DESC
    LIMIT 10
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) { $recentUploads[] = $row; }
    mysqli_free_result($rs);
}


// =========================================================
// 3. Filter by Team（每个 team 的文件数 + 最近上传时间）
// =========================================================
$teamFiles = array();

$sql = "
    SELECT
        g.id           AS group_id,
        g.name         AS team_name,
        COUNT(f.id)    AS file_count,
        MAX(f.uploaded_at) AS last_upload
    FROM `Groups` g
    LEFT JOIN `Files` f ON f.group_id = g.id
    GROUP BY g.id, g.name
    HAVING file_count > 0
    ORDER BY file_count DESC, g.id
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) { $teamFiles[] = $row; }
    mysqli_free_result($rs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Files</h1>
        <p class="subtitle">All project files uploaded by your teams</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Files</div>
                <div class="value"><?php echo $totalFiles; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">This Week</div>
                <div class="value"><?php echo $thisWeek; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Teams Active</div>
                <div class="value"><?php echo $teamsActive; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Storage Used</div>
                <div class="value"><?php echo htmlspecialchars($storageUsed); ?></div>
            </div>
        </div>

        <!-- Recent Uploads -->
        <h2>Recent Uploads</h2>
        <div class="card">
            <table>
                <tr>
                    <th>File Name</th>
                    <th>Team</th>
                    <th>Uploaded By</th>
                    <th>Size</th>
                    <th>Date</th>
                    <th></th>
                </tr>

                <?php if (count($recentUploads) === 0): ?>
                    <tr>
                        <td colspan="6" class="muted" style="text-align: center; padding: 30px;">
                            No files uploaded yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($recentUploads as $f): ?>
                    <tr>
                        <td>
                            <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                               class="file-name-link"
                               title="<?php echo htmlspecialchars($f['file_name']); ?>">
                                <?php echo htmlspecialchars($f['file_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($f['team_name']); ?></td>
                        <td><?php echo htmlspecialchars($f['uploader_name']); ?></td>
                        <td class="muted"><?php echo htmlspecialchars($f['file_size']); ?></td>
                        <td class="muted"><?php echo timeAgo($f['uploaded_at']); ?></td>
                        <td>
                            <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">View</a>
                            &nbsp;·&nbsp;
                            <a href="#" class="link">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Filter by Team -->
        <h2>Filter by Team</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team</th>
                    <th>Files</th>
                    <th>Last Upload</th>
                    <th></th>
                </tr>

                <?php if (count($teamFiles) === 0): ?>
                    <tr>
                        <td colspan="4" class="muted" style="text-align: center; padding: 30px;">
                            No team has uploaded any files yet.
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

</body>
</html>