<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
    >

    <title>TeamBox Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f5f5;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 1000px;
            height: 550px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);

            display: flex;
        }

        /* Logo section */
        .logo-section {
            width: 50%;
            background: black;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-section img {
            width: 85%;
            max-width: 450px;
            height: auto;
            display: block;
        }

        /* Login selection section */
        .login-section {
            width: 50%;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;

            gap: 30px;
        }

        .login-section h1 {
            font-size: 42px;
            color: #222;
            text-align: center;
        }

        .login-section p {
            color: #666;
            font-size: 18px;
            margin-bottom: 20px;
        }

        /* Link styled as a button */
        .btn {
            width: 280px;
            height: 60px;

            background: white;
            color: black;

            border: 2px solid black;
            border-radius: 10px;

            font-size: 20px;
            text-decoration: none;

            display: flex;
            justify-content: center;
            align-items: center;

            cursor: pointer;

            transition:
                    background-color 0.3s,
                    color 0.3s,
                    transform 0.15s;
        }

        /* Mouse moves over the link */
        .btn:hover {
            background: black;
            color: white;
        }

        /* User is pressing the link */
        .btn:active {
            background: #333;
            color: white;
            transform: scale(0.97);
        }

        /* Keyboard focus */
        .btn:focus-visible {
            background: black;
            color: white;
            outline: 3px solid #999;
            outline-offset: 3px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- Logo section -->
    <div class="logo-section">
        <img src="TeamBox_logo_black.png" alt="TeamBox Logo">
    </div>

    <!-- Login selection section -->
    <div class="login-section">

        <h1>Welcome to TeamBox</h1>

        <p>Choose your login type</p>

        <!-- Jump to student login page -->
        <a href="student_login.php" class="btn">
            Login as Student
        </a>

        <!-- Jump to teacher login page -->
        <a href="teacher_login.php" class="btn">
            Login as Teacher
        </a>

    </div>

</div>

</body>
</html>