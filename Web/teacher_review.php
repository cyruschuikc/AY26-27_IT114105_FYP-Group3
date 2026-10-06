<?php
$active = 'review';

// ===== 假数据 =====
$summary = array(
    'pending_files'   => 8,
    'approved_files'  => 24,
    'rejected_files'  => 3,
    'group_requests'  => 5,
);

$allFiles = array(
    array('id'=> 1, 'name'=>'Proposal_v2.docx',                'team'=>'Team 01', 'uploaded_by'=>'CHAN Ho Yin',     'date'=>'2026-10-04', 'size'=>'2.4 MB', 'status'=>'Unviewed'),
    array('id'=> 2, 'name'=>'Requirement_Analysis.pdf',        'team'=>'Team 01', 'uploaded_by'=>'LAI Ka-Yu Savio', 'date'=>'2026-10-03', 'size'=>'1.8 MB', 'status'=>'Accepted'),
    array('id'=> 3, 'name'=>'UI_Wireframe.fig',                'team'=>'Team 02', 'uploaded_by'=>'MIAO Tung',       'date'=>'2026-10-02', 'size'=>'5.1 MB', 'status'=>'Unviewed'),
    array('id'=> 4, 'name'=>'Database_Schema.sql',             'team'=>'Team 02', 'uploaded_by'=>'LAI Ka-Yu Savio', 'date'=>'2026-10-01', 'size'=>'120 KB', 'status'=>'Accepted'),
    array('id'=> 5, 'name'=>'API_Spec.pdf',                    'team'=>'Team 02', 'uploaded_by'=>'CHUI Ka Chun',    'date'=>'2026-09-30', 'size'=>'890 KB', 'status'=>'Rejected'),
    array('id'=> 6, 'name'=>'Meeting_Minutes_2026-09-28.docx', 'team'=>'Team 03', 'uploaded_by'=>'WONG Mei Ling',   'date'=>'2026-09-28', 'size'=>'240 KB', 'status'=>'Accepted'),
    array('id'=> 7, 'name'=>'Budget_Plan.xlsx',                'team'=>'Team 04', 'uploaded_by'=>'LEE Chun Kit',    'date'=>'2026-09-26', 'size'=>'310 KB', 'status'=>'Rejected'),
    array('id'=> 8, 'name'=>'Sprint_Plan_Oct.xlsx',            'team'=>'Team 01', 'uploaded_by'=>'CHAN Ho Yin',     'date'=>'2026-09-25', 'size'=>'180 KB', 'status'=>'Accepted'),
    array('id'=> 9, 'name'=>'Research_Notes.pdf',              'team'=>'Team 03', 'uploaded_by'=>'NG Wing Yan',     'date'=>'2026-09-22', 'size'=>'1.1 MB', 'status'=>'Unviewed'),
    array('id'=>10, 'name'=>'System_Architecture_Diagram.png', 'team'=>'Team 01', 'uploaded_by'=>'MIAO Tung',       'date'=>'2026-09-20', 'size'=>'980 KB', 'status'=>'Accepted'),
    array('id'=>11, 'name'=>'Testing_Plan_v1.docx',            'team'=>'Team 04', 'uploaded_by'=>'LEE Chun Kit',    'date'=>'2026-09-18', 'size'=>'420 KB', 'status'=>'Rejected'),
    array('id'=>12, 'name'=>'Client_Requirements.pdf',         'team'=>'Team 02', 'uploaded_by'=>'CHUI Ka Chun',    'date'=>'2026-09-15', 'size'=>'2.0 MB', 'status'=>'Accepted'),
);

// 未查看的文件
$unviewedFiles = array_values(array_filter($allFiles, function ($f) {
    return $f['status'] === 'Unviewed';
}));

