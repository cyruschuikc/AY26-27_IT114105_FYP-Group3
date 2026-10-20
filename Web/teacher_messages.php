<?php
$active = 'messages';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/teacher_chat_helpers.php';
/** @var mysqli $conn */

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

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
// Conversation list
// =========================================================
$groups = teacherChatGroups($conn, $teacherId, $userID);

$activeGroupId = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
$activeGroup   = null;

foreach ($groups as $g) {
    if ((int) $g['id'] === $activeGroupId) {
        $activeGroup = $g;
        break;
    }
}

if (!$activeGroup && count($groups) > 0) {
    $activeGroup = $groups[0];
    $activeGroupId = (int) $activeGroup['id'];
}

$messages = array();
$lastMessageId = 0;
$lastDay = '';
$pinnedMessages = array();

if ($activeGroup) {
    $guard = teacherChatAccess($conn, $activeGroupId, $teacherId);
    if (!$guard) {
        $activeGroup = null;
        $activeGroupId = 0;
    } else {
        $messages      = teacherChatMessages($conn, $activeGroupId, 0, 300);
        $lastMessageId = teacherChatMaxId($conn, $activeGroupId);
        $pinnedMessages = teacherChatPinned($conn, $activeGroupId, 3);
        teacherChatMarkRead($conn, $userID, $activeGroupId, $lastMessageId);

        // Opening a conversation clears its unread badge.
        foreach ($groups as &$g) {
            if ((int) $g['id'] === $activeGroupId) {
                $g['unread'] = 0;
                break;
            }
        }
        unset($g);
    }
}

