<?php
$active = 'dashboard';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
/** @var mysqli $conn */


// =========================================================
// 1. 汇总数字
// =========================================================

// Total Students
$totalStudents = 0;
$rs = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `Students`");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $totalStudents = (int)$rc['c']; mysqli_free_result($rs); }

// Formed Teams
$formedTeams = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(*) AS c
    FROM `Groups`
    WHERE status IN ('Voting','Executing','Completed')
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $formedTeams = (int)$rc['c']; mysqli_free_result($rs); }

// Unassigned Students（不在任何 Group 里的学生）
$unassigned = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(*) AS c
    FROM `Students` s
    WHERE s.id NOT IN (SELECT DISTINCT student_id FROM `Group_Members`)
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $unassigned = (int)$rc['c']; mysqli_free_result($rs); }

// Past Due Tasks（未完成 + 已过期）
$pastDue = 0;
$rs = mysqli_query($conn, "
    SELECT COUNT(*) AS c
    FROM `Tasks`
    WHERE status <> 'Completed'
      AND due_date < CURDATE()
");
if ($rs) { $rc = mysqli_fetch_assoc($rs); $pastDue = (int)$rc['c']; mysqli_free_result($rs); }


// =========================================================
// 2. Teams 表格
// =========================================================
$teams = array();

$sql = "
    SELECT
        g.id,
        g.name,
        g.status,
        g.formed_at,
        g.max_members,
        p.name AS project_name,
        u.username AS leader_name,
        (SELECT COUNT(*) FROM `Group_Members` gm WHERE gm.group_id = g.id) AS member_count,
        (SELECT COUNT(*) FROM `Tasks` t
            WHERE t.group_id = g.id
              AND t.status <> 'Completed'
              AND t.due_date < CURDATE()) AS overdue_count,
        (SELECT COUNT(*) FROM `Tasks` t2
            WHERE t2.group_id = g.id
              AND t2.status = 'Completed') AS completed_tasks,
        (SELECT COUNT(*) FROM `Tasks` t3
            WHERE t3.group_id = g.id) AS total_tasks,
        (SELECT MAX(a.created_at) FROM `Activities` a WHERE a.group_id = g.id) AS last_activity
    FROM `Groups` g
    LEFT JOIN `Projects` p ON p.id = g.project_id
    LEFT JOIN `Students` s ON s.id = g.leader_id
    LEFT JOIN `Users`    u ON u.id = s.user_id
    ORDER BY g.id
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $teams[] = $row;
    }
    mysqli_free_result($rs);
}


// =========================================================
// 3. Recent Activity
// =========================================================
$activities = array();

$sql = "
    SELECT a.action,
           a.detail,
           a.created_at,
           u.username AS actor_name
    FROM `Activities` a
    LEFT JOIN `Users` u ON u.id = a.actor_id
    ORDER BY a.created_at DESC
    LIMIT 5
";

