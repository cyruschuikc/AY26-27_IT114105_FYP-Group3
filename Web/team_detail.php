<?php
$active = 'teams';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */

$teamId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];
$teacherId = 0;
$stmtTeacher = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtTeacher, "i", $userID);
mysqli_stmt_execute($stmtTeacher);
$rsTeacher = mysqli_stmt_get_result($stmtTeacher);
$rowTeacher = mysqli_fetch_assoc($rsTeacher);
mysqli_stmt_close($stmtTeacher);
if ($rowTeacher) {
    $teacherId = (int) $rowTeacher['id'];
}

if ($teacherId <= 0) {
    header('Location: teacher_login.php');
    exit;
}


// =========================================================
// 1. Team + Project 信息
// =========================================================
$team = null;

$sql = "
    SELECT
        g.id,
        g.name            AS team_name,
        g.status,
        g.max_members,
        g.formed_at,
        g.leader_id,
        p.id              AS project_id,
        p.name            AS project_name,
        p.category        AS project_category,
        p.period_start,
        p.period_end,
        p.supervisor_id,
        c.code            AS course_code,
        c.name            AS course_name,
        (SELECT COUNT(*) FROM `group_requests` r
            WHERE r.project_id = g.project_id
              AND r.group_name = g.name
              AND r.status = 'Pending') AS pending_requests,
        tu.username       AS project_supervisor
    FROM `Groups` g
    LEFT JOIN `Projects` p ON p.id = g.project_id
    LEFT JOIN `Teachers` t ON t.id = p.supervisor_id
    LEFT JOIN `Users`    tu ON tu.id = t.user_id
    LEFT JOIN `Courses`  c ON c.id = p.course_id
    WHERE g.id = ? AND p.supervisor_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $teamId, $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$team = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$team) {
    // 找不到 team → 回列表
    header('Location: teacher_teams.php');
    exit;
}

$isPending = ((int) $team['pending_requests'] > 0);

if ($isPending) {
    $statusLabel = 'Pending Approval';
    $statusClass = 'pending';
} elseif ($team['status'] === 'Waiting_Send') {
    $statusLabel = 'Waiting to Submit';
    $statusClass = 'waiting';
} else {
    $statusLabel = $team['status'];
    $statusClass = strtolower($team['status']);
}

$canMessage = (!$isPending
    && in_array($team['status'], array('Voting', 'Executing'), true));

$currentPhase = null;
$stmt = mysqli_prepare($conn, "
    SELECT name, start_date, end_date, status
    FROM `Milestones`
    WHERE project_id = ?
      AND status <> 'Completed'
    ORDER BY
        CASE
            WHEN CURDATE() BETWEEN start_date AND end_date THEN 0
            WHEN start_date > CURDATE() THEN 1
            ELSE 2
        END,
        start_date ASC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $team['project_id']);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$currentPhase = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

$projectMilestones = array();
$stmt = mysqli_prepare($conn, "
    SELECT name, start_date, end_date, status, progress
    FROM `Milestones`
    WHERE project_id = ?
    ORDER BY start_date ASC
");
mysqli_stmt_bind_param($stmt, "i", $team['project_id']);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($milestone = mysqli_fetch_assoc($rs)) {
    $projectMilestones[] = $milestone;
}
mysqli_stmt_close($stmt);

$projectDaysLeft = null;
$projectDeadlineLabel = '';
if (!empty($team['period_end'])) {
    $projectDaysLeft = (int) floor(
        (strtotime($team['period_end']) - strtotime(date('Y-m-d'))) / 86400
    );
    if ($projectDaysLeft > 0) {
        $projectDeadlineLabel = $projectDaysLeft . ' days left';
    } elseif ($projectDaysLeft === 0) {
        $projectDeadlineLabel = 'Due today';
    } else {
        $projectDeadlineLabel = 'Overdue ' . abs($projectDaysLeft) . 'd';
    }
}

$phaseTiming = '';
if ($currentPhase) {
    $todayTs = strtotime(date('Y-m-d'));
    $startTs = strtotime($currentPhase['start_date']);
    $endTs   = strtotime($currentPhase['end_date']);

    if ($todayTs < $startTs) {
        $phaseTiming = 'starts ' . date('d M', $startTs);
    } elseif ($todayTs > $endTs) {
        $phaseTiming = 'overdue since ' . date('d M', $endTs);
    } else {
        $phaseTiming = 'ends ' . date('d M', $endTs);
    }
}


// =========================================================
// 2. Members
// =========================================================
$members = array();

$sql = "
    SELECT
        s.id            AS student_id,
        s.student_number,
        s.major,
        s.grade_level,
        c.class_code,
        u.username      AS name,
        gm.role,
        (SELECT COUNT(*) FROM `Tasks` t
            WHERE t.assigned_to = s.id
              AND t.status = 'Completed') AS done_tasks,
        (SELECT COUNT(*) FROM `Tasks` t2
            WHERE t2.assigned_to = s.id) AS total_tasks,
        (SELECT COUNT(DISTINCT CONCAT(
                LOWER(TRIM(f.file_name)), '|', COALESCE(f.task_id, 0)
            ))
            FROM `Files` f
            WHERE f.uploader_id = s.id
              AND f.task_id IS NOT NULL) AS deliverables,
        GREATEST(
            COALESCE((
                SELECT MAX(f2.uploaded_at)
                FROM `Files` f2
                WHERE f2.uploader_id = s.id AND f2.task_id IS NOT NULL
            ), '1970-01-01 00:00:00'),
            COALESCE((
                SELECT MAX(t3.completed_at)
                FROM `Tasks` t3
                WHERE t3.assigned_to = s.id AND t3.completed_at IS NOT NULL
            ), '1970-01-01 00:00:00')
        ) AS last_active
    FROM `Group_Members` gm
    INNER JOIN `Students` s ON s.id = gm.student_id
    INNER JOIN `Users`    u ON u.id = s.user_id
    LEFT JOIN `classes`   c ON c.id = s.class_id
    WHERE gm.group_id = ?
    ORDER BY gm.role ASC, s.id
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $members[] = $row;
}
mysqli_stmt_close($stmt);

$leaderName = '';
foreach ($members as $member) {
    if ($member['role'] === 'leader') {
        $leaderName = $member['name'];
        break;
    }
}


// =========================================================
// 3. Progress
// =========================================================

// Overall：完成任务 / 总任务
$doneTasks = 0;
$rs = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `Tasks` WHERE group_id = " . $teamId . " AND status = 'Completed'");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $doneTasks = (int)$rc['c']; mysqli_free_result($rs); }

$totalTasks = 0;
$rs = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `Tasks` WHERE group_id = " . $teamId);
if ($rs) { $rc = mysqli_fetch_assoc($rs); $totalTasks = (int)$rc['c']; mysqli_free_result($rs); }

$overallPercent = $totalTasks > 0 ? (int)round($doneTasks / $totalTasks * 100) : 0;

$overdueTaskCount = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c
    FROM `Tasks`
    WHERE group_id = ?
      AND status <> 'Completed'
      AND due_date < CURDATE()
");
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
$overdueTaskCount = (int) $row['c'];
mysqli_stmt_close($stmt);

// Roster Locked：有 leader 就认为锁了
$rosterLocked = !empty($team['leader_id']) ? $team['formed_at'] : null;

