<?php
$active = 'teams';

$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ===== 假数据 =====
$team = array(
        'team_name'          => 'Team 01',
        'project_name'       => 'TeamBox',
        'project_category'   => 'Software Engineering',
        'project_supervisor' => 'Esther Yuen',
        'period_start'       => '2026-09-01',
        'period_end'         => '2027-04-30',
        'status'             => 'Executing',
        'formed_at'          => '2026-09-15',
        'roster_locked_at'   => '2026-09-20',
        'leader_elected_at'  => '2026-09-21',
        'last_activity_at'   => '2026-09-29 10:42:00',
);

$members = array(
        array('name'=>'CHAN Ho Yin',     'student_number'=>'260222111', 'major'=>'SE', 'grade_level'=>12, 'team_role'=>'Leader · Full-stack',   'is_leader'=>1, 'tasks_done'=>'2 / 3'),
        array('name'=>'CHUI Ka Chun',    'student_number'=>'260222112', 'major'=>'SE', 'grade_level'=>12, 'team_role'=>'Android Developer',     'is_leader'=>0, 'tasks_done'=>'1 / 2'),
        array('name'=>'LAI Ka-Yu Savio', 'student_number'=>'260222113', 'major'=>'SE', 'grade_level'=>12, 'team_role'=>'Backend Developer',     'is_leader'=>0, 'tasks_done'=>'1 / 2'),
        array('name'=>'MIAO Tung',       'student_number'=>'260222114', 'major'=>'SE', 'grade_level'=>12, 'team_role'=>'Frontend Developer',    'is_leader'=>0, 'tasks_done'=>'0 / 2'),
);

function formatClass($major, $gradeLevel) {
    if ($gradeLevel >= 12) {
        $year = $gradeLevel - 10;
    } else {
        $year = $gradeLevel;
    }
    return $major . '-' . $year . 'B';
}

$progress = array(
        array('label'=>'Overall',         'value'=>'<span class="progress"><span class="bar"><span style="width: 60%"></span></span>60%</span>', 'raw'=>true),
        array('label'=>'Tasks Completed', 'value'=>'3 / 5'),
        array('label'=>'Team Formed',     'value'=>date('d M Y', strtotime($team['formed_at']))),
        array('label'=>'Roster Locked',   'value'=>date('d M Y', strtotime($team['roster_locked_at']))),
        array('label'=>'Leader Elected',  'value'=>date('d M Y', strtotime($team['leader_elected_at']))),
        array('label'=>'Last Activity',   'value'=>date('H:i', strtotime($team['last_activity_at'])) . ' today'),
);

$division = array(
        array('CHAN Ho Yin',     'Proposal, Architecture, Web UI'),
        array('CHUI Ka Chun',    'Android App, Push Notification'),
        array('LAI Ka-Yu Savio', 'Database, REST API'),
        array('MIAO Tung',       'Web Frontend, File Module'),
        array('Shared',          'Testing, Documentation'),
);

$tasks = array(
        array('name'=>'Complete Project Proposal', 'owner'=>'CHAN Ho Yin · CHUI Ka Chun', 'status'=>'Completed',   'due'=>'30 Sep 2026'),
        array('name'=>'Requirement Analysis',      'owner'=>'LAI Ka-Yu Savio',            'status'=>'Completed',   'due'=>'05 Oct 2026'),
        array('name'=>'Create System Interface',   'owner'=>'MIAO Tung',                  'status'=>'In Progress', 'due'=>'10 Oct 2026'),
        array('name'=>'Database Schema Design',    'owner'=>'LAI Ka-Yu Savio',            'status'=>'Pending',     'due'=>'15 Oct 2026'),
        array('name'=>'Android Prototype',         'owner'=>'CHUI Ka Chun',               'status'=>'Pending',     'due'=>'25 Oct 2026'),
);

