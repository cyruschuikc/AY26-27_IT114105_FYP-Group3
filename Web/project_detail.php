<?php
$active = 'projects';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ===== 假数据 =====
$project = array(
    'id'          => $projectId,
    'name'        => 'TeamBox',
    'category'    => 'Software Engineering',
    'supervisor'  => 'Esther Yuen',
    'period_start'=> '2026-09-01',
    'period_end'  => '2027-04-30',
    'status'      => 'Executing',
    'description' => 'A cross-platform collaboration and management system for student project teams, featuring task tracking, file sharing, group chat and contribution analysis.',
    'progress'    => 60,
);

$teams = array(
    array('id'=>1, 'name'=>'Team 01', 'leader'=>'CHAN Ho Yin',     'members'=>'4 / 4', 'progress'=>'3 / 5', 'status'=>'Executing'),
    array('id'=>2, 'name'=>'Team 02', 'leader'=>'LAI Ka-Yu Savio', 'members'=>'4 / 4', 'progress'=>'1 / 5', 'status'=>'Voting'),
);

$milestones = array(
    array('name'=>'Requirement Analysis',    'start'=>'2026-09-01', 'end'=>'2026-10-05', 'status'=>'Completed',   'progress'=>100),
    array('name'=>'System Design',           'start'=>'2026-10-06', 'end'=>'2026-10-20', 'status'=>'Completed',   'progress'=>100),
    array('name'=>'Core Development',        'start'=>'2026-10-21', 'end'=>'2027-01-15', 'status'=>'In Progress', 'progress'=>55),
    array('name'=>'Testing & Documentation', 'start'=>'2027-01-16', 'end'=>'2027-03-20', 'status'=>'Pending',     'progress'=>0),
    array('name'=>'Final Presentation',      'start'=>'2027-03-21', 'end'=>'2027-04-30', 'status'=>'Pending',     'progress'=>0),
);

$tasks = array(
    array('name'=>'Complete Project Proposal', 'team'=>'Team 01', 'owner'=>'CHAN Ho Yin · CHUI Ka Chun', 'status'=>'Completed',   'due'=>'30 Sep 2026'),
    array('name'=>'Requirement Analysis',      'team'=>'Team 01', 'owner'=>'LAI Ka-Yu Savio',            'status'=>'Completed',   'due'=>'05 Oct 2026'),
    array('name'=>'Create System Interface',   'team'=>'Team 01', 'owner'=>'MIAO Tung',                  'status'=>'In Progress', 'due'=>'10 Oct 2026'),
    array('name'=>'Database Schema Design',    'team'=>'Team 02', 'owner'=>'LAI Ka-Yu Savio',            'status'=>'Pending',     'due'=>'15 Oct 2026'),
    array('name'=>'Android Prototype',         'team'=>'Team 02', 'owner'=>'CHUI Ka Chun',               'status'=>'Pending',     'due'=>'25 Oct 2026'),
);

$fileGroups = array(
    array(
        'team'       => 'Team 01',
        'team_id'    => 1,
        'file_count' => 12,
        'total_size' => '24.8 MB',
        'last_upload'=> 'Proposal_v2.docx · 10:42 today',
        'items'      => array(
            array('name'=>'Proposal_v2.docx',         'owner'=>'CHAN Ho Yin',     'meta'=>'2.4 MB · 10:42 today'),
            array('name'=>'Requirement_Analysis.pdf', 'owner'=>'LAI Ka-Yu Savio', 'meta'=>'1.8 MB · Yesterday'),
            array('name'=>'UI_Wireframe.fig',         'owner'=>'MIAO Tung',       'meta'=>'5.1 MB · 2 days ago'),
        ),
    ),
    array(
        'team'       => 'Team 02',
        'team_id'    => 2,
        'file_count' => 5,
        'total_size' => '8.2 MB',
        'last_upload'=> 'Database_Schema.sql · 3 days ago',
        'items'      => array(
            array('name'=>'Database_Schema.sql', 'owner'=>'LAI Ka-Yu Savio', 'meta'=>'120 KB · 3 days ago'),
            array('name'=>'API_Spec.pdf',        'owner'=>'CHUI Ka Chun',    'meta'=>'890 KB · 4 days ago'),
        ),
    ),
);