// Leader Elected：从 Activities 里找 'elected' 记录
$leaderElectedAt = null;
$stmt = mysqli_prepare($conn, "
    SELECT created_at FROM `Activities`
    WHERE group_id = ? AND action = 'elected'
    ORDER BY created_at DESC LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
if ($row) { $leaderElectedAt = $row['created_at']; }
mysqli_stmt_close($stmt);

// Last Activity
$lastActivity = null;
$stmt = mysqli_prepare($conn, "
    SELECT MAX(created_at) AS last_at FROM `Activities` WHERE group_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($rs);
if ($row && !empty($row['last_at'])) { $lastActivity = $row['last_at']; }
mysqli_stmt_close($stmt);

// =========================================================
// 4. Tasks
// =========================================================
$tasks = array();

$sql = "
    SELECT
        t.id,
        t.title,
        t.priority,
        t.status,
        t.due_date,
        t.assigned_to,
        (SELECT COUNT(*) FROM `Files` f WHERE f.task_id = t.id) AS attachment_count,
        au.username AS owner_name
    FROM `Tasks` t
    LEFT JOIN `Students` s ON s.id = t.assigned_to
    LEFT JOIN `Users`    au ON au.id = s.user_id
    WHERE t.group_id = ?
    ORDER BY
        CASE t.status
            WHEN 'In Progress' THEN 1
            WHEN 'Pending'     THEN 2
            WHEN 'Completed'   THEN 3
            ELSE 4
        END,
        t.due_date
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $tasks[] = $row;
}
mysqli_stmt_close($stmt);

$openTasks = array();
$completedTasks = array();
foreach ($tasks as $task) {
    if ($task['status'] === 'Completed') {
        $completedTasks[] = $task;
    } else {
        $openTasks[] = $task;
    }
}

usort($completedTasks, function ($a, $b) {
    $ad = $a['due_date'] ? strtotime($a['due_date']) : 0;
    $bd = $b['due_date'] ? strtotime($b['due_date']) : 0;
    return $bd <=> $ad;
});


// =========================================================
// 5. Files
// =========================================================
$files = array();

$sql = "
    SELECT
        f.id,
        f.file_name,
        f.file_size,
        f.uploaded_at,
        f.status,
        f.task_id,
        f.uploader_id,
        t.title AS task_title,
        u.username AS owner_name
    FROM `Files` f
    INNER JOIN `Tasks` t ON t.id = f.task_id
    LEFT JOIN `Students` s ON s.id = f.uploader_id
    LEFT JOIN `Users`    u ON u.id = s.user_id
    WHERE f.group_id = ?
      AND f.task_id IS NOT NULL
    ORDER BY f.uploaded_at DESC, f.id DESC
    LIMIT 30
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $files[] = $row;
}
mysqli_stmt_close($stmt);

$attachmentGroups = array();
foreach ($files as $f) {
    $key = strtolower(trim($f['file_name'])) . '|' . (int) $f['uploader_id'] . '|' . (int) $f['task_id'];
    if (!isset($attachmentGroups[$key])) {
        $f['version_count'] = 1;
        $attachmentGroups[$key] = $f;
    } else {
        $attachmentGroups[$key]['version_count']++;
    }
}
$attachmentGroups = array_values($attachmentGroups);


// =========================================================
// 6. Recent Activity
// =========================================================
$activities = array();

$sql = "
    SELECT
        a.id,
        a.actor_id,
        a.group_id,
        a.project_id,
        a.detail,
        a.action,
        a.created_at,
        u.username AS actor_name,
        g.name     AS team_name,
        p.name     AS project_name
    FROM `Activities` a
    LEFT JOIN `Users` u ON u.id = a.actor_id
    LEFT JOIN `Groups` g ON g.id = a.group_id
    LEFT JOIN `Projects` p ON p.id = a.project_id
    WHERE a.group_id = ?
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT 300
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $activities[] = $row;
}
mysqli_stmt_close($stmt);

// Files and tasks used to resolve activity objects.
$activityFileMap = array();
$stmt = mysqli_prepare($conn, "
    SELECT id, file_name, uploaded_at, task_id
    FROM `Files`
    WHERE group_id = ?
    ORDER BY uploaded_at DESC, id DESC
");
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($file = mysqli_fetch_assoc($rs)) {
    $key = strtolower(trim($file['file_name']));
    $activityFileMap[$key][] = $file;
}
mysqli_stmt_close($stmt);

$activityTaskMap = array();
$stmt = mysqli_prepare($conn, "SELECT id, title FROM `Tasks` WHERE group_id = ?");
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($task = mysqli_fetch_assoc($rs)) {
    $activityTaskMap[strtolower(trim($task['title']))] = array(
        'id'    => (int) $task['id'],
        'title' => $task['title'],
    );
}
mysqli_stmt_close($stmt);

if (!function_exists('parseTeamActivity')) {
    function parseTeamActivity($activity, $fileMap, $taskMap)
    {
        $detail = (string) $activity['detail'];
        $action = strtolower((string) $activity['action']);
        $createdTs = strtotime($activity['created_at']);

        $fileNames = array();
        $taskTitle = null;
        $status = null;

        if (preg_match('/uploaded\s+(.+?)\s+to\s+task\s+"/i', $detail, $m)) {
            $fileNames[] = trim($m[1], "\" '");
        }
        if (preg_match('/task file\s+"([^"]+)"/i', $detail, $m)) {
            $fileNames[] = trim($m[1]);
        }
        if (preg_match('/uploaded\s+shared file\s+"([^"]+)"/i', $detail, $m)) {
            $fileNames[] = trim($m[1]);
        }
        if (preg_match('/marked file\s+(.+?)\s+as\s+(accepted|rejected)/i', $detail, $m)) {
            $fileNames[] = trim($m[1], "\" '");
            $status = ucfirst(strtolower($m[2]));
        }
        if (count($fileNames) === 0 && preg_match('/^uploaded\s+(.+)$/i', $detail, $m)) {
            $fileNames[] = trim($m[1], "\" '");
        }
        if (count($fileNames) === 0 && preg_match('/file\s+"([^"]+)"/i', $detail, $m)) {
            $fileNames[] = trim($m[1]);
        }
        if (count($fileNames) === 0) {
            foreach ($fileMap as $rows) {
                if (!empty($rows[0]['file_name'])
                    && stripos($detail, $rows[0]['file_name']) !== false) {
                    $fileNames[] = $rows[0]['file_name'];
                    break;
                }
            }
        }

        if (preg_match('/task\s+"([^"]+)"/i', $detail, $m)) {
            $taskTitle = $m[1];
        } else {
            foreach ($taskMap as $taskData) {
                if ($taskData['title'] !== ''
                    && stripos($detail, $taskData['title']) !== false) {
                    $taskTitle = $taskData['title'];
                    break;
                }
            }
        }

        $file = null;
        foreach ($fileNames as $name) {
            $key = strtolower(trim($name));
            $candidates = isset($fileMap[$key]) ? $fileMap[$key] : null;

            if ($candidates === null) {
                $base = strtolower(pathinfo($name, PATHINFO_FILENAME));
                foreach ($fileMap as $rows) {
                    if (!empty($rows[0]['file_name'])
                        && strtolower(pathinfo($rows[0]['file_name'], PATHINFO_FILENAME)) === $base) {
                        $candidates = $rows;
                        break;
                    }
                }
            }

            if ($candidates === null) {
                continue;
            }

            $best = null;
            $bestDiff = PHP_INT_MAX;
            foreach ($candidates as $candidate) {
                $diff = abs(strtotime($candidate['uploaded_at']) - $createdTs);
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $best = $candidate;
                }
            }
            if ($best) {
                $file = $best;
                break;
            }
        }

        $taskId = null;
        if ($taskTitle !== null) {
            $taskKey = strtolower(trim($taskTitle));
            if (isset($taskMap[$taskKey])) {
                $taskId = (int) $taskMap[$taskKey]['id'];
            }
        }

        if ($action === 'deleted' || stripos($detail, 'deleted ') === 0) {
            $category = 'cleanup';
        } elseif ($status !== null || stripos($detail, 'marked file') !== false) {
            $category = 'reviews';
        } elseif (count($fileNames) > 0 || $action === 'uploaded') {
            $category = 'files';
        } elseif ($taskTitle !== null || preg_match('/\btask\b/i', $detail)) {
            $category = 'tasks';
        } else {
            $category = 'team';
        }

        return array(
            'raw'         => $activity,
            'category'    => $category,
            'file'        => $file,
            'file_name'   => $file ? $file['file_name'] : ($fileNames[0] ?? ''),
            'task_id'     => $taskId,
            'task_title'  => $taskTitle,
            'status'      => $status,
            'actor_id'    => (int) $activity['actor_id'],
            'actor_name'  => $activity['actor_name'] ?: 'Unknown',
            'created_at'  => $activity['created_at'],
            'group_id'    => (int) $activity['group_id'],
            'project_id'  => (int) $activity['project_id'],
            'team_name'   => $activity['team_name'] ?: '',
            'project_name'=> $activity['project_name'] ?: '',
        );
    }
}