$rs = mysqli_query($conn, $sql);
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $activities[] = $row;
    }
    mysqli_free_result($rs);
}

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

        <h1>Instructor Dashboard</h1>
        <p class="subtitle">ITE4116M Final Year Project · 2026/27</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Students</div>
                <div class="value"><?php echo $totalStudents; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Formed Teams</div>
                <div class="value"><?php echo $formedTeams; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Unassigned</div>
                <div class="value"><?php echo $unassigned; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Past Due Tasks</div>
                <div class="value"><?php echo $pastDue; ?></div>
            </div>
        </div>

        <!-- Quick Access -->
        <h2>Quick Access</h2>
        <div class="entry-grid">

            <a href="teacher_projects.php" class="entry-card">
                <div class="entry-icon">PRJ</div>
                <div class="entry-title">Projects</div>
                <div class="entry-desc">View all FYP projects under your supervision.</div>
                <div class="entry-meta">View all</div>
            </a>

            <a href="teacher_teams.php" class="entry-card">
                <div class="entry-icon">TEAM</div>
                <div class="entry-title">Teams</div>
                <div class="entry-desc">Manage teams, rosters, leaders and progress.</div>
                <div class="entry-meta"><?php echo $formedTeams; ?> teams</div>
            </a>

            <a href="teacher_students.php" class="entry-card">
                <div class="entry-icon">STU</div>
                <div class="entry-title">Students</div>
                <div class="entry-desc">See all students, assigned or unassigned.</div>
                <div class="entry-meta"><?php echo $totalStudents; ?> students</div>
            </a>

            <a href="teacher_review.php" class="entry-card">
                <div class="entry-icon">REV</div>
                <div class="entry-title">Review Center</div>
                <div class="entry-desc">Review files and approve group formation requests.</div>
                <div class="entry-meta">Open</div>
            </a>

            <a href="teacher_files.php" class="entry-card">
                <div class="entry-icon">FILE</div>
                <div class="entry-title">Files</div>
                <div class="entry-desc">Browse all files uploaded by your teams.</div>
                <div class="entry-meta">View all</div>
            </a>

            <a href="teacher_announcements.php" class="entry-card">
                <div class="entry-icon">ANN</div>
                <div class="entry-title">Announcements</div>
                <div class="entry-desc">Send messages to all teams or a selected team.</div>
                <div class="entry-meta">Open</div>
            </a>

            <a href="teacher_profile.php" class="entry-card">
                <div class="entry-icon">PRO</div>
                <div class="entry-title">Profile</div>
                <div class="entry-desc">View and edit your instructor account.</div>
                <div class="entry-meta">Account</div>
            </a>

        </div>

        <!-- Teams -->
        <h2>Teams</h2>

        <div class="toolbar">
            <div class="field field-search">
                <label for="teamSearch">Search team</label>
                <input type="search" id="teamSearch" name="teamSearch"
                       placeholder="e.g. Team 01 or CHAN Ho Yin">
            </div>

            <div class="field field-status">
                <label for="teamStatus">Filter by status</label>
                <select id="teamStatus" name="teamStatus">
                    <option value="all">All Status</option>
                    <option value="executing">Executing</option>
                    <option value="voting">Voting</option>
                    <option value="recruiting">Recruiting</option>
                </select>
            </div>
        </div>

        <div class="card">
            <table>
                <tr>
                    <th>Team Name</th>
                    <th>Leader</th>
                    <th>Members</th>
                    <th>Progress</th>
                    <th>Last Activity</th>
                    <th>Status</th>
                    <th>Overdue</th>
                    <th></th>
                </tr>

                <?php if (count($teams) === 0): ?>
                    <tr>
                        <td colspan="8" class="muted" style="text-align: center; padding: 30px;">
                            No teams yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td>
                            <?php if (!empty($t['leader_name'])): ?>
                                <?php echo htmlspecialchars($t['leader_name']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo (int)$t['member_count']; ?> / <?php echo (int)$t['max_members']; ?>
                        </td>
                        <td>
                            <?php
                            $done  = (int)$t['completed_tasks'];
                            $total = (int)$t['total_tasks'];
                            echo $total > 0 ? ($done . ' / ' . $total) : '—';
                            ?>
                        </td>
                        <td class="muted">
                            <?php echo timeAgo($t['last_activity']); ?>
                        </td>
                        <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                        <td>
                            <?php if ((int)$t['overdue_count'] > 0): ?>
                                <span style="color:#d32f2f; font-weight:600;"><?php echo (int)$t['overdue_count']; ?></span>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="team_detail.php?id=<?php echo (int)$t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Activity -->
        <h2>Recent Activity</h2>
        <div class="card">
            <ul>
                <?php if (count($activities) === 0): ?>
                    <li class="muted">No activity yet.</li>
                <?php endif; ?>

                <?php foreach ($activities as $a): ?>
                    <li>
                        <span>
                            <?php if (!empty($a['actor_name'])): ?>
                                <strong><?php echo htmlspecialchars($a['actor_name']); ?></strong>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($a['detail']); ?>
                        </span>
                        <span class="muted"><?php echo timeAgo($a['created_at']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</main>

</body>
</html>