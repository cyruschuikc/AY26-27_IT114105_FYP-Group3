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

    <title>My Team &amp; Project | TeamBox</title>

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

        /* Team Overview */

        .team-overview {
            padding: 30px;
            margin-bottom: 25px;

            background: white;
            border-left: 6px solid black;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .team-header {
            margin-bottom: 25px;

            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .team-title h2 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .team-title p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }

        .team-status {
            padding: 9px 15px;

            background: #fff1c2;
            color: #765900;
            border-radius: 20px;

            font-size: 14px;
            font-weight: bold;
            white-space: nowrap;
        }

        .team-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .team-info-card {
            padding: 20px;

            background: #f7f7f7;
            border: 1px solid #dddddd;
            border-radius: 10px;
        }

        .team-info-card h3 {
            margin-bottom: 10px;
            color: #777;
            font-size: 14px;
        }

        .team-info-card p {
            color: #222;
            font-size: 17px;
            font-weight: bold;
        }

        /* Project Section */

        .project-section {
            padding: 30px;
            margin-bottom: 25px;

            background: white;
            border-radius: 15px;
            border-left: 6px solid black;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .project-section h2 {
            padding-bottom: 15px;
            margin-bottom: 22px;

            border-bottom: 2px solid #eeeeee;
            font-size: 23px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-box {
            padding: 20px;

            background: #fafafa;
            border: 1px solid #eeeeee;
            border-radius: 12px;
        }

        .info-box h3 {
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .info-box p {
            font-size: 17px;
            font-weight: bold;
        }

        .project-description {
            padding-top: 20px;
            border-top: 2px solid #eeeeee;

            color: #666;
            font-size: 15px;
            line-height: 1.7;
        }

        /* Team Progress */

        .team-progress {
            padding-top: 22px;
            border-top: 2px solid #eeeeee;
        }

        .progress-heading {
            margin-bottom: 12px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .progress-heading strong {
            font-size: 18px;
        }

        .progress-heading span {
            font-size: 17px;
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            height: 16px;

            background: #dddddd;
            border-radius: 12px;
            overflow: hidden;
        }

        .progress-value {
            width: 50%;
            height: 100%;

            background: black;
            border-radius: 12px;
        }

        .progress-note {
            margin-top: 10px;
            color: #777;
            font-size: 14px;
        }

        /* Content Boxes */

        .information-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
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
            margin-bottom: 20px;

            border-bottom: 2px solid #eeeeee;
            font-size: 23px;
        }

        /* Team Members */

        .member-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .member-card {
            padding: 20px;

            background: #f8f8f8;
            border: 1px solid #dddddd;
            border-radius: 12px;

            display: flex;
            align-items: center;
            gap: 15px;

            transition:
                    transform 0.3s,
                    box-shadow 0.3s;
        }

        .member-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.1);
        }

        .member-avatar {
            width: 52px;
            height: 52px;
            flex-shrink: 0;

            background: black;
            color: white;
            border-radius: 50%;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 18px;
            font-weight: bold;
        }

        .member-information {
            flex: 1;
        }

        .member-information h3 {
            margin-bottom: 5px;
            font-size: 17px;
        }

        .member-information p {
            margin-bottom: 4px;
            color: #777;
            font-size: 13px;
        }

        .member-role {
            display: inline-block;
            padding: 5px 9px;
            margin-top: 4px;

            background: #eeeeee;
            color: #555;
            border-radius: 15px;

            font-size: 12px;
            font-weight: bold;
        }

        .leader-role {
            background: black;
            color: white;
        }

        /* Team Details */

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item h3 {
            margin-bottom: 7px;
            color: #555;
            font-size: 15px;
        }

        .detail-item p {
            color: #222;
            font-size: 16px;
            line-height: 1.5;
        }

        /* Tasks */

        .task-list {
            display: flex;
            flex-direction: column;
        }

        .task-item {
            padding: 17px 0;
            border-bottom: 1px solid #eeeeee;

            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-information {
            flex: 1;
        }

        .task-information h3 {
            margin-bottom: 6px;
            font-size: 17px;
        }

        .task-information p {
            margin-bottom: 5px;
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

        .status-completed {
            background: #d9f5df;
            color: #176b2c;
        }

        .status-progress {
            background: #fff1c2;
            color: #765900;
        }

        .status-pending {
            background: #eeeeee;
            color: #555;
        }

        /* Team Activity */

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eeeeee;

            display: flex;
            gap: 15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-dot {
            width: 13px;
            height: 13px;
            margin-top: 5px;
            flex-shrink: 0;

            background: black;
            border-radius: 50%;
        }

        .activity-information h3 {
            margin-bottom: 5px;
            font-size: 15px;
        }

        .activity-information p {
            color: #777;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Responsive Design */

        @media (max-width: 1000px) {
            .team-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .information-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
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

            .team-header {
                flex-direction: column;
            }

            .team-info-grid,
            .info-grid,
            .member-list {
                grid-template-columns: 1fr;
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

    <div class="function-menu">

        <a href="student_index.php" class="nav-link">
            Dashboard
        </a>

        <a href="student_create_group.php" class="nav-link">
            Create Group
        </a>

        <a href="my_team.php" class="nav-link active">
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

        <a href="student_notifications.php" class="nav-link">
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

    <!-- Page Heading -->

    <section class="page-heading">

        <h1>My Team &amp; Project</h1>

        <p>
            View your team information, project details, members, current
            progress, assigned tasks and recent team activities — all in
            one page.
        </p>

    </section>

    <!-- Team Overview -->

    <section class="team-overview">

        <div class="team-header">

            <div class="team-title">

                <h2>Team A</h2>

                <p>
                    Team responsible for developing the TeamBox
                    cross-platform collaboration and management system.
                </p>

            </div>

            <span class="team-status">
                In Progress
            </span>

        </div>

        <div class="team-info-grid">

            <div class="team-info-card">
                <h3>Project</h3>
                <p>TeamBox System</p>
            </div>

            <div class="team-info-card">
                <h3>Team Leader</h3>
                <p>Student A</p>
            </div>

            <div class="team-info-card">
                <h3>Team Members</h3>
                <p>4 Members</p>
            </div>

            <div class="team-info-card">
                <h3>Team Status</h3>
                <p>Executing</p>
            </div>

        </div>

        <div class="team-progress">

            <div class="progress-heading">
                <strong>Overall Team Progress</strong>
                <span>50%</span>
            </div>

            <div class="progress-bar">
                <div class="progress-value"></div>
            </div>

            <p class="progress-note">
                4 out of 8 team tasks have been completed.
            </p>

        </div>

    </section>

    <!-- Project Details -->

    <section class="project-section">

        <h2>Project Details</h2>

        <div class="info-grid">

            <div class="info-box">
                <h3>Project ID</h3>
                <p>PRJ-2026-001</p>
            </div>

            <div class="info-box">
                <h3>Course</h3>
                <p>Software Engineering Project</p>
            </div>

            <div class="info-box">
                <h3>Supervisor</h3>
                <p>Dr. Tan</p>
            </div>

            <div class="info-box">
                <h3>Start Date</h3>
                <p>01 September 2026</p>
            </div>

            <div class="info-box">
                <h3>Deadline</h3>
                <p>30 November 2026</p>
            </div>

            <div class="info-box">
                <h3>Members</h3>
                <p>4 Students</p>
            </div>

        </div>

        <p class="project-description">
            The TeamBox project is currently at 50% completion.
            The requirement analysis and project proposal have been
            finished. The next phase focuses on system interface
            design, database structure, and core feature development.
        </p>

    </section>

    <!-- Members + Team Details -->

    <section class="information-grid">

        <div class="content-box">

            <h2>Team Members</h2>

            <div class="member-list">

                <div class="member-card">
                    <div class="member-avatar">SA</div>

                    <div class="member-information">
                        <h3>Student A</h3>
                        <p>Student ID: 250000001</p>
                        <span class="member-role leader-role">Team Leader</span>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-avatar">SB</div>

                    <div class="member-information">
                        <h3>Student B</h3>
                        <p>Student ID: 250000002</p>
                        <span class="member-role">Team Member</span>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-avatar">SC</div>

                    <div class="member-information">
                        <h3>Student C</h3>
                        <p>Student ID: 250000003</p>
                        <span class="member-role">Team Member</span>
                    </div>
                </div>

                <div class="member-card">
                    <div class="member-avatar">SD</div>

                    <div class="member-information">
                        <h3>Student D</h3>
                        <p>Student ID: 250000004</p>
                        <span class="member-role">Team Member</span>
                    </div>
                </div>

            </div>

        </div>

        <div class="content-box">

            <h2>Team Details</h2>

            <div class="detail-item">
                <h3>Team Created</h3>
                <p>15 September 2026</p>
            </div>

            <div class="detail-item">
                <h3>Current Project Stage</h3>
                <p>System Development</p>
            </div>

            <div class="detail-item">
                <h3>Team Formation Status</h3>
                <p>Locked</p>
            </div>

            <div class="detail-item">
                <h3>Leader Election</h3>
                <p>Completed</p>
            </div>

            <div class="detail-item">
                <h3>Next Meeting</h3>
                <p>30 September 2026</p>
            </div>

        </div>

    </section>

    <!-- Tasks + Activity -->

    <section class="information-grid">

        <div class="content-box">

            <h2>My Assigned Tasks</h2>

            <div class="task-list">

                <div class="task-item">
                    <div class="task-information">
                        <h3>Create Student Login Page</h3>
                        <p>Deadline: 30 September 2026</p>
                        <p>Assigned by: Student A</p>
                    </div>

                    <span class="task-status status-completed">
                        Completed
                    </span>
                </div>

                <div class="task-item">
                    <div class="task-information">
                        <h3>Develop My Team Page</h3>
                        <p>Deadline: 10 October 2026</p>
                        <p>Assigned by: Student A</p>
                    </div>

                    <span class="task-status status-progress">
                        In Progress
                    </span>
                </div>

                <div class="task-item">
                    <div class="task-information">
                        <h3>Prepare Testing Document</h3>
                        <p>Deadline: 20 October 2026</p>
                        <p>Assigned by: Student A</p>
                    </div>

                    <span class="task-status status-pending">
                        Pending
                    </span>
                </div>

            </div>

        </div>

        <div class="content-box">

            <h2>Recent Activity</h2>

            <div class="activity-item">
                <div class="activity-dot"></div>

                <div class="activity-information">
                    <h3>Task Completed</h3>
                    <p>Student A completed the student login page.</p>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot"></div>

                <div class="activity-information">
                    <h3>New Task Assigned</h3>
                    <p>Student B was assigned the My Team page.</p>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot"></div>

                <div class="activity-information">
                    <h3>Team Meeting Scheduled</h3>
                    <p>Next meeting on 30 September 2026.</p>
                </div>
            </div>

        </div>

    </section>

</main>

</body>
</html>