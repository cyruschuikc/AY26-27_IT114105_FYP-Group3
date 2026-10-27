<?php
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/topic_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
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
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'submitted') {
        $flash = ($activeGroup && $activeGroup['topic_mode'] === 'Self')
            ? 'Project title submitted for teacher approval.'
            : 'Topic proposal submitted for teacher approval.';
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
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f3f5f7; color: #222; font-family: Arial, sans-serif; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 18px; padding: 16px 28px; background: #111317; color: #fff; }
        .brand { display: flex; align-items: center; gap: 11px; font-size: 20px; font-weight: 700; }
        .brand img { width: 42px; height: 42px; object-fit: contain; }
        .nav { display: flex; flex-wrap: wrap; gap: 18px; font-size: 13px; }
        .nav a { color: #d8dde4; text-decoration: none; }
        .nav a:hover, .nav a.active { color: #fff; }
        .main { width: min(940px, 92%); margin: 34px auto 60px; }
        h1 { margin: 0 0 6px; font-size: 28px; }
        .subtitle { margin: 0 0 24px; color: #707780; font-size: 14px; line-height: 1.6; }
        .flash { margin-bottom: 18px; padding: 12px 15px; border: 1px solid #c8d8e8; border-radius: 8px; background: #f4f8fc; color: #38556f; font-size: 13px; }
        .switcher { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; padding: 14px 16px; border: 1px solid #e1e5ea; border-radius: 9px; background: #fff; }
        .switcher label { color: #626a74; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .switcher select { min-width: 320px; padding: 9px 11px; border: 1px solid #d8dde3; border-radius: 7px; background: #fff; font-family: inherit; font-size: 13px; }
        .topic-card { margin-bottom: 18px; padding: 20px; border: 1px solid #e1e5ea; border-radius: 10px; background: #fff; }
        .topic-card h2 { margin: 0 0 6px; font-size: 15px; }
        .topic-card p { margin: 0; color: #6d747d; font-size: 13px; line-height: 1.65; }
        .mode-line { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 13px; padding-bottom: 12px; border-bottom: 1px solid #edf0f3; }
        .mode-line span { color: #8a919a; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        .mode-line strong { color: #313842; font-size: 13px; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
        .status-pending { background: #fff2d9; color: #8a5c00; }
        .status-approved { background: #e7f4eb; color: #27633d; }
        .status-rejected { background: #fdeaea; color: #a52b2b; }
        .status-none { background: #eef1f4; color: #626974; }
        .proposal-title { margin: 4px 0 8px; color: #20252c; font-size: 20px; font-weight: 700; }
        .review-note { margin-top: 13px; padding: 11px 13px; border-left: 3px solid #d07171; background: #fff7f7; color: #7e3b3b; font-size: 12px; line-height: 1.55; }
        .form-row { margin-bottom: 15px; }
        .form-row label { display: block; margin-bottom: 7px; color: #343b45; font-size: 12px; font-weight: 700; }
        .form-row input, .form-row textarea { width: 100%; padding: 10px 11px; border: 1px solid #d8dde3; border-radius: 7px; font-family: inherit; font-size: 13px; }
        .form-row textarea { min-height: 110px; resize: vertical; line-height: 1.55; }
        .topic-option { display: flex; gap: 10px; margin-bottom: 9px; padding: 12px; border: 1px solid #e1e5ea; border-radius: 8px; cursor: pointer; }
        .topic-option:hover { border-color: #aebbc9; background: #fafbfc; }
        .topic-option input { margin-top: 3px; }
        .topic-option strong { display: block; margin-bottom: 4px; color: #2d333b; font-size: 13px; }
        .topic-option small { color: #777f89; font-size: 11.5px; line-height: 1.45; }
        .primary-btn { padding: 10px 16px; border: 0; border-radius: 7px; background: #175196; color: #fff; font-family: inherit; font-size: 12px; font-weight: 700; cursor: pointer; }
        .secondary-btn { display: inline-block; padding: 9px 13px; border: 1px solid #ccd7e3; border-radius: 7px; background: #fff; color: #315e8d; font-size: 12px; font-weight: 700; text-decoration: none; }
        .locked { padding: 12px 14px; border-radius: 8px; background: #f5f6f8; color: #6c737c; font-size: 12px; line-height: 1.55; }
        .history { margin-top: 18px; padding: 18px 20px; border: 1px solid #e1e5ea; border-radius: 10px; background: #fff; }
        .history h2 { margin: 0 0 12px; font-size: 15px; }
        .history-row { display: grid; grid-template-columns: 70px minmax(0, 1fr) auto; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eef0f2; font-size: 12px; }
        .history-row:last-child { border-bottom: 0; }
        .history-row span { color: #858c95; }
        .empty { padding: 40px 20px; border: 1px solid #e1e5ea; border-radius: 10px; background: #fff; color: #777f89; text-align: center; }
        @media (max-width: 700px) {
            .topbar { align-items: flex-start; padding: 14px 18px; }
            .nav { display: none; }
            .switcher select { min-width: 100%; }
            .history-row { grid-template-columns: 1fr; gap: 4px; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand"><img src="TeamBox_logo_black.png" alt="TeamBox">TeamBox</div>
    <nav class="nav">
        <a href="student_index.php">Dashboard</a>
        <a href="student_create_group.php">My Group</a>
        <a href="student_topic.php" class="active"><?= htmlspecialchars($pageHeading) ?></a>
        <a href="student_task.php">Tasks</a>
        <a href="student_files.php">Files</a>
        <a href="logout.php">Log Out</a>
    </nav>
</header>

<main class="main">
    <h1><?= htmlspecialchars($pageHeading) ?></h1>
    <p class="subtitle">View the teacher's topic rules, submit an approval request, and track every proposal version.</p>

    <?php if ($flash !== ''): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if (count($groups) === 0): ?>
        <div class="empty">No active group is available. Create a group and complete the approval process first.</div>
    <?php else: ?>
        <div class="switcher">
            <label for="topicGroup">Project / Team</label>
            <select id="topicGroup" onchange="if(this.value){window.location.href='student_topic.php?group_id='+encodeURIComponent(this.value);}">
                <?php foreach ($groups as $group): ?>
                    <option value="<?= (int) $group['id'] ?>" <?= (int) $group['id'] === (int) $activeGroup['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['project_name'] . ' / ' . $group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <section class="topic-card">
            <div class="mode-line">
                <div>
                    <span>Project</span><br>
                    <strong><?= htmlspecialchars($activeGroup['project_name']) ?></strong>
                </div>
                <div style="text-align:right;">
                    <span>Allocation</span><br>
                    <strong><?= htmlspecialchars(topicModeLabel($activeGroup['topic_mode'])) ?></strong>
                </div>
            </div>

            <?php if ($activeGroup['topic_mode'] === 'Fixed'): ?>
                <?php if ($fixedTopic): ?>
                    <span class="status status-approved">Teacher assigned</span>
                    <div class="proposal-title"><?= htmlspecialchars($fixedTopic['title']) ?></div>
                    <p><?= nl2br(htmlspecialchars((string) $fixedTopic['description'])) ?></p>
                <?php else: ?>
                    <div class="locked">The teacher has not added the fixed topic yet.</div>
                <?php endif; ?>
            <?php elseif ($latestProposal): ?>
                <span class="status status-<?= strtolower($latestProposal['status']) ?>">
                    <?= htmlspecialchars(topicProposalStatusLabel($latestProposal['status'])) ?>
                </span>
                <div class="proposal-title"><?= htmlspecialchars($latestProposal['topic_title']) ?></div>
                <p><?= nl2br(htmlspecialchars((string) $latestProposal['topic_description'])) ?></p>
                <?php if ($latestProposal['status'] === 'Rejected' && !empty($latestProposal['review_note'])): ?>
                    <div class="review-note"><strong>Teacher feedback:</strong> <?= nl2br(htmlspecialchars($latestProposal['review_note'])) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <span class="status status-none">Not submitted</span>
                <div class="proposal-title">No topic submitted yet</div>
                <p><?= $isLeader ? 'Choose or propose a topic below.' : 'Waiting for the elected team leader to submit a topic.' ?></p>
            <?php endif; ?>
        </section>

        <?php if ($canSubmit): ?>
            <section class="topic-card">
                <h2><?= $activeGroup['topic_mode'] === 'Multi' ? 'Choose a topic' : 'Propose a topic' ?></h2>
                <p style="margin-bottom:16px;">
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
                            <label for="topicTitle">Project title</label>
                            <input type="text" id="topicTitle" name="topicTitle" maxlength="180" required>
                        </div>
                        <div class="form-row">
                            <label for="topicDescription">Proposal description</label>
                            <textarea id="topicDescription" name="topicDescription" required
                                      placeholder="Describe the problem, proposed solution, scope and expected outcome."></textarea>
                        </div>
                        <button type="submit" class="primary-btn">Submit for Approval</button>
                    <?php endif; ?>
                </form>
            </section>
        <?php elseif ($activeGroup['topic_mode'] !== 'Fixed'): ?>
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
        <?php endif; ?>

        <?php if (count($proposalHistory) > 0): ?>
            <section class="history">
                <h2><?= htmlspecialchars($historyHeading) ?></h2>
                <?php foreach ($proposalHistory as $history): ?>
                    <div class="history-row">
                        <span>Version <?= (int) $history['version'] ?></span>
                        <strong><?= htmlspecialchars($history['topic_title']) ?></strong>
                        <span class="status status-<?= strtolower($history['status']) ?>">
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
