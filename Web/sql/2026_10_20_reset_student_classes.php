<?php
/**
 * Rebuild the demo student, class, project and team dataset.
 * CLI only. Run after taking a database backup.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

$seedDatabase = getenv('TEAMBOX_SEED_DB');
if ($seedDatabase !== false && $seedDatabase !== '') {
    $conn = mysqli_connect('127.0.0.1', 'root', '', $seedDatabase);
    mysqli_set_charset($conn, 'utf8mb4');
} else {
    require_once dirname(__DIR__) . '/db.php';
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
/** @var mysqli $conn */

function runQuery(mysqli $conn, string $sql, string $label): void
{
    if (!mysqli_query($conn, $sql)) {
        throw new RuntimeException($label . ': ' . mysqli_error($conn));
    }
    echo $label . PHP_EOL;
}

function columnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = (bool) mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $exists;
}

function indexExists(mysqli $conn, string $table, string $index): bool
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT 1 FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $table, $index);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = (bool) mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $exists;
}

$preservedNames = array(
    'CHAN Ho Yin',
    'MIAO Tung',
    'CHUI Ka Chun',
    'LAI Ka-Yu Savio',
);

$placeholders = implode(',', array_fill(0, count($preservedNames), '?'));
$stmt = mysqli_prepare(
    $conn,
    "SELECT s.id AS student_id, s.user_id, u.username, u.password_hash
     FROM students s
     INNER JOIN users u ON u.id = s.user_id
     WHERE u.username IN ($placeholders)"
);
mysqli_stmt_bind_param($stmt, str_repeat('s', count($preservedNames)), ...$preservedNames);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$preservedRows = array();
while ($row = mysqli_fetch_assoc($result)) {
    $preservedRows[$row['username']] = $row;
}
mysqli_stmt_close($stmt);

if (count($preservedRows) !== 4) {
    throw new RuntimeException('Expected all four preserved students to exist.');
}

$preservedStudentIds = array();
$preservedUserIds = array();
foreach ($preservedRows as $row) {
    $preservedStudentIds[] = (int) $row['student_id'];
    $preservedUserIds[] = (int) $row['user_id'];
}
$studentPasswordHash = $preservedRows['CHAN Ho Yin']['password_hash'];

mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS = 0');