if (!function_exists('mergeActivityItems')) {
    function mergeActivityItems($items)
    {
        $merged = array();
        foreach ($items as $item) {
            $lastIndex = count($merged) - 1;
            $isUpload  = ($item['category'] === 'files'
                && strtolower((string) $item['raw']['action']) === 'uploaded');

            if ($isUpload && $lastIndex >= 0
                && $merged[$lastIndex]['category'] === 'files'
                && strtolower((string) $merged[$lastIndex]['raw']['action']) === 'uploaded'
                && $merged[$lastIndex]['actor_id'] === $item['actor_id']) {
                $merged[$lastIndex]['uploads'][] = $item;
            } else {
                $item['uploads'] = array($item);
                $merged[] = $item;
            }
        }
        return $merged;
    }
}

$parsedActivities = array();
$cleanupActivities = array();

foreach ($activities as $activity) {
    $parsed = parseTeamActivity($activity, $activityFileMap, $activityTaskMap);
    if ($parsed['category'] === 'cleanup') {
        $cleanupActivities[] = $parsed;
    } else {
        $parsedActivities[] = $parsed;
    }
}

$activityType = isset($_GET['atype']) ? trim($_GET['atype']) : 'all';
$validActivityTypes = array('all', 'files', 'tasks', 'reviews', 'team');
if (!in_array($activityType, $validActivityTypes, true)) {
    $activityType = 'all';
}

$categories = array('files', 'tasks', 'reviews', 'team');
$categoryItems = array('files' => array(), 'tasks' => array(), 'reviews' => array(), 'team' => array());
foreach ($parsedActivities as $item) {
    $categoryItems[$item['category']][] = $item;
}

$activityCounts = array('all' => count(mergeActivityItems($parsedActivities)));
foreach ($categories as $category) {
    $activityCounts[$category] = count(mergeActivityItems($categoryItems[$category]));
}

$mergedActivities = mergeActivityItems($parsedActivities);

if (!function_exists('taskDueInfo')) {
    function taskDueInfo($dueDate, $status)
    {
        if (empty($dueDate)) {
            return array('date' => '—', 'label' => '', 'class' => '');
        }

        $days = (int) round(
            (strtotime($dueDate) - strtotime(date('Y-m-d'))) / 86400
        );

        $label = '';
        $class = '';

        if ($status !== 'Completed') {
            if ($days < 0) {
                $label = 'Overdue ' . abs($days) . 'd';
                $class = 'overdue';
            } elseif ($days === 0) {
                $label = 'Today';
                $class = 'today';
            } elseif ($days === 1) {
                $label = 'Tomorrow';
                $class = 'soon';
            } else {
                $label = 'In ' . $days . 'd';
                $class = 'soon';
            }
        }

        return array(
            'date'  => date('d M Y', strtotime($dueDate)),
            'label' => $label,
            'class' => $class,
        );
    }
}

$memberPalette = array('#dbeafe', '#e9d5ff', '#fce7f3', '#d1fae5', '#fef3c7');

if (!function_exists('memberInitials')) {
    function memberInitials($name)
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr((string) $name, 0, 2));
    }
}

if (!function_exists('memberAvatarBg')) {
    function memberAvatarBg($name, $palette)
    {
        return $palette[abs(crc32((string) $name)) % count($palette)];
    }
}

$selectedMemberId = isset($_GET['member']) ? (int) $_GET['member'] : 0;
$selectedMember   = null;

foreach ($members as $m) {
    if ((int) $m['student_id'] === $selectedMemberId) {
        $selectedMember = $m;
        break;
    }
}

