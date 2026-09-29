<?php
// team-detail.php

require 'db.php';

$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// ===== 查 Team + Project =====
// 注意：这里假设后面会补 Teams / Projects 表。
// 先用假数据顶替，直接看下面那段
$team = null;
try {
    $sql = "
        SELECT
            t.id            AS team_id,
            t.name          AS team_name,
            p.name          AS project_name,
            p.category      AS project_category,
            p.supervisor    AS project_supervisor,
            p.period_start,
            p.period_end,
            t.status,
            t.formed_at,
            t.roster_locked_at,
            t.leader_elected_at,
            t.last_activity_at
        FROM Teams t
        JOIN Projects p ON p.id = t.project_id
        WHERE t.id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $teamId));
    $team = $stmt->fetch();
} catch (PDOException $e) {
    $team = null;
}

// fallback：如果数据库里没有 Teams / Projects 表，就用假数据
if (!$team) {
    $team = [
        'team_id'            => $teamId,
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
    ];
}

// ===== 查 Members =====
// JOIN Users + Students + Team_Members（Team_Members 之后要建）
// 现在还没有 Team_Members 表，也用 fallback。
$members = [];
try {
    $sql = "
        SELECT
            u.username      AS name,
            s.student_number,
            s.major,
            s.grade_level,
            tm.role         AS team_role,
            tm.is_leader,
            tm.tasks_done
        FROM Team_Members tm
        JOIN Students s ON s.id = tm.student_id
        JOIN Users u    ON u.id = s.user_id
        WHERE tm.team_id = :team_id
        ORDER BY tm.is_leader DESC, tm.joined_at ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':team_id' => $teamId]);
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    $members = [];
}

if (!$members) {
    $members = [
        [
            'name'           => 'CHAN Ho Yin',
            'student_number' => '260222111',
            'major'          => 'SE',
            'grade_level'    => 12,
            'team_role'      => 'Leader · Full-stack',
            'is_leader'      => 1,
            'tasks_done'     => '2 / 3',
        ],
        [
            'name'           => 'CHUI Ka Chun',
            'student_number' => '260222112',
            'major'          => 'SE',
            'grade_level'    => 12,
            'team_role'      => 'Android Developer',
            'is_leader'      => 0,
            'tasks_done'     => '1 / 2',
        ],
        [
            'name'           => 'LAI Ka-Yu Savio',
            'student_number' => '260222113',
            'major'          => 'SE',
            'grade_level'    => 12,
            'team_role'      => 'Backend Developer',
            'is_leader'      => 0,
            'tasks_done'     => '1 / 2',
        ],
        [
            'name'           => 'MIAO Tung',
            'student_number' => '260222114',
            'major'          => 'SE',
            'grade_level'    => 12,
            'team_role'      => 'Frontend Developer',
            'is_leader'      => 0,
            'tasks_done'     => '0 / 2',
        ],
    ];
}

// ===== 把 grade_level 转成人类看得懂的班级 =====
function formatClass($major, $gradeLevel) {
    // 12 -> SE-2A 这种；这里只是粗略映射，按你项目实际规则再调
    if ($gradeLevel >= 12) {
        $year = $gradeLevel - 10; // 12 -> 2
    } else {
        $year = $gradeLevel;
    }
    return $major . '-' . $year . 'B';
}

// ===== Progress =====
$progress = [
    ['label' => 'Overall',         'value' => '<span class="progress"><span class="bar"><span style="width: 60%"></span></span>60%</span>', 'raw' => true],
    ['label' => 'Tasks Completed', 'value' => '3 / 5'],
    ['label' => 'Team Formed',     'value' => date('d M Y', strtotime($team['formed_at']))],
    ['label' => 'Roster Locked',   'value' => date('d M Y', strtotime($team['roster_locked_at']))],
    ['label' => 'Leader Elected',  'value' => date('d M Y', strtotime($team['leader_elected_at']))],
    ['label' => 'Last Activity',   'value' => date('H:i', strtotime($team['last_activity_at'])) . ' today'],
];

