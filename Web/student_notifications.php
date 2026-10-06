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

    <title>Notifications | TeamBox</title>

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

        /* Navigation text links */

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
            max-width: 1200px;
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

        /* Notification Summary */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            min-height: 140px;
            padding: 24px;

            background: white;
            border-left: 5px solid black;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);

            transition:
                    transform 0.3s,
                    box-shadow 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .summary-card h2 {
            margin-bottom: 14px;
            color: #666;
            font-size: 16px;
        }

        .summary-number {
            font-size: 38px;
            font-weight: bold;
        }

        .summary-description {
            margin-top: 7px;
            color: #777;
            font-size: 13px;
        }

        /* Notification Section */

        .notification-section {
            padding: 28px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .section-header h2 {
            font-size: 24px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-select {
            min-width: 165px;
            height: 43px;
            padding: 0 12px;

            background: white;
            color: black;
            border: 2px solid #cccccc;
            border-radius: 8px;

            font-size: 14px;
            outline: none;
            cursor: pointer;
        }

        .filter-select:focus {
            border-color: black;
        }

        .mark-read-btn {
            min-width: 155px;
            height: 43px;
            padding: 0 14px;

            background: white;
            color: black;
            border: 2px solid black;
            border-radius: 8px;

            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .mark-read-btn:hover {
            background: black;
            color: white;
        }

        /* Notification Tabs */

        .notification-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        .tab-btn {
            min-width: 100px;
            height: 40px;
            padding: 0 14px;

            background: white;
            color: black;
            border: 1px solid #bbbbbb;
            border-radius: 20px;

            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .tab-btn:hover,
        .tab-btn.active {
            background: black;
            color: white;
            border-color: black;
        }

        /* Notification List */

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .notification-item {
            padding: 20px;

            background: #fafafa;
            border: 1px solid #dddddd;
            border-radius: 12px;

            display: grid;
            grid-template-columns: 54px 1fr auto;
            align-items: center;
            gap: 17px;

            transition:
                    transform 0.3s,
                    box-shadow 0.3s;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .notification-item.unread {
            background: white;
            border-left: 6px solid black;
        }

        .notification-icon {
            width: 50px;
            height: 50px;

            background: black;
            color: white;
            border-radius: 50%;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 13px;
            font-weight: bold;
        }

        .read-icon {
            background: #777777;
        }

        .notification-content h3 {
            margin-bottom: 7px;
            font-size: 17px;
        }

        .notification-content p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .notification-time {
            display: block;
            margin-top: 8px;

            color: #888;
            font-size: 12px;
        }

        .notification-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 9px;
        }

        .unread-dot {
            width: 11px;
            height: 11px;

            background: black;
            border-radius: 50%;
        }

        .notification-btn {
            min-width: 105px;
            height: 38px;
            padding: 0 12px;

            background: white;
            color: black;
            border: 2px solid black;
            border-radius: 7px;

            font-size: 13px;
            cursor: pointer;
            transition: 0.3s;
        }

        .notification-btn:hover {
            background: black;
            color: white;
        }

        .notification-type {
            display: inline-block;
            padding: 5px 9px;
            margin-top: 9px;

            background: #eeeeee;
            color: #555;
            border-radius: 15px;

            font-size: 12px;
            font-weight: bold;
        }

        .type-task {
            background: #fff1c2;
            color: #765900;
        }

        .type-team {
            background: #e5f0ff;
            color: #245889;
        }

        .type-file {
            background: #e9e1ff;
            color: #553192;
        }

        .type-project {
            background: #d9f5df;
            color: #176b2c;
        }

        .type-alert {
            background: #ffe1e1;
            color: #a21b1b;
        }

        /* Information Note */

        .notification-note {
            padding: 18px;
            margin-top: 22px;

            background: #f7f7f7;
            border-left: 5px solid black;
            border-radius: 8px;

            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Responsive Design */

        @media (max-width: 950px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .filter-select,
            .mark-read-btn {
                flex: 1;
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

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .filter-select,
            .mark-read-btn {
                width: 100%;
            }

            .notification-item {
                grid-template-columns: 48px 1fr;
                align-items: flex-start;
            }

            .notification-icon {
                width: 45px;
                height: 45px;
            }

            .notification-actions {
                grid-column: 1 / -1;
                align-items: stretch;
            }

            .notification-btn {
                width: 100%;
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

        <a href="student_index.php" class="nav-link">
            Dashboard
        </a>
        <a href="student_create_group.php" class="nav-link">Create Group</a>


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
        <a href="student_contribution.php" class="nav-link">
            Contribution
        </a>
        <a href="student_notifications.php" class="nav-link active">
            Notifications
        </a>

        <a href="student_profile.php" class="nav-link">
            Profile
        </a>

        <a href="logout.php" class="nav-link logout-link">
            Log Out
        </a>

    </div>

</nav>

<!-- Main Content -->

<main class="main-content">

    <section class="page-heading">
        <h1>Notifications</h1>
        <p>
            View your task reminders, project updates, team activities,
            file notifications and important deadline alerts.
        </p>
    </section>

    <!-- Notification Summary -->

    <section class="summary-grid">

        <div class="summary-card">
            <h2>All Notifications</h2>
            <p class="summary-number">12</p>
            <p class="summary-description">Total notifications received</p>
        </div>

        <div class="summary-card">
            <h2>Unread</h2>
            <p class="summary-number">4</p>
            <p class="summary-description">Notifications not yet read</p>
        </div>

        <div class="summary-card">
            <h2>Task Alerts</h2>
            <p class="summary-number">3</p>
            <p class="summary-description">Task assignments and reminders</p>
        </div>

        <div class="summary-card">
            <h2>Deadline Alerts</h2>
            <p class="summary-number">2</p>
            <p class="summary-description">Upcoming project deadlines</p>
        </div>

    </section>

    <!-- Notification List -->

    <section class="notification-section">

        <div class="section-header">

            <h2>Recent Notifications</h2>

            <div class="header-actions">

                <select class="filter-select" aria-label="Filter notifications">
                    <option value="all">All Notifications</option>
                    <option value="unread">Unread Only</option>
                    <option value="tasks">Task Notifications</option>
                    <option value="project">Project Updates</option>
                    <option value="team">Team Activities</option>
                    <option value="files">File Updates</option>
                </select>

                <button type="button" class="mark-read-btn">
                    Mark All as Read
                </button>

            </div>

        </div>

        <div class="notification-tabs">

            <button type="button" class="tab-btn active">All</button>
            <button type="button" class="tab-btn">Tasks</button>
            <button type="button" class="tab-btn">Projects</button>
            <button type="button" class="tab-btn">Team</button>
            <button type="button" class="tab-btn">Files</button>

        </div>

        <div class="notification-list">

            <!-- Notification 1 -->

            <article class="notification-item unread">

                <div class="notification-icon">TASK</div>

                <div class="notification-content">

                    <h3>New Task Assigned</h3>

                    <p>
                        Student A assigned the task "Develop Student
                        Dashboard" to you.
                    </p>

                    <span class="notification-type type-task">Task</span>

                    <span class="notification-time">Today at 09:15</span>

                </div>

                <div class="notification-actions">

                    <span class="unread-dot" title="Unread"></span>

                    <button type="button" class="notification-btn">
                        View Task
                    </button>

                </div>

            </article>

            <!-- Notification 2 -->

            <article class="notification-item unread">

                <div class="notification-icon">ALERT</div>

                <div class="notification-content">

                    <h3>Task Deadline Approaching</h3>

                    <p>
                        The task "Create My Team Page" is due on
                        15 October 2026.
                    </p>

                    <span class="notification-type type-alert">
                        Deadline Alert
                    </span>

                    <span class="notification-time">Today at 08:30</span>

                </div>

                <div class="notification-actions">

                    <span class="unread-dot" title="Unread"></span>

                    <button type="button" class="notification-btn">
                        View Details
                    </button>

                </div>

            </article>

            <!-- Notification 3 -->

            <article class="notification-item unread">

                <div class="notification-icon">FILE</div>

                <div class="notification-content">

                    <h3>New File Uploaded</h3>

                    <p>
                        Student B uploaded "Project_Proposal.pdf"
                        to the shared project workspace.
                    </p>

                    <span class="notification-type type-file">File</span>

                    <span class="notification-time">
                        Yesterday at 16:40
                    </span>

                </div>

                <div class="notification-actions">

                    <span class="unread-dot" title="Unread"></span>

                    <button type="button" class="notification-btn">
                        View File
                    </button>

                </div>

            </article>

            <!-- Notification 4 -->

            <article class="notification-item unread">

                <div class="notification-icon">CHAT</div>

                <div class="notification-content">

                    <h3>New Group Message</h3>

                    <p>
                        A new meeting reminder was posted in the
                        Team A group chat.
                    </p>

                    <span class="notification-type type-team">Team</span>

                    <span class="notification-time">
                        Yesterday at 15:20
                    </span>

                </div>

                <div class="notification-actions">

                    <span class="unread-dot" title="Unread"></span>

                    <button type="button" class="notification-btn">
                        Open Chat
                    </button>

                </div>

            </article>

            <!-- Notification 5 -->

            <article class="notification-item">

                <div class="notification-icon read-icon">PROJ</div>

                <div class="notification-content">

                    <h3>Project Progress Updated</h3>

                    <p>
                        The overall progress of the TeamBox project
                        has been updated to 50%.
                    </p>

                    <span class="notification-type type-project">
                        Project
                    </span>

                    <span class="notification-time">
                        22 September 2026 at 14:10
                    </span>

                </div>

                <div class="notification-actions">

                    <button type="button" class="notification-btn">
                        View Project
                    </button>

                </div>

            </article>

            <!-- Notification 6 -->

            <article class="notification-item">

                <div class="notification-icon read-icon">TEAM</div>

                <div class="notification-content">

                    <h3>Team Member Activity</h3>

                    <p>
                        Student C completed the database design task.
                    </p>

                    <span class="notification-type type-team">Team</span>

                    <span class="notification-time">
                        21 September 2026 at 11:45
                    </span>

                </div>

                <div class="notification-actions">

                    <button type="button" class="notification-btn">
                        View Team
                    </button>

                </div>

            </article>

            <!-- Notification 7 -->

            <article class="notification-item">

                <div class="notification-icon read-icon">TASK</div>

                <div class="notification-content">

                    <h3>Task Completed Successfully</h3>

                    <p>
                        Your task "Create Student Login Page" has been
                        marked as completed.
                    </p>

                    <span class="notification-type type-task">Task</span>

                    <span class="notification-time">
                        20 September 2026 at 17:30
                    </span>

                </div>

                <div class="notification-actions">

                    <button type="button" class="notification-btn">
                        View Task
                    </button>

                </div>

            </article>

        </div>

        <div class="notification-note">
            This page currently demonstrates the notification interface only.
            Filtering, marking notifications as read and displaying live
            notifications will require PHP, database and real-time update
            functions.
        </div>

    </section>

</main>

</body>
</html>