try {
    runQuery(
        $conn,
        "CREATE TABLE IF NOT EXISTS `classes` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `class_code` VARCHAR(20) NOT NULL,
            `programme` VARCHAR(100) NOT NULL,
            `year_level` TINYINT NOT NULL,
            `section` CHAR(1) NOT NULL,
            `course_id` INT(11) NOT NULL,
            `teacher_id` INT(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_classes_code` (`class_code`),
            KEY `idx_classes_course` (`course_id`),
            KEY `idx_classes_teacher` (`teacher_id`),
            CONSTRAINT `fk_classes_course`
                FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
            CONSTRAINT `fk_classes_teacher`
                FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'Created classes table'
    );

    if (!columnExists($conn, 'students', 'class_id')) {
        runQuery($conn, "ALTER TABLE `students` ADD COLUMN `class_id` INT(11) NULL AFTER `grade_level`", 'Added students.class_id');
    }
    if (!columnExists($conn, 'projects', 'class_id')) {
        runQuery($conn, "ALTER TABLE `projects` ADD COLUMN `class_id` INT(11) NULL AFTER `course_id`", 'Added projects.class_id');
    }
    if (!columnExists($conn, 'group_members', 'project_id')) {
        runQuery($conn, "ALTER TABLE `group_members` ADD COLUMN `project_id` INT(11) NULL AFTER `student_id`", 'Added group_members.project_id');
    }
    if (!columnExists($conn, 'group_members', 'course_id')) {
        runQuery($conn, "ALTER TABLE `group_members` ADD COLUMN `course_id` INT(11) NULL AFTER `project_id`", 'Added group_members.course_id');
    }

    runQuery($conn, "DROP TRIGGER IF EXISTS `trg_group_members_validate_insert`", 'Dropped old insert trigger');
    runQuery($conn, "DROP TRIGGER IF EXISTS `trg_group_members_validate_update`", 'Dropped old update trigger');

    $tablesToClear = array(
        'activities',
        'announcement_targets',
        'announcements',
        'contributions',
        'course_enrollments',
        'files',
        'group_members',
        'group_requests',
        'invitations',
        'leader_votes',
        'messages',
        'message_reads',
        'milestones',
        'notifications',
        'project_attachments',
        'project_extension_requests',
        'request_members',
        'tasks',
        'teacher_student',
        'groups',
        'projects',
    );
    foreach ($tablesToClear as $table) {
        runQuery($conn, "DELETE FROM `$table`", 'Cleared ' . $table);
    }

    runQuery($conn, "UPDATE `students` SET `class_id` = NULL", 'Detached preserved students from old classes');
    runQuery($conn, "DELETE FROM `classes`", 'Cleared old classes');
    runQuery($conn, "ALTER TABLE `classes` AUTO_INCREMENT = 1", 'Reset class IDs');
    runQuery($conn, "ALTER TABLE `projects` AUTO_INCREMENT = 1", 'Reset project IDs');
    runQuery($conn, "ALTER TABLE `groups` AUTO_INCREMENT = 1", 'Reset team IDs');

    $keepStudentIds = implode(',', $preservedStudentIds);
    $keepUserIds = implode(',', $preservedUserIds);
    runQuery(
        $conn,
        "DELETE FROM `students` WHERE `id` NOT IN ($keepStudentIds)",
        'Removed old student profiles'
    );
    runQuery(
        $conn,
        "DELETE FROM `users` WHERE `role` = 'student' AND `id` NOT IN ($keepUserIds)",
        'Removed old student accounts'
    );

    $classRows = array(
        array(1, 'SE-1A', 'Software Engineering', 1, 'A', 1),
        array(2, 'SE-1B', 'Software Engineering', 1, 'B', 2),
        array(3, 'SE-2A', 'Software Engineering', 2, 'A', 3),
        array(4, 'SE-2B', 'Software Engineering', 2, 'B', 1),
        array(5, 'CY-1A', 'Cybersecurity', 1, 'A', 1),
        array(6, 'CY-1B', 'Cybersecurity', 1, 'B', 2),
        array(7, 'CY-2A', 'Cybersecurity', 2, 'A', 4),
        array(8, 'CY-2B', 'Cybersecurity', 2, 'B', 5),
    );

    $insertClass = mysqli_prepare(
        $conn,
        "INSERT INTO `classes`
            (`id`, `class_code`, `programme`, `year_level`, `section`, `course_id`, `teacher_id`)
         VALUES (?, ?, ?, ?, ?, 1, ?)"
    );
    foreach ($classRows as $classRow) {
        list($classId, $classCode, $programme, $yearLevel, $section, $classTeacherId) = $classRow;
        mysqli_stmt_bind_param(
            $insertClass,
            'issisi',
            $classId,
            $classCode,
            $programme,
            $yearLevel,
            $section,
            $classTeacherId
        );
        mysqli_stmt_execute($insertClass);
    }
    mysqli_stmt_close($insertClass);
    echo "Created 8 classes" . PHP_EOL;

    $classMeta = array();
    foreach ($classRows as $classRow) {
        $classMeta[(int) $classRow[0]] = array(
            'code'       => $classRow[1],
            'programme'  => $classRow[2],
            'year'       => (int) $classRow[3],
            'section'    => $classRow[4],
            'teacher_id' => (int) $classRow[5],
        );
    }

    $classStudents = array();
    foreach ($classMeta as $classId => $meta) {
        $classStudents[$classId] = array();
    }

    $updatePreserved = mysqli_prepare(
        $conn,
        "UPDATE `students`
         SET `major` = ?, `grade_level` = ?, `class_id` = ?
         WHERE `id` = ?"
    );
    foreach ($preservedStudentIds as $studentId) {
        $major = 'Software Engineering';
        $gradeLevel = 13;
        $classId = 4;
        mysqli_stmt_bind_param($updatePreserved, 'siii', $major, $gradeLevel, $classId, $studentId);
        mysqli_stmt_execute($updatePreserved);
        $classStudents[4][] = $studentId;
    }
    mysqli_stmt_close($updatePreserved);

    $surnames = array(
        'AU', 'CHAN', 'CHENG', 'CHEUNG', 'CHIU', 'CHOW', 'CHU', 'FUNG', 'HE', 'HO',
        'HUANG', 'HUI', 'IP', 'KWAN', 'KWOK', 'LAI', 'LAM', 'LAU', 'LEE', 'LEUNG',
        'LI', 'LIN', 'LO', 'LUO', 'MAK', 'NG', 'NGAI', 'POON', 'QIU', 'SIU',
        'SO', 'TAM', 'TANG', 'TSE', 'WONG', 'WU', 'XU', 'YAU', 'YIP', 'YU',
    );
    $givenNames = array(
        'KA CHUN', 'WING YAN', 'HO YIN', 'MEI LING', 'CHUN HEI',
        'KA YAN', 'HIU TUNG', 'SZE WING', 'MAN KIT', 'CHEUK YIU',
        'YUEN TING', 'TSZ HIN', 'HO KWAN', 'WAI LAM', 'CHING MAN',
        'KA HO', 'WING CHI', 'CHUN MING', 'MEI YI', 'TSZ CHING',
        'CHI HANG', 'MAN YEE', 'KA WAI', 'HO CHEUNG', 'WING LAM',
    );

    $usedNames = array();
    foreach ($preservedNames as $preservedName) {
        $usedNames[strtolower($preservedName)] = true;
    }
    $studentUserIds = array();
    $insertUser = mysqli_prepare(
        $conn,
        "INSERT INTO `users` (`username`, `email`, `password_hash`, `role`)
         VALUES (?, ?, ?, 'student')"
    );
    $insertStudent = mysqli_prepare(
        $conn,
        "INSERT INTO `students`
            (`user_id`, `student_number`, `major`, `grade_level`, `class_id`)
         VALUES (?, ?, ?, ?, ?)"
    );
    $insertTeacherStudent = mysqli_prepare(
        $conn,
        "INSERT INTO `teacher_student` (`teacher_id`, `student_id`) VALUES (?, ?)"
    );
    $insertEnrollment = mysqli_prepare(
        $conn,
        "INSERT INTO `course_enrollments` (`course_id`, `student_id`) VALUES (1, ?)"
    );

    $generated = 0;
    $candidateIndex = 0;
    foreach ($classMeta as $classId => $meta) {
        while (count($classStudents[$classId]) < 20) {
            $surname = $surnames[$candidateIndex % count($surnames)];
            $given = $givenNames[$generated % count($givenNames)];
            $candidateIndex++;
            $fullName = $surname . ' ' . ucwords(strtolower($given));
            $nameKey = strtolower($fullName);
            if (isset($usedNames[$nameKey])) {
                continue;
            }
            $usedNames[$nameKey] = true;
            $email = 'student' . ($generated + 1) . '@student.teambox.local';
            $studentNumber = 'S' . sprintf('%08d', 27000000 + $generated);
            $gradeLevel = 12 + $meta['year'] - 1;

            mysqli_stmt_bind_param($insertUser, 'sss', $fullName, $email, $studentPasswordHash);
            mysqli_stmt_execute($insertUser);
            $userId = (int) mysqli_insert_id($conn);

            mysqli_stmt_bind_param(
                $insertStudent,
                'isssi',
                $userId,
                $studentNumber,
                $meta['programme'],
                $gradeLevel,
                $classId
            );
            mysqli_stmt_execute($insertStudent);
            $studentId = (int) mysqli_insert_id($conn);

            $studentUserIds[$studentId] = $userId;
            $classStudents[$classId][] = $studentId;
            $generated++;
        }
    }

    mysqli_stmt_close($insertUser);
    mysqli_stmt_close($insertStudent);

    $allStudentIds = array();
    foreach ($classMeta as $classId => $meta) {
        foreach ($classStudents[$classId] as $studentId) {
            $allStudentIds[] = $studentId;
            mysqli_stmt_bind_param($insertTeacherStudent, 'ii', $meta['teacher_id'], $studentId);
            mysqli_stmt_execute($insertTeacherStudent);
            mysqli_stmt_bind_param($insertEnrollment, 'i', $studentId);
            mysqli_stmt_execute($insertEnrollment);
        }
    }
    mysqli_stmt_close($insertTeacherStudent);
    mysqli_stmt_close($insertEnrollment);

    foreach ($preservedStudentIds as $studentId) {
        if (!isset($studentUserIds[$studentId])) {
            $stmt = mysqli_prepare($conn, "SELECT user_id FROM `students` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $studentId);
            mysqli_stmt_execute($stmt);
            $studentResult = mysqli_stmt_get_result($stmt);
            $studentRow = mysqli_fetch_assoc($studentResult);
            $studentUserIds[$studentId] = (int) $studentRow['user_id'];
            mysqli_stmt_close($stmt);
        }
    }

    echo "Kept 4 existing students and generated $generated students" . PHP_EOL;

    $projectRows = array(
        1 => array('TeamBox', 'Software Engineering', 1, 1, 'Executing', '2026-10-18'),
        4 => array('Smart Attendance', 'Software Engineering', 1, 4, 'Executing', '2026-11-03'),
        5 => array('Campus Map AR', 'Cybersecurity', 1, 5, 'Executing', '2026-11-10'),
        2 => array('Smart Library', 'Software Engineering', 2, 2, 'Executing', '2026-11-05'),
        6 => array('Campus Food Order', 'Cybersecurity', 2, 6, 'Executing', '2026-11-07'),
        3 => array('AI Study Buddy', 'Software Engineering', 3, 3, 'Executing', '2026-11-07'),
        7 => array('Green Campus', 'Cybersecurity', 4, 7, 'Executing', '2026-11-09'),
        8 => array('Career Path Finder', 'Cybersecurity', 5, 8, 'Executing', '2026-11-11'),
    );

    $insertProject = mysqli_prepare(
        $conn,
        "INSERT INTO `projects`
            (`name`, `category`, `course_id`, `class_id`, `supervisor_id`,
             `period_start`, `period_end`, `status`, `progress`, `description`, `requirements`,
             `team_mode`, `team_deadline`, `allow_cross_class`, `default_max_members`,
             `leader_permissions`, `post_deadline_action`, `visibility`, `reminder_days`,
             `allow_extension`, `is_draft`)
         VALUES (?, 'Final Year Project', 1, ?, ?, '2026-09-01', '2027-01-31',
                 ?, 20, ?, ?, 'Random', ?, 0, 4, ?, 'Lock', 'Course', 3, 1, 0)"
    );
    $leaderPermissions = json_encode(array('invite', 'remove', 'rename', 'assign', 'submit'));
    $projectIdsByClass = array();
    foreach ($projectRows as $classId => $projectRow) {
        list($projectName, $category, $supervisorId, $mappedClassId, $projectStatus, $teamDeadline) = $projectRow;
        $description = $projectName . ' is a Final Year Project for class ' . $classMeta[$mappedClassId]['code'] . '.';
        $requirements = 'Students work in teams of four and submit progress through TeamBox.';
        mysqli_stmt_bind_param(
            $insertProject,
            'siisssss',
            $projectName,
            $mappedClassId,
            $supervisorId,
            $projectStatus,
            $description,
            $requirements,
            $teamDeadline,
            $leaderPermissions
        );
        mysqli_stmt_execute($insertProject);
        $projectIdsByClass[$mappedClassId] = (int) mysqli_insert_id($conn);
    }
    mysqli_stmt_close($insertProject);
    echo "Created 8 projects" . PHP_EOL;

    $insertMilestone = mysqli_prepare(
        $conn,
        "INSERT INTO `milestones`
            (`project_id`, `name`, `start_date`, `end_date`, `status`, `progress`)
         VALUES (?, ?, ?, ?, 'Pending', 0)"
    );
    foreach ($projectIdsByClass as $projectId) {
        $milestones = array(
            array('Proposal and planning', '2026-09-01', '2026-10-15'),
            array('Prototype checkpoint', '2026-10-16', '2026-11-20'),
            array('Final submission', '2026-11-21', '2027-01-20'),
        );
        foreach ($milestones as $milestone) {
            mysqli_stmt_bind_param(
                $insertMilestone,
                'isss',
                $projectId,
                $milestone[0],
                $milestone[1],
                $milestone[2]
            );
            mysqli_stmt_execute($insertMilestone);
        }
    }
    mysqli_stmt_close($insertMilestone);

    $insertGroup = mysqli_prepare(
        $conn,
        "INSERT INTO `groups`
            (`project_id`, `course_id`, `name`, `leader_id`, `creator_id`,
             `max_members`, `status`, `formed_at`)
         VALUES (?, 1, ?, ?, ?, 4, ?, NOW())"
    );
    $insertMember = mysqli_prepare(
        $conn,
        "INSERT INTO `group_members` (`group_id`, `student_id`, `role`)
         VALUES (?, ?, ?)"
    );
    $insertRequest = mysqli_prepare(
        $conn,
        "INSERT INTO `group_requests`
            (`group_name`, `project_id`, `submitted_by`, `status`)
         VALUES (?, ?, ?, 'Pending')"
    );
    $insertTask = mysqli_prepare(
        $conn,
        "INSERT INTO `tasks`
            (`project_id`, `group_id`, `title`, `description`, `assigned_to`,
             `created_by`, `status`, `priority`, `due_date`, `completed_at`)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Medium', ?, ?)"
    );
    $insertActivity = mysqli_prepare(
        $conn,
        "INSERT INTO `activities` (`project_id`, `group_id`, `actor_id`, `action`, `detail`)
         VALUES (?, ?, ?, 'joined_team', ?)"
    );

    $teamNumber = 0;
    $teamIds = array();
    $teamStatus = 'Executing';
    foreach ($projectRows as $classId => $projectRow) {
        $mappedClassId = (int) $projectRow[3];
        $projectId = $projectIdsByClass[$mappedClassId];
        $classStudentList = $classStudents[$mappedClassId];
        $assignedStudents = array_slice($classStudentList, 0, 16);

        for ($teamIndex = 0; $teamIndex < 4; $teamIndex++) {
            $teamNumber++;
            $teamName = sprintf('Team %02d', $teamNumber);
            $teamMembers = array_slice($assignedStudents, $teamIndex * 4, 4);
            $leaderId = $teamMembers[0];
            $teamStatus = ($classMeta[$mappedClassId]['teacher_id'] === 1 && $teamIndex === 2)
                ? 'Waiting_Send'
                : 'Executing';

            mysqli_stmt_bind_param(
                $insertGroup,
                'isiis',
                $projectId,
                $teamName,
                $leaderId,
                $leaderId,
                $teamStatus
            );
            mysqli_stmt_execute($insertGroup);
            $groupId = (int) mysqli_insert_id($conn);
            $teamIds[] = $groupId;

            foreach ($teamMembers as $memberIndex => $studentId) {
                $role = $memberIndex === 0 ? 'leader' : 'member';
                mysqli_stmt_bind_param($insertMember, 'iis', $groupId, $studentId, $role);
                mysqli_stmt_execute($insertMember);

                $studentUserId = $studentUserIds[$studentId];
                $detail = $classMeta[$mappedClassId]['code'] . ' · ' . $teamName;
                mysqli_stmt_bind_param(
                    $insertActivity,
                    'iiis',
                    $projectId,
                    $groupId,
                    $studentUserId,
                    $detail
                );
                mysqli_stmt_execute($insertActivity);
            }

            if ($teamStatus === 'Waiting_Send') {
                mysqli_stmt_bind_param($insertRequest, 'sii', $teamName, $projectId, $leaderId);
                mysqli_stmt_execute($insertRequest);
                continue;
            }

            $leaderUserId = $studentUserIds[$leaderId];
            $firstDue = date('Y-m-d', strtotime('-14 days'));
            $firstCompleted = date('Y-m-d H:i:s', strtotime('-10 days'));
            $firstTask = 'Prepare project proposal';
            $firstDescription = 'Create the initial proposal and agree on team responsibilities.';
            $firstStatus = 'Completed';
            mysqli_stmt_bind_param(
                $insertTask,
                'iissiisss',
                $projectId,
                $groupId,
                $firstTask,
                $firstDescription,
                $leaderId,
                $leaderUserId,
                $firstStatus,
                $firstDue,
                $firstCompleted
            );
            mysqli_stmt_execute($insertTask);

            $secondStudentId = $teamMembers[1];
            $secondDue = $teamIndex === 0
                ? date('Y-m-d', strtotime('-1 day'))
                : date('Y-m-d', strtotime('+7 days'));
            $secondTask = 'Complete prototype checkpoint';
            $secondDescription = 'Prepare the current prototype and document the completed work.';
            $secondStatus = 'In Progress';
            $nullCompleted = null;
            mysqli_stmt_bind_param(
                $insertTask,
                'iissiisss',
                $projectId,
                $groupId,
                $secondTask,
                $secondDescription,
                $secondStudentId,
                $leaderUserId,
                $secondStatus,
                $secondDue,
                $nullCompleted
            );
            mysqli_stmt_execute($insertTask);
        }
    }
    mysqli_stmt_close($insertGroup);
    mysqli_stmt_close($insertMember);
    mysqli_stmt_close($insertRequest);
    mysqli_stmt_close($insertTask);
    mysqli_stmt_close($insertActivity);

    if (!indexExists($conn, 'teacher_student', 'uq_teacher_student_student')) {
        runQuery(
            $conn,
            "ALTER TABLE `teacher_student`
             ADD UNIQUE KEY `uq_teacher_student_student` (`student_id`)",
            'Added one-teacher-per-student constraint'
        );
    }
    if (!indexExists($conn, 'course_enrollments', 'uq_course_enrollment')) {
        runQuery(
            $conn,
            "ALTER TABLE `course_enrollments`
             ADD UNIQUE KEY `uq_course_enrollment` (`course_id`, `student_id`)",
            'Added unique course enrollment constraint'
        );
    }
    if (!indexExists($conn, 'group_members', 'uq_group_member_course')) {
        runQuery(
            $conn,
            "ALTER TABLE `group_members`
             ADD UNIQUE KEY `uq_group_member_course` (`student_id`, `course_id`)",
            'Added one-project-per-course constraint'
        );
    }

    runQuery(
        $conn,
        "CREATE TRIGGER `trg_group_members_validate_insert`
         BEFORE INSERT ON `group_members`
         FOR EACH ROW
         BEGIN
            DECLARE v_project_id INT;
            DECLARE v_course_id INT;
            DECLARE v_project_class_id INT;
            DECLARE v_allow_cross_class TINYINT DEFAULT 0;
            DECLARE v_student_class_id INT;

            SELECT g.project_id, g.course_id, p.class_id, p.allow_cross_class
              INTO v_project_id, v_course_id, v_project_class_id, v_allow_cross_class
              FROM groups g
              INNER JOIN projects p ON p.id = g.project_id
             WHERE g.id = NEW.group_id;

            SELECT class_id INTO v_student_class_id
              FROM students WHERE id = NEW.student_id;

            IF v_project_id IS NULL OR v_student_class_id IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid student or project group';
            END IF;

            IF v_allow_cross_class = 0 AND v_project_class_id <> v_student_class_id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student is not in the project class';
            END IF;

            SET NEW.project_id = v_project_id;
            SET NEW.course_id = v_course_id;
         END",
        'Created team membership validation trigger'
    );

    if (!indexExists($conn, 'students', 'fk_students_class')) {
        runQuery(
            $conn,
            "ALTER TABLE `students`
             ADD CONSTRAINT `fk_students_class`
             FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)",
            'Added students.class_id foreign key'
        );
    }
    if (!indexExists($conn, 'projects', 'fk_projects_class')) {
        runQuery(
            $conn,
            "ALTER TABLE `projects`
             ADD CONSTRAINT `fk_projects_class`
             FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)",
            'Added projects.class_id foreign key'
        );
    }

    mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS = 1');
    echo 'Reset complete: 160 students, 8 classes, 8 projects, 32 teams.' . PHP_EOL;
} catch (Throwable $error) {
    mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS = 1');
    fwrite(STDERR, 'RESET FAILED: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
?>
