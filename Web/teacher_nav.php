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

if ($navTeacherId > 0) {

    // 待审核文件
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) AS c
        FROM `files` f
        INNER JOIN `projects` p ON p.id = f.project_id
        WHERE f.status = 'Unviewed' AND p.supervisor_id = ?
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
        SELECT COUNT(DISTINCT ce.student_id) AS c
        FROM `course_enrollments` ce
        INNER JOIN `projects` p ON p.course_id = ce.course_id
        WHERE p.supervisor_id = ?
          AND ce.student_id NOT IN (SELECT DISTINCT uploader_id FROM `files`)
    ");
    mysqli_stmt_bind_param($stmt, "i", $navTeacherId);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    $rc = mysqli_fetch_assoc($rs);
    $navUnsubmittedStudents = (int) $rc['c'];
    mysqli_stmt_close($stmt);
}

$navReviewCount = $navPendingFiles + $navPendingRequests;

// 头像圆圈背景：从几个浅色里按名字挑一个（同名同色）
$avatarPalette = array(
        '#dbeafe', // 浅蓝
        '#e9d5ff', // 浅紫
        '#fce7f3', // 浅粉
        '#d1fae5', // 浅绿
        '#fef3c7', // 浅黄
);
$avatarBg = $avatarPalette[abs(crc32($teacherName)) % count($avatarPalette)];
?>
<!-- =========================================================
     Sidebar
     ========================================================= -->
<aside class="sidebar">

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
        <a href="teacher_index.php"          class="<?= $active==='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="teacher_projects.php"       class="<?= $active==='projects'?'active':'' ?>">Projects</a>
        <a href="teacher_teams.php"          class="<?= $active==='teams'?'active':'' ?>">
            <span>Teams</span>
            <?php if ($navOverdueTasks > 0): ?>
                <span class="nav-dot" title="<?= (int) $navOverdueTasks ?> overdue task<?= $navOverdueTasks === 1 ? '' : 's' ?>" aria-label="<?= (int) $navOverdueTasks ?> overdue tasks"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_students.php"       class="<?= $active==='students'?'active':'' ?>">
            <span>Students</span>
            <?php if ($navUnsubmittedStudents > 0): ?>
                <span class="nav-dot" title="<?= (int) $navUnsubmittedStudents ?> student<?= $navUnsubmittedStudents === 1 ? '' : 's' ?> with no submissions" aria-label="<?= (int) $navUnsubmittedStudents ?> students with no submissions"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_review.php"         class="<?= $active==='review'?'active':'' ?>">
            <span>Review Center</span>
            <?php if ($navReviewCount > 0): ?>
                <span class="nav-dot" title="<?= (int) $navReviewCount ?> item<?= $navReviewCount === 1 ? '' : 's' ?> waiting for review" aria-label="<?= (int) $navReviewCount ?> items waiting for review"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_files.php"          class="<?= $active==='files'?'active':'' ?>">
            <span>Files</span>
            <?php if ($navPendingFiles > 0): ?>
                <span class="nav-dot" title="<?= (int) $navPendingFiles ?> unviewed file<?= $navPendingFiles === 1 ? '' : 's' ?>" aria-label="<?= (int) $navPendingFiles ?> unviewed files"></span>
            <?php endif; ?>
        </a>
        <a href="teacher_announcements.php"  class="<?= $active==='announcements'?'active':'' ?>">Announcements</a>
        <a href="teacher_profile.php"        class="<?= $active==='profile'?'active':'' ?>">Profile</a>
    </nav>

</aside>

<!-- =========================================================
     Log Out —— 页面右上角
     ========================================================= -->
<a href="logout.php" class="logout-btn">Log Out</a>

<?php
// 铃铛只出现在老师首页
if ($active === 'dashboard') {
    include __DIR__ . '/teacher_notifications.php';
}
?>
