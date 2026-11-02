<?php
session_start();

$isEmbed = isset($_GET['embed']) && $_GET['embed'] === '1';

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/topic_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

$unreadNotifications = 0;
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM `notifications` WHERE user_id = ? AND is_read = 0");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int) $rc['c'];
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT id FROM `students` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$student) {
    header('Location: logout.php');
    exit;
}
$studentID = (int) $student['id'];

$groups = array();
$stmt = mysqli_prepare($conn, "
    SELECT
        g.id,
        g.name,
        g.status,
        g.leader_id,
        p.id AS project_id,
        p.name AS project_name,
        p.topic_mode,
        p.supervisor_id
    FROM `group_members` gm
    INNER JOIN `groups` g ON g.id = gm.group_id
    INNER JOIN `projects` p ON p.id = g.project_id
    WHERE gm.student_id = ?
      AND g.status IN ('Executing', 'Completed')
    ORDER BY p.name, g.name
");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $groups[] = $row;
}
mysqli_stmt_close($stmt);

$activeGroup = null;
$requestedGroupID = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
foreach ($groups as $group) {
    if ($requestedGroupID === 0 || (int) $group['id'] === $requestedGroupID) {
        $activeGroup = $group;
        break;
    }
}
if (!$activeGroup && count($groups) > 0) {
    $activeGroup = $groups[0];
}

$latestProposal = null;
$proposalHistory = array();
$topics = array();
$fixedTopic = null;
$isLeader = false;
$canSubmit = false;

if ($activeGroup) {
    $latestProposal = latestTeamTopicProposal($conn, (int) $activeGroup['id']);
    $isLeader = ((int) $activeGroup['leader_id'] === $studentID);

    $stmt = mysqli_prepare($conn, "
        SELECT *
        FROM `team_topic_proposals`
        WHERE group_id = ?
        ORDER BY version DESC, id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroup['id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $proposalHistory[] = $row;
    }
    mysqli_stmt_close($stmt);

    $topics = activeProjectTopics($conn, (int) $activeGroup['project_id']);
    if ($activeGroup['topic_mode'] === 'Fixed' && count($topics) > 0) {
        $fixedTopic = $topics[0];
    }

    $latestStatus = $latestProposal ? $latestProposal['status'] : '';
    $canSubmit = (
            $activeGroup['status'] === 'Executing'
            && $isLeader
            && $activeGroup['topic_mode'] !== 'Fixed'
            && !in_array($latestStatus, array('Pending', 'Approved'), true)
    );
}

$flash = '';
$flashType = 'error';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'submitted') {
        $flash = ($activeGroup && $activeGroup['topic_mode'] === 'Self')
                ? 'Project title submitted for teacher approval.'
                : 'Topic proposal submitted for teacher approval.';
        $flashType = 'success';
    } elseif ($_GET['msg'] === 'notleader') {
        $flash = ($activeGroup && $activeGroup['topic_mode'] === 'Self')
                ? 'Only the elected team leader can submit the project title.'
                : 'Only the elected team leader can submit a topic proposal.';
    } elseif ($_GET['msg'] === 'pending') {
        $flash = 'This team already has a topic proposal awaiting approval.';
    } elseif ($_GET['msg'] === 'approved') {
        $flash = 'The current topic is already approved and cannot be resubmitted.';
    } elseif ($_GET['msg'] === 'invalid') {
        $flash = 'Please complete the required topic information.';
    }
}

$pageHeading = ($activeGroup && $activeGroup['topic_mode'] === 'Self')
        ? 'Project Title'
        : 'Team Topic';
