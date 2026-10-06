<?php $active = 'projects'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Projects</h1>
        <p class="subtitle">All final year projects under your supervision · 2026/27</p>

        <h2>Active Projects</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Project Name</th>
                    <th>Category</th>
                    <th>Team</th>
                    <th>Progress</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <tr>
                    <td>TeamBox</td>
                    <td>Software Engineering</td>
                    <td>Team 01</td>
                    <td>60%</td>
                    <td>30 Apr 2027</td>
                    <td><span class="pill">Executing</span></td>
                    <td><a href="project_detail.php?id=1" class="link">View</a></td>
                </tr>
                <tr>
                    <td>Smart Attendance</td>
                    <td>Mobile App</td>
                    <td>Team 02</td>
                    <td>35%</td>
                    <td>30 Apr 2027</td>
                    <td><span class="pill">Executing</span></td>
                    <td><a href="project_detail.php?id=2" class="link">View</a></td>
                </tr>
                <tr>
                    <td>Campus Map AR</td>
                    <td>AR / VR</td>
                    <td>Team 03</td>
                    <td>10%</td>
                    <td>30 Apr 2027</td>
                    <td><span class="pill">Recruiting</span></td>
                    <td><a href="project_detail.php?id=3" class="link">View</a></td>
                </tr>
            </table>
        </div>

        <h2>Archived Projects</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Project Name</th>
                    <th>Team</th>
                    <th>Final Score</th>
                    <th>Completed</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Library Booking System</td>
                    <td>Team 12</td>
                    <td>A-</td>
                    <td>May 2025</td>
                    <td><a href="project_detail.php?id=99" class="link">View</a></td>
                </tr>
            </table>
        </div>

    </div>
</main>

</body>
</html>