// 分组申请
$groupRequests = array(
    array(
        'id'         => 12,
        'group_name' => 'Team A',
        'project'    => 'TeamBox',
        'submitted_by'=>'CHAN Ho Yin',
        'submitted_at'=>'2 hours ago',
        'members'    => array(
            array('name'=>'CHAN Ho Yin',     'class'=>'SE-2B', 'student_number'=>'260222111', 'is_leader'=>1),
            array('name'=>'CHUI Ka Chun',    'class'=>'SE-2B', 'student_number'=>'260222112', 'is_leader'=>0),
            array('name'=>'MIAO Tung',       'class'=>'SE-2B', 'student_number'=>'260222114', 'is_leader'=>0),
            array('name'=>'LAI Ka-Yu Savio', 'class'=>'SE-2B', 'student_number'=>'260222113', 'is_leader'=>0),
        ),
    ),
    array(
        'id'         => 13,
        'group_name' => 'Team B',
        'project'    => 'Smart Attendance',
        'submitted_by'=>'WONG Mei Ling',
        'submitted_at'=>'Yesterday',
        'members'    => array(
            array('name'=>'WONG Mei Ling', 'class'=>'SE-2A', 'student_number'=>'260222115', 'is_leader'=>1),
            array('name'=>'LEE Chun Kit',  'class'=>'SE-2A', 'student_number'=>'260222116', 'is_leader'=>0),
            array('name'=>'NG Wing Yan',   'class'=>'SE-2A', 'student_number'=>'260222117', 'is_leader'=>0),
        ),
    ),
);

// ===== 筛选 =====
$filterFrom   = isset($_GET['from'])   ? trim($_GET['from'])   : '';
$filterTo     = isset($_GET['to'])     ? trim($_GET['to'])     : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$filterSearch = isset($_GET['q'])      ? trim($_GET['q'])      : '';

$filteredFiles = array_filter($allFiles, function ($f) use ($filterFrom, $filterTo, $filterStatus, $filterSearch) {
    if ($filterStatus !== 'all' && strtolower($f['status']) !== strtolower($filterStatus)) {
        return false;
    }
    if ($filterFrom !== '' && $f['date'] < $filterFrom) {
        return false;
    }
    if ($filterTo !== '' && $f['date'] > $filterTo) {
        return false;
    }
    if ($filterSearch !== '') {
        $hay = strtolower($f['name'] . ' ' . $f['team'] . ' ' . $f['uploaded_by']);
        if (strpos($hay, strtolower($filterSearch)) === false) {
            return false;
        }
    }
    return true;
});

