<?php
$active = 'projects';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/project_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

// =========================================================
// Teacher only
// =========================================================
$userID = (int) $_SESSION['userID'];

$stmt = mysqli_prepare($conn, "
    SELECT t.id, u.username
    FROM `teachers` t
    INNER JOIN `users` u ON u.id = t.user_id
    WHERE t.user_id = ? AND u.role = 'teacher'
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$teacher = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$teacher) {
    header('Location: login.php');
    exit;
}
$teacherId = (int) $teacher['id'];

// =========================================================
// Mode: create / edit / duplicate
// =========================================================
$editId      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$duplicateId = isset($_GET['duplicate']) ? (int) $_GET['duplicate'] : 0;

$isEdit      = $editId > 0;
$isDuplicate = !$isEdit && $duplicateId > 0;
$sourceId    = $isEdit ? $editId : $duplicateId;

// =========================================================
// Classes taught by this teacher
// =========================================================
$classes = array();
$stmt = mysqli_prepare($conn, "
    SELECT
        cl.id,
        cl.class_code,
        cl.course_id,
        c.code AS course_code,
        c.name AS course_name,
        c.semester
    FROM `classes` cl
    INNER JOIN `courses` c ON c.id = cl.course_id
    WHERE cl.teacher_id = ?
    ORDER BY cl.class_code
");
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $classes[] = $row;
}
mysqli_stmt_close($stmt);

