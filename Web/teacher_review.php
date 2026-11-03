<?php
$active = 'review';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/topic_helpers.php';
/** @var mysqli $conn */

// 当前老师的 Teachers.id
$teacherId = 0;
if (isset($_SESSION['userID'])) {
    $stmtT = mysqli_prepare($conn, "SELECT id FROM `teachers` WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtT, "i", $_SESSION['userID']);
    mysqli_stmt_execute($stmtT);
    $rsT = mysqli_stmt_get_result($stmtT);
    $rowT = mysqli_fetch_assoc($rsT);
    mysqli_stmt_close($stmtT);
    if ($rowT) { $teacherId = (int)$rowT['id']; }
}

$teacherFlash = null;
if (isset($_SESSION['teacher_flash'])) {
    $teacherFlash = $_SESSION['teacher_flash'];
    unset($_SESSION['teacher_flash']);
}

function groupRequestReviewIssues($conn, $requestId)
{
    $issues = array();

    $stmt = mysqli_prepare($conn, "
        SELECT
            r.id,
            r.project_id,
            r.group_name,
            p.class_id AS home_class_id,
            p.default_max_members,
            p.allow_cross_class,
            g.id AS group_id
        FROM `group_requests` r
        INNER JOIN `projects` p ON p.id = r.project_id
        LEFT JOIN `groups` g ON g.project_id = r.project_id AND g.name = r.group_name
        WHERE r.id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "i", $requestId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $request = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$request) {
        return array('Request data could not be loaded.');
    }

    $members = array();
    $stmt = mysqli_prepare($conn, "
        SELECT s.id, s.class_id
        FROM `request_members` rm
        INNER JOIN `students` s ON s.id = rm.student_id
        WHERE rm.request_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $requestId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) $members[] = $row;
    mysqli_stmt_close($stmt);

    if (count($members) === 0 && !empty($request['group_id'])) {
        $stmt = mysqli_prepare($conn, "
            SELECT s.id, s.class_id
            FROM `group_members` gm
            INNER JOIN `students` s ON s.id = gm.student_id
            WHERE gm.group_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $request['group_id']);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) $members[] = $row;
        mysqli_stmt_close($stmt);
    }

    if (count($members) === 0) {
        $issues[] = 'No group members were found.';
        return $issues;
    }

    if (count($members) > (int) $request['default_max_members']) {
        $issues[] = 'The team exceeds the configured member limit.';
    }

    $partnerClasses = array();
    $stmt = mysqli_prepare($conn, "
        SELECT class_id FROM `project_cross_classes` WHERE project_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $request['project_id']);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $partnerClasses[] = (int) $row['class_id'];
    }
    mysqli_stmt_close($stmt);

    foreach ($members as $member) {
        $memberClass = (int) $member['class_id'];
        if ($memberClass !== (int) $request['home_class_id']
            && (!(int) $request['allow_cross_class'] || !in_array($memberClass, $partnerClasses, true))) {
            $issues[] = 'One or more members are from a class that is not allowed for this project.';
            break;
        }
    }

    if (!empty($request['group_id'])) {
        $memberIds = array_map(function ($member) { return (int) $member['id']; }, $members);
        $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
        $types = 'ii' . str_repeat('i', count($memberIds));
        $params = array_merge(
            array($request['project_id'], $request['group_id']),
            $memberIds
        );
        $stmt = mysqli_prepare($conn, "
            SELECT DISTINCT g2.name
            FROM `group_members` gm2
            INNER JOIN `groups` g2 ON g2.id = gm2.group_id
            WHERE g2.project_id = ?
              AND g2.id <> ?
              AND gm2.student_id IN ($placeholders)
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $duplicate = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);
        if ($duplicate) {
            $issues[] = 'A member already belongs to another team in this project.';
        }
    }

    return array_values(array_unique($issues));
}

