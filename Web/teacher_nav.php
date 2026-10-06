<?php
// teacher_nav.php
// $active 可选值：dashboard / projects / teams / students / review / files / announcements / profile

if (!isset($active)) { $active = ''; }

// session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 数据库
require_once __DIR__ . '/db.php';

// 当前登录老师
$teacherName     = 'Unknown';
$teacherRole     = 'Teacher';
$teacherInitials = '';

if (isset($_SESSION['userID'])) {

    $sql = "
        SELECT u.id, u.username, u.email, u.role,
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

            // 首字母
            $parts = preg_split('/\s+/', trim($teacherName));
            if (count($parts) >= 2) {
                $teacherInitials = strtoupper(
                        substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1)
                );
            } elseif (count($parts) === 1 && $parts[0] !== '') {
                $teacherInitials = strtoupper(substr($parts[0], 0, 2));
            }

            // 角色
            $teacherRole = !empty($rc['title']) ? $rc['title'] : 'Teacher';
        }
    }
}

// 头像圆圈背景：从浅色里按名字挑一个
$avatarPalette = array('#dbeafe', '#e9d5ff', '#fce7f3', '#d1fae5', '#fef3c7');
$avatarBg = $avatarPalette[abs(crc32($teacherName)) % count($avatarPalette)];
?>
<aside class="sidebar">

    <!-- 顶部：用户卡片 -->
    <div class="sidebar-user">
        <div class="sidebar-avatar" style="background: <?php echo $avatarBg; ?>;">
            <?php echo htmlspecialchars($teacherInitials); ?>
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?php echo htmlspecialchars($teacherName); ?></div>
            <div class="sidebar-user-role"><?php echo htmlspecialchars($teacherRole); ?></div>
        </div>
    </div>

    <!-- 菜单 -->
    <nav class="sidebar-menu">
        <a href="teacher_index.php"          class="<?php echo $active==='dashboard'?'active':''; ?>">Dashboard</a>
        <a href="teacher_projects.php"       class="<?php echo $active==='projects'?'active':''; ?>">Projects</a>
        <a href="teacher_teams.php"          class="<?php echo $active==='teams'?'active':''; ?>">Teams</a>
        <a href="teacher_students.php"       class="<?php echo $active==='students'?'active':''; ?>">Students</a>
        <a href="teacher_review.php"         class="<?php echo $active==='review'?'active':''; ?>">Review Center</a>
        <a href="teacher_files.php"          class="<?php echo $active==='files'?'active':''; ?>">Files</a>
        <a href="teacher_announcements.php"  class="<?php echo $active==='announcements'?'active':''; ?>">Announcements</a>
        <a href="teacher_profile.php"        class="<?php echo $active==='profile'?'active':''; ?>">Profile</a>
    </nav>

</aside>

<!-- Log Out -->
<a href="logout.php" class="logout-btn">Log Out</a>