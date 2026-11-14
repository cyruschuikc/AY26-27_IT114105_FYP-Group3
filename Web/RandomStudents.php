<?php
$active = 'projects';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$teacherId = 0;

$stmt = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);
if ($row) {
    $teacherId = (int) $row['id'];
}

if ($teacherId <= 0) {
    header('Location: login.php');
    exit;
}

$projectId = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
if ($projectId <= 0) {
    header('Location: teacher_projects.php');
    exit;
}

$project = null;
$stmt = mysqli_prepare($conn, "
    SELECT p.*, cl.class_code, c.name AS course_name
    FROM `projects` p
    LEFT JOIN `classes` cl ON cl.id = p.class_id
    LEFT JOIN `courses` c  ON c.id = p.course_id
    WHERE p.id = ? AND p.supervisor_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $projectId, $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$project) {
    header('Location: teacher_projects.php');
    exit;
}

$courseId = (int) $project['course_id'];
$maxMembers = (int) $project['default_max_members'];
if ($maxMembers < 2) {
    $maxMembers = 4;
}
$allowCrossClass = (int) $project['allow_cross_class'];

// =========================================================
// 项目相关班别：主班别 + 跨班班别
// =========================================================
$classIds = array();
if (!empty($project['class_id'])) {
    $classIds[] = (int) $project['class_id'];
}
if ($allowCrossClass === 1) {
    $stmt = mysqli_prepare($conn, "SELECT class_id FROM `project_cross_classes` WHERE project_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($crossRow = mysqli_fetch_assoc($rs)) {
        $classIds[] = (int) $crossRow['class_id'];
    }
    mysqli_stmt_close($stmt);
}
$classIds = array_values(array_unique($classIds));

// =========================================================
// 查询函数
// =========================================================
function randomStudentsFetchUnassigned($conn, $classIds, $courseId) {
    if (count($classIds) === 0) {
        return array();
    }

    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $types = str_repeat('i', count($classIds)) . 'i';
    $params = $classIds;
    $params[] = $courseId;

    $sql = "
        SELECT
            s.id,
            s.student_number,
            u.username AS name,
            c.class_code
        FROM `students` s
        INNER JOIN `users`   u ON u.id = s.user_id
        INNER JOIN `classes` c ON c.id = s.class_id
        WHERE s.class_id IN ($placeholders)
          AND NOT EXISTS (
              SELECT 1
              FROM `group_members` gm
              INNER JOIN `groups`   g  ON g.id = gm.group_id
              INNER JOIN `projects` gp ON gp.id = g.project_id
              WHERE gm.student_id = s.id
                AND gp.course_id = ?
          )
        ORDER BY c.class_code, u.username
    ";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $students = array();
    while ($student = mysqli_fetch_assoc($rs)) {
        $students[] = $student;
    }
    mysqli_stmt_close($stmt);
    return $students;
}

function randomStudentsFetchGroups($conn, $projectId) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            g.id,
            g.name,
            g.leader_id,
            g.max_members,
            (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count
        FROM `groups` g
        WHERE g.project_id = ?
        ORDER BY g.id
    ");
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $groups = array();
    while ($group = mysqli_fetch_assoc($rs)) {
        $groups[] = $group;
    }
    mysqli_stmt_close($stmt);
    return $groups;
}

