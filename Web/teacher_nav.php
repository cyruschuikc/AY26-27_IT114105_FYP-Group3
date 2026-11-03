<?php
// teacher_nav.php
// $active 可选值：dashboard / projects / teams / students / review / files / announcements / profile

if (!isset($active)) { $active = ''; }

// ===== 确保 session 已启动 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== 拿数据库连接（mysqli） =====
require_once __DIR__ . '/db.php';

/** @var mysqli $conn */

// ===== 当前登录老师（默认值，防止未登录时报错） =====
$teacherName     = 'Unknown';
$teacherRole     = 'Teacher';
$teacherInitials = '';
$navTeacherId    = 0;

if (isset($_SESSION['userID'])) {

    $sql = "
        SELECT u.id, u.username, u.email, u.role,
               t.id AS teacher_id,
               t.department, t.title
        FROM Users u
        LEFT JOIN Teachers t ON t.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'teacher'
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        $userID = (int) $_SESSION['userID'];
        mysqli_stmt_bind_param($stmt, "i", $userID);
        mysqli_stmt_execute($stmt);

        $rs = mysqli_stmt_get_result($stmt);
        $rc = mysqli_fetch_assoc($rs);

        mysqli_stmt_close($stmt);

        if ($rc) {
            $teacherName = $rc['username'];
            $navTeacherId = (int) $rc['teacher_id'];

            // 头像首字母
            $parts = preg_split('/\s+/', trim($teacherName));
            if (count($parts) >= 2) {
                $teacherInitials = strtoupper(
                        substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1)
                );
            } elseif (count($parts) === 1 && $parts[0] !== '') {
                $teacherInitials = strtoupper(substr($parts[0], 0, 2));
            }

            // 角色文字：优先用 Teachers.title
            $teacherRole = !empty($rc['title']) ? $rc['title'] : 'Teacher';
        }
    }
}

// ===== 侧边栏红点：当前老师的待办数量 =====
$navPendingFiles        = 0;
$navPendingRequests     = 0;
$navOverdueTasks        = 0;
$navUnsubmittedStudents = 0;
$navUnreadMessages      = 0;