$activities = array(
    array('Team 01 uploaded Proposal_v2.docx',                     '10:42'),
    array('LAI Ka-Yu Savio marked Requirement Analysis complete',  '09:20'),
    array('MIAO Tung commented on UI_Wireframe',                   'Yesterday'),
    array('Project progress updated to 60%',                       '2 days ago'),
);

// ===== 甘特图计算 =====
$projStart = strtotime($project['period_start']);
$projEnd   = strtotime($project['period_end']);
$projSpan  = $projEnd - $projStart;
if ($projSpan <= 0) { $projSpan = 1; }

foreach ($milestones as &$m) {
    $ms = strtotime($m['start']);
    $me = strtotime($m['end']);
    $m['offset'] = (($ms - $projStart) / $projSpan) * 100;
    $m['width']  = (($me - $ms) / $projSpan) * 100;
    if ($m['width'] < 2) { $m['width'] = 2; }

    $today = strtotime(date('Y-m-d'));
    $m['is_late'] = ($today > $me && $m['status'] !== 'Completed');
}
unset($m);

// ===== today 红线 =====
$todayTs      = strtotime(date('Y-m-d'));
$todayPercent = (($todayTs - $projStart) / $projSpan) * 100;
if ($todayPercent < 0)   { $todayPercent = 0; }
if ($todayPercent > 100) { $todayPercent = 100; }
$todayInRange = ($todayTs >= $projStart && $todayTs <= $projEnd);

// ===== 顶部月份刻度 =====
$monthTicks = array();
$cursor = strtotime(date('Y-m-01', $projStart));
$lastMonth = date('Y-m', $projEnd);

while (date('Y-m', $cursor) <= $lastMonth) {
    $monthStart = max($cursor, $projStart);
    $percent    = (($monthStart - $projStart) / $projSpan) * 100;

    $monthTicks[] = array(
        'label'   => date('M', $cursor),
        'year'    => date('Y', $cursor),
        'percent' => $percent,
    );

    $cursor = strtotime('+1 month', $cursor);
}

