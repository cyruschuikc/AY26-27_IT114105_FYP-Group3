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

    <title>Student Profile | TeamBox</title>

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

        /* Profile Header */

        .profile-header {
            padding: 30px;
            margin-bottom: 25px;

            background: white;
            border-left: 6px solid black;
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);

            display: flex;
            align-items: center;
            gap: 25px;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            flex-shrink: 0;

            background: black;
            color: white;
            border-radius: 50%;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 35px;
            font-weight: bold;
        }

        .profile-summary {
            flex: 1;
        }

        .profile-summary h2 {
            margin-bottom: 8px;
            font-size: 28px;
        }

        .profile-summary p {
            margin-bottom: 6px;
            color: #666;
            font-size: 15px;
        }

        .account-status {
            display: inline-block;
            padding: 7px 13px;
            margin-top: 7px;

            background: #d9f5df;
            color: #176b2c;
            border-radius: 18px;

            font-size: 13px;
            font-weight: bold;
        }

        /* Edit Profile button (kept as button) */

        .edit-profile-btn {
            min-width: 145px;
            height: 46px;
            padding: 0 15px;

            background: white;
            color: black;
            border: 2px solid black;
            border-radius: 8px;

            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .edit-profile-btn:hover {
            background: black;
            color: white;
        }

        /* Profile Statistics */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            min-height: 135px;
            padding: 23px;

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

        .summary-card h3 {
            margin-bottom: 14px;
            color: #666;
            font-size: 15px;
        }

        .summary-number {
            font-size: 35px;
            font-weight: bold;
        }

        .summary-description {
            margin-top: 7px;
            color: #777;
            font-size: 13px;
        }

        /* Content Layout */

        .information-grid {
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

        /* Profile Information */

        .profile-detail {
            padding: 14px 0;
            border-bottom: 1px solid #eeeeee;

            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
        }

        .profile-detail:last-child {
            border-bottom: none;
        }

        .profile-detail strong {
            color: #666;
            font-size: 14px;
        }

        .profile-detail span {
            color: #222;
            font-size: 15px;
        }

        /* Edit Profile Form */

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0 13px;

            background: white;
            color: #222;
            border: 2px solid #cccccc;
            border-radius: 8px;

            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .form-group input,
        .form-group select {
            height: 47px;
        }

        .form-group textarea {
            min-height: 100px;
            padding-top: 12px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: black;
        }

        .readonly-input {
            background: #eeeeee !important;
            color: #777 !important;
            cursor: not-allowed;
        }

        /* Form Buttons (kept as buttons) */

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .form-btn {
            min-width: 130px;
            height: 46px;
            padding: 0 16px;

            background: white;
            color: black;
            border: 2px solid black;
            border-radius: 8px;

            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-btn:hover {
            background: black;
            color: white;
        }

        .save-btn {
            background: black;
            color: white;
        }

        .save-btn:hover {
            background: #333;
        }

        /* Password Section */

        .password-information {
            padding: 16px;
            margin-bottom: 20px;

            background: #f7f7f7;
            border-left: 5px solid black;
            border-radius: 8px;

            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Account Activity */

        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eeeeee;

            display: flex;
            gap: 14px;
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

        .activity-content h3 {
            margin-bottom: 5px;
            font-size: 15px;
        }

        .activity-content p {
            color: #777;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Information Notice */

        .profile-note {
            padding: 18px;

            background: white;
            border-left: 5px solid black;
            border-radius: 8px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);

            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Responsive Design */

        @media (max-width: 950px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .information-grid {
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

            .profile-header {
                align-items: center;
                flex-direction: column;
                text-align: center;
            }

            .edit-profile-btn {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .profile-detail {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-btn {
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
        <a href="student_notifications.php" class="nav-link">
            Notifications
        </a>

        <a href="student_profile.php" class="nav-link active">
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

        <h1>Student Profile</h1>

        <p>
            View and manage your personal information, academic details,
            account security and TeamBox activities.
        </p>

    </section>

    <!-- Profile Header -->

    <section class="profile-header">

        <div class="profile-avatar">
            CH
        </div>

        <div class="profile-summary">

            <h2>ABC</h2>

            <p>Student ID: 250000001</p>

            <p>Higher Diploma in Software Engineering</p>

            <span class="account-status">
                Active Account
            </span>

        </div>

        <button type="button" class="edit-profile-btn">
            Edit Profile
        </button>

    </section>

    <!-- Profile Summary -->

    <section class="summary-grid">

        <div class="summary-card">
            <h3>Active Projects</h3>
            <p class="summary-number">1</p>
            <p class="summary-description">Current project participation</p>
        </div>

        <div class="summary-card">
            <h3>Completed Tasks</h3>
            <p class="summary-number">2</p>
            <p class="summary-description">Tasks completed by you</p>
        </div>

        <div class="summary-card">
            <h3>Team Contributions</h3>
            <p class="summary-number">25%</p>
            <p class="summary-description">Current project contribution</p>
        </div>

        <div class="summary-card">
            <h3>Files Uploaded</h3>
            <p class="summary-number">4</p>
            <p class="summary-description">Files shared with your team</p>
        </div>

    </section>

    <!-- Personal and Academic Information -->

    <section class="information-grid">

        <div class="content-box">

            <h2>Personal Information</h2>

            <div class="profile-detail">
                <strong>Full Name</strong>
                <span>ABC</span>
            </div>

            <div class="profile-detail">
                <strong>Student ID</strong>
                <span>250000001</span>
            </div>

            <div class="profile-detail">
                <strong>Email Address</strong>
                <span>250000001@example.edu.hk</span>
            </div>

            <div class="profile-detail">
                <strong>Account Role</strong>
                <span>Student</span>
            </div>

            <div class="profile-detail">
                <strong>Account Status</strong>
                <span>Active</span>
            </div>

        </div>

        <div class="content-box">

            <h2>Academic Information</h2>

            <div class="profile-detail">
                <strong>Programme</strong>
                <span>Higher Diploma in Software Engineering</span>
            </div>

            <div class="profile-detail">
                <strong>Department</strong>
                <span>Department of AI &amp; Data</span>
            </div>

            <div class="profile-detail">
                <strong>Academic Year</strong>
                <span>2026/2027</span>
            </div>

            <div class="profile-detail">
                <strong>Current Project</strong>
                <span>TeamBox Collaboration System</span>
            </div>

            <div class="profile-detail">
                <strong>Team</strong>
                <span>Team A</span>
            </div>

        </div>

    </section>

    <!-- Edit Profile -->

    <section class="content-box" style="margin-bottom: 25px;">

        <h2>Edit Profile</h2>

        <form action="student_profile_process.php" method="post">

            <div class="form-row">

                <div class="form-group">
                    <label for="studentID">Student ID</label>

                    <input
                        type="text"
                        id="studentID"
                        name="studentID"
                        value="250000001"
                        class="readonly-input"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="fullName">Full Name</label>

                    <input
                        type="text"
                        id="fullName"
                        name="fullName"
                        value="ABC"
                        required
                    >
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="250000001@example.edu.hk"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="programme">Programme</label>

                    <input
                        type="text"
                        id="programme"
                        name="programme"
                        value="Higher Diploma in Software Engineering"
                        class="readonly-input"
                        readonly
                    >
                </div>

            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>

                <textarea
                    id="bio"
                    name="bio"
                    placeholder="Write a short introduction about yourself"
                ></textarea>
            </div>

            <div class="form-actions">

                <button type="reset" class="form-btn">
                    Reset
                </button>

                <button type="submit" class="form-btn save-btn">
                    Save Changes
                </button>

            </div>

        </form>

    </section>

    <!-- Change Password -->

    <section class="content-box" style="margin-bottom: 25px;">

        <h2>Change Password</h2>

        <div class="password-information">
            For your account security, choose a strong password with at
            least 8 characters, including letters and numbers.
        </div>

        <form action="student_profile_process.php" method="post">

            <div class="form-row">

                <div class="form-group">
                    <label for="currentPassword">Current Password</label>

                    <input
                        type="password"
                        id="currentPassword"
                        name="currentPassword"
                        placeholder="Enter current password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>

                    <input
                        type="password"
                        id="newPassword"
                        name="newPassword"
                        placeholder="Enter new password"
                        required
                    >
                </div>

            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>

                <input
                    type="password"
                    id="confirmPassword"
                    name="confirmPassword"
                    placeholder="Re-enter new password"
                    required
                >
            </div>

            <div class="form-actions">

                <button type="submit" class="form-btn save-btn">
                    Update Password
                </button>

            </div>

        </form>

    </section>

    <!-- Recent Account Activity -->

    <section class="content-box" style="margin-bottom: 25px;">

        <h2>Recent Account Activity</h2>

        <div class="activity-item">
            <div class="activity-dot"></div>

            <div class="activity-content">
                <h3>Logged In</h3>
                <p>Signed in to TeamBox from a new device.</p>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-dot"></div>

            <div class="activity-content">
                <h3>Profile Updated</h3>
                <p>You updated your email address.</p>
            </div>
        </div>

        <div class="activity-item">
            <div class="activity-dot"></div>

            <div class="activity-content">
                <h3>Password Changed</h3>
                <p>Account password was successfully updated.</p>
            </div>
        </div>

    </section>

    <div class="profile-note">
        This page currently demonstrates the profile interface only.
        Saving profile changes, updating passwords and storing account
        activity will require PHP and database functions.
    </div>

</main>

</body>
</html>