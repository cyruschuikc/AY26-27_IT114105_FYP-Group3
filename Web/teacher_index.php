<?php
// teacher_index.php

// ===== 假数据（之后换成 PDO 查询） =====

$summary = array(
    'total_students' => 20,
    'formed_teams'   => 3,
    'unassigned'     => 5,
    'past_due'       => 2,   // 有几支队有逾期任务
);

$teams = array(
    array('id'=>1, 'name'=>'Team 01', 'leader'=>'CHAN Ho Yin',     'members'=>'4 / 4', 'progress'=>'3 / 5', 'last_activity'=>'2h ago', 'overdue'=>0, 'status'=>'Executing'),
    array('id'=>2, 'name'=>'Team 02', 'leader'=>'LAI Ka-Yu Savio', 'members'=>'4 / 4', 'progress'=>'1 / 5', 'last_activity'=>'1d ago', 'overdue'=>1, 'status'=>'Voting'),
    array('id'=>3, 'name'=>'Team 03', 'leader'=>'MIAO Tung',       'members'=>'3 / 4', 'progress'=>'0 / 5', 'last_activity'=>'3d ago', 'overdue'=>2, 'status'=>'Recruiting'),
    array('id'=>4, 'name'=>'Team 04', 'leader'=>'',                'members'=>'2 / 4', 'progress'=>'',      'last_activity'=>'5d ago', 'overdue'=>0, 'status'=>'Recruiting'),
);

$unassigned = array(
    array('name'=>'WONG Mei Ling', 'student_number'=>'202611234', 'year'=>'Year 2'),
    array('name'=>'LEE Chun Kit',  'student_number'=>'202645678', 'year'=>'Year 1'),
    array('name'=>'CHEUNG Ka Wai', 'student_number'=>'202678901', 'year'=>'Year 3'),
    array('name'=>'NG Wing Yan',   'student_number'=>'202632145', 'year'=>'Year 2'),
    array('name'=>'HO Pui Shan',   'student_number'=>'202654321', 'year'=>'Year 1'),
);

$actions = array(
    array('text'=>'Team 04 recruiting over 7 days',        'btn'=>'Random Assign'),
    array('text'=>'WONG Mei Ling unassigned for 10 days',  'btn'=>'Assign'),
    array('text'=>'3 contribution reports pending review', 'btn'=>'Review'),
    array('text'=>'Team 02 roster locked, voting pending', 'btn'=>'Check'),
);

$activities = array(
    array('Team 01 uploaded Proposal_v2.docx',                 '10:42'),
    array('Team 02 started leader voting',                     '09:15'),
    array('MIAO Tung created Team 03',                         'Yesterday'),
    array('Requirement Analysis marked complete',              'Yesterday'),
    array('LAI Ka-Yu invited CHUI Ka Chun to Team 02',         '2 days ago'),
);

$contributions = array(
    array('CHAN Ho Yin',     '32%'),
    array('LAI Ka-Yu Savio', '26%'),
    array('CHUI Ka Chun',    '24%'),
    array('MIAO Tung',       '18%'),
);

$files = array(
    'week_count' => 12,
    'latest'     => 'Proposal_v2.docx · CHAN Ho Yin · 10:42 today',
);