// ===== Division of Work（暂时写死，等 Tasks 表建好再从数据库取） =====
$division = [
    ['CHAN Ho Yin',     'Proposal, Architecture, Web UI'],
    ['CHUI Ka Chun',    'Android App, Push Notification'],
    ['LAI Ka-Yu Savio', 'Database, REST API'],
    ['MIAO Tung',       'Web Frontend, File Module'],
    ['Shared',          'Testing, Documentation'],
];

// ===== Tasks（之后从 Tasks 表取） =====
$tasks = [
    ['name' => 'Complete Project Proposal', 'owner' => 'CHAN Ho Yin · CHUI Ka Chun', 'status' => 'Completed',   'due' => '30 Sep 2026'],
    ['name' => 'Requirement Analysis',      'owner' => 'LAI Ka-Yu Savio',           'status' => 'Completed',   'due' => '05 Oct 2026'],
    ['name' => 'Create System Interface',   'owner' => 'MIAO Tung',                 'status' => 'In Progress', 'due' => '10 Oct 2026'],
    ['name' => 'Database Schema Design',    'owner' => 'LAI Ka-Yu Savio',           'status' => 'Pending',     'due' => '15 Oct 2026'],
    ['name' => 'Android Prototype',         'owner' => 'CHUI Ka Chun',              'status' => 'Pending',     'due' => '25 Oct 2026'],
];

// ===== Files（之后从 Files 表取） =====
$files = [
    ['name' => 'Proposal_v2.docx',         'owner' => 'CHAN Ho Yin',     'meta' => '2.4 MB · 10:42 today'],
    ['name' => 'Requirement_Analysis.pdf', 'owner' => 'LAI Ka-Yu Savio', 'meta' => '1.8 MB · Yesterday'],
    ['name' => 'UI_Wireframe.fig',         'owner' => 'MIAO Tung',       'meta' => '5.1 MB · 2 days ago'],
];

// ===== Contribution（之后从 Contributions 表取） =====
$contributions = [
    ['CHAN Ho Yin',     2, 3, 14, '32%'],
    ['CHUI Ka Chun',    1, 1,  8, '24%'],
    ['LAI Ka-Yu Savio', 1, 2, 11, '26%'],
    ['MIAO Tung',       0, 1,  6, '18%'],
];