$avatarPalette = array('#dbeafe', '#e9d5ff', '#fce7f3', '#d1fae5', '#fef3c7');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | TeamBox</title>
    <link rel="stylesheet" href="teacher.css?v=20261020d">
    <style>
        .chat-page { padding-top: 44px; }
        .chat-shell {
            display: flex;
            height: calc(100vh - 120px);
            min-height: 560px;
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(22, 24, 29, 0.05);
            overflow: hidden;
        }

        /* ---------- conversation list ---------- */
        .chat-sidebar {
            width: 300px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            background: #fbfbfc;
            border-right: 1px solid #eef0f2;
        }
        .chat-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 18px 12px;
            border-bottom: 1px solid #f0f2f4;
        }
        .chat-sidebar-head h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .chat-sidebar-head span {
            color: #8b8f98;
            font-size: 11px;
        }
        .conv-list {
            flex: 1;
            overflow-y: auto;
            padding: 6px;
        }
        .conv-item {
            position: relative;
            display: flex;
            gap: 10px;
            padding: 10px;
            margin-bottom: 2px;
            border-radius: 10px;
            color: inherit;
            text-decoration: none;
            transition: background 0.12s;
        }
        .conv-item:hover { background: #f1f4f8; }
        .conv-item.active { background: #e9f1fb; }
        .conv-avatar {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #175196;
            font-size: 11px;
            font-weight: 700;
        }
        .conv-main { flex: 1; min-width: 0; }
        .conv-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
        }
        .conv-name {
            overflow: hidden;
            color: #2c2f36;
            font-size: 13px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .conv-time {
            flex-shrink: 0;
            color: #9aa0a8;
            font-size: 10px;
        }
        .conv-preview {
            margin-top: 2px;
            overflow: hidden;
            color: #6b6f78;
            font-size: 11.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .conv-meta {
            margin-top: 2px;
            overflow: hidden;
            color: #9aa0a8;
            font-size: 10.5px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .conv-badge {
            position: absolute;
            top: 9px;
            right: 9px;
            min-width: 17px;
            height: 17px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #e5484d;
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
        }

        /* ---------- thread ---------- */
        .chat-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .chat-head {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 13px 20px;
            border-bottom: 1px solid #eef0f2;
            background: #fff;
        }
        .chat-head-avatar {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8eef7;
            color: #175196;
            font-size: 11px;
            font-weight: 700;
        }
        .chat-head h2 {
            margin: 0;
            color: #2c2f36;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0;
            text-transform: none;
        }
        .chat-head p {
            margin-top: 2px;
            color: #8b8f98;
            font-size: 11px;
        }
        .chat-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 20px 22px;
            background: #fafbfc;
        }
        .day-sep {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 8px 0 18px;
            color: #9aa0a8;
            font-size: 10.5px;
        }
        .day-sep::before,
        .day-sep::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #eceef1;
        }
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 12px;
        }
        .msg-row.me { justify-content: flex-end; }
        .msg-row.cont { margin-top: -6px; }
        .msg-avatar {
            width: 29px;
            height: 29px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #175196;
            font-size: 9px;
            font-weight: 700;
        }
        .msg-avatar.ghost { visibility: hidden; }
        .msg-body { max-width: 68%; min-width: 0; }
        .msg-row.me .msg-body { display: flex; flex-direction: column; align-items: flex-end; }
        .msg-sender {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0 2px 3px;
            color: #6b6f78;
            font-size: 10.5px;
            font-weight: 600;
        }
        .msg-role {
            padding: 1px 5px;
            border-radius: 4px;
            background: #eef2f7;
            color: #8b8f98;
            font-size: 8.5px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .msg-bubble {
            padding: 9px 13px;
            border-radius: 12px;
            background: #eef1f5;
            color: #2c2f36;
            font-size: 12.5px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .msg-row.me .msg-bubble {
            background: #175196;
            color: #fff;
        }
        .msg-time {
            margin: 3px 3px 0;
            color: #9aa0a8;
            font-size: 9.5px;
        }
        .msg-attach {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            padding: 5px 8px;
            border-radius: 6px;
            background: rgba(23, 81, 150, 0.09);
            color: #175196;
            font-size: 11px;
            text-decoration: none;
        }
        .msg-row.me .msg-attach {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        /* ---------- pinned announcement ---------- */
        .chat-pinned {
            max-height: 190px;
            overflow-y: auto;
            padding: 10px 18px 12px;
            border-bottom: 1px solid #f0e3c0;
            background: #fffaf0;
        }
        .pinned-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            color: #9a6a13;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .pinned-item {
            padding: 7px 0;
            border-top: 1px dashed #f0e3c0;
        }
        .pinned-item:first-of-type { border-top: none; }
        .pinned-content {
            color: #4b5058;
            font-size: 12px;
            line-height: 1.55;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .pinned-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 5px;
            color: #9a6a13;
            font-size: 10px;
        }
        .pinned-unpin {
            padding: 2px 8px;
            border: 1px solid #ead7aa;
            border-radius: 5px;
            background: #fff;
            color: #8a5a10;
            font-size: 9.5px;
            font-weight: 600;
            cursor: pointer;
        }
        .pinned-unpin:hover { background: #fdf3e2; }

        /* ---------- composer ---------- */
        .chat-composer {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 7px;
            padding: 12px 16px;
            border-top: 1px solid #eef0f2;
            background: #fff;
        }
        .composer-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
        .pin-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #8b8f98;
            font-size: 10.5px;
            cursor: pointer;
        }
        .pin-toggle input { accent-color: #175196; }
        .chat-composer textarea {
            flex: 1;
            min-height: 42px;
            max-height: 110px;
            padding: 10px 12px;
            border: 1px solid #dfe2e6;
            border-radius: 10px;
            resize: none;
            font-size: 12.5px;
            line-height: 1.5;
            color: #2c2f36;
        }
        .chat-composer textarea:focus {
            outline: none;
            border-color: #8db8ea;
            box-shadow: 0 0 0 3px rgba(141, 184, 234, 0.18);
        }
        .chat-send {
            height: 42px;
            padding: 0 18px;
            border: none;
            border-radius: 9px;
            background: #175196;
            color: #fff;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
        }
        .chat-send:disabled { opacity: 0.45; cursor: not-allowed; }
        .chat-send:hover:enabled { background: #123f76; }

        .chat-empty-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #8b8f98;
            font-size: 13px;
            text-align: center;
        }
        .chat-empty-note {
            padding: 26px 20px;
            color: #8b8f98;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .chat-page { padding-top: 44px; }
            .chat-shell {
                flex-direction: column;
                height: auto;
                min-height: 0;
            }
            .chat-sidebar {
                width: 100%;
                max-height: 220px;
                border-right: none;
                border-bottom: 1px solid #eef0f2;
            }
            .chat-main { height: 62vh; }
            .chat-scroll { padding: 16px; }
            .msg-body { max-width: 82%; }
        }
    </style>
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content chat-page">

        <?php if (count($groups) === 0): ?>
            <div class="chat-shell">
                <div class="chat-empty-main">
                    No teams under your supervision yet.<br>
                    Create or assign a project first, then team conversations will appear here.
                </div>
            </div>
        <?php else: ?>
            <div class="chat-shell">

                <!-- Conversation list -->
                <aside class="chat-sidebar">
                    <div class="chat-sidebar-head">
                        <h1>Messages</h1>
                        <span><?= count($groups) ?> team<?= count($groups) === 1 ? '' : 's' ?></span>
                    </div>

                    <div class="conv-list">
                        <?php foreach ($groups as $g): ?>
                            <?php
                            $isActive = ((int) $g['id'] === $activeGroupId);
                            $unread   = (int) $g['unread'];
                            $preview  = trim((string) $g['last_content']);
                            if ($preview === '') {
                                $preview = $g['last_at'] ? 'Attachment' : 'No messages yet';
                            }
                            if (!empty($g['last_sender']) && $g['last_at']) {
                                $preview = $g['last_sender'] . ': ' . $preview;
                            }
                            ?>
                            <a class="conv-item <?= $isActive ? 'active' : '' ?>"
                               href="teacher_messages.php?group_id=<?= (int) $g['id'] ?>">
                                <div class="conv-avatar" style="background: <?= teacherChatAvatarBg($g['name'], $avatarPalette) ?>;">
                                    <?= htmlspecialchars(teacherChatInitials($g['name'])) ?>
                                </div>
                                <div class="conv-main">
                                    <div class="conv-top">
                                        <span class="conv-name"><?= htmlspecialchars($g['name']) ?></span>
                                        <span class="conv-time">
                                            <?= $g['last_at'] ? htmlspecialchars(timeAgo($g['last_at'])) : '' ?>
                                        </span>
                                    </div>
                                    <div class="conv-preview"><?= htmlspecialchars($preview) ?></div>
                                    <div class="conv-meta">
                                        <?= htmlspecialchars($g['project_name']) ?>
                                        · <?= (int) $g['member_count'] ?> member<?= (int) $g['member_count'] === 1 ? '' : 's' ?>
                                    </div>
                                </div>
                                <?php if ($unread > 0): ?>
                                    <span class="conv-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <!-- Thread -->
                <section class="chat-main">
                    <?php if (!$activeGroup): ?>
                        <div class="chat-empty-main">Select a team to start messaging.</div>
                    <?php else: ?>
                        <header class="chat-head">
                            <div class="chat-head-avatar">
                                <?= htmlspecialchars(teacherChatInitials($activeGroup['name'])) ?>
                            </div>
                            <div>
                                <h2><?= htmlspecialchars($activeGroup['name']) ?></h2>
                                <p>
                                    <?= htmlspecialchars($activeGroup['project_name']) ?>
                                    · <?= (int) $activeGroup['member_count'] ?> members
                                    · <?= htmlspecialchars($activeGroup['status']) ?>
                                </p>
                            </div>
                        </header>

                        <?php if (count($pinnedMessages) > 0): ?>
                            <div class="chat-pinned">
                                <div class="pinned-head">
                                    <span>📌 Pinned announcement</span>
                                    <span><?= count($pinnedMessages) ?></span>
                                </div>
                                <?php foreach ($pinnedMessages as $pin): ?>
                                    <div class="pinned-item">
                                        <div class="pinned-content"><?= nl2br(htmlspecialchars($pin['content'])) ?></div>
                                        <div class="pinned-meta">
                                            <span>
                                                <?= htmlspecialchars($pin['sender_name'] ?: 'Teacher') ?>
                                                · <?= htmlspecialchars(timeAgo($pin['pinned_at'] ?: $pin['created_at'])) ?>
                                            </span>
                                            <form method="post" action="teacher_chat_thread.php">
                                                <input type="hidden" name="action" value="unpin">
                                                <input type="hidden" name="group_id" value="<?= (int) $activeGroupId ?>">
                                                <input type="hidden" name="message_id" value="<?= (int) $pin['id'] ?>">
                                                <button type="submit" class="pinned-unpin">Unpin</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="chat-scroll" id="messageArea">
                            <?php if (count($messages) === 0): ?>
                                <div class="chat-empty-note">
                                    No messages yet. Say hello to <?= htmlspecialchars($activeGroup['name']) ?>.
                                </div>
                            <?php endif; ?>

                            <?php
                            $prevDay    = '';
                            $prevSender = '';

                            foreach ($messages as $m):
                                $msgTs      = strtotime($m['created_at']);
                                $dayKey     = date('Y-m-d', $msgTs);
                                $isMe       = ((int) $m['sender_user_id'] === $userID);
                                $senderKey  = $m['sender_user_id']
                                    ? 'u' . (int) $m['sender_user_id']
                                    : 's' . (int) $m['sender_id'];
                                $isCont     = (!$isMe && $senderKey === $prevSender && $dayKey === $prevDay);
                                $senderName = $m['sender_name'] ?: 'Unknown';
                                $roleLabel  = 'Member';
                                if ($m['sender_role'] === 'teacher')   { $roleLabel = 'Teacher'; }
                                elseif ($m['sender_role'] === 'leader') { $roleLabel = 'Team Leader'; }
                            ?>
                                <?php if ($dayKey !== $prevDay): ?>
                                    <div class="day-sep"><span><?= date('d M Y', $msgTs) ?></span></div>
                                <?php endif; ?>

                                <div class="msg-row <?= $isMe ? 'me' : 'them' ?> <?= $isCont ? 'cont' : '' ?>">
                                    <?php if (!$isMe): ?>
                                        <div class="msg-avatar <?= $isCont ? 'ghost' : '' ?>"
                                             style="background: <?= teacherChatAvatarBg($senderName, $avatarPalette) ?>;">
                                            <?= htmlspecialchars(teacherChatInitials($senderName)) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="msg-body">
                                        <?php if (!$isMe && !$isCont): ?>
                                            <div class="msg-sender">
                                                <?= htmlspecialchars($senderName) ?>
                                                <span class="msg-role"><?= htmlspecialchars($roleLabel) ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (trim((string) $m['content']) !== ''): ?>
                                            <div class="msg-bubble"><?= nl2br(htmlspecialchars($m['content'])) ?></div>
                                        <?php endif; ?>

                                        <?php if (!empty($m['attachment_id'])): ?>
                                            <a class="msg-attach"
                                               href="download_file.php?id=<?= (int) $m['attachment_id'] ?>">
                                                📎 <?= htmlspecialchars($m['attachment_name'] ?: 'Attachment') ?>
                                            </a>
                                        <?php endif; ?>

                                        <div class="msg-time"><?= date('H:i', $msgTs) ?></div>
                                    </div>
                                </div>

                                <?php
                                $prevDay    = $dayKey;
                                $prevSender = $senderKey;
                            endforeach;
                            ?>
                        </div>

                        <form class="chat-composer" method="post" action="teacher_chat_thread.php">
                            <input type="hidden" name="group_id" value="<?= (int) $activeGroupId ?>">
                            <label class="pin-toggle">
                                <input type="checkbox" name="pin" value="1">
                                Pin as group announcement
                            </label>
                            <div class="composer-row">
                                <textarea id="messageInput" name="content" maxlength="1000" rows="1"
                                          placeholder="Message <?= htmlspecialchars($activeGroup['name']) ?>..."></textarea>
                                <button type="submit" class="chat-send" id="sendButton" disabled>Send</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>

            </div>
        <?php endif; ?>

    </div>
</main>

<?php if ($activeGroup): ?>
<script>
(function () {
    var area  = document.getElementById('messageArea');
    var input = document.getElementById('messageInput');
    var send  = document.getElementById('sendButton');
    var groupId = <?= (int) $activeGroupId ?>;
    var lastId  = <?= (int) $lastMessageId ?>;
    var lastDay = '<?= $messages ? date('Y-m-d', strtotime(end($messages)['created_at'])) : '' ?>';

    function nearBottom() {
        return area.scrollHeight - area.scrollTop - area.clientHeight < 120;
    }

    function scrollToBottom() {
        area.scrollTop = area.scrollHeight;
    }

    function addDay(dayLabel) {
        var sep = document.createElement('div');
        sep.className = 'day-sep';
        var span = document.createElement('span');
        span.textContent = dayLabel;
        sep.appendChild(span);
        area.appendChild(sep);
    }

    function addMessage(m) {
        if (m.day !== lastDay) {
            addDay(m.day_label);
            lastDay = m.day;
        }

        var row = document.createElement('div');
        row.className = 'msg-row ' + (m.is_me ? 'me' : 'them');

        if (!m.is_me) {
            var av = document.createElement('div');
            av.className = 'msg-avatar';
            av.style.background = m.avatar_bg;
            av.textContent = m.initials;
            row.appendChild(av);
        }

        var body = document.createElement('div');
        body.className = 'msg-body';

        if (!m.is_me) {
            var who = document.createElement('div');
            who.className = 'msg-sender';
            who.textContent = m.sender_name;
            var role = document.createElement('span');
            role.className = 'msg-role';
            role.textContent = m.role_label;
            who.appendChild(role);
            body.appendChild(who);
        }

        if (m.content !== '') {
            var bubble = document.createElement('div');
            bubble.className = 'msg-bubble';
            bubble.textContent = m.content;
            body.appendChild(bubble);
        }

        if (m.attachment_id) {
            var link = document.createElement('a');
            link.className = 'msg-attach';
            link.href = 'download_file.php?id=' + m.attachment_id;
            link.textContent = '📎 ' + (m.attachment_name || 'Attachment');
            body.appendChild(link);
        }

        var time = document.createElement('div');
        time.className = 'msg-time';
        time.textContent = m.time_label;
        body.appendChild(time);

        row.appendChild(body);
        area.appendChild(row);
    }

    scrollToBottom();

    input.addEventListener('input', function () {
        send.disabled = input.value.trim() === '';
        input.style.height = '42px';
        input.style.height = Math.min(input.scrollHeight, 110) + 'px';
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (input.value.trim() !== '') {
                input.form.submit();
            }
        }
    });

    setInterval(function () {
        var visible = document.visibilityState === 'visible' ? '1' : '0';
        fetch('teacher_chat_feed.php?group_id=' + groupId + '&after_id=' + lastId + '&visible=' + visible, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || !data.messages || data.messages.length === 0) return;
            var wasNearBottom = nearBottom();
            data.messages.forEach(function (m) {
                addMessage(m);
                if (m.id > lastId) lastId = m.id;
            });
            if (wasNearBottom) scrollToBottom();
        })
        .catch(function () {});
    }, 5000);
})();
</script>
<?php endif; ?>

</body>
</html>
