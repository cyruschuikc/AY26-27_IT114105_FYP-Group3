<?php
$active = 'review';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */

// 没登录 → 回登录页
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// 拿当前老师的 Teachers.id
$teacherId = 0;
$stmt = mysqli_prepare($conn, "SELECT id FROM `Teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
if ($row) { $teacherId = (int)$row['id']; }
$teacherFlash = null;
if (isset($_SESSION['teacher_flash'])) {
    $teacherFlash = $_SESSION['teacher_flash'];
    unset($_SESSION['teacher_flash']);
}

$fileId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$queueMode = isset($_GET['queue']) && $_GET['queue'] === '1';


// =========================================================
// 1. 处理提交（POST）
// =========================================================
if (isset($_POST['submitReview'])) {

    $newStatus  = isset($_POST['reviewStatus']) ? trim($_POST['reviewStatus']) : '';
    $reviewNote = isset($_POST['reviewNote'])   ? trim($_POST['reviewNote'])   : '';

    if ($newStatus === 'Accepted' || $newStatus === 'Rejected') {

        $stmt = mysqli_prepare($conn, "
            UPDATE `Files`
            SET status = ?,
                reviewed_by = ?,
                reviewed_at = NOW(),
                review_note = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "sisi", $newStatus, $teacherId, $reviewNote, $fileId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // 记一条 Activity
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `Activities` (project_id, group_id, actor_id, action, detail)
            SELECT project_id, group_id, ?, 'reviewed',
                   CONCAT('marked file ', file_name, ' as ', ?)
            FROM `Files`
            WHERE id = ?
        ");
        $actionWord = ($newStatus === 'Accepted') ? 'accepted' : 'rejected';
        mysqli_stmt_bind_param($stmt, "iss", $userID, $actionWord, $fileId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['teacher_flash'] = array(
            'message' => 'File review saved as ' . strtolower($newStatus) . '.',
            'type' => $newStatus === 'Accepted' ? 'success' : 'warning',
        );
    }

    $nextFileId = 0;
    if ($queueMode) {
        $stmt = mysqli_prepare($conn, "
            SELECT f.id
            FROM `files` f
            INNER JOIN `projects` p ON p.id = f.project_id
            WHERE f.status = 'Unviewed'
              AND f.task_id IS NOT NULL
              AND p.supervisor_id = ?
            ORDER BY f.uploaded_at ASC, f.id ASC
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $teacherId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $nextFile = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);
        if ($nextFile) $nextFileId = (int) $nextFile['id'];
    }

    header('Location: ' . ($nextFileId > 0
        ? 'file_view.php?id=' . $nextFileId . '&queue=1'
        : 'teacher_review.php'));
    exit;
}


// =========================================================
// 2. 读文件信息
// =========================================================
$file = null;

$sql = "
    SELECT
        f.id,
        f.file_name,
        f.stored_name,
        f.file_size,
        f.file_type,
        f.status,
        f.uploaded_at,
        f.review_note,
        f.reviewed_at,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))) AS version_count,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id <= f.id) AS version_number,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id < f.id
              AND fv.status = 'Rejected') AS prior_rejections,
        p.name  AS project_name,
        p.category AS project_category,
        g.id    AS group_id,
        g.name  AS team_name,
        s.id    AS uploader_id,
        s.student_number,
        s.major,
        s.grade_level,
        c.class_code,
        u.username AS uploader_name,
        gm.role AS team_role,
        tu.username AS reviewer_name
    FROM `Files` f
    LEFT JOIN `Projects`  p ON p.id = f.project_id
    LEFT JOIN `Groups`    g ON g.id = f.group_id
    LEFT JOIN `Students`  s ON s.id = f.uploader_id
    LEFT JOIN `Users`     u ON u.id = s.user_id
    LEFT JOIN `classes`   c ON c.id = s.class_id
    LEFT JOIN `Group_Members` gm ON gm.group_id = g.id AND gm.student_id = s.id
    LEFT JOIN `Teachers`  t ON t.id = f.reviewed_by
    LEFT JOIN `Users`    tu ON tu.id = t.user_id
    WHERE f.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $fileId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$file = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$file) {
    header('Location: teacher_review.php');
    exit;
}

// Physical file check (the database can contain records for files that are
// no longer present on this server, e.g. after moving the project folder).
$filePath = '';
if (!empty($file['stored_name'])) {
    $filePath = __DIR__ . '/uploads/chat_files/' . basename($file['stored_name']);
}
$fileAvailable = ($filePath !== '' && is_file($filePath));
$fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
$previewType = 'none';
if (in_array($fileExtension, array('png', 'jpg', 'jpeg', 'gif', 'webp'), true)) {
    $previewType = 'image';
} elseif (in_array($fileExtension, array('pdf', 'txt'), true)) {
    $previewType = 'document';
}

