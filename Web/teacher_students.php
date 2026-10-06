<?php $active = 'students'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Students</h1>
        <p class="subtitle">All students under your supervision · 20 students</p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Students</div>
                <div class="value">20</div>
            </div>
            <div class="summary-item">
                <div class="label">Assigned to Teams</div>
                <div class="value">15</div>
            </div>
            <div class="summary-item">
                <div class="label">Unassigned</div>
                <div class="value">5</div>
            </div>
            <div class="summary-item">
                <div class="label">At Risk</div>
                <div class="value">2</div>
            </div>
        </div>

        <h2>Assigned Students</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>Team</th>
                    <th>Role</th>
                    <th>Tasks</th>
                    <th></th>
                </tr>
                <tr>
                    <td>CHAN Ho Yin</td>
                    <td>260222111</td>
                    <td>SE-2B</td>
                    <td>Team 01</td>
                    <td>Leader</td>
                    <td>2 / 3</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
                <tr>
                    <td>CHUI Ka Chun</td>
                    <td>260222112</td>
                    <td>SE-2B</td>
                    <td>Team 01</td>
                    <td>Member</td>
                    <td>1 / 2</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
                <tr>
                    <td>LAI Ka-Yu Savio</td>
                    <td>260222113</td>
                    <td>SE-2B</td>
                    <td>Team 01</td>
                    <td>Member</td>
                    <td>1 / 2</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
                <tr>
                    <td>MIAO Tung</td>
                    <td>260222114</td>
                    <td>SE-2B</td>
                    <td>Team 01</td>
                    <td>Member</td>
                    <td>0 / 2</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
            </table>
        </div>

        <h2>Unassigned Students</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <tr>
                    <td>WONG Mei Ling</td>
                    <td>202611234</td>
                    <td>Year 2</td>
                    <td><span class="pill">Unassigned 10d</span></td>
                    <td><button class="link">Assign</button></td>
                </tr>
                <tr>
                    <td>LEE Chun Kit</td>
                    <td>202645678</td>
                    <td>Year 1</td>
                    <td><span class="pill">Unassigned 3d</span></td>
                    <td><button class="link">Assign</button></td>
                </tr>
                <tr>
                    <td>CHEUNG Ka Wai</td>
                    <td>202678901</td>
                    <td>Year 3</td>
                    <td><span class="pill">Unassigned 3d</span></td>
                    <td><button class="link">Assign</button></td>
                </tr>
            </table>
        </div>

    </div>
</main>

</body>
</html>