if (!$selectedMember && count($members) > 0) {
    foreach ($members as $m) {
        if ($m['role'] === 'leader') {
            $selectedMember = $m;
            break;
        }
    }
    if (!$selectedMember) {
        $selectedMember = $members[0];
    }
    $selectedMemberId = (int) $selectedMember['student_id'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($team['team_name']); ?> | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261020d">

    <style>
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 34px;
            gap: 20px;
        }

        .page-head-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-label {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-pending    { background: #fdf3e2; color: #9a6a13; }
        .status-waiting    { background: #fdf3e2; color: #9a6a13; }
        .status-recruiting { background: #eef2f7; color: #4b5058; }
        .status-voting     { background: #f1ecfb; color: #6a4fb3; }
        .status-executing  { background: #e8f0fa; color: #175196; }
        .status-completed  { background: #e8f6ee; color: #256b3f; }
        .status-unviewed   { background: #fdf3e2; color: #9a6a13; }
        .status-accepted   { background: #e8f6ee; color: #256b3f; }
        .status-rejected   { background: #fdecec; color: #a61b1b; }

        .btn-secondary {
            display: inline-block;
            padding: 7px 12px;
            border: 1px solid #c9d8ea;
            border-radius: 7px;
            background: #fff;
            color: #175196;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-secondary:hover { background: #f4f8fd; }

        .team-notice {
            margin: -16px 0 26px;
            padding: 11px 15px;
            border: 1px solid #f0d5a8;
            border-radius: 8px;
            background: #fdf7ea;
            color: #8a5a10;
            font-size: 12px;
            line-height: 1.55;
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

        .team-metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 14px;
        }
        .metric-card {
            padding: 14px 16px;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
        }
        .metric-label {
            color: #8b8f98;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .metric-value {
            margin-top: 5px;
            color: #2c2f36;
            font-size: 22px;
            font-weight: 600;
        }
        .metric-card.danger .metric-value { color: #d32f2f; }
        .metric-bar {
            height: 6px;
            margin-top: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: #eef0f3;
        }
        .metric-bar span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: #175196;
        }

        .team-meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 9px 26px;
            margin-bottom: 30px;
            padding: 12px 16px;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
            color: #6b6f78;
            font-size: 11.5px;
        }
        .team-meta-strip strong {
            margin-right: 4px;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .task-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: #8b8f98;
            font-size: 11.5px;
        }
        .task-toolbar strong { color: #2c2f36; }
        .task-toolbar .danger,
        .task-toolbar .danger strong { color: #d32f2f; }
        .task-dot { color: #c9ccd1; }

        .task-card { overflow: hidden; }
        .task-head-row,
        .task-row {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) 110px 60px 115px;
            gap: 14px;
            align-items: center;
        }
        .task-head-row {
            padding: 9px 16px;
            border-bottom: 1px solid #eef0f2;
            background: #f7f9fc;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .task-row {
            padding: 11px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 12px;
        }
        .task-row:last-child { border-bottom: none; }
        .task-row.is-overdue {
            padding-left: 14px;
            border-left: 2px solid #d9534f;
            background: #fdf2f2;
        }
        .task-name {
            overflow: hidden;
            color: #2c2f36;
            font-size: 13px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .task-owner {
            margin-top: 2px;
            overflow: hidden;
            color: #9aa0a8;
            font-size: 10.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .task-due-date { color: #6b6f78; font-size: 11px; }
        .task-due-label {
            display: inline-block;
            margin-top: 2px;
            font-size: 9.5px;
            font-weight: 600;
        }
        .task-due-label.overdue { color: #d32f2f; }
        .task-due-label.today { color: #9a6a13; }
        .task-due-label.soon { color: #8b8f98; }
        .task-files a {
            color: #175196;
            font-size: 11px;
            text-decoration: none;
            white-space: nowrap;
        }
        .task-files a:hover { text-decoration: underline; }

        .task-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }
        .task-status-pill {
            padding: 3px 8px;
            border-radius: 999px;
            background: #f4f6f8;
            color: #5b6068;
        }
        .task-status-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9aa0a8;
        }
        .task-status-pill.in-progress::before     { background: #6488b5; }
        .task-status-pill.pending::before         { background: #b08a3e; }
        .task-status-pill.completed::before       { background: #4f8066; }
        .task-status-pill.waiting-confirm::before { background: #7d6aa8; }

        .completed-tasks {
            margin-top: 10px;
            overflow: hidden;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
        }
        .completed-tasks summary {
            display: flex;
            align-items: center;
            padding: 11px 16px;
            color: #6b6f78;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            list-style: none;
        }
        .completed-tasks summary::-webkit-details-marker { display: none; }
        .completed-tasks summary::after {
            content: "+";
            margin-left: auto;
            color: #9aa0a8;
            font-size: 14px;
        }
        .completed-tasks[open] summary::after { content: "−"; }
        .completed-tasks summary span {
            margin-left: 6px;
            padding: 1px 6px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #8b8f98;
            font-size: 9px;
        }
        .completed-head,
        .completed-row {
            display: grid;
            grid-template-columns: minmax(220px, 1.6fr) minmax(140px, 1fr) 100px 50px;
            gap: 12px;
            align-items: center;
        }
        .completed-head {
            padding: 8px 16px;
            border-top: 1px solid #eef0f2;
            border-bottom: 1px solid #eef0f2;
            background: #f7f9fc;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .completed-row {
            padding: 10px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 11.5px;
        }
        .completed-row:last-child { border-bottom: none; }
        .completed-task-name {
            overflow: hidden;
            color: #2c2f36;
            font-weight: 500;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .completed-files a {
            color: #175196;
            text-decoration: none;
            white-space: nowrap;
        }
        .completed-files a:hover { text-decoration: underline; }

        .attachment-card { overflow: hidden; }
        .attachment-head,
        .attachment-row {
            display: grid;
            grid-template-columns: minmax(180px, 1.2fr) minmax(180px, 1.2fr) 100px 80px;
            gap: 14px;
            align-items: center;
        }
        .attachment-head {
            padding: 8px 16px;
            border-bottom: 1px solid #eef0f2;
            background: #f7f9fc;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .attachment-row {
            padding: 10px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 11.5px;
        }
        .attachment-row:last-child { border-bottom: none; }
        .attachment-file {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .attachment-file a {
            overflow: hidden;
            color: #175196;
            font-weight: 500;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .attachment-file a:hover { text-decoration: underline; }
        .attachment-version {
            flex-shrink: 0;
            padding: 1px 6px;
            border-radius: 999px;
            background: #eef2f7;
            color: #4b5058;
            font-size: 9px;
            font-weight: 700;
        }
        .attachment-task {
            overflow: hidden;
            color: #6b6f78;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .attachment-status {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #5b6068;
            font-size: 10.5px;
        }
        .attachment-status i {
            width: 6px;
            height: 6px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #9aa0a8;
        }
        .attachment-status .dot-unviewed { background: #b08a3e; }
        .attachment-status .dot-accepted { background: #4f8066; }
        .attachment-status .dot-rejected { background: #c05a5a; }
        .attachment-time {
            color: #9aa0a8;
            font-size: 10.5px;
        }
        .attachment-empty {
            padding: 24px 16px;
            color: #8b8f98;
            font-size: 12px;
            text-align: center;
        }

        .activity-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }
        .activity-filter {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border: 1px solid #e2e4e8;
            border-radius: 999px;
            background: #fff;
            color: #6b6f78;
            font-family: inherit;
            font-size: 10.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .activity-filter span { color: #9aa0a8; font-size: 9.5px; }
        .activity-filter:hover { border-color: #b9cfe9; color: #175196; }
        .activity-filter.active {
            background: #175196;
            border-color: #175196;
            color: #fff;
        }
        .activity-filter.active span { color: #cfe0f5; }

        .activity-card { overflow: hidden; }
        .activity-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr) 90px;
            gap: 10px;
            align-items: flex-start;
            padding: 12px 16px;
            border-bottom: 1px solid #f2f3f5;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-item[hidden] { display: none; }
        .activity-avatar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #175196;
            font-size: 9px;
            font-weight: 700;
        }
        .activity-main { min-width: 0; }
        .activity-line {
            color: #4b5058;
            font-size: 12px;
            line-height: 1.5;
        }
        .activity-line strong { color: #2c2f36; }
        .activity-line a { color: #175196; text-decoration: none; }
        .activity-line a:hover { text-decoration: underline; }
        .activity-meta {
            margin-top: 3px;
            color: #9aa0a8;
            font-size: 10px;
        }
        .activity-meta a { color: #6b6f78; text-decoration: none; }
        .activity-meta a:hover { color: #175196; text-decoration: underline; }
        .activity-time {
            color: #9aa0a8;
            font-size: 10px;
            text-align: right;
            white-space: nowrap;
        }
        .activity-uploads {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 12px;
            margin-top: 5px;
        }
        .activity-uploads a,
        .activity-uploads span {
            color: #6b6f78;
            font-size: 10.5px;
            text-decoration: none;
        }
        .activity-uploads a:hover { color: #175196; text-decoration: underline; }
        .activity-review {
            display: inline-block;
            margin-left: 5px;
            padding: 1px 6px;
            border-radius: 4px;
            background: #f4f6f8;
            color: #5b6068;
            font-size: 9px;
            font-weight: 600;
        }
        .activity-review.rejected { color: #b02a2a; }
        .activity-show-more {
            display: block;
            width: 100%;
            margin-top: 10px;
            padding: 9px 12px;
            border: 1px solid #e2e4e8;
            border-radius: 8px;
            background: #fff;
            color: #175196;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
        }
        .activity-show-more:hover { background: #f4f8fd; }
        .activity-empty {
            padding: 26px 16px;
            color: #8b8f98;
            font-size: 12px;
            text-align: center;
        }

        .system-cleanup {
            margin-top: 16px;
            overflow: hidden;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
        }
        .system-cleanup summary {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #6b6f78;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            list-style: none;
        }
        .system-cleanup summary::-webkit-details-marker { display: none; }
        .system-cleanup summary::after {
            content: "+";
            margin-left: auto;
            color: #9aa0a8;
            font-size: 14px;
        }
        .system-cleanup[open] summary::after { content: "−"; }
        .system-cleanup summary span {
            margin-left: 6px;
            padding: 1px 6px;
            border-radius: 999px;
            background: #f2f4f7;
            color: #8b8f98;
            font-size: 9px;
        }
        .cleanup-list { border-top: 1px solid #eef0f2; }
        .cleanup-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 16px;
            border-bottom: 1px solid #f4f5f7;
            color: #8b8f98;
            font-size: 10.5px;
        }
        .cleanup-item:last-child { border-bottom: none; }
        .cleanup-item strong { color: #6b6f78; }

        .overview-pair {
            display: grid;
            grid-template-columns: 38% minmax(0, 1fr);
            grid-template-areas:
                "project overview"
                "members members";
            gap: 22px;
            align-items: stretch;
            margin-bottom: 30px;
        }
        .overview-pair > section {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .overview-pair > section:nth-child(1) { grid-area: project; }
        .overview-pair > section:nth-child(2) {
            grid-area: members;
            display: block;
        }
        .overview-pair > section:nth-child(3) { grid-area: overview; }

        .project-card {
            flex: 1;
            margin-bottom: 0;
            padding: 16px 18px;
        }
        .project-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }
        .project-title {
            color: #2c2f36;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
        }
        .project-title:hover { color: #175196; }
        .project-subtitle {
            margin-top: 3px;
            color: #8b8f98;
            font-size: 10.5px;
            line-height: 1.5;
        }
        .project-open {
            flex-shrink: 0;
            color: #175196;
            font-size: 10px;
            font-weight: 600;
            text-decoration: none;
        }
        .project-open:hover { text-decoration: underline; }
        .project-facts { display: flex; flex-direction: column; }
        .project-fact {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 0;
            border-bottom: 1px solid #f2f3f5;
            font-size: 11px;
        }
        .project-fact:last-child { border-bottom: none; }
        .project-fact > span { flex-shrink: 0; color: #9aa0a8; }
        .project-fact strong {
            color: #2c2f36;
            font-weight: 500;
            line-height: 1.4;
            text-align: right;
        }
        .project-fact small {
            display: block;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 400;
        }

        .team-overview-card {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-bottom: 0;
        }
        .overview-metrics {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 14px;
        }
        .overview-metric {
            padding: 10px 12px;
            border: 1px solid #eef0f2;
            border-radius: 8px;
        }
        .overview-metric .metric-value {
            margin-top: 4px;
            font-size: 19px;
        }
        .overview-metric.danger .metric-value { color: #d32f2f; }
        .team-overview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 16px;
            padding: 10px 14px;
            border-top: 1px solid #eef0f2;
            color: #6b6f78;
            font-size: 9.5px;
        }
        .team-overview-meta strong {
            margin-right: 3px;
            color: #9aa0a8;
            font-size: 8.5px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .milestone-card {
            margin-bottom: 30px;
            overflow: hidden;
        }
        .milestone-head,
        .milestone-row {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) 190px 100px 110px;
            gap: 12px;
            align-items: center;
        }
        .milestone-head {
            padding: 8px 16px;
            border-bottom: 1px solid #eef0f2;
            background: #f7f9fc;
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .milestone-row {
            padding: 10px 16px;
            border-bottom: 1px solid #f2f3f5;
            font-size: 11.5px;
        }
        .milestone-row:last-child { border-bottom: none; }
        .milestone-name { color: #2c2f36; font-weight: 500; }
        .milestone-dates { color: #6b6f78; font-size: 10.5px; }
        .milestone-progress {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #9aa0a8;
            font-size: 10px;
        }
        .milestone-bar {
            width: 50px;
            height: 5px;
            overflow: hidden;
            border-radius: 999px;
            background: #eef0f3;
        }
        .milestone-bar i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: #175196;
        }
        .milestone-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #f4f6f8;
            color: #5b6068;
            font-size: 9.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .milestone-status::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #9aa0a8;
        }
        .milestone-status.completed::before { background: #4f8066; }
        .milestone-status.in-progress::before { background: #6488b5; }
        .milestone-empty {
            padding: 24px 16px;
            color: #8b8f98;
            font-size: 12px;
            text-align: center;
        }

        .pending-lock {
            margin: 4px 0 30px;
            padding: 18px 20px;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fafbfc;
            color: #6b6f78;
        }
        .pending-lock strong {
            display: block;
            margin-bottom: 3px;
            color: #2c2f36;
            font-size: 12px;
        }
        .pending-lock p {
            font-size: 11.5px;
            line-height: 1.5;
        }

        .member-note {
            margin: -5px 0 10px;
            color: #9aa0a8;
            font-size: 10.5px;
        }
        .members-card { margin-bottom: 0; overflow: hidden; }
        .members-layout {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .member-switcher {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 10px;
            overflow: visible;
            border-right: none;
            border-bottom: 1px solid #eef0f2;
            background: #fbfbfc;
        }
        .member-switch {
            width: 78px;
            flex: 0 0 78px;
            padding: 8px 4px 7px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }
        .member-switch:hover { border-color: #b9cfe9; }
        .member-switch.active {
            border-color: #175196;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(23, 81, 150, 0.12);
        }
        .member-switch-avatar {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #175196;
            font-size: 10px;
            font-weight: 700;
        }
        .member-switch-name {
            width: 100%;
            overflow: hidden;
            color: #6b6f78;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .member-switch-role {
            color: #9a6a13;
            font-size: 8px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .member-detail { width: 100%; min-width: 0; }
        .member-panel { padding: 16px 18px; }
        .member-panel[hidden] { display: none; }
        .member-panel-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .member-avatar-lg {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #175196;
            font-size: 12px;
            font-weight: 700;
        }
        .member-panel-head h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 7px;
            color: #2c2f36;
            font-size: 15px;
            font-weight: 600;
        }
        .member-role {
            padding: 2px 6px;
            border-radius: 4px;
            background: #eef2f7;
            color: #8b8f98;
            font-size: 8.5px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .member-panel-head p {
            margin-top: 2px;
            color: #9aa0a8;
            font-size: 10.5px;
        }
        .member-metrics {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 12px;
        }
        .member-metric:first-child { grid-column: auto; }
        .member-metric {
            padding: 11px 13px;
            border: 1px solid #eef0f2;
            border-radius: 8px;
        }
        .member-metric > span {
            color: #9aa0a8;
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .member-metric strong {
            display: block;
            margin-top: 4px;
            color: #2c2f36;
            font-size: 17px;
            font-weight: 600;
        }
        .member-bar {
            height: 5px;
            margin-top: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #eef0f3;
        }
        .member-bar i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: #175196;
        }

        @media (max-width: 900px) {
            .team-metrics { grid-template-columns: repeat(2, 1fr); }
            .overview-pair {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "project"
                    "overview"
                    "members";
            }
            .members-layout { display: flex; }
            .member-switcher {
                flex-direction: row;
                flex-wrap: wrap;
                overflow: visible;
                border-right: none;
                border-bottom: 1px solid #eef0f2;
                padding: 8px;
            }
            .member-switch {
                width: 72px;
                flex: 0 0 72px;
            }
        }

        @media (max-width: 600px) {
            .team-metrics { grid-template-columns: 1fr; }
            .activity-item { grid-template-columns: 32px minmax(0, 1fr); }
            .activity-time { grid-column: 2; text-align: left; }
            .cleanup-item { flex-direction: column; gap: 2px; }
        }

        @media (max-width: 760px) {
            .task-head-row { display: none; }
            .task-row {
                grid-template-columns: 1fr auto;
                gap: 8px 12px;
            }
            .task-main { grid-column: 1 / -1; }
            .attachment-head { display: none; }
            .attachment-row {
                grid-template-columns: 1fr auto;
                gap: 4px 12px;
            }
            .attachment-file { grid-column: 1 / -1; }
            .completed-head { display: none; }
            .completed-row {
                grid-template-columns: 1fr auto;
                gap: 4px 12px;
            }
            .completed-task-name { grid-column: 1 / -1; }
            .milestone-head { display: none; }
            .milestone-row {
                grid-template-columns: 1fr auto;
                gap: 4px 12px;
            }
            .milestone-name { grid-column: 1 / -1; }
        }

        @media (max-width: 700px) {
            .page-head { flex-direction: column; gap: 12px; }
            .page-head-actions { justify-content: flex-start; }
            .member-metrics { grid-template-columns: 1fr; }
            .member-switch { width: 72px; }
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
                    Leader: <?php echo htmlspecialchars($leaderName ?: '—'); ?>
                    · <?php echo count($members); ?> / <?php echo (int) $team['max_members']; ?> members
                </div>
            </div>
            <div class="page-head-actions">
                <span class="status-label status-<?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($statusLabel); ?>
                </span>
                <?php if ($isPending): ?>
                    <a href="teacher_review.php#requests" class="btn-secondary">Review Request</a>
                <?php elseif ($canMessage): ?>
                    <a href="teacher_messages.php?group_id=<?php echo (int) $team['id']; ?>"
                       class="btn-secondary">Message Team</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isPending): ?>
            <div class="team-notice">
                This team is waiting for your approval. Group chat becomes available after the request is accepted.
            </div>
        <?php endif; ?>

        <?php
        $courseLine = trim(
            (!empty($team['course_code']) ? $team['course_code'] . ' · ' : '')
            . (!empty($team['course_name']) ? $team['course_name'] : '')
        );
        ?>
        <div class="overview-pair">
            <section>
                <h2>Project</h2>
                <div class="card project-card">
                    <div class="project-head">
                        <div>
                            <a class="project-title" href="project_detail.php?id=<?php echo (int) $team['project_id']; ?>">
                                <?php echo htmlspecialchars($team['project_name']); ?>
                            </a>
                            <div class="project-subtitle">
                                <?php echo htmlspecialchars($team['project_category']); ?>
                                <?php if ($courseLine !== ''): ?>
                                    · <?php echo htmlspecialchars($courseLine); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a class="project-open" href="project_detail.php?id=<?php echo (int) $team['project_id']; ?>">
                            Open
                        </a>
                    </div>

                    <div class="project-facts">
                        <div class="project-fact">
                            <span>Supervisor</span>
                            <strong><?php echo htmlspecialchars($team['project_supervisor'] ?: '—'); ?></strong>
                        </div>
                        <div class="project-fact">
                            <span>Period</span>
                            <strong>
                                <?php echo date('M Y', strtotime($team['period_start'])); ?>
                                – <?php echo date('M Y', strtotime($team['period_end'])); ?>
                            </strong>
                        </div>
                        <div class="project-fact">
                            <span>Deadline</span>
                            <strong>
                                <?php echo date('d M Y', strtotime($team['period_end'])); ?>
                                <?php if ($projectDeadlineLabel !== ''): ?>
                                    <small><?php echo htmlspecialchars($projectDeadlineLabel); ?></small>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <div class="project-fact">
                            <span>Current phase</span>
                            <strong>
                                <?php echo $currentPhase ? htmlspecialchars($currentPhase['name']) : '—'; ?>
                                <?php if ($phaseTiming !== ''): ?>
                                    <small><?php echo htmlspecialchars($phaseTiming); ?></small>
                                <?php endif; ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2>Members</h2>
                <p class="member-note">Counts are context, not a score.</p>

        <?php if (count($members) === 0): ?>
            <div class="card attachment-empty">No members yet.</div>
        <?php else: ?>
            <div class="card members-card">
                <div class="members-layout">
            <div class="member-switcher">
                <?php foreach ($members as $m): ?>
                    <?php
                    $memberId   = (int) $m['student_id'];
                    $isActive   = ($memberId === $selectedMemberId);
                    $nameParts  = preg_split('/\s+/', trim($m['name']));
                    $shortName  = $nameParts[0];
                    $avatarBg   = memberAvatarBg($m['name'], $memberPalette);
                    ?>
                    <button type="button"
                            class="member-switch <?php echo $isActive ? 'active' : ''; ?>"
                            data-member="<?php echo $memberId; ?>"
                            aria-pressed="<?php echo $isActive ? 'true' : 'false'; ?>">
                        <span class="member-switch-avatar" style="background: <?php echo $avatarBg; ?>;">
                            <?php echo htmlspecialchars(memberInitials($m['name'])); ?>
                        </span>
                        <span class="member-switch-name" title="<?php echo htmlspecialchars($m['name']); ?>">
                            <?php echo htmlspecialchars($shortName); ?>
                        </span>
                        <?php if ($m['role'] === 'leader'): ?>
                            <span class="member-switch-role">Leader</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="member-detail">
                <?php foreach ($members as $m): ?>
                    <?php
                    $memberId  = (int) $m['student_id'];
                    $isActive  = ($memberId === $selectedMemberId);
                    $done      = (int) $m['done_tasks'];
                    $total     = (int) $m['total_tasks'];
                    $pct       = $total > 0 ? (int) round($done / $total * 100) : 0;
                    $lastAt    = (string) $m['last_active'];
                    $lastLabel = ($lastAt === '' || strpos($lastAt, '1970-') === 0)
                        ? '—'
                        : timeAgo($lastAt);
                    ?>
                    <div class="member-panel"
                         data-member-panel="<?php echo $memberId; ?>"
                         <?php echo $isActive ? '' : 'hidden'; ?>>
                        <div class="member-panel-head">
                            <div class="member-avatar-lg"
                                 style="background: <?php echo memberAvatarBg($m['name'], $memberPalette); ?>;">
                                <?php echo htmlspecialchars(memberInitials($m['name'])); ?>
                            </div>
                            <div>
                                <h3>
                                    <?php echo htmlspecialchars($m['name']); ?>
                                    <?php if ($m['role'] === 'leader'): ?>
                                        <span class="member-role">Leader</span>
                                    <?php endif; ?>
                                </h3>
                                <p>
                                    <?php echo htmlspecialchars($m['student_number']); ?>
        · <?php echo htmlspecialchars(formatClass($m['major'], $m['grade_level'], $m['class_code'] ?? null)); ?>
                                </p>
                            </div>
                        </div>

                        <div class="member-metrics">
                            <div class="member-metric">
                                <span>Tasks Completed</span>
                                <strong><?php echo $done; ?> / <?php echo $total; ?></strong>
                                <div class="member-bar">
                                    <i style="width: <?php echo $pct; ?>%;"></i>
                                </div>
                            </div>
                            <div class="member-metric">
                                <span>Deliverables</span>
                                <strong><?php echo (int) $m['deliverables']; ?></strong>
                            </div>
                            <div class="member-metric">
                                <span>Last Active</span>
                                <strong><?php echo htmlspecialchars($lastLabel); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                </div>
            </div>
        <?php endif; ?>

            </section>

            <section>
                <h2>Team Overview</h2>
                <div class="card team-overview-card">
                    <div class="overview-metrics">
                        <div class="overview-metric">
                            <div class="metric-label">Overall</div>
                            <div class="metric-value"><?php echo $overallPercent; ?>%</div>
                            <div class="metric-bar">
                                <span style="width: <?php echo $overallPercent; ?>%;"></span>
                            </div>
                        </div>
                        <div class="overview-metric">
                            <div class="metric-label">Tasks Completed</div>
                            <div class="metric-value"><?php echo $doneTasks; ?> / <?php echo $totalTasks; ?></div>
                        </div>
                        <div class="overview-metric <?php echo $overdueTaskCount > 0 ? 'danger' : ''; ?>">
                            <div class="metric-label">Overdue Tasks</div>
                            <div class="metric-value"><?php echo $overdueTaskCount; ?></div>
                        </div>
                        <div class="overview-metric">
                            <div class="metric-label">Members</div>
                            <div class="metric-value">
                                <?php echo count($members); ?> / <?php echo (int) $team['max_members']; ?>
                            </div>
                        </div>
                    </div>

                    <div class="team-overview-meta">
                        <span><strong>Team ID</strong> #<?php echo (int) $team['id']; ?></span>
                        <span>
                            <strong>Formed</strong>
                            <?php echo $team['formed_at'] ? date('d M Y', strtotime($team['formed_at'])) : '—'; ?>
                        </span>
                        <span>
                            <strong>Roster locked</strong>
                            <?php echo $rosterLocked ? date('d M Y', strtotime($rosterLocked)) : '—'; ?>
                        </span>
                        <span>
                            <strong>Leader elected</strong>
                            <?php echo $leaderElectedAt ? date('d M Y', strtotime($leaderElectedAt)) : '—'; ?>
                        </span>
                        <span><strong>Last activity</strong> <?php echo htmlspecialchars(timeAgo($lastActivity)); ?></span>
                    </div>
                </div>
            </section>
        </div>

        <!-- Project milestones (teacher, read-only) -->
        <h2>Project Milestones</h2>
        <p class="muted" style="margin-bottom: 12px;">
            Read-only project phases set by the supervisor.
        </p>
        <div class="card milestone-card">
            <?php if (count($projectMilestones) === 0): ?>
                <div class="milestone-empty">No project milestones yet.</div>
            <?php else: ?>
                <div class="milestone-head">
                    <span>Milestone</span>
                    <span>Dates</span>
                    <span>Progress</span>
                    <span>Status</span>
                </div>
                <?php foreach ($projectMilestones as $milestone): ?>
                    <?php
                    $milestoneClass = strtolower(str_replace(
                        array(' ', '_'),
                        '-',
                        $milestone['status']
                    ));
                    ?>
                    <div class="milestone-row">
                        <div class="milestone-name"><?php echo htmlspecialchars($milestone['name']); ?></div>
                        <div class="milestone-dates">
                            <?php echo date('d M Y', strtotime($milestone['start_date'])); ?>
                            – <?php echo date('d M Y', strtotime($milestone['end_date'])); ?>
                        </div>
                        <div class="milestone-progress">
                            <span class="milestone-bar">
                                <i style="width: <?php echo (int) $milestone['progress']; ?>%;"></i>
                            </span>
                            <span><?php echo (int) $milestone['progress']; ?>%</span>
                        </div>
                        <div>
                            <span class="milestone-status <?php echo $milestoneClass; ?>">
                                <?php echo htmlspecialchars($milestone['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($isPending): ?>
            <div class="pending-lock">
                <strong>Approval required</strong>
                <p>Team tasks, attachments and activity are locked until this group is approved.</p>
            </div>
        <?php else: ?>

        <!-- Tasks -->
        <h2 id="tasks">Tasks</h2>
        <div class="task-toolbar">
            <span><strong><?php echo count($openTasks); ?></strong> open</span>
            <span class="task-dot">·</span>
            <span class="<?php echo $overdueTaskCount > 0 ? 'danger' : ''; ?>">
                <strong><?php echo $overdueTaskCount; ?></strong> overdue
            </span>
            <span class="task-dot">·</span>
            <span><strong><?php echo count($completedTasks); ?></strong> completed</span>
        </div>

        <div class="card task-card">
            <?php if (count($openTasks) === 0): ?>
                <div class="muted" style="text-align: center; padding: 30px;">
                    No open tasks — all tasks are completed.
                </div>
            <?php else: ?>
                <div class="task-head-row">
                    <span>Task</span>
                    <span>Due</span>
                    <span>Files</span>
                    <span>Status</span>
                </div>

                <?php foreach ($openTasks as $t): ?>
                    <?php
                    $due          = taskDueInfo($t['due_date'], $t['status']);
                    $statusClass  = strtolower(str_replace(array(' ', '_'), '-', $t['status']));
                    $isOverdue    = ($due['class'] === 'overdue');
                    ?>
                    <div class="task-row <?php echo $isOverdue ? 'is-overdue' : ''; ?>">
                        <div class="task-main">
                            <div class="task-name"><?php echo htmlspecialchars($t['title']); ?></div>
                            <div class="task-owner">
                                Assigned to <?php echo htmlspecialchars($t['owner_name'] ?: 'Unassigned'); ?>
                            </div>
                        </div>
                        <div class="task-due">
                            <div class="task-due-date"><?php echo htmlspecialchars($due['date']); ?></div>
                            <?php if ($due['label'] !== ''): ?>
                                <span class="task-due-label <?php echo $due['class']; ?>">
                                    <?php echo htmlspecialchars($due['label']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="task-files">
                            <a href="#task-attachments" title="Task attachments">
                                📎 <?php echo (int) $t['attachment_count']; ?>
                            </a>
                        </div>
                        <div>
                            <span class="task-status-pill <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($t['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($completedTasks) > 0): ?>
            <details class="completed-tasks">
                <summary>
                    Completed Tasks
                    <span><?php echo count($completedTasks); ?></span>
                </summary>

                <div class="completed-list">
                    <div class="completed-head">
                        <span>Task</span>
                        <span>Assigned To</span>
                        <span>Due</span>
                        <span>Files</span>
                    </div>

                    <?php foreach ($completedTasks as $t): ?>
                        <div class="completed-row">
                            <div class="completed-task-name"><?php echo htmlspecialchars($t['title']); ?></div>
                            <div class="muted"><?php echo htmlspecialchars($t['owner_name'] ?: 'Unassigned'); ?></div>
                            <div class="muted">
                                <?php echo $t['due_date'] ? date('d M Y', strtotime($t['due_date'])) : '—'; ?>
                            </div>
                            <div class="completed-files">
                                <a href="#task-attachments">📎 <?php echo (int) $t['attachment_count']; ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>

        <!-- Task attachments -->
        <h2>Task Attachments</h2>
        <div class="card attachment-card">
            <?php if (count($attachmentGroups) === 0): ?>
                <div class="attachment-empty">No task attachments uploaded yet.</div>
            <?php else: ?>
                <div class="attachment-head">
                    <span>File</span>
                    <span>Task</span>
                    <span>Status</span>
                    <span>Updated</span>
                </div>

                <?php foreach ($attachmentGroups as $f): ?>
                    <div class="attachment-row">
                        <div class="attachment-file">
                            <a href="file_view.php?id=<?php echo (int) $f['id']; ?>"
                               title="Uploaded by <?php echo htmlspecialchars($f['owner_name']); ?> · <?php echo htmlspecialchars($f['file_size']); ?>">
                                <?php echo htmlspecialchars($f['file_name']); ?>
                            </a>
                            <?php if ((int) $f['version_count'] > 1): ?>
                                <span class="attachment-version">v<?php echo (int) $f['version_count']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="attachment-task" title="<?php echo htmlspecialchars($f['task_title']); ?>">
                            <?php echo htmlspecialchars($f['task_title']); ?>
                        </div>
                        <div class="attachment-status">
                            <i class="dot-<?php echo strtolower($f['status']); ?>"></i>
                            <?php echo htmlspecialchars($f['status']); ?>
                        </div>
                        <div class="attachment-time"><?php echo timeAgo($f['uploaded_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Activity -->
        <h2>Activity</h2>

        <?php
        $activityChipDefs = array(
            'all'     => 'All',
            'files'   => 'Files',
            'tasks'   => 'Tasks',
            'reviews' => 'Reviews',
            'team'    => 'Team changes',
        );
        ?>
        <div class="activity-filters">
            <?php foreach ($activityChipDefs as $key => $label): ?>
                <button type="button"
                        class="activity-filter <?php echo $activityType === $key ? 'active' : ''; ?>"
                        data-filter="<?php echo htmlspecialchars($key); ?>"
                        aria-pressed="<?php echo $activityType === $key ? 'true' : 'false'; ?>">
                    <?php echo htmlspecialchars($label); ?>
                    <span><?php echo (int) $activityCounts[$key]; ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="card activity-card">
            <?php if (count($mergedActivities) === 0): ?>
                <div class="activity-empty">No activity in this category.</div>
            <?php endif; ?>

            <?php foreach ($mergedActivities as $index => $item): ?>
                <?php
                $uploads     = $item['uploads'];
                $uploadCount = count($uploads);
                $isUpload    = ($item['category'] === 'files'
                    && strtolower((string) $item['raw']['action']) === 'uploaded');
                $actorName   = $item['actor_name'];
                $initials    = memberInitials($actorName);
                $avatarBg    = memberAvatarBg($actorName, $memberPalette);
                $detailHtml  = htmlspecialchars($item['raw']['detail']);
                $taskUrl     = 'team_detail.php?id=' . $teamId . '#tasks';
                $fullTime    = date('d M Y H:i', strtotime($item['created_at']));
                $showTaskMeta = !empty($item['task_title']);

                if ($isUpload && $uploadCount > 1) {
                    $uploadTaskIds = array();
                    foreach ($uploads as $upload) {
                        $uploadTaskIds[] = (int) $upload['task_id'];
                    }
                    $uploadTaskIds = array_unique($uploadTaskIds);
                    $showTaskMeta = (count($uploadTaskIds) === 1 && !empty($item['task_id']));
                }

                $fileHtml = 'a file';
                if ($item['file']) {
                    $fileHtml = '<a href="file_view.php?id=' . (int) $item['file']['id'] . '">'
                              . htmlspecialchars($item['file']['file_name']) . '</a>';
                } elseif ($item['file_name'] !== '') {
                    $fileHtml = htmlspecialchars($item['file_name']);
                }

                if ($item['category'] === 'tasks' && !empty($item['task_title'])) {
                    $linkedTask = '<a href="' . $taskUrl . '">'
                                . htmlspecialchars($item['task_title']) . '</a>';
                    $detailHtml = str_replace(
                        htmlspecialchars($item['task_title']),
                        $linkedTask,
                        $detailHtml
                    );
                }
                ?>
                <div class="activity-item"
                     data-category="<?php echo htmlspecialchars($item['category']); ?>">
                    <div class="activity-avatar" style="background: <?php echo $avatarBg; ?>;">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>

                    <div class="activity-main">
                        <div class="activity-line">
                            <strong><?php echo htmlspecialchars($actorName); ?></strong>
                            <?php if ($isUpload): ?>
                                uploaded
                                <?php if ($uploadCount > 1): ?>
                                    <?php echo $uploadCount; ?> files
                                <?php else: ?>
                                    <?php echo $fileHtml; ?>
                                <?php endif; ?>
                            <?php elseif ($item['category'] === 'reviews'): ?>
                                reviewed <?php echo $fileHtml; ?>
                                <?php if ($item['status']): ?>
                                    <span class="activity-review <?php echo strtolower($item['status']); ?>">
                                        <?php echo htmlspecialchars($item['status']); ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo $detailHtml; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($isUpload && $uploadCount > 1): ?>
                            <div class="activity-uploads">
                                <?php foreach ($uploads as $upload): ?>
                                    <?php if ($upload['file']): ?>
                                        <a href="file_view.php?id=<?php echo (int) $upload['file']['id']; ?>"
                                           <?php if (!empty($upload['task_title'])): ?>
                                               title="Task: <?php echo htmlspecialchars($upload['task_title']); ?>"
                                           <?php endif; ?>>
                                            <?php echo htmlspecialchars($upload['file']['file_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($upload['file_name'] ?: 'File'); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="activity-meta">
                            <?php if (!empty($item['team_name'])): ?>
                                <a href="team_detail.php?id=<?php echo (int) $item['group_id']; ?>">
                                    <?php echo htmlspecialchars($item['team_name']); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($item['project_name'])): ?>
                                · <a href="project_detail.php?id=<?php echo (int) $item['project_id']; ?>">
                                    <?php echo htmlspecialchars($item['project_name']); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($showTaskMeta): ?>
                                · <a href="<?php echo $taskUrl; ?>">
                                    <?php echo htmlspecialchars($item['task_title']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="activity-time" title="<?php echo htmlspecialchars($fullTime); ?>">
                        <?php echo timeAgo($item['created_at']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($mergedActivities) > 5): ?>
            <button type="button" class="activity-show-more" id="activityShowMore">
                Show more (<?php echo count($mergedActivities) - 5; ?>)
            </button>
        <?php endif; ?>

        <?php if (count($cleanupActivities) > 0): ?>
            <details class="system-cleanup">
                <summary>
                    System cleanup
                    <span><?php echo count($cleanupActivities); ?></span>
                </summary>
                <div class="cleanup-list">
                    <?php foreach ($cleanupActivities as $item): ?>
                        <div class="cleanup-item">
                            <span>
                                <strong><?php echo htmlspecialchars($item['actor_name']); ?></strong>
                                <?php echo htmlspecialchars($item['raw']['detail']); ?>
                            </span>
                            <span title="<?php echo htmlspecialchars(date('d M Y H:i', strtotime($item['created_at']))); ?>">
                                <?php echo timeAgo($item['created_at']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<script>
(function () {
    var buttons = Array.prototype.slice.call(document.querySelectorAll('.member-switch'));
    var panels  = Array.prototype.slice.call(document.querySelectorAll('.member-panel'));

    function activate(memberId) {
        buttons.forEach(function (button) {
            var active = button.getAttribute('data-member') === memberId;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-member-panel') !== memberId;
        });

        var url = new URL(window.location.href);
        url.searchParams.set('member', memberId);
        history.replaceState(null, '', url.pathname + '?' + url.searchParams.toString());
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            activate(button.getAttribute('data-member'));
        });
    });
})();
</script>

<script>
(function () {
    var filters = Array.prototype.slice.call(document.querySelectorAll('.activity-filter'));
    var items   = Array.prototype.slice.call(document.querySelectorAll('.activity-item'));
    var more    = document.getElementById('activityShowMore');
    var current = <?php echo json_encode($activityType); ?>;

    function matching(filter) {
        return items.filter(function (item) {
            return filter === 'all' || item.getAttribute('data-category') === filter;
        });
    }

    function applyFilter(filter) {
        current = filter;

        filters.forEach(function (button) {
            var active = button.getAttribute('data-filter') === filter;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        items.forEach(function (item) {
            item.hidden = true;
        });

        var visibleItems = matching(filter);
        visibleItems.forEach(function (item, index) {
            item.hidden = index >= 5;
        });

        if (more) {
            if (visibleItems.length > 5) {
                more.hidden = false;
                more.textContent = 'Show more (' + (visibleItems.length - 5) + ')';
            } else {
                more.hidden = true;
            }
        }

        var url = new URL(window.location.href);
        url.searchParams.set('atype', filter);
        history.replaceState(null, '', url.pathname + '?' + url.searchParams.toString());
    }

    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            applyFilter(button.getAttribute('data-filter'));
        });
    });

    if (more) {
        more.addEventListener('click', function () {
            matching(current).forEach(function (item) {
                item.hidden = false;
            });
            more.hidden = true;
        });
    }

    applyFilter(current);
})();
</script>

</body>
</html>
