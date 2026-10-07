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
// Unread notifications（给铃铛用）
// =========================================================
$unreadNotifications = 0;

$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM `notifications`
    WHERE user_id = ?
      AND is_read = 0
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int) $rc['c'];
mysqli_stmt_close($stmt);

// =========================================================
// 1. Get student_id
// =========================================================
$studentID = 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if ($rc) {
    $studentID = (int) $rc['id'];
} else {
    header('Location: logout.php');
    exit;
}

// =========================================================
// 2. Get all groups this student has joined
// =========================================================
$myGroups = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        g.id            AS group_id,
        g.name          AS group_name,
        g.status,
        g.max_members,
        g.project_id,
        g.course_id,
        g.leader_id,
        g.created_at,
        p.name          AS project_name,
        c.code          AS course_code,
        c.name          AS course_name,
        (SELECT COUNT(*) FROM `group_members` gm2 WHERE gm2.group_id = g.id) AS member_count
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    LEFT JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `courses`  c ON c.id = g.course_id
    WHERE gm.student_id = ?
      AND g.status <> 'Completed'
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
// 3. Decide active group
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
// 4. Group members
// =========================================================
$groupMembers = array();

if ($myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id AS student_id,
            s.student_number,
            u.username,
            gm.role,
            gm.joined_at
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ?
        ORDER BY gm.joined_at
    ");
    mysqli_stmt_bind_param($stmt, "i", $myGroup['group_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($rs)) {
        $groupMembers[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 5. Invitations
// =========================================================
$invitations = array();

if ($myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            i.id,
            i.status,
            i.sent_at,
            i.responded_at,
            s.id AS invitee_student_id,
            s.student_number,
            u.username
        FROM `invitations` i
        JOIN `students` s ON s.id = i.invitee_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE i.group_id = ?
        ORDER BY i.sent_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $myGroup['group_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($rs)) {
        $invitations[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 6. Vote results
// =========================================================
$voteResults = array();

if ($myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id AS student_id,
            s.student_number,
            u.username,
            COUNT(v.id) AS vote_count
        FROM `leader_votes` v
        JOIN `students` s ON s.id = v.candidate_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE v.group_id = ?
        GROUP BY s.id, s.student_number, u.username
        ORDER BY vote_count DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $myGroup['group_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($rs)) {
        $voteResults[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 7. Helper functions
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

if (!function_exists('statusClass')) {
    function statusClass($status) {
        switch ($status) {
            case 'Accepted':  return 'status-accepted';
            case 'Rejected':  return 'status-rejected';
            case 'Cancelled': return 'status-cancelled';
            default:          return 'status-pending';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        /* =========================================================
           Navbar (same as student_index.php)
        ========================================================= */

        .navbar {
            width: 100%;
            padding: 15px 30px;
            background: black;

            display: flex;
            flex-direction: column;
            gap: 15px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }

        /* First row: logo (center) + bell (right) */
        .navbar-top {
            position: relative;
            width: 100%;
            min-height: 65px;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-area img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            display: block;
        }

        .logo-area span {
            color: white;
            font-size: 30px;
            font-weight: bold;
        }

        /* Bell icon */
        .bell-link {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);

            display: flex;
            align-items: center;
            justify-content: center;

            width: 44px;
            height: 44px;

            border-radius: 50%;
            transition: background 0.3s;
        }

        .bell-link:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .bell-link img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            display: block;
        }

        /* Unread badge */
        .bell-badge {
            position: absolute;
            top: 2px;
            right: 2px;

            min-width: 18px;
            height: 18px;
            padding: 0 5px;

            background: #d93025;
            color: white;
            border-radius: 9px;

            font-size: 11px;
            font-weight: bold;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 2px solid black;
        }

        /* Second row: Navigation */
        .function-menu {
            width: 100%;

            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 22px;
        }

        .nav-link {
            color: white;
            font-size: 15px;
            text-decoration: none;
            padding: 6px 2px;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #bbbbbb;
        }

        .nav-link.active {
            color: white;
            font-weight: bold;
            border-bottom: 2px solid white;
        }

        .logout-link {
            color: #ff8888;
        }

        .logout-link:hover {
            color: #ff4444;
        }

        /* =========================================================
           Page-specific CSS
        ========================================================= */

        .main-content { width: 90%; max-width: 1250px; margin: 40px auto; }

        .page-heading {
            padding: 30px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .info-note {
            padding: 16px 20px; margin-bottom: 25px;
            background: #f7f7f7; border-left: 5px solid black; border-radius: 8px;
            color: #555; font-size: 14px; line-height: 1.6;
        }

        .alert { padding: 14px 18px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; border-left: 4px solid; }
        .alert-success { background: #eaf7ee; color: #176b2c; border-color: #2e9d4d; }
        .alert-error   { background: #fdeeee; color: #a21b1b; border-color: #d32f2f; }

        .section-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }

        .content-box {
            padding: 28px; background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .content-box h2 {
            padding-bottom: 15px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }

        .form-group { margin-bottom: 19px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-size: 14px; font-weight: bold; }
        .form-group input,
        .form-group select {
            width: 100%; height: 47px; padding: 0 13px;
            background: white; color: #222;
            border: 2px solid #cccccc; border-radius: 8px;
            font-size: 14px; outline: none; transition: 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus { border-color: black; }

        .create-group-section {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .create-group-section h2 {
            padding-bottom: 15px; margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee; font-size: 23px;
        }
        .create-group-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

        .primary-btn {
            width: 100%; height: 50px; padding: 0 20px; margin-top: 15px;
            background: black; color: white; border: 2px solid black;
            border-radius: 9px; font-size: 16px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .primary-btn:hover { background: #333; }
        .primary-btn:disabled { background: #cccccc; border-color: #cccccc; color: #888; cursor: not-allowed; }

        .invite-form {
            padding: 18px; margin-bottom: 22px;
            background: #fafafa; border: 2px dashed #cccccc; border-radius: 12px;
        }
        .invite-form-row { display: grid; grid-template-columns: 1fr; gap: 14px; margin-bottom: 14px; }
        .invite-form-row.two-cols { grid-template-columns: 1fr 1fr; gap: 14px; }

        .invite-form .form-group { margin-bottom: 0; }

        .readonly-input {
            background: #f0f0f0 !important;
            color: #555 !important;
            cursor: not-allowed;
        }

        .invite-btn-main {
            width: 100%; height: 47px; padding: 0 20px;
            background: black; color: white; border: 2px solid black;
            border-radius: 8px; font-size: 15px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .invite-btn-main:hover { background: #333; }

        .invite-history-title { font-size: 14px; font-weight: bold; color: #555; margin-bottom: 12px; }
        .invite-history { display: flex; flex-direction: column; gap: 10px; }

        .invite-item {
            padding: 14px; background: #fafafa;
            border: 1px solid #dddddd; border-radius: 10px;
            display: flex; align-items: center; gap: 12px;
        }
        .invite-avatar {
            width: 38px; height: 38px; flex-shrink: 0;
            background: black; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 13px; font-weight: bold;
        }
        .invite-info { flex: 1; }
        .invite-info h3 { font-size: 15px; margin-bottom: 3px; }
        .invite-info p { color: #777; font-size: 12px; }

        .invite-status { padding: 5px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; white-space: nowrap; }
        .status-pending   { background: #fff1c2; color: #765900; }
        .status-accepted  { background: #d9f5df; color: #176b2c; }
        .status-rejected  { background: #ffe1e1; color: #a21b1b; }
        .status-cancelled { background: #eeeeee; color: #777; }

        .cancel-invite-btn {
            background: white; color: #c62828;
            border: 2px solid #c62828; border-radius: 7px;
            padding: 5px 9px; font-size: 11px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .cancel-invite-btn:hover { background: #c62828; color: white; }

        .group-info { margin-bottom: 18px; color: #555; font-size: 14px; line-height: 1.8; }

        .status-badge {
            display: inline-block; padding: 6px 12px; margin-left: 5px;
            background: #e5f0ff; color: #245889; border-radius: 18px;
            font-size: 12px; font-weight: bold;
        }

        .member-item {
            padding: 14px 0; border-bottom: 1px solid #eeeeee;
            display: flex; justify-content: space-between; align-items: center; gap: 15px;
        }
        .member-item:last-child { border-bottom: none; }
        .member-item h3 { font-size: 16px; margin-bottom: 4px; }
        .member-item p { color: #777; font-size: 13px; }

        .member-status { padding: 6px 12px; border-radius: 18px; font-size: 12px; font-weight: bold; white-space: nowrap; }
        .status-accepted-member { background: #d9f5df; color: #176b2c; }
        .status-leader          { background: black; color: white; }

        .vote-option {
            padding: 16px; margin-bottom: 12px;
            background: #fafafa; border: 2px solid #dddddd; border-radius: 10px;
            display: flex; align-items: center; gap: 14px;
            cursor: pointer; transition: 0.3s;
        }
        .vote-option:hover { border-color: black; background: white; }
        .vote-option input[type="radio"] { width: 20px; height: 20px; accent-color: black; cursor: pointer; }
        .vote-option-info { flex: 1; }
        .vote-option-info h3 { font-size: 16px; margin-bottom: 4px; }
        .vote-option-info p { color: #777; font-size: 13px; }

        .result-item { padding: 14px 0; border-bottom: 1px solid #eeeeee; }
        .result-item:last-child { border-bottom: none; }
        .result-header {
            margin-bottom: 8px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .result-header strong { font-size: 15px; }
        .result-header span { font-size: 14px; font-weight: bold; }
        .result-bar {
            width: 100%; height: 12px; background: #dddddd;
            border-radius: 10px; overflow: hidden;
        }
        .result-value { height: 100%; background: black; border-radius: 10px; }

        /* Group Switcher */
        .group-switcher {
            padding: 20px 24px;
            margin-bottom: 25px;

            background: white;
            border-radius: 15px;
            border-left: 6px solid #245889;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);

            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .group-switcher label {
            font-size: 15px;
            color: #333;
            font-weight: bold;
        }
        .group-switcher select {
            flex: 1;
            min-width: 260px;
            height: 45px;
            padding: 0 14px;

            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;

            font-size: 14px;
            cursor: pointer;
            outline: none;
        }
        .group-switcher select:focus { border-color: black; }

        .muted { color: #777; font-size: 14px; }

        /* Responsive */
        @media (max-width: 1000px) {
            .create-group-grid { grid-template-columns: 1fr; gap: 0; }
        }
        @media (max-width: 900px) {
            .section-grid { grid-template-columns: 1fr; }
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

            .group-switcher { flex-direction: column; align-items: stretch; }
            .group-switcher select { min-width: 100%; }

            .invite-form-row.two-cols { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>

<!-- =========================================================
     Top Navigation Bar (same as student_index.php)
========================================================= -->

<nav class="navbar">

    <!-- First row: Logo (center) + Bell (right) -->
    <div class="navbar-top">

        <div class="logo-area">
            <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
            <span>TeamBox</span>
        </div>

        <a href="student_notifications.php" class="bell-link" title="Notifications">
            <img src="bell.png" alt="Notifications">

            <?php if ($unreadNotifications > 0): ?>
                <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
            <?php endif; ?>
        </a>

    </div>

    <!-- Second row: Navigation -->
    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link active">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>

</nav>

<!-- =========================================================
     Main Content
========================================================= -->

<main class="main-content">

    <section class="page-heading">
        <h1>Create Group</h1>
        <p>
            Name your group, choose the course and project,
            invite classmates by Student ID, then lock the team and vote for a leader.
        </p>
    </section>

    <div class="info-note">
        <strong>How it works:</strong>
        1. Fill the Create Group form (Course + Group Name + Project + Max Members) &nbsp;→&nbsp;
        2. Enter Student ID, click "Invite" &nbsp;→&nbsp;
        3. Wait for them to accept &nbsp;→&nbsp;
        4. Group fills to full &nbsp;→&nbsp;
        5. Lock Team &nbsp;→&nbsp;
        6. Vote Group Leader.
    </div>

    <!-- System messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'created'): ?>
            <div class="alert alert-success">Group created successfully.</div>
        <?php elseif ($_GET['msg'] === 'invited'): ?>
            <div class="alert alert-success">Invitation sent.</div>
        <?php elseif ($_GET['msg'] === 'cancel'): ?>
            <div class="alert alert-success">Invitation cancelled.</div>
        <?php elseif ($_GET['msg'] === 'locked'): ?>
            <div class="alert alert-success">Team locked. Voting has started.</div>
        <?php elseif ($_GET['msg'] === 'voted'): ?>
            <div class="alert alert-success">Your vote has been recorded.</div>
        <?php elseif ($_GET['msg'] === 'notfound'): ?>
            <div class="alert alert-error">Student ID not found.</div>
        <?php elseif ($_GET['msg'] === 'notfull'): ?>
            <div class="alert alert-error">Team is not full yet.</div>
        <?php elseif ($_GET['msg'] === 'notenrolled'): ?>
            <div class="alert alert-error">You are not enrolled in this course.</div>
        <?php elseif ($_GET['msg'] === 'error'): ?>
            <div class="alert alert-error">Something went wrong. Please try again.</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Create Group Form (always shown) -->
    <section class="create-group-section">

        <h2>Create Group</h2>

        <form action="create_group_process.php" method="post">

            <div class="create-group-grid">

                <div class="form-group">
                    <label for="newGroupName">Group Name</label>
                    <input type="text" id="newGroupName" name="newGroupName"
                           placeholder="e.g. Team A" required>
                </div>

                <div class="form-group">
                    <label for="courseID">Course</label>
                    <select id="courseID" name="courseID" required>
                        <option value="">-- Select Course --</option>
                        <?php
                        $stmtCourse = mysqli_prepare($conn, "
                            SELECT c.id, c.code, c.name
                            FROM `course_enrollments` ce
                            JOIN `courses` c ON c.id = ce.course_id
                            WHERE ce.student_id = ?
                            ORDER BY c.code
                        ");
                        mysqli_stmt_bind_param($stmtCourse, "i", $studentID);
                        mysqli_stmt_execute($stmtCourse);
                        $rsCourse = mysqli_stmt_get_result($stmtCourse);

                        while ($c = mysqli_fetch_assoc($rsCourse)) {
                            echo '<option value="' . (int)$c['id'] . '">'
                                    . htmlspecialchars($c['code'] . ' - ' . $c['name'])
                                    . '</option>';
                        }
                        mysqli_stmt_close($stmtCourse);
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="projectID">Project</label>
                    <select id="projectID" name="projectID" required>
                        <option value="">-- Select Project --</option>
                        <?php
                        $rs = mysqli_query($conn, "
                            SELECT p.id, p.name, c.code AS course_code
                            FROM `projects` p
                            LEFT JOIN `courses` c ON c.id = p.course_id
                            WHERE p.status <> 'Archived'
                            ORDER BY p.name
                        ");
                        while ($p = mysqli_fetch_assoc($rs)) {
                            echo '<option value="' . (int)$p['id'] . '">'
                                    . htmlspecialchars($p['name'] . ($p['course_code'] ? ' (' . $p['course_code'] . ')' : ''))
                                    . '</option>';
                        }
                        mysqli_free_result($rs);
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="newMaxMembers">Maximum Members</label>
                    <select id="newMaxMembers" name="newMaxMembers" required>
                        <option value="2">2 Members</option>
                        <option value="3">3 Members</option>
                        <option value="4" selected>4 Members</option>
                        <option value="5">5 Members</option>
                        <option value="6">6 Members</option>
                    </select>
                </div>

            </div>

            <button type="submit" class="primary-btn">Create Group</button>

        </form>

    </section>

    <!-- Group / Project Switcher -->
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
                                . ' (' . $g['status'] . ')'
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <script>
            function switchGroup(groupId) {
                window.location.href = 'student_create_group.php?group_id=' + groupId;
            }
        </script>
    <?php endif; ?>

    <!-- Has group → Invite + Current Group + Vote -->
    <?php if ($myGroup): ?>

        <section class="section-grid">

            <!-- Invite -->
            <div class="content-box">

                <h2>Invite Classmate</h2>

                <form action="invite_process.php" method="post" class="invite-form">

                    <input type="hidden" name="groupID" value="<?php echo (int)$myGroup['group_id']; ?>">

                    <div class="invite-form-row two-cols">

                        <div class="form-group">
                            <label for="inviteStudentNo">Student ID</label>
                            <input type="text" id="inviteStudentNo" name="inviteStudentNo"
                                   placeholder="e.g. S15942736"
                                   autocomplete="off"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="inviteStudentName">Student Name</label>
                            <input type="text" id="inviteStudentName" name="inviteStudentName"
                                   placeholder="(auto-filled)"
                                   class="readonly-input"
                                   readonly>
                        </div>

                    </div>

                    <button type="submit" class="invite-btn-main">Invite to Group</button>

                </form>

                <p class="invite-history-title">Invitation History</p>

                <div class="invite-history">

                    <?php if (count($invitations) === 0): ?>
                        <p class="muted">No invitations yet.</p>
                    <?php endif; ?>

                    <?php foreach ($invitations as $inv): ?>
                        <div class="invite-item">
                            <div class="invite-avatar">
                                <?php echo htmlspecialchars(initials($inv['username'])); ?>
                            </div>

                            <div class="invite-info">
                                <h3><?php echo htmlspecialchars($inv['username']); ?></h3>
                                <p>Student ID: <?php echo htmlspecialchars($inv['student_number']); ?></p>
                            </div>

                            <span class="invite-status <?php echo statusClass($inv['status']); ?>">
                                <?php echo htmlspecialchars($inv['status']); ?>
                            </span>

                            <?php if ($inv['status'] === 'Pending'): ?>
                                <form action="cancel_invite_process.php" method="post" style="margin:0;">
                                    <input type="hidden" name="inviteID" value="<?php echo (int)$inv['id']; ?>">
                                    <button type="submit" class="cancel-invite-btn">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                </div>

            </div>

            <!-- Current Group -->
            <div class="content-box">

                <h2>Current Group</h2>

                <div class="group-info">
                    <strong>Group Name:</strong>
                    <?php echo htmlspecialchars($myGroup['group_name']); ?><br>

                    <strong>Course:</strong>
                    <?php
                    echo htmlspecialchars(
                            !empty($myGroup['course_code'])
                                    ? $myGroup['course_code'] . ' - ' . $myGroup['course_name']
                                    : '-'
                    );
                    ?><br>

                    <strong>Project:</strong>
                    <?php echo htmlspecialchars($myGroup['project_name']); ?><br>

                    <strong>Maximum Members:</strong>
                    <?php echo (int)$myGroup['max_members']; ?><br>

                    <strong>Members:</strong>
                    <?php echo count($groupMembers); ?> / <?php echo (int)$myGroup['max_members']; ?><br>

                    <strong>Status:</strong>
                    <span class="status-badge"><?php echo htmlspecialchars($myGroup['status']); ?></span>
                </div>

                <?php foreach ($groupMembers as $m): ?>
                    <div class="member-item">
                        <div>
                            <h3><?php echo htmlspecialchars($m['username']); ?></h3>
                            <p>Student ID: <?php echo htmlspecialchars($m['student_number']); ?></p>
                        </div>

                        <span class="member-status <?php echo $m['role'] === 'leader' ? 'status-leader' : 'status-accepted-member'; ?>">
                            <?php echo $m['role'] === 'leader' ? 'Leader' : 'Member'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php if (count($groupMembers) < (int)$myGroup['max_members']): ?>
                    <button type="button" class="primary-btn" disabled>
                        Lock Team (Need <?php echo (int)$myGroup['max_members']; ?> / <?php echo (int)$myGroup['max_members']; ?>)
                    </button>
                <?php elseif ($myGroup['status'] === 'Recruiting'): ?>
                    <form action="lock_team_process.php" method="post" style="margin-top: 20px;">
                        <input type="hidden" name="groupID" value="<?php echo (int)$myGroup['group_id']; ?>">
                        <button type="submit" class="primary-btn">Lock Team &amp; Start Voting</button>
                    </form>
                <?php endif; ?>

            </div>

        </section>

        <!-- Voting -->
        <?php if ($myGroup['status'] === 'Voting' || $myGroup['status'] === 'Executing' || count($voteResults) > 0): ?>

            <section class="section-grid">

                <div class="content-box">

                    <h2>Vote Group Leader</h2>

                    <?php if ($myGroup['status'] === 'Voting'): ?>
                        <p style="margin-bottom: 18px; color: #666; font-size: 14px;">
                            Each member casts <strong>one anonymous vote</strong>. The member
                            with the most votes becomes the group leader.
                        </p>

                        <form action="vote_leader_process.php" method="post">

                            <input type="hidden" name="groupID" value="<?php echo (int)$myGroup['group_id']; ?>">

                            <?php foreach ($groupMembers as $m): ?>
                                <label class="vote-option">
                                    <input type="radio" name="candidateID"
                                           value="<?php echo (int)$m['student_id']; ?>" required>

                                    <div class="vote-option-info">
                                        <h3><?php echo htmlspecialchars($m['username']); ?></h3>
                                        <p>Student ID: <?php echo htmlspecialchars($m['student_number']); ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>

                            <button type="submit" class="primary-btn" style="margin-top: 10px;">
                                Submit My Vote
                            </button>

                        </form>
                    <?php else: ?>
                        <p class="muted">Voting has ended.</p>
                    <?php endif; ?>

                </div>

                <div class="content-box">

                    <h2>Vote Result</h2>

                    <?php if (count($voteResults) === 0): ?>
                        <p class="muted">No votes yet.</p>
                    <?php else: ?>
                        <?php
                        $totalVotes = 0;
                        foreach ($voteResults as $vr) {
                            $totalVotes += (int)$vr['vote_count'];
                        }
                        ?>
                        <?php foreach ($voteResults as $vr): ?>
                            <?php
                            $pct = $totalVotes > 0 ? round($vr['vote_count'] / $totalVotes * 100) : 0;
                            ?>
                            <div class="result-item">
                                <div class="result-header">
                                    <strong><?php echo htmlspecialchars($vr['username']); ?></strong>
                                    <span><?php echo (int)$vr['vote_count']; ?> votes</span>
                                </div>
                                <div class="result-bar">
                                    <div class="result-value" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>

            </section>

        <?php endif; ?>

    <?php endif; ?>

</main>

<!-- Auto-fill Student Name -->
<script>
    (function () {
        var inputId   = document.getElementById('inviteStudentNo');
        var inputName = document.getElementById('inviteStudentName');
        var timer     = null;

        if (!inputId || !inputName) return;

        inputId.addEventListener('input', function () {
            var no = inputId.value.trim();

            inputName.value = '';
            inputName.style.background = '';
            inputName.style.color = '';

            if (no === '') return;

            clearTimeout(timer);
            timer = setTimeout(function () {

                fetch('get_student_name.php?student_no=' + encodeURIComponent(no))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data && data.name) {
                            inputName.value = data.name;
                            inputName.style.background = '#eaf7ee';
                            inputName.style.color = '#176b2c';
                        } else {
                            inputName.value = 'Not found';
                            inputName.style.background = '#fdeeee';
                            inputName.style.color = '#a21b1b';
                        }
                    })
                    .catch(function () {
                        inputName.value = '';
                    });

            }, 300);
        });
    })();
</script>

</body>
</html>