// =========================================================
// POST：执行随机分配
// =========================================================
if (isset($_POST['action']) && $_POST['action'] === 'randomize') {
    $unassigned = randomStudentsFetchUnassigned($conn, $classIds, $courseId);
    $groups = randomStudentsFetchGroups($conn, $projectId);

    if (count($classIds) === 0) {
        header('Location: RandomStudents.php?id=' . $projectId . '&msg=noclass');
        exit;
    }
    if (count($unassigned) === 0) {
        header('Location: RandomStudents.php?id=' . $projectId . '&msg=none');
        exit;
    }

    // 计算新组的起始编号
    $nextTeamNumber = 1;
    foreach ($groups as $group) {
        if (preg_match('/(\d+)\s*$/', (string) $group['name'], $m)) {
            $num = (int) $m[1];
            if ($num >= $nextTeamNumber) {
                $nextTeamNumber = $num + 1;
            }
        }
    }

    shuffle($unassigned);

    $buckets = array();
    foreach ($groups as $group) {
        $buckets[] = array(
            'id'             => (int) $group['id'],
            'name'           => (string) $group['name'],
            'max'            => (int) $group['max_members'],
            'original_count' => (int) $group['member_count'],
            'leader_id'      => (int) $group['leader_id'],
            'members'        => array(),
        );
    }
    shuffle($buckets);

    foreach ($unassigned as $student) {
        $candidates = array();
        foreach ($buckets as $index => $bucket) {
            $current = $bucket['original_count'] + count($bucket['members']);
            if ($current < $bucket['max']) {
                $candidates[] = $index;
            }
        }

        if (count($candidates) === 0) {
            $name = sprintf('Team %02d', $nextTeamNumber);
            $nextTeamNumber++;
            $buckets[] = array(
                'id'             => 0,
                'name'           => $name,
                'max'            => $maxMembers,
                'original_count' => 0,
                'leader_id'      => 0,
                'members'        => array(),
            );
            $candidates[] = count($buckets) - 1;
        }

        $pick = $candidates[array_rand($candidates)];
        $buckets[$pick]['members'][] = $student;
    }

    $assignedCount = 0;
    $newGroupCount = 0;

    mysqli_begin_transaction($conn);
    try {
        $insertGroup = mysqli_prepare($conn, "
            INSERT INTO `groups`
                (project_id, course_id, name, leader_id, creator_id, max_members, status, formed_at)
            VALUES (?, ?, ?, ?, ?, ?, 'Executing', NOW())
        ");
        $insertMember = mysqli_prepare($conn, "
            INSERT INTO `group_members` (group_id, student_id, role)
            VALUES (?, ?, ?)
        ");
        $updateLeader = mysqli_prepare($conn, "
            UPDATE `groups` SET leader_id = ? WHERE id = ?
        ");
        $insertActivity = mysqli_prepare($conn, "
            INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
            VALUES (?, ?, ?, 'joined_team', ?)
        ");

        foreach ($buckets as $bucket) {
            if (count($bucket['members']) === 0) {
                continue;
            }

            $groupId = $bucket['id'];
            $isNewGroup = ($groupId === 0);
            $needLeader = (!$isNewGroup && $bucket['original_count'] === 0 && empty($bucket['leader_id']));

            if ($isNewGroup) {
                $leaderId = (int) $bucket['members'][0]['id'];
                mysqli_stmt_bind_param(
                    $insertGroup,
                    "iisiii",
                    $projectId,
                    $courseId,
                    $bucket['name'],
                    $leaderId,
                    $leaderId,
                    $maxMembers
                );
                mysqli_stmt_execute($insertGroup);
                $groupId = (int) mysqli_insert_id($conn);
                $newGroupCount++;
            }

            foreach ($bucket['members'] as $memberIndex => $student) {
                $role = 'member';
                if ($isNewGroup && $memberIndex === 0) {
                    $role = 'leader';
                } elseif ($needLeader && $memberIndex === 0) {
                    $role = 'leader';
                }

                mysqli_stmt_bind_param($insertMember, "iis", $groupId, (int) $student['id'], $role);
                mysqli_stmt_execute($insertMember);

                $detail = htmlspecialchars($student['class_code']) . ' · ' . $bucket['name'];
                mysqli_stmt_bind_param(
                    $insertActivity,
                    "iiis",
                    $projectId,
                    $groupId,
                    (int) $student['id'],
                    $detail
                );
                mysqli_stmt_execute($insertActivity);

                $assignedCount++;
            }

            if ($needLeader) {
                $leaderId = (int) $bucket['members'][0]['id'];
                mysqli_stmt_bind_param($updateLeader, "ii", $leaderId, $groupId);
                mysqli_stmt_execute($updateLeader);
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header('Location: RandomStudents.php?id=' . $projectId . '&msg=error');
        exit;
    }

    header('Location: RandomStudents.php?id=' . $projectId
        . '&done=1&assigned=' . $assignedCount . '&newgroups=' . $newGroupCount);
    exit;
}

// =========================================================
// POST：老师手动指定学生进组
// =========================================================
if (isset($_POST['action']) && $_POST['action'] === 'assign') {
    $assignStudentId = (int) (isset($_POST['student_id']) ? $_POST['student_id'] : 0);
    $assignGroupId   = (int) (isset($_POST['group_id']) ? $_POST['group_id'] : 0);

    $unassignedNow = randomStudentsFetchUnassigned($conn, $classIds, $courseId);
    $studentInfo = null;
    foreach ($unassignedNow as $u) {
        if ((int) $u['id'] === $assignStudentId) {
            $studentInfo = $u;
            break;
        }
    }

    if ($studentInfo === null) {
        header('Location: RandomStudents.php?id=' . $projectId . '&msg=assignfail');
        exit;
    }

    $targetGroupId = 0;
    $targetGroupName = '';
    $targetMemberCount = 0;
    $targetLeaderId = 0;
    $targetMax = $maxMembers;

    if ($assignGroupId > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT g.id, g.name, g.max_members, g.leader_id,
                   (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count
            FROM `groups` g
            WHERE g.id = ? AND g.project_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $assignGroupId, $projectId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $target = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        if (!$target) {
            header('Location: RandomStudents.php?id=' . $projectId . '&msg=assignfail');
            exit;
        }
        $targetGroupId = (int) $target['id'];
        $targetGroupName = (string) $target['name'];
        $targetMax = (int) $target['max_members'];
        $targetMemberCount = (int) $target['member_count'];
        $targetLeaderId = (int) $target['leader_id'];

        if ($targetMemberCount >= $targetMax) {
            header('Location: RandomStudents.php?id=' . $projectId . '&msg=assignfull');
            exit;
        }
    } else {
        $groupsNow = randomStudentsFetchGroups($conn, $projectId);
        $nextTeamNumber = 1;
        foreach ($groupsNow as $g) {
            if (preg_match('/(\d+)\s*$/', (string) $g['name'], $m)) {
                $num = (int) $m[1];
                if ($num >= $nextTeamNumber) {
                    $nextTeamNumber = $num + 1;
                }
            }
        }
        $targetGroupName = sprintf('Team %02d', $nextTeamNumber);
    }

    mysqli_begin_transaction($conn);
    try {
        $isLeader = false;

        if ($assignGroupId === 0) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO `groups`
                    (project_id, course_id, name, leader_id, creator_id, max_members, status, formed_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Executing', NOW())
            ");
            mysqli_stmt_bind_param(
                $stmt, "iisiii",
                $projectId, $courseId, $targetGroupName,
                $assignStudentId, $assignStudentId, $maxMembers
            );
            mysqli_stmt_execute($stmt);
            $targetGroupId = (int) mysqli_insert_id($conn);
            $isLeader = true;
        } else {
            $isLeader = ($targetMemberCount === 0 && $targetLeaderId === 0);
        }

        $role = $isLeader ? 'leader' : 'member';
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `group_members` (group_id, student_id, role)
            VALUES (?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "iis", $targetGroupId, $assignStudentId, $role);
        mysqli_stmt_execute($stmt);

        if ($isLeader && $assignGroupId > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE `groups` SET leader_id = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $assignStudentId, $targetGroupId);
            mysqli_stmt_execute($stmt);
        }

        $detail = htmlspecialchars((string) $studentInfo['class_code']) . ' · ' . $targetGroupName;
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `activities` (project_id, group_id, actor_id, action, detail)
            VALUES (?, ?, ?, 'joined_team', ?)
        ");
        mysqli_stmt_bind_param($stmt, "iiis", $projectId, $targetGroupId, $assignStudentId, $detail);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header('Location: RandomStudents.php?id=' . $projectId . '&msg=error');
        exit;
    }

    header('Location: RandomStudents.php?id=' . $projectId
        . '&assignedone=1&group=' . urlencode($targetGroupName));
    exit;
}

// =========================================================
// GET：展示预览
// =========================================================
$unassigned = randomStudentsFetchUnassigned($conn, $classIds, $courseId);
$groups = randomStudentsFetchGroups($conn, $projectId);

$spareSeats = 0;
foreach ($groups as $group) {
    $spareSeats += max(0, (int) $group['max_members'] - (int) $group['member_count']);
}
$overflow = max(0, count($unassigned) - $spareSeats);
$newGroupsNeeded = $overflow > 0 ? (int) ceil($overflow / $maxMembers) : 0;

// 班别展示
$scopeLabels = array();
if (count($classIds) > 0) {
    $placeholders = implode(',', array_fill(0, count($classIds), '?'));
    $types = str_repeat('i', count($classIds));
    $stmt = mysqli_prepare($conn, "SELECT class_code FROM `classes` WHERE id IN ($placeholders) ORDER BY class_code");
    mysqli_stmt_bind_param($stmt, $types, ...$classIds);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($classRow = mysqli_fetch_assoc($rs)) {
        $scopeLabels[] = $classRow['class_code'];
    }
    mysqli_stmt_close($stmt);
}

$flashMessage = '';
$flashType = 'success';
if (isset($_GET['done']) && $_GET['done'] === '1') {
    $assignedDone = (int) (isset($_GET['assigned']) ? $_GET['assigned'] : 0);
    $newGroupsDone = (int) (isset($_GET['newgroups']) ? $_GET['newgroups'] : 0);
    $flashMessage = $assignedDone . ' student' . ($assignedDone === 1 ? '' : 's')
        . ' randomly assigned' . ($newGroupsDone > 0 ? ' · ' . $newGroupsDone . ' new group' . ($newGroupsDone === 1 ? '' : 's') . ' created' : '') . '.';
} elseif (isset($_GET['assignedone']) && $_GET['assignedone'] === '1') {
    $assignedGroup = isset($_GET['group']) ? (string) $_GET['group'] : 'group';
    $flashMessage = 'Student assigned to ' . $assignedGroup . '.';
} elseif (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'none') {
        $flashMessage = 'No unassigned students in this project\'s classes.';
        $flashType = 'warning';
    } elseif ($_GET['msg'] === 'noclass') {
        $flashMessage = 'This project has no related classes configured.';
        $flashType = 'warning';
    } elseif ($_GET['msg'] === 'error') {
        $flashMessage = 'Something went wrong while assigning students. Please try again.';
        $flashType = 'error';
    } elseif ($_GET['msg'] === 'assignfull') {
        $flashMessage = 'That group is already full. Please pick another group.';
        $flashType = 'warning';
    } elseif ($_GET['msg'] === 'assignfail') {
        $flashMessage = 'Could not assign the student. Please try again.';
        $flashType = 'error';
    }
}

$teamModeLabel = 'Free teams';
if ($project['team_mode'] === 'Random') {
    $teamModeLabel = 'Random assignment';
} elseif ($project['team_mode'] === 'Teacher_Assigned') {
    $teamModeLabel = 'Teacher assigned';
}
$categoryLabel = trim((string) $project['category']) !== '' ? $project['category'] : 'Uncategorised';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Assignment | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_projects.css?v=<?= filemtime('assets/css/teacher_projects.css') ?>">
    <link rel="stylesheet" href="assets/css/random_students.css?v=<?= filemtime('assets/css/random_students.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <div class="page-head-simple rs-head">
            <div>
                <div class="rs-breadcrumb">
                    <a href="teacher_projects.php">Projects</a>
                    <span class="rs-sep">/</span>
                    <span>Random assignment</span>
                </div>
                <h1>Random Assignment</h1>
                <div class="rs-meta">
                    <span class="class-tag"><?php echo htmlspecialchars($project['class_code'] ?: 'No class'); ?></span>
                    <span class="meta-separator">·</span>
                    <span><?php echo htmlspecialchars($categoryLabel); ?></span>
                    <span class="meta-separator">·</span>
                    <span><?php echo htmlspecialchars($teamModeLabel); ?></span>
                </div>
            </div>
            <a href="teacher_projects.php" class="back-link">Back to Projects</a>
        </div>

        <?php if ($flashMessage !== ''): ?>
            <div class="tb-alert tb-alert--<?php echo $flashType === 'success' ? 'success' : ($flashType === 'error' ? 'error' : 'warning'); ?>">
                <span><?php echo htmlspecialchars($flashMessage); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($project['is_draft'])): ?>
            <div class="tb-alert tb-alert--warning">
                <span><strong>Draft project.</strong> Students cannot see it until it is published.</span>
            </div>
        <?php endif; ?>

        <?php if (count($classIds) === 0): ?>
            <div class="empty-panel">
                <strong>No related classes</strong>
                <span>Assign a primary class (or enable cross-class) for this project before randomising.</span>
            </div>
        <?php else: ?>

            <div class="projects-kpi rs-kpi">
                <div class="kpi-item kpi-item--highlight">
                    <span class="kpi-label">Unassigned Students</span>
                    <strong class="kpi-value"><?php echo count($unassigned); ?></strong>
                </div>
                <div class="kpi-item">
                    <span class="kpi-label">Existing Groups</span>
                    <strong class="kpi-value"><?php echo count($groups); ?></strong>
                </div>
                <div class="kpi-item">
                    <span class="kpi-label">Spare Seats</span>
                    <strong class="kpi-value"><?php echo $spareSeats; ?></strong>
                </div>
                <div class="kpi-item">
                    <span class="kpi-label">New Groups Needed</span>
                    <strong class="kpi-value"><?php echo $newGroupsNeeded; ?></strong>
                </div>
            </div>

            <div class="rs-panel">
                <div class="rs-panel-head">
                    <div>
                        <h2>Unassigned students</h2>
                        <p>Students in <?php echo htmlspecialchars(implode(', ', $scopeLabels) ?: 'related classes'); ?> who are not in a group yet</p>
                    </div>
                    <span class="rs-count-badge"><?php echo count($unassigned); ?></span>
                </div>

                <?php if (count($unassigned) === 0): ?>
                    <div class="rs-empty">
                        <span class="rs-empty-icon">✓</span>
                        <span>Everyone is already in a group.</span>
                    </div>
                <?php else: ?>
                    <div class="rs-student-list">
                        <?php foreach ($unassigned as $student): ?>
                            <?php
                            $studentName = trim((string) $student['name']);
                            $studentParts = preg_split('/\s+/', $studentName);
                            $studentInitials = count($studentParts) >= 2
                                    ? strtoupper(substr($studentParts[0], 0, 1) . substr($studentParts[count($studentParts) - 1], 0, 1))
                                    : strtoupper(substr($studentName, 0, 2));
                            ?>
                            <div class="rs-student">
                                <span class="rs-avatar"><?php echo htmlspecialchars($studentInitials); ?></span>
                                <div class="rs-student-main">
                                    <strong><?php echo htmlspecialchars($studentName); ?></strong>
                                    <span><?php echo htmlspecialchars($student['student_number']); ?></span>
                                </div>
                                <span class="rs-chip"><?php echo htmlspecialchars($student['class_code']); ?></span>
                                <form method="post"
                                      action="RandomStudents.php?id=<?php echo (int) $projectId; ?>"
                                      class="rs-assign-form"
                                      onsubmit="return confirm('Assign this student to the selected group?');">
                                    <input type="hidden" name="action" value="assign">
                                    <input type="hidden" name="student_id" value="<?php echo (int) $student['id']; ?>">
                                    <select name="group_id" class="rs-assign-select"
                                            onchange="if (this.value !== '') { this.form.submit(); } else { this.selectedIndex = 0; }">
                                        <option value="">Assign to…</option>
                                        <option value="0">＋ New group</option>
                                        <?php foreach ($groups as $group): ?>
                                            <?php
                                            $groupMemberCount = (int) $group['member_count'];
                                            $groupMax = (int) $group['max_members'];
                                            $groupFull = $groupMemberCount >= $groupMax;
                                            ?>
                                            <option value="<?php echo (int) $group['id']; ?>" <?php echo $groupFull ? 'disabled' : ''; ?>>
                                                <?php echo htmlspecialchars($group['name']); ?>
                                                (<?php echo $groupMemberCount; ?>/<?php echo $groupMax; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rs-panel">
                <div class="rs-panel-head">
                    <div>
                        <h2>Existing groups</h2>
                        <p>Remaining seats that new students will be shuffled into first</p>
                    </div>
                    <span class="rs-count-badge"><?php echo count($groups); ?></span>
                </div>

                <?php if (count($groups) === 0): ?>
                    <div class="rs-empty">
                        <span class="rs-empty-icon">✦</span>
                        <span>No groups yet — new groups will be created automatically.</span>
                    </div>
                <?php else: ?>
                    <div class="rs-group-list">
                        <?php foreach ($groups as $group): ?>
                            <?php
                            $memberCount = (int) $group['member_count'];
                            $groupMax = (int) $group['max_members'];
                            $fill = $groupMax > 0 ? min(100, (int) round(($memberCount / $groupMax) * 100)) : 0;
                            $isFull = $memberCount >= $groupMax;
                            ?>
                            <div class="rs-group">
                                <span class="rs-group-name"><?php echo htmlspecialchars($group['name']); ?></span>
                                <span class="rs-capacity">
                                    <span class="<?php echo $isFull ? 'is-full' : ''; ?>" style="width: <?php echo $fill; ?>%;"></span>
                                </span>
                                <span class="rs-group-count"><?php echo $memberCount; ?> / <?php echo $groupMax; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="post"
                  action="RandomStudents.php?id=<?php echo (int) $projectId; ?>"
                  class="rs-actions"
                  onsubmit="return confirm('Randomly assign <?php echo count($unassigned); ?> student<?php echo count($unassigned) === 1 ? '' : 's'; ?> now? This cannot be undone.');">
                <input type="hidden" name="action" value="randomize">
                <span class="rs-actions-icon">⇄</span>
                <div class="rs-actions-copy">
                    <h2>Ready to randomise?</h2>
                    <p>
                        Students are shuffled into existing groups first, respecting each group's
                        max members. Remaining students go into new groups.
                    </p>
                </div>
                <button type="submit" class="rs-submit" <?php echo count($unassigned) === 0 ? 'disabled' : ''; ?>>
                    Randomly Assign Now
                </button>
            </form>

        <?php endif; ?>

    </div>
</main>

</body>
</html>