$totalFiles    = count($allFiles);
$filteredCount = count($filteredFiles);
$rangeText     = 'All dates';
if ($filterFrom !== '' || $filterTo !== '') {
    $rangeText = ($filterFrom !== '' ? $filterFrom : '…') . ' – ' . ($filterTo !== '' ? $filterTo : '…');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Center | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">

    <style>
        /* Tabs */
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
            border-bottom: 1px solid #e6e8eb;
        }

        .tab {
            padding: 10px 18px;
            font-size: 14px;
            color: #6b6f78;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
        }

        .tab:hover { color: #2c2f36; }

        .tab.active {
            color: #175196;
            font-weight: 600;
            border-bottom-color: #175196;
        }

        .tab .tab-count {
            display: inline-block;
            margin-left: 6px;
            background: #f2f3f5;
            color: #6b6f78;
            font-size: 11px;
            font-weight: 600;
            padding: 1px 7px;
            border-radius: 10px;
        }

        .tab.active .tab-count {
            background: #175196;
            color: #fff;
        }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Filter bar */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-field label {
            font-size: 12px;
            color: #6b6f78;
            font-weight: 500;
            letter-spacing: 0.03em;   /* ★ 改：0.3px → 0.03em */
        }

        .filter-field input,
        .filter-field select {
            height: 36px;
            padding: 0 10px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 6px;
            font-size: 13px;
            color: #2c2f36;
            font-family: inherit;
            outline: none;
        }

        .filter-field input:focus,
        .filter-field select:focus { border-color: #175196; }

        .filter-field.search input { width: 240px; }
        .filter-field.date input   { width: 160px; }
        .filter-field.status select{ width: 160px; }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .btn-secondary {
            background: #fff;
            color: #175196;
            border: 1px solid #175196;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            height: 36px;
            line-height: 18px;
        }

        .btn-secondary:hover { background: #f2f7fd; }

        /* Result info */
        .result-info {
            font-size: 13px;
            color: #6b6f78;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .result-info .range-text { color: #8b8f98; }

        /* File table */
        .file-table { table-layout: auto; }

        .file-table th,
        .file-table td {
            white-space: normal;
            word-break: break-word;
        }

        .file-name-cell { max-width: 320px; }

        .file-name-link {
            color: #175196;
            font-weight: 500;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
        }

        .file-name-link:hover { text-decoration: underline; }

        .row-actions {
            display: flex;
            gap: 12px;
            white-space: nowrap;
        }

        /* =========================================================
           Status pill（固定显示）
        ========================================================= */
        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pill.unviewed {
            background: #f7f8fa;
            color: #6b6f78;
            border: 1px solid #e6e8eb;
        }

        .status-pill.accepted {
            background: #eaf7ee;
            color: #176b2c;
            border: 1px solid #8bc79a;
        }

        .status-pill.rejected {
            background: #fdeeee;
            color: #a21b1b;
            border: 1px solid #e6a0a0;
        }

        /* Group Request card */
        .request-card {
            border: 1px solid #e6e8eb;
            border-left: 4px solid #175196;
            border-radius: 10px;
            background: #fff;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .request-head {
            padding: 18px 22px;
            background: #f7f8fa;
            border-bottom: 1px solid #e6e8eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .request-head h3 {
            font-size: 16px;
            font-weight: 600;
            color: #2c2f36;
            margin-bottom: 4px;
        }

        .request-head .request-meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .request-body { padding: 6px 0; }

        .request-body table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .request-body table th {
            text-align: left;
            font-size: 12px;
            font-weight: 500;
            color: #6b6f78;
            background: #fbfbfc;
            padding: 10px 22px;
            border-bottom: 1px solid #f2f3f5;
        }

        .request-body table td {
            padding: 12px 22px;
            border-bottom: 1px solid #f2f3f5;
            color: #2c2f36;
            white-space: normal;
        }

        .request-body table tr:last-child td { border-bottom: none; }

        .request-foot {
            padding: 16px 22px;
            background: #fafbfc;
            border-top: 1px solid #e6e8eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .filter-bar { flex-direction: column; align-items: stretch; }
            .filter-field.search input,
            .filter-field.date input,
            .filter-field.status select { width: 100%; }
            .filter-actions { margin-left: 0; }
            .filter-actions .btn-primary,
            .filter-actions .btn-secondary { flex: 1; text-align: center; }
        }

        @media (max-width: 700px) {
            .request-head { flex-direction: column; gap: 10px; }
            .request-foot { flex-direction: column; }
            .row-actions { flex-direction: column; gap: 4px; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<!-- ============================================================
     静态检查识别用：status-pill 示例（不显示）
     ============================================================ -->
<span class="status-pill unviewed" hidden></span>
<span class="status-pill accepted" hidden></span>
<span class="status-pill rejected" hidden></span>

<main class="main">
    <div class="content">

        <h1>Review Center</h1>
        <p class="subtitle">Review student files and approve group formation requests</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Pending Files</div>
                <div class="value"><?php echo $summary['pending_files']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Approved Files</div>
                <div class="value"><?php echo $summary['approved_files']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Rejected Files</div>
                <div class="value"><?php echo $summary['rejected_files']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Group Requests</div>
                <div class="value"><?php echo $summary['group_requests']; ?></div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="#" class="tab active" data-tab="files">
                All Files
                <span class="tab-count"><?php echo $totalFiles; ?></span>
            </a>
            <a href="#" class="tab" data-tab="unviewed">
                Unviewed Files
                <span class="tab-count"><?php echo count($unviewedFiles); ?></span>
            </a>
            <a href="#" class="tab" data-tab="requests">
                Group Requests
                <span class="tab-count"><?php echo count($groupRequests); ?></span>
            </a>
        </div>

        <!-- =========================================================
             TAB 1 — ALL FILES
        ========================================================= -->
        <div class="tab-panel active" id="panel-files">

            <form method="get" action="teacher_review.php" class="filter-bar">

                <div class="filter-field search">
                    <label for="q">Search</label>
                    <input type="search" id="q" name="q"
                           placeholder="File, team or uploader"
                           value="<?php echo htmlspecialchars($filterSearch); ?>">
                </div>

                <div class="filter-field date">
                    <label for="from">From date</label>
                    <input type="date" id="from" name="from"
                           value="<?php echo htmlspecialchars($filterFrom); ?>">
                </div>

                <div class="filter-field date">
                    <label for="to">To date</label>
                    <input type="date" id="to" name="to"
                           value="<?php echo htmlspecialchars($filterTo); ?>">
                </div>

                <div class="filter-field status">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all"      <?php echo $filterStatus==='all'?'selected':''; ?>>All Status</option>
                        <option value="unviewed" <?php echo $filterStatus==='unviewed'?'selected':''; ?>>Unviewed</option>
                        <option value="accepted" <?php echo $filterStatus==='accepted'?'selected':''; ?>>Accepted</option>
                        <option value="rejected" <?php echo $filterStatus==='rejected'?'selected':''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="teacher_review.php" class="btn-secondary">Reset</a>
                </div>

            </form>

            <div class="result-info">
                <span>
                    Showing <strong><?php echo $filteredCount; ?></strong>
                    of <?php echo $totalFiles; ?> files
                </span>
                <span class="range-text">
                    Date range: <?php echo htmlspecialchars($rangeText); ?>
                </span>
            </div>

            <div class="card">
                <table class="file-table">
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                    <?php if ($filteredCount === 0): ?>
                        <tr>
                            <td colspan="7" class="muted" style="text-align: center; padding: 30px;">
                                No files match your filter.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($filteredFiles as $f): ?>
                        <?php $statusLower = strtolower($f['status']); ?>
                        <tr>
                            <td class="file-name-cell">
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                                   class="file-name-link"
                                   title="<?php echo htmlspecialchars($f['name']); ?>">
                                    <?php echo htmlspecialchars($f['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($f['team']); ?></td>
                            <td><?php echo htmlspecialchars($f['uploaded_by']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['date']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['size']); ?></td>
                            <td>
                                <span class="status-pill <?php echo $statusLower; ?>">
                                    <?php echo htmlspecialchars($f['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </table>
            </div>

        </div>

        <!-- =========================================================
             TAB 2 — UNVIEWED FILES
        ========================================================= -->
        <div class="tab-panel" id="panel-unviewed">

            <h2>Unviewed Files</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Files that students uploaded but you have not reviewed yet.
                Open one to accept or reject it.
            </p>

            <div class="card">
                <table>
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Size</th>
                        <th>Action</th>
                    </tr>

                    <?php if (count($unviewedFiles) === 0): ?>
                        <tr>
                            <td colspan="6" class="muted" style="text-align: center; padding: 30px;">
                                Nothing to review — all files have been viewed.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($unviewedFiles as $f): ?>
                        <tr>
                            <td class="file-name-cell">
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                                   class="file-name-link"
                                   title="<?php echo htmlspecialchars($f['name']); ?>">
                                    <?php echo htmlspecialchars($f['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($f['team']); ?></td>
                            <td><?php echo htmlspecialchars($f['uploaded_by']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['date']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['size']); ?></td>
                            <td>
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </table>
            </div>

        </div>

        <!-- =========================================================
             TAB 3 — GROUP REQUESTS
        ========================================================= -->
        <div class="tab-panel" id="panel-requests">

            <h2>Group Formation Requests</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Each request lists the group members. The team is only formed
                after the teacher accepts the request.
            </p>

            <?php foreach ($groupRequests as $req): ?>
                <div class="request-card">

                    <div class="request-head">
                        <div>
                            <h3>
                                <?php echo htmlspecialchars($req['group_name']); ?>
                                ·
                                <span class="muted" style="font-weight: normal;">
                                    <?php echo htmlspecialchars($req['project']); ?>
                                </span>
                            </h3>
                            <div class="request-meta">
                                Request #<?php echo $req['id']; ?> ·
                                Submitted by <?php echo htmlspecialchars($req['submitted_by']); ?> ·
                                <?php echo htmlspecialchars($req['submitted_at']); ?>
                            </div>
                        </div>
                        <span class="pill">Pending Review</span>
                    </div>

                    <div class="request-body">
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Student ID</th>
                                <th>Role</th>
                            </tr>
                            <?php foreach ($req['members'] as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['name']); ?></td>
                                    <td><?php echo htmlspecialchars($m['class']); ?></td>
                                    <td><?php echo htmlspecialchars($m['student_number']); ?></td>
                                    <td>
                                        <?php if ($m['is_leader']): ?>
                                            <span class="pill">Leader</span>
                                        <?php else: ?>
                                            <span class="muted">Member</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <div class="request-foot">
                        <button type="button" class="btn-primary">Accept</button>
                        <button type="button" class="btn-ghost">Reject</button>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

    </div>
</main>

<script>
    document.querySelectorAll('.tab').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });

            tab.classList.add('active');
            const id = tab.getAttribute('data-tab');
            const panel = document.getElementById('panel-' + id);
            if (panel) { panel.classList.add('active'); }

            history.replaceState(null, '', '#' + id);
        });
    });

    (function () {
        const hash = window.location.hash.replace('#', '');
        if (!hash) return;
        const tabEl = document.querySelector('.tab[data-tab="' + hash + '"]');
        if (tabEl) { tabEl.click(); }
    })();
</script>

</body>
</html>