$files = array(
        array('name'=>'Proposal_v2.docx',         'owner'=>'CHAN Ho Yin',     'meta'=>'2.4 MB · 10:42 today'),
        array('name'=>'Requirement_Analysis.pdf', 'owner'=>'LAI Ka-Yu Savio', 'meta'=>'1.8 MB · Yesterday'),
        array('name'=>'UI_Wireframe.fig',         'owner'=>'MIAO Tung',       'meta'=>'5.1 MB · 2 days ago'),
);

// ★ 改动：把原来的 $contributions（含百分比 / 分数）换成客观数量，不评分、不排名
$memberActivity = array(
        array('name'=>'CHAN Ho Yin',     'tasks'=>2, 'files'=>3, 'comments'=>14),
        array('name'=>'CHUI Ka Chun',    'tasks'=>1, 'files'=>1, 'comments'=> 8),
        array('name'=>'LAI Ka-Yu Savio', 'tasks'=>1, 'files'=>2, 'comments'=>11),
        array('name'=>'MIAO Tung',       'tasks'=>0, 'files'=>1, 'comments'=> 6),
);

$activities = array(
        array('CHAN Ho Yin uploaded Proposal_v2.docx',                '10:42'),
        array('LAI Ka-Yu Savio marked Requirement Analysis complete', '09:20'),
        array('MIAO Tung commented on UI_Wireframe',                  'Yesterday'),
        array('Team roster locked',                                   '20 Sep'),
        array('CHAN Ho Yin elected as team leader',                   '21 Sep'),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['team_name']); ?> | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">

    <style>
        /* =========================================================
           页面头部
        ========================================================= */
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 34px;
            gap: 20px;
        }

        .page-head h1 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .page-head .meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .back {
            display: inline-block;
            font-size: 13px;
            color: #8b8f98;
            text-decoration: none;
            margin-bottom: 22px;
            cursor: pointer;
        }

        .back:hover { color: #2c2f36; }

        /* =========================================================
           两列布局
        ========================================================= */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .progress-table td { white-space: normal; }

        .progress-table td:first-child {
            width: 40%;
            background: #175196;
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.3em;
        }

        .progress-table td:last-child { color: #2c2f36; }

        /* =========================================================
           Task rows
        ========================================================= */
        .task-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 14px;
        }

        .task-row:last-child { border-bottom: none; }

        .task-row .task-name { color: #2c2f36; }

        .task-row .task-owner {
            font-size: 13px;
            color: #8b8f98;
            margin-top: 3px;
        }

        .task-row .task-right {
            text-align: right;
            white-space: nowrap;
        }

        .task-row .task-due {
            font-size: 12px;
            color: #a8acb4;
            margin-top: 3px;
        }

        /* =========================================================
           File list
        ========================================================= */
        .file-list {
            list-style: none;
            font-size: 14px;
        }

        .file-list li {
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .file-list li:last-child { border-bottom: none; }

        /* =========================================================
           Activity list
        ========================================================= */
        .activity-list {
            list-style: none;
            font-size: 14px;
        }

        .activity-list li {
            padding: 12px 16px;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
        }

        .activity-list li:last-child { border-bottom: none; }

        /* =========================================================
           Member Activity（替换原来的 Contribution）
        ========================================================= */
        .activity-table td {
            white-space: normal;
        }

        .activity-table td:first-child {
            color: #2c2f36;
        }

        .activity-table td:not(:first-child) {
            text-align: right;
            color: #2c2f36;
            font-weight: 500;
        }

        .activity-table th:not(:first-child) {
            text-align: right;
        }

        .leader-mark {
            font-size: 11px;
            color: #6b6f78;
            margin-left: 6px;
            letter-spacing: 0.3em;
        }

        /* =========================================================
           响应式
        ========================================================= */
        @media (max-width: 800px) {
            .info-grid { grid-template-columns: 1fr; gap: 0; }
        }

        @media (max-width: 700px) {
            .page-head { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <a href="teacher_teams.php" class="back">← Back to Teams</a>

        <div class="page-head">
            <div>
                <h1><?php echo htmlspecialchars($team['team_name']); ?></h1>
                <div class="meta">
                    <?php echo htmlspecialchars($team['project_name']); ?> ·
                    <?php echo htmlspecialchars($team['project_category']); ?>
                </div>
            </div>
            <span class="pill"><?php echo htmlspecialchars($team['status']); ?></span>
        </div>

        <!-- Project -->
        <h2>Project</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Project Name</th>
                    <th>Category</th>
                    <th>Supervisor</th>
                    <th>Project Period</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($team['project_name']); ?></td>
                    <td><?php echo htmlspecialchars($team['project_category']); ?></td>
                    <td><?php echo htmlspecialchars($team['project_supervisor']); ?></td>
                    <td>
                        <?php echo date('M Y', strtotime($team['period_start'])); ?>
                        –
                        <?php echo date('M Y', strtotime($team['period_end'])); ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Members -->
        <h2>Members</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>Role</th>
                    <th>Tasks Done</th>
                </tr>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($m['name']); ?>
                            <?php if (!empty($m['is_leader'])): ?>
                                <span class="leader-mark">LEADER</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($m['student_number']); ?></td>
                        <td><?php echo htmlspecialchars(formatClass($m['major'], $m['grade_level'])); ?></td>
                        <td><?php echo htmlspecialchars($m['team_role']); ?></td>
                        <td><?php echo htmlspecialchars($m['tasks_done']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Progress + Division -->
        <div class="info-grid">
            <div>
                <h2>Progress</h2>
                <div class="card">
                    <table class="progress-table">
                        <?php foreach ($progress as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['label']); ?></td>
                                <td>
                                    <?php if (!empty($p['raw'])): ?>
                                        <?php echo $p['value']; ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($p['value']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div>
                <h2>Division of Work</h2>
                <div class="card">
                    <table class="progress-table">
                        <?php foreach ($division as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($d[0]); ?></td>
                                <td><?php echo htmlspecialchars($d[1]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tasks -->
        <h2>Tasks</h2>
        <div class="card">
            <?php foreach ($tasks as $t): ?>
                <div class="task-row">
                    <div>
                        <div class="task-name"><?php echo htmlspecialchars($t['name']); ?></div>
                        <div class="task-owner"><?php echo htmlspecialchars($t['owner']); ?></div>
                    </div>
                    <div class="task-right">
                        <div><?php echo htmlspecialchars($t['status']); ?></div>
                        <div class="task-due"><?php echo htmlspecialchars($t['due']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Files -->
        <h2>Files</h2>
        <div class="card">
            <ul class="file-list">
                <?php foreach ($files as $f): ?>
                    <li>
                        <span>
                            <?php echo htmlspecialchars($f['name']); ?>
                            <span class="muted">· <?php echo htmlspecialchars($f['owner']); ?></span>
                        </span>
                        <span class="muted"><?php echo htmlspecialchars($f['meta']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- ★ Member Activity（替换原来的 Contribution） -->
        <h2>Member Activity</h2>
        <p class="muted" style="margin-bottom: 12px;">
            Objective activity counts only — no scores, no ranking.
        </p>
        <div class="card">
            <table class="activity-table">
                <tr>
                    <th>Member</th>
                    <th>Tasks Completed</th>
                    <th>Files Uploaded</th>
                    <th>Comments</th>
                </tr>
                <?php foreach ($memberActivity as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['name']); ?></td>
                        <td><?php echo (int)$a['tasks']; ?></td>
                        <td><?php echo (int)$a['files']; ?></td>
                        <td><?php echo (int)$a['comments']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Activity -->
        <h2>Activity</h2>
        <div class="card">
            <ul class="activity-list">
                <?php foreach ($activities as $a): ?>
                    <li>
                        <span><?php echo htmlspecialchars($a[0]); ?></span>
                        <span class="muted"><?php echo htmlspecialchars($a[1]); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</main>

</body>
</html>