if ($navTeacherId > 0) {

    // 待审核文件
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `files` f
        INNER JOIN `projects` p ON p.id = f.project_id
        WHERE f.status = 'Unviewed'
          AND f.task_id IS NOT NULL
          AND p.supervisor_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $navTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navPendingFiles = (int) $rc['c'];
    mysqli_stmt_close($stmt);

    // 待审批小组申请
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `group_requests` r
        INNER JOIN `projects` p ON p.id = r.project_id
        WHERE r.status = 'Pending' AND p.supervisor_id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $navTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navPendingRequests = (int) $rc['c'];
    mysqli_stmt_close($stmt);

    // 逾期任务
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `tasks` t
        INNER JOIN `projects` p ON p.id = t.project_id
        WHERE p.supervisor_id = ?
          AND t.status <> 'Completed'
          AND t.due_date IS NOT NULL
          AND t.due_date < CURDATE()
    ");
    mysqli_stmt_bind_param($stmt, "i", $navTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navOverdueTasks = (int) $rc['c'];
    mysqli_stmt_close($stmt);

    // 还没交过任何文件的学生
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(DISTINCT ts.student_id) AS c
        FROM `teacher_student` ts
        WHERE ts.teacher_id = ?
          AND ts.student_id NOT IN (SELECT DISTINCT uploader_id FROM `files`)
    ");
    mysqli_stmt_bind_param($stmt, "i", $navTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navUnsubmittedStudents = (int) $rc['c'];
    mysqli_stmt_close($stmt);

    // 未读小组消息
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `messages` m
        INNER JOIN `groups` g   ON g.id = m.group_id
        INNER JOIN `projects` p ON p.id = g.project_id
        LEFT JOIN `message_reads` mr
               ON mr.group_id = m.group_id AND mr.user_id = ?
        WHERE p.supervisor_id = ?
          AND m.recipient_id IS NULL
          AND m.id > COALESCE(mr.last_read_message_id, 0)
          AND (m.sender_user_id IS NULL OR m.sender_user_id <> ?)
          AND g.status IN ('Voting', 'Executing')
          AND NOT EXISTS (
              SELECT 1 FROM `group_requests` gr
              WHERE gr.project_id = g.project_id
                AND gr.group_name = g.name
                AND gr.status = 'Pending'
          )
    ");
    $navUserID = (int) $_SESSION['userID'];
    mysqli_stmt_bind_param($stmt, "iii", $navUserID, $navTeacherId, $navUserID);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navUnreadMessages = (int) $rc['c'];
    mysqli_stmt_close($stmt);
}

$navReviewCount = $navPendingFiles + $navPendingRequests;

// 头像圆圈背景：三档中性灰，按名字哈希取色（单色体系内不再出现彩色头像）
$avatarPalette = array(
        '#EFEDE8',
        '#E4E1DB',
        '#DAD6CF',
);
$avatarBg = $avatarPalette[abs(crc32($teacherName)) % count($avatarPalette)];
?>
<!-- =========================================================
     Sidebar
     ========================================================= -->
<aside class="sidebar" id="teacherSidebar">

    <!-- 顶部：用户卡片 -->
    <div class="sidebar-user">
        <div class="sidebar-avatar" style="background: <?= $avatarBg ?>;">
            <?= htmlspecialchars($teacherInitials) ?>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($teacherName) ?></div>
            <div class="sidebar-user-role"><?= htmlspecialchars($teacherRole) ?></div>
        </div>
    </div>

    <!-- 菜单 -->
    <nav class="sidebar-menu">
        <a href="teacher_index.php" class="item <?= $active==='dashboard'?'active':'' ?>" title="Dashboard" aria-label="Dashboard">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
            </svg>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="teacher_projects.php" class="item <?= $active==='projects'?'active':'' ?>" title="Projects" aria-label="Projects">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5v8A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5z"></path>
            </svg>
            <span class="nav-label">Projects</span>
        </a>
        <a href="teacher_teams.php" class="item <?= $active==='teams'?'active':'' ?>" title="Teams" aria-label="Teams">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span class="nav-label">Teams</span>
            <?php if ($navOverdueTasks > 0): ?>
                <span class="nav-dot" title="<?= (int) $navOverdueTasks ?> overdue task<?= $navOverdueTasks === 1 ? '' : 's' ?>" aria-label="<?= (int) $navOverdueTasks ?> overdue tasks"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_students.php" class="item <?= $active==='students'?'active':'' ?>" title="Students" aria-label="Students">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m2 9 10-5 10 5-10 5z"></path>
                <path d="M6 11.5V16c2.5 2 9.5 2 12 0v-4.5M22 9v6"></path>
            </svg>
            <span class="nav-label">Students</span>
            <?php if ($navUnsubmittedStudents > 0): ?>
                <span class="nav-dot" title="<?= (int) $navUnsubmittedStudents ?> student<?= $navUnsubmittedStudents === 1 ? '' : 's' ?> with no submissions" aria-label="<?= (int) $navUnsubmittedStudents ?> students with no submissions"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_review.php" class="item <?= $active==='review'?'active':'' ?>" title="Review Center" aria-label="Review Center">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                <path d="m8 12 2.2 2.2L16 9"></path>
            </svg>
            <span class="nav-label">Review Center</span>
            <?php if ($navReviewCount > 0): ?>
                <span class="nav-dot" title="<?= (int) $navReviewCount ?> item<?= $navReviewCount === 1 ? '' : 's' ?> waiting for review" aria-label="<?= (int) $navReviewCount ?> items waiting for review"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_files.php" class="item <?= $active==='files'?'active':'' ?>" title="Files" aria-label="Files">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <path d="M14 2v6h6M8 13h8M8 17h6"></path>
            </svg>
            <span class="nav-label">Files</span>
            <?php if ($navPendingFiles > 0): ?>
                <span class="nav-dot" title="<?= (int) $navPendingFiles ?> unviewed file<?= $navPendingFiles === 1 ? '' : 's' ?>" aria-label="<?= (int) $navPendingFiles ?> unviewed files"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_messages.php" class="item <?= $active==='messages'?'active':'' ?>" title="Messages" aria-label="Messages">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 15a4 4 0 0 1-4 4H8l-5 2 1.5-4A4 4 0 0 1 3 14V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
            </svg>
            <span class="nav-label">Messages</span>
            <?php if ($navUnreadMessages > 0): ?>
                <span class="nav-dot" title="<?= (int) $navUnreadMessages ?> unread message<?= $navUnreadMessages === 1 ? '' : 's' ?>" aria-label="<?= (int) $navUnreadMessages ?> unread messages"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_announcements.php" class="item <?= $active==='announcements'?'active':'' ?>" title="Announcements" aria-label="Announcements">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m3 11 18-7-5 16-4-5-5-1zM12 15l4 1"></path>
            </svg>
            <span class="nav-label">Announcements</span>
        </a>
        <a href="teacher_profile.php" class="item <?= $active==='profile'?'active':'' ?>" title="Profile" aria-label="Profile">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M4 21a8 8 0 0 1 16 0"></path>
            </svg>
            <span class="nav-label">Profile</span>
        </a>
    </nav>

    <!-- 底部：退出登录 -->
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                 stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <path d="M16 17l5-5-5-5"></path>
                <path d="M21 12H9"></path>
            </svg>
            <span>Log Out</span>
        </a>
    </div>

</aside>

<button type="button" class="sidebar-toggle" id="sidebarToggle"
        aria-controls="teacherSidebar" aria-expanded="true" aria-label="Collapse navigation">
    <svg class="toggle-menu-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M4 7h16M4 12h16M4 17h16"></path>
    </svg>
    <svg class="toggle-close-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="m6 6 12 12M18 6 6 18"></path>
    </svg>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

<script>
(function () {
    var body = document.body;
    var toggle = document.getElementById('sidebarToggle');
    var backdrop = document.getElementById('sidebarBackdrop');
    var shell = document.getElementById('teacherSidebar');
    var desktop = window.matchMedia('(min-width: 901px)');
    var storageKey = 'teambox-teacher-sidebar-collapsed';

    if (!toggle || !shell) return;

    function setDesktopCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapsed', collapsed);
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Expand navigation' : 'Collapse navigation');
        try { localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (error) {}
    }

    function setDrawerOpen(open) {
        body.classList.toggle('sidebar-drawer-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    }

    if (desktop.matches) {
        try {
            setDesktopCollapsed(localStorage.getItem(storageKey) === '1');
        } catch (error) {}
    } else {
        setDrawerOpen(false);
    }

    toggle.addEventListener('click', function () {
        if (desktop.matches) {
            setDesktopCollapsed(!body.classList.contains('sidebar-collapsed'));
        } else {
            setDrawerOpen(!body.classList.contains('sidebar-drawer-open'));
        }
    });

    backdrop.addEventListener('click', function () {
        setDrawerOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.classList.contains('sidebar-drawer-open')) {
            setDrawerOpen(false);
            toggle.focus();
        }
    });

    shell.querySelectorAll('.sidebar-menu a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!desktop.matches) setDrawerOpen(false);
        });
    });

    desktop.addEventListener('change', function () {
        if (desktop.matches) {
            setDrawerOpen(false);
            try {
                setDesktopCollapsed(localStorage.getItem(storageKey) === '1');
            } catch (error) {}
        } else {
            body.classList.remove('sidebar-collapsed');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open navigation');
        }
    });
})();
</script>

<?php include __DIR__ . '/teacher_notifications.php'; ?>
