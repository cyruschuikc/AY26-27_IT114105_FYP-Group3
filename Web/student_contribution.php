<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($conn) || !$conn) {
    die('Database connection not available. Check db.php.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// =========================================================
// 1. 取 student_id
// =========================================================
$studentID = 0;
$studentName = '';

$stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, u.username
    FROM `students` s
    JOIN `users` u ON u.id = s.user_id
    WHERE s.user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: logout.php');
    exit;
}
$studentID   = (int) $rc['student_id'];
$studentName = $rc['username'];

// =========================================================
// 2. 取我所有 group (Voting / Executing)
// =========================================================
$myGroups = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        g.id            AS group_id,
        g.name          AS group_name,
        g.status,
        g.leader_id,
        p.name          AS project_name,
        c.code          AS course_code
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    LEFT JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `courses`  c ON c.id = g.course_id
    WHERE gm.student_id = ?
      AND g.status IN ('Voting', 'Executing')
    ORDER BY g.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $myGroups[] = $row;
}
mysqli_stmt_close($stmt);

// =========================================================
// 3. 決定 active group
// =========================================================
$activeGroupID = 0;
if (isset($_GET['group_id'])) {
    $activeGroupID = (int) $_GET['group_id'];
}

$myGroup = null;
if ($activeGroupID > 0) {
    foreach ($myGroups as $g) {
        if ((int)$g['group_id'] === $activeGroupID) {
            $myGroup = $g;
            break;
        }
    }
}
if (!$myGroup && count($myGroups) > 0) {
    $myGroup = $myGroups[0];
    $activeGroupID = (int) $myGroup['group_id'];
}

// =========================================================
// 4. 計算每位成員的貢獻分數（只計算 Task）
// =========================================================
$memberStats = array();
$totalTeamScore = 0;
$topContributor = '—';
$topScore = 0;

if ($myGroup) {

    // 4.1 取所有組員
    $members = array();
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            s.student_number,
            u.username      AS name,
            gm.role
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, s.id
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $members[] = $row;
    }
    mysqli_stmt_close($stmt);

    // 4.2 初始化每位成員的分數
    foreach ($members as $m) {
        $sid = (int)$m['student_id'];
        $memberStats[$sid] = array(
                'student_id'     => $sid,
                'student_number' => $m['student_number'],
                'name'           => $m['name'],
                'role'           => $m['role'],
                'tasks_completed'=> 0,
                'total_points'   => 0
        );
    }

    // 4.3 任務完成數（Waiting_Confirm 或 Completed）
    $stmt = mysqli_prepare($conn, "
        SELECT assigned_to, COUNT(*) AS c
        FROM `tasks`
        WHERE group_id = ?
          AND status IN ('Waiting_Confirm', 'Completed')
          AND assigned_to IS NOT NULL
        GROUP BY assigned_to
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $sid = (int)$row['assigned_to'];
        if (isset($memberStats[$sid])) {
            $memberStats[$sid]['tasks_completed'] = (int)$row['c'];
            $memberStats[$sid]['total_points']    = (int)$row['c'] * 10;
        }
    }
    mysqli_stmt_close($stmt);

    // 4.4 計算總分、最高分
    foreach ($memberStats as $sid => $stat) {
        $totalTeamScore += $stat['total_points'];

        if ($stat['total_points'] > $topScore) {
            $topScore = $stat['total_points'];
            $topContributor = $stat['name'];
        }
    }
}

// =========================================================
// 5. 統計 Summary
// =========================================================
$totalMembers = count($memberStats);
$averageScore = $totalMembers > 0 ? round($totalTeamScore / $totalMembers) : 0;

// 團隊平衡度（最高分與最低分的差距）
$balanceLabel = '—';
if ($totalMembers > 0) {
    $scores = array();
    foreach ($memberStats as $stat) {
        $scores[] = $stat['total_points'];
    }
    $maxScore = max($scores);
    $minScore = min($scores);
    $diff = $maxScore - $minScore;

    if ($diff <= 5)       $balanceLabel = 'Excellent';
    elseif ($diff <= 15)  $balanceLabel = 'Good';
    elseif ($diff <= 30)  $balanceLabel = 'Fair';
    else                  $balanceLabel = 'Uneven';
}

// =========================================================
// 6. 顏色 Palette
// =========================================================
$palette = array(
        '#000000', '#555555', '#999999', '#cccccc',
        '#175196', '#2e9d4d', '#e0a030', '#c62828',
        '#6b21a8', '#0891b2'
);

