<?php
$active = 'review';

// ===== 假数据 =====

$summary = array(
    'pending_files'    => 8,
    'approved_files'   => 24,
    'rejected_files'   => 3,
    'group_requests'   => 5,
);

// 待查看 / 已查看的文件
$files = array(
    array('id'=>1, 'name'=>'Proposal_v2.docx',         'team'=>'Team 01', 'uploaded_by'=>'CHAN Ho Yin',     'date'=>'10:42 today', 'size'=>'2.4 MB', 'status'=>'Unviewed'),
    array('id'=>2, 'name'=>'Requirement_Analysis.pdf', 'team'=>'Team 01', 'uploaded_by'=>'LAI Ka-Yu Savio', 'date'=>'Yesterday',   'size'=>'1.8 MB', 'status'=>'Accepted'),
    array('id'=>3, 'name'=>'UI_Wireframe.fig',         'team'=>'Team 02', 'uploaded_by'=>'MIAO Tung',       'date'=>'2 days ago',  'size'=>'5.1 MB', 'status'=>'Unviewed'),
    array('id'=>4, 'name'=>'Database_Schema.sql',      'team'=>'Team 02', 'uploaded_by'=>'LAI Ka-Yu Savio', 'date'=>'3 days ago',  'size'=>'120 KB', 'status'=>'Accepted'),
    array('id'=>5, 'name'=>'API_Spec.pdf',             'team'=>'Team 02', 'uploaded_by'=>'CHUI Ka Chun',    'date'=>'4 days ago',  'size'=>'890 KB', 'status'=>'Rejected'),
);

