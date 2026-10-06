<?php
$active = 'teams';

$summary = array(
    'total_teams'    => 4,
    'formed_teams'   => 3,
    'recruiting'     => 1,
    'past_due'       => 2,
);

$teams = array(
    array('id'=>1, 'name'=>'Team 01', 'leader'=>'CHAN Ho Yin',     'members'=>'4 / 4', 'progress'=>'3 / 5', 'last_activity'=>'2h ago', 'overdue'=>0, 'status'=>'Executing'),
    array('id'=>2, 'name'=>'Team 02', 'leader'=>'LAI Ka-Yu Savio', 'members'=>'4 / 4', 'progress'=>'1 / 5', 'last_activity'=>'1d ago', 'overdue'=>1, 'status'=>'Voting'),
    array('id'=>3, 'name'=>'Team 03', 'leader'=>'MIAO Tung',       'members'=>'3 / 4', 'progress'=>'0 / 5', 'last_activity'=>'3d ago', 'overdue'=>2, 'status'=>'Recruiting'),
    array('id'=>4, 'name'=>'Team 04', 'leader'=>'',                'members'=>'2 / 4', 'progress'=>'',      'last_activity'=>'5d ago', 'overdue'=>0, 'status'=>'Recruiting'),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Teams</h1>
        <p class="subtitle">All project teams under your supervision · 2026/27</p>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Teams</div>
                <div class="value"><?php echo $summary['total_teams']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Formed Teams</div>
                <div class="value"><?php echo $summary['formed_teams']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Recruiting</div>
                <div class="value"><?php echo $summary['recruiting']; ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Past Due Tasks</div>
                <div class="value"><?php echo $summary['past_due']; ?></div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="field field-search">
                <label for="teamSearch">Search team</label>
                <input
                    type="search"
                    id="teamSearch"
                    name="teamSearch"
                    placeholder="e.g. Team 01 or CHAN Ho Yin"
                >
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

        <!-- Team list -->
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
                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td>
                            <?php if ($t['leader'] !== ''): ?>
                                <?php echo htmlspecialchars($t['leader']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['members']); ?></td>
                        <td>
                            <?php if ($t['progress'] !== ''): ?>
                                <?php echo htmlspecialchars($t['progress']); ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="muted"><?php echo htmlspecialchars($t['last_activity']); ?></td>
                        <td><span class="pill"><?php echo htmlspecialchars($t['status']); ?></span></td>
                        <td>
                            <?php if ($t['overdue'] > 0): ?>
                                <?php echo $t['overdue']; ?>
                            <?php else: ?>
                                <span class="muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="team_detail.php?id=<?php echo (int)$t['id']; ?>" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</main>

</body>
</html>