// ===== Activity（之后从 Activity_Log 表取） =====
$activities = [
    ['CHAN Ho Yin uploaded Proposal_v2.docx',                '10:42'],
    ['LAI Ka-Yu Savio marked Requirement Analysis complete', '09:20'],
    ['MIAO Tung commented on UI_Wireframe',                  'Yesterday'],
    ['Team roster locked',                                   '20 Sep'],
    ['CHAN Ho Yin elected as team leader',                   '21 Sep'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($team['team_name']) ?> | TeamBox</title>
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

        .back {
            display: inline-block;
            font-size: 13px;
            color: #8b8f98;
            text-decoration: none;
            margin-bottom: 22px;
            cursor: pointer;
        }

        .back:hover {
            color: #2c2f36;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 34px;
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

        .pill {
            display: inline-block;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            background: #f2f3f5;
            color: #6b6f78;
            font-weight: 500;
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

        .leader-mark {
            font-size: 11px;
            color: #6b6f78;
            margin-left: 6px;
            letter-spacing: 0.3px;
        }

        /* Progress */
        .progress-table td {
            white-space: normal;
        }

        .progress-table td:first-child {
            width: 40%;
            background: #175196;
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .progress-table td:last-child {
            color: #2c2f36;
        }

        /* Two-column info grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Task list */
        .task-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 14px;
        }

        .task-row:last-child {
            border-bottom: none;
        }

        .task-row .task-name {
            color: #2c2f36;
        }

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

        /* Progress bar */
        .progress {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #6b6f78;
        }

        .progress .bar {
            width: 120px;
            height: 6px;
            background: #eceef1;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress .bar span {
            display: block;
            height: 100%;
            background: #175196;
            border-radius: 6px;
        }

        /* File list */
        .file-list {
            list-style: none;
            font-size: 14px;
        }

        .file-list li {
            padding: 13px 16px;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
        }

        .file-list li:last-child {
            border-bottom: none;
        }

        /* Activity list */
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

        .activity-list li:last-child {
            border-bottom: none;
        }

        @media (max-width: 1000px) {
            .content {
                max-width: 100%;
            }
        }

        @media (max-width: 800px) {
            .info-grid { grid-template-columns: 1fr; gap: 0; }
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
            .page-head { flex-direction: column; gap: 12px; }
            table { table-layout: auto; }
            td { white-space: normal; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">TeamBox</div>
    <a>Dashboard</a>
    <a>Projects</a>
    <a class="active">Teams</a>
    <a>Students</a>
    <a>Contribution Reports</a>
    <a>Files</a>
    <a>Announcements</a>
    <a>Profile</a>
    <a href="login.php" class="logout">Log Out</a>
</aside>

<main class="main">
    <div class="content">

        <a href="teacher_index.php?id=1" class="back">← Back to Teams</a>

        <div class="page-head">
            <div>
                <h1><?= htmlspecialchars($team['team_name']) ?></h1>
                <div class="meta">
                    <?= htmlspecialchars($team['project_name']) ?> ·
                    <?= htmlspecialchars($team['project_category']) ?>
                </div>
            </div>
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
                    <td><?= htmlspecialchars($team['project_name']) ?></td>
                    <td><?= htmlspecialchars($team['project_category']) ?></td>
                    <td><?= htmlspecialchars($team['project_supervisor']) ?></td>
                    <td>
                        <?= date('M Y', strtotime($team['period_start'])) ?>
                        –
                        <?= date('M Y', strtotime($team['period_end'])) ?>
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
                            <?= htmlspecialchars($m['name']) ?>
                            <?php if (!empty($m['is_leader'])): ?>
                                <span class="leader-mark">LEADER</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($m['student_number']) ?></td>
                        <td><?= htmlspecialchars(formatClass($m['major'], $m['grade_level'])) ?></td>
                        <td><?= htmlspecialchars($m['team_role']) ?></td>
                        <td><?= htmlspecialchars($m['tasks_done']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Progress + division of work -->
        <div class="info-grid">
            <div>
                <h2>Progress</h2>
                <div class="card">
                    <table class="progress-table">
                        <?php foreach ($progress as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['label']) ?></td>
                                <td>
                                    <?php if (!empty($p['raw'])): ?>
                                        <?= $p['value'] ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($p['value']) ?>
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
                                <td><?= htmlspecialchars($d[0]) ?></td>
                                <td><?= htmlspecialchars($d[1]) ?></td>
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
                        <div class="task-name"><?= htmlspecialchars($t['name']) ?></div>
                        <div class="task-owner"><?= htmlspecialchars($t['owner']) ?></div>
                    </div>
                    <div class="task-right">
                        <div><?= htmlspecialchars($t['status']) ?></div>
                        <div class="task-due"><?= htmlspecialchars($t['due']) ?></div>
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
                        <?= htmlspecialchars($f['name']) ?>
                        <span class="muted">· <?= htmlspecialchars($f['owner']) ?></span>
                    </span>
                        <span class="muted"><?= htmlspecialchars($f['meta']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contribution summary -->
        <h2>Contribution</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Member</th>
                    <th>Tasks</th>
                    <th>Files</th>
                    <th>Comments</th>
                    <th>Score</th>
                </tr>
                <?php foreach ($contributions as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c[0]) ?></td>
                        <td><?= htmlspecialchars($c[1]) ?></td>
                        <td><?= htmlspecialchars($c[2]) ?></td>
                        <td><?= htmlspecialchars($c[3]) ?></td>
                        <td><?= htmlspecialchars($c[4]) ?></td>
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
                        <span><?= htmlspecialchars($a[0]) ?></span>
                        <span class="muted"><?= htmlspecialchars($a[1]) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</main>

</body>
</html>