// =========================================================
// 0a. Topic proposal review
// =========================================================
if (isset($_POST['topicAction'], $_POST['proposalId'])) {
    $topicAction = $_POST['topicAction'] === 'approve' ? 'Approved' : 'Rejected';
    $proposalId = (int) $_POST['proposalId'];
    $topicNote = isset($_POST['topicReviewNote']) ? trim($_POST['topicReviewNote']) : '';

    if ($topicAction !== 'Rejected' || $topicNote !== '') {
        $stmt = mysqli_prepare($conn, "
            SELECT tp.id, tp.group_id, tp.topic_title, p.name AS project_name, p.topic_mode
            FROM `team_topic_proposals` tp
            INNER JOIN `projects` p ON p.id = tp.project_id
            WHERE tp.id = ?
              AND tp.status = 'Pending'
              AND p.supervisor_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $proposalId, $teacherId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $proposal = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        if ($proposal) {
            $stmt = mysqli_prepare($conn, "
                UPDATE `team_topic_proposals`
                SET status = ?, review_note = ?, reviewed_by = ?, reviewed_at = NOW()
                WHERE id = ? AND status = 'Pending'
            ");
            mysqli_stmt_bind_param($stmt, "ssii", $topicAction, $topicNote, $teacherId, $proposalId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($proposal['topic_mode'] === 'Self') {
                $title = $topicAction === 'Approved'
                    ? 'Project title approved'
                    : 'Project title needs revision';
            } else {
                $title = $topicAction === 'Approved'
                    ? 'Topic proposal approved'
                    : 'Topic proposal needs revision';
            }
            $_SESSION['teacher_flash'] = array(
                'message' => $title,
                'type' => $topicAction === 'Approved' ? 'success' : 'warning',
            );
            $message = $proposal['project_name'] . ' · ' . $proposal['topic_title']
                . ($topicNote !== '' ? ' — ' . $topicNote : '');
            $stmt = mysqli_prepare($conn, "
                INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
                SELECT s.user_id, 'system', ?, ?, 'student_topic.php?group_id=" . (int) $proposal['group_id'] . "', 0
                FROM `group_members` gm
                INNER JOIN `students` s ON s.id = gm.student_id
                WHERE gm.group_id = ?
            ");
            mysqli_stmt_bind_param($stmt, "ss", $title, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header('Location: teacher_review.php#topics');
    exit;
}

// =========================================================
// 0b. Group formation approval
// =========================================================
if (isset($_POST['action']) && isset($_POST['requestId'])) {

    $action    = $_POST['action'];
    $requestId = (int) $_POST['requestId'];
    $note      = isset($_POST['reviewNote']) ? trim($_POST['reviewNote']) : '';

    if ($action === 'accept') {
        $acceptIssues = groupRequestReviewIssues($conn, $requestId);
        if (count($acceptIssues) > 0) {
            $_SESSION['teacher_flash'] = array(
                'message' => 'This request cannot be approved: ' . $acceptIssues[0],
                'type' => 'error',
            );
            header('Location: teacher_review.php#requests');
            exit;
        }
        $stmt = mysqli_prepare($conn, "
            UPDATE `group_requests`
            SET status = 'Accepted',
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $teacherId, $requestId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['teacher_flash'] = array(
            'message' => 'Group formation request approved.',
            'type' => 'success',
        );

        // Approving a formation request makes the group official.
        $stmt = mysqli_prepare($conn, "
            UPDATE `groups` g
            INNER JOIN `group_requests` r
                    ON r.project_id = g.project_id AND r.group_name = g.name
            SET g.status = 'Voting',
                g.formed_at = COALESCE(g.formed_at, NOW())
            WHERE r.id = ?
              AND g.status IN ('Recruiting', 'Waiting_Send')
        ");
        mysqli_stmt_bind_param($stmt, "i", $requestId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

    } elseif ($action === 'reject' && $note !== '') {
        $stmt = mysqli_prepare($conn, "
            UPDATE `group_requests`
            SET status = 'Rejected',
                review_note = ?,
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "sii", $note, $teacherId, $requestId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['teacher_flash'] = array(
            'message' => 'Group formation request rejected.',
            'type' => 'warning',
        );
    }

    header('Location: teacher_review.php#requests');
    exit;
}


// =========================================================
// 1. 汇总数字（只看我负责项目下的文件 / 申请）
// =========================================================

// Pending Files
$pendingFiles = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE f.status = 'Unviewed'
      AND f.task_id IS NOT NULL
      AND p.supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$pendingFiles = (int)$rc['c'];
mysqli_stmt_close($stmt);

// Approved Files
$approvedFiles = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE f.status = 'Accepted'
      AND f.task_id IS NOT NULL
      AND p.supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$approvedFiles = (int)$rc['c'];
mysqli_stmt_close($stmt);

// Rejected Files
$rejectedFiles = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE f.status = 'Rejected'
      AND f.task_id IS NOT NULL
      AND p.supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$rejectedFiles = (int)$rc['c'];
mysqli_stmt_close($stmt);

// Group Requests（我负责项目下的 Pending）
$groupRequestCount = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `group_requests` r
    INNER JOIN `projects` p ON p.id = r.project_id
    WHERE r.status = 'Pending' AND p.supervisor_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$groupRequestCount = (int)$rc['c'];
mysqli_stmt_close($stmt);


// =========================================================
// 2. Tab 1 — All Files（只看我的项目 + 筛选）
// =========================================================
$filterFrom   = isset($_GET['from'])   ? trim($_GET['from'])   : '';
$filterTo     = isset($_GET['to'])     ? trim($_GET['to'])     : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$filterSearch = isset($_GET['q'])      ? trim($_GET['q'])      : '';

$where  = array("p.supervisor_id = ?", "f.task_id IS NOT NULL");
$params = array($teacherId);
$types  = 'i';

if ($filterStatus !== 'all' && $filterStatus !== '') {
    $where[]  = "f.status = ?";
    $params[] = $filterStatus;
    $types   .= 's';
}

if ($filterFrom !== '') {
    $where[]  = "DATE(f.uploaded_at) >= ?";
    $params[] = $filterFrom;
    $types   .= 's';
}

if ($filterTo !== '') {
    $where[]  = "DATE(f.uploaded_at) <= ?";
    $params[] = $filterTo;
    $types   .= 's';
}

if ($filterSearch !== '') {
    $where[]  = "(f.file_name LIKE ? OR g.name LIKE ? OR u.username LIKE ?
                  OR p.name LIKE ? OR t.title LIKE ?)";
    $params[] = '%' . $filterSearch . '%';
    $params[] = '%' . $filterSearch . '%';
    $params[] = '%' . $filterSearch . '%';
    $params[] = '%' . $filterSearch . '%';
    $params[] = '%' . $filterSearch . '%';
    $types   .= 'sssss';
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$allFiles = array();

$sql = "
    SELECT
        f.id,
        f.file_name,
        f.file_size,
        f.file_type,
        f.status,
        f.uploaded_at,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))) AS version_count,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id <= f.id) AS version_number,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id < f.id
              AND fv.status = 'Rejected') AS prior_rejections,
        g.id     AS group_id,
        g.name   AS team_name,
        p.name   AS project_name,
        t.title  AS task_title,
        s.id     AS uploader_id,
        s.student_number,
        u.username AS uploader_name
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    LEFT JOIN `groups`   g ON g.id = f.group_id
    LEFT JOIN `tasks`    t ON t.id = f.task_id
    LEFT JOIN `students` s ON s.id = f.uploader_id
    LEFT JOIN `users`    u ON u.id = s.user_id
    $whereSql
    ORDER BY f.uploaded_at DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $allFiles[] = $row; }
mysqli_stmt_close($stmt);

// Total Files（我的项目下的）
$totalFiles = 0;
$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    WHERE p.supervisor_id = ?
      AND f.task_id IS NOT NULL
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$totalFiles = (int)$rc['c'];
mysqli_stmt_close($stmt);

$filteredCount = count($allFiles);

$rangeText = 'All dates';
if ($filterFrom !== '' || $filterTo !== '') {
    $rangeText = ($filterFrom !== '' ? $filterFrom : '…') . ' – ' . ($filterTo !== '' ? $filterTo : '…');
}


// =========================================================
// 3. Tab 2 — Unviewed Files
// =========================================================
$unviewedFiles = array();

$sql = "
    SELECT
        f.id,
        f.file_name,
        f.file_size,
        f.file_type,
        f.uploaded_at,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))) AS version_count,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id <= f.id) AS version_number,
        (SELECT COUNT(*) FROM `files` fv
            WHERE fv.group_id = f.group_id
              AND fv.uploader_id = f.uploader_id
              AND COALESCE(fv.task_id, 0) = COALESCE(f.task_id, 0)
              AND LOWER(TRIM(fv.file_name)) = LOWER(TRIM(f.file_name))
              AND fv.id < f.id
              AND fv.status = 'Rejected') AS prior_rejections,
        g.name     AS team_name,
        t.title    AS task_title,
        u.username AS uploader_name
    FROM `files` f
    INNER JOIN `projects` p ON p.id = f.project_id
    LEFT JOIN `groups`   g ON g.id = f.group_id
    LEFT JOIN `tasks`    t ON t.id = f.task_id
    LEFT JOIN `students` s ON s.id = f.uploader_id
    LEFT JOIN `users`    u ON u.id = s.user_id
    WHERE f.status = 'Unviewed'
      AND f.task_id IS NOT NULL
      AND p.supervisor_id = ?
    ORDER BY f.uploaded_at ASC, f.id ASC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) { $unviewedFiles[] = $row; }
