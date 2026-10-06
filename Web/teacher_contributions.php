<?php $active = 'contributions'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Reports | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Contribution Reports</h1>
        <p class="subtitle">Review and approve student contribution scores</p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Pending Review</div>
                <div class="value">3</div>
            </div>
            <div class="summary-item">
                <div class="label">Approved</div>
                <div class="value">12</div>
            </div>
            <div class="summary-item">
                <div class="label">Flagged</div>
                <div class="value">1</div>
            </div>
            <div class="summary-item">
                <div class="label">Avg. Balance</div>
                <div class="value">Good</div>
            </div>
        </div>

        <h2>Pending Reports</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team</th>
                    <th>Submitted By</th>
                    <th>Period</th>
                    <th>Top Contributor</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Team 01</td>
                    <td>CHAN Ho Yin</td>
                    <td>Sep – Oct 2026</td>
                    <td>CHAN Ho Yin (32%)</td>
                    <td>Good</td>
                    <td><span class="pill">Pending</span></td>
                    <td><a href="#" class="link">Review</a></td>
                </tr>
                <tr>
                    <td>Team 02</td>
                    <td>LAI Ka-Yu Savio</td>
                    <td>Sep – Oct 2026</td>
                    <td>LAI Ka-Yu Savio (30%)</td>
                    <td>Fair</td>
                    <td><span class="pill">Pending</span></td>
                    <td><a href="#" class="link">Review</a></td>
                </tr>
                <tr>
                    <td>Team 03</td>
                    <td>MIAO Tung</td>
                    <td>Sep – Oct 2026</td>
                    <td>MIAO Tung (45%)</td>
                    <td>Unbalanced</td>
                    <td><span class="pill">Flagged</span></td>
                    <td><a href="#" class="link">Review</a></td>
                </tr>
            </table>
        </div>

        <h2>Recently Approved</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team</th>
                    <th>Period</th>
                    <th>Approved On</th>
                    <th>Score</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Team 05</td>
                    <td>Sep 2026</td>
                    <td>28 Sep 2026</td>
                    <td>Good</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
            </table>
        </div>

    </div>
</main>

</body>
</html>