$memberColors = array();
$colorIndex = 0;
foreach ($memberStats as $sid => $stat) {
    $memberColors[$sid] = $palette[$colorIndex % count($palette)];
    $colorIndex++;
}

// =========================================================
// 7. Recent Contribution Activity
//    ★ 改為直接從 contributions 表讀取（只有加分的活動）
// =========================================================
$recentActivities = array();

if ($myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            c.id,
            c.title,
            c.notes,
            c.contribution_date,
            c.created_at,
            u.username AS actor_name,
            s.student_number
        FROM `contributions` c
        LEFT JOIN `students` s ON s.id = c.student_id
        LEFT JOIN `users`    u ON u.id = s.user_id
        WHERE c.group_id = ?
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $recentActivities[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 8. Helper
// =========================================================
if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (empty($datetime)) return '—';
        $ts   = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60)         return 'Just now';
        if ($diff < 3600)       return floor($diff / 60) . 'm ago';
        if ($diff < 86400)      return floor($diff / 3600) . 'h ago';
        if ($diff < 86400 * 2)  return 'Yesterday';
        if ($diff < 86400 * 7)  return floor($diff / 86400) . 'd ago';
        return date('d M Y', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Analysis | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        .navbar {
            width: 100%; padding: 15px 30px; background: black;
            display: flex; flex-direction: column; align-items: center; gap: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-top {
            position: relative; width: 100%; min-height: 65px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-area { display: flex; justify-content: center; align-items: center; gap: 12px; }
        .logo-area img { width: 65px; height: 65px; object-fit: contain; display: block; }
        .logo-area span { color: white; font-size: 30px; font-weight: bold; }

        .bell-link {
            position: absolute; right: 0; top: 50%; transform: translateY(-50%);
            display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%; transition: background 0.3s;
        }
        .bell-link:hover { background: rgba(255, 255, 255, 0.15); }
        .bell-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        .bell-badge {
            position: absolute; top: 2px; right: 2px;
            min-width: 18px; height: 18px; padding: 0 5px;
            background: #d93025; color: white; border-radius: 9px;
            font-size: 11px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid black;
        }

        .function-menu {
            width: 100%; display: flex; justify-content: center; align-items: center;
            flex-wrap: wrap; gap: 22px;
        }
        .nav-link { color: white; font-size: 15px; text-decoration: none; padding: 6px 2px; transition: color 0.3s; }
        .nav-link:hover { color: #bbbbbb; }
        .nav-link.active { color: white; font-weight: bold; border-bottom: 2px solid white; }
        .logout-link { color: #ff8888; }
        .logout-link:hover { color: #ff4444; }

        .main-content { width: 90%; max-width: 1250px; margin: 40px auto; }

        .page-heading {
            padding: 30px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; flex-wrap: wrap;
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .export-btn {
            min-width: 160px; height: 48px; padding: 0 20px;
            background: black; color: white;
            border: 2px solid black; border-radius: 9px;
            font-size: 15px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .export-btn:hover { background: #333; }

        .summary-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 20px; margin-bottom: 25px;
        }
        .summary-card {
            min-height: 145px; padding: 24px;
            background: white; border-left: 5px solid black;
            border-radius: 15px; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .summary-card:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); }
        .summary-card h2 { margin-bottom: 15px; color: #666; font-size: 16px; }
        .summary-number { color: black; font-size: 36px; font-weight: bold; }
        .summary-description { margin-top: 7px; color: #777; font-size: 13px; }

        .content-box {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .content-box h2 {
            padding-bottom: 15px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }

        /* =========================
           Pie Chart Section
        ========================= */
        .chart-wrapper {
            display: flex; align-items: center; justify-content: center;
            gap: 50px; flex-wrap: wrap;
        }
        .pie-chart { width: 280px; height: 280px; flex-shrink: 0; }

        .chart-legend {
            display: flex; flex-direction: column; gap: 14px; min-width: 260px;
        }
        .legend-item {
            padding: 12px 14px;
            background: #fafafa; border: 1px solid #dddddd; border-radius: 10px;
            display: flex; align-items: center; gap: 12px;
        }
        .legend-color { width: 18px; height: 18px; border-radius: 4px; flex-shrink: 0; }
        .legend-info { flex: 1; }
        .legend-info h3 { font-size: 15px; margin-bottom: 3px; }
        .legend-info p { color: #777; font-size: 13px; }
        .legend-percent { font-size: 16px; font-weight: bold; white-space: nowrap; }

        /* =========================
           Member Contribution Rows
        ========================= */
        .member-contrib { padding: 18px 0; border-bottom: 1px solid #eeeeee; }
        .member-contrib:last-child { border-bottom: none; }

        .member-header {
            margin-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center; gap: 15px;
        }
        .member-left { display: flex; align-items: center; gap: 14px; }
        .member-avatar {
            width: 44px; height: 44px; flex-shrink: 0;
            color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 15px; font-weight: bold;
        }
        .member-info h3 { font-size: 16px; margin-bottom: 4px; }
        .member-info p { color: #777; font-size: 13px; }
        .member-score { font-size: 22px; font-weight: bold; white-space: nowrap; }

        .contrib-bar {
            width: 100%; height: 14px;
            background: #dddddd; border-radius: 10px; overflow: hidden;
        }
        .contrib-value { height: 100%; border-radius: 10px; }

        .contrib-details {
            margin-top: 8px;
            display: flex; justify-content: space-between;
            color: #777; font-size: 12px;
        }

        /* =========================
           Activity Log
        ========================= */
        .activity-item {
            padding: 15px 0; border-bottom: 1px solid #eeeeee;
            display: flex; gap: 14px;
        }
        .activity-item:last-child { border-bottom: none; }

        .activity-icon {
            width: 32px; height: 32px; flex-shrink: 0;
            background: black; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 14px; font-weight: bold;
        }

        .activity-content { flex: 1; }
        .activity-content h3 { margin-bottom: 5px; font-size: 15px; }
        .activity-content p { color: #777; font-size: 13px; line-height: 1.5; }
        .activity-time { font-size: 12px; color: #aaa; white-space: nowrap; }
        .activity-points {
            font-size: 13px; font-weight: bold;
            color: #176b2c; white-space: nowrap;
        }

        .info-note {
            padding: 18px;
            background: #f7f7f7; border-left: 5px solid black; border-radius: 8px;
            color: #666; font-size: 14px; line-height: 1.6;
        }

        .empty-state {
            padding: 60px 40px; background: white;
            border-radius: 15px; border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .empty-state h2 { font-size: 24px; margin-bottom: 12px; }
        .empty-state p { color: #666; font-size: 15px; line-height: 1.6; max-width: 560px; margin: 0 auto 28px; }
        .empty-state .create-group-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 200px; height: 50px; padding: 0 24px;
            background: black; color: white;
            border: 2px solid black; border-radius: 9px;
            font-size: 16px; font-weight: bold;
            text-decoration: none; cursor: pointer; transition: 0.3s;
        }
        .empty-state .create-group-btn:hover { background: #333; }

        .group-switcher {
            padding: 20px 24px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            border-left: 6px solid #245889;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        }
        .group-switcher label { font-size: 15px; color: #333; font-weight: bold; }
        .group-switcher select {
            flex: 1; min-width: 260px; height: 45px; padding: 0 14px;
            background: white; color: #222;
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 14px; cursor: pointer; outline: none;
        }
        .group-switcher select:focus { border-color: black; }

        .muted { color: #777; font-size: 14px; }

        @media (max-width: 1000px) {
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 650px) {
            .navbar { padding: 15px; }
            .logo-area img { width: 55px; height: 55px; }
            .logo-area span { font-size: 26px; }
            .bell-link { width: 38px; height: 38px; }
            .bell-link img { width: 22px; height: 22px; }
            .function-menu { flex-direction: column; gap: 12px; }
            .main-content { width: 94%; margin: 25px auto; }
            .page-heading h1 { font-size: 28px; }
            .page-heading { flex-direction: column; align-items: flex-start; }
            .export-btn { width: 100%; }
            .summary-grid { grid-template-columns: 1fr; }
            .chart-wrapper { gap: 30px; }
            .pie-chart { width: 220px; height: 220px; }
            .chart-legend { min-width: 100%; }
            .group-switcher { flex-direction: column; align-items: stretch; }
            .group-switcher select { min-width: 100%; }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="navbar-top">
        <div class="logo-area">
            <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
            <span>TeamBox</span>
        </div>

        <a href="student_notifications.php" class="bell-link" title="Notifications">
            <img src="bell.png" alt="Notifications">
            <?php if (isset($unreadNotifications) && $unreadNotifications > 0): ?>
                <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link active">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <?php if (count($myGroups) === 0): ?>

        <section class="page-heading">
            <div>
                <h1>Contribution Analysis</h1>
                <p>You don't have an active team yet.</p>
            </div>
        </section>

        <div class="empty-state">
            <h2>No active team</h2>
            <p>
                Contribution analysis is only available after your team
                has been submitted to your supervisor.
            </p>
            <a href="student_create_group.php" class="create-group-btn">
                Go to Create Group
            </a>
        </div>

    <?php else: ?>

    <?php if (count($myGroups) > 1): ?>
        <div class="group-switcher">
            <label for="groupSwitcher">Switch Project / Group:</label>
            <select id="groupSwitcher" onchange="switchGroup(this.value)">
                <?php foreach ($myGroups as $g): ?>
                    <option value="<?php echo (int)$g['group_id']; ?>"
                            <?php echo ((int)$g['group_id'] === $activeGroupID) ? 'selected' : ''; ?>>
                        <?php
                        echo htmlspecialchars(
                                ($g['course_code'] ? $g['course_code'] . ' - ' : '')
                                . $g['project_name']
                                . ' / ' . $g['group_name']
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <script>
            function switchGroup(groupId) {
                window.location.href = 'student_contribution.php?group_id=' + groupId;
            }
        </script>
    <?php endif; ?>

        <section class="page-heading">
            <div>
                <h1>Contribution Analysis</h1>
                <p>
                    Track each member's participation. Contribution scores
                    are based solely on completed tasks. Each completed task
                    is worth 10 points.
                </p>
            </div>
            <button type="button" class="export-btn">Export Report (PDF)</button>
        </section>

        <!-- Summary Cards -->
        <section class="summary-grid">
            <div class="summary-card">
                <h2>Total Team Score</h2>
                <p class="summary-number"><?php echo $totalTeamScore; ?></p>
                <p class="summary-description">Combined contribution points</p>
            </div>

            <div class="summary-card">
                <h2>Average Score</h2>
                <p class="summary-number"><?php echo $averageScore; ?></p>
                <p class="summary-description">Average per member</p>
            </div>

            <div class="summary-card">
                <h2>Top Contributor</h2>
                <p class="summary-number"><?php echo $topScore; ?></p>
                <p class="summary-description"><?php echo htmlspecialchars($topContributor); ?></p>
            </div>

            <div class="summary-card">
                <h2>Team Balance</h2>
                <p class="summary-number"><?php echo htmlspecialchars($balanceLabel); ?></p>
                <p class="summary-description">Distribution of contribution points</p>
            </div>
        </section>

        <!-- Pie Chart -->
        <section class="content-box">
            <h2>Contribution Distribution</h2>

            <div class="chart-wrapper">
                <?php
                $offset = 25;
                $chartSegments = array();
                foreach ($memberStats as $sid => $stat) {
                    $pct = $totalTeamScore > 0 ? ($stat['total_points'] / $totalTeamScore) * 100 : 0;
                    $dash = round($pct, 2);
                    $gap  = round(100 - $pct, 2);
                    $chartSegments[] = array(
                            'color'  => $memberColors[$sid],
                            'dash'   => $dash,
                            'gap'    => $gap,
                            'offset' => $offset,
                            'name'   => $stat['name'],
                            'points' => $stat['total_points'],
                            'pct'    => round($pct, 1)
                    );
                    $offset -= $pct;
                }
                ?>
                <svg class="pie-chart" viewBox="0 0 42 42">
                    <circle cx="21" cy="21" r="15.915"
                            fill="transparent" stroke="#eeeeee" stroke-width="8"></circle>

                    <?php if ($totalTeamScore > 0): ?>
                        <?php foreach ($chartSegments as $seg): ?>
                            <circle cx="21" cy="21" r="15.915"
                                    fill="transparent"
                                    stroke="<?php echo htmlspecialchars($seg['color']); ?>"
                                    stroke-width="8"
                                    stroke-dasharray="<?php echo $seg['dash']; ?> <?php echo $seg['gap']; ?>"
                                    stroke-dashoffset="<?php echo $seg['offset']; ?>">
                                <title><?php echo htmlspecialchars($seg['name']); ?> · <?php echo $seg['points']; ?> pts (<?php echo $seg['pct']; ?>%)</title>
                            </circle>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <text x="21" y="20" text-anchor="middle" font-size="4" font-weight="bold" fill="#222">
                        <?php echo $totalTeamScore; ?>
                    </text>
                    <text x="21" y="24.5" text-anchor="middle" font-size="2.2" fill="#777">
                        Total Points
                    </text>
                </svg>

                <div class="chart-legend">
                    <?php if (count($chartSegments) === 0): ?>
                        <p class="muted">No contribution data yet.</p>
                    <?php endif; ?>

                    <?php foreach ($chartSegments as $seg): ?>
                        <div class="legend-item">
                            <div class="legend-color" style="background: <?php echo htmlspecialchars($seg['color']); ?>;"></div>
                            <div class="legend-info">
                                <h3><?php echo htmlspecialchars($seg['name']); ?></h3>
                                <p><?php echo (int)$seg['points']; ?> points</p>
                            </div>
                            <div class="legend-percent"><?php echo $seg['pct']; ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Member Contribution Details -->
        <section class="content-box">
            <h2>Member Contribution Details</h2>

            <?php if (count($memberStats) === 0): ?>
                <p class="muted">No members in this group yet.</p>
            <?php endif; ?>

            <?php
            $maxMemberScore = 0;
            foreach ($memberStats as $stat) {
                if ($stat['total_points'] > $maxMemberScore) $maxMemberScore = $stat['total_points'];
            }
            ?>

            <?php foreach ($memberStats as $sid => $stat): ?>
                <?php
                $barWidth = $maxMemberScore > 0 ? round(($stat['total_points'] / $maxMemberScore) * 100) : 0;
                $avatarColor = $memberColors[$sid];
                ?>
                <div class="member-contrib">
                    <div class="member-header">
                        <div class="member-left">
                            <div class="member-avatar" style="background: <?php echo htmlspecialchars($avatarColor); ?>;">
                                <?php echo htmlspecialchars(initials($stat['name'])); ?>
                            </div>
                            <div class="member-info">
                                <h3>
                                    <?php echo htmlspecialchars($stat['name']); ?>
                                    <?php if ($stat['role'] === 'leader'): ?>
                                        (Leader)
                                    <?php endif; ?>
                                </h3>
                                <p>Student ID: <?php echo htmlspecialchars($stat['student_number']); ?></p>
                            </div>
                        </div>
                        <div class="member-score"><?php echo $stat['total_points']; ?> pts</div>
                    </div>

                    <div class="contrib-bar">
                        <div class="contrib-value" style="width: <?php echo $barWidth; ?>%; background: <?php echo htmlspecialchars($avatarColor); ?>;"></div>
                    </div>

                    <div class="contrib-details">
                        <span>Tasks Completed: <?php echo $stat['tasks_completed']; ?></span>
                        <span>Points: <?php echo $stat['total_points']; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Recent Contribution Activity（只顯示已加分的活動） -->
        <section class="content-box">
            <h2>Recent Contribution Activity</h2>

            <?php if (count($recentActivities) === 0): ?>
                <p class="muted">No contribution points have been awarded yet.</p>
            <?php endif; ?>

            <?php foreach ($recentActivities as $a): ?>
                <div class="activity-item">
                    <div class="activity-icon">✓</div>
                    <div class="activity-content">
                        <h3><?php echo htmlspecialchars($a['actor_name'] ?: 'System'); ?></h3>
                        <p>
                            <?php
                            // 顯示「Completed task: <id>」對應的實際任務名稱
                            if (preg_match('/^Completed task:\s*(\d+)$/', $a['title'], $matches)) {
                                $tid = (int)$matches[1];
                                $taskTitle = $a['title'];

                                // 查任務名稱（簡單查詢）
                                $stmtT = mysqli_prepare($conn, "SELECT title FROM `tasks` WHERE id = ? LIMIT 1");
                                if ($stmtT) {
                                    mysqli_stmt_bind_param($stmtT, "i", $tid);
                                    mysqli_stmt_execute($stmtT);
                                    $rsT = mysqli_stmt_get_result($stmtT);
                                    $rowT = mysqli_fetch_assoc($rsT);
                                    mysqli_stmt_close($stmtT);
                                    if ($rowT) {
                                        $taskTitle = $rowT['title'];
                                    }
                                }
                                echo 'Completed task: <strong>' . htmlspecialchars($taskTitle) . '</strong>';
                            } else {
                                echo htmlspecialchars($a['title']);
                            }
                            ?>
                        </p>
                        <?php if (!empty($a['notes'])): ?>
                            <p style="margin-top:4px; color:#999; font-size:12px; font-style:italic;">
                                <?php echo htmlspecialchars($a['notes']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="activity-points">+10 pts</div>
                    <div class="activity-time"><?php echo timeAgo($a['created_at']); ?></div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Info Note -->
        <div class="info-note">
            The contribution score is calculated automatically by the
            system. Only completed tasks count towards the score.
            Each completed task is worth 10 points. File uploads and
            chat messages do not contribute to the score.
        </div>

    <?php endif; ?>

</main>

</body>
</html>