<?php
// teacher_nav.php
// $active 可选值：dashboard / projects / teams / students / review / files / announcements / profile

if (!isset($active)) { $active = ''; }

// ===== 当前用户（UI 阶段先硬编码，将来接后端时改成 $_SESSION） =====
$teacherName  = 'Esther Yuen';
$teacherRole  = 'Teacher';
$teacherParts = preg_split('/\s+/', trim($teacherName));
$teacherInitials = '';

if (count($teacherParts) >= 2) {
    $teacherInitials = strtoupper(substr($teacherParts[0], 0, 1) . substr($teacherParts[count($teacherParts) - 1], 0, 1));
} elseif (count($teacherParts) === 1 && $teacherParts[0] !== '') {
    $teacherInitials = strtoupper(substr($teacherParts[0], 0, 2));
}

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
        <a href="teacher_teams.php"          class="<?= $active==='teams'?'active':'' ?>">Teams</a>
        <a href="teacher_students.php"       class="<?= $active==='students'?'active':'' ?>">Students</a>
        <a href="teacher_review.php"         class="<?= $active==='review'?'active':'' ?>">Review Center</a>
        <a href="teacher_files.php"          class="<?= $active==='files'?'active':'' ?>">Files</a>
        <a href="teacher_announcements.php"  class="<?= $active==='announcements'?'active':'' ?>">Announcements</a>
        <a href="teacher_profile.php"        class="<?= $active==='profile'?'active':'' ?>">Profile</a>
    </nav>

</aside>

<!-- =========================================================
     Log Out —— 页面右上角，跟内容一起滚
     ========================================================= -->
<a href="login.php" class="logout-btn">Log Out</a>