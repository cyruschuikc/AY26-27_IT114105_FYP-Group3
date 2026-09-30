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

    <title>My Tasks | TeamBox</title>

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

        /* =========================
           Task Summary Cards
        ========================= */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            min-height: 145px;
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
            margin-bottom: 15px;
            color: #666;
            font-size: 16px;
        }

        .summary-number {
            color: black;
            font-size: 38px;
            font-weight: bold;
        }

        .summary-description {
            margin-top: 7px;
            color: #777;
            font-size: 13px;
        }

        /* =========================
           Toolbar
        ========================= */

        .task-section {
            padding: 28px;

            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .section-heading {
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 2px solid #eeeeee;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .section-heading h2 {
            font-size: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-input,
        .filter-select {
            height: 44px;
            padding: 0 13px;

            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;

            font-size: 14px;
            outline: none;
        }

        .search-input {
            width: 220px;
        }

        .filter-select {
            min-width: 145px;
            cursor: pointer;
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: black;
        }

        /* =========================
           Task Cards
        ========================= */

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .task-card {
            padding: 22px;

            background: #fafafa;
            border: 1px solid #dddddd;
            border-left: 5px solid black;
            border-radius: 12px;

            transition:
                    transform 0.3s,
                    box-shadow 0.3s;
        }

        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.1);
        }

        .task-top {
            margin-bottom: 16px;

            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .task-title {
            flex: 1;
        }

        .task-title h3 {
            margin-bottom: 7px;
            font-size: 20px;
        }

        .task-title p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .task-status {
            padding: 7px 12px;
            border-radius: 20px;

            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-pending {
            background: #eeeeee;
            color: #555;
        }

        .status-progress {
            background: #fff1c2;
            color: #765900;
        }

        .status-completed {
            background: #d9f5df;
            color: #176b2c;
        }

        .task-details {
            padding-top: 16px;
            border-top: 1px solid #e4e4e4;

            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .detail-box h4 {
            margin-bottom: 6px;
            color: #777;
            font-size: 13px;
        }

        .detail-box p {
            color: #222;
            font-size: 14px;
            font-weight: bold;
        }

        /* Priority Labels */

        .priority {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;

            font-size: 12px;
            font-weight: bold;
        }

        .priority-high {
            background: #ffe1e1;
            color: #a21b1b;
        }

        .priority-medium {
            background: #fff1c2;
            color: #765900;
        }

        .priority-low {
            background: #e5f0ff;
            color: #245889;
        }

        /* =========================
           Task Actions (text links)
        ========================= */

        .task-actions {
            margin-top: 18px;

            display: flex;
            justify-content: flex-end;
            gap: 18px;
        }

        .action-link {
            color: black;
            font-size: 15px;
            text-decoration: underline;

            transition: color 0.3s;
        }

        .action-link:hover {
            color: #555555;
        }

        .action-link:active {
            color: #000000;
        }

        .action-link.disabled {
            color: #aaaaaa;
            cursor: not-allowed;
            text-decoration: none;
        }

        /* =========================
           Information Box
        ========================= */

        .task-note {
            padding: 18px;
            margin-top: 22px;

            background: #f7f7f7;
            border-left: 5px solid black;
            border-radius: 8px;

            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* =========================
           Responsive Design
        ========================= */

        @media (max-width: 1000px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .toolbar {
                width: 100%;
            }

            .search-input {
                flex: 1;
                width: auto;
            }

            .task-details {
                grid-template-columns: repeat(2, 1fr);
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

            .toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .search-input,
            .filter-select {
                width: 100%;
            }

            .task-top {
                flex-direction: column;
            }

            .task-details {
                grid-template-columns: 1fr;
            }

            .task-actions {
                flex-direction: column;
                align-items: flex-start;
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
        <a href="my_project.php" class="nav-link">
            My Project
        </a>

        <a href="my_team.php" class="nav-link">
            My Team
        </a>

        <a href="student_task.php" class="nav-link active">
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

        <h1>My Tasks</h1>

        <p>
            View your assigned tasks, deadlines, priorities and completion
            status. Update your task progress after completing your work.
        </p>

    </section>

    <!-- Task Summary -->

    <section class="summary-grid">

        <div class="summary-card">
            <h2>Total Assigned Tasks</h2>
            <p class="summary-number">6</p>
            <p class="summary-description">All tasks assigned to you</p>
        </div>

        <div class="summary-card">
            <h2>Pending Tasks</h2>
            <p class="summary-number">2</p>
            <p class="summary-description">Tasks waiting to be started</p>
        </div>

        <div class="summary-card">
            <h2>In Progress</h2>
            <p class="summary-number">2</p>
            <p class="summary-description">Tasks currently being completed</p>
        </div>

        <div class="summary-card">
            <h2>Completed Tasks</h2>
            <p class="summary-number">2</p>
            <p class="summary-description">Tasks successfully completed</p>
        </div>

    </section>

    <!-- Task List -->

    <section class="task-section">

        <div class="section-heading">

            <h2>Assigned Task List</h2>

            <div class="toolbar">

                <input
                    type="search"
                    class="search-input"
                    placeholder="Search tasks"
                    aria-label="Search tasks"
                >

                <select
                    class="filter-select"
                    aria-label="Filter task status"
                >
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>

            </div>

        </div>

        <div class="task-list">

            <!-- Task 1 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Develop Student Dashboard</h3>
                        <p>
                            Create the student dashboard interface with project,
                            task, team progress and notification information.
                        </p>
                    </div>

                    <span class="task-status status-progress">
                        In Progress
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Student A</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>23 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Deadline</h4>
                        <p>10 October 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-high">High</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <a href="#" class="action-link">
                        Confirm Complete
                    </a>

                </div>

            </article>

            <!-- Task 2 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Create My Team Page</h3>
                        <p>
                            Develop a page showing the team leader, members,
                            team progress, assigned tasks and recent activities.
                        </p>
                    </div>

                    <span class="task-status status-progress">
                        In Progress
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Student A</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>25 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Deadline</h4>
                        <p>15 October 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-medium">Medium</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <a href="#" class="action-link">
                        Confirm Complete
                    </a>

                </div>

            </article>

            <!-- Task 3 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Prepare System Testing Document</h3>
                        <p>
                            Prepare test cases for the login, project management
                            and task management functions.
                        </p>
                    </div>

                    <span class="task-status status-pending">
                        Pending
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Student A</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>26 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Deadline</h4>
                        <p>20 October 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-medium">Medium</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <a href="#" class="action-link">
                        Start Task
                    </a>

                </div>

            </article>

            <!-- Task 4 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Design Project Database</h3>
                        <p>
                            Create the initial database structure for users,
                            projects, teams, tasks, files and activities.
                        </p>
                    </div>

                    <span class="task-status status-pending">
                        Pending
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Student A</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>27 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Deadline</h4>
                        <p>25 October 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-low">Low</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <a href="#" class="action-link">
                        Start Task
                    </a>

                </div>

            </article>

            <!-- Task 5 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Create Student Login Page</h3>
                        <p>
                            Design the Student ID, password and human
                            verification login interface.
                        </p>
                    </div>

                    <span class="task-status status-completed">
                        Completed
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Student A</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>15 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Completed Date</h4>
                        <p>22 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-high">High</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <span class="action-link disabled">
                        Task Completed
                    </span>

                </div>

            </article>

            <!-- Task 6 -->

            <article class="task-card">

                <div class="task-top">

                    <div class="task-title">
                        <h3>Prepare Requirement Analysis</h3>
                        <p>
                            Document the project background, identified
                            problems, user requirements and system functions.
                        </p>
                    </div>

                    <span class="task-status status-completed">
                        Completed
                    </span>

                </div>

                <div class="task-details">

                    <div class="detail-box">
                        <h4>Assigned By</h4>
                        <p>Project Supervisor</p>
                    </div>

                    <div class="detail-box">
                        <h4>Assigned Date</h4>
                        <p>5 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Completed Date</h4>
                        <p>18 September 2026</p>
                    </div>

                    <div class="detail-box">
                        <h4>Priority</h4>
                        <span class="priority priority-medium">Medium</span>
                    </div>

                </div>

                <div class="task-actions">

                    <a href="#" class="action-link">
                        View Details
                    </a>

                    <span class="action-link disabled">
                        Task Completed
                    </span>

                </div>

            </article>

        </div>

        <div class="task-note">
            The links on this page currently demonstrate the user
            interface only. Task status updates will require PHP and
            database functions in the final system.
        </div>

    </section>

</main>

</body>
</html>
