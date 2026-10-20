<?php
// teacher_notifications.php
// Notification bell shown on every teacher page.
// Requires: $conn (mysqli), $navTeacherId, $teacherName

if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/db.php';
}
require_once __DIR__ . '/helpers.php';

/** @var mysqli $conn */

$notifTeacherId = isset($navTeacherId) ? (int) $navTeacherId : 0;
if ($notifTeacherId <= 0) {
    return;
}

$notifPendingFiles    = isset($navPendingFiles) ? (int) $navPendingFiles : 0;
$notifPendingRequests = isset($navPendingRequests) ? (int) $navPendingRequests : 0;
$notifOverdueTasks    = isset($navOverdueTasks) ? (int) $navOverdueTasks : 0;

$notifTeacherName = isset($teacherName) ? trim($teacherName) : '';
$notifFirstName   = '';
if ($notifTeacherName !== '') {
    $notifNameParts = preg_split('/\s+/', $notifTeacherName);
    $notifFirstName = $notifNameParts[0];
}

$notifRequestItems = array();
$notifOverdueItems = array();
$notifFileItems    = array();
$notifMentionItems = array();
$notifMentionCount = 0;


// =========================================================
// 1. 待审批的小组申请（有才显示）
// =========================================================
if ($notifPendingRequests > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT r.id, r.group_name, r.submitted_at,
               p.name AS project_name,
               u.username AS submitter_name
        FROM `group_requests` r
        INNER JOIN `projects` p ON p.id = r.project_id
        LEFT JOIN `students` s ON s.id = r.submitted_by
        LEFT JOIN `users` u    ON u.id = s.user_id
        WHERE r.status = 'Pending' AND p.supervisor_id = ?
        ORDER BY r.submitted_at DESC
        LIMIT 3
    ");
    mysqli_stmt_bind_param($stmt, "i", $notifTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $notifRequestItems[] = $row;
    }
    mysqli_stmt_close($stmt);
}


// =========================================================
// 2. 逾期任务
// =========================================================
if ($notifOverdueTasks > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT t.id, t.title, t.due_date,
               DATEDIFF(CURDATE(), t.due_date) AS days_late,
               g.id AS group_id, g.name AS group_name
        FROM `tasks` t
        INNER JOIN `projects` p ON p.id = t.project_id
        INNER JOIN `groups` g   ON g.id = t.group_id
        WHERE p.supervisor_id = ?
          AND t.status <> 'Completed'
          AND t.due_date IS NOT NULL
          AND t.due_date < CURDATE()
        ORDER BY t.due_date ASC
        LIMIT 3
    ");
    mysqli_stmt_bind_param($stmt, "i", $notifTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $notifOverdueItems[] = $row;
    }
    mysqli_stmt_close($stmt);
}