// 打回的文件
$rejectedFiles = array(
    array('id'=>5, 'name'=>'API_Spec.pdf',        'team'=>'Team 02', 'uploaded_by'=>'CHUI Ka Chun',   'rejected_on'=>'3 days ago', 'reason'=>'Missing authentication section.',        'resubmitted'=>false),
    array('id'=>6, 'name'=>'Meeting_Minutes.docx','team'=>'Team 03', 'uploaded_by'=>'WONG Mei Ling',  'rejected_on'=>'5 days ago', 'reason'=>'Wrong template — please use the FYP one.', 'resubmitted'=>true),
    array('id'=>7, 'name'=>'Budget_Plan.xlsx',    'team'=>'Team 04', 'uploaded_by'=>'LEE Chun Kit',   'rejected_on'=>'1 week ago', 'reason'=>'Numbers do not match the proposal.',       'resubmitted'=>false),
);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Center | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">

    <style>
        /* =========================================================
           Tabs
        ========================================================= */
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
            border-bottom: 1px solid #e6e8eb;
            padding-bottom: 0;
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

        /* =========================================================
           Status Select (select list)
        ========================================================= */
        .status-select {
            height: 32px;
            padding: 0 26px 0 10px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #e6e8eb;
            background: #fff;
            color: #2c2f36;
            cursor: pointer;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='%236b6f78' d='M6 8L2 4h8z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 10px;
        }

        .status-select.unviewed {
            border-color: #e6e8eb;
            color: #6b6f78;
            background-color: #f7f8fa;
        }

        .status-select.accepted {
            border-color: #8bc79a;
            color: #176b2c;
            background-color: #eaf7ee;
        }

        .status-select.rejected {
            border-color: #e6a0a0;
            color: #a21b1b;
            background-color: #fdeeee;
        }

        /* =========================================================
           Rejected reason
        ========================================================= */
        .reject-reason {
            font-size: 13px;
            color: #a21b1b;
            line-height: 1.5;
        }

        .resubmit-tag {
            display: inline-block;
            margin-left: 6px;
            padding: 2px 8px;
            background: #eaf7ee;
            color: #176b2c;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
        }

        /* =========================================================
           Group Request Card
        ========================================================= */
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

        .request-body {
            padding: 6px 0;
        }

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

        .request-body table tr:last-child td {
            border-bottom: none;
        }

        .request-foot {
            padding: 16px 22px;
            background: #fafbfc;
            border-top: 1px solid #e6e8eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* =========================================================
           Status banner (student_group_request.php 也会用)
        ========================================================= */
        .status-banner {
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .status-banner.status-draft {
            background: #f7f8fa;
            color: #6b6f78;
            border-left: 4px solid #c9ccd1;
        }

        .status-banner.status-submitted {
            background: #fff8e8;
            color: #765900;
            border-left: 4px solid #e0a030;
        }

        .status-banner.status-accepted {
            background: #eaf7ee;
            color: #176b2c;
            border-left: 4px solid #2e9d4d;
        }

        .status-banner.status-rejected {
            background: #fdeeee;
            color: #a21b1b;
            border-left: 4px solid #d32f2f;
        }

        /* Submit area (student_group_request.php) */
        .submit-area {
            padding: 22px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .submit-area .muted {
            max-width: 620px;
            line-height: 1.5;
        }

        /* Tab panels: 简单切换 */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        @media (max-width: 700px) {
            .request-head { flex-direction: column; gap: 10px; }
            .request-foot { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

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
            <a href="#files" class="tab active" data-tab="files">
                Files
                <span class="tab-count"><?php echo count($files); ?></span>
            </a>
            <a href="#rejected" class="tab" data-tab="rejected">
                Rejected Files
                <span class="tab-count"><?php echo count($rejectedFiles); ?></span>
            </a>
            <a href="#requests" class="tab" data-tab="requests">
                Group Requests
                <span class="tab-count"><?php echo count($groupRequests); ?></span>
            </a>
        </div>

        <!-- =========================================================
             TAB 1 — FILES
        ========================================================= -->
        <div class="tab-panel active" id="panel-files">

            <h2>Pending &amp; Reviewed Files</h2>
            <div class="card">
                <table>
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($files as $f): ?>
                        <?php
                        $statusLower = strtolower($f['status']);
                        // 把 Accepted/Rejected 映射到 select 里的小写值
                        if ($f['status'] === 'Unviewed')      { $selClass = 'unviewed'; $selValue = 'unviewed'; }
                        elseif ($f['status'] === 'Accepted')  { $selClass = 'accepted'; $selValue = 'accepted'; }
                        elseif ($f['status'] === 'Rejected')  { $selClass = 'rejected'; $selValue = 'rejected'; }
                        else                                  { $selClass = 'unviewed'; $selValue = 'unviewed'; }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f['name']); ?></td>
                            <td><?php echo htmlspecialchars($f['team']); ?></td>
                            <td><?php echo htmlspecialchars($f['uploaded_by']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['date']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['size']); ?></td>
                            <td>
                                <label class="sr-only" for="fileStatus<?php echo $f['id']; ?>">
                                    Review status for <?php echo htmlspecialchars($f['name']); ?>
                                </label>
                                <select
                                    id="fileStatus<?php echo $f['id']; ?>"
                                    name="fileStatus<?php echo $f['id']; ?>"
                                    class="status-select <?php echo $selClass; ?>"
                                >
                                    <option value="unviewed" <?php echo $selValue==='unviewed'?'selected':''; ?>>Unviewed</option>
                                    <option value="accepted" <?php echo $selValue==='accepted'?'selected':''; ?>>Accept</option>
                                    <option value="rejected" <?php echo $selValue==='rejected'?'selected':''; ?>>Reject</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>

        <!-- =========================================================
             TAB 2 — REJECTED FILES
        ========================================================= -->
        <div class="tab-panel" id="panel-rejected">

            <h2>Rejected Files</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Files the teacher sent back. Students can re-upload; once resubmitted,
                a green tag appears here.
            </p>

            <div class="card">
                <table>
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Reason</th>
                        <th>Rejected On</th>
                        <th>Resubmitted</th>
                        <th></th>
                    </tr>
                    <?php foreach ($rejectedFiles as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['name']); ?></td>
                            <td><?php echo htmlspecialchars($r['team']); ?></td>
                            <td><?php echo htmlspecialchars($r['uploaded_by']); ?></td>
                            <td class="reject-reason"><?php echo htmlspecialchars($r['reason']); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($r['rejected_on']); ?></td>
                            <td>
                                <?php if ($r['resubmitted']): ?>
                                    <span class="resubmit-tag">Yes</span>
                                <?php else: ?>
                                    <span class="muted">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="link">View</button>
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

<!-- 简单 tab 切换（纯前端，不影响后端） -->
<script>
    document.querySelectorAll('.tab').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });

            tab.classList.add('active');
            var id = tab.getAttribute('data-tab');
            var panel = document.getElementById('panel-' + id);
            if (panel) { panel.classList.add('active'); }
        });
    });
</script>

</body>
</html>