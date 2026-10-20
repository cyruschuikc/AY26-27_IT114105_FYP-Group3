<?php
// project_helpers.php
// Shared helpers for the teacher project module.

if (!function_exists('projectAudienceUsers')) {
    /**
     * Students who can see a project, based on its visibility setting.
     * Returns rows with user_id, username, email.
     */
    function projectAudienceUsers($conn, $courseId, $visibility)
    {
        $users = array();

        if ($visibility === 'All') {
            $sql = "
                SELECT u.id AS user_id, u.username, u.email
                FROM `users` u
                INNER JOIN `students` s ON s.user_id = u.id
                WHERE u.role = 'student'
                ORDER BY u.username
            ";
            $rs = mysqli_query($conn, $sql);
            if ($rs) {
                while ($row = mysqli_fetch_assoc($rs)) {
                    $users[] = $row;
                }
                mysqli_free_result($rs);
            }
            return $users;
        }

        if ($courseId <= 0) {
            return $users;
        }

        $stmt = mysqli_prepare($conn, "
            SELECT DISTINCT u.id AS user_id, u.username, u.email
            FROM `course_enrollments` ce
            INNER JOIN `students` s ON s.id = ce.student_id
            INNER JOIN `users` u    ON u.id = s.user_id
            WHERE ce.course_id = ?
            ORDER BY u.username
        ");
        mysqli_stmt_bind_param($stmt, "i", $courseId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $users[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $users;
    }
}

if (!function_exists('projectDispatchAnnouncement')) {
    /**
     * Send an announcement to its project audience: in-app notifications
     * and, when selected, email. Marks the announcement as Sent.
     *
     * @return array{inapp:int,email:int,email_attempted:int}
     */
    function projectDispatchAnnouncement($conn, $announcementId)
    {
        $result = array('inapp' => 0, 'email' => 0, 'email_attempted' => 0);

        $stmt = mysqli_prepare($conn, "
            SELECT a.id, a.title, a.message, a.channels, a.target_project_id,
                   p.course_id, p.visibility, p.name AS project_name
            FROM `announcements` a
            LEFT JOIN `projects` p ON p.id = a.target_project_id
            WHERE a.id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $announcementId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $ann = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        if (!$ann) {
            return $result;
        }

        $courseId   = (int) $ann['course_id'];
        $visibility = $ann['visibility'] ?: 'Course';
        $users      = projectAudienceUsers($conn, $courseId, $visibility);

        $channels = array_filter(array_map('trim', explode(',', (string) $ann['channels'])));
        $sendInApp = in_array('inapp', $channels, true);
        $sendEmail = in_array('email', $channels, true);

        if ($sendInApp) {
            $ins = mysqli_prepare($conn, "
                INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
                VALUES (?, 'system', ?, ?, 'my_team.php', 0)
            ");
            foreach ($users as $u) {
                $uid     = (int) $u['user_id'];
                $title   = 'Announcement: ' . $ann['title'];
                $message = $ann['message'];
                mysqli_stmt_bind_param($ins, "iss", $uid, $title, $message);
                if (mysqli_stmt_execute($ins)) {
                    $result['inapp']++;
                }
            }
            mysqli_stmt_close($ins);
        }

        if ($sendEmail) {
            $headers = "From: TeamBox <no-reply@teambox.local>\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n";
            $subject = 'TeamBox: ' . $ann['title'];
            $body    = $ann['message'] . "\n\nProject: " . $ann['project_name'];

            foreach ($users as $u) {
                $email = trim((string) $u['email']);
                if ($email === '') {
                    continue;
                }
                $result['email_attempted']++;
                if (@mail($email, $subject, $body, $headers)) {
                    $result['email']++;
                }
            }
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE `announcements`
            SET status = 'Sent', sent_at = NOW(), email_sent_count = ?
            WHERE id = ?
        ");
        $emailSent = (int) $result['email'];
        mysqli_stmt_bind_param($stmt, "ii", $emailSent, $announcementId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }
}

if (!function_exists('projectSendDeadlineReminders')) {
    /**
     * Create one in-app reminder per student per day for projects whose
     * deadline is within the configured reminder window.
     *
     * @return int number of notifications created
     */
    function projectSendDeadlineReminders($conn)
    {
        $created = 0;

        $rs = mysqli_query($conn, "
            SELECT id, name, course_id, visibility, period_end, reminder_days
            FROM `projects`
            WHERE is_draft = 0
              AND status <> 'Archived'
              AND period_end IS NOT NULL
              AND period_end >= CURDATE()
              AND period_end <= DATE_ADD(CURDATE(), INTERVAL reminder_days DAY)
              AND (last_reminder_sent IS NULL OR last_reminder_sent < CURDATE())
        ");
        if (!$rs) {
            return 0;
        }

        $projects = array();
        while ($row = mysqli_fetch_assoc($rs)) {
            $projects[] = $row;
        }
        mysqli_free_result($rs);

        $ins = mysqli_prepare($conn, "
            INSERT INTO `notifications` (user_id, type, title, message, link, is_read)
            VALUES (?, 'system', ?, ?, 'my_team.php', 0)
        ");

        foreach ($projects as $p) {
            $daysLeft = (int) floor((strtotime($p['period_end']) - strtotime(date('Y-m-d'))) / 86400);
            $title    = 'Project deadline reminder';
            $message  = $p['name'] . ' is due on '
                      . date('d M Y', strtotime($p['period_end']))
                      . ($daysLeft === 0 ? ' (today).' : " ({$daysLeft} day(s) left).");

            $users = projectAudienceUsers($conn, (int) $p['course_id'], $p['visibility']);
            foreach ($users as $u) {
                $uid = (int) $u['user_id'];
                mysqli_stmt_bind_param($ins, "iss", $uid, $title, $message);
                if (mysqli_stmt_execute($ins)) {
                    $created++;
                }
            }

            $stmt = mysqli_prepare($conn, "UPDATE `projects` SET last_reminder_sent = CURDATE() WHERE id = ?");
            $pid  = (int) $p['id'];
            mysqli_stmt_bind_param($stmt, "i", $pid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($ins);
        return $created;
    }
}
