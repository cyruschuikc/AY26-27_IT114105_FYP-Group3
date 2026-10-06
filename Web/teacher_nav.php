<?php
if (!isset($active)) { $active = ''; }
?>
<aside class="sidebar">
    <div class="brand">TeamBox</div>

    <a href="teacher_index.php"          class="<?= $active==='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="teacher_projects.php"       class="<?= $active==='projects'?'active':'' ?>">Projects</a>
    <a href="teacher_teams.php"          class="<?= $active==='teams'?'active':'' ?>">Teams</a>
    <a href="teacher_students.php"       class="<?= $active==='students'?'active':'' ?>">Students</a>
    <a href="teacher_review.php"         class="<?= $active==='review'?'active':'' ?>">Review Center</a>
    <a href="teacher_files.php"          class="<?= $active==='files'?'active':'' ?>">Files</a>
    <a href="teacher_announcements.php"  class="<?= $active==='announcements'?'active':'' ?>">Announcements</a>
    <a href="teacher_profile.php"        class="<?= $active==='profile'?'active':'' ?>">Profile</a>
    <a href="login.php" class="logout">Log Out</a>
</aside>