// ===== 延期计数 =====
$lateCount = 0;
foreach ($milestones as $m) {
    if (!empty($m['is_late'])) { $lateCount++; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['name']); ?> | TeamBox</title>
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
            letter-spacing: 0.3px;
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
           File groups
        ========================================================= */
        .file-groups {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .file-group {
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .file-group-head {
            padding: 16px 18px;
            background: #f7f8fa;
            border-bottom: 1px solid #e6e8eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .file-group-head h3 {
            font-size: 14px;
            font-weight: 600;
            color: #2c2f36;
        }

        .file-group-head .file-group-meta {
            font-size: 12px;
            color: #8b8f98;
        }

        .file-group-body {
            padding: 4px 0;
        }

        .file-group-body ul {
            list-style: none;
            font-size: 13px;
        }

        .file-group-body ul li {
            padding: 10px 18px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #f2f3f5;
        }

        .file-group-body ul li:last-child { border-bottom: none; }

        .file-group-body ul li span:first-child { color: #2c2f36; }

        .file-group-foot {
            padding: 10px 18px;
            background: #fafbfc;
            border-top: 1px solid #e6e8eb;
            font-size: 12px;
            color: #8b8f98;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        /* =========================================================
           Gantt
        ========================================================= */
        .gantt-wrap {
            padding: 24px;
        }

        .gantt-header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #8b8f98;
            margin-bottom: 12px;
            padding-left: 160px;
        }

        .gantt-header .gantt-start,
        .gantt-header .gantt-end {
            font-weight: 600;
            color: #6b6f78;
        }

        .gantt-months {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #e6e8eb;
            padding-bottom: 8px;
        }

        .gantt-months-label {
            width: 160px;
            flex-shrink: 0;
        }

        .gantt-months-track {
            position: relative;
            flex: 1;
            height: 18px;
        }

        .gantt-month {
            position: absolute;
            top: 0;
            transform: translateX(-50%);
            font-size: 11px;
            font-weight: 600;
            color: #6b6f78;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }

        .gantt-month:first-child { transform: translateX(0); }

        .gantt-body { position: relative; }

        .gantt-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            min-height: 32px;
        }

        .gantt-row:last-child { margin-bottom: 0; }

        .gantt-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 13px;
            color: #2c2f36;
            padding-right: 12px;
        }

        .gantt-label .gantt-label-status {
            display: block;
            font-size: 11px;
            color: #8b8f98;
            margin-top: 2px;
        }

        .gantt-track {
            position: relative;
            flex: 1;
            height: 22px;
            background: #f2f3f5;
            border-radius: 6px;
            overflow: hidden;
        }

        .gantt-bar {
            position: absolute;
            top: 0;
            height: 100%;
            border-radius: 6px;
            display: flex;
            align-items: center;
            padding: 0 8px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            z-index: 2;
        }

        .gantt-bar.done    { background: #175196; }
        .gantt-bar.active  { background: #e0a030; color: #2c2f36; }
        .gantt-bar.pending { background: #c9ccd1; color: #2c2f36; }
        .gantt-bar.late    { background: #d32f2f; color: #fff; }

        .gantt-month-line {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #eef0f3;
            z-index: 1;
            pointer-events: none;
        }

        .gantt-today-line {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #d32f2f;
            z-index: 5;
            pointer-events: none;
        }

        .gantt-today-line::before {
            content: "";
            position: absolute;
            top: -4px;
            left: -3px;
            width: 8px;
            height: 8px;
            background: #d32f2f;
            border-radius: 50%;
        }

        .gantt-today-label {
            position: absolute;
            top: -22px;
            left: 50%;
            transform: translateX(-50%);
            background: #d32f2f;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .gantt-late-text {
            color: #d32f2f;
            font-weight: 600;
        }

        .gantt-legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #f2f3f5;
            font-size: 12px;
            color: #6b6f78;
        }

        .gantt-legend .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .gantt-legend .dot.done    { background: #175196; }
        .gantt-legend .dot.active  { background: #e0a030; }
        .gantt-legend .dot.pending { background: #c9ccd1; }
        .gantt-legend .dot.late    { background: #d32f2f; }
        .gantt-legend .dot.today   { background: #d32f2f; border-radius: 50%; }

        /* =========================================================
           Review Center CTA（替换掉原来的 Contribution CTA）
        ========================================================= */
        .review-cta {
            padding: 26px 28px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-left: 4px solid #175196;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .review-cta h3 {
            font-size: 16px;
            font-weight: 600;
            color: #2c2f36;
            margin-bottom: 6px;
        }

        .review-cta p {
            font-size: 13px;
            color: #8b8f98;
            line-height: 1.5;
            max-width: 620px;
        }

        .review-cta .cta-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        /* =========================================================
           响应式
        ========================================================= */
        @media (max-width: 900px) {
            .file-groups { grid-template-columns: 1fr; }

            .gantt-header { padding-left: 0; }
            .gantt-months { display: none; }
            .gantt-month-line { display: none; }
            .gantt-today-line { display: none; }

            .gantt-row { flex-direction: column; align-items: stretch; }
            .gantt-label { width: 100%; padding: 0 0 6px 0; }

            .review-cta {
                flex-direction: column;
                align-items: flex-start;
            }
        }

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

        <a href="teacher_projects.php" class="back">← Back to Projects</a>

        <!-- 页面头部 -->
        <div class="page-head">
            <div>
                <h1><?php echo htmlspecialchars($project['name']); ?></h1>
                <div class="meta">
                    <?php echo htmlspecialchars($project['category']); ?> ·
                    Supervisor: <?php echo htmlspecialchars($project['supervisor']); ?>
                    <?php if ($lateCount > 0): ?>
                        · <span class="gantt-late-text"><?php echo $lateCount; ?> milestone<?php echo $lateCount > 1 ? 's' : ''; ?> late</span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="pill"><?php echo htmlspecialchars($project['status']); ?></span>
        </div>

        <!-- Overview -->
        <h2>Overview</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Category</th>
                    <th>Supervisor</th>
                    <th>Project Period</th>
                    <th>Teams</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($project['category']); ?></td>
                    <td><?php echo htmlspecialchars($project['supervisor']); ?></td>
                    <td>
                        <?php echo date('M Y', $projStart); ?>
                        –
                        <?php echo date('M Y', $projEnd); ?>
                    </td>
                    <td><?php echo count($teams); ?> teams</td>
                </tr>
            </table>
        </div>

        <p class="muted" style="margin: -10px 0 30px;">
            <?php echo htmlspecialchars($project['description']); ?>
        </p>

        <!-- Review Center 入口（替换原来的 Contribution CTA） -->
        <div class="review-cta">
            <div>
                <h3>Review Center</h3>
                <p>
                    Check files uploaded by this project's teams, approve or
                    reject them, and review group formation requests. Rejected
                    files will be sent back to the students for revision.
                </p>
            </div>
            <div class="cta-actions">
                <a href="teacher_review.php" class="btn-primary">Open Review Center</a>
            </div>
        </div>

        <!-- Teams -->
        <h2>Teams in this Project</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>Leader</th>
                    <th>Members</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td><?php echo htmlspecialchars($t['leader']); ?></td>
                        <td><?php echo htmlspecialchars($t['members']); ?></td>
                        <td><?php echo htmlspecialchars($t['progress']); ?></td>
                        <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                        <td><a href="team_detail.php?id=<?php echo (int)$t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Progress + Milestones -->
        <div class="info-grid">
            <div>
                <h2>Progress</h2>
                <div class="card">
                    <table class="progress-table">
                        <tr>
                            <td>Overall</td>
                            <td>
                                <span class="progress">
                                    <span class="bar">
                                        <span style="width: <?php echo $project['progress']; ?>%"></span>
                                    </span>
                                    <?php echo $project['progress']; ?>%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Teams Involved</td>
                            <td><?php echo count($teams); ?></td>
                        </tr>
                        <tr>
                            <td>Tasks Completed</td>
                            <td>3 / 5</td>
                        </tr>
                        <tr>
                            <td>Milestones Late</td>
                            <td>
                                <?php if ($lateCount > 0): ?>
                                    <span class="gantt-late-text"><?php echo $lateCount; ?></span>
                                <?php else: ?>
                                    0
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Start Date</td>
                            <td><?php echo date('d M Y', $projStart); ?></td>
                        </tr>
                        <tr>
                            <td>Deadline</td>
                            <td><?php echo date('d M Y', $projEnd); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div>
                <h2>Milestones</h2>
                <div class="card">
                    <table class="progress-table">
                        <?php foreach ($milestones as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($m['status']); ?>
                                    <span class="muted">· <?php echo date('d M Y', strtotime($m['end'])); ?></span>
                                    <?php if ($m['is_late']): ?>
                                        · <span class="gantt-late-text">Late</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Gantt -->
        <h2>Project Timeline (Gantt)</h2>
        <div class="card">
            <div class="gantt-wrap">

                <div class="gantt-header">
                    <span class="gantt-start"><?php echo date('d M Y', $projStart); ?></span>
                    <span class="gantt-end"><?php echo date('d M Y', $projEnd); ?></span>
                </div>

                <div class="gantt-months">
                    <div class="gantt-months-label"></div>
                    <div class="gantt-months-track">
                        <?php foreach ($monthTicks as $tick): ?>
                            <div class="gantt-month"
                                 style="left: <?php echo round($tick['percent'], 2); ?>%;"
                                 title="<?php echo htmlspecialchars($tick['label'] . ' ' . $tick['year']); ?>">
                                <?php echo htmlspecialchars($tick['label']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="gantt-body">

                    <?php if ($todayInRange): ?>
                        <div class="gantt-today-line"
                             style="left: calc(160px + (100% - 160px) * <?php echo round($todayPercent / 100, 4); ?>);"
                             title="Today · <?php echo date('d M Y'); ?>">
                            <span class="gantt-today-label">Today</span>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($monthTicks as $i => $tick): ?>
                        <?php if ($i === 0) continue; ?>
                        <div class="gantt-month-line"
                             style="left: calc(160px + (100% - 160px) * <?php echo round($tick['percent'] / 100, 4); ?>);"></div>
                    <?php endforeach; ?>

                    <?php foreach ($milestones as $m): ?>
                        <?php
                        if ($m['status'] === 'Completed')        { $cls = 'done'; }
                        elseif ($m['is_late'])                    { $cls = 'late'; }
                        elseif ($m['status'] === 'In Progress')   { $cls = 'active'; }
                        else                                      { $cls = 'pending'; }
                        ?>
                        <div class="gantt-row">
                            <div class="gantt-label">
                                <?php echo htmlspecialchars($m['name']); ?>
                                <span class="gantt-label-status">
                                    <?php echo date('d M', strtotime($m['start'])); ?>
                                    –
                                    <?php echo date('d M Y', strtotime($m['end'])); ?>
                                    <?php if ($m['is_late']): ?>
                                        · <span class="gantt-late-text">Late</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="gantt-track">
                                <div class="gantt-bar <?php echo $cls; ?>"
                                     style="left: <?php echo round($m['offset'], 2); ?>%;
                                             width: <?php echo round($m['width'], 2); ?>%;"
                                     title="<?php echo htmlspecialchars($m['name']); ?> · <?php echo $m['progress']; ?>%">
                                    <?php echo $m['progress']; ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="gantt-legend">
                    <span><span class="dot done"></span>Completed</span>
                    <span><span class="dot active"></span>In Progress</span>
                    <span><span class="dot pending"></span>Pending</span>
                    <span><span class="dot late"></span>Late</span>
                    <span><span class="dot today"></span>Today</span>
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
                        <div class="task-owner">
                            <?php echo htmlspecialchars($t['team']); ?> ·
                            <?php echo htmlspecialchars($t['owner']); ?>
                        </div>
                    </div>
                    <div class="task-right">
                        <div><?php echo htmlspecialchars($t['status']); ?></div>
                        <div class="task-due"><?php echo htmlspecialchars($t['due']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Files by Team -->
        <h2>Files by Team</h2>
        <div class="file-groups">
            <?php foreach ($fileGroups as $g): ?>
                <div class="file-group">

                    <div class="file-group-head">
                        <h3><?php echo htmlspecialchars($g['team']); ?></h3>
                        <span class="file-group-meta">
                            <?php echo $g['file_count']; ?> files ·
                            <?php echo htmlspecialchars($g['total_size']); ?>
                        </span>
                    </div>

                    <div class="file-group-body">
                        <ul>
                            <?php foreach ($g['items'] as $f): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($f['name']); ?></span>
                                    <span class="muted">
                                        <?php echo htmlspecialchars($f['owner']); ?> ·
                                        <?php echo htmlspecialchars($f['meta']); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="file-group-foot">
                        <span>Last: <?php echo htmlspecialchars($g['last_upload']); ?></span>
                        <a href="teacher_files.php?team=<?php echo (int)$g['team_id']; ?>" class="link">View all</a>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Activity -->
        <h2 style="margin-top: 30px;">Activity</h2>
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