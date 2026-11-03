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
    <link rel="stylesheet" href="teacher.css?v=<?= filemtime('teacher.css') ?>">
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= filemtime('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="assets/css/soft-glass.css?v=<?= filemtime('assets/css/soft-glass.css') ?>">
    <link rel="stylesheet" href="assets/css/teacher_messages.css?v=<?= filemtime('assets/css/teacher_messages.css') ?>">
    <link rel="stylesheet" href="teacher_ui.css?v=<?= filemtime('teacher_ui.css') ?>">
</head>
<body class="sg-teacher">

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

<script src="teacher_ui.js?v=<?= filemtime('teacher_ui.js') ?>"></script>
</body>
</html>
