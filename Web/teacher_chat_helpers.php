<?php
// teacher_chat_helpers.php
// Shared queries for the teacher-side group chat.

if (!function_exists('teacherChatGroups')) {
    /**
     * Groups under the teacher's supervised projects, newest conversation first.
     */
    function teacherChatGroups($conn, $teacherId, $userId)
    {
        $groups = array();

        $stmt = mysqli_prepare($conn, "
            SELECT
                g.id,
                g.name,
                g.status,
                g.max_members,
                g.project_id,
                p.name AS project_name,
                (SELECT COUNT(*) FROM `group_members` gm WHERE gm.group_id = g.id) AS member_count,
                lm.content     AS last_content,
                lm.created_at  AS last_at,
                COALESCE(su.username, stu.username) AS last_sender,
                COALESCE(unread.c, 0) AS unread
            FROM `groups` g
            INNER JOIN `projects` p ON p.id = g.project_id
            LEFT JOIN `messages` lm ON lm.id = (
                SELECT MAX(m2.id)
                FROM `messages` m2
                WHERE m2.group_id = g.id AND m2.recipient_id IS NULL
            )
            LEFT JOIN `students` ls ON ls.id = lm.sender_id
            LEFT JOIN `users` stu    ON stu.id = ls.user_id
            LEFT JOIN `users` su     ON su.id = lm.sender_user_id
            LEFT JOIN (
                SELECT m.group_id, COUNT(*) AS c
                FROM `messages` m
                LEFT JOIN `message_reads` mr
                       ON mr.group_id = m.group_id AND mr.user_id = ?
                WHERE m.recipient_id IS NULL
                  AND m.id > COALESCE(mr.last_read_message_id, 0)
                  AND (m.sender_user_id IS NULL OR m.sender_user_id <> ?)
                GROUP BY m.group_id
            ) unread ON unread.group_id = g.id
            WHERE p.supervisor_id = ?
              AND g.status IN ('Voting', 'Executing')
              AND NOT EXISTS (
                  SELECT 1 FROM `group_requests` gr
                  WHERE gr.project_id = g.project_id
                    AND gr.group_name = g.name
                    AND gr.status = 'Pending'
              )
            ORDER BY (lm.created_at IS NULL), lm.created_at DESC, g.id DESC
        ");

        mysqli_stmt_bind_param($stmt, "iii", $userId, $userId, $teacherId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $groups[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $groups;
    }
}

if (!function_exists('teacherChatAccess')) {
    /**
     * Returns group + project data when the teacher supervises that project.
     */
    function teacherChatAccess($conn, $groupId, $teacherId)
    {
        $stmt = mysqli_prepare($conn, "
            SELECT g.id, g.name, g.status, g.project_id, g.course_id,
                   p.name AS project_name, p.supervisor_id
            FROM `groups` g
            INNER JOIN `projects` p ON p.id = g.project_id
            WHERE g.id = ? AND p.supervisor_id = ?
              AND g.status IN ('Voting', 'Executing')
              AND NOT EXISTS (
                  SELECT 1 FROM `group_requests` gr
                  WHERE gr.project_id = g.project_id
                    AND gr.group_name = g.name
                    AND gr.status = 'Pending'
              )
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $groupId, $teacherId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        return $row;
    }
}

if (!function_exists('teacherChatMaxId')) {
    function teacherChatMaxId($conn, $groupId)
    {
        $stmt = mysqli_prepare($conn, "
            SELECT COALESCE(MAX(id), 0) AS max_id
            FROM `messages`
            WHERE group_id = ? AND recipient_id IS NULL
        ");
        mysqli_stmt_bind_param($stmt, "i", $groupId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        return (int) $row['max_id'];
    }
}

if (!function_exists('teacherChatMessages')) {
    /**
     * Group chat messages. afterId = 0 returns the latest 300, in ascending order.
     */
    function teacherChatMessages($conn, $groupId, $afterId = 0, $limit = 300)
    {
        $messages = array();
        $limit = max(1, min(300, (int) $limit));

        if ($afterId > 0) {
            $stmt = mysqli_prepare($conn, "
                SELECT
                    m.id, m.content, m.created_at, m.sender_id, m.sender_user_id,
                    m.attachment_id, f.file_name AS attachment_name,
                    COALESCE(su.username, stu.username) AS sender_name,
                    CASE WHEN m.sender_user_id IS NOT NULL THEN 'teacher'
                         ELSE COALESCE(gm.role, 'member') END AS sender_role
                FROM `messages` m
                LEFT JOIN `students` s ON s.id = m.sender_id
                LEFT JOIN `users` stu   ON stu.id = s.user_id
                LEFT JOIN `users` su    ON su.id = m.sender_user_id
                LEFT JOIN `files` f     ON f.id = m.attachment_id
                LEFT JOIN `group_members` gm
                       ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
                WHERE m.group_id = ?
                  AND m.recipient_id IS NULL
                  AND m.id > ?
                ORDER BY m.id ASC
                LIMIT {$limit}
            ");
            mysqli_stmt_bind_param($stmt, "ii", $groupId, $afterId);
        } else {
            $stmt = mysqli_prepare($conn, "
                SELECT * FROM (
                    SELECT
                        m.id, m.content, m.created_at, m.sender_id, m.sender_user_id,
                        m.attachment_id, f.file_name AS attachment_name,
                        COALESCE(su.username, stu.username) AS sender_name,
                        CASE WHEN m.sender_user_id IS NOT NULL THEN 'teacher'
                             ELSE COALESCE(gm.role, 'member') END AS sender_role
                    FROM `messages` m
                    LEFT JOIN `students` s ON s.id = m.sender_id
                    LEFT JOIN `users` stu   ON stu.id = s.user_id
                    LEFT JOIN `users` su    ON su.id = m.sender_user_id
                    LEFT JOIN `files` f     ON f.id = m.attachment_id
                    LEFT JOIN `group_members` gm
                           ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
                    WHERE m.group_id = ?
                      AND m.recipient_id IS NULL
                    ORDER BY m.id DESC
                    LIMIT {$limit}
                ) recent
                ORDER BY recent.id ASC
            ");
            mysqli_stmt_bind_param($stmt, "i", $groupId);
        }

        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $messages[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $messages;
    }
}

if (!function_exists('teacherChatMarkRead')) {
    function teacherChatMarkRead($conn, $userId, $groupId, $messageId)
    {
        $messageId = (int) $messageId;
        if ($messageId <= 0) {
            return;
        }

        $stmt = mysqli_prepare($conn, "
            INSERT INTO `message_reads` (user_id, group_id, last_read_message_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))
        ");
        mysqli_stmt_bind_param($stmt, "iii", $userId, $groupId, $messageId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if (!function_exists('teacherChatPinned')) {
    function teacherChatPinned($conn, $groupId, $limit = 3)
    {
        $pinned = array();
        $limit  = max(1, min(10, (int) $limit));

        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id, m.content, m.pinned_at, m.created_at,
                COALESCE(su.username, stu.username) AS sender_name
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users` stu   ON stu.id = s.user_id
            LEFT JOIN `users` su    ON su.id = m.sender_user_id
            WHERE m.group_id = ?
              AND m.recipient_id IS NULL
              AND m.is_pinned = 1
            ORDER BY m.pinned_at DESC, m.id DESC
            LIMIT {$limit}
        ");
        mysqli_stmt_bind_param($stmt, "i", $groupId);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $pinned[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $pinned;
    }
}

if (!function_exists('teacherChatInitials')) {
    function teacherChatInitials($name)
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr((string) $name, 0, 2));
    }
}

if (!function_exists('teacherChatAvatarBg')) {
    function teacherChatAvatarBg($name, $palette)
    {
        return $palette[abs(crc32((string) $name)) % count($palette)];
    }
}
