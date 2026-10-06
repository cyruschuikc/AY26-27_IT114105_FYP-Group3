<?php $active = 'announcements'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Announcements</h1>
        <p class="subtitle">Send messages to all teams or selected teams</p>

        <h2>New Announcement</h2>
        <div class="card">
            <div class="announcement-block stack">

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annTitle">Title</label>
                    <input
                        type="text"
                        id="annTitle"
                        name="annTitle"
                        placeholder="e.g. Progress Report Deadline"
                    >
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annMessage">Message</label>
                    <textarea
                        id="annMessage"
                        name="annMessage"
                        placeholder="Write your announcement here..."
                    ></textarea>
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annSendTo">Send To</label>
                    <select id="annSendTo" name="annSendTo">
                        <option value="all">All Teams</option>
                        <option value="team01">Team 01</option>
                        <option value="team02">Team 02</option>
                        <option value="team03">Team 03</option>
                    </select>
                </div>

                <button type="button" class="btn-primary">Send Announcement</button>

            </div>
        </div>

        <h2>Sent History</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Title</th>
                    <th>Sent To</th>
                    <th>Sent On</th>
                    <th>Read</th>
                    <th></th>
                </tr>
                <tr>
                    <td>Progress Report Deadline</td>
                    <td>All Teams</td>
                    <td>3 days ago</td>
                    <td>18 / 20</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
                <tr>
                    <td>Team 02 Meeting</td>
                    <td>Team 02</td>
                    <td>5 days ago</td>
                    <td>4 / 4</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
                <tr>
                    <td>Welcome to FYP</td>
                    <td>All Teams</td>
                    <td>20 Sep 2026</td>
                    <td>20 / 20</td>
                    <td><a href="#" class="link">View</a></td>
                </tr>
            </table>
        </div>

    </div>
</main>

</body>
</html>