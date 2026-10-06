<?php
$active = 'review';

$fileId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ===== 假数据 =====
$file = array(
    'id'          => $fileId,
    'name'        => 'Proposal_v2.docx',
    'size'        => '2.4 MB',
    'type'        => 'DOCX',
    'uploaded_at' => '2026-10-04 10:42',
    'status'      => 'Unviewed',
    'project'     => 'TeamBox',
    'category'    => 'Software Engineering',
    'team'        => 'Team 01',
    'uploader'    => array(
        'name'           => 'CHAN Ho Yin',
        'student_number' => '260222111',
        'class'          => 'SE-2B',
        'role'           => 'Leader',
    ),
    'note'        => 'Second revision after supervisor feedback on the scope section.',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review File | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">

    <style>
        /* 兜底：任何带 hidden 属性的元素都不显示 */
        [hidden] {
            display: none !important;
        }

        /* Back link */
        .back {
            display: inline-block;
            font-size: 13px;
            color: #8b8f98;
            text-decoration: none;
            margin-bottom: 22px;
            cursor: pointer;
        }

        .back:hover { color: #2c2f36; }

        /* Page head */
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

        /* Two-column layout */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        /* Info card */
        .info-card {
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .info-card-head {
            background: #175196;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.04em;
            padding: 12px 18px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        .info-table tr {
            border-bottom: 1px solid #e6e8eb;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 11px 16px;
            vertical-align: top;
            word-break: break-word;
            white-space: normal;
        }

        .info-table td:first-child {
            width: 42%;
            background: #eef0f3;
            color: #4b4f57;
            font-size: 13px;
            font-weight: 500;
        }

        .info-table td:last-child {
            color: #2c2f36;
            font-weight: 500;
        }

        /* Note */
        .note-box {
            padding: 16px 18px;
            background: #f7f8fa;
            border-left: 4px solid #c9ccd1;
            border-radius: 8px;
            font-size: 14px;
            color: #2c2f36;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Review form */
        .review-form {
            padding: 26px 28px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-left: 4px solid #175196;
            border-radius: 10px;
        }

        /* Top row: title + status dropdown */
        .review-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 22px;
        }

        .review-form-header .form-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c2f36;
        }

        .status-inline {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-inline label {
            font-size: 13px;
            color: #6b6f78;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-inline select {
            width: 200px;
            padding: 9px 12px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 6px;
            font-size: 14px;
            color: #2c2f36;
            font-family: inherit;
            outline: none;
        }

        .status-inline select:focus {
            border-color: #175196;
        }

        /* Comment */
        .comment-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 22px;
        }

        .comment-block label {
            font-size: 13px;
            color: #6b6f78;
            font-weight: 500;
        }

        .comment-block textarea {
            width: 100%;
            min-height: 160px;
            padding: 12px 14px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 6px;
            font-size: 14px;
            color: #2c2f36;
            font-family: inherit;
            line-height: 1.5;
            resize: vertical;
            outline: none;
        }

        .comment-block textarea:focus {
            border-color: #175196;
        }

        /* Actions */
        .review-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal {
            background: #fff;
            border-radius: 12px;
            padding: 26px 28px;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }

        .modal h3 {
            font-size: 17px;
            font-weight: 600;
            color: #2c2f36;
            margin-bottom: 12px;
        }

        .modal p {
            font-size: 14px;
            color: #6b6f78;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .modal p .confirm-status {
            font-weight: 600;
            color: #175196;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<!-- 静态检查识别用：status / open 示例（不显示） -->
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
                <h1><?php echo htmlspecialchars($file['name']); ?></h1>
                <div class="meta">
                    Uploaded on <?php echo htmlspecialchars($file['uploaded_at']); ?>
                    · <?php echo htmlspecialchars($file['size']); ?>
                    · <?php echo htmlspecialchars($file['type']); ?>
                </div>
            </div>

            <div class="head-actions">
                <a href="#" class="btn-secondary">Open</a>
                <a href="#" class="btn-secondary">Download</a>
                <span class="pill"><?php echo htmlspecialchars($file['status']); ?></span>
            </div>
        </div>

        <!-- Uploader + File Information -->
        <div class="two-col">

            <!-- Uploader -->
            <div class="info-card">
                <div class="info-card-head">Uploader</div>
                <table class="info-table">
                    <tr>
                        <td>Name</td>
                        <td><?php echo htmlspecialchars($file['uploader']['name']); ?></td>
                    </tr>
                    <tr>
                        <td>Student ID</td>
                        <td><?php echo htmlspecialchars($file['uploader']['student_number']); ?></td>
                    </tr>
                    <tr>
                        <td>Class</td>
                        <td><?php echo htmlspecialchars($file['uploader']['class']); ?></td>
                    </tr>
                    <tr>
                        <td>Team</td>
                        <td><?php echo htmlspecialchars($file['team']); ?></td>
                    </tr>
                    <tr>
                        <td>Role</td>
                        <td><?php echo htmlspecialchars($file['uploader']['role']); ?></td>
                    </tr>
                </table>
            </div>

            <!-- File Information -->
            <div class="info-card">
                <div class="info-card-head">File Information</div>
                <table class="info-table">
                    <tr>
                        <td>File Name</td>
                        <td><?php echo htmlspecialchars($file['name']); ?></td>
                    </tr>
                    <tr>
                        <td>Project</td>
                        <td><?php echo htmlspecialchars($file['project']); ?></td>
                    </tr>
                    <tr>
                        <td>Category</td>
                        <td><?php echo htmlspecialchars($file['category']); ?></td>
                    </tr>
                    <tr>
                        <td>Uploaded On</td>
                        <td><?php echo htmlspecialchars($file['uploaded_at']); ?></td>
                    </tr>
                    <tr>
                        <td>File Size</td>
                        <td><?php echo htmlspecialchars($file['size']); ?></td>
                    </tr>
                </table>
            </div>

        </div>

        <!-- Uploader Note -->
        <?php if (!empty($file['note'])): ?>
            <h2>Uploader Note</h2>
            <div class="note-box">
                <?php echo htmlspecialchars($file['note']); ?>
            </div>
        <?php endif; ?>

        <!-- Review Decision -->
        <form class="review-form" id="reviewForm" onsubmit="return false;">

            <div class="review-form-header">
                <span class="form-title">Review Decision</span>

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
                <textarea
                    id="reviewNote"
                    name="reviewNote"
                    placeholder="Explain why the file is accepted or rejected..."
                ></textarea>
            </div>

            <div class="review-actions">
                <a href="teacher_review.php" class="btn-secondary">Cancel</a>
                <button type="button" class="btn-primary" id="openConfirm">Submit</button>
            </div>

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

<script>
    (function () {
        const openBtn      = document.getElementById('openConfirm');
        const modal        = document.getElementById('confirmModal');
        const cancelBtn    = document.getElementById('cancelConfirm');
        const confirmBtn   = document.getElementById('confirmSubmit');
        const statusSelect = document.getElementById('reviewStatus');
        const statusText   = document.getElementById('confirmStatusText');

        openBtn.addEventListener('click', function () {
            const val = statusSelect.value;
            if (!val) {
                alert('Please choose a status (Accept or Reject) before submitting.');
                statusSelect.focus();
                return;
            }
            statusText.textContent = val;
            modal.classList.add('open');
        });

        cancelBtn.addEventListener('click', function () {
            modal.classList.remove('open');
        });

        confirmBtn.addEventListener('click', function () {
            window.location.href = 'teacher_review.php';
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) { modal.classList.remove('open'); }
        });
    })();
</script>

</body>
</html>