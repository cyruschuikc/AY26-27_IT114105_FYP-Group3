<?php
session_start();

require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

if (!isset($conn) || !$conn) {
    die('Database connection not available. Check db.php.');
}

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// =========================================================
// 自動建立投票所需資料表（如果不存在）
// =========================================================
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `chat_polls` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_id` int(11) NOT NULL,
        `creator_id` int(11) NOT NULL,
        `title` varchar(200) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `chat_poll_options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `poll_id` int(11) NOT NULL,
        `option_text` varchar(200) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `poll_id` (`poll_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS `chat_poll_votes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `poll_id` int(11) NOT NULL,
        `option_id` int(11) NOT NULL,
        `voter_id` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_vote` (`poll_id`,`voter_id`),
        KEY `option_id` (`option_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// =========================================================
// 更新自己 last_active_at
// =========================================================
$stmtActive = mysqli_prepare($conn, "UPDATE `users` SET `last_active_at` = NOW() WHERE id = ?");
mysqli_stmt_bind_param($stmtActive, "i", $userID);
mysqli_stmt_execute($stmtActive);
mysqli_stmt_close($stmtActive);

// =========================================================
// 1. 取 student_id
// =========================================================
$studentID   = 0;
$studentName = '';

$stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, u.username
    FROM `students` s
    JOIN `users` u ON u.id = s.user_id
    WHERE s.user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

if (!$rc) {
    header('Location: logout.php');
    exit;
}
$studentID   = (int) $rc['student_id'];
$studentName = $rc['username'];

// =========================================================
// 2. 未讀通知數量 + 預覽列表
// =========================================================
$unreadNotifications = 0;

$stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS c FROM `notifications`
    WHERE user_id = ? AND is_read = 0
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$rc = mysqli_fetch_assoc($rs);
$unreadNotifications = (int) $rc['c'];
mysqli_stmt_close($stmt);

// 未讀通知預覽（最多 8 條）
$unreadList = array();
$stmt = mysqli_prepare($conn, "
    SELECT id, type, title, message, created_at
    FROM `notifications`
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 8
");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($rowN = mysqli_fetch_assoc($rs)) {
    $unreadList[] = $rowN;
}
mysqli_stmt_close($stmt);

// =========================================================
// 3. 取我所有 group
// =========================================================
$myGroups = array();

$stmt = mysqli_prepare($conn, "
    SELECT
        g.id            AS group_id,
        g.name          AS group_name,
        g.status,
        g.max_members,
        g.project_id,
        g.course_id,
        g.leader_id,
        p.name          AS project_name,
        c.code          AS course_code
    FROM `group_members` gm
    JOIN `groups` g ON g.id = gm.group_id
    LEFT JOIN `projects` p ON p.id = g.project_id
    LEFT JOIN `courses`  c ON c.id = g.course_id
    WHERE gm.student_id = ?
      AND g.status IN ('Voting', 'Executing')
    ORDER BY g.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $studentID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $myGroups[] = $row;
}
mysqli_stmt_close($stmt);

// =========================================================
// 4. 決定 active group
// =========================================================
$activeGroupID = 0;

if (isset($_GET['group_id'])) {
    $activeGroupID = (int) $_GET['group_id'];
}

$myGroup = null;

if ($activeGroupID > 0) {
    foreach ($myGroups as $g) {
        if ((int)$g['group_id'] === $activeGroupID) {
            $myGroup = $g;
            break;
        }
    }
}

if (!$myGroup && count($myGroups) > 0) {
    $myGroup = $myGroups[0];
    $activeGroupID = (int) $myGroup['group_id'];
}

// =========================================================
// 4.5 DM 模式（?dm=student_id）
// =========================================================
$dmStudentID = 0;
$dmStudent   = null;

if (isset($_GET['dm'])) {
    $dmStudentID = (int) $_GET['dm'];
}

if ($dmStudentID > 0 && $myGroup) {
    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            u.username      AS name,
            gm.role
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ? AND gm.student_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "ii", $activeGroupID, $dmStudentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $dmStudent = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$dmStudent) {
        $dmStudentID = 0;
        $dmStudent   = null;
    }
}

