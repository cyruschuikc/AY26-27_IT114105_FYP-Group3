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

    <title>Student Dashboard | TeamBox</title>

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

        /* =========================
           Top Navigation Bar
        ========================= */

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
            flex-direction: row;
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

        /* =========================
           Navigation text links
        ========================= */

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

        .nav-link:active {
            color: #ffffff;
        }

        /* Current page */
        .nav-link.active {
            color: white;
            font-weight: bold;
            border-bottom: 2px solid white;
        }

        /* Log out */
        .logout-link {
            color: #ff8888;
        }

        .logout-link:hover {
            color: #ff4444;
        }

        /* =========================
           Main Content
        ========================= */

        .main-content {
            width: 90%;
            max-width: 1250px;
            margin: 40px auto;
        }

        .welcome-section {
            padding: 30px;
            margin-bottom: 30px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h1 {
            margin-bottom: 10px;
            font-size: 35px;
        }

        .welcome-section p {
            color: #666;
            font-size: 17px;
            line-height: 1.6;
        }

        /* =========================
           Dashboard Cards
        ========================= */

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            min-height: 155px;
            padding: 25px;

            background: white;
            border-left: 5px solid black;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);

            transition:
                    transform 0.3s,
                    box-shadow 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card h2 {
            margin-bottom: 18px;
            color: #555;
            font-size: 17px;
        }

        .dashboard-card .number {
            color: black;
            font-size: 38px;
            font-weight: bold;
        }

        .dashboard-card .description {
            margin-top: 8px;
            color: #777;
            font-size: 14px;
        }

        /* =========================
           Lower Information Section
        ========================= */

        .information-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .content-box {
            padding: 28px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .content-box h2 {
            padding-bottom: 15px;
            margin-bottom: 20px;

            border-bottom: 2px solid #eeeeee;
            font-size: 23px;
        }

        /* Recent Tasks */

        .task-item {
            padding: 16px 0;
            border-bottom: 1px solid #eeeeee;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .task-item:last-of-type {
            border-bottom: none;
        }

        .task-information h3 {
            margin-bottom: 6px;
            font-size: 17px;
        }

        .task-information p {
            color: #777;
            font-size: 14px;
        }

        .task-status {
            padding: 7px 12px;
            border-radius: 20px;

            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .in-progress {
            background: #fff1c2;
            color: #765900;
        }

        .completed {
            background: #d9f5df;
            color: #176b2c;
        }

        .pending {
            background: #eeeeee;
            color: #555;
        }

        /* Team Progress */

        .progress-container {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #eeeeee;
        }

        .progress-information {
            margin-bottom: 12px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .progress-information strong {
            font-size: 17px;
        }

        .progress-information span {
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            height: 15px;

            background: #dddddd;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-value {
            width: 50%;
            height: 100%;

            background: black;
            border-radius: 10px;
        }

        .progress-description {
            margin-top: 10px;
            color: #777;
            font-size: 14px;
        }

        /* =========================
           Quick Access (text links)
        ========================= */

        .quick-links {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .text-link {
            color: black;
            font-size: 16px;
            text-decoration: underline;

            transition: color 0.3s;
        }

        .text-link:hover {
            color: #555555;
        }

        .text-link:active {
            color: #000000;
        }

        .quick-note {
            margin-top: 16px;
            color: #777;
            font-size: 13px;
            line-height: 1.5;
        }

        /* =========================
           Responsive Design
        ========================= */

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .information-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                width: 94%;
                margin: 25px auto;
            }

            .welcome-section h1 {
                font-size: 28px;
            }

            .task-item {
                align-items: flex-start;
                flex-direction: column;
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

    <!-- Navigation text links -->

    <div class="function-menu">

        <a href="student_index.php" class="nav-link active">
            Dashboard
        </a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>
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

<!-- Main Dashboard Content -->

<main class="main-content">

    <section class="welcome-section">

        <h1>Welcome, Student</h1>

        <p>
            This is your TeamBox student dashboard. You can view
            your current project, assigned tasks, team progress,
            shared files and recent notifications.
        </p>

    </section>

    <section class="dashboard-grid">

        <div class="dashboard-card">
            <h2>Active Projects</h2>
            <p class="number">1</p>
            <p class="description">Project currently in progress</p>
        </div>

        <div class="dashboard-card">
            <h2>Assigned Tasks</h2>
            <p class="number">4</p>
            <p class="description">Tasks assigned to you</p>
        </div>

        <div class="dashboard-card">
            <h2>Completed Tasks</h2>
            <p class="number">2</p>
            <p class="description">Tasks completed by you</p>
        </div>

        <div class="dashboard-card">
            <h2>Notifications</h2>
            <p class="number">3</p>
            <p class="description">Unread project notifications</p>
        </div>

    </section>

    <section class="information-grid">

        <div class="content-box">

            <h2>Recent Tasks</h2>

            <div class="task-item">
                <div class="task-information">
                    <h3>Complete Project Proposal</h3>
                    <p>Deadline: 30 September 2026</p>
                </div>

                <span class="task-status in-progress">
                    In Progress
                </span>
            </div>

            <div class="task-item">
                <div class="task-information">
                    <h3>Create System Interface</h3>
                    <p>Deadline: 10 October 2026</p>
                </div>

                <span class="task-status pending">
                    Pending
                </span>
            </div>

            <div class="task-item">
                <div class="task-information">
                    <h3>Prepare Requirement Analysis</h3>
                    <p>Completed task</p>
                </div>

                <span class="task-status completed">
                    Completed
                </span>
            </div>

            <div class="progress-container">

                <div class="progress-information">
                    <strong>Team Progress</strong>
                    <span>50%</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-value"></div>
                </div>

                <p class="progress-description">
                    2 out of 4 project tasks have been completed.
                </p>

            </div>

        </div>

        <!-- Quick Access (text links) -->

        <div class="content-box">

            <h2>Quick Access</h2>

            <div class="quick-links">

                <a href="my_project.php" class="text-link">
                    View My Project
                </a>

                <a href="student_task.php" class="text-link">
                    View My Tasks
                </a>

                <a href="my_team.php" class="text-link">
                    View My Team
                </a>

                <a href="student_files.php" class="text-link">
                    Open Shared Files
                </a>

                <a href="student_chat.php" class="text-link">
                    Open Group Chat
                </a>

            </div>

            <p class="quick-note">
                Click any link to open the matching page.
            </p>

        </div>

    </section>

</main>

</body>
</html>