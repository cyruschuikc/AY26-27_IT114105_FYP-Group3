<?php
$active = 'dashboard';

$summary = array(
        'total_students' => 20,
        'formed_teams'   => 3,
        'unassigned'     => 5,
        'past_due'       => 2,
);

$activities = array(
        array('Team 01 uploaded Proposal_v2.docx',         '10:42'),
        array('Team 02 started leader voting',             '09:15'),
        array('MIAO Tung created Team 03',                 'Yesterday'),
        array('Requirement Analysis marked complete',      'Yesterday'),
        array('LAI Ka-Yu invited CHUI Ka Chun to Team 02', '2 days ago'),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamBox | Instructor</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Dashboard</h1>
        <p class="subtitle">ITE4116M Final Year Project · Esther Yuen · 2026/27</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Students</div>
                <div class="value"><?php echo $summary['total_students']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Formed Teams</div>
                <div class="value"><?php echo $summary['formed_teams']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Unassigned</div>
                <div class="value"><?php echo $summary['unassigned']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Past Due Tasks</div>
                <div class="value"><?php echo $summary['past_due']; ?></div>
            </div>
        </div>

        <!-- Quick Access -->
        <h2>Quick Access</h2>
        <div class="entry-grid">

            <a href="teacher_projects.php" class="entry-card">
                <div class="entry-icon">PRJ</div>
                <div class="entry-title">Projects</div>
                <div class="entry-desc">View all FYP projects under your supervision.</div>
                <div class="entry-meta">3 active</div>
            </a>

            <a href="teacher_teams.php" class="entry-card">
                <div class="entry-icon">TEAM</div>
                <div class="entry-title">Teams</div>
                <div class="entry-desc">Manage teams, rosters, leaders and progress.</div>
                <div class="entry-meta">4 teams</div>
            </a>

            <a href="teacher_students.php" class="entry-card">
                <div class="entry-icon">STU</div>
                <div class="entry-title">Students</div>
                <div class="entry-desc">See all students, assigned or unassigned.</div>
                <div class="entry-meta">20 students</div>
            </a>

            <a href="teacher_review.php" class="entry-card">
                <div class="entry-icon">REV</div>
                <div class="entry-title">Review Center</div>
                <div class="entry-desc">Review files and approve group formation requests.</div>
                <div class="entry-meta">8 files · 5 requests</div>
            </a>

            <a href="teacher_files.php" class="entry-card">
                <div class="entry-icon">FILE</div>
                <div class="entry-title">Files</div>
                <div class="entry-desc">Browse all files uploaded by your teams.</div>
                <div class="entry-meta">48 files</div>
            </a>

            <a href="teacher_announcements.php" class="entry-card">
                <div class="entry-icon">ANN</div>
                <div class="entry-title">Announcements</div>
                <div class="entry-desc">Send messages to all teams or a selected team.</div>
                <div class="entry-meta">Last sent 3d ago</div>
            </a>

            <a href="teacher_profile.php" class="entry-card">
                <div class="entry-icon">PRO</div>
                <div class="entry-title">Profile</div>
                <div class="entry-desc">View and edit your instructor account.</div>
                <div class="entry-meta">Esther Yuen</div>
            </a>

        </div>

        <!-- Activity -->
        <h2>Recent Activity</h2>
        <div class="card">
            <ul>
                <?php foreach ($activities as $a): ?>
                    <li>
                        <span><?php echo htmlspecialchars($a[0]); ?></span>
                        <span class="muted"><?php echo htmlspecialchars($a[1]); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</main>

</body>
</html>