$versionHistory = array();
$stmt = mysqli_prepare($conn, "
    SELECT id, status, uploaded_at, reviewed_at, review_note
    FROM `files`
    WHERE group_id = ?
      AND uploader_id = ?
      AND COALESCE(task_id, 0) = COALESCE(?, 0)
      AND LOWER(TRIM(file_name)) = LOWER(TRIM(?))
    ORDER BY uploaded_at ASC, id ASC
");
mysqli_stmt_bind_param(
    $stmt,
    "iiis",
    $file['group_id'],
    $file['uploader_id'],
    $file['task_id'],
    $file['file_name']
);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $versionHistory[] = $row;
}
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review File | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261028b">
    <link rel="stylesheet" href="teacher_ui.css?v=20261028b">

    <style>
        /* 兜底：任何带 hidden 属性的元素都不显示 */
        [hidden] {
            display: none !important;
        }

        /* Page head（file_view.php 专属） */
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            gap: 20px;
        }

        .page-head .title-block {
            min-width: 0;
        }

        .page-head h1 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .page-head .meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .page-head .head-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
            align-items: center;
        }

        .btn-secondary.is-disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: auto;
        }

        .file-warning {
            margin: -18px 0 24px;
            padding: 11px 15px;
            border: 1px solid #f0d5a8;
            border-radius: 8px;
            background: #fdf7ea;
            color: #8a5a10;
            font-size: 12.5px;
            line-height: 1.55;
        }
        .file-preview-card {
            margin-bottom: 24px;
            overflow: hidden;
            border: 1px solid #e2e6ea;
            border-radius: 9px;
            background: #fff;
        }
        .file-preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 14px;
            border-bottom: 1px solid #edf0f3;
            color: #39424d;
            font-size: 12px;
            font-weight: 700;
        }
        .file-preview-body {
            display: grid;
            min-height: 420px;
            place-items: center;
            padding: 12px;
            background: #f7f8fa;
        }
        .file-preview-body iframe {
            width: 100%;
            height: 520px;
            border: 0;
            border-radius: 6px;
            background: #fff;
        }
        .file-preview-body img {
            display: block;
            max-width: 100%;
            max-height: 640px;
            border-radius: 6px;
            object-fit: contain;
        }
        .version-badge {
            display: inline-block;
            margin-left: 7px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #edf4fb;
            color: #315f87;
            font-size: 10px;
            font-weight: 700;
        }
        .version-history {
            margin-bottom: 24px;
            overflow: hidden;
            border: 1px solid #e2e6ea;
            border-radius: 9px;
            background: #fff;
        }
        .version-row {
            display: grid;
            grid-template-columns: 84px minmax(0, 1fr) 90px;
            gap: 12px;
            align-items: center;
            padding: 10px 14px;
            border-bottom: 1px solid #eef0f2;
            color: #59616b;
            font-size: 11.5px;
        }
        .version-row:last-child { border-bottom: 0; }
        .version-row.is-current { background: #f5f8fb; }
        .version-row a { color: #315f87; font-weight: 600; text-decoration: none; }
        .version-note {
            grid-column: 2 / -1;
            color: #7b838d;
            font-size: 10.5px;
            line-height: 1.45;
        }
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .quick-reply-btn {
            padding: 5px 8px;
            border: 1px solid #d7dee5;
            border-radius: 5px;
            background: #fff;
            color: #56616d;
            font-family: inherit;
            font-size: 10.5px;
            cursor: pointer;
        }
        .quick-reply-btn:hover { border-color: #8fa7bd; background: #f5f8fb; }
        .shortcut-hint {
            margin-left: 8px;
            color: #8b929b;
            font-size: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<!-- 静态检查识别用 -->
<span class="status-pill unviewed" hidden></span>
<span class="status-pill accepted" hidden></span>
<span class="status-pill rejected" hidden></span>
<div class="modal-overlay open" hidden></div>

<main class="main">
    <div class="content">

        <a href="teacher_review.php" class="back">← Back to Review Center</a>

        <!-- Page head -->
        <div class="page-head">
            <div class="title-block">
                <h1>
                    <?php echo htmlspecialchars($file['file_name']); ?>
                    <?php if ((int) $file['version_count'] > 1): ?>
                        <span class="version-badge">Version <?= (int) $file['version_number'] ?> of <?= (int) $file['version_count'] ?></span>
                    <?php endif; ?>
                    <?php if ((int) $file['prior_rejections'] > 0): ?>
                        <span class="version-badge">Resubmitted</span>
                    <?php endif; ?>
                </h1>
                <div class="meta">
                    Uploaded on <?php echo date('Y-m-d H:i', strtotime($file['uploaded_at'])); ?>
                    · <?php echo htmlspecialchars($file['file_size']); ?>
                    · <?php echo htmlspecialchars($file['file_type']); ?>
                </div>
            </div>

            <div class="head-actions">
                <?php if ($fileAvailable): ?>
                    <a href="open_file.php?id=<?php echo (int)$file['id']; ?>"
                       target="_blank" rel="noopener" class="btn-secondary">Open</a>
                    <a href="download_file.php?id=<?php echo (int)$file['id']; ?>"
                       class="btn-secondary">Download</a>
                <?php else: ?>
                    <span class="btn-secondary is-disabled" title="The uploaded file is missing from this server.">Open</span>
                    <span class="btn-secondary is-disabled" title="The uploaded file is missing from this server.">Download</span>
                <?php endif; ?>
                <span class="pill"><?php echo htmlspecialchars($file['status']); ?></span>
            </div>
        </div>

        <?php if (!$fileAvailable): ?>
            <div class="file-warning">
                This file record exists, but the uploaded file is missing from the server.
                Ask the student to upload it again to preview or download it.
            </div>
        <?php endif; ?>

        <?php if ($fileAvailable && $previewType !== 'none'): ?>
            <section class="file-preview-card">
                <div class="file-preview-head">
                    <span>File Preview</span>
                    <a href="open_file.php?id=<?= (int) $file['id'] ?>" target="_blank" rel="noopener" class="link">Open in new tab</a>
                </div>
                <div class="file-preview-body">
                    <?php if ($previewType === 'image'): ?>
                        <img src="open_file.php?id=<?= (int) $file['id'] ?>"
                             alt="Preview of <?= htmlspecialchars($file['file_name']) ?>">
                    <?php else: ?>
                        <iframe src="open_file.php?id=<?= (int) $file['id'] ?>"
                                title="Preview of <?= htmlspecialchars($file['file_name']) ?>"></iframe>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Uploader + File Information -->
        <div class="two-col">

            <!-- Uploader -->
            <div class="info-card">
                <div class="info-card-head">Uploader</div>
                <table class="info-table">
                    <tr>
                        <td>Name</td>
                        <td><?php echo htmlspecialchars($file['uploader_name']); ?></td>
                    </tr>
                    <tr>
                        <td>Student ID</td>
                        <td><?php echo htmlspecialchars($file['student_number']); ?></td>
                    </tr>
                    <tr>
                        <td>Class</td>
                    <td><?php echo htmlspecialchars(formatClass($file['major'], $file['grade_level'], $file['class_code'] ?? null)); ?></td>
                    </tr>
                    <tr>
                        <td>Team</td>
                        <td><?php echo htmlspecialchars($file['team_name']); ?></td>
                    </tr>
                    <tr>
                        <td>Role</td>
                        <td>
                            <?php if ($file['team_role'] === 'leader'): ?>
                                <span class="pill">Leader</span>
                            <?php else: ?>
                                <span class="muted">Member</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- File Information -->
            <div class="info-card">
                <div class="info-card-head">File Information</div>
                <table class="info-table">
                    <tr>
                        <td>File Name</td>
                        <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                    </tr>
                    <tr>
                        <td>Project</td>
                        <td><?php echo htmlspecialchars($file['project_name']); ?></td>
                    </tr>
                    <tr>
                        <td>Category</td>
                        <td><?php echo htmlspecialchars($file['project_category']); ?></td>
                    </tr>
                    <tr>
                        <td>Uploaded On</td>
                        <td><?php echo date('Y-m-d H:i', strtotime($file['uploaded_at'])); ?></td>
                    </tr>
                    <tr>
                        <td>File Size</td>
                        <td><?php echo htmlspecialchars($file['file_size']); ?></td>
                    </tr>
                </table>
            </div>

        </div>

        <?php if (count($versionHistory) > 1): ?>
            <h2>Version History</h2>
            <div class="version-history">
                <?php foreach ($versionHistory as $historyIndex => $version): ?>
                    <div class="version-row <?= (int) $version['id'] === (int) $file['id'] ? 'is-current' : '' ?>">
                        <span>Version <?= $historyIndex + 1 ?></span>
                        <span>
                            <?= date('d M Y H:i', strtotime($version['uploaded_at'])) ?>
                            · <?= htmlspecialchars($version['status']) ?>
                        </span>
                        <?php if ((int) $version['id'] === (int) $file['id']): ?>
                            <span>Current</span>
                        <?php else: ?>
                            <a href="file_view.php?id=<?= (int) $version['id'] ?><?= $queueMode ? '&queue=1' : '' ?>">View</a>
                        <?php endif; ?>
                        <?php if (!empty($version['review_note'])): ?>
                            <div class="version-note"><?= htmlspecialchars($version['review_note']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Previous Review Note（如果有） -->
        <?php if (!empty($file['review_note'])): ?>
            <h2>Previous Review Note</h2>
            <div class="note-box">
                <?php echo htmlspecialchars($file['review_note']); ?>
            </div>
        <?php endif; ?>

        <!-- Review Decision -->
        <form class="review-form" id="reviewForm" method="post" action="file_view.php?id=<?php echo (int)$file['id']; ?>">

            <div class="review-form-header">
                <span>
                    <span class="form-title">Review Decision</span>
                    <span class="shortcut-hint">Shortcuts: A approve · R reject</span>
                </span>

                <div class="status-inline">
                    <label for="reviewStatus">Status</label>
                    <select id="reviewStatus" name="reviewStatus" required>
                        <option value="">-- Select --</option>
                        <option value="Accepted" <?php echo $file['status']==='Accepted'?'selected':''; ?>>Accept</option>
                        <option value="Rejected" <?php echo $file['status']==='Rejected'?'selected':''; ?>>Reject</option>
                    </select>
                </div>
            </div>

            <div class="comment-block">
                <label for="reviewNote">Comment for the student (optional)</label>
                <div class="quick-replies" aria-label="Quick feedback templates">
                    <button type="button" class="quick-reply-btn" data-reply="The required file is missing or incomplete.">Missing file</button>
                    <button type="button" class="quick-reply-btn" data-reply="Please provide more detail and supporting evidence.">Insufficient detail</button>
                    <button type="button" class="quick-reply-btn" data-reply="Please upload the correct version of the file.">Wrong version</button>
                    <button type="button" class="quick-reply-btn" data-reply="Please cite your sources and explain how they support the work.">Add citations</button>
                </div>
                <textarea
                        id="reviewNote"
                        name="reviewNote"
                        placeholder="Explain why the file is accepted or rejected..."
                ><?php echo htmlspecialchars($file['review_note']); ?></textarea>
            </div>

            <div class="review-actions">
                <a href="teacher_review.php" class="btn-secondary">Cancel</a>
                <button type="button" class="btn-primary" id="openConfirm">
                    <?= $queueMode ? 'Submit &amp; Next' : 'Submit' ?>
                </button>
            </div>

            <input type="hidden" name="submitReview" value="1">
        </form>

    </div>
</main>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <h3>Confirm Submission</h3>
        <p>
            This file will be marked as
            <span class="confirm-status" id="confirmStatusText">—</span>.<br>
            Once submitted, it can no longer be changed.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" id="cancelConfirm">Cancel</button>
            <button type="button" class="btn-primary" id="confirmSubmit">OK</button>
        </div>
    </div>
</div>

<script src="teacher_ui.js?v=20261028b"></script>
<script>
    window.TEACHER_FLASH = <?= json_encode(
        $teacherFlash,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;

    (function () {
        const openBtn      = document.getElementById('openConfirm');
        const modal        = document.getElementById('confirmModal');
        const cancelBtn    = document.getElementById('cancelConfirm');
        const confirmBtn   = document.getElementById('confirmSubmit');
        const statusSelect = document.getElementById('reviewStatus');
        const statusText   = document.getElementById('confirmStatusText');
        const form         = document.getElementById('reviewForm');

        openBtn.addEventListener('click', function () {
            const val = statusSelect.value;
            if (!val) {
                TeamBoxUI.toast('Please choose Accept or Reject before submitting.', 'warning');
                statusSelect.focus();
                return;
            }
            statusText.textContent = val;
            TeamBoxUI.openModal(modal);
        });

        cancelBtn.addEventListener('click', function () {
            TeamBoxUI.closeModal(modal);
        });

        confirmBtn.addEventListener('click', function () {
            form.submit();
        });

        document.querySelectorAll('.quick-reply-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const note = document.getElementById('reviewNote');
                note.value = button.getAttribute('data-reply') || '';
                note.focus();
            });
        });

        document.addEventListener('keydown', function (event) {
            const tag = event.target.tagName;
            if (event.ctrlKey || event.metaKey || event.altKey
                || tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
                return;
            }
            const key = event.key.toLowerCase();
            if (key === 'a') {
                statusSelect.value = 'Accepted';
                openBtn.click();
            } else if (key === 'r') {
                statusSelect.value = 'Rejected';
                openBtn.click();
            }
        });
    })();
</script>

</body>
</html>
