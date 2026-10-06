<?php $active = 'files'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Files</h1>
        <p class="subtitle">All project files uploaded by your teams</p>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Files</div>
                <div class="value">48</div>
            </div>
            <div class="summary-item">
                <div class="label">This Week</div>
                <div class="value">12</div>
            </div>
            <div class="summary-item">
                <div class="label">Teams Active</div>
                <div class="value">3</div>
            </div>
            <div class="summary-item">
                <div class="label">Storage Used</div>
                <div class="value">2.4 GB</div>
            </div>
        </div>

        <h2>Recent Uploads</h2>
        <div class="card">
            <table>
                <tr>
                    <th>File Name</th>
                    <th>Team</th>
                    <th>Uploaded By</th>
                    <th>Size</th>
                    <th>Date</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Proposal_v2.docx</td>
                    <td>Team 01</td>
                    <td>CHAN Ho Yin</td>
                    <td>2.4 MB</td>
                    <td>Today 10:42</td>
                    <td><a href="#" class="link">Download</a></td>
                </tr>
                <tr>
                    <td>Requirement_Analysis.pdf</td>
                    <td>Team 01</td>
                    <td>LAI Ka-Yu Savio</td>
                    <td>1.8 MB</td>
                    <td>Yesterday</td>
                    <td><a href="#" class="link">Download</a></td>
                </tr>
                <tr>
                    <td>UI_Wireframe.fig</td>
                    <td>Team 01</td>
                    <td>MIAO Tung</td>
                    <td>5.1 MB</td>
                    <td>2 days ago</td>
                    <td><a href="#" class="link">Download</a></td>
                </tr>
                <tr>
                    <td>Database_Schema.sql</td>
                    <td>Team 02</td>
                    <td>LAI Ka-Yu Savio</td>
                    <td>120 KB</td>
                    <td>3 days ago</td>
                    <td><a href="#" class="link">Download</a></td>
                </tr>
            </table>
        </div>

        <h2>Filter by Team</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Team</th>
                    <th>Files</th>
                    <th>Last Upload</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Team 01</td>
                    <td>8</td>
                    <td>Today 10:42</td>
                    <td><a href="#" class="link">Open</a></td>
                </tr>
                <tr>
                    <td>Team 02</td>
                    <td>5</td>
                    <td>Yesterday</td>
                    <td><a href="#" class="link">Open</a></td>
                </tr>
                <tr>
                    <td>Team 03</td>
                    <td>2</td>
                    <td>5 days ago</td>
                    <td><a href="#" class="link">Open</a></td>
                </tr>
            </table>
        </div>

    </div>
</main>

</body>
</html>