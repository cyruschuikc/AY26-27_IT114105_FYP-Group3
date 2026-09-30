<?php
session_start();

// if (!isset($_SESSION['studentID'])) {
//     header('Location: student_login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Group | TeamBox</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f5f5f5;
            color: #222;
        }

        /* Top Navigation Bar */

        .navbar {
            width: 100%;
            padding: 15px 30px;
            background: black;

            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;

            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
        }

        .logo-area {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .logo-area img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            display: block;
        }

        .logo-area span {
            color: white;
            font-size: 30px;
            font-weight: bold;
        }

        .function-menu {
            width: 100%;

            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 22px;
        }

        .nav-link {
            color: white;
            font-size: 15px;
            text-decoration: none;

            padding: 6px 2px;

            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #bbbbbb;
        }

        .nav-link.active {
            color: white;
            font-weight: bold;
            border-bottom: 2px solid white;
        }

        .logout-link {
            color: #ff8888;
        }

        .logout-link:hover {
            color: #ff4444;
        }

        /* Main Content */

        .main-content {
            width: 90%;
            max-width: 1250px;
            margin: 40px auto;
        }

        .page-heading {
            padding: 30px;
            margin-bottom: 25px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .page-heading h1 {
            margin-bottom: 10px;
            font-size: 35px;
        }

        .page-heading p {
            color: #666;
            font-size: 17px;
            line-height: 1.6;
        }

        .info-note {
            padding: 16px 20px;
            margin-bottom: 25px;

            background: #f7f7f7;
            border-left: 5px solid black;
            border-radius: 8px;

            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Section Layout */

        .section-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .content-box {
            padding: 28px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .content-box h2 {
            padding-bottom: 15px;
            margin-bottom: 22px;

            border-bottom: 2px solid #eeeeee;
            font-size: 23px;
        }

        /* Form (inside Current Group) */

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;

            color: #333;
            font-size: 14px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            height: 47px;
            padding: 0 13px;

            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;

            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: black;
        }

        /* Classroom list */

        .class-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .class-item {
            padding: 16px;

            background: #fafafa;
            border: 2px solid #dddddd;
            border-radius: 10px;

            display: flex;
            align-items: center;
            gap: 14px;

            cursor: pointer;
            transition: 0.3s;
        }

        .class-item:hover {
            border-color: black;
            background: white;
        }

        .class-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: black;
            cursor: pointer;
        }

        .class-avatar {
            width: 42px;
            height: 42px;
            flex-shrink: 0;

            background: black;
            color: white;
            border-radius: 50%;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 14px;
            font-weight: bold;
        }

        .class-info {
            flex: 1;
        }

        .class-info h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .class-info p {
            color: #777;
            font-size: 13px;
        }

        .class-status {
            padding: 5px 10px;

            background: #e5f0ff;
            color: #245889;
            border-radius: 15px;

            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .class-status.taken {
            background: #eeeeee;
            color: #888;
        }

        /* Buttons */

        .primary-btn {
            width: 100%;
            height: 50px;
            padding: 0 20px;
            margin-top: 15px;

            background: black;
            color: white;
            border: 2px solid black;
            border-radius: 9px;

            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .primary-btn:hover {
            background: #333;
        }

        .primary-btn:active {
            transform: scale(0.98);
        }

        .primary-btn:disabled {
            background: #cccccc;
            border-color: #cccccc;
            color: #888;
            cursor: not-allowed;
        }

        /* Current Group */

        .group-info {
            margin-bottom: 18px;
            color: #555;
            font-size: 14px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            margin-left: 5px;

            background: #e5f0ff;
            color: #245889;
            border-radius: 18px;

            font-size: 12px;
            font-weight: bold;
        }

        .status-locked {
            background: #fff1c2;
            color: #765900;
        }

        .status-voting {
            background: #e9e1ff;
            color: #553192;
        }

        .status-ready {
            background: #d9f5df;
            color: #176b2c;
        }

        .member-item {
            padding: 14px 0;
            border-bottom: 1px solid #eeeeee;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-item h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .member-item p {
            color: #777;
            font-size: 13px;
        }

        .remove-btn {
            background: white;
            color: #c62828;
            border: 2px solid #c62828;
            border-radius: 7px;

            padding: 6px 10px;

            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .remove-btn:hover {
            background: #c62828;
            color: white;
        }

        /* Vote Section */

        .vote-option {
            padding: 16px;
            margin-bottom: 12px;

            background: #fafafa;
            border: 2px solid #dddddd;
            border-radius: 10px;

            display: flex;
            align-items: center;
            gap: 14px;

            cursor: pointer;
            transition: 0.3s;
        }

        .vote-option:hover {
            border-color: black;
            background: white;
        }

        .vote-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: black;
            cursor: pointer;
        }

        .vote-option-info {
            flex: 1;
        }

        .vote-option-info h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .vote-option-info p {
            color: #777;
            font-size: 13px;
        }

        /* Vote Result */

        .result-item {
            padding: 14px 0;
            border-bottom: 1px solid #eeeeee;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-header {
            margin-bottom: 8px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-header strong {
            font-size: 15px;
        }

        .result-header span {
            font-size: 14px;
            font-weight: bold;
        }

        .result-bar {
            width: 100%;
            height: 12px;

            background: #dddddd;
            border-radius: 10px;
            overflow: hidden;
        }

        .result-value {
            height: 100%;
            background: black;
            border-radius: 10px;
        }

        /* Responsive */

        @media (max-width: 900px) {
            .section-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 15px;
            }

            .logo-area img {
                width: 55px;
                height: 55px;
            }

            .logo-area span {
                font-size: 26px;
            }

            .function-menu {
                flex-direction: column;
                gap: 12px;
            }

            .main-content {
                width: 94%;
                margin: 25px auto;
            }

            .page-heading h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<!-- Top Navigation Bar -->

<nav class="navbar">

    <div class="logo-area">
        <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
        <span>TeamBox</span>
    </div>

    <div class="function-menu">

        <a href="student_index.php" class="nav-link">
            Dashboard
        </a>
        <a href="student_create_group.php" class="nav-link active">
            Create Group
        </a>
        <a href="my_project.php" class="nav-link">
            My Project
        </a>

        <a href="my_team.php" class="nav-link">
            My Team
        </a>



        <a href="student_task.php" class="nav-link">
            Tasks
        </a>

        <a href="student_files.php" class="nav-link">
            Files
        </a>

        <a href="student_chat.php" class="nav-link">
            Group Chat
        </a>

        <a href="student_notifications.php" class="nav-link">
            Notifications
        </a>

        <a href="student_profile.php" class="nav-link">
            Profile
        </a>

        <a href="login.php" class="nav-link logout-link">
            Log Out
        </a>

    </div>

</nav>

<!-- Main Content -->

<main class="main-content">

    <section class="page-heading">

        <h1>Create Group</h1>

        <p>
            Name your group, set the project, pick classmates from the
            classroom list, then lock the team and vote for a leader.
        </p>

    </section>

    <div class="info-note">
        <strong>How it works:</strong>
        1. Enter Group Name &amp; Project Name &nbsp;→&nbsp;
        2. Tick classmates in the Classroom List &nbsp;→&nbsp;
        3. Click "Add Selected to Group" &nbsp;→&nbsp;
        4. Group fills to 4/4 &nbsp;→&nbsp;
        5. Lock Team &nbsp;→&nbsp;
        6. Vote Group Leader.
    </div>

    <!-- Top Section: Classroom List + Current Group -->

    <section class="section-grid">

        <!-- Classroom List (select members) -->

        <div class="content-box">

            <h2>Classmate List</h2>

            <form action="#" method="post">

                <div class="class-list">

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="A">

                        <div class="class-avatar">SA</div>

                        <div class="class-info">
                            <h3>Student A</h3>
                            <p>Student ID: 250000001</p>
                        </div>

                        <span class="class-status">Available</span>
                    </label>

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="B">

                        <div class="class-avatar">SB</div>

                        <div class="class-info">
                            <h3>Student B</h3>
                            <p>Student ID: 250000002</p>
                        </div>

                        <span class="class-status">Available</span>
                    </label>

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="C">

                        <div class="class-avatar">SC</div>

                        <div class="class-info">
                            <h3>Student C</h3>
                            <p>Student ID: 250000003</p>
                        </div>

                        <span class="class-status">Available</span>
                    </label>

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="D">

                        <div class="class-avatar">SD</div>

                        <div class="class-info">
                            <h3>Student D</h3>
                            <p>Student ID: 250000004</p>
                        </div>

                        <span class="class-status">Available</span>
                    </label>

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="E" disabled>

                        <div class="class-avatar">SE</div>

                        <div class="class-info">
                            <h3>Student E</h3>
                            <p>Student ID: 250000005</p>
                        </div>

                        <span class="class-status taken">In a Group</span>
                    </label>

                    <label class="class-item">
                        <input type="checkbox" name="members[]" value="F">

                        <div class="class-avatar">SF</div>

                        <div class="class-info">
                            <h3>Student F</h3>
                            <p>Student ID: 250000006</p>
                        </div>

                        <span class="class-status">Available</span>
                    </label>

                </div>

                <button type="submit" class="primary-btn">
                    Add Selected to Group
                </button>

            </form>

        </div>

        <!-- Current Group -->

        <div class="content-box">

            <h2>Current Group</h2>

            <form action="#" method="post">

                <div class="form-group">
                    <label for="groupName">Group Name</label>

                    <input
                        type="text"
                        id="groupName"
                        name="groupName"
                        placeholder="e.g. Team A"
                        value="Team A"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="projectName">Project Name</label>

                    <input
                        type="text"
                        id="projectName"
                        name="projectName"
                        placeholder="e.g. TeamBox Collaboration System"
                        value="TeamBox Collaboration System"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="maxMembers">Maximum Members</label>

                    <select id="maxMembers" name="maxMembers" required>
                        <option value="2">2 Members</option>
                        <option value="3">3 Members</option>
                        <option value="4" selected>4 Members</option>
                        <option value="5">5 Members</option>
                        <option value="6">6 Members</option>
                    </select>
                </div>

            </form>

            <div class="group-info">
                <strong>Members:</strong> 3 / 4<br>
                <strong>Status:</strong>
                <span class="status-badge">Recruiting</span>
            </div>

            <div class="member-item">
                <div>
                    <h3>Student A</h3>
                    <p>Student ID: 250000001</p>
                </div>

                <button type="button" class="remove-btn">
                    Remove
                </button>
            </div>

            <div class="member-item">
                <div>
                    <h3>Student B</h3>
                    <p>Student ID: 250000002</p>
                </div>

                <button type="button" class="remove-btn">
                    Remove
                </button>
            </div>

            <div class="member-item">
                <div>
                    <h3>Student C</h3>
                    <p>Student ID: 250000003</p>
                </div>

                <button type="button" class="remove-btn">
                    Remove
                </button>
            </div>

            <form action="#" method="post" style="margin-top: 20px;">
                <button type="submit" class="primary-btn" disabled>
                    Lock Team (Need 4 / 4)
                </button>
            </form>

        </div>

    </section>

    <!-- Vote Section + Result -->

    <section class="section-grid">

        <!-- Vote Group Leader -->

        <div class="content-box">

            <h2>Vote Group Leader</h2>

            <p style="margin-bottom: 18px; color: #666; font-size: 14px;">
                After the team is locked, each member casts
                <strong>one anonymous vote</strong>. The member with
                the most votes becomes the group leader. In a tie, the
                earliest-joined member wins.
            </p>

            <form action="#" method="post">

                <label class="vote-option">
                    <input type="radio" name="vote" value="A" required>

                    <div class="vote-option-info">
                        <h3>Student A</h3>
                        <p>Student ID: 250000001</p>
                    </div>
                </label>

                <label class="vote-option">
                    <input type="radio" name="vote" value="B">

                    <div class="vote-option-info">
                        <h3>Student B</h3>
                        <p>Student ID: 250000002</p>
                    </div>
                </label>

                <label class="vote-option">
                    <input type="radio" name="vote" value="C">

                    <div class="vote-option-info">
                        <h3>Student C</h3>
                        <p>Student ID: 250000003</p>
                    </div>
                </label>

                <label class="vote-option">
                    <input type="radio" name="vote" value="D">

                    <div class="vote-option-info">
                        <h3>Student D</h3>
                        <p>Student ID: 250000004</p>
                    </div>
                </label>

                <button type="submit" class="primary-btn" style="margin-top: 10px;">
                    Submit My Vote
                </button>

            </form>

        </div>

        <!-- Vote Result -->

        <div class="content-box">

            <h2>Vote Result</h2>

            <p style="margin-bottom: 18px; color: #666; font-size: 14px;">
                Results update once all members have voted.
            </p>

            <div class="result-item">
                <div class="result-header">
                    <strong>Student A</strong>
                    <span>2 votes</span>
                </div>
                <div class="result-bar">
                    <div class="result-value" style="width: 50%;"></div>
                </div>
            </div>

            <div class="result-item">
                <div class="result-header">
                    <strong>Student B</strong>
                    <span>1 vote</span>
                </div>
                <div class="result-bar">
                    <div class="result-value" style="width: 25%;"></div>
                </div>
            </div>

            <div class="result-item">
                <div class="result-header">
                    <strong>Student C</strong>
                    <span>1 vote</span>
                </div>
                <div class="result-bar">
                    <div class="result-value" style="width: 25%;"></div>
                </div>
            </div>

            <div class="result-item">
                <div class="result-header">
                    <strong>Student D</strong>
                    <span>0 votes</span>
                </div>
                <div class="result-bar">
                    <div class="result-value" style="width: 0%;"></div>
                </div>
            </div>

            <p style="margin-top: 18px;">
                <strong>Current Leader:</strong>
                <span class="status-badge status-ready">Student A</span>
            </p>

        </div>

    </section>

</main>

</body>
</html>