// =========================================================
// 4.6 標記 active group / DM 為已讀
// =========================================================
if ($myGroup) {

    if ($dmStudentID > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT MAX(id) AS max_id
            FROM `messages`
            WHERE group_id = ?
              AND (
                    (sender_id = ? AND recipient_id = ?)
                 OR (sender_id = ? AND recipient_id = ?)
              )
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiiii",
                $activeGroupID,
                $studentID, $dmStudentID,
                $dmStudentID, $studentID
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT MAX(id) AS max_id
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id IS NULL
        ");
        mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    }

    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rowMax = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    $maxReadID = $rowMax && $rowMax['max_id'] ? (int)$rowMax['max_id'] : 0;

    if ($maxReadID > 0) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `message_reads` (user_id, group_id, last_read_message_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id))
        ");
        mysqli_stmt_bind_param($stmt, "iii", $userID, $activeGroupID, $maxReadID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// =========================================================
// 4.7 計算每個 group 嘅未讀數 + 每個成員嘅未讀 DM 數
// =========================================================
$unreadByGroup  = array();
$unreadByMember = array();

if (count($myGroups) > 0) {
    foreach ($myGroups as $g) {
        $gid = (int)$g['group_id'];

        $stmt = mysqli_prepare($conn, "
            SELECT last_read_message_id
            FROM `message_reads`
            WHERE user_id = ? AND group_id = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $userID, $gid);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $rowRead = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        $lastRead = $rowRead ? (int)$rowRead['last_read_message_id'] : 0;

        $stmt = mysqli_prepare($conn, "
            SELECT COUNT(*) AS c
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id IS NULL
              AND sender_id <> ?
              AND id > ?
        ");
        mysqli_stmt_bind_param($stmt, "iii", $gid, $studentID, $lastRead);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        $rc = mysqli_fetch_assoc($rs);
        mysqli_stmt_close($stmt);

        $unreadByGroup[$gid] = (int)$rc['c'];

        $stmt = mysqli_prepare($conn, "
            SELECT sender_id, COUNT(*) AS c
            FROM `messages`
            WHERE group_id = ?
              AND recipient_id = ?
              AND sender_id <> ?
              AND id > ?
            GROUP BY sender_id
        ");
        mysqli_stmt_bind_param($stmt, "iiii", $gid, $studentID, $studentID, $lastRead);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($rowDm = mysqli_fetch_assoc($rs)) {
            $sid = (int)$rowDm['sender_id'];
            if (!isset($unreadByMember[$sid])) {
                $unreadByMember[$sid] = 0;
            }
            $unreadByMember[$sid] += (int)$rowDm['c'];
        }
        mysqli_stmt_close($stmt);
    }
}

// =========================================================
// 4.8 AJAX：投票（votePoll）
// =========================================================
if (isset($_POST['votePoll']) && $myGroup) {
    header('Content-Type: application/json');

    $pollID   = isset($_POST['poll_id'])   ? (int)$_POST['poll_id']   : 0;
    $optionID = isset($_POST['option_id']) ? (int)$_POST['option_id'] : 0;

    $stmt = mysqli_prepare($conn, "SELECT id FROM `chat_polls` WHERE id = ? AND group_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ii", $pollID, $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $pollRow = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$pollRow) {
        echo json_encode(array('ok' => false, 'error' => 'Poll not found'));
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM `chat_poll_options` WHERE id = ? AND poll_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ii", $optionID, $pollID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $optRow = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$optRow) {
        echo json_encode(array('ok' => false, 'error' => 'Option not found'));
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        INSERT INTO `chat_poll_votes` (poll_id, option_id, voter_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE option_id = VALUES(option_id)
    ");
    mysqli_stmt_bind_param($stmt, "iii", $pollID, $optionID, $studentID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $counts = array();
    $total  = 0;
    $stmt = mysqli_prepare($conn, "
        SELECT o.id, COUNT(v.id) AS c
        FROM `chat_poll_options` o
        LEFT JOIN `chat_poll_votes` v ON v.option_id = o.id
        WHERE o.poll_id = ?
        GROUP BY o.id
    ");
    mysqli_stmt_bind_param($stmt, "i", $pollID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($rs)) {
        $counts[(int)$r['id']] = (int)$r['c'];
        $total += (int)$r['c'];
    }
    mysqli_stmt_close($stmt);

    echo json_encode(array(
            'ok'    => true,
            'counts'=> $counts,
            'total' => $total,
            'voted' => $optionID
    ));
    exit;
}

// =========================================================
// 4.9 POST：建立投票（createPoll）
// =========================================================
if (isset($_POST['createPoll']) && $myGroup) {
    $pollTitle = trim(isset($_POST['pollTitle']) ? $_POST['pollTitle'] : '');

    $rawOptions = isset($_POST['pollOption']) ? $_POST['pollOption'] : array();
    $pollOptions = array();
    if (is_array($rawOptions)) {
        foreach ($rawOptions as $opt) {
            $opt = trim($opt);
            if ($opt !== '') {
                $pollOptions[] = $opt;
            }
        }
    }

    if ($pollTitle !== '' && count($pollOptions) >= 2) {
        mysqli_begin_transaction($conn);
        $ok = true;

        $stmt = mysqli_prepare($conn, "INSERT INTO `chat_polls` (group_id, creator_id, title) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $activeGroupID, $studentID, $pollTitle);
        if (!mysqli_stmt_execute($stmt)) { $ok = false; }
        $newPollID = $ok ? (int)mysqli_insert_id($conn) : 0;
        mysqli_stmt_close($stmt);

        if ($ok && $newPollID > 0) {
            foreach ($pollOptions as $optText) {
                $stmt = mysqli_prepare($conn, "INSERT INTO `chat_poll_options` (poll_id, option_text) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "is", $newPollID, $optText);
                if (!mysqli_stmt_execute($stmt)) { $ok = false; }
                mysqli_stmt_close($stmt);
                if (!$ok) break;
            }
        }

        if ($ok && $newPollID > 0) {
            $pollContent = '__POLL__:' . $newPollID;
            $stmt = mysqli_prepare($conn, "
                INSERT INTO `messages` (group_id, sender_id, recipient_id, content)
                VALUES (?, ?, NULL, ?)
            ");
            mysqli_stmt_bind_param($stmt, "iis", $activeGroupID, $studentID, $pollContent);
            if (!mysqli_stmt_execute($stmt)) { $ok = false; }
            mysqli_stmt_close($stmt);
        }

        if ($ok) {
            mysqli_commit($conn);
            header('Location: student_chat.php?group_id=' . $activeGroupID . '&msg=poll_created');
            exit;
        } else {
            mysqli_rollback($conn);
        }
    }

    header('Location: student_chat.php?group_id=' . $activeGroupID . '&msg=poll_error');
    exit;
}

// =========================================================
// 4.10 AJAX：置頂 / 取消置頂（pinMessage）
// =========================================================
if (isset($_POST['pinMessage']) && $myGroup) {
    header('Content-Type: application/json');

    $messageID = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

    $stmt = mysqli_prepare($conn, "
        SELECT is_pinned FROM `messages`
        WHERE id = ? AND group_id = ? AND sender_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "iii", $messageID, $activeGroupID, $studentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rowMsg = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$rowMsg) {
        echo json_encode(array('ok' => false, 'error' => 'Not allowed'));
        exit;
    }

    $newPinned = ((int)$rowMsg['is_pinned'] === 1) ? 0 : 1;

    if ($newPinned) {
        $stmt = mysqli_prepare($conn, "
            UPDATE `messages`
            SET is_pinned = 1, pinned_at = NOW(), pinned_by_user_id = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $userID, $messageID);
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE `messages`
            SET is_pinned = 0, pinned_at = NULL, pinned_by_user_id = NULL
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $messageID);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo json_encode(array('ok' => true, 'pinned' => $newPinned));
    exit;
}

// =========================================================
// 4.11 AJAX：刪除訊息（deleteMessage）
// =========================================================
if (isset($_POST['deleteMessage']) && $myGroup) {
    header('Content-Type: application/json');

    $messageID = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

    $stmt = mysqli_prepare($conn, "
        SELECT id, content FROM `messages`
        WHERE id = ? AND group_id = ? AND sender_id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "iii", $messageID, $activeGroupID, $studentID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rowMsg = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);

    if (!$rowMsg) {
        echo json_encode(array('ok' => false, 'error' => 'Not allowed'));
        exit;
    }

    $contentRaw = trim((string)$rowMsg['content']);
    if (preg_match('/^__POLL__:(\d+)$/', $contentRaw, $pm)) {
        $pollID = (int)$pm[1];

        $stmt = mysqli_prepare($conn, "DELETE FROM `chat_poll_votes` WHERE poll_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $pollID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "DELETE FROM `chat_poll_options` WHERE poll_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $pollID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "DELETE FROM `chat_polls` WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $pollID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM `messages` WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $messageID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo json_encode(array('ok' => true));
    exit;
}

// =========================================================
// 5. 處理 POST：發送訊息（文字 + 附件 可同時）
// =========================================================
$flashMsg  = '';
$flashType = '';

if (isset($_POST['sendMessage']) && $myGroup) {

    $messageText  = trim(isset($_POST['message']) ? $_POST['message'] : '');
    $attachmentID = null;

    $recipientID = null;
    if (isset($_POST['recipient_id']) && (int)$_POST['recipient_id'] > 0) {
        $recipientID = (int) $_POST['recipient_id'];
    }

    if (isset($_FILES['chatFile'])
            && $_FILES['chatFile']['error'] === UPLOAD_ERR_OK
            && $_FILES['chatFile']['size'] > 0) {

        $file     = $_FILES['chatFile'];
        $origName = $file['name'];
        $fileSize = $file['size'];
        $tmpPath  = $file['tmp_name'];

        $allowedExt = array(
                'pdf', 'doc', 'docx', 'ppt', 'pptx',
                'xls', 'xlsx', 'png', 'jpg', 'jpeg',
                'txt', 'zip', 'fig', 'sql', 'ipynb'
        );
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $flashMsg  = 'File type not allowed.';
            $flashType = 'error';
        } else {
            if (!function_exists('formatBytes')) {
                function formatBytes($bytes) {
                    if ($bytes < 1024)               return $bytes . ' B';
                    if ($bytes < 1024 * 1024)        return round($bytes / 1024, 1) . ' KB';
                    if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 1) . ' MB';
                    return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
                }
            }
            $sizeText = formatBytes($fileSize);
            $fileType = strtoupper($ext);

            $storedName = 'chat' . $activeGroupID . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

            $uploadDir = __DIR__ . '/uploads/chat_files/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $targetPath = $uploadDir . $storedName;

            if (!move_uploaded_file($tmpPath, $targetPath)) {
                $flashMsg  = 'Failed to save uploaded file.';
                $flashType = 'error';
            } else {
                $stmt = mysqli_prepare($conn, "
                    INSERT INTO `files`
                        (project_id, group_id, task_id, uploader_id,
                         file_name, stored_name, file_size, file_type, status)
                    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'Unviewed')
                ");
                $projectId = (int) $myGroup['project_id'];

                mysqli_stmt_bind_param(
                        $stmt, "iiissss",
                        $projectId, $activeGroupID, $studentID,
                        $origName, $storedName, $sizeText, $fileType
                );

                if (mysqli_stmt_execute($stmt)) {
                    $attachmentID = (int) mysqli_insert_id($conn);
                } else {
                    $flashMsg  = 'Failed to save file: ' . mysqli_error($conn);
                    $flashType = 'error';
                    @unlink($targetPath);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if ($flashType !== 'error' && ($messageText !== '' || $attachmentID !== null)) {

        $stmt = mysqli_prepare($conn, "
            INSERT INTO `messages` (group_id, sender_id, recipient_id, content, attachment_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiisi",
                $activeGroupID, $studentID, $recipientID,
                $messageText,
                $attachmentID
        );

        if (mysqli_stmt_execute($stmt)) {
            $flashMsg  = 'Message sent.';
            $flashType = 'success';
        } else {
            $flashMsg  = 'Failed to send message: ' . mysqli_error($conn);
            $flashType = 'error';
        }
        mysqli_stmt_close($stmt);

        if ($recipientID !== null) {
            header('Location: student_chat.php?group_id=' . $activeGroupID . '&dm=' . $recipientID);
        } else {
            header('Location: student_chat.php?group_id=' . $activeGroupID);
        }
        exit;
    } elseif ($flashType !== 'error') {
        $flashMsg  = 'Please type a message or attach a file.';
        $flashType = 'error';
    }
}

// =========================================================
// 5.5 來自 GET 的 flash
// =========================================================
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'poll_created') {
        $flashMsg  = 'Vote created and posted to the chat.';
        $flashType = 'success';
    } elseif ($_GET['msg'] === 'poll_error') {
        $flashMsg  = 'Could not create the vote. Please check the title and at least 2 options.';
        $flashType = 'error';
    }
}

// =========================================================
// 6. 拎 group 資料
// =========================================================
$groupMembers   = array();
$messages       = array();
$allMembers     = array();
$pinnedMessages = array();

$groupTeacher   = null;

if ($myGroup) {

    // Project supervisor also participates in this group chat.
    $stmt = mysqli_prepare($conn, "
        SELECT t.id AS teacher_id, t.user_id, u.username AS name,
               u.last_active_at,
               (u.last_active_at IS NOT NULL AND
                u.last_active_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online
        FROM `groups` g
        JOIN `projects` p ON p.id = g.project_id
        JOIN `teachers` t ON t.id = p.supervisor_id
        JOIN `users` u ON u.id = t.user_id
        WHERE g.id = ?
        LIMIT 1
    " );
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $groupTeacher = mysqli_fetch_assoc($rs);
    mysqli_stmt_close($stmt);


    $stmt = mysqli_prepare($conn, "
        SELECT
            s.id            AS student_id,
            u.username      AS name,
            gm.role,
            u.last_active_at,
            (u.last_active_at IS NOT NULL
             AND u.last_active_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online
        FROM `group_members` gm
        JOIN `students` s ON s.id = gm.student_id
        JOIN `users`    u ON u.id = s.user_id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, s.id
    ");
    mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $groupMembers[] = $row;
    }
    mysqli_stmt_close($stmt);

    if (count($myGroups) > 0) {
        $groupIds = array();
        foreach ($myGroups as $g) {
            $groupIds[] = (int)$g['group_id'];
        }

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $types = str_repeat('i', count($groupIds));

        $sql = "
            SELECT
                s.id            AS student_id,
                u.username      AS name,
                u.last_active_at,
                (u.last_active_at IS NOT NULL
                 AND u.last_active_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) AS is_online,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS group_names
            FROM `group_members` gm
            JOIN `students` s ON s.id = gm.student_id
            JOIN `users`    u ON u.id = s.user_id
            JOIN `groups`   g ON g.id = gm.group_id
            WHERE gm.group_id IN ($placeholders)
            GROUP BY s.id, u.username, u.last_active_at
            ORDER BY u.username
        ";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$groupIds);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $allMembers[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    if ($dmStudentID > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.created_at,
                m.sender_id,
                m.sender_user_id,
                m.recipient_id,
                m.attachment_id,
                m.is_pinned,
                COALESCE(su.username, tu.username) AS sender_name,
                CASE WHEN tu.role = 'teacher' THEN 'teacher' ELSE gm.role END AS sender_role,
                f.file_name     AS attachment_name,
                f.file_size     AS attachment_size,
                f.file_type     AS attachment_type
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users` su ON su.id = s.user_id
            LEFT JOIN `users` tu ON tu.id = m.sender_user_id
            LEFT JOIN `group_members` gm
                   ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND (
                    (m.sender_id = ? AND m.recipient_id = ?)
                 OR (m.sender_id = ? AND m.recipient_id = ?)
              )
            ORDER BY m.created_at ASC
            LIMIT 200
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiiii",
                $activeGroupID,
                $studentID, $dmStudentID,
                $dmStudentID, $studentID
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.created_at,
                m.sender_id,
                m.sender_user_id,
                m.recipient_id,
                m.attachment_id,
                m.is_pinned,
                COALESCE(su.username, tu.username) AS sender_name,
                CASE WHEN tu.role = 'teacher' THEN 'teacher' ELSE gm.role END AS sender_role,
                f.file_name     AS attachment_name,
                f.file_size     AS attachment_size,
                f.file_type     AS attachment_type
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users` su ON su.id = s.user_id
            LEFT JOIN `users` tu ON tu.id = m.sender_user_id
            LEFT JOIN `group_members` gm
                   ON gm.group_id = m.group_id AND gm.student_id = m.sender_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND m.recipient_id IS NULL
            ORDER BY m.created_at ASC
            LIMIT 200
        ");
        mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    }

    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);

    // ---- 查詢所有置頂訊息 ----
    if ($dmStudentID > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.sender_id,
                m.sender_user_id,
                m.attachment_id,
                m.pinned_at,
                COALESCE(su.username, tu.username) AS sender_name,
                f.file_name     AS attachment_name
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users` su ON su.id = s.user_id
            LEFT JOIN `users` tu ON tu.id = m.sender_user_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND m.is_pinned = 1
              AND (
                    (m.sender_id = ? AND m.recipient_id = ?)
                 OR (m.sender_id = ? AND m.recipient_id = ?)
              )
            ORDER BY m.pinned_at DESC
        ");
        mysqli_stmt_bind_param(
                $stmt, "iiiii",
                $activeGroupID,
                $studentID, $dmStudentID,
                $dmStudentID, $studentID
        );
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT
                m.id,
                m.content,
                m.sender_id,
                m.sender_user_id,
                m.attachment_id,
                m.pinned_at,
                COALESCE(su.username, tu.username) AS sender_name,
                f.file_name     AS attachment_name
            FROM `messages` m
            LEFT JOIN `students` s ON s.id = m.sender_id
            LEFT JOIN `users` su ON su.id = s.user_id
            LEFT JOIN `users` tu ON tu.id = m.sender_user_id
            LEFT JOIN `files` f ON f.id = m.attachment_id
            WHERE m.group_id = ?
              AND m.recipient_id IS NULL
              AND m.is_pinned = 1
            ORDER BY m.pinned_at DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $activeGroupID);
    }
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $pinnedMessages[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// =========================================================
// 7. Helper
// =========================================================
if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Chat | TeamBox</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { min-height: 100vh; background: #f5f5f5; color: #222; }

        .navbar {
            width: 100%; padding: 15px 30px; background: black;
            display: flex; flex-direction: column; align-items: center; gap: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-top {
            position: relative; width: 100%; min-height: 65px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-area { display: flex; justify-content: center; align-items: center; gap: 12px; }
        .logo-area img { width: 65px; height: 65px; object-fit: contain; display: block; }
        .logo-area span { color: white; font-size: 30px; font-weight: bold; }

        /* Calendar logo beside notification bell */
        .calendar-link {
            position: absolute;
            right: 52px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 7px;
            border-radius: 50%;
            text-decoration: none;
            transition: background 0.3s;
        }
        .calendar-link:hover { background: rgba(255, 255, 255, 0.15); }
        .calendar-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        /* =========================================================
           鈴鐺 + 未讀通知預覽
           ========================================================= */
        .bell-wrap {
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-50%);
            z-index: 100;
        }
        .bell-link {
            position: relative;
            display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%;
            transition: background 0.3s;
            text-decoration: none;
        }
        .bell-link:hover { background: rgba(255, 255, 255, 0.15); }
        .bell-link img { width: 28px; height: 28px; object-fit: contain; display: block; }
        .bell-badge {
            position: absolute; top: 2px; right: 2px;
            min-width: 18px; height: 18px; padding: 0 5px;
            background: #d93025; color: white;
            border-radius: 9px; font-size: 11px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid black;
        }

        .bell-preview {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 340px;
            max-height: 420px;
            background: white;
            color: #222;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
            overflow: hidden;
            z-index: 200;
            display: flex;
            flex-direction: column;
        }
        .bell-wrap:hover .bell-preview {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .bell-preview::before {
            content: "";
            position: absolute;
            top: -6px;
            right: 18px;
            width: 12px;
            height: 12px;
            background: white;
            border-left: 1px solid #e0e0e0;
            border-top: 1px solid #e0e0e0;
            transform: rotate(45deg);
        }
        .bell-preview-header {
            padding: 12px 14px;
            border-bottom: 1px solid #eeeeee;
            font-size: 12px;
            font-weight: 700;
            color: #333;
            background: #fafbfc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }
        .bell-preview-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            max-height: 320px;
        }
        .bell-preview-item {
            display: block;
            padding: 10px 14px;
            border-bottom: 1px solid #f2f3f5;
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
        }
        .bell-preview-item:hover { background: #f7f8fa; }
        .bell-preview-item:last-child { border-bottom: none; }
        .bell-preview-title {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #222;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bell-preview-msg {
            display: block;
            font-size: 11.5px;
            color: #777;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        .bell-preview-time {
            display: block;
            font-size: 10.5px;
            color: #9aa0a6;
        }
        .bell-preview-footer {
            padding: 10px 14px;
            text-align: center;
            background: #fafbfc;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            font-weight: 700;
            color: #175196;
            text-decoration: none;
            flex-shrink: 0;
        }
        .bell-preview-footer:hover { background: #f0f6fd; }

        .function-menu {
            width: 100%; display: flex; justify-content: center; align-items: center;
            flex-wrap: wrap; gap: 22px;
        }
        .nav-link { color: white; font-size: 15px; text-decoration: none; padding: 6px 2px; transition: color 0.3s; }
        .nav-link:hover { color: #bbbbbb; }
        .nav-link.active { color: white; font-weight: bold; border-bottom: 2px solid white; }
        .logout-link { color: #ff8888; }
        .logout-link:hover { color: #ff4444; }

        .main-content { width: 90%; max-width: 1250px; margin: 40px auto; }

        .page-heading {
            padding: 30px; margin-bottom: 25px;
            background: white; border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }
        .page-heading h1 { margin-bottom: 10px; font-size: 35px; }
        .page-heading p { color: #666; font-size: 17px; line-height: 1.6; }

        .alert {
            padding: 14px 18px; margin-bottom: 20px;
            border-radius: 8px; font-size: 14px;
            border-left: 4px solid;
        }
        .alert-success { background: #eaf7ee; color: #176b2c; border-color: #2e9d4d; }
        .alert-error   { background: #fdeeee; color: #a21b1b; border-color: #d32f2f; }

        .empty-state {
            padding: 60px 40px; background: white;
            border-radius: 15px; border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .empty-state h2 { font-size: 24px; margin-bottom: 12px; }
        .empty-state p { color: #666; font-size: 15px; line-height: 1.6; max-width: 560px; margin: 0 auto 28px; }
        .empty-state .create-group-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 200px; height: 50px; padding: 0 24px;
            background: black; color: white;
            border: 2px solid black; border-radius: 9px;
            font-size: 16px; font-weight: bold;
            text-decoration: none; cursor: pointer; transition: 0.3s;
        }
        .empty-state .create-group-btn:hover { background: #333; }

        .chat-container {
            height: 700px;
            min-height: 500px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 290px 1fr;
            grid-template-rows: 100%;
        }

        .member-sidebar {
            background: #f7f7f7;
            border-right: 1px solid #dddddd;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        .sidebar-heading {
            padding: 15px 20px;
            border-bottom: 1px solid #dddddd;
            flex-shrink: 0;
        }
        .sidebar-heading h2 {
            margin-bottom: 4px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b6f78;
        }
        .sidebar-heading p { color: #8b8f98; font-size: 12px; }

        .group-list {
            padding: 10px 12px;
            max-height: 180px;
            overflow-y: auto;
            flex-shrink: 0;
            border-bottom: 1px solid #dddddd;
        }

        .group-item {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 6px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            cursor: pointer;
            transition: 0.2s;
        }
        .group-item:hover {
            border-color: black;
            background: #fafbfc;
        }
        .group-item.active {
            background: black;
            color: white;
            border-color: black;
        }

        .group-icon {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            background: #333;
            color: white;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            font-weight: bold;
        }
        .group-item.active .group-icon {
            background: white;
            color: black;
        }

        .group-details {
            flex: 1;
            min-width: 0;
        }
        .group-details h3 {
            font-size: 13px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .group-details p {
            font-size: 11px;
            color: #8b8f98;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .group-item.active .group-details p {
            color: #cccccc;
        }

        .member-list {
            padding: 10px 12px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }
        .member-item {
            width: 100%; padding: 10px 12px; margin-bottom: 6px;
            background: transparent; border: none; border-radius: 9px;
            display: flex; align-items: center; gap: 10px;
            text-align: left; cursor: pointer; transition: 0.3s;
        }
        .member-item:hover { background: #e8e8e8; }
        .member-item.active { background: black; color: white; }

        .member-avatar {
            width: 36px; height: 36px; flex-shrink: 0;
            background: #333; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 13px; font-weight: bold;
        }
        .member-item.active .member-avatar { background: white; color: black; }

        .member-details { flex: 1; min-width: 0; }
        .member-details h3 {
            margin-bottom: 3px;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .member-details p {
            color: #777;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .member-item.active .member-details p { color: #dddddd; }

        .member-right {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .member-status {
            width: 10px;
            height: 10px;
            flex-shrink: 0;
            background: #2e9d4d;
            border-radius: 50%;
        }
        .member-status.offline {
            background: #999999;
        }

        .unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            background: #d93025;
            color: white;
            border-radius: 9px;
            font-size: 10px;
            font-weight: bold;
            flex-shrink: 0;
            line-height: 1;
        }
        .group-item.active .unread-badge {
            background: white;
            color: #d93025;
        }
        .member-item.active .unread-badge {
            background: white;
            color: #d93025;
        }

        .chat-area {
            min-width: 0;
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            min-height: 81px;
            padding: 18px 24px;
            border-bottom: 1px solid #dddddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }
        .chat-title h2 { margin-bottom: 5px; font-size: 22px; }
        .chat-title p { color: #777; font-size: 13px; }

        /* =========================================================
           置頂訊息欄
           ========================================================= */
        .pinned-bar {
            flex-shrink: 0;
            background: #fff8e1;
            border-bottom: 1px solid #f0e0b0;
            padding: 8px 20px;
            max-height: 170px;
            overflow-y: auto;
        }
        .pinned-bar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #8a5c00;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pinned-bar-toggle {
            background: none;
            border: 1px solid #e0c98a;
            color: #8a5c00;
            cursor: pointer;
            font-size: 11px;
            padding: 3px 9px;
            border-radius: 5px;
            font-family: inherit;
            font-weight: 600;
            transition: 0.2s;
        }
        .pinned-bar-toggle:hover { background: rgba(138, 92, 0, 0.1); }
        .pinned-bar.collapsed .pinned-bar-list { display: none; }
        .pinned-bar.collapsed .pinned-bar-header { margin-bottom: 0; }
        .pinned-bar-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .pinned-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            background: white;
            border: 1px solid #f0e0b0;
            border-radius: 7px;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .pinned-item:hover {
            background: #fffaef;
            border-color: #e0c98a;
        }
        .pinned-item-sender {
            font-size: 11px;
            color: #8a5c00;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pinned-item-text {
            flex: 1;
            min-width: 0;
            font-size: 12px;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pinned-unpin {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 14px;
            padding: 0 4px;
            border-radius: 3px;
            line-height: 1;
            font-family: inherit;
            flex-shrink: 0;
            transition: 0.2s;
        }
        .pinned-unpin:hover { background: #fdecea; color: #c62828; }

        .message-row.highlight-flash {
            animation: highlightFlash 1.6s ease;
            border-radius: 10px;
        }
        @keyframes highlightFlash {
            0%   { background: rgba(255, 193, 7, 0.45); }
            100% { background: transparent; }
        }

        .message-area {
            flex: 1 1 auto;
            min-height: 0;
            padding: 24px;
            background: #fcfcfc;
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
            overscroll-behavior: contain;
        }

        .date-separator {
            margin: 12px 0 24px;
            display: flex; align-items: center; gap: 13px;
            color: #888; font-size: 12px;
        }
        .date-separator::before,
        .date-separator::after {
            content: ""; height: 1px; flex: 1; background: #dddddd;
        }

        .message-row {
            margin-bottom: 20px;
            display: flex; align-items: flex-end; gap: 11px;
            padding: 4px 6px;
            border-radius: 10px;
            transition: background 0.3s;
        }
        .message-row.my-message { justify-content: flex-end; }

        .message-avatar {
            width: 38px; height: 38px; flex-shrink: 0;
            background: #333; color: white; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            font-size: 12px; font-weight: bold;
        }
        .message-content {
            max-width: 65%;
            position: relative;
        }
        .sender-name { margin-bottom: 6px; color: #666; font-size: 12px; }
        .my-message .sender-name { text-align: right; }

        /* =========================================================
           自己訊息的 ⋯ 操作按鈕
           ========================================================= */
        .msg-actions {
            position: absolute;
            top: 0;
            left: -44px;
            z-index: 20;
        }
        .msg-menu-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: white;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #555;
            padding: 0;
            line-height: 1;
            font-family: inherit;
            transition: background 0.2s;
        }
        .msg-menu-btn:hover { background: #f0f0f0; }
        .my-message:hover .msg-menu-btn,
        .msg-menu-btn.menu-open {
            display: flex;
        }
        .msg-menu {
            position: absolute;
            top: 38px;
            left: 0;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            z-index: 30;
            min-width: 130px;
            overflow: hidden;
            display: none;
            padding: 4px;
        }
        .msg-menu.open { display: block; }
        .msg-menu button {
            display: block;
            width: 100%;
            padding: 9px 12px;
            background: transparent;
            border: none;
            text-align: left;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
            color: #222;
            border-radius: 6px;
        }
        .msg-menu button:hover { background: #f2f3f5; }
        .msg-menu button.danger { color: #c62828; }
        .msg-menu button.danger:hover { background: #fdecea; }

        .pinned-flag {
            display: inline-block;
            margin-left: 6px;
            font-size: 11px;
            color: #175196;
            font-weight: 600;
        }

        .message-bubble {
            padding: 13px 16px;
            background: #eeeeee; color: #222;
            border-radius: 4px 15px 15px 15px;
            font-size: 14px; line-height: 1.55; word-break: break-word;
        }
        .my-message .message-bubble {
            background: black; color: white;
            border-radius: 15px 4px 15px 15px;
        }

        .attachment-image-wrap {
            margin-top: 6px;
            max-width: 320px;
        }
        .attachment-image-wrap > a {
            display: block;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e6e8eb;
            background: #fafbfc;
            transition: 0.2s;
        }
        .attachment-image-wrap > a:hover {
            border-color: black;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .attachment-image {
            display: block;
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .attachment-image-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            margin-top: 4px;
            font-size: 11px;
            color: #8b8f98;
        }
        .my-message .attachment-image-meta { color: #a8acb4; }

        .file-name-small {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #175196;
            text-decoration: none;
            font-weight: 500;
            font-size: 11px;
        }
        .file-name-small:hover {
            text-decoration: underline;
            color: #123f75;
        }
        .my-message .file-name-small { color: #8ec5ff; }
        .my-message .file-name-small:hover { color: #b8dbff; }

        .file-size-small { flex-shrink: 0; }

        .attachment-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
            padding: 10px 12px;
            background: #f7f8fa;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            font-size: 13px;
            max-width: 320px;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: 0.2s;
        }
        .attachment-chip:hover {
            background: white;
            border-color: black;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }
        .my-message .attachment-chip {
            background: #1f232b;
            border-color: #2a2f3a;
        }
        .my-message .attachment-chip:hover {
            background: #2a2f3a;
            border-color: white;
        }

        .attachment-icon {
            width: 34px; height: 34px;
            background: black; color: white;
            border-radius: 6px;
            display: flex; justify-content: center; align-items: center;
            font-size: 10px; font-weight: bold;
            flex-shrink: 0;
        }
        .my-message .attachment-icon {
            background: white; color: black;
        }
        .attachment-info {
            flex: 1; min-width: 0;
        }
        .attachment-info h4 {
            font-size: 13px; font-weight: 600;
            margin-bottom: 3px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            color: #175196;
        }
        .my-message .attachment-info h4 { color: #8ec5ff; }
        .attachment-chip:hover .attachment-info h4 { text-decoration: underline; }

        .attachment-info p {
            font-size: 11px; color: #8b8f98;
        }
        .my-message .attachment-info p { color: #a8acb4; }

        .message-time {
            display: block; margin-top: 6px;
            color: #888; font-size: 11px;
        }
        .my-message .message-time { text-align: right; }

        /* =========================================================
           投票卡片
           ========================================================= */
        .poll-card {
            margin-top: 6px;
            padding: 14px 16px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 12px;
            max-width: 360px;
        }
        .my-message .poll-card {
            background: white;
            border-color: #e6e8eb;
        }
        .poll-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .poll-icon {
            width: 28px;
            height: 28px;
            background: #175196;
            color: white;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .poll-title {
            font-size: 15px;
            font-weight: 700;
            color: #222;
            word-break: break-word;
            line-height: 1.4;
        }
        .poll-option {
            display: block;
            width: 100%;
            text-align: left;
            padding: 10px 12px;
            background: #f7f8fa;
            border: 1.5px solid #e6e8eb;
            border-radius: 8px;
            margin-bottom: 6px;
            cursor: pointer;
            transition: 0.2s;
            position: relative;
            overflow: hidden;
            font-family: inherit;
        }
        .poll-option:last-of-type { margin-bottom: 0; }
        .poll-option:hover { border-color: #175196; }
        .poll-option.voted {
            border-color: #175196;
            background: #f0f6fd;
        }
        .poll-option-bar {
            position: absolute;
            top: 0; left: 0; bottom: 0;
            background: rgba(23, 81, 150, 0.12);
            z-index: 0;
            transition: width 0.3s;
        }
        .poll-option-text {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #222;
            font-weight: 500;
        }
        .poll-option-label {
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }
        .poll-option-count {
            color: #6b6f78;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .poll-meta {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #eef0f2;
            font-size: 11px;
            color: #8b8f98;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .poll-meta .voted-tag {
            color: #175196;
            font-weight: 600;
        }

        /* =========================================================
           輸入區
           ========================================================= */
        .message-input-area {
            flex-shrink: 0;
            padding: 17px 20px;
            background: white;
            border-top: 1px solid #dddddd;
            display: flex;
            align-items: flex-end;
            gap: 10px;
            position: relative;
        }

        .attach-button {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            background: black;
            color: white;
            border: 2px solid black;
            border-radius: 8px;
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            line-height: 1;
            font-weight: bold;
        }
        .attach-button:hover { background: #333; }
        .attach-button.has-file {
            background: #2e9d4d;
            border-color: #2e9d4d;
            color: white;
        }
        .attach-button input[type="file"] {
            display: none;
        }

        .attach-menu {
            position: absolute;
            bottom: 100%;
            left: 20px;
            margin-bottom: 8px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
            padding: 6px;
            display: none;
            flex-direction: column;
            gap: 4px;
            z-index: 60;
            min-width: 200px;
        }
        .attach-menu.open { display: flex; }
        .attach-menu-item {
            padding: 11px 14px;
            background: transparent;
            border: none;
            border-radius: 7px;
            text-align: left;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #222;
        }
        .attach-menu-item:hover { background: #f2f3f5; }
        .attach-menu-item .menu-emoji {
            font-size: 16px;
        }

        .selected-file {
            flex-shrink: 0;
            align-self: flex-end;
            display: none;
            align-items: center;
            gap: 6px;
            padding: 0 10px;
            height: 46px;
            background: #eaf7ee;
            border: 2px solid #2e9d4d;
            border-radius: 8px;
            font-size: 12px;
            color: #176b2c;
            font-weight: 600;
            max-width: 160px;
            overflow: hidden;
        }
        .selected-file.visible { display: flex; }
        .selected-file .file-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }
        .selected-file .remove-file {
            background: none;
            border: none;
            color: #a21b1b;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            padding: 0 2px;
        }
        .selected-file .remove-file:hover {
            color: #d32f2f;
        }

        .message-input {
            min-height: 46px;
            max-height: 110px;
            height: 46px;
            flex: 1;
            padding: 10px 14px;
            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 14px;
            line-height: 22px;
            font-family: inherit;
            outline: none;
            resize: none;
            overflow-y: auto;
            transition: border-color 0.2s, height 0.05s;
        }
        .message-input:focus { border-color: black; }

        .emoji-button {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            background: white;
            color: #333;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 22px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            line-height: 1;
        }
        .emoji-button:hover {
            background: #f7f7f7;
            border-color: black;
        }
        .emoji-button.active {
            background: black;
            color: white;
            border-color: black;
        }

        .send-button {
            min-width: 100px;
            height: 46px;
            flex-shrink: 0;
            align-self: flex-end;
            padding: 0 16px;
            background: black;
            color: white;
            border: 2px solid black;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .send-button:hover { background: #333; }
        .send-button:active { transform: scale(0.97); }
        .send-button:disabled {
            background: #cccccc;
            border-color: #cccccc;
            color: #888;
            cursor: not-allowed;
            transform: none;
        }

        .emoji-picker {
            position: absolute;
            bottom: 100%;
            right: 120px;
            margin-bottom: 10px;
            width: 340px;
            max-height: 280px;
            background: white;
            border: 1px solid #e6e8eb;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            display: none;
            z-index: 50;
            flex-direction: column;
        }
        .emoji-picker.open { display: flex; }

        .emoji-picker-header {
            padding: 10px 14px;
            border-bottom: 1px solid #eeeeee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
            font-size: 13px;
            font-weight: 600;
            color: #6b6f78;
            flex-shrink: 0;
        }
        .emoji-picker-close {
            background: none;
            border: none;
            color: #888;
            font-size: 18px;
            cursor: pointer;
            line-height: 1;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .emoji-picker-close:hover { background: #eeeeee; color: #d32f2f; }

        .emoji-categories {
            display: flex;
            gap: 2px;
            padding: 8px 10px;
            border-bottom: 1px solid #eeeeee;
            overflow-x: auto;
            flex-shrink: 0;
            scrollbar-width: thin;
        }
        .emoji-cat-btn {
            flex-shrink: 0;
            padding: 5px 8px;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }
        .emoji-cat-btn:hover { background: #f2f3f5; }
        .emoji-cat-btn.active { background: #175196; }

        .emoji-scroll {
            overflow-y: auto;
            padding: 10px;
            flex: 1;
            min-height: 0;
        }

        .emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 2px;
        }

        .emoji-item {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-size: 22px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.15s;
            padding: 0;
            line-height: 1;
        }
        .emoji-item:hover {
            background: #f2f3f5;
            transform: scale(1.2);
        }

        .input-hint {
            flex-shrink: 0;
            padding: 8px 20px;
            background: #fafafa;
            border-top: 1px solid #eeeeee;
            font-size: 12px;
            color: #8b8f98;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .input-hint .hint-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .input-hint .char-count { font-weight: 600; color: #6b6f78; }

        /* =========================================================
           投票 Modal
           ========================================================= */
        .vote-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .vote-modal-overlay.active { display: flex; }
        .vote-modal {
            background: white;
            border-radius: 14px;
            width: 100%;
            max-width: 480px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        }
        .vote-modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid #eeeeee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .vote-modal-header h3 { font-size: 18px; }
        .vote-modal-close {
            width: 34px;
            height: 34px;
            background: white;
            color: #555;
            border: 1.5px solid #cccccc;
            border-radius: 7px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            flex-shrink: 0;
        }
        .vote-modal-close:hover {
            background: #c62828;
            color: white;
            border-color: #c62828;
        }
        .vote-modal-body {
            padding: 18px 22px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }
        .vote-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #333;
            margin-bottom: 6px;
        }
        .vote-input {
            width: 100%;
            padding: 10px 12px;
            background: white;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            margin-bottom: 8px;
            outline: none;
            transition: border-color 0.2s;
        }
        .vote-input:focus { border-color: black; }
        .vote-option-input { margin-bottom: 8px; }
        .vote-add-option {
            background: white;
            color: #175196;
            border: 1.5px solid #175196;
            border-radius: 7px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 4px;
            transition: 0.2s;
        }
        .vote-add-option:hover { background: #175196; color: white; }
        .vote-modal-footer {
            padding: 14px 22px;
            border-top: 1px solid #eeeeee;
            text-align: right;
            flex-shrink: 0;
        }
        .vote-submit {
            background: black;
            color: white;
            border: 2px solid black;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .vote-submit:hover { background: #333; }

        .vote-option-row {
            display: flex;
            gap: 8px;
            align-items: stretch;
            margin-bottom: 8px;
        }
        .vote-option-row .vote-input {
            flex: 1;
            margin-bottom: 0;
        }
        .vote-remove-option {
            width: 42px;
            flex-shrink: 0;
            background: white;
            color: #c62828;
            border: 2px solid #cccccc;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-family: inherit;
            line-height: 1;
        }
        .vote-remove-option:hover {
            background: #c62828;
            color: white;
            border-color: #c62828;
        }
        .vote-option-row.deletable .vote-remove-option {
            display: flex;
        }

        @media (max-width: 850px) {
            .chat-container {
                height: auto; min-height: auto;
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
            }
            .member-sidebar {
                border-right: none;
                border-bottom: 1px solid #dddddd;
                max-height: 420px;
            }
            .group-list {
                max-height: 150px;
            }
            .member-list {
                max-height: 240px;
            }
            .chat-area { height: 600px; min-height: 500px; }

            .emoji-picker { width: 300px; right: 10px; }
            .attach-menu { left: 10px; }
            .bell-preview { width: 300px; }
        }
        @media (max-width: 650px) {
            .navbar { padding: 15px; }
            .logo-area img { width: 55px; height: 55px; }
            .logo-area span { font-size: 26px; }
            .calendar-link { right: 44px; width: 38px; height: 38px; }
            .calendar-link img { width: 22px; height: 22px; }
            .bell-link { width: 38px; height: 38px; }
            .bell-link img { width: 22px; height: 22px; }
            .bell-preview { width: calc(100vw - 40px); right: -8px; }
            .function-menu { flex-direction: column; gap: 12px; }
            .main-content { width: 94%; margin: 25px auto; }
            .page-heading h1 { font-size: 28px; }

            .chat-header { align-items: flex-start; flex-direction: column; }
            .message-content { max-width: 80%; }

            .message-input-area { flex-wrap: wrap; }
            .message-input { min-width: 100%; }
            .attach-button,
            .emoji-button { width: 46px; height: 46px; }
            .send-button { flex: 1; width: auto; }

            .emoji-picker { width: calc(100% - 20px); right: 10px; left: 10px; }
            .poll-card { max-width: 100%; }

            .msg-actions { left: -38px; }
            .msg-menu-btn { width: 28px; height: 28px; font-size: 16px; }
            .pinned-bar { padding: 8px 12px; }
            .pinned-item-sender { max-width: 80px; }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="navbar-top">
        <div class="logo-area">
            <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
            <span>TeamBox</span>
        </div>

        <a href="student_calendar.php" class="calendar-link" title="Calendar" aria-label="Calendar">
            <img src="cal_logo.png" alt="Calendar">
        </a>
        <div class="bell-wrap">
            <a href="student_notifications.php" class="bell-link" title="Notifications">
                <img src="bell.png" alt="Notifications">
                <?php if ($unreadNotifications > 0): ?>
                    <span class="bell-badge"><?php echo $unreadNotifications; ?></span>
                <?php endif; ?>
            </a>

            <?php if (count($unreadList) > 0): ?>
                <div class="bell-preview">
                    <div class="bell-preview-header">
                        Unread Notifications (<?php echo count($unreadList); ?>)
                    </div>
                    <div class="bell-preview-list">
                        <?php foreach ($unreadList as $n): ?>
                            <a href="student_notifications.php" class="bell-preview-item">
                                <span class="bell-preview-title"><?php echo htmlspecialchars($n['title']); ?></span>
                                <?php if (!empty($n['message'])): ?>
                                    <span class="bell-preview-msg"><?php echo htmlspecialchars($n['message']); ?></span>
                                <?php endif; ?>
                                <span class="bell-preview-time">
                                    <?php echo date('d M Y H:i', strtotime($n['created_at'])); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="student_notifications.php" class="bell-preview-footer">
                        View all notifications →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="function-menu">
        <a href="student_index.php" class="nav-link">Dashboard</a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
        <a href="my_team.php" class="nav-link">My Team</a>
        <a href="student_task.php" class="nav-link">Tasks</a>
        <a href="student_files.php" class="nav-link">Files</a>
        <a href="student_chat.php" class="nav-link active">Group Chat</a>
        <a href="student_contribution.php" class="nav-link">Contribution</a>
        <a href="student_notifications.php" class="nav-link">Notifications</a>
        <a href="student_profile.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout-link">Log Out</a>
    </div>
</nav>

<main class="main-content">

    <section class="page-heading">
        <h1>Group Chat</h1>
        <p>
            Communicate with your team members, discuss project tasks,
            share updates and exchange project files.
        </p>
    </section>

    <?php if (count($myGroups) === 0): ?>

        <div class="empty-state">
            <h2>You don't have an active team yet</h2>
            <p>
                Group chat is only available after your team has been submitted
                to your supervisor. Create or manage your group first.
            </p>
            <a href="student_create_group.php" class="create-group-btn">
                Go to Create Group
            </a>
        </div>

    <?php else: ?>

        <?php if ($flashMsg !== ''): ?>
            <div class="alert <?php echo $flashType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($flashMsg); ?>
            </div>
        <?php endif; ?>

        <section class="chat-container">

            <aside class="member-sidebar">

                <div class="sidebar-heading">
                    <h2>My Groups</h2>
                    <p><?php echo count($myGroups); ?> group<?php echo count($myGroups) > 1 ? 's' : ''; ?></p>
                </div>

                <div class="group-list">
                    <?php foreach ($myGroups as $g): ?>
                        <?php
                        $gid = (int)$g['group_id'];
                        $unread = isset($unreadByGroup[$gid]) ? (int)$unreadByGroup[$gid] : 0;
                        ?>
                        <button type="button"
                                class="group-item <?php echo ((int)$g['group_id'] === $activeGroupID && $dmStudentID === 0) ? 'active' : ''; ?>"
                                onclick="window.location.href='student_chat.php?group_id=<?php echo $gid; ?>'">
                            <div class="group-icon">GRP</div>
                            <div class="group-details">
                                <h3><?php echo htmlspecialchars($g['group_name']); ?></h3>
                                <p><?php echo htmlspecialchars($g['project_name']); ?></p>
                            </div>
                            <?php if ($unread > 0): ?>
                                <span class="unread-badge"><?php echo $unread > 99 ? '99+' : $unread; ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="member-list">
                    <?php if ($groupTeacher): ?>
                        <div class="member-item" title="Project supervisor · Group chat participant">
                            <div class="member-avatar"><?php echo htmlspecialchars(initials($groupTeacher['name'])); ?></div>
                            <div class="member-details">
                                <h3><?php echo htmlspecialchars($groupTeacher['name']); ?></h3>
                                <p>Teacher · Project Supervisor</p>
                            </div>
                            <div class="member-right">
                                <span class="member-status <?php echo ((int)$groupTeacher['is_online'] === 1) ? '' : 'offline'; ?>"
                                      title="<?php echo ((int)$groupTeacher['is_online'] === 1) ? 'Online' : 'Offline'; ?>"></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($allMembers as $m): ?>
                        <?php if ((int)$m['student_id'] === $studentID) continue; ?>
                        <?php
                        $sid = (int)$m['student_id'];
                        $dmUnread = isset($unreadByMember[$sid]) ? (int)$unreadByMember[$sid] : 0;
                        ?>
                        <button type="button"
                                class="member-item <?php echo ((int)$m['student_id'] === $dmStudentID) ? 'active' : ''; ?>"
                                onclick="openDMChat(<?php echo $sid; ?>)"
                                title="Message <?php echo htmlspecialchars($m['name']); ?>">
                            <div class="member-avatar">
                                <?php echo htmlspecialchars(initials($m['name'])); ?>
                            </div>
                            <div class="member-details">
                                <h3><?php echo htmlspecialchars($m['name']); ?></h3>
                                <p><?php echo htmlspecialchars($m['group_names']); ?></p>
                            </div>
                            <div class="member-right">
                                <?php if ($dmUnread > 0): ?>
                                    <span class="unread-badge"><?php echo $dmUnread > 99 ? '99+' : $dmUnread; ?></span>
                                <?php endif; ?>
                                <span class="member-status <?php echo ((int)$m['is_online'] === 1) ? '' : 'offline'; ?>"
                                      title="<?php echo ((int)$m['is_online'] === 1) ? 'Online' : 'Offline'; ?>"></span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>

            </aside>

            <div class="chat-area">

                <header class="chat-header">
                    <div class="chat-title">
                        <?php if ($dmStudentID > 0 && $dmStudent): ?>
                            <h2>DM · <?php echo htmlspecialchars($dmStudent['name']); ?></h2>
                            <p><?php echo htmlspecialchars($myGroup['group_name']); ?> · <?php echo htmlspecialchars($myGroup['project_name']); ?></p>
                        <?php else: ?>
                            <h2><?php echo htmlspecialchars($myGroup['group_name']); ?> Group Chat</h2>
                            <p><?php echo count($groupMembers) + ($groupTeacher ? 1 : 0); ?> participants · <?php echo htmlspecialchars($myGroup['project_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </header>

                <?php if (count($pinnedMessages) > 0): ?>
                    <div class="pinned-bar" id="pinnedBar">
                        <div class="pinned-bar-header">
                            <span>📌 Pinned Messages (<?php echo count($pinnedMessages); ?>)</span>
                            <button type="button"
                                    class="pinned-bar-toggle"
                                    id="pinnedToggleBtn"
                                    onclick="togglePinnedBar()">Hide</button>
                        </div>
                        <div class="pinned-bar-list">
                            <?php foreach ($pinnedMessages as $pm): ?>
                                <?php
                                $pinText = '';
                                $pinRaw  = trim((string)$pm['content']);

                                if (preg_match('/^__POLL__:(\d+)$/', $pinRaw, $pinPollMatch)) {
                                    $pollID2 = (int)$pinPollMatch[1];
                                    $pStmt2 = mysqli_prepare($conn, "SELECT title FROM `chat_polls` WHERE id = ? LIMIT 1");
                                    mysqli_stmt_bind_param($pStmt2, "i", $pollID2);
                                    mysqli_stmt_execute($pStmt2);
                                    $pRs2 = mysqli_stmt_get_result($pStmt2);
                                    $pRow2 = mysqli_fetch_assoc($pRs2);
                                    mysqli_stmt_close($pStmt2);
                                    $pinText = '📊 ' . ($pRow2 ? $pRow2['title'] : 'Poll');
                                } elseif (!empty($pm['attachment_name']) && $pinRaw === '') {
                                    $pinText = '📎 ' . $pm['attachment_name'];
                                } else {
                                    $pinText = $pinRaw;
                                }

                                if (mb_strlen($pinText) > 100) {
                                    $pinText = mb_substr($pinText, 0, 100) . '…';
                                }

                                $pinIsMe = ((int)$pm['sender_id'] === $studentID);
                                ?>
                                <div class="pinned-item"
                                     onclick="scrollToMessage(<?php echo (int)$pm['id']; ?>)"
                                     title="Jump to message">
                                    <span class="pinned-item-sender">
                                        <?php echo htmlspecialchars($pinIsMe ? 'You' : $pm['sender_name']); ?>
                                    </span>
                                    <span class="pinned-item-text"><?php echo htmlspecialchars($pinText); ?></span>
                                    <?php if ($pinIsMe): ?>
                                        <button type="button"
                                                class="pinned-unpin"
                                                onclick="event.stopPropagation(); unpinMessage(<?php echo (int)$pm['id']; ?>)"
                                                title="Unpin">✕</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="message-area" id="messageArea">

                    <?php if (count($messages) === 0): ?>
                        <p style="text-align:center; color:#888; padding:40px 0;">
                            <?php if ($dmStudentID > 0 && $dmStudent): ?>
                                No messages yet. Start the conversation with <?php echo htmlspecialchars($dmStudent['name']); ?> 👋
                            <?php else: ?>
                                No messages yet. Say hello to your team 👋
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php
                    $lastDate = '';
                    foreach ($messages as $msg):
                        $isMe = ((int)$msg['sender_id'] === $studentID);

                        $msgDate = date('d M Y', strtotime($msg['created_at']));
                        if ($msgDate !== $lastDate):
                            $lastDate = $msgDate;
                            ?>
                            <div class="date-separator"><?php echo htmlspecialchars($msgDate); ?></div>
                        <?php endif; ?>

                        <?php
                        $contentRaw = trim((string)$msg['content']);
                        $isPollMsg  = preg_match('/^__POLL__:(\d+)$/', $contentRaw, $pollMatch);
                        $pollID     = $isPollMsg ? (int)$pollMatch[1] : 0;

                        $pollRow        = null;
                        $pollOptions    = array();
                        $pollTotalVotes = 0;
                        $myVoteOptionID = 0;

                        if ($isPollMsg && $pollID > 0) {
                            $pStmt = mysqli_prepare($conn, "SELECT title, creator_id, created_at FROM `chat_polls` WHERE id = ? AND group_id = ? LIMIT 1");
                            mysqli_stmt_bind_param($pStmt, "ii", $pollID, $activeGroupID);
                            mysqli_stmt_execute($pStmt);
                            $pRs = mysqli_stmt_get_result($pStmt);
                            $pollRow = mysqli_fetch_assoc($pRs);
                            mysqli_stmt_close($pStmt);

                            if ($pollRow) {
                                $oStmt = mysqli_prepare($conn, "
                                    SELECT o.id, o.option_text, COUNT(v.id) AS vote_count
                                    FROM `chat_poll_options` o
                                    LEFT JOIN `chat_poll_votes` v ON v.option_id = o.id
                                    WHERE o.poll_id = ?
                                    GROUP BY o.id, o.option_text
                                    ORDER BY o.id
                                ");
                                mysqli_stmt_bind_param($oStmt, "i", $pollID);
                                mysqli_stmt_execute($oStmt);
                                $oRs = mysqli_stmt_get_result($oStmt);
                                while ($o = mysqli_fetch_assoc($oRs)) {
                                    $pollOptions[] = $o;
                                    $pollTotalVotes += (int)$o['vote_count'];
                                }
                                mysqli_stmt_close($oStmt);

                                $vStmt = mysqli_prepare($conn, "SELECT option_id FROM `chat_poll_votes` WHERE poll_id = ? AND voter_id = ? LIMIT 1");
                                mysqli_stmt_bind_param($vStmt, "ii", $pollID, $studentID);
                                mysqli_stmt_execute($vStmt);
                                $vRs = mysqli_stmt_get_result($vStmt);
                                $vRow = mysqli_fetch_assoc($vRs);
                                mysqli_stmt_close($vStmt);

                                if ($vRow) {
                                    $myVoteOptionID = (int)$vRow['option_id'];
                                }
                            }
                        }

                        $isPinned = !empty($msg['is_pinned']);
                        ?>

                        <div class="message-row <?php echo $isMe ? 'my-message' : ''; ?>"
                             data-msg-id="<?php echo (int)$msg['id']; ?>">
                            <?php if (!$isMe): ?>
                                <div class="message-avatar">
                                    <?php echo htmlspecialchars(initials($msg['sender_name'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="message-content">

                                <?php if ($isMe): ?>
                                    <div class="msg-actions">
                                        <button type="button"
                                                class="msg-menu-btn"
                                                onclick="toggleMsgMenu(event, <?php echo (int)$msg['id']; ?>)"
                                                title="More">⋯</button>
                                        <div class="msg-menu" id="msgMenu-<?php echo (int)$msg['id']; ?>">
                                            <button type="button"
                                                    onclick="pinMessage(<?php echo (int)$msg['id']; ?>)">
                                                <?php echo $isPinned ? 'Unpin' : 'Pin'; ?>
                                            </button>
                                            <button type="button"
                                                    class="danger"
                                                    onclick="deleteMessage(<?php echo (int)$msg['id']; ?>)">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <p class="sender-name">
                                    <?php if ($isMe): ?>
                                        You
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($msg['sender_name'] ?: 'Unknown'); ?>
                                        <?php if (isset($msg['sender_role']) && $msg['sender_role'] === 'teacher'): ?>
                                            · Teacher
                                        <?php elseif (isset($msg['sender_role']) && $msg['sender_role'] === 'leader'): ?>
                                            · Team Leader
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($isPinned): ?>
                                        <span class="pinned-flag">📌 Pinned</span>
                                    <?php endif; ?>
                                </p>

                                <?php if ($isPollMsg && $pollRow): ?>
                                    <div class="poll-card" data-poll-id="<?php echo $pollID; ?>">
                                        <div class="poll-header">
                                            <div class="poll-icon">📊</div>
                                            <div class="poll-title"><?php echo htmlspecialchars($pollRow['title']); ?></div>
                                        </div>
                                        <?php foreach ($pollOptions as $opt): ?>
                                            <?php
                                            $oid     = (int)$opt['id'];
                                            $votes   = (int)$opt['vote_count'];
                                            $pct     = $pollTotalVotes > 0 ? round($votes / $pollTotalVotes * 100) : 0;
                                            $isVoted = ($myVoteOptionID === $oid);
                                            ?>
                                            <button type="button"
                                                    class="poll-option <?php echo $isVoted ? 'voted' : ''; ?>"
                                                    data-option-id="<?php echo $oid; ?>"
                                                    onclick="votePoll(<?php echo $pollID; ?>, <?php echo $oid; ?>)">
                                                <div class="poll-option-bar" style="width: <?php echo $pct; ?>%;"></div>
                                                <div class="poll-option-text">
                                                    <span class="poll-option-label">
                                                        <?php if ($isVoted): ?>✓ <?php endif; ?>
                                                        <?php echo htmlspecialchars($opt['option_text']); ?>
                                                    </span>
                                                    <span class="poll-option-count">
                                                        <?php echo $votes; ?> (<?php echo $pct; ?>%)
                                                    </span>
                                                </div>
                                            </button>
                                        <?php endforeach; ?>
                                        <div class="poll-meta">
                                            <span class="poll-total">
                                                <?php echo $pollTotalVotes; ?> vote<?php echo $pollTotalVotes !== 1 ? 's' : ''; ?>
                                            </span>
                                            <?php if ($myVoteOptionID > 0): ?>
                                                <span class="voted-tag">You voted · tap another option to change</span>
                                            <?php else: ?>
                                                <span>Tap an option to vote</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <?php elseif (!empty($msg['content'])): ?>
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($msg['attachment_name'])): ?>
                                    <?php
                                    $attExt  = strtolower(pathinfo($msg['attachment_name'], PATHINFO_EXTENSION));
                                    $isImage = in_array($attExt, array('png', 'jpg', 'jpeg', 'gif', 'webp'));
                                    ?>

                                    <?php if ($isImage): ?>
                                        <div class="attachment-image-wrap">
                                            <a href="open_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                               target="_blank"
                                               title="Open full image">
                                                <img src="open_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                                     alt="<?php echo htmlspecialchars($msg['attachment_name']); ?>"
                                                     class="attachment-image"
                                                     loading="lazy">
                                            </a>
                                            <div class="attachment-image-meta">
                                                <a href="download_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                                   class="file-name-small"
                                                   title="Download <?php echo htmlspecialchars($msg['attachment_name']); ?>">
                                                    <?php echo htmlspecialchars($msg['attachment_name']); ?>
                                                </a>
                                                <span class="file-size-small">
                                                    <?php echo htmlspecialchars($msg['attachment_size']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <a href="download_file.php?id=<?php echo (int)$msg['attachment_id']; ?>"
                                           class="attachment-chip"
                                           title="Download <?php echo htmlspecialchars($msg['attachment_name']); ?>">
                                            <div class="attachment-icon">
                                                <?php echo htmlspecialchars($msg['attachment_type'] ?: 'FILE'); ?>
                                            </div>
                                            <div class="attachment-info">
                                                <h4><?php echo htmlspecialchars($msg['attachment_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($msg['attachment_size']); ?></p>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <span class="message-time">
                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <form action="student_chat.php?group_id=<?php echo $activeGroupID; ?><?php echo ($dmStudentID > 0 ? '&dm=' . $dmStudentID : ''); ?>"
                      method="post"
                      enctype="multipart/form-data"
                      class="message-input-area"
                      id="chatForm">

                    <input type="hidden" name="sendMessage" value="1">

                    <?php if ($dmStudentID > 0): ?>
                        <input type="hidden" name="recipient_id" value="<?php echo $dmStudentID; ?>">
                    <?php endif; ?>

                    <button type="button" class="attach-button" id="attachBtn" title="More options">
                        +
                    </button>

                    <div class="attach-menu" id="attachMenu">
                        <button type="button" class="attach-menu-item" onclick="openVoteModal()">
                            <span class="menu-emoji">📊</span>
                            <span>Create Vote</span>
                        </button>
                        <button type="button" class="attach-menu-item" onclick="chooseUploadFile()">
                            <span class="menu-emoji">📎</span>
                            <span>Upload File</span>
                        </button>
                    </div>

                    <input type="file"
                           id="chatFile"
                           name="chatFile"
                           style="display:none;"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg,.txt,.zip,.fig,.sql,.ipynb"
                           onchange="onFileSelected()">

                    <div class="selected-file" id="selectedFile">
                        <span class="file-label" id="selectedFileName"></span>
                        <button type="button" class="remove-file" onclick="clearSelectedFile()" title="Remove">✕</button>
                    </div>

                    <textarea
                            name="message"
                            id="messageInput"
                            class="message-input"
                            placeholder="<?php echo $dmStudentID > 0 ? 'Message ' . htmlspecialchars($dmStudent['name']) . '...' : 'Type your message...'; ?> (Enter to send, Shift+Enter for new line)"
                            aria-label="Type your message"></textarea>

                    <button type="button"
                            class="emoji-button"
                            id="emojiButton"
                            title="Insert emoji">
                        😊
                    </button>

                    <button type="submit" class="send-button" id="sendButton" disabled>
                        Send
                    </button>

                    <div class="emoji-picker" id="emojiPicker">
                        <div class="emoji-picker-header">
                            <span>Emojis</span>
                            <button type="button" class="emoji-picker-close" id="emojiClose">✕</button>
                        </div>

                        <div class="emoji-categories" id="emojiCategories">
                            <button type="button" class="emoji-cat-btn active" data-cat="smileys">😀</button>
                            <button type="button" class="emoji-cat-btn" data-cat="gestures">👍</button>
                            <button type="button" class="emoji-cat-btn" data-cat="hearts">❤️</button>
                            <button type="button" class="emoji-cat-btn" data-cat="animals">🐶</button>
                            <button type="button" class="emoji-cat-btn" data-cat="food">🍔</button>
                            <button type="button" class="emoji-cat-btn" data-cat="objects">💡</button>
                            <button type="button" class="emoji-cat-btn" data-cat="symbols">✅</button>
                        </div>

                        <div class="emoji-scroll" id="emojiScroll">
                            <div class="emoji-grid" id="emojiGrid"></div>
                        </div>
                    </div>

                </form>

                <div class="input-hint">
                    <span class="hint-text">
                        Press <strong>Enter</strong> to send · <strong>Shift + Enter</strong> for new line
                    </span>
                    <span class="char-count" id="charCount">0 / 1000</span>
                </div>

            </div>
        </section>

        <!-- ===== 建立投票 Modal ===== -->
        <div class="vote-modal-overlay" id="voteModal">
            <div class="vote-modal" role="dialog" aria-modal="true" aria-labelledby="voteModalTitle">
                <div class="vote-modal-header">
                    <h3 id="voteModalTitle">Create a Vote</h3>
                    <button type="button" class="vote-modal-close" id="voteModalClose" aria-label="Close">✕</button>
                </div>

                <form method="post" action="student_chat.php?group_id=<?php echo $activeGroupID; ?>" id="voteForm">
                    <input type="hidden" name="createPoll" value="1">

                    <div class="vote-modal-body">
                        <label class="vote-label" for="pollTitle">Question / Title</label>
                        <input type="text"
                               id="pollTitle"
                               name="pollTitle"
                               class="vote-input"
                               placeholder="e.g. When should we meet?"
                               maxlength="200"
                               required>

                        <label class="vote-label" style="margin-top:16px;">Options (at least 2)</label>
                        <div id="voteOptions">
                            <div class="vote-option-row">
                                <input type="text" name="pollOption[]" class="vote-input vote-option-input" required placeholder="Option 1">
                                <button type="button" class="vote-remove-option"
                                        onclick="removeVoteOption(this)"
                                        title="Remove option">−</button>
                            </div>
                            <div class="vote-option-row">
                                <input type="text" name="pollOption[]" class="vote-input vote-option-input" required placeholder="Option 2">
                                <button type="button" class="vote-remove-option"
                                        onclick="removeVoteOption(this)"
                                        title="Remove option">−</button>
                            </div>
                        </div>
                        <button type="button" class="vote-add-option" id="voteAddOption">+ Add Option</button>
                    </div>

                    <div class="vote-modal-footer">
                        <button type="submit" class="vote-submit">Create Vote</button>
                    </div>
                </form>
            </div>
        </div>

    <?php endif; ?>

</main>

<script>
    function openDMChat(memberStudentID) {
        var groupId = <?php echo (int)$activeGroupID; ?>;
        window.location.href = 'student_chat.php?group_id=' + groupId + '&dm=' + memberStudentID;
    }

    (function () {
        var area = document.getElementById('messageArea');
        if (area) {
            area.scrollTop = area.scrollHeight;
        }
    })();

    (function () {
        var input        = document.getElementById('messageInput');
        var sendBtn      = document.getElementById('sendButton');
        var form         = document.getElementById('chatForm');
        var charCount    = document.getElementById('charCount');
        var fileInput    = document.getElementById('chatFile');
        var attachBtn    = document.getElementById('attachBtn');
        var selectedFile = document.getElementById('selectedFile');
        var selFileName  = document.getElementById('selectedFileName');

        if (!input || !sendBtn || !form) return;

        var MAX_LEN = 1000;

        function autoResize() {
            input.style.height = '46px';
            var newHeight = Math.min(input.scrollHeight, 110);
            input.style.height = newHeight + 'px';
        }

        function updateState() {
            var len = input.value.length;
            var hasText = (input.value.trim() !== '');
            var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

            sendBtn.disabled = !(hasText || hasFile);

            if (charCount) {
                charCount.textContent = len + ' / ' + MAX_LEN;
                charCount.style.color = (len > MAX_LEN) ? '#d32f2f' : '';
            }

            autoResize();
        }

        input.focus();
        input.addEventListener('input', updateState);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();

                var hasText = (input.value.trim() !== '');
                var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);

                if (hasText || hasFile) {
                    form.submit();
                }
            }
        });

        window.onFileSelected = function () {
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                return;
            }

            var name = fileInput.files[0].name;

            if (selFileName) selFileName.textContent = name;
            if (selectedFile) selectedFile.classList.add('visible');
            if (attachBtn) attachBtn.classList.add('has-file');

            updateState();
            input.focus();
        };

        window.clearSelectedFile = function () {
            if (fileInput) fileInput.value = '';
            if (selectedFile) selectedFile.classList.remove('visible');
            if (attachBtn) attachBtn.classList.remove('has-file');

            updateState();
            input.focus();
        };

        updateState();
        autoResize();
    })();

    (function () {
        var emojiBtn    = document.getElementById('emojiButton');
        var picker      = document.getElementById('emojiPicker');
        var closeBtn    = document.getElementById('emojiClose');
        var grid        = document.getElementById('emojiGrid');
        var scrollArea  = document.getElementById('emojiScroll');
        var catBtns     = document.querySelectorAll('.emoji-cat-btn');
        var input       = document.getElementById('messageInput');

        if (!emojiBtn || !picker || !grid) return;

        var EMOJIS = {
            smileys: [
                '😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇',
                '🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚',
                '😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥳',
                '😏','😒','😞','😔','😟','😕','🙁','😣','😖','😫',
                '😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳',
                '🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭',
                '🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧',
                '😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢',
                '🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹',
                '👺','🤡','💩','👻','💀','👽','🤖','😺','😸','😹'
            ],
            gestures: [
                '👍','👎','👌','✌️','🤞','🤟','🤘','🤙','👈','👉',
                '👆','👇','☝️','✋','🤚','🖐️','🖖','👋','🤝','🙏',
                '✍️','💅','🤳','💪','🦾','🦵','🦶','👂','🦻','👃',
                '👀','👁️','👅','👄','💋','🫶','🫰','🫱','🫲','🫳',
                '🤌','🤏','🫴','🫵','👏','🙌','👐','🤲','🤜','🤛',
                '✊','👊','🖕','🖐️','💪','🦿','🦴','👣','🧠','🫀'
            ],
            hearts: [
                '❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔',
                '❣️','💕','💞','💓','💗','💖','💘','💝','💟','♥️',
                '💌','💋','👩‍❤️‍👨','👨‍❤️‍👨','👩‍❤️‍👩','💑','💏','👪','👨‍👩‍👧','👨‍👩‍👦'
            ],
            animals: [
                '🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯',
                '🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐒','🐔',
                '🐧','🐦','🐤','🐣','🐥','🦆','🦅','🦉','🦇','🐺',
                '🐗','🐴','🦄','🐝','🐛','🦋','🐌','🐞','🐜','🦟',
                '🦗','🕷️','🕸️','🦂','🐢','🐍','🦎','🦖','🦕','🐙',
                '🦑','🦐','🦞','🦀','🐡','🐠','🐟','🐬','🐳','🐋'
            ],
            food: [
                '🍏','🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🫐',
                '🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑',
                '🥦','🥬','🥒','🌶️','🫑','🌽','🥕','🫒','🧄','🧅',
                '🥔','🍠','🥐','🥯','🍞','🥖','🥨','🧀','🥚','🍳',
                '🧈','🥞','🧇','🥓','🥩','🍗','🍖','🦴','🌭','🍔',
                '🍟','🍕','🫓','🥪','🥙','🧆','🌮','🌯','🫔','🥗'
            ],
            objects: [
                '💡','🔦','🕯️','🪔','🧯','🛢️','💸','💵','💴','💶',
                '💷','🪙','💰','💳','💎','⚖️','🪜','🧰','🪛','🔧',
                '🔨','⚒️','🛠️','⛏️','🪚','🔩','⚙️','🪤','🧱','⛓️',
                '🧲','🔫','💣','🧨','🪓','🔪','🗡️','⚔️','🛡️','🚬',
                '⚰️','🪦','⚱️','🏺','🔮','📿','🧿','💈','⚗️','🔭'
            ],
            symbols: [
                '✅','❌','⭕','🚫','💯','🔴','🟠','🟡','🟢','🔵',
                '🟣','⚫','⚪','🟤','🔶','🔷','🔸','🔹','🔺','🔻',
                '💠','🔘','🔳','🔲','▪️','▫️','◾','◽','◼️','◻️',
                '🟥','🟧','🟨','🟩','🟦','🟪','⬛','⬜','🟫','🔈',
                '🔇','🔉','🔊','🔔','🔕','📣','📢','💬','💭','🗯️'
            ]
        };

        var currentCat = 'smileys';

        function renderEmojis(cat) {
            grid.innerHTML = '';
            var list = EMOJIS[cat] || [];

            list.forEach(function (emoji) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'emoji-item';
                btn.textContent = emoji;
                btn.title = emoji;

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    insertEmoji(emoji);
                });

                grid.appendChild(btn);
            });

            if (scrollArea) scrollArea.scrollTop = 0;
        }

        function insertEmoji(emoji) {
            if (!input) return;

            var start = input.selectionStart || 0;
            var end   = input.selectionEnd || 0;
            var value = input.value;

            input.value = value.slice(0, start) + emoji + value.slice(end);

            var newPos = start + emoji.length;
            input.setSelectionRange(newPos, newPos);
            input.focus();

            input.dispatchEvent(new Event('input'));
        }

        function openPicker() {
            picker.classList.add('open');
            emojiBtn.classList.add('active');
            renderEmojis(currentCat);
        }

        function closePicker() {
            picker.classList.remove('open');
            emojiBtn.classList.remove('active');
        }

        function togglePicker() {
            if (picker.classList.contains('open')) {
                closePicker();
            } else {
                openPicker();
            }
        }

        emojiBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            togglePicker();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closePicker();
            });
        }

        catBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                catBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                currentCat = btn.getAttribute('data-cat');
                renderEmojis(currentCat);
            });
        });

        document.addEventListener('click', function (e) {
            if (!picker.contains(e.target) && e.target !== emojiBtn) {
                closePicker();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closePicker();
            }
        });

        closePicker();
    })();

    (function () {
        var attachBtn  = document.getElementById('attachBtn');
        var attachMenu = document.getElementById('attachMenu');
        if (!attachBtn || !attachMenu) return;

        attachBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            attachMenu.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (!attachMenu.contains(e.target) && e.target !== attachBtn) {
                attachMenu.classList.remove('open');
            }
        });

        window.chooseUploadFile = function () {
            attachMenu.classList.remove('open');
            var fileInput = document.getElementById('chatFile');
            if (fileInput) fileInput.click();
        };

        window.openVoteModal = function () {
            attachMenu.classList.remove('open');
            var modal = document.getElementById('voteModal');
            if (modal) {
                modal.classList.add('active');
                setTimeout(function () {
                    var t = document.getElementById('pollTitle');
                    if (t) t.focus();
                }, 100);
            }
        };
    })();

    (function () {
        var modal    = document.getElementById('voteModal');
        var closeBtn = document.getElementById('voteModalClose');
        var addOptBtn= document.getElementById('voteAddOption');
        var options  = document.getElementById('voteOptions');
        if (!modal) return;

        function updateRemoveVisibility() {
            if (!options) return;
            var rows = options.querySelectorAll('.vote-option-row');
            var deletable = rows.length > 2;
            rows.forEach(function (row) {
                row.classList.toggle('deletable', deletable);
            });
        }

        window.removeVoteOption = function (btn) {
            if (!options) return;
            var rows = options.querySelectorAll('.vote-option-row');
            if (rows.length <= 2) return;

            var row = btn.parentNode;
            if (row && row.parentNode === options) {
                options.removeChild(row);
            }
            updateRemoveVisibility();
        };

        function closeModal() {
            modal.classList.remove('active');
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        if (addOptBtn && options) {
            addOptBtn.addEventListener('click', function () {
                var count = options.querySelectorAll('.vote-option-row').length;
                if (count >= 10) return;

                var row = document.createElement('div');
                row.className = 'vote-option-row';

                var inp = document.createElement('input');
                inp.type = 'text';
                inp.name = 'pollOption[]';
                inp.className = 'vote-input vote-option-input';
                inp.placeholder = 'Option ' + (count + 1);

                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'vote-remove-option';
                rm.title = 'Remove option';
                rm.textContent = '−';
                rm.onclick = function () { window.removeVoteOption(rm); };

                row.appendChild(inp);
                row.appendChild(rm);
                options.appendChild(row);

                inp.focus();
                updateRemoveVisibility();
            });

            updateRemoveVisibility();
        }
    })();

    function votePoll(pollId, optionId) {
        var formData = new FormData();
        formData.append('votePoll', '1');
        formData.append('poll_id', pollId);
        formData.append('option_id', optionId);

        fetch('student_chat.php?group_id=<?php echo (int)$activeGroupID; ?>', {
            method: 'POST',
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) return;

                var card = document.querySelector('.poll-card[data-poll-id="' + pollId + '"]');
                if (!card) return;

                var optionBtns = card.querySelectorAll('.poll-option');
                optionBtns.forEach(function (btn) {
                    var oid = parseInt(btn.getAttribute('data-option-id'), 10);
                    var count = data.counts[oid] || 0;
                    var pct = data.total > 0 ? Math.round(count / data.total * 100) : 0;

                    var bar   = btn.querySelector('.poll-option-bar');
                    var label = btn.querySelector('.poll-option-label');
                    var cnt   = btn.querySelector('.poll-option-count');

                    if (bar) bar.style.width = pct + '%';
                    if (cnt) cnt.textContent = count + ' (' + pct + '%)';

                    if (label) {
                        var raw = label.textContent.replace(/^✓\s*/, '').trim();
                        if (oid === data.voted) {
                            label.textContent = '✓ ' + raw;
                        } else {
                            label.textContent = raw;
                        }
                    }

                    btn.classList.toggle('voted', oid === data.voted);
                });

                var totalEl = card.querySelector('.poll-total');
                if (totalEl) {
                    totalEl.textContent = data.total + ' vote' + (data.total !== 1 ? 's' : '');
                }
            })
            .catch(function () { /* ignore */ });
    }

    function toggleMsgMenu(e, msgId) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('msgMenu-' + msgId);
        if (!menu) return;

        var allOpen = document.querySelectorAll('.msg-menu.open');
        for (var i = 0; i < allOpen.length; i++) {
            if (allOpen[i] !== menu) allOpen[i].classList.remove('open');
        }
        menu.classList.toggle('open');
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        var insideMenu = false;
        var node = t;
        while (node) {
            if (node.classList && (node.classList.contains('msg-menu') || node.classList.contains('msg-menu-btn'))) {
                insideMenu = true;
                break;
            }
            node = node.parentNode;
        }
        if (!insideMenu) {
            var allOpen = document.querySelectorAll('.msg-menu.open');
            for (var i = 0; i < allOpen.length; i++) {
                allOpen[i].classList.remove('open');
            }
        }
    });

    function pinMessage(msgId) {
        var url = 'student_chat.php?group_id=<?php echo (int)$activeGroupID; ?><?php echo ($dmStudentID > 0 ? '&dm=' . $dmStudentID : ''); ?>';

        var formData = new FormData();
        formData.append('pinMessage', '1');
        formData.append('message_id', msgId);

        fetch(url, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok) {
                    window.location.reload();
                } else {
                    alert('Could not update the pin status.');
                }
            })
            .catch(function () { alert('Request failed.'); });
    }

    function unpinMessage(msgId) {
        var url = 'student_chat.php?group_id=<?php echo (int)$activeGroupID; ?><?php echo ($dmStudentID > 0 ? '&dm=' . $dmStudentID : ''); ?>';

        var formData = new FormData();
        formData.append('pinMessage', '1');
        formData.append('message_id', msgId);

        fetch(url, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok) {
                    window.location.reload();
                } else {
                    alert('Could not unpin this message.');
                }
            })
            .catch(function () { alert('Request failed.'); });
    }

    function deleteMessage(msgId) {
        if (!confirm('Delete this message?')) return;

        var url = 'student_chat.php?group_id=<?php echo (int)$activeGroupID; ?><?php echo ($dmStudentID > 0 ? '&dm=' . $dmStudentID : ''); ?>';

        var formData = new FormData();
        formData.append('deleteMessage', '1');
        formData.append('message_id', msgId);

        fetch(url, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok) {
                    window.location.reload();
                } else {
                    alert('Could not delete this message.');
                }
            })
            .catch(function () { alert('Request failed.'); });
    }

    function togglePinnedBar() {
        var bar = document.getElementById('pinnedBar');
        var btn = document.getElementById('pinnedToggleBtn');
        if (!bar) return;
        bar.classList.toggle('collapsed');
        if (btn) btn.textContent = bar.classList.contains('collapsed') ? 'Show' : 'Hide';
    }

    function scrollToMessage(msgId) {
        var row = document.querySelector('.message-row[data-msg-id="' + msgId + '"]');
        if (!row) return;

        row.scrollIntoView({ behavior: 'smooth', block: 'center' });

        row.classList.remove('highlight-flash');
        void row.offsetWidth;
        row.classList.add('highlight-flash');

        setTimeout(function () {
            row.classList.remove('highlight-flash');
        }, 1700);
    }
</script>

</body>
</html>