// All classes are available as cross-class partners.
$allClasses = array();
$stmt = mysqli_query($conn, "
    SELECT cl.id, cl.class_code, cl.course_id, c.code AS course_code
    FROM `classes` cl
    INNER JOIN `courses` c ON c.id = cl.course_id
    ORDER BY cl.class_code
");
while ($row = mysqli_fetch_assoc($stmt)) {
    $allClasses[] = $row;
}
mysqli_free_result($stmt);

// =========================================================
// Defaults
// =========================================================
$defaultPermissions = array('invite', 'rename', 'assign', 'submit');
$allowedCategories = array('CA', 'EA', 'Assignment', 'Final Year Project');
$allowedAssessmentScopes = array('CA', 'EA', 'This Semester', 'Full Year');
$allowedTopicModes = array('Fixed', 'Multi', 'Self');

$form = array(
        'name'                 => '',
        'class_id'             => count($classes) > 0 ? (int) $classes[0]['id'] : 0,
        'course_id'            => count($classes) > 0 ? (int) $classes[0]['course_id'] : 0,
        'category'             => '',
        'topic_mode'           => 'Fixed',
        'assessment_scope'     => '',
        'assessment_weight'    => '',
        'description'          => '',
        'requirements'         => '',
        'period_start'         => date('Y-m-d'),
        'period_end'           => date('Y-m-d', strtotime('+16 weeks')),
        'visibility'           => 'Course',
        'team_mode'            => 'Free',
        'team_deadline'        => date('Y-m-d', strtotime('+2 weeks')),
        'max_members'          => 4,
        'allow_cross_class'    => 0,
        'cross_class_ids'      => array(),
        'leader_permissions'   => $defaultPermissions,
        'post_deadline_action' => 'Lock',
        'reminder_days'        => 3,
        'allow_extension'      => 1,
        'ann_title'            => '',
        'ann_message'          => '',
        'ann_channels'         => array('inapp'),
        'ann_timing'           => 'now',
        'ann_scheduled_at'     => '',
);

$milestones = array(
        array('name' => '', 'start' => '', 'end' => ''),
);
$topics = array(
        array('id' => 0, 'title' => '', 'description' => ''),
);

$existingAttachments = array();
$errors  = array();
$notice  = '';
$success = '';

// =========================================================
// Load source project (edit / duplicate)
// =========================================================
if ($sourceId > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT *
        FROM `projects`
        WHERE id = ? AND supervisor_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $sourceId, $teacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $source = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$source) {
        $errors[] = 'Project not found, or you do not have permission to edit it.';
    } else {
        $form['name']                 = $source['name'];
        $form['class_id']             = (int) $source['class_id'];
        $form['course_id']            = (int) $source['course_id'];
        $form['category']             = in_array($source['category'], $allowedCategories, true)
                ? $source['category']
                : '';
        $form['topic_mode']            = in_array($source['topic_mode'], $allowedTopicModes, true)
                ? $source['topic_mode']
                : 'Fixed';

        // ★ 修正：?? → isset() ? :
        $form['assessment_scope']     = (string) (isset($source['assessment_scope']) ? $source['assessment_scope'] : '');

        $form['assessment_weight']    = $source['assessment_weight'] !== null
                ? (string) (int) $source['assessment_weight']
                : '';
        $form['description']          = $source['description'];
        $form['requirements']         = $source['requirements'];
        $form['period_start']         = $source['period_start'];
        $form['period_end']           = $source['period_end'];
        $form['visibility']           = $source['visibility'];
        $form['team_mode']            = $source['team_mode'];
        $form['team_deadline']        = $source['team_deadline'];
        $form['max_members']          = (int) $source['default_max_members'];
        $form['allow_cross_class']    = (int) $source['allow_cross_class'];
        $form['post_deadline_action'] = $source['post_deadline_action'];
        $form['reminder_days']        = (int) $source['reminder_days'];
        $form['allow_extension']      = (int) $source['allow_extension'];

        $savedPerms = json_decode((string) $source['leader_permissions'], true);
        if (is_array($savedPerms)) {
            $form['leader_permissions'] = $savedPerms;
        }

        if ($isDuplicate) {
            $form['name'] .= ' (Copy)';
            $form['ann_title']   = '';
            $form['ann_message'] = '';
        }

        // Milestones
        $stmt = mysqli_prepare($conn, "
            SELECT name, start_date, end_date
            FROM `milestones`
            WHERE project_id = ?
            ORDER BY start_date
        ");
        mysqli_stmt_bind_param($stmt, "i", $sourceId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $loadedMs = array();
        while ($row = mysqli_fetch_assoc($rs)) {
            $loadedMs[] = array(
                    'name'  => $row['name'],
                    'start' => $row['start_date'],
                    'end'   => $row['end_date'],
            );
        }
        mysqli_stmt_close($stmt);
        if (count($loadedMs) > 0) {
            $milestones = $loadedMs;
        }

        // Topic options. Duplicate mode copies them as new rows.
        $stmt = mysqli_prepare($conn, "
            SELECT id, title, description
            FROM `project_topics`
            WHERE project_id = ? AND is_active = 1
            ORDER BY position, id
        ");
        mysqli_stmt_bind_param($stmt, "i", $sourceId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $loadedTopics = array();
        while ($row = mysqli_fetch_assoc($rs)) {
            $loadedTopics[] = array(
                    'id' => $isDuplicate ? 0 : (int) $row['id'],
                    'title' => $row['title'],
                    'description' => (string) $row['description'],
            );
        }
        mysqli_stmt_close($stmt);
        if (count($loadedTopics) > 0) {
            $topics = $loadedTopics;
        }

        $form['cross_class_ids'] = array();
        $stmt = mysqli_prepare($conn, "
            SELECT class_id
            FROM `project_cross_classes`
            WHERE project_id = ?
            ORDER BY class_id
        ");
        mysqli_stmt_bind_param($stmt, "i", $sourceId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $form['cross_class_ids'][] = (int) $row['class_id'];
        }
        mysqli_stmt_close($stmt);

        // Attachments are only managed in edit mode, never copied on duplicate.
        if ($isEdit) {
            $stmt = mysqli_prepare($conn, "
                SELECT id, file_name, display_name, file_size, uploaded_at
                FROM `project_attachments`
                WHERE project_id = ?
                ORDER BY uploaded_at DESC
            ");
            mysqli_stmt_bind_param($stmt, "i", $sourceId);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($rs)) {
                $existingAttachments[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// =========================================================
// Handle submit
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : 'publish';
    $isDraft = ($action === 'draft');

    $form['name']                 = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $form['class_id']             = (int) (isset($_POST['classId']) ? $_POST['classId'] : 0);
    $form['course_id']            = 0;
    $selectedClassCode            = '';
    $selectedCourseCode           = '';
    foreach ($classes as $classRow) {
        if ((int) $classRow['id'] === $form['class_id']) {
            $form['course_id'] = (int) $classRow['course_id'];
            $selectedClassCode = $classRow['class_code'];
            $selectedCourseCode = $classRow['course_code'];
            break;
        }
    }
    $form['category']             = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $form['topic_mode']           = isset($_POST['topicMode']) ? trim($_POST['topicMode']) : 'Fixed';
    $form['assessment_scope']     = trim(isset($_POST['assessmentScope']) ? $_POST['assessmentScope'] : '');
    $form['assessment_weight']    = trim(isset($_POST['assessmentWeight']) ? $_POST['assessmentWeight'] : '');
    $form['description']          = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $form['requirements']         = trim(isset($_POST['requirements']) ? $_POST['requirements'] : '');
    $form['period_start']         = trim(isset($_POST['periodStart']) ? $_POST['periodStart'] : '');
    $form['period_end']           = trim(isset($_POST['periodEnd']) ? $_POST['periodEnd'] : '');
    $form['visibility']           = isset($_POST['visibility']) ? $_POST['visibility'] : 'Course';
    $form['team_mode']            = isset($_POST['teamMode']) ? trim($_POST['teamMode']) : '';
    $form['team_deadline']        = trim(isset($_POST['teamDeadline']) ? $_POST['teamDeadline'] : '');
    $form['max_members']          = (int) (isset($_POST['maxMembers']) ? $_POST['maxMembers'] : 0);
    $form['allow_cross_class']    = isset($_POST['allowCrossClass']) ? 1 : 0;
    $form['cross_class_ids']      = array();
    if ($form['allow_cross_class'] && isset($_POST['crossClassIds']) && is_array($_POST['crossClassIds'])) {
        foreach ($_POST['crossClassIds'] as $crossClassId) {
            $crossClassId = (int) $crossClassId;
            if ($crossClassId > 0) {
                $form['cross_class_ids'][] = $crossClassId;
            }
        }
        $form['cross_class_ids'] = array_values(array_unique($form['cross_class_ids']));
    }
    $form['post_deadline_action'] = isset($_POST['postDeadlineAction']) ? trim($_POST['postDeadlineAction']) : '';
    $form['reminder_days']        = (int) (isset($_POST['reminderDays']) ? $_POST['reminderDays'] : 3);
    $form['allow_extension']      = isset($_POST['allowExtension']) ? 1 : 0;

    $allowedPerms = array('invite', 'remove', 'rename', 'assign', 'submit');
    $postedPerms  = isset($_POST['leaderPerms']) && is_array($_POST['leaderPerms']) ? $_POST['leaderPerms'] : array();
    $form['leader_permissions'] = array_values(array_intersect($allowedPerms, $postedPerms));

    $form['ann_title']        = trim(isset($_POST['annTitle']) ? $_POST['annTitle'] : '');
    $form['ann_message']      = trim(isset($_POST['annMessage']) ? $_POST['annMessage'] : '');
    $form['ann_timing']       = isset($_POST['annTiming']) ? $_POST['annTiming'] : 'now';
    $form['ann_scheduled_at'] = trim(isset($_POST['annScheduledAt']) ? $_POST['annScheduledAt'] : '');

    // Announcements are delivered in-app only.
    $form['ann_channels'] = array('inapp');

    // Milestones from parallel arrays
    $msNames  = isset($_POST['msName'])  && is_array($_POST['msName'])  ? $_POST['msName']  : array();
    $msStarts = isset($_POST['msStart']) && is_array($_POST['msStart']) ? $_POST['msStart'] : array();
    $msEnds   = isset($_POST['msEnd'])   && is_array($_POST['msEnd'])   ? $_POST['msEnd']   : array();

    $milestones = array();
    $msCount = max(count($msNames), count($msStarts), count($msEnds));
    for ($i = 0; $i < $msCount; $i++) {
        $mName  = trim(isset($msNames[$i]) ? $msNames[$i] : '');
        $mStart = trim(isset($msStarts[$i]) ? $msStarts[$i] : '');
        $mEnd   = trim(isset($msEnds[$i]) ? $msEnds[$i] : '');
        // A row without a name is treated as an unused blank row.
        if ($mName === '') {
            continue;
        }
        $milestones[] = array('name' => $mName, 'start' => $mStart, 'end' => $mEnd);
    }
    if (count($milestones) === 0) {
        $milestones[] = array('name' => '', 'start' => '', 'end' => '');
    }

    // Topic allocation
    $postedTopicIds   = isset($_POST['topicId']) && is_array($_POST['topicId']) ? $_POST['topicId'] : array();
    $postedTopicNames = isset($_POST['topicTitle']) && is_array($_POST['topicTitle']) ? $_POST['topicTitle'] : array();
    $postedTopicDescs = isset($_POST['topicDescription']) && is_array($_POST['topicDescription']) ? $_POST['topicDescription'] : array();
    $topics = array();
    $topicCount = max(count($postedTopicIds), count($postedTopicNames), count($postedTopicDescs));
    for ($i = 0; $i < $topicCount; $i++) {
        $topicTitle = trim(isset($postedTopicNames[$i]) ? $postedTopicNames[$i] : '');
        if ($topicTitle === '') {
            continue;
        }
        $topics[] = array(
                'id' => isset($postedTopicIds[$i]) ? (int) $postedTopicIds[$i] : 0,
                'title' => $topicTitle,
                'description' => trim(isset($postedTopicDescs[$i]) ? $postedTopicDescs[$i] : ''),
        );
    }
    if (count($topics) === 0) {
        $topics[] = array('id' => 0, 'title' => '', 'description' => '');
    }

    if (in_array($form['topic_mode'], array('Multi', 'Self'), true)
            && $selectedClassCode !== ''
            && $selectedCourseCode !== ''
            && in_array($form['category'], $allowedCategories, true)) {
        $form['name'] = $selectedCourseCode . '_' . $selectedClassCode . '_' . $form['category'];
    }

    // Fixed topic and Project title are the same title.
    if ($form['topic_mode'] === 'Fixed' && $form['name'] !== '') {
        $fixedTopicId = count($topics) > 0 ? (int) $topics[0]['id'] : 0;
        $topics = array(array(
                'id' => $fixedTopicId,
                'title' => $form['name'],
                'description' => '',
        ));
    }

    // ---------- Validation ----------
    if ($form['name'] === '') {
        $errors[] = in_array($form['topic_mode'], array('Multi', 'Self'), true)
                ? 'The automatic project title could not be generated. Check the class and category.'
                : 'Project title is required.';
    } elseif (strlen($form['name']) > 150) {
        $errors[] = 'Project title must be 150 characters or fewer.';
    }

    if ($form['course_id'] <= 0) {
        $errors[] = 'Please choose a class that belongs to you.';
    }
    if (!in_array($form['category'], $allowedCategories, true)) {
        $errors[] = 'Please choose a valid project category.';
    }
    if (!in_array($form['topic_mode'], $allowedTopicModes, true)) {
        $errors[] = 'Please choose a valid topic allocation method.';
    }
    $namedTopics = array_values(array_filter($topics, function ($topic) {
        return trim((string) $topic['title']) !== '';
    }));
    if ($form['topic_mode'] === 'Multi' && count($namedTopics) === 0) {
        $errors[] = 'Add at least one topic option for multi-select mode.';
    }
    if ($form['topic_mode'] !== 'Self' && count($namedTopics) > 0) {
        foreach ($namedTopics as $topic) {
            if (mb_strlen($topic['title']) > 180) {
                $errors[] = 'Each topic title must be 180 characters or fewer.';
                break;
            }
        }
    }
    $hasAssessmentScope = ($form['assessment_scope'] !== '');
    $hasAssessmentWeight = ($form['assessment_weight'] !== '');
    if ($hasAssessmentScope !== $hasAssessmentWeight) {
        $errors[] = 'Assessment scope and percentage must be filled in together.';
    } elseif ($hasAssessmentScope) {
        if (!in_array($form['assessment_scope'], $allowedAssessmentScopes, true)) {
            $errors[] = 'Please choose a valid assessment scope.';
        }
        if (!preg_match('/^\d+$/', $form['assessment_weight'])
                || (int) $form['assessment_weight'] < 0
                || (int) $form['assessment_weight'] > 100) {
            $errors[] = 'Assessment percentage must be a whole number between 0 and 100.';
        }
    }

    if (!$isDraft) {
        if ($form['description'] === '' && $form['topic_mode'] !== 'Multi') {
            $errors[] = 'Project description is required before publishing.';
        }
        if ($form['period_start'] === '' || $form['period_end'] === '') {
            $errors[] = 'Project start date and deadline are required before publishing.';
        }
    }

    if ($form['period_start'] !== '' && $form['period_end'] !== ''
            && strtotime($form['period_end']) < strtotime($form['period_start'])) {
        $errors[] = 'Project deadline cannot be earlier than the start date.';
    }

    if ($form['team_deadline'] !== '') {
        if ($form['period_start'] !== '' && strtotime($form['team_deadline']) < strtotime($form['period_start'])) {
            $errors[] = 'Team formation deadline cannot be earlier than the project start date.';
        }
        if ($form['period_end'] !== '' && strtotime($form['team_deadline']) > strtotime($form['period_end'])) {
            $errors[] = 'Team formation deadline cannot be after the project deadline.';
        }
    } else {
        $errors[] = 'Team formation deadline is required.';
    }

    if ($form['max_members'] < 2 || $form['max_members'] > 10) {
        $errors[] = 'Maximum team size must be between 2 and 10.';
    }

    if (!in_array($form['visibility'], array('All', 'Course'), true)) {
        $form['visibility'] = 'Course';
    }
    if (!in_array($form['team_mode'], array('Free', 'Random', 'Teacher_Assigned'), true)) {
        $errors[] = 'Please choose a valid team formation method.';
    }
    if (!in_array($form['post_deadline_action'], array('Lock', 'Notify'), true)) {
        $errors[] = 'Please choose what happens after the team deadline.';
    }
    $allClassIds = array();
    foreach ($allClasses as $classOption) {
        $allClassIds[] = (int) $classOption['id'];
    }
    $form['cross_class_ids'] = array_values(array_intersect(
            $form['cross_class_ids'],
            $allClassIds
    ));
    $form['cross_class_ids'] = array_values(array_filter(
            $form['cross_class_ids'],
            function ($classId) use ($form) {
                return (int) $classId !== (int) $form['class_id'];
            }
    ));
    if (!$form['allow_cross_class']) {
        $form['cross_class_ids'] = array();
    } elseif (count($form['cross_class_ids']) === 0) {
        $errors[] = 'Select at least one partner class for cross-class teams.';
    }
    if ($form['reminder_days'] < 0 || $form['reminder_days'] > 60) {
        $errors[] = 'Reminder days must be between 0 and 60.';
    }

    foreach ($milestones as $idx => $m) {
        if ($m['name'] === '') {
            continue;
        }
        if ($m['start'] === '' || $m['end'] === '') {
            $errors[] = 'Milestone #' . ($idx + 1) . ': start date and due date are required.';
            continue;
        }
        if (strtotime($m['end']) < strtotime($m['start'])) {
            $errors[] = 'Milestone #' . ($idx + 1) . ': due date cannot be earlier than its start date.';
        }
        if ($form['period_start'] !== '' && strtotime($m['start']) < strtotime($form['period_start'])) {
            $errors[] = 'Milestone #' . ($idx + 1) . ': start date is before the project start.';
        }
        if ($form['period_end'] !== '' && strtotime($m['end']) > strtotime($form['period_end'])) {
            $errors[] = 'Milestone #' . ($idx + 1) . ': due date is after the project deadline.';
        }
    }

    $hasAnnouncement = ($form['ann_title'] !== '' || $form['ann_message'] !== '');
    if ($hasAnnouncement) {
        if ($form['ann_title'] === '' || $form['ann_message'] === '') {
            $errors[] = 'Announcement title and message must both be filled in.';
        }
        if ($form['ann_timing'] === 'scheduled') {
            if ($form['ann_scheduled_at'] === '') {
                $errors[] = 'Please choose a date and time for the scheduled announcement.';
            } elseif (strtotime($form['ann_scheduled_at']) <= time()) {
                $errors[] = 'The scheduled announcement time must be in the future.';
            }
        }
    }

    // One class may only have one active Final Year Project workspace.
    if (!$isDraft
            && $form['category'] === 'Final Year Project'
            && $form['class_id'] > 0) {
        $excludeProjectId = $isEdit ? $editId : 0;
        $stmt = mysqli_prepare($conn, "
            SELECT id
            FROM `projects`
            WHERE class_id = ?
              AND category = 'Final Year Project'
              AND is_draft = 0
              AND status NOT IN ('Completed', 'Archived')
              AND id <> ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $form['class_id'], $excludeProjectId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $conflict = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);
        if ($conflict) {
            $errors[] = 'This class already has an active Final Year Project. Complete or archive it before publishing another.';
        }
    }

    // ---------- Save ----------
    if (count($errors) === 0) {

        $leaderPermissions = json_encode($form['leader_permissions']);
        $desc              = ($form['description'] !== '' && $form['topic_mode'] !== 'Multi')
                ? $form['description']
                : null;
        $reqs              = ($form['requirements'] !== '') ? $form['requirements'] : null;
        $startDate         = ($form['period_start'] !== '') ? $form['period_start'] : null;
        $endDate           = ($form['period_end'] !== '') ? $form['period_end'] : null;
        $teamDeadline      = ($form['team_deadline'] !== '') ? $form['team_deadline'] : null;
        $category          = ($form['category'] !== '') ? $form['category'] : null;
        $draftFlag         = $isDraft ? 1 : 0;
        $courseId          = $form['course_id'];
        $classId           = $form['class_id'];

        if ($isEdit) {
            $stmt = mysqli_prepare($conn, "
                UPDATE `projects`
                SET name = ?, category = ?, course_id = ?, class_id = ?, period_start = ?, period_end = ?,
                    description = ?, requirements = ?, team_mode = ?, team_deadline = ?,
                    allow_cross_class = ?, default_max_members = ?, leader_permissions = ?,
                    post_deadline_action = ?, visibility = ?, reminder_days = ?,
                    allow_extension = ?, is_draft = ?
                WHERE id = ? AND supervisor_id = ?
            ");
            mysqli_stmt_bind_param(
                    $stmt,
                    "ssiissssssiisssiiiii",
                    $form['name'], $category, $courseId, $classId, $startDate, $endDate,
                    $desc, $reqs, $form['team_mode'], $teamDeadline,
                    $form['allow_cross_class'], $form['max_members'], $leaderPermissions,
                    $form['post_deadline_action'], $form['visibility'], $form['reminder_days'],
                    $form['allow_extension'], $draftFlag, $editId, $teacherId
            );
            if (!mysqli_stmt_execute($stmt)) {
                $errors[] = 'Could not update the project: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
            $projectId = $editId;
        } else {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO `projects`
                    (name, category, course_id, class_id, supervisor_id, period_start, period_end,
                     status, progress, description, requirements, team_mode, team_deadline,
                     allow_cross_class, default_max_members, leader_permissions,
                     post_deadline_action, visibility, reminder_days, allow_extension, is_draft)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Recruiting', 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param(
                    $stmt,
                    "ssiiissssssiisssiii",
                    $form['name'], $category, $courseId, $classId, $teacherId, $startDate, $endDate,
                    $desc, $reqs, $form['team_mode'], $teamDeadline,
                    $form['allow_cross_class'], $form['max_members'], $leaderPermissions,
                    $form['post_deadline_action'], $form['visibility'], $form['reminder_days'],
                    $form['allow_extension'], $draftFlag
            );
            if (!mysqli_stmt_execute($stmt)) {
                $errors[] = 'Could not create the project: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
            $projectId = (int) mysqli_insert_id($conn);
        }

        if (count($errors) === 0 && $projectId > 0) {
            $assessmentScope = $form['assessment_scope'] !== '' ? $form['assessment_scope'] : null;
            $assessmentWeight = $form['assessment_weight'] !== ''
                    ? (int) $form['assessment_weight']
                    : null;
            $weightStmt = mysqli_prepare($conn, "
                UPDATE `projects`
                SET assessment_scope = ?, assessment_weight = ?
                WHERE id = ? AND supervisor_id = ?
            ");
            mysqli_stmt_bind_param(
                    $weightStmt,
                    "sdii",
                    $assessmentScope,
                    $assessmentWeight,
                    $projectId,
                    $teacherId
            );
            if (!mysqli_stmt_execute($weightStmt)) {
                $errors[] = 'Could not save the assessment weighting: ' . mysqli_stmt_error($weightStmt);
            }
            mysqli_stmt_close($weightStmt);

            $topicModeStmt = mysqli_prepare($conn, "
                UPDATE `projects`
                SET topic_mode = ?
                WHERE id = ? AND supervisor_id = ?
            ");
            mysqli_stmt_bind_param(
                    $topicModeStmt,
                    "sii",
                    $form['topic_mode'],
                    $projectId,
                    $teacherId
            );
            if (!mysqli_stmt_execute($topicModeStmt)) {
                $errors[] = 'Could not save the topic allocation method: ' . mysqli_stmt_error($topicModeStmt);
            }
            mysqli_stmt_close($topicModeStmt);

            // ---------- Milestones: replace ----------
            $del = mysqli_prepare($conn, "DELETE FROM `milestones` WHERE project_id = ?");
            mysqli_stmt_bind_param($del, "i", $projectId);
            mysqli_stmt_execute($del);
            mysqli_stmt_close($del);

            $ins = mysqli_prepare($conn, "
                INSERT INTO `milestones` (project_id, name, start_date, end_date, status, progress)
                VALUES (?, ?, ?, ?, 'Pending', 0)
            ");
            foreach ($milestones as $m) {
                if ($m['name'] === '') {
                    continue;
                }
                mysqli_stmt_bind_param($ins, "isss", $projectId, $m['name'], $m['start'], $m['end']);
                mysqli_stmt_execute($ins);
            }
            mysqli_stmt_close($ins);

            // ---------- Topic options: deactivate and synchronize ----------
            $deactivateTopics = mysqli_prepare($conn, "
                UPDATE `project_topics`
                SET is_active = 0
                WHERE project_id = ?
            ");
            mysqli_stmt_bind_param($deactivateTopics, "i", $projectId);
            mysqli_stmt_execute($deactivateTopics);
            mysqli_stmt_close($deactivateTopics);

            if ($form['topic_mode'] !== 'Self') {
                $updateTopic = mysqli_prepare($conn, "
                    UPDATE `project_topics`
                    SET title = ?, description = ?, position = ?, is_active = 1
                    WHERE id = ? AND project_id = ?
                ");
                $insertTopic = mysqli_prepare($conn, "
                    INSERT INTO `project_topics`
                        (project_id, title, description, position, is_active)
                    VALUES (?, ?, ?, ?, 1)
                ");

                foreach ($topics as $position => $topic) {
                    if (trim((string) $topic['title']) === '') {
                        continue;
                    }
                    $topicDescription = $topic['description'] !== ''
                            ? $topic['description']
                            : null;
                    $topicId = (int) $topic['id'];
                    if ($topicId > 0) {
                        mysqli_stmt_bind_param(
                                $updateTopic,
                                "ssiii",
                                $topic['title'],
                                $topicDescription,
                                $position,
                                $topicId,
                                $projectId
                        );
                        mysqli_stmt_execute($updateTopic);
                    } else {
                        mysqli_stmt_bind_param(
                                $insertTopic,
                                "issi",
                                $projectId,
                                $topic['title'],
                                $topicDescription,
                                $position
                        );
                        mysqli_stmt_execute($insertTopic);
                    }
                }
                mysqli_stmt_close($updateTopic);
                mysqli_stmt_close($insertTopic);
            }

            // ---------- Cross-class partner classes ----------
            $clearCrossClasses = mysqli_prepare($conn, "
                DELETE FROM `project_cross_classes`
                WHERE project_id = ?
            ");
            mysqli_stmt_bind_param($clearCrossClasses, "i", $projectId);
            mysqli_stmt_execute($clearCrossClasses);
            mysqli_stmt_close($clearCrossClasses);

            if ($form['allow_cross_class'] && count($form['cross_class_ids']) > 0) {
                $insertCrossClass = mysqli_prepare($conn, "
                    INSERT INTO `project_cross_classes` (project_id, class_id)
                    VALUES (?, ?)
                ");
                foreach ($form['cross_class_ids'] as $crossClassId) {
                    mysqli_stmt_bind_param($insertCrossClass, "ii", $projectId, $crossClassId);
                    mysqli_stmt_execute($insertCrossClass);
                }
                mysqli_stmt_close($insertCrossClass);
            }

            // ---------- Attachments ----------
            $uploadDir = __DIR__ . '/uploads/project_attachments';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            // Remove selected attachments (edit mode only)
            if ($isEdit && isset($_POST['removeAttachment']) && is_array($_POST['removeAttachment'])) {
                $delAtt = mysqli_prepare($conn, "
                    DELETE FROM `project_attachments`
                    WHERE id = ? AND project_id = ?
                ");
                foreach ($_POST['removeAttachment'] as $attId) {
                    $attId = (int) $attId;
                    if ($attId <= 0) {
                        continue;
                    }
                    $stmt = mysqli_prepare($conn, "
                        SELECT stored_name
                        FROM `project_attachments`
                        WHERE id = ? AND project_id = ?
                        LIMIT 1
                    ");
                    mysqli_stmt_bind_param($stmt, "ii", $attId, $projectId);
                    mysqli_stmt_execute($stmt);
                    $rs = mysqli_stmt_get_result($stmt);
                    $att = mysqli_fetch_assoc($rs);
                    mysqli_stmt_close($stmt);

                    if ($att) {
                        $path = $uploadDir . '/' . basename($att['stored_name']);
                        if (is_file($path)) {
                            @unlink($path);
                        }
                        mysqli_stmt_bind_param($delAtt, "ii", $attId, $projectId);
                        mysqli_stmt_execute($delAtt);
                    }
                }
                mysqli_stmt_close($delAtt);
            }

            // New uploads
            if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                $allowedExt = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'png', 'jpg', 'jpeg');
                $maxBytes   = 5 * 1024 * 1024;
                $count      = count($_FILES['attachments']['name']);

                $insAtt = mysqli_prepare($conn, "
                    INSERT INTO `project_attachments`
                        (project_id, file_name, display_name, stored_name, file_size, file_type)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                for ($i = 0; $i < $count; $i++) {
                    if ((int) $_FILES['attachments']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    if ((int) $_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                        $errors[] = 'Attachment "' . $_FILES['attachments']['name'][$i] . '" could not be uploaded.';
                        continue;
                    }

                    $origName = $_FILES['attachments']['name'][$i];
                    $tmpPath  = $_FILES['attachments']['tmp_name'][$i];
                    $size     = (int) $_FILES['attachments']['size'][$i];
                    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if ($size > $maxBytes) {
                        $errors[] = 'Attachment "' . $origName . '" is larger than 5 MB.';
                        continue;
                    }
                    if (!in_array($ext, $allowedExt, true)) {
                        $errors[] = 'Attachment "' . $origName . '" has a file type that is not allowed.';
                        continue;
                    }

                    $storedName = 'project_' . $projectId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    $targetPath = $uploadDir . '/' . $storedName;

                    if (!move_uploaded_file($tmpPath, $targetPath)) {
                        $errors[] = 'Could not save attachment "' . $origName . '".';
                        continue;
                    }

                    $displayName = $origName;
                    $sizeLabel   = formatBytes($size);
                    $mimeType    = function_exists('mime_content_type') ? @mime_content_type($targetPath) : $ext;

                    mysqli_stmt_bind_param(
                            $insAtt,
                            "isssss",
                            $projectId, $origName, $displayName, $storedName, $sizeLabel, $mimeType
                    );
                    mysqli_stmt_execute($insAtt);
                }
                mysqli_stmt_close($insAtt);
            }

            // ---------- Announcement ----------
            if ($hasAnnouncement) {
                $channels     = implode(',', $form['ann_channels']);
                $annStatus    = ($form['ann_timing'] === 'scheduled') ? 'Scheduled' : 'Sent';
                $scheduledAt  = ($form['ann_timing'] === 'scheduled') ? $form['ann_scheduled_at'] : null;
                $sentAt       = ($annStatus === 'Sent') ? date('Y-m-d H:i:s') : null;

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO `announcements`
                        (teacher_id, title, message, target_project_id, channels, status, scheduled_at, sent_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param(
                        $stmt, "ississss",
                        $teacherId, $form['ann_title'], $form['ann_message'], $projectId,
                        $channels, $annStatus, $scheduledAt, $sentAt
                );
                $annOk = mysqli_stmt_execute($stmt);
                $annId = (int) mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if ($annOk && $annId > 0) {
                    $stmt = mysqli_prepare($conn, "
                        INSERT INTO `announcement_targets` (announcement_id, target_type, target_id)
                        VALUES (?, 'course', ?)
                    ");
                    mysqli_stmt_bind_param($stmt, "ii", $annId, $courseId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    if ($annStatus === 'Sent') {
                        $dispatch = projectDispatchAnnouncement($conn, $annId);
                        $notice = 'Announcement sent to ' . $dispatch['inapp'] . ' student(s) via in-app notifications.';
                    } else {
                        $notice = 'Announcement scheduled for ' . date('d M Y H:i', strtotime($scheduledAt)) . '.';
                    }
                }
            }

            if (count($errors) === 0) {
                $redirect = 'project_detail.php?id=' . $projectId . '&msg=';
                $redirect .= $isDraft ? 'draft' : ($isEdit ? 'updated' : 'created');
                if ($notice !== '') {
                    $_SESSION['project_notice'] = $notice;
                }
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
}

// Rebuild the visible milestone list if there was a validation error.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // already loaded above
}

$pageTitle = $isEdit ? 'Edit Project' : ($isDuplicate ? 'Duplicate Project' : 'Create Project');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_project_create.css?v=<?= filemtime('assets/css/teacher_project_create.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content pf-wrap">

        <a href="teacher_projects.php" class="back">← Back to Projects</a>

        <div class="pf-head">
            <div>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
            </div>
        </div>

        <?php if (count($errors) > 0): ?>
            <div class="pf-error">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="teacher_project_create.php<?= $isEdit ? '?id=' . (int) $editId : '' ?>" enctype="multipart/form-data">
            <?php if ($isEdit): ?>
                <input type="hidden" name="projectId" value="<?= (int) $editId ?>">
            <?php endif; ?>

            <!-- ================= Basics ================= -->
            <div class="pf-section">
                <h2>Project Basics</h2>
                <div class="pf-section-desc">What students will see, and which class and subject the project belongs to.</div>

                <div class="pf-grid">
                    <div class="pf-field full">
                        <label for="topicMode">Allocation method <span class="req">*</span></label>
                        <select id="topicMode" name="topicMode" required>
                            <option value="Fixed" <?= $form['topic_mode'] === 'Fixed' ? 'selected' : '' ?>>Fixed topic — Project title is the topic title</option>
                            <option value="Multi" <?= $form['topic_mode'] === 'Multi' ? 'selected' : '' ?>>Multi-select topics — teams choose one and request approval</option>
                            <option value="Self" <?= $form['topic_mode'] === 'Self' ? 'selected' : '' ?>>Self-selected topic — each team proposes its own title</option>
                        </select>
                        <span class="pf-help" id="topicModeHelp"></span>
                    </div>

                    <div class="pf-field" id="manualProjectTitleField"
                            <?= $form['topic_mode'] !== 'Fixed' ? 'hidden' : '' ?>>
                        <label for="name">Project title <span class="req">*</span></label>
                        <input type="text" id="name" name="name" maxlength="150"
                               value="<?= htmlspecialchars($form['name']) ?>"
                               placeholder="e.g. Smart Campus Assistant">
                    </div>

                    <div class="pf-field auto-project-title" id="autoProjectTitleField"
                            <?= $form['topic_mode'] !== 'Self' ? 'hidden' : '' ?>>
                        <label>Project title <span class="req">*</span></label>
                        <div id="autoProjectTitlePreview" class="auto-project-title-value">
                            Select a class and Category to generate the title.
                        </div>
                        <span class="pf-help">Generated automatically as CourseCode_ClassCode_Category.</span>
                    </div>

                    <div class="pf-field <?= $form['topic_mode'] === 'Multi' ? 'full' : '' ?>"
                         id="assessmentWeightField">
                        <label for="assessmentScope">Assessment Weight</label>
                        <div class="assessment-weight-row">
                            <select id="assessmentScope" name="assessmentScope"
                                    class="<?= $form['assessment_scope'] === '' ? 'is-placeholder' : '' ?>">
                                <option value=""
                                        <?= $form['assessment_scope'] === '' ? 'selected' : '' ?>
                                        disabled hidden>e.g. CA</option>
                                <?php foreach ($allowedAssessmentScopes as $scope): ?>
                                    <option value="<?= htmlspecialchars($scope) ?>"
                                            <?= $form['assessment_scope'] === $scope ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($scope) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="assessment-percent">
                                <input type="number" id="assessmentWeight" name="assessmentWeight"
                                       min="0" max="100" step="1" inputmode="numeric"
                                       value="<?= htmlspecialchars($form['assessment_weight']) ?>"
                                       placeholder="e.g. 20">
                                <span>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="pf-field full" id="projectDescriptionField"
                            <?= $form['topic_mode'] === 'Multi' ? 'hidden' : '' ?>>
                        <label for="description">Project description <span class="req">*</span></label>
                        <textarea id="description" name="description"
                                  placeholder="Describe the project idea, the problem it solves and what students will build."><?= htmlspecialchars($form['description']) ?></textarea>
                        <span class="pf-help">Shown to students on the project page.</span>
                    </div>

                    <div class="pf-field full topic-editor-field" id="topicEditor" <?= $form['topic_mode'] !== 'Multi' ? 'hidden' : '' ?>>
                        <div id="topicRows">
                            <?php foreach ($topics as $topic): ?>
                                <div class="pf-topic-row">
                                    <input type="hidden" name="topicId[]" value="<?= (int) $topic['id'] ?>">
                                    <input type="text" name="topicTitle[]" maxlength="180"
                                           placeholder="Topic title" value="<?= htmlspecialchars($topic['title']) ?>">
                                    <textarea name="topicDescription[]"
                                              placeholder="Short description students will see"><?= htmlspecialchars($topic['description']) ?></textarea>
                                    <button type="button" class="pf-icon-btn js-topic-remove" title="Remove topic">×</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="pf-btn secondary" id="addTopic">+ Add topic option</button>
                    </div>

                    <div class="pf-field">
                        <label for="classId">Class <span class="req">*</span></label>
                        <select id="classId" name="classId" required>
                            <?php if (count($classes) === 0): ?>
                                <option value="0">No classes assigned to you</option>
                            <?php endif; ?>
                            <?php foreach ($classes as $classRow): ?>
                                <option value="<?= (int) $classRow['id'] ?>"
                                        data-class-code="<?= htmlspecialchars($classRow['class_code']) ?>"
                                        data-course-code="<?= htmlspecialchars($classRow['course_code']) ?>"
                                        <?= (int) $classRow['id'] === (int) $form['class_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classRow['class_code']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (count($classes) === 0): ?>
                            <span class="pf-help">Ask an administrator to assign a class to your teacher account first.</span>
                        <?php else: ?>
                            <span class="pf-help">A project belongs to one class and one subject.</span>
                        <?php endif; ?>
                    </div>

                    <div class="pf-field">
                        <label for="category">Category <span class="req">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($allowedCategories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>"
                                        <?= $form['category'] === $category ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pf-field">
                        <label for="periodStart">Project start date <span class="req">*</span></label>
                        <input type="date" id="periodStart" name="periodStart" value="<?= htmlspecialchars($form['period_start']) ?>">
                    </div>

                    <div class="pf-field">
                        <label for="periodEnd">Project deadline <span class="req">*</span></label>
                        <input type="date" id="periodEnd" name="periodEnd" value="<?= htmlspecialchars($form['period_end']) ?>">
                    </div>

                    <div class="pf-topic-note full" id="selfTopicNote" <?= $form['topic_mode'] !== 'Self' ? 'hidden' : '' ?>>
                        Each team leader submits a versioned Project title for teacher approval. Multiple Self-selected projects may use the same generated workspace title.
                    </div>

                </div>
            </div>

            <!-- ================= Team formation ================= -->
            <div class="pf-section">
                <h2>Team Formation</h2>
                <div class="pf-section-desc">How teams are formed, distributed and handled after the deadline.</div>

                <div class="pf-grid">
                    <div class="pf-field">
                        <label for="teamMode">Formation method <span class="req">*</span></label>
                        <select id="teamMode" name="teamMode" required>
                            <option value="Free" <?= $form['team_mode'] === 'Free' ? 'selected' : '' ?>>Free — students form their own teams</option>
                            <option value="Random" <?= $form['team_mode'] === 'Random' ? 'selected' : '' ?>>Random assignment</option>
                            <option value="Teacher_Assigned" <?= $form['team_mode'] === 'Teacher_Assigned' ? 'selected' : '' ?>>Teacher assigns members</option>
                        </select>
                    </div>

                    <div class="pf-field">
                        <label for="teamDeadline">Team formation deadline <span class="req">*</span></label>
                        <input type="date" id="teamDeadline" name="teamDeadline"
                               value="<?= htmlspecialchars($form['team_deadline']) ?>" required>
                    </div>

                    <div class="pf-field">
                        <label for="maxMembers">Maximum team size <span class="req">*</span></label>
                        <select id="maxMembers" name="maxMembers" required>
                            <?php for ($i = 2; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= (int) $form['max_members'] === $i ? 'selected' : '' ?>><?= $i ?> members</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="pf-field">
                        <label for="postDeadlineAction">After the team deadline <span class="req">*</span></label>
                        <select id="postDeadlineAction" name="postDeadlineAction" required>
                            <option value="Lock" <?= $form['post_deadline_action'] === 'Lock' ? 'selected' : '' ?>>Lock Team — requires teacher approval</option>
                            <option value="Notify" <?= $form['post_deadline_action'] === 'Notify' ? 'selected' : '' ?>>Notify the teacher only</option>
                        </select>
                    </div>

                    <div class="pf-field full">
                        <label class="pf-inline">
                            <input type="checkbox" id="allowCrossClass" name="allowCrossClass" value="1"
                                    <?= (int) $form['allow_cross_class'] === 1 ? 'checked' : '' ?>>
                            Allow cross-class teams
                        </label>
                        <span class="pf-help">Select which partner classes may join teams from the home class.</span>

                        <div id="crossClassOptions" <?= (int) $form['allow_cross_class'] !== 1 ? 'hidden' : '' ?>>
                            <div class="cross-class-grid">
                                <?php foreach ($allClasses as $classOption): ?>
                                    <?php
                                    $optionClassId = (int) $classOption['id'];
                                    $isHomeClass = $optionClassId === (int) $form['class_id'];
                                    $isPartnerClass = in_array($optionClassId, $form['cross_class_ids'], true);
                                    ?>
                                    <label class="cross-class-option <?= $isHomeClass ? 'is-home' : '' ?>">
                                        <input type="checkbox" name="crossClassIds[]"
                                               value="<?= $optionClassId ?>"
                                               data-cross-class-id="<?= $optionClassId ?>"
                                                <?= $isHomeClass || $isPartnerClass ? 'checked' : '' ?>
                                                <?= $isHomeClass ? 'disabled' : '' ?>>
                                        <span><?= htmlspecialchars($classOption['class_code']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= Milestones ================= -->
            <div class="pf-section">
                <h2>Milestones &amp; Phases</h2>
                <div class="pf-section-desc">Add as many phases as you need. Dates must stay inside the project period.</div>

                <div id="msRows">
                    <?php foreach ($milestones as $m): ?>
                        <div class="pf-ms-row">
                            <input type="text" name="msName[]" placeholder="Milestone name" value="<?= htmlspecialchars($m['name']) ?>">
                            <input type="date" name="msStart[]" value="<?= htmlspecialchars($m['start']) ?>" title="Start date">
                            <input type="date" name="msEnd[]" value="<?= htmlspecialchars($m['end']) ?>" title="Due date">
                            <button type="button" class="pf-icon-btn js-ms-remove" title="Remove milestone">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="pf-btn secondary" id="addMilestone">+ Add milestone</button>

            </div>

            <!-- ================= Announcement ================= -->
            <details class="pf-section pf-disclosure">
                <summary>
                    <div>
                        <h2>Announcement</h2>
                        <div class="pf-section-desc">Write it here, preview it, then send now or schedule it.</div>
                    </div>
                    <span class="optional-badge">Optional</span>
                </summary>
                <div class="pf-disclosure-body">
                    <div class="pf-grid">
                        <div>
                            <div class="pf-field">
                                <label for="annTitle">Announcement title</label>
                                <input type="text" id="annTitle" name="annTitle" maxlength="200"
                                       value="<?= htmlspecialchars($form['ann_title']) ?>"
                                       placeholder="e.g. Team formation opens on Monday">
                            </div>

                            <div class="pf-field">
                                <label for="annMessage">Message</label>
                                <textarea id="annMessage" name="annMessage"
                                          placeholder="Write the announcement students will receive..."><?= htmlspecialchars($form['ann_message']) ?></textarea>
                            </div>

                            <div class="pf-grid">
                                <div class="pf-field">
                                    <label for="annTiming">Send timing</label>
                                    <select id="annTiming" name="annTiming">
                                        <option value="now" <?= $form['ann_timing'] === 'now' ? 'selected' : '' ?>>Send immediately</option>
                                        <option value="scheduled" <?= $form['ann_timing'] === 'scheduled' ? 'selected' : '' ?>>Schedule for later</option>
                                    </select>
                                </div>
                                <div class="pf-field">
                                    <label for="annScheduledAt">Scheduled date &amp; time</label>
                                    <input type="datetime-local" id="annScheduledAt" name="annScheduledAt"
                                           value="<?= htmlspecialchars($form['ann_scheduled_at']) ?>">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="pf-preview">
                                <div class="pf-preview-head">Announcement preview</div>
                                <div class="pf-preview-body">
                                    <div class="pf-preview-title" id="pvTitle">Your announcement title</div>
                                    <div class="pf-preview-message" id="pvMessage">Your message will appear here as students see it.</div>
                                    <div class="pf-preview-meta">
                                        <span class="pf-chip" id="pvChannels">In-app notification</span>
                                        <span id="pvTiming">Sent immediately</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </details>

            <!-- ================= Advanced settings ================= -->
            <details class="pf-section pf-disclosure">
                <summary>
                    <div>
                        <h2>Advanced Settings</h2>
                        <div class="pf-section-desc">Optional requirements, reminders, permissions and attachments.</div>
                    </div>
                    <span class="optional-badge">Optional</span>
                </summary>
                <div class="pf-disclosure-body">
                    <div class="pf-grid">
                        <div class="pf-field full">
                            <label for="requirements">Requirements &amp; deliverables</label>
                            <textarea id="requirements" name="requirements"
                                      placeholder="Required features, expected deliverables, assessment criteria, technical constraints..."><?= htmlspecialchars($form['requirements']) ?></textarea>
                        </div>

                        <div class="pf-field full">
                            <label>Team leader permissions</label>
                            <div class="pf-checks">
                                <label><input type="checkbox" name="leaderPerms[]" value="invite" <?= in_array('invite', $form['leader_permissions'], true) ? 'checked' : '' ?>> Invite members</label>
                                <label><input type="checkbox" name="leaderPerms[]" value="remove" <?= in_array('remove', $form['leader_permissions'], true) ? 'checked' : '' ?>> Remove members</label>
                                <label><input type="checkbox" name="leaderPerms[]" value="rename" <?= in_array('rename', $form['leader_permissions'], true) ? 'checked' : '' ?>> Rename team</label>
                                <label><input type="checkbox" name="leaderPerms[]" value="assign" <?= in_array('assign', $form['leader_permissions'], true) ? 'checked' : '' ?>> Assign tasks</label>
                                <label><input type="checkbox" name="leaderPerms[]" value="submit" <?= in_array('submit', $form['leader_permissions'], true) ? 'checked' : '' ?>> Submit files</label>
                            </div>
                        </div>

                        <div class="pf-field">
                            <label for="reminderDays">Deadline reminder</label>
                            <select id="reminderDays" name="reminderDays">
                                <?php foreach (array(0, 1, 3, 5, 7, 14) as $d): ?>
                                    <option value="<?= $d ?>" <?= (int) $form['reminder_days'] === $d ? 'selected' : '' ?>>
                                        <?= $d === 0 ? 'No automatic reminder' : $d . ' day(s) before the deadline' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="pf-help">Students receive an in-app reminder inside this window.</span>
                        </div>

                        <div class="pf-field">
                            <label class="pf-inline" style="margin-top: 24px;">
                                <input type="checkbox" name="allowExtension" value="1" <?= (int) $form['allow_extension'] === 1 ? 'checked' : '' ?>>
                                Allow students to request a deadline extension
                            </label>
                            <span class="pf-help">Requests appear on the project page for your approval.</span>
                        </div>

                        <?php if (count($existingAttachments) > 0): ?>
                            <?php foreach ($existingAttachments as $att): ?>
                                <div class="pf-attach full">
                                    <span>
                                        <strong><?= htmlspecialchars($att['display_name'] ?: $att['file_name']) ?></strong>
                                        <span class="muted"> · <?= htmlspecialchars($att['file_size']) ?> · <?= htmlspecialchars(timeAgo($att['uploaded_at'])) ?></span>
                                    </span>
                                    <label>
                                        <input type="checkbox" name="removeAttachment[]" value="<?= (int) $att['id'] ?>">
                                        Remove
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="pf-field full">
                            <label for="attachments">Project attachments</label>
                            <input type="file" id="attachments" name="attachments[]" multiple>
                            <span class="pf-help">PDF, Office, text, ZIP and images up to 5 MB each.</span>
                        </div>
                    </div>
                </div>
            </details>

            <!-- ================= Actions ================= -->
            <div class="pf-actions">
                <a href="teacher_projects.php" class="pf-btn ghost">Cancel</a>
                <button type="submit" name="action" value="draft" class="pf-btn secondary">Save as Draft</button>
                <button type="submit" name="action" value="publish" class="pf-btn primary">Publish Project</button>
            </div>
        </form>
    </div>
</main>

<template id="msTemplate">
    <div class="pf-ms-row">
        <input type="text" name="msName[]" placeholder="Milestone name">
        <input type="date" name="msStart[]" title="Start date">
        <input type="date" name="msEnd[]" title="Due date">
        <button type="button" class="pf-icon-btn js-ms-remove" title="Remove milestone">×</button>
    </div>
</template>

<template id="topicTemplate">
    <div class="pf-topic-row">
        <input type="hidden" name="topicId[]" value="0">
        <input type="text" name="topicTitle[]" maxlength="180" placeholder="Topic title">
        <textarea name="topicDescription[]" placeholder="Short description students will see"></textarea>
        <button type="button" class="pf-icon-btn js-topic-remove" title="Remove topic">×</button>
    </div>
</template>

<script>
    (function () {
        // Make required and optional fields visually consistent.
        document.querySelectorAll('.pf-field > label').forEach(function (label) {
            if (!label.querySelector('.req') && !label.querySelector('.optional-badge')) {
                var badge = document.createElement('span');
                badge.className = 'optional-badge';
                badge.textContent = 'Optional';
                label.appendChild(badge);
            }
        });

        // Milestones
        var rows = document.getElementById('msRows');
        var tpl  = document.getElementById('msTemplate');

        document.getElementById('addMilestone').addEventListener('click', function () {
            rows.appendChild(tpl.content.cloneNode(true));
        });

        rows.addEventListener('click', function (event) {
            if (event.target.classList.contains('js-ms-remove')) {
                if (rows.querySelectorAll('.pf-ms-row').length > 1) {
                    event.target.closest('.pf-ms-row').remove();
                }
            }
        });

        // Keep the empty assessment-scope option styled like a placeholder.
        var assessmentScope = document.getElementById('assessmentScope');
        function syncAssessmentScopeStyle() {
            assessmentScope.classList.toggle('is-placeholder', assessmentScope.value === '');
        }
        assessmentScope.addEventListener('change', syncAssessmentScopeStyle);
        syncAssessmentScopeStyle();

        // Topic allocation
        var topicMode = document.getElementById('topicMode');
        var topicEditor = document.getElementById('topicEditor');
        var topicRows = document.getElementById('topicRows');
        var topicTemplate = document.getElementById('topicTemplate');
        var addTopic = document.getElementById('addTopic');
        var selfTopicNote = document.getElementById('selfTopicNote');
        var topicModeHelp = document.getElementById('topicModeHelp');
        var nameInput = document.getElementById('name');
        var classSelect = document.getElementById('classId');
        var categorySelect = document.getElementById('category');
        var manualProjectTitleField = document.getElementById('manualProjectTitleField');
        var autoProjectTitleField = document.getElementById('autoProjectTitleField');
        var autoProjectTitlePreview = document.getElementById('autoProjectTitlePreview');
        var projectDescriptionField = document.getElementById('projectDescriptionField');
        var assessmentWeightGroup = document.getElementById('assessmentWeightField');
        var allowCrossClass = document.getElementById('allowCrossClass');
        var crossClassOptions = document.getElementById('crossClassOptions');

        function syncCrossClassOptions() {
            var isEnabled = allowCrossClass.checked;
            crossClassOptions.hidden = !isEnabled;
            var homeClassId = classSelect.value;

            crossClassOptions.querySelectorAll('[data-cross-class-id]').forEach(function (checkbox) {
                var isHome = checkbox.getAttribute('data-cross-class-id') === homeClassId;
                checkbox.disabled = isHome;
                checkbox.checked = isHome || checkbox.checked;
                checkbox.closest('.cross-class-option').classList.toggle('is-home', isHome);
            });
        }

        function syncProjectTitle() {
            var isFixed = topicMode.value === 'Fixed';
            var isSelf = topicMode.value === 'Self';
            var isMulti = topicMode.value === 'Multi';
            manualProjectTitleField.hidden = !isFixed;
            autoProjectTitleField.hidden = !isSelf;
            projectDescriptionField.hidden = isMulti;
            assessmentWeightGroup.classList.toggle('full', isMulti);
            nameInput.required = isFixed;

            if (!isSelf) return;

            var classOption = classSelect.options[classSelect.selectedIndex];
            var classCode = classOption ? (classOption.getAttribute('data-class-code') || '') : '';
            var courseCode = classOption ? (classOption.getAttribute('data-course-code') || '') : '';
            var category = categorySelect.value || '';

            autoProjectTitlePreview.textContent = (courseCode && classCode && category)
                ? courseCode + '_' + classCode + '_' + category
                : 'Select a class and Category to generate the title.';
        }

        function syncTopicMode() {
            var mode = topicMode.value;
            var isSelf = mode === 'Self';
            topicEditor.hidden = mode !== 'Multi';
            selfTopicNote.hidden = !isSelf;
            addTopic.style.display = mode === 'Multi' ? '' : 'none';

            if (mode === 'Fixed') {
                while (topicRows.querySelectorAll('.pf-topic-row').length > 1) {
                    topicRows.querySelector('.pf-topic-row:last-child').remove();
                }
            }

            var help = {
                Fixed: 'Project title is used as the topic title for every team. No separate topic approval is required.',
                Multi: 'Add any number of topic options. Multiple teams may select the same topic.',
                Self: 'No topics are pre-defined. Only a team leader may submit a topic proposal.'
            };
            topicModeHelp.textContent = help[mode] || '';
            syncProjectTitle();
        }

        topicMode.addEventListener('change', syncTopicMode);
        classSelect.addEventListener('change', function () {
            syncProjectTitle();
            syncCrossClassOptions();
        });
        categorySelect.addEventListener('change', syncProjectTitle);
        allowCrossClass.addEventListener('change', syncCrossClassOptions);
        syncTopicMode();
        syncCrossClassOptions();

        addTopic.addEventListener('click', function () {
            topicRows.appendChild(topicTemplate.content.cloneNode(true));
        });

        topicRows.addEventListener('click', function (event) {
            if (!event.target.classList.contains('js-topic-remove')) {
                return;
            }
            var allRows = topicRows.querySelectorAll('.pf-topic-row');
            if (allRows.length > 1) {
                event.target.closest('.pf-topic-row').remove();
            }
        });

        // Announcement live preview
        var title    = document.getElementById('annTitle');
        var message  = document.getElementById('annMessage');
        var timing   = document.getElementById('annTiming');
        var when     = document.getElementById('annScheduledAt');

        function render() {
            var t = title.value.trim();
            var m = message.value.trim();
            document.getElementById('pvTitle').textContent = t || 'Your announcement title';
            document.getElementById('pvMessage').textContent = m || 'Your message will appear here as students see it.';

            document.getElementById('pvChannels').textContent = 'In-app notification';

            var label = 'Sent immediately';
            if (timing.value === 'scheduled') {
                label = when.value ? 'Scheduled for ' + when.value.replace('T', ' ') : 'Scheduled (time not set)';
            }
            document.getElementById('pvTiming').textContent = label;
        }

        [title, message, when].forEach(function (el) {
            el.addEventListener('input', render);
            el.addEventListener('change', render);
        });
        timing.addEventListener('change', function () {
            when.style.opacity = timing.value === 'scheduled' ? '1' : '0.45';
            render();
        });
        when.style.opacity = timing.value === 'scheduled' ? '1' : '0.45';
        render();
    })();
</script>

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
</body>
</html>