$announcement = array(
    'last_sent' => '3 days ago',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamBox | Instructor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f0f1f3;
            color: #2c2f36;
        }

        /* Sidebar - fixed */
        .sidebar {
            width: 220px;
            height: 100vh;
            background: #16181d;
            color: #fff;
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar .brand {
            padding: 0 24px 32px;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: #fff;
        }

        .sidebar a {
            display: block;
            padding: 14px 24px;
            color: #8b8f98;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            border-left: 2px solid transparent;
            transition: color 0.15s;
        }

        .sidebar a:hover {
            color: #dfe1e5;
        }

        .sidebar a.active {
            color: #fff;
            font-weight: 500;
            border-left-color: #fff;
        }

        .sidebar a.logout {
            color: #6b6f78;
            margin-top: auto;
        }

        .sidebar a.logout:hover {
            color: #a8acb4;
        }

        /* Main */
        .main {
            margin-left: 220px;
            padding: 38px 44px;
            min-height: 100vh;
        }

        .content {
            max-width: 1080px;
            margin: 0 auto;
        }

        h1 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #8b8f98;
            margin-bottom: 38px;
        }

        h2 {
            font-size: 13px;
            font-weight: 600;
            color: #6b6f78;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 0 0 14px;
        }

        /* Card */
        .card {
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Summary */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-item {
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            padding: 20px 22px;
        }

        .summary-item .label {
            font-size: 12px;
            color: #8b8f98;
            letter-spacing: 0.3px;
            margin-bottom: 10px;
        }

        .summary-item .value {
            font-size: 30px;
            font-weight: 600;
            color: #2c2f36;
            line-height: 1;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            table-layout: fixed;
        }

        th {
            text-align: left;
            font-weight: 500;
            font-size: 12px;
            color: #fff;
            background: #175196;
            padding: 12px 16px;
            letter-spacing: 0.3px;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f2f3f5;
            color: #2c2f36;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .muted {
            color: #a8acb4;
            font-size: 13px;
        }

        /* Pill - light gray */
        .pill {
            display: inline-block;
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 20px;
            background: #f2f3f5;
            color: #6b6f78;
            font-weight: 500;
        }

        /* List */
        ul {
            list-style: none;
            font-size: 14px;
        }

        ul li {
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul li:last-child {
            border-bottom: none;
        }

        /* Text links / buttons */
        .link {
            background: none;
            border: none;
            color: #6b6f78;
            font-size: 13px;
            cursor: pointer;
            padding: 0;
            text-decoration: none;
        }

        .link:hover {
            color: #2c2f36;
            text-decoration: underline;
        }

        /* Contribution */
        .contribution-table td {
            white-space: normal;
        }

        .contribution-table td:first-child {
            width: 70%;
            color: #2c2f36;
        }

        .contribution-table td:last-child {
            text-align: right;
            color: #2c2f36;
            font-weight: 500;
        }

        /* Files */
        .file-block {
            padding: 18px 20px;
        }

        .file-block .row {
            font-size: 14px;
            color: #2c2f36;
            margin-bottom: 6px;
        }

        .file-block .row:last-child {
            margin-bottom: 0;
            color: #8b8f98;
            font-size: 13px;
        }

        /* Announcement */
        .announcement-block {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .announcement-block .meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .btn-primary {
            background: #175196;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #123f75;
        }

        @media (max-width: 1000px) {
            .content { max-width: 100%; }
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                min-height: auto;
            }
            .main {
                margin-left: 0;
                padding: 25px 22px;
            }
            .summary-grid { grid-template-columns: 1fr; }
            table { table-layout: auto; }
            td { white-space: normal; }
            .announcement-block { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">TeamBox</div>
    <a class="active">Dashboard</a>
    <a>Projects</a>
    <a>Teams</a>
    <a>Students</a>
    <a>Contribution Reports</a>
    <a>Files</a>
    <a>Announcements</a>
    <a>Profile</a>
    <a href="login.php" class="logout">Log Out</a>
</aside>

<main class="main">
    <div class="content">

        <h1>Instructor Dashboard</h1>
        <p class="subtitle">ITE4116M Final Year Project · Esther Yuen · 2026/27</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Students</div>
                <div class="value"><?php echo (int)$summary['total_students']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Formed Teams</div>
                <div class="value"><?php echo (int)$summary['formed_teams']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Unassigned</div>
                <div class="value"><?php echo (int)$summary['unassigned']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Past Due Tasks</div>
                <div class="value"><?php echo (int)$summary['past_due']; ?></div>
            </div>
        </div>

        <!-- Teams -->
        <h2>Teams</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>Leader</th>
                    <th>Members</th>
                    <th>Progress</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                    <th>Overdue</th>
                    <th></th>
                </tr>
                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td>
                            <?php if ($t['leader'] !== ''): ?>
                                <?php echo htmlspecialchars($t['leader']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['members']); ?></td>
                        <td>
                            <?php if ($t['progress'] !== ''): ?>
                                <?php echo htmlspecialchars($t['progress']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="muted"><?php echo htmlspecialchars($t['last_activity']); ?></td>
                        <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                        <td>
                            <?php if ((int)$t['overdue'] > 0): ?>
                                <?php echo (int)$t['overdue']; ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="team_detail.php?id=<?php echo (int)$t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Unassigned Students -->
        <h2>Unassigned Students</h2>
        <div class="card">
            <ul>
                <?php foreach ($unassigned as $u): ?>
                    <li>
                    <span>
                        <?php echo htmlspecialchars($u['name']); ?>
                        <span class="muted">
                            <?php echo htmlspecialchars($u['student_number']); ?>
                            · <?php echo htmlspecialchars($u['year']); ?>
                        </span>
                    </span>
                        <button class="link">Assign</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Action Required -->
        <h2>Action Required</h2>
        <div class="card">
            <ul>
                <?php foreach ($actions as $a): ?>
                    <li>
                        <span><?php echo htmlspecialchars($a['text']); ?></span>
                        <button class="link"><?php echo htmlspecialchars($a['btn']); ?></button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Activity -->
        <h2>Activity</h2>
        <div class="card">
            <ul>
                <?php foreach ($activities as $a): ?>
                    <li>
                        <span><?php echo htmlspecialchars($a[0]); ?></span>
                        <span class="muted"><?php echo htmlspecialchars($a[1]); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contribution Overview -->
        <h2>Contribution Overview</h2>
        <div class="card">
            <table class="contribution-table">
                <?php foreach ($contributions as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c[0]); ?></td>
                        <td><?php echo htmlspecialchars($c[1]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Files -->
        <h2>Files</h2>
        <div class="card">
            <div class="file-block">
                <div class="row">This week: <?php echo (int)$files['week_count']; ?> files uploaded</div>
                <div class="row">Latest: <?php echo htmlspecialchars($files['latest']); ?></div>
            </div>
        </div>

        <!-- Announcements -->
        <h2>Announcements</h2>
        <div class="card">
            <div class="announcement-block">
                <div class="meta">Last sent: <?php echo htmlspecialchars($announcement['last_sent']); ?></div>
                <button class="btn-primary">Send Announcement to All Teams</button>
            </div>
        </div>

    </div>
</main>

</body>
</html>