$historyHeading = ($activeGroup && $activeGroup['topic_mode'] === 'Self')
        ? 'Project Title History'
        : 'Proposal History';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageHeading) ?> | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        .navbar {
            width: 100%; padding: 15px 30px; background: black;
            display: flex; flex-direction: column; gap: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-top {
            position: relative; width: 100%; min-height: 65px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
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

        .main-content { width: 90%; max-width: 1000px; margin: 40px auto; }

        .page-heading {
            padding: 30px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .alert { padding: 14px 18px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; border-left: 4px solid; }
        .alert-success { background: #eaf7ee; color: #176b2c; border-color: #2e9d4d; }
        .alert-error   { background: #fdeeee; color: #a21b1b; border-color: #d32f2f; }

        .content-box {
            padding: 28px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .content-box h2 {
            padding-bottom: 15px; margin-bottom: 20px;
            border-bottom: 2px solid #eeeeee; font-size: 22px;
        }

        .empty-state {
            padding: 60px 40px; background: white;
            border-radius: 15px; border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .empty-state h2 { font-size: 24px; margin-bottom: 12px; }
        .empty-state p { color: #666; font-size: 15px; line-height: 1.6; max-width: 560px; margin: 0 auto 28px; }
        .empty-state .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 200px; height: 50px; padding: 0 24px;
            background: black; color: white; border: 2px solid black;
            border-radius: 9px; font-size: 16px; font-weight: bold;
            text-decoration: none; cursor: pointer; transition: 0.3s;
        }
        .empty-state .btn:hover { background: #333; }

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

        .topic-meta {
            display: flex; justify-content: space-between; gap: 16px;
            margin-bottom: 18px; padding-bottom: 15px;
            border-bottom: 1px solid #eeeeee; flex-wrap: wrap;
        }
        .topic-meta div span {
            display: block; margin-bottom: 4px;
            color: #8a919a; font-size: 11px;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .topic-meta div strong { color: #313842; font-size: 14px; }

        .status {
            display: inline-block; padding: 5px 11px; margin-bottom: 12px;
            border-radius: 999px; font-size: 11px; font-weight: 700;
        }
        .status-pending  { background: #fff2d9; color: #8a5c00; }
        .status-approved { background: #e7f4eb; color: #27633d; }
        .status-rejected { background: #fdeaea; color: #a52b2b; }
        .status-none     { background: #eef1f4; color: #626974; }

        .proposal-title { margin: 6px 0 10px; color: #20252c; font-size: 21px; font-weight: 700; word-break: break-word; }
        .proposal-desc { color: #6d747d; font-size: 14px; line-height: 1.7; white-space: pre-wrap; word-break: break-word; }

        .review-note {
            margin-top: 14px; padding: 11px 13px;
            border-left: 3px solid #d07171;
            background: #fff7f7; color: #7e3b3b;
            font-size: 13px; line-height: 1.6; border-radius: 4px;
        }

        .form-row { margin-bottom: 18px; }
        .form-row label {
            display: block; margin-bottom: 8px;
            color: #333; font-size: 14px; font-weight: bold;
        }
        .form-row input,
        .form-row textarea {
            width: 100%; padding: 11px 13px;
            background: white; color: #222;
            border: 2px solid #cccccc; border-radius: 8px;
            font-family: inherit; font-size: 14px; outline: none;
            transition: border-color 0.2s;
        }
        .form-row input:focus,
        .form-row textarea:focus { border-color: black; }
        .form-row textarea { min-height: 120px; resize: vertical; line-height: 1.55; }

        .topic-option {
            display: flex; gap: 12px; margin-bottom: 10px; padding: 14px;
            border: 2px solid #e1e5ea; border-radius: 10px;
            cursor: pointer; transition: 0.2s;
        }
        .topic-option:hover { border-color: black; background: #fafbfc; }
        .topic-option input { margin-top: 4px; flex-shrink: 0; }
        .topic-option strong { display: block; margin-bottom: 4px; color: #2d333b; font-size: 14px; }
        .topic-option small { color: #777f89; font-size: 12px; line-height: 1.5; }

        .primary-btn {
            padding: 12px 20px;
            background: black; color: white;
            border: 2px solid black; border-radius: 8px;
            font-size: 14px; font-weight: bold;
            cursor: pointer; transition: 0.3s;
        }
        .primary-btn:hover { background: #333; }

        .locked {
            padding: 14px 16px; border-radius: 8px;
            background: #f5f6f8; color: #6c737c;
            font-size: 13px; line-height: 1.6;
        }

        .history-row {
            display: grid;
            grid-template-columns: 100px minmax(0, 1fr) auto;
            gap: 14px; align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eeeeee;
            font-size: 13px;
        }
        .history-row:last-child { border-bottom: none; }
        .history-row > span:first-child { color: #858c95; font-size: 12px; }
        .history-row strong { color: #2d333b; word-break: break-word; }

        @media (max-width: 700px) {
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
            .history-row { grid-template-columns: 1fr; gap: 4px; }
            .topic-meta { flex-direction: column; gap: 10px; }
            .topic-meta div { text-align: left !important; }
        }

        /* ===== Embed mode (for iframe in my_team.php) ===== */
        body.embed-mode { background: #f5f5f5; }
        body.embed-mode .navbar { display: none !important; }
        body.embed-mode .page-heading { display: none !important; }
        body.embed-mode .main-content {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 20px 20px 40px;
        }
    </style>
</head>
<body class="<?= $isEmbed ? 'embed-mode' : '' ?>">

<nav class="navbar">
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

    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_topic.php" class="nav-link active"><?= htmlspecialchars($pageHeading) ?></a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <section class="page-heading">
        <h1><?= htmlspecialchars($pageHeading) ?></h1>
        <p>
            View the teacher's topic rules, submit an approval request,
            and track every proposal version.
        </p>
    </section>

    <?php if ($flash !== ''): ?>
        <div class="alert <?= $flashType === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <?php if (count($groups) === 0): ?>

        <div class="empty-state">
            <h2>No active group</h2>
            <p>
                Topic management is only available after your team is formed
                and the project has started.
            </p>
            <a href="student_create_group.php" class="btn">Go to Create Group</a>
        </div>

    <?php else: ?>

        <?php if (count($groups) > 1): ?>
            <div class="group-switcher">
                <label for="topicGroup">Switch Project / Team:</label>
                <select id="topicGroup" onchange="if(this.value){window.location.href='student_topic.php?group_id='+encodeURIComponent(this.value)<?= $isEmbed ? "+'&embed=1'" : '' ?>;}">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= (int) $group['id'] ?>" <?= (int) $group['id'] === (int) $activeGroup['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['project_name'] . ' / ' . $group['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <section class="content-box">
            <h2>Current Topic</h2>

            <div class="topic-meta">
                <div>
                    <span>Project</span>
                    <strong><?= htmlspecialchars($activeGroup['project_name']) ?></strong>
                </div>
                <div style="text-align:right;">
                    <span>Allocation</span>
                    <strong><?= htmlspecialchars(topicModeLabel($activeGroup['topic_mode'])) ?></strong>
                </div>
            </div>

            <?php if ($activeGroup['topic_mode'] === 'Fixed'): ?>
                <?php if ($fixedTopic): ?>
                    <span class="status status-approved">Teacher assigned</span>
                    <div class="proposal-title"><?= htmlspecialchars($fixedTopic['title']) ?></div>
                    <p class="proposal-desc"><?= nl2br(htmlspecialchars((string) $fixedTopic['description'])) ?></p>
                <?php else: ?>
                    <div class="locked">The teacher has not added the fixed topic yet.</div>
                <?php endif; ?>

            <?php elseif ($latestProposal): ?>
                <span class="status status-<?= strtolower($latestProposal['status']) ?>">
                    <?= htmlspecialchars(topicProposalStatusLabel($latestProposal['status'])) ?>
                </span>
                <div class="proposal-title"><?= htmlspecialchars($latestProposal['topic_title']) ?></div>
                <p class="proposal-desc"><?= nl2br(htmlspecialchars((string) $latestProposal['topic_description'])) ?></p>

                <?php if ($latestProposal['status'] === 'Rejected' && !empty($latestProposal['review_note'])): ?>
                    <div class="review-note">
                        <strong>Teacher feedback:</strong>
                        <?= nl2br(htmlspecialchars($latestProposal['review_note'])) ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <span class="status status-none">Not submitted</span>
                <div class="proposal-title">No topic submitted yet</div>
                <p class="proposal-desc">
                    <?= $isLeader ? 'Choose or propose a topic below.' : 'Waiting for the elected team leader to submit a topic.' ?>
                </p>
            <?php endif; ?>
        </section>

        <?php if ($canSubmit): ?>
            <section class="content-box">
                <h2><?= $activeGroup['topic_mode'] === 'Multi' ? 'Choose a Topic' : 'Propose a Topic' ?></h2>

                <p style="margin-bottom:18px; color:#666; font-size:14px; line-height:1.6;">
                    <?= $activeGroup['topic_mode'] === 'Multi'
                            ? 'Select one teacher-provided option. Multiple teams may choose the same topic.'
                            : 'Submit an original topic for your Final Year Project.' ?>
                </p>

                <form action="student_topic_process.php" method="post">
                    <input type="hidden" name="groupID" value="<?= (int) $activeGroup['id'] ?>">

                    <?php if ($activeGroup['topic_mode'] === 'Multi'): ?>
                        <?php if (count($topics) === 0): ?>
                            <div class="locked">No topic options are available yet.</div>
                        <?php endif; ?>

                        <?php foreach ($topics as $topic): ?>
                            <label class="topic-option">
                                <input type="radio" name="topicID" value="<?= (int) $topic['id'] ?>" required>
                                <span>
                                    <strong><?= htmlspecialchars($topic['title']) ?></strong>
                                    <small><?= htmlspecialchars((string) $topic['description']) ?></small>
                                </span>
                            </label>
                        <?php endforeach; ?>

                        <?php if (count($topics) > 0): ?>
                            <button type="submit" class="primary-btn">Submit for Approval</button>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="form-row">
                            <label for="topicTitle">Project Title</label>
                            <input type="text" id="topicTitle" name="topicTitle" maxlength="180" required
                                   placeholder="e.g. TeamBox — Final Year Project Workspace">
                        </div>
                        <div class="form-row">
                            <label for="topicDescription">Proposal Description</label>
                            <textarea id="topicDescription" name="topicDescription" required
                                      placeholder="Describe the problem, proposed solution, scope and expected outcome."></textarea>
                        </div>
                        <button type="submit" class="primary-btn">Submit for Approval</button>
                    <?php endif; ?>
                </form>
            </section>

        <?php elseif ($activeGroup['topic_mode'] !== 'Fixed'): ?>
            <section class="content-box">
                <h2>Submission Status</h2>
                <div class="locked">
                    <?php if (!$isLeader): ?>
                        Only the elected team leader can submit a topic proposal.
                    <?php elseif ($latestProposal && $latestProposal['status'] === 'Pending'): ?>
                        Your latest topic version is awaiting teacher approval.
                    <?php elseif ($latestProposal && $latestProposal['status'] === 'Approved'): ?>
                        Your topic is approved and locked.
                    <?php else: ?>
                        Topic submission is not available until the team is executing.
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (count($proposalHistory) > 0): ?>
            <section class="content-box">
                <h2><?= htmlspecialchars($historyHeading) ?></h2>
                <?php foreach ($proposalHistory as $history): ?>
                    <div class="history-row">
                        <span>Version <?= (int) $history['version'] ?></span>
                        <strong><?= htmlspecialchars($history['topic_title']) ?></strong>
                        <span class="status status-<?= strtolower($history['status']) ?>" style="margin:0;">
                            <?= htmlspecialchars(topicProposalStatusLabel($history['status'])) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

    <?php endif; ?>

</main>

</body>
</html>