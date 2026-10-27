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
    header('Location: teacher_login.php');
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

// =========================================================
// Defaults
// =========================================================
$defaultPermissions = array('invite', 'rename', 'assign', 'submit');
$allowedCategories = array('CA', 'EA', 'Assignment', 'Final Year Project');
$allowedAssessmentScopes = array('CA', 'EA', 'This Semester', 'Full Year');

$form = array(
    'name'                 => '',
    'class_id'             => count($classes) > 0 ? (int) $classes[0]['id'] : 0,
    'course_id'            => count($classes) > 0 ? (int) $classes[0]['course_id'] : 0,
    'category'             => '',
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
        $form['assessment_scope']     = (string) ($source['assessment_scope'] ?? '');
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
    foreach ($classes as $classRow) {
        if ((int) $classRow['id'] === $form['class_id']) {
            $form['course_id'] = (int) $classRow['course_id'];
            break;
        }
    }
    $form['category']             = trim(isset($_POST['category']) ? $_POST['category'] : '');
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

    // ---------- Validation ----------
    if ($form['name'] === '') {
        $errors[] = 'Project title is required.';
    } elseif (strlen($form['name']) > 150) {
        $errors[] = 'Project title must be 150 characters or fewer.';
    }

    if ($form['course_id'] <= 0) {
        $errors[] = 'Please choose a class that belongs to you.';
    }
    if (!in_array($form['category'], $allowedCategories, true)) {
        $errors[] = 'Please choose a valid project category.';
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
        if ($form['description'] === '') {
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

    // ---------- Save ----------
    if (count($errors) === 0) {

        $leaderPermissions = json_encode($form['leader_permissions']);
        $desc              = ($form['description'] !== '') ? $form['description'] : null;
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
    <link rel="stylesheet" href="teacher.css?v=20261028a">
    <style>
        .pf-wrap { max-width: 980px; }
        .pf-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
        .pf-head h1 { margin-bottom: 5px; }
        .pf-head .subtitle { margin-bottom: 0; }
        .pf-section { background: #fff; border: 1px solid #e6e8eb; border-radius: 10px; padding: 22px 24px; margin-bottom: 22px; }
        .pf-section h2 { margin: 0 0 16px; color: #252a31; font-size: 13.5px; font-weight: 700; }
        .pf-section .pf-section-desc { margin: -8px 0 18px; color: #69717b; font-size: 12px; }
        .pf-section.pf-disclosure { padding: 0; overflow: hidden; }
        .pf-disclosure > summary {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            cursor: pointer;
            list-style: none;
        }
        .pf-disclosure > summary::-webkit-details-marker { display: none; }
        .pf-disclosure > summary::after {
            flex: 0 0 auto;
            margin-left: auto;
            color: #7f858e;
            font-size: 18px;
            font-weight: 400;
            content: "+";
        }
        .pf-disclosure[open] > summary {
            border-bottom: 1px solid #edf0f3;
        }
        .pf-disclosure[open] > summary::after { content: "−"; }
        .pf-disclosure > summary h2 { margin: 0; }
        .pf-disclosure > summary .pf-section-desc { margin: 4px 0 0; }
        .pf-disclosure > summary .optional-badge { margin-left: 0; }
        .pf-disclosure-body { padding: 20px 24px 24px; }
        .pf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .pf-grid.three { grid-template-columns: 1fr 1fr 1fr; }
        .pf-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .pf-field.full { grid-column: 1 / -1; }
        .pf-field label { color: #303640; font-size: 12.5px; font-weight: 600; }
        .pf-field > label {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
        }
        .pf-field .req { color: #b42318; }
        .optional-badge {
            margin-left: auto;
            padding: 2px 6px;
            border: 1px solid #d9dee4;
            border-radius: 4px;
            background: #f4f6f8;
            color: #646b75;
            font-size: 8.5px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .pf-field input[type=text], .pf-field input[type=date], .pf-field input[type=datetime-local],
        .pf-field input[type=number], .pf-field select, .pf-field textarea {
            width: 100%; padding: 9px 11px; border: 1px solid #dfe2e6; border-radius: 7px;
            font-size: 13px; color: #2c2f36; background: #fff;
        }
        .pf-field textarea { min-height: 90px; resize: vertical; line-height: 1.5; }
        .pf-field input:focus, .pf-field select:focus, .pf-field textarea:focus {
            outline: none; border-color: #8db8ea; box-shadow: 0 0 0 3px rgba(141, 184, 234, 0.2);
        }
        .pf-help { color: #69717b; font-size: 11.5px; line-height: 1.5; }
        .assessment-weight-row {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) 110px;
            gap: 8px;
        }
        .assessment-percent { position: relative; }
        .assessment-percent input { padding-right: 28px; }
        .pf-field select.is-placeholder { color: #9aa0a8; }
        .assessment-percent span {
            position: absolute;
            top: 50%;
            right: 11px;
            color: #6b6f78;
            font-size: 12px;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .pf-checks { display: flex; flex-wrap: wrap; gap: 8px 18px; }
        .pf-checks label { display: inline-flex; align-items: center; gap: 7px; color: #3d444e; font-size: 12.5px; font-weight: 500; }
        .pf-inline { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #4b5058; }
        .pf-ms-row { display: grid; grid-template-columns: 1.6fr 1fr 1fr 34px; gap: 10px; margin-bottom: 10px; align-items: center; }
        .pf-ms-row input { width: 100%; padding: 8px 10px; border: 1px solid #dfe2e6; border-radius: 7px; font-size: 12.5px; }
        .pf-icon-btn { width: 34px; height: 34px; border: 1px solid #e2e4e8; border-radius: 7px; background: #fff; color: #8b8f98; cursor: pointer; font-size: 16px; line-height: 1; }
        .pf-icon-btn:hover { color: #d32f2f; border-color: #f0b4b4; }
        .pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; border-radius: 8px; border: 1px solid transparent; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .pf-btn.primary { background: #175196; color: #fff; }
        .pf-btn.primary:hover { background: #123f76; }
        .pf-btn.secondary { background: #fff; color: #175196; border-color: #c9d8ea; }
        .pf-btn.secondary:hover { background: #f4f8fd; }
        .pf-btn.ghost { background: transparent; color: #6b6f78; border-color: #e2e4e8; }
        .pf-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 6px 0 40px; }
        .pf-error { background: #fdecec; border: 1px solid #f5c2c2; color: #a61b1b; border-radius: 8px; padding: 12px 15px; margin-bottom: 18px; font-size: 12.5px; line-height: 1.6; }
        .pf-error ul { margin: 0; padding-left: 18px; }
        .pf-preview { border: 1px solid #e2e4e8; border-radius: 10px; overflow: hidden; background: #fafbfc; }
        .pf-preview-head { padding: 12px 16px; border-bottom: 1px solid #eef0f2; background: #fff; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8b8f98; }
        .pf-preview-body { padding: 16px; }
        .pf-preview-title { font-size: 15px; font-weight: 600; margin-bottom: 8px; color: #2c2f36; }
        .pf-preview-message { font-size: 12.5px; color: #4b5058; line-height: 1.6; white-space: pre-wrap; min-height: 52px; }
        .pf-preview-meta { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px; font-size: 11px; color: #8b8f98; }
        .pf-chip { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #eef2f7; color: #4b5058; font-size: 10.5px; font-weight: 600; }
        .pf-attach { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 10px; border: 1px solid #eef0f2; border-radius: 7px; margin-bottom: 8px; font-size: 12.5px; }
        .pf-attach label { display: inline-flex; align-items: center; gap: 8px; color: #6b6f78; font-size: 11.5px; }
        .pf-note { font-size: 11.5px; color: #8b8f98; background: #f7f9fc; border-radius: 7px; padding: 9px 12px; line-height: 1.5; }
        @media (max-width: 760px) {
            .pf-grid, .pf-grid.three { grid-template-columns: 1fr; }
            .assessment-weight-row { grid-template-columns: 1fr; }
            .pf-ms-row { grid-template-columns: 1fr; }
            .pf-icon-btn { width: 100%; }
        }
    </style>
</head>
<body>

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
                        <label for="name">Project title <span class="req">*</span></label>
                        <input type="text" id="name" name="name" maxlength="150" required
                               value="<?= htmlspecialchars($form['name']) ?>"
                               placeholder="e.g. Smart Campus Assistant">
                    </div>

                    <div class="pf-field">
                        <label for="classId">Class <span class="req">*</span></label>
                        <select id="classId" name="classId" required>
                            <?php if (count($classes) === 0): ?>
                                <option value="0">No classes assigned to you</option>
                            <?php endif; ?>
                            <?php foreach ($classes as $classRow): ?>
                                <option value="<?= (int) $classRow['id'] ?>"
                                    <?= (int) $classRow['id'] === (int) $form['class_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classRow['class_code']) ?> —
                                    <?= htmlspecialchars($classRow['course_code'] . ' ' . $classRow['course_name']) ?>
                                    <?= $classRow['semester'] ? ' (' . htmlspecialchars($classRow['semester']) . ')' : '' ?>
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

                    <div class="pf-field full">
                        <label for="assessmentScope">Assessment Weight</label>
                        <div class="assessment-weight-row">
                            <select id="assessmentScope" name="assessmentScope"
                                    class="<?= $form['assessment_scope'] === '' ? 'is-placeholder' : '' ?>">
                                <option value="">e.g. CA</option>
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
                        <span class="pf-help">This project contributes this percentage toward the selected assessment scope.</span>
                    </div>

                    <div class="pf-field full">
                        <label for="description">Project description <span class="req">*</span></label>
                        <textarea id="description" name="description"
                                  placeholder="Describe the project idea, the problem it solves and what students will build."><?= htmlspecialchars($form['description']) ?></textarea>
                        <span class="pf-help">Shown to students on the project page.</span>
                    </div>

                    <div class="pf-field">
                        <label for="periodStart">Project start date <span class="req">*</span></label>
                        <input type="date" id="periodStart" name="periodStart" value="<?= htmlspecialchars($form['period_start']) ?>">
                    </div>

                    <div class="pf-field">
                        <label for="periodEnd">Project deadline <span class="req">*</span></label>
                        <input type="date" id="periodEnd" name="periodEnd" value="<?= htmlspecialchars($form['period_end']) ?>">
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
                            <input type="checkbox" name="allowCrossClass" value="1" <?= (int) $form['allow_cross_class'] === 1 ? 'checked' : '' ?>>
                            Allow cross-class teams
                        </label>
                        <span class="pf-help">When unchecked, teams should only contain students from the same class.</span>
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

</body>
</html>