mysqli_stmt_close($stmt);


// =========================================================
// 4. Tab 3 — Group Requests（只看我的项目）
// =========================================================
$groupRequests = array();

$sql = "
    SELECT
        r.id,
        r.group_name,
        r.status,
        r.submitted_at,
        p.name AS project_name,
        g.id AS group_id,
        g.status AS group_status,
        u.username AS submitted_by_name
    FROM `group_requests` r
    INNER JOIN `projects` p ON p.id = r.project_id
    LEFT JOIN `groups` g ON g.project_id = r.project_id AND g.name = r.group_name
    LEFT JOIN `students` s ON s.id = r.submitted_by
    LEFT JOIN `users`    u ON u.id = s.user_id
    WHERE r.status = 'Pending'
      AND p.supervisor_id = ?
    ORDER BY r.submitted_at DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($rs)) {

    $members = array();
    $stmtM = mysqli_prepare($conn, "
        SELECT
            s.id AS student_id,
            s.student_number,
            u.username AS name,
            rm.is_leader
        FROM `request_members` rm
        INNER JOIN `students` s ON s.id = rm.student_id
        INNER JOIN `users`    u ON u.id = s.user_id
        WHERE rm.request_id = ?
        ORDER BY rm.is_leader DESC, s.id
    ");
    mysqli_stmt_bind_param($stmtM, "i", $row['id']);
    mysqli_stmt_execute($stmtM);
    $rsM = mysqli_stmt_get_result($stmtM);
    while ($m = mysqli_fetch_assoc($rsM)) {
        $members[] = $m;
    }
    mysqli_stmt_close($stmtM);

    // Fallback for requests created without request_members rows.
    if (count($members) === 0 && !empty($row['group_id'])) {
        $stmtM = mysqli_prepare($conn, "
            SELECT
                s.id AS student_id,
                s.student_number,
                u.username AS name,
                (gm.role = 'leader') AS is_leader
            FROM `group_members` gm
            INNER JOIN `students` s ON s.id = gm.student_id
            INNER JOIN `users`    u ON u.id = s.user_id
            WHERE gm.group_id = ?
            ORDER BY is_leader DESC, s.id
        ");
        $memberGroupId = (int) $row['group_id'];
        mysqli_stmt_bind_param($stmtM, "i", $memberGroupId);
        mysqli_stmt_execute($stmtM);
        $rsM = mysqli_stmt_get_result($stmtM);
        while ($m = mysqli_fetch_assoc($rsM)) {
            $members[] = $m;
        }
        mysqli_stmt_close($stmtM);
    }

    $row['members'] = $members;
    if ($filterSearch !== '') {
        $haystack = $row['group_name'] . ' ' . $row['project_name'] . ' ' . $row['submitted_by_name'];
        if (stripos($haystack, $filterSearch) === false) {
            continue;
        }
    }
    $row['review_issues'] = groupRequestReviewIssues($conn, (int) $row['id']);
    $groupRequests[] = $row;
}
mysqli_stmt_close($stmt);

// =========================================================
// 5. Pending Topic Proposals
// =========================================================
$topicProposals = array();
$stmt = mysqli_prepare($conn, "
    SELECT
        tp.id,
        tp.group_id,
        tp.topic_id,
        tp.version,
        tp.topic_title,
        tp.topic_description,
        tp.submitted_at,
        g.name AS group_name,
        g.status AS group_status,
        p.id AS project_id,
        p.name AS project_name,
        p.topic_mode,
        u.username AS submitted_by_name
    FROM `team_topic_proposals` tp
    INNER JOIN `groups` g ON g.id = tp.group_id
    INNER JOIN `projects` p ON p.id = tp.project_id
    LEFT JOIN `students` s ON s.id = tp.submitted_by
    LEFT JOIN `users` u ON u.id = s.user_id
    WHERE tp.status = 'Pending'
      AND p.supervisor_id = ?
    ORDER BY tp.submitted_at DESC, tp.id DESC
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    if ($filterSearch !== '') {
        $haystack = $row['topic_title'] . ' ' . $row['project_name'] . ' '
            . $row['group_name'] . ' ' . $row['submitted_by_name'];
        if (stripos($haystack, $filterSearch) === false) {
            continue;
        }
    }
    $row['selection_summary'] = null;
    if ($row['topic_mode'] === 'Multi' && !empty($row['topic_id'])) {
        $row['selection_summary'] = topicSelectionSummary(
            $conn,
            (int) $row['project_id'],
            (int) $row['topic_id']
        );
    }
    $topicProposals[] = $row;
}
mysqli_stmt_close($stmt);
$topicProposalCount = count($topicProposals);
$reviewAttentionCount = $pendingFiles + $groupRequestCount + $topicProposalCount;

$queueCandidates = array();
if (count($unviewedFiles) > 0) {
    $queueCandidates[] = array(
        'time' => $unviewedFiles[0]['uploaded_at'],
        'label' => 'Start file review',
        'url' => 'file_view.php?id=' . (int) $unviewedFiles[0]['id'] . '&queue=1',
    );
}
if (count($groupRequests) > 0) {
    $queueCandidates[] = array(
        'time' => $groupRequests[0]['submitted_at'],
        'label' => 'Review group request',
        'url' => 'teacher_review.php#requests',
    );
}
if (count($topicProposals) > 0) {
    $queueCandidates[] = array(
        'time' => $topicProposals[0]['submitted_at'],
        'label' => 'Review topic proposal',
        'url' => 'teacher_review.php#topics',
    );
}
usort($queueCandidates, function ($a, $b) {
    $aTs = strtotime($a['time']);
    $bTs = strtotime($b['time']);
    if ($aTs < $bTs) return -1;
    if ($aTs > $bTs) return 1;
    return 0;
});
$startReview = count($queueCandidates) > 0 ? $queueCandidates[0] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Center | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261028b">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">

    <style>
        .review-title-row h1 { margin-bottom: 2px; }
        .review-title-row .subtitle { margin-bottom: 14px; }
        .review-desktop-actions { display: none; }

        /* ===== Tabs ===== */
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 14px;
            border-bottom: 1px solid #e6e8eb;
        }

        .tab {
            padding: 9px 14px;
            font-size: 13px;
            color: #6b6f78;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
        }

        .tab:hover { color: #2c2f36; }

        .tab.active {
            color: #175196;
            font-weight: 600;
            border-bottom-color: #175196;
        }

        .tab .tab-count {
            display: inline-block;
            margin-left: 6px;
            background: #f2f3f5;
            color: #6b6f78;
            font-size: 11px;
            font-weight: 600;
            padding: 1px 7px;
            border-radius: 10px;
        }

        .tab.active .tab-count {
            background: #175196;
            color: #fff;
        }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ===== Filter bar ===== */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
            padding: 12px 14px;
            margin-bottom: 12px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-field[hidden] { display: none !important; }

        .filter-field label {
            font-size: 12px;
            color: #6b6f78;
            font-weight: 500;
            letter-spacing: 0.03em;
        }

        .filter-field input,
        .filter-field select {
            height: 36px;
            padding: 0 10px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 6px;
            font-size: 13px;
            color: #2c2f36;
            font-family: inherit;
            outline: none;
        }

        .filter-field input:focus,
        .filter-field select:focus { border-color: #175196; }

        .filter-field.search input { width: 240px; }
        .filter-field.date input   { width: 160px; }
        .filter-field.status select{ width: 160px; }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .btn-secondary {
            background: #fff;
            color: #175196;
            border: 1px solid #175196;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            height: 36px;
            line-height: 18px;
        }

        .btn-secondary:hover { background: #f2f7fd; }
        .btn-primary:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* ===== Result info ===== */
        .result-info {
            font-size: 13px;
            color: #6b6f78;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .result-info .range-text { color: #8b8f98; }

        /* ===== File table ===== */
        .file-table { table-layout: auto; }

        .file-table th,
        .file-table td {
            white-space: normal;
            word-break: break-word;
        }

        .file-name-cell { max-width: 320px; }

        .file-name-link {
            color: #175196;
            font-weight: 500;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
        }

        .file-name-link:hover { text-decoration: underline; }

        .file-task-label {
            margin-top: 2px;
            color: #9aa0a8;
            font-size: 10.5px;
            line-height: 1.4;
        }
        .file-version-label {
            margin-top: 3px;
            color: #526f88;
            font-size: 10px;
            font-weight: 600;
        }

        .row-actions {
            display: flex;
            gap: 12px;
            white-space: nowrap;
        }

        /* ===== Status pill ===== */
        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pill.unviewed {
            background: #f7f8fa;
            color: #6b6f78;
            border: 1px solid #e6e8eb;
        }

        .status-pill.accepted {
            background: #eaf7ee;
            color: #176b2c;
            border: 1px solid #8bc79a;
        }

        .status-pill.rejected {
            background: #fdeeee;
            color: #a21b1b;
            border: 1px solid #e6a0a0;
        }
        .pill-pending {
            background: #edf4fb;
            color: #315f87;
        }

        /* ===== Group Request card ===== */
        .request-card {
            border: 1px solid #e6e8eb;
            border-left: 4px solid #175196;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .request-head {
            padding: 14px 17px;
            background: #f7f8fa;
            border-bottom: 1px solid #e6e8eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .request-head h3 {
            font-size: 14px;
            font-weight: 600;
            color: #2c2f36;
            margin-bottom: 4px;
        }

        .request-head .request-meta {
            font-size: 13px;
            color: #8b8f98;
        }

        .request-body { padding: 6px 0; }

        .request-body table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .request-body table th {
            text-align: left;
            font-size: 12px;
            font-weight: 500;
            color: #6b6f78;
            background: #fbfbfc;
            padding: 10px 22px;
            border-bottom: 1px solid #f2f3f5;
        }

        .request-body table td {
            padding: 12px 22px;
            border-bottom: 1px solid #f2f3f5;
            color: #2c2f36;
            white-space: normal;
        }

        .request-body table tr:last-child td { border-bottom: none; }

        .request-foot {
            padding: 16px 22px;
            background: #fafbfc;
            border-top: 1px solid #e6e8eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .member-toggle {
            display: none;
            align-items: center;
            padding: 6px 9px;
            border: 1px solid #d7dee5;
            border-radius: 5px;
            background: #fff;
            color: #56616d;
            font-family: inherit;
            font-size: 10.5px;
            font-weight: 600;
            cursor: pointer;
        }
        .member-toggle:hover { border-color: #9eb0c1; background: #f4f7fa; }

        .topic-review-card {
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid #e2e6ea;
            border-left: 4px solid #537da7;
            border-radius: 8px;
            background: #fff;
        }
        .topic-review-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 20px;
            border-bottom: 1px solid #edf0f3;
            background: #f8fafc;
        }
        .topic-review-head h3 {
            margin: 0 0 4px;
            color: #252b33;
            font-size: 16px;
        }
        .topic-review-meta {
            color: #7f8791;
            font-size: 11.5px;
            line-height: 1.55;
        }
        .topic-review-body { padding: 16px 20px; }
        .topic-review-description {
            margin: 0 0 14px;
            color: #4f5761;
            font-size: 12.5px;
            line-height: 1.65;
        }
        .topic-selection-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }
        .topic-selection-summary span {
            padding: 5px 8px;
            border-radius: 6px;
            background: #eef3f8;
            color: #4e6275;
            font-size: 10.5px;
            font-weight: 600;
        }
        .topic-review-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 8px;
            align-items: center;
        }
        .topic-review-actions input {
            min-width: 0;
            padding: 8px 10px;
            border: 1px solid #dfe3e8;
            border-radius: 6px;
            color: #3e4650;
            font-family: inherit;
            font-size: 12px;
        }
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .quick-reply-btn {
            padding: 5px 8px;
            border: 1px solid #d7dee5;
            border-radius: 5px;
            background: #fff;
            color: #56616d;
            font-family: inherit;
            font-size: 10.5px;
            cursor: pointer;
        }
        .quick-reply-btn:hover { border-color: #8fa7bd; background: #f5f8fb; }

        /* ===== Responsive ===== */
        @media (max-width: 900px) {
            .filter-bar { flex-direction: column; align-items: stretch; }
            .filter-field.search input,
            .filter-field.date input,
            .filter-field.status select { width: 100%; }
            .filter-actions { margin-left: 0; }
            .filter-actions .btn-primary,
            .filter-actions .btn-secondary { flex: 1; text-align: center; }
        }

        @media (max-width: 700px) {
            .request-head { flex-direction: column; gap: 10px; }
            .request-foot { flex-direction: column; }
            .row-actions { flex-direction: column; gap: 4px; }
            .topic-review-head { flex-direction: column; }
            .topic-review-actions { grid-template-columns: 1fr; }
        }

        /* Desktop-only hierarchy; mobile rules above remain unchanged. */
        @media (min-width: 901px) {
            .review-title-row {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
            }
            .review-title-row h1 { margin-bottom: 4px; }
            .review-title-row .subtitle { margin-bottom: 16px; }
            .review-desktop-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 0 0 auto;
                padding-top: 3px;
            }
            .review-desktop-actions > span {
                color: #7d858f;
                font-size: 11px;
                font-weight: 600;
            }
            .review-desktop-actions .btn-primary {
                min-height: 34px;
                padding: 7px 12px;
                text-decoration: none;
            }
            .tb-alert.review-attention-alert { display: none; }
            .review-title-row + .tabs { margin-top: 2px; }
            .filter-bar {
                padding: 10px;
                border-color: #e3e7eb;
                box-shadow: none;
            }
            .filter-bar .filter-field label { font-size: 10.5px; }
            .filter-bar .btn-primary {
                background: #fff;
                color: #175196;
                border: 1px solid #afc3d8;
            }
            .filter-bar .btn-primary:hover { background: #f3f7fb; }
            .request-head { padding: 12px 15px; }
            .request-head h3 { font-size: 13px; }
            .request-head .request-meta { font-size: 11px; }
            .request-body table { font-size: 12px; }
            .request-body table th { padding: 8px 15px; }
            .request-body table td { padding: 9px 15px; }
            .request-foot { padding: 10px 15px; }
            .request-members { display: none; }
            .request-members.is-expanded { display: block; }
            .member-toggle { display: inline-flex; margin-right: auto; }
            .topic-review-list {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }
            .topic-review-card { margin-bottom: 0; }
            .topic-review-head { padding: 13px 15px; }
            .topic-review-head h3 { font-size: 14px; }
            .topic-review-body { padding: 13px 15px; }
            .topic-review-description { font-size: 11.5px; }
        }
    </style>
    <link rel="stylesheet" href="teacher_ui.css?v=20261028b">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <div class="review-title-row">
            <div>
                <h1>Review Center</h1>
                <p class="subtitle">Review student files, group formation requests and topic proposals</p>
            </div>
            <?php if ($reviewAttentionCount > 0): ?>
                <div class="review-desktop-actions">
                    <span><?= (int) $reviewAttentionCount ?> pending</span>
                    <?php if ($startReview): ?>
                        <a href="<?= htmlspecialchars($startReview['url']) ?>" class="btn-primary">
                            Start Review
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($reviewAttentionCount > 0): ?>
            <div class="tb-alert tb-alert--warning review-attention-alert">
                <span><strong><?= (int) $reviewAttentionCount ?> item<?= $reviewAttentionCount === 1 ? '' : 's' ?></strong> <?= $reviewAttentionCount === 1 ? 'requires' : 'require' ?> review.</span>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <a href="#" class="tab active" data-tab="files">
                All Files
                <span class="tab-count"><?php echo $totalFiles; ?></span>
            </a>
            <a href="#" class="tab" data-tab="unviewed">
                Unviewed Files
                <span class="tab-count"><?php echo count($unviewedFiles); ?></span>
            </a>
            <a href="#" class="tab" data-tab="requests">
                Group Requests
                <span class="tab-count"><?php echo count($groupRequests); ?></span>
            </a>
            <a href="#" class="tab" data-tab="topics">
                Topic Proposals
                <span class="tab-count"><?= (int) $topicProposalCount ?></span>
            </a>
        </div>

        <!-- =========================================================
             TAB 1 — ALL FILES
        ========================================================= -->
        <div class="tab-panel active" id="panel-files">

            <form method="get" action="teacher_review.php" class="filter-bar">

                <div class="filter-field search">
                    <label for="q">Search</label>
                    <input type="search" id="q" name="q"
                           placeholder="File, team, project, topic or person"
                           value="<?php echo htmlspecialchars($filterSearch); ?>">
                </div>

                <div class="filter-field date">
                    <label for="from">From date</label>
                    <input type="date" id="from" name="from"
                           value="<?php echo htmlspecialchars($filterFrom); ?>">
                </div>

                <div class="filter-field date">
                    <label for="to">To date</label>
                    <input type="date" id="to" name="to"
                           value="<?php echo htmlspecialchars($filterTo); ?>">
                </div>

                <div class="filter-field status">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all"      <?php echo $filterStatus==='all'?'selected':''; ?>>All Status</option>
                        <option value="Unviewed" <?php echo $filterStatus==='Unviewed'?'selected':''; ?>>Unviewed</option>
                        <option value="Accepted" <?php echo $filterStatus==='Accepted'?'selected':''; ?>>Accepted</option>
                        <option value="Rejected" <?php echo $filterStatus==='Rejected'?'selected':''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="teacher_review.php" class="btn-secondary">Reset</a>
                </div>

            </form>

            <div class="result-info">
                <span>
                    Showing <strong><?php echo $filteredCount; ?></strong>
                    of <?php echo $totalFiles; ?> files
                </span>
                <span class="range-text">
                    Date range: <?php echo htmlspecialchars($rangeText); ?>
                </span>
            </div>

            <div class="card">
                <table class="file-table">
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th style="width: 72px;">Action</th>
                    </tr>

                    <?php if ($filteredCount === 0): ?>
                        <tr>
                            <td colspan="7" class="muted" style="text-align: center; padding: 30px;">
                                No files match your filter.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($allFiles as $f): ?>
                        <?php $statusLower = strtolower($f['status']); ?>
                        <tr>
                            <td class="file-name-cell">
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                                   class="file-name-link"
                                   data-tooltip="Open the complete file review"
                                   title="<?php echo htmlspecialchars($f['file_name']); ?>">
                                    <?php echo htmlspecialchars($f['file_name']); ?>
                                </a>
                                <?php if (!empty($f['task_title'])): ?>
                                    <div class="file-task-label">Task: <?php echo htmlspecialchars($f['task_title']); ?></div>
                                <?php endif; ?>
                                <?php if ((int) $f['version_count'] > 1): ?>
                                    <div class="file-version-label">
                                        Version <?= (int) $f['version_number'] ?> of <?= (int) $f['version_count'] ?>
                                        <?= (int) $f['prior_rejections'] > 0 ? ' · Resubmitted' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($f['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($f['uploader_name']); ?></td>
                            <td class="muted"><?php echo date('Y-m-d', strtotime($f['uploaded_at'])); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['file_size']); ?></td>
                            <td>
                                <span class="status-pill <?php echo $statusLower; ?>"
                                      tabindex="0"
                                      data-tooltip="Review status: <?= htmlspecialchars($f['status']) ?>">
                                    <?php echo htmlspecialchars($f['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>

        <!-- =========================================================
             TAB 2 — UNVIEWED FILES
        ========================================================= -->
        <div class="tab-panel" id="panel-unviewed">

            <h2>Unviewed Files</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Task attachments that students uploaded but you have not reviewed yet.
                Open one to accept or reject it.
            </p>

            <div class="card">
                <table>
                    <tr>
                        <th>File</th>
                        <th>Team</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Size</th>
                        <th>Action</th>
                    </tr>

                    <?php if (count($unviewedFiles) === 0): ?>
                        <tr>
                            <td colspan="6" class="muted" style="text-align: center; padding: 30px;">
                                Nothing to review — all files have been viewed.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($unviewedFiles as $f): ?>
                        <tr>
                            <td class="file-name-cell">
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>"
                                   class="file-name-link"
                                   data-tooltip="Open the complete file review"
                                   title="<?php echo htmlspecialchars($f['file_name']); ?>">
                                    <?php echo htmlspecialchars($f['file_name']); ?>
                                </a>
                                <?php if (!empty($f['task_title'])): ?>
                                    <div class="file-task-label">Task: <?php echo htmlspecialchars($f['task_title']); ?></div>
                                <?php endif; ?>
                                <?php if ((int) $f['version_count'] > 1): ?>
                                    <div class="file-version-label">
                                        Version <?= (int) $f['version_number'] ?> of <?= (int) $f['version_count'] ?>
                                        <?= (int) $f['prior_rejections'] > 0 ? ' · Resubmitted' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($f['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($f['uploader_name']); ?></td>
                            <td class="muted"><?php echo date('Y-m-d', strtotime($f['uploaded_at'])); ?></td>
                            <td class="muted"><?php echo htmlspecialchars($f['file_size']); ?></td>
                            <td>
                                <a href="file_view.php?id=<?php echo (int)$f['id']; ?>" class="link">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        </div>

        <!-- =========================================================
             TAB 3 — GROUP REQUESTS
        ========================================================= -->
        <div class="tab-panel" id="panel-requests">

            <h2>Group Formation Requests</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Each request lists the group members. The team is only formed
                after the teacher accepts the request.
            </p>

            <?php if (count($groupRequests) === 0): ?>
                <div class="card">
                    <div class="muted" style="text-align: center; padding: 30px;">
                        No pending group requests.
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($groupRequests as $req): ?>
                <div class="request-card">

                    <div class="request-head">
                        <div>
                            <h3>
                                <?php echo htmlspecialchars($req['group_name']); ?>
                                ·
                                <span class="muted" style="font-weight: normal;">
                                    <?php echo htmlspecialchars($req['project_name']); ?>
                                </span>
                            </h3>
                            <div class="request-meta">
                                Request #<?php echo (int)$req['id']; ?> ·
                                Submitted by <?php echo htmlspecialchars($req['submitted_by_name']); ?> ·
                                <?php echo date('Y-m-d H:i', strtotime($req['submitted_at'])); ?>
                            </div>
                        </div>
                        <span class="pill">Pending Review</span>
                    </div>

                    <?php if (count($req['review_issues']) > 0): ?>
                        <div class="tb-alert tb-alert--error" style="margin:12px 17px;">
                            <?= htmlspecialchars(implode(' ', $req['review_issues'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="request-body request-members" id="requestMembers<?= (int) $req['id'] ?>">
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Student Number</th>
                                <th>Role</th>
                            </tr>
                            <?php foreach ($req['members'] as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['name']); ?></td>
                                    <td><?php echo htmlspecialchars($m['student_number']); ?></td>
                                    <td>
                                        <?php if ((int)$m['is_leader'] === 1): ?>
                                            <span class="pill">Leader</span>
                                        <?php else: ?>
                                            <span class="muted">Member</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <div class="request-foot">
                        <button type="button" class="member-toggle"
                                data-target="requestMembers<?= (int) $req['id'] ?>"
                                data-count="<?= count($req['members']) ?>"
                                aria-expanded="false">
                            View members (<?= count($req['members']) ?>)
                        </button>
                        <button type="button" class="btn-primary js-accept"
                                data-id="<?php echo (int)$req['id']; ?>"
                                data-name="<?php echo htmlspecialchars($req['group_name']); ?>"
                                <?= count($req['review_issues']) > 0 ? 'disabled' : '' ?>>
                            Accept
                        </button>
                        <button type="button" class="btn-ghost js-reject"
                                data-id="<?php echo (int)$req['id']; ?>"
                                data-name="<?php echo htmlspecialchars($req['group_name']); ?>">
                            Reject
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <!-- =========================================================
             TAB 4 — TOPIC PROPOSALS
        ========================================================= -->
        <div class="tab-panel" id="panel-topics">
            <h2>Topic Proposals</h2>
            <p class="muted" style="margin-bottom: 16px;">
                Review one submission per team. Rejections preserve the version history so the team leader can submit a revised proposal.
            </p>

            <?php if (count($topicProposals) === 0): ?>
                <div class="card">
                    <div class="muted" style="text-align: center; padding: 30px;">
                        No topic proposals are waiting for review.
                    </div>
                </div>
            <?php endif; ?>

            <div class="topic-review-list">
            <?php foreach ($topicProposals as $proposal): ?>
                <article class="topic-review-card">
                    <div class="topic-review-head">
                        <div>
                            <h3><?= htmlspecialchars($proposal['topic_title']) ?></h3>
                            <div class="topic-review-meta">
                                <?= $proposal['topic_mode'] === 'Self' ? 'Project title' : 'Topic proposal' ?> ·
                                <?= htmlspecialchars($proposal['project_name']) ?>
                                · <?= htmlspecialchars($proposal['group_name']) ?>
                                · Version <?= (int) $proposal['version'] ?>
                                · Submitted by <?= htmlspecialchars($proposal['submitted_by_name'] ?: '—') ?>
                                · <?= date('Y-m-d H:i', strtotime($proposal['submitted_at'])) ?>
                            </div>
                        </div>
                        <span class="pill pill-pending">Pending review</span>
                    </div>
                    <div class="topic-review-body">
                        <p class="topic-review-description">
                            <?= nl2br(htmlspecialchars((string) $proposal['topic_description'])) ?>
                        </p>

                        <?php if ($proposal['selection_summary']): ?>
                            <?php $selection = $proposal['selection_summary']; ?>
                            <div class="topic-selection-summary">
                                <span data-tooltip="Latest active team selections for this option"><?= (int) $selection['teams'] ?> teams selected this topic</span>
                                <span data-tooltip="Students in teams whose latest proposal uses this option"><?= (int) $selection['students'] ?> students selected this topic</span>
                                <span data-tooltip="Students whose topic was approved"><?= (int) $selection['approved_students'] ?> approved students</span>
                                <span data-tooltip="Students still awaiting approval"><?= (int) $selection['pending_students'] ?> pending students</span>
                            </div>
                        <?php endif; ?>

                        <div class="request-foot" style="padding:0; background:transparent; border-top:0;">
                            <button type="button" class="btn-primary js-topic-review"
                                    data-action="approve"
                                    data-id="<?= (int) $proposal['id'] ?>"
                                    data-title="<?= htmlspecialchars($proposal['topic_title']) ?>">
                                Approve
                            </button>
                            <button type="button" class="btn-ghost js-topic-review"
                                    data-action="reject"
                                    data-id="<?= (int) $proposal['id'] ?>"
                                    data-title="<?= htmlspecialchars($proposal['topic_title']) ?>">
                                Reject
                            </button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<!-- Accept Modal -->
<div class="modal-overlay" id="acceptModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="acceptModalTitle">
        <h3 id="acceptModalTitle">Accept Group Request</h3>
        <p>
            Accept the group request
            <strong id="acceptGroupName">—</strong>?<br>
            The team will be officially formed.
        </p>
        <form method="post" action="teacher_review.php#requests" id="acceptForm">
            <input type="hidden" name="action" value="accept">
            <input type="hidden" name="requestId" id="acceptRequestId" value="">
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="acceptCancel">Cancel</button>
                <button type="submit" class="btn-primary">Yes, Accept</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle">
        <h3 id="rejectModalTitle">Reject Group Request</h3>
        <p>
            Reject the group request
            <strong id="rejectGroupName">—</strong>?<br>
            Please provide a reason below.
        </p>
        <form method="post" action="teacher_review.php#requests" id="rejectForm">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="requestId" id="rejectRequestId" value="">

            <div class="comment-block" style="margin-bottom:18px;">
                <label for="rejectNote">Reason for rejection <span style="color:#d32f2f;">*</span></label>
                <textarea id="rejectNote" name="reviewNote" required
                          placeholder="Explain why this group request is rejected..."></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="rejectCancel">Cancel</button>
                <button type="submit" class="btn-primary">Yes, Reject</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="topicReviewModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="topicReviewHeading">
        <h3 id="topicReviewHeading">Review Project Title</h3>
        <p>
            <strong id="topicReviewName">—</strong>
        </p>
        <form method="post" action="teacher_review.php#topics" id="topicReviewForm">
            <input type="hidden" name="topicAction" id="topicReviewAction" value="approve">
            <input type="hidden" name="proposalId" id="topicReviewId" value="">

            <div class="comment-block" style="margin-bottom:18px;">
                <label for="topicReviewNote" id="topicReviewNoteLabel">Feedback</label>
                <div class="quick-replies" aria-label="Quick feedback templates">
                    <button type="button" class="quick-reply-btn" data-topic-reply="Please provide a clearer project scope and expected outcome.">Clarify scope</button>
                    <button type="button" class="quick-reply-btn" data-topic-reply="The proposed topic overlaps too much with an existing submission.">Topic overlap</button>
                    <button type="button" class="quick-reply-btn" data-topic-reply="Please explain the technical approach and required resources.">Technical detail</button>
                </div>
                <textarea id="topicReviewNote" name="topicReviewNote"
                          placeholder="Optional feedback"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="topicReviewCancel">Cancel</button>
                <button type="submit" class="btn-primary" id="topicReviewSubmit">Approve</button>
            </div>
        </form>
    </div>
</div>

<script src="teacher_ui.js?v=20261028b"></script>
<script>
    window.TEACHER_FLASH = <?= json_encode(
        $teacherFlash,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;

    function syncReviewFilterFields(tabId) {
        if (!window.matchMedia('(min-width: 901px)').matches) {
            document.querySelectorAll('.filter-field.date, .filter-field.status').forEach(function (field) {
                field.hidden = false;
            });
            return;
        }
        const filesOnly = tabId === 'files';
        document.querySelectorAll('.filter-field.date, .filter-field.status').forEach(function (field) {
            field.hidden = !filesOnly;
        });
    }

    // Tab switching
    document.querySelectorAll('.tab').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });

            tab.classList.add('active');
            const id = tab.getAttribute('data-tab');
            const panel = document.getElementById('panel-' + id);
            if (panel) { panel.classList.add('active'); }
            syncReviewFilterFields(id);

            history.replaceState(null, '', '#' + id);
        });
    });

    function activateTabFromHash() {
        const hash = window.location.hash.replace('#', '');
        if (!hash) return;
        const tabEl = document.querySelector('.tab[data-tab="' + hash + '"]');
        if (tabEl) { tabEl.click(); }
    }
    activateTabFromHash();
    window.addEventListener('hashchange', activateTabFromHash);

    document.querySelectorAll('.member-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const panel = document.getElementById(toggle.getAttribute('data-target'));
            if (!panel) return;
            const expanded = panel.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.textContent = expanded
                ? 'Hide members'
                : 'View members (' + toggle.getAttribute('data-count') + ')';
        });
    });

    // Move the existing filter bar above the tabs on desktop only.
    (function () {
        const media = window.matchMedia('(min-width: 901px)');
        const filter = document.querySelector('.filter-bar');
        const tabs = document.querySelector('.tabs');
        const filesPanel = document.getElementById('panel-files');
        if (!filter || !tabs || !filesPanel) return;

        function syncFilterPosition() {
            if (media.matches) {
                if (filter.nextElementSibling !== tabs) {
                    tabs.parentNode.insertBefore(filter, tabs);
                }
            } else if (filter.parentNode !== filesPanel) {
                filesPanel.insertBefore(filter, filesPanel.firstChild);
            }
            const activeTab = document.querySelector('.tab.active');
            syncReviewFilterFields(activeTab ? activeTab.getAttribute('data-tab') : 'files');
        }

        syncFilterPosition();
        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', syncFilterPosition);
        } else {
            media.addListener(syncFilterPosition);
        }
    })();

    // Accessible review modals
    (function () {
        const acceptModal  = document.getElementById('acceptModal');
        const rejectModal  = document.getElementById('rejectModal');
        const topicModal   = document.getElementById('topicReviewModal');
        const acceptName   = document.getElementById('acceptGroupName');
        const acceptReqId  = document.getElementById('acceptRequestId');
        const acceptCancel = document.getElementById('acceptCancel');
        const rejectName   = document.getElementById('rejectGroupName');
        const rejectReqId  = document.getElementById('rejectRequestId');
        const rejectCancel = document.getElementById('rejectCancel');
        const rejectNote   = document.getElementById('rejectNote');
        const topicName    = document.getElementById('topicReviewName');
        const topicAction  = document.getElementById('topicReviewAction');
        const topicId      = document.getElementById('topicReviewId');
        const topicCancel  = document.getElementById('topicReviewCancel');
        const topicSubmit  = document.getElementById('topicReviewSubmit');
        const topicNote    = document.getElementById('topicReviewNote');
        const topicHeading = document.getElementById('topicReviewHeading');

        document.querySelectorAll('.js-accept').forEach(function (btn) {
            btn.addEventListener('click', function () {
                acceptName.textContent = btn.getAttribute('data-name');
                acceptReqId.value      = btn.getAttribute('data-id');
                TeamBoxUI.openModal(acceptModal);
            });
        });
        acceptCancel.addEventListener('click', function () {
            TeamBoxUI.closeModal(acceptModal);
        });

        document.querySelectorAll('.js-reject').forEach(function (btn) {
            btn.addEventListener('click', function () {
                rejectName.textContent = btn.getAttribute('data-name');
                rejectReqId.value      = btn.getAttribute('data-id');
                rejectNote.value       = '';
                TeamBoxUI.openModal(rejectModal);
            });
        });
        rejectCancel.addEventListener('click', function () {
            TeamBoxUI.closeModal(rejectModal);
        });

        document.querySelectorAll('.js-topic-review').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-action');
                var rejecting = action === 'reject';
                topicName.textContent = btn.getAttribute('data-title');
                topicAction.value = action;
                topicId.value = btn.getAttribute('data-id');
                topicHeading.textContent = rejecting ? 'Reject Project Title' : 'Approve Project Title';
                topicSubmit.textContent = rejecting ? 'Reject' : 'Approve';
                topicNote.value = '';
                topicNote.required = rejecting;
                topicNote.placeholder = rejecting
                    ? 'Explain what the team should revise'
                    : 'Optional feedback';
                TeamBoxUI.openModal(topicModal);
            });
        });
        topicCancel.addEventListener('click', function () {
            TeamBoxUI.closeModal(topicModal);
        });
        document.querySelectorAll('[data-topic-reply]').forEach(function (button) {
            button.addEventListener('click', function () {
                topicNote.value = button.getAttribute('data-topic-reply') || '';
                topicNote.focus();
            });
        });

        [
            [document.getElementById('acceptForm'), acceptModal],
            [document.getElementById('rejectForm'), rejectModal],
            [document.getElementById('topicReviewForm'), topicModal]
        ].forEach(function (pair) {
            pair[0].addEventListener('submit', function () {
                TeamBoxUI.closeModal(pair[1]);
            });
        });
    })();
</script>

</body>
</html>