// =========================================================
// 3. 群组里 @ 老师（为后续师生群组预留，当前无数据时自动隐藏）
// =========================================================
if ($notifTeacherName !== '') {
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `messages` m
        INNER JOIN `groups` g   ON g.id = m.group_id
        INNER JOIN `projects` p ON p.id = g.project_id
        LEFT JOIN `message_reads` mr
               ON mr.group_id = m.group_id AND mr.user_id = ?
        WHERE p.supervisor_id = ?
          AND m.id > COALESCE(mr.last_read_message_id, 0)
          AND (m.content LIKE CONCAT('%@', ?, '%')
               OR m.content LIKE CONCAT('%@', ?, '%'))
    ");
    $notifUserId = (int) $_SESSION['userID'];
    mysqli_stmt_bind_param($stmt, "iiss", $notifUserId, $notifTeacherId, $notifTeacherName, $notifFirstName);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $notifMentionCount = (int) $rc['c'];
    mysqli_stmt_close($stmt);

    if ($notifMentionCount > 0) {
        $stmt = mysqli_prepare($conn, "
            SELECT m.id, m.content, m.created_at,
                   g.id AS group_id, g.name AS group_name,
                   u.username AS sender_name
            FROM `messages` m
            INNER JOIN `groups` g   ON g.id = m.group_id
            INNER JOIN `projects` p ON p.id = g.project_id
            LEFT JOIN `students` s  ON s.id = m.sender_id
            LEFT JOIN `users` u     ON u.id = s.user_id
            LEFT JOIN `message_reads` mr
                   ON mr.group_id = m.group_id AND mr.user_id = ?
            WHERE p.supervisor_id = ?
              AND m.id > COALESCE(mr.last_read_message_id, 0)
              AND (m.content LIKE CONCAT('%@', ?, '%')
                   OR m.content LIKE CONCAT('%@', ?, '%'))
            ORDER BY m.created_at DESC
            LIMIT 3
        ");
        mysqli_stmt_bind_param($stmt, "iiss", $notifUserId, $notifTeacherId, $notifTeacherName, $notifFirstName);
        mysqli_stmt_execute($stmt);
        $rs = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($rs)) {
            $notifMentionItems[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}


// =========================================================
// 4. 待审核文件（带数量）
// =========================================================
if ($notifPendingFiles > 0) {
    $stmt = mysqli_prepare($conn, "
        SELECT f.id, f.file_name, f.display_name, f.uploaded_at,
               g.name AS group_name
        FROM `files` f
        INNER JOIN `projects` p ON p.id = f.project_id
        LEFT JOIN `groups` g    ON g.id = f.group_id
        WHERE f.status = 'Unviewed' AND p.supervisor_id = ?
        ORDER BY f.uploaded_at DESC
        LIMIT 3
    ");
    mysqli_stmt_bind_param($stmt, "i", $notifTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($rs)) {
        $notifFileItems[] = $row;
    }
    mysqli_stmt_close($stmt);
}


$notifTotal = $notifPendingRequests + $notifOverdueTasks + $notifMentionCount + $notifPendingFiles;
$notifBadge = $notifTotal > 99 ? '99+' : (string) $notifTotal;

if (!function_exists('notifSnippet')) {
    function notifSnippet($text, $length = 70) {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }
        $short = function_exists('mb_substr') ? mb_substr($text, 0, $length) : substr($text, 0, $length);
        $full  = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        return $short . ($full > $length ? '…' : '');
    }
}
?>
<!-- =========================================================
     Notifications bell (Dashboard only)
     ========================================================= -->
<div class="notif-wrap" id="notifWrap">
    <button type="button"
            class="notif-bell"
            id="notifBell"
            aria-label="Notifications"
            aria-haspopup="true"
            aria-expanded="false">
        <svg viewBox="0 0 24 24" width="19" height="19" fill="none"
             stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.7 21a2 2 0 0 1-3.4 0"></path>
        </svg>
        <?php if ($notifTotal > 0): ?>
            <span class="notif-badge"><?= htmlspecialchars($notifBadge) ?></span>
        <?php endif; ?>
    </button>

    <div class="notif-panel" role="dialog" aria-label="Notifications">
        <div class="notif-head">
            <span>Notifications</span>
            <span class="notif-head-count"><?= (int) $notifTotal ?> pending</span>
        </div>

        <div class="notif-body">
            <?php if ($notifTotal === 0): ?>
                <div class="notif-empty">
                    You're all caught up.<br>
                    <span class="notif-empty-sub">No pending reviews, overdue tasks or mentions.</span>
                </div>
            <?php endif; ?>

            <?php if ($notifPendingRequests > 0): ?>
                <div class="notif-section">
                    <div class="notif-section-title">
                        <span>Group requests</span>
                        <span class="notif-count"><?= (int) $notifPendingRequests ?></span>
                    </div>
                    <?php foreach ($notifRequestItems as $item): ?>
                        <a class="notif-item" href="teacher_review.php#requests">
                            <div class="notif-item-title"><?= htmlspecialchars($item['group_name']) ?></div>
                            <div class="notif-item-meta">
                                <?= htmlspecialchars($item['project_name']) ?>
                                <?php if (!empty($item['submitter_name'])): ?>
                                    · <?= htmlspecialchars($item['submitter_name']) ?>
                                <?php endif; ?>
                                · <?= htmlspecialchars(timeAgo($item['submitted_at'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($notifPendingRequests > count($notifRequestItems)): ?>
                        <a class="notif-more" href="teacher_review.php#requests">
                            +<?= (int) ($notifPendingRequests - count($notifRequestItems)) ?> more
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($notifOverdueTasks > 0): ?>
                <div class="notif-section">
                    <div class="notif-section-title">
                        <span>Overdue tasks</span>
                        <span class="notif-count notif-count-late"><?= (int) $notifOverdueTasks ?></span>
                    </div>
                    <?php foreach ($notifOverdueItems as $item): ?>
                        <a class="notif-item" href="team_detail.php?id=<?= (int) $item['group_id'] ?>">
                            <div class="notif-item-title"><?= htmlspecialchars($item['title']) ?></div>
                            <div class="notif-item-meta">
                                <?= htmlspecialchars($item['group_name']) ?>
                                · due <?= htmlspecialchars(date('d M Y', strtotime($item['due_date']))) ?>
                                · <span class="notif-late">Overdue <?= (int) $item['days_late'] ?>d</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($notifOverdueTasks > count($notifOverdueItems)): ?>
                        <a class="notif-more" href="teacher_teams.php">+<?= (int) ($notifOverdueTasks - count($notifOverdueItems)) ?> more</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($notifMentionCount > 0): ?>
                <div class="notif-section">
                    <div class="notif-section-title">
                        <span>Mentions</span>
                        <span class="notif-count"><?= (int) $notifMentionCount ?></span>
                    </div>
                    <?php foreach ($notifMentionItems as $item): ?>
                        <a class="notif-item" href="teacher_messages.php?group_id=<?= (int) $item['group_id'] ?>">
                            <div class="notif-item-title"><?= htmlspecialchars($item['sender_name']) ?> mentioned you</div>
                            <div class="notif-item-meta">
                                <?= htmlspecialchars($item['group_name']) ?>
                                · <?= htmlspecialchars(notifSnippet($item['content'])) ?>
                                · <?= htmlspecialchars(timeAgo($item['created_at'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($notifPendingFiles > 0): ?>
                <div class="notif-section">
                    <div class="notif-section-title">
                        <span>Files to review</span>
                        <span class="notif-count"><?= (int) $notifPendingFiles ?></span>
                    </div>
                    <?php foreach ($notifFileItems as $item): ?>
                        <a class="notif-item" href="teacher_review.php">
                            <div class="notif-item-title">
                                <?= htmlspecialchars($item['display_name'] ?: $item['file_name']) ?>
                            </div>
                            <div class="notif-item-meta">
                                <?= htmlspecialchars($item['group_name'] ?: '—') ?>
                                · uploaded <?= htmlspecialchars(timeAgo($item['uploaded_at'])) ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($notifPendingFiles > count($notifFileItems)): ?>
                        <a class="notif-more" href="teacher_review.php">
                            +<?= (int) ($notifPendingFiles - count($notifFileItems)) ?> more
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($notifPendingFiles + $notifPendingRequests > 0): ?>
            <div class="notif-foot">
                <a href="teacher_review.php">Open Review Center →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var wrap = document.getElementById('notifWrap');
    var btn  = document.getElementById('notifBell');
    if (!wrap || !btn) return;

    function close() {
        wrap.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (event) {
        event.stopPropagation();
        var open = wrap.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
        if (!wrap.contains(event.target)) close();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') close();
    });
})();
</script>
