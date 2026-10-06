<?php $active = 'profile'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | TeamBox</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>

<?php include 'teacher_nav.php'; ?>

<main class="main">
    <div class="content">

        <h1>Profile</h1>
        <p class="subtitle">Your instructor account information</p>

        <h2>Account</h2>
        <div class="card">
            <table>
                <tr><td style="width:40%; color:#6b6f78;">Full Name</td><td>Esther Yuen</td></tr>
                <tr><td style="color:#6b6f78;">Email</td><td>wyyuen@vtc.edu.hk</td></tr>
                <tr><td style="color:#6b6f78;">Role</td><td>Teacher</td></tr>
                <tr><td style="color:#6b6f78;">Department</td><td>Information Technology</td></tr>
                <tr><td style="color:#6b6f78;">Title</td><td>Senior Lecturer</td></tr>
                <tr><td style="color:#6b6f78;">Hire Date</td><td>01 Sep 2022</td></tr>
            </table>
        </div>

        <h2>Edit Profile</h2>
        <div class="card">
            <div class="announcement-block stack">

                <div class="field" style="margin-bottom: 16px;">
                    <label for="profileName">Full Name</label>
                    <input type="text" id="profileName" name="profileName" value="Esther Yuen">
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="profileEmail">Email</label>
                    <input type="email" id="profileEmail" name="profileEmail" value="wyyuen@vtc.edu.hk">
                </div>

                <button type="button" class="btn-primary">Save Changes</button>

            </div>
        </div>

        <h2>Change Password</h2>
        <div class="card">
            <div class="announcement-block stack">

                <div class="field" style="margin-bottom: 16px;">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword">
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword">
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword">
                </div>

                <button type="button" class="btn-primary">Update Password</button>

            </div>
        </div>

    </div>
</main>

</body>
</html>