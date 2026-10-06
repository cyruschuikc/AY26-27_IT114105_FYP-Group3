<?php
// teacher_nav.php
// 用法：在每个页面顶部设置 $active，然后 include 这个文件
// 可选值：dashboard / projects / teams / students / contributions / files / announcements / profile
if (!isset($active)) { $active = ''; }
?>
<aside class="sidebar">
    <div class="brand">TeamBox</div>

    <a href="teacher_index.php"          class="<?= $active==='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="teacher_projects.php"       class="<?= $active==='projects'?'active':'' ?>">Projects</a>
    <a href="teacher_index.php"          class="<?= $active==='teams'?'active':'' ?>">Teams</a>
    <a href="teacher_students.php"       class="<?= $active==='students'?'active':'' ?>">Students</a>
    <a href="teacher_contributions.php"  class="<?= $active==='contributions'?'active':'' ?>">Contribution Reports</a>
    <a href="teacher_files.php"          class="<?= $active==='files'?'active':'' ?>">Files</a>
    <a href="teacher_announcements.php"  class="<?= $active==='announcements'?'active':'' ?>">Announcements</a>
    <a href="teacher_profile.php"        class="<?= $active==='profile'?'active':'' ?>">Profile</a>
    <a href="login.php" class="logout">Log Out</a>
</aside>