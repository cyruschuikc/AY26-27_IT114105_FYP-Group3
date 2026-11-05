<?php
session_start();

// 已登入 → 直接跳轉
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'teacher') {
        header('Location: teacher_index.php');
        exit;
    } elseif ($_SESSION['role'] === 'student') {
        header('Location: student_index.php');
        exit;
    }
}

$error = '';

if (isset($_POST['userId'])) {

    require_once 'db.php';
    /** @var mysqli $conn */

    $userId   = trim($_POST['userId']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($userId === '' || $password === '') {
        $error = 'Please enter both User ID and password.';
    } else {

        $firstChar = strtoupper(substr($userId, 0, 1));

        $stmt = null;

        if ($firstChar === 'T') {

            // 老师：用 Teachers.teacher_code 登录
            $sql = "
                SELECT u.id, u.username, u.email, u.password_hash, u.role
                FROM `Teachers` t
                INNER JOIN `Users` u ON u.id = t.user_id
                WHERE t.teacher_code = ?
                LIMIT 1
            ";

            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $userId);
            }

        } elseif ($firstChar === 'S') {

            // 学生：用 Students.student_number 登录
            $sql = "
                SELECT u.id, u.username, u.email, u.password_hash, u.role
                FROM `Students` s
                INNER JOIN `Users` u ON u.id = s.user_id
                WHERE s.student_number = ?
                LIMIT 1
            ";

            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $userId);
            }

        } else {
            $error = 'Invalid User ID. It should start with "T" (teacher) or "S" (student).';
        }

        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($rs) <= 0) {
                $error = 'Invalid User ID or password.';
            } else {
                $rc = mysqli_fetch_assoc($rs);

                // UI 阶段：明文比较
                if ($password !== $rc['password_hash']) {
                    $error = 'Invalid User ID or password.';
                } else {

                    // 设置 session
                    $_SESSION['userID']   = $rc['id'];
                    $_SESSION['username'] = $rc['username'];
                    $_SESSION['email']    = $rc['email'];
                    $_SESSION['role']     = $rc['role'];

                    // =========================================================
                    // 更新 last_active_at（在線狀態）
                    // =========================================================
                    $stmtActive = mysqli_prepare($conn, "UPDATE `users` SET `last_active_at` = NOW() WHERE id = ?");
                    if ($stmtActive) {
                        $activeUserID = (int) $rc['id'];
                        mysqli_stmt_bind_param($stmtActive, "i", $activeUserID);
                        mysqli_stmt_execute($stmtActive);
                        mysqli_stmt_close($stmtActive);
                    }

                    mysqli_free_result($rs);
                    mysqli_stmt_close($stmt);

                    // 跳轉
                    if ($rc['role'] === 'teacher') {
                        header('Location: teacher_index.php');
                    } else {
                        header('Location: student_index.php');
                    }
                    exit;
                }
            }

            mysqli_free_result($rs);
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TeamBox</title>
    <style>
        :root {
            --navy: #0b1220;
            --navy-soft: #13213a;
            --blue: #4f7cff;
            --blue-dark: #315fdb;
            --text: #152033;
            --muted: #697386;
            --line: #dde3ec;
            --surface: rgba(255,255,255,.94);
            --danger: #b42318;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
            color: var(--text);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            background:
                    radial-gradient(circle at 15% 15%, rgba(79,124,255,.20), transparent 34%),
                    radial-gradient(circle at 85% 85%, rgba(26,188,156,.12), transparent 30%),
                    linear-gradient(135deg, #edf2f9 0%, #f8fafc 52%, #e9eff8 100%);
        }
        .container {
            width: min(1080px, 100%);
            min-height: 620px;
            display: grid;
            grid-template-columns: 46% 54%;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid rgba(255,255,255,.9);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15,30,55,.18);
            backdrop-filter: blur(18px);
        }
        .logo-section {
            position: relative;
            min-height: 620px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 54px;
            overflow: hidden;
            background: linear-gradient(145deg, var(--navy) 0%, var(--navy-soft) 60%, #234b87 100%);
        }
        .logo-section::before,
        .logo-section::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.10);
        }
        .logo-section::before { width: 390px; height: 390px; left: -180px; top: -150px; }
        .logo-section::after { width: 300px; height: 300px; right: -150px; bottom: -105px; }
        .logo-section img {
            position: relative;
            z-index: 1;
            width: min(88%, 400px);
            height: auto;
            filter: drop-shadow(0 16px 28px rgba(0,0,0,.30));
        }
        .login-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 68px clamp(42px, 7vw, 86px);
        }

        .login-section h1 {
            margin-bottom: 10px;
            color: var(--text);
            font-size: clamp(38px, 5vw, 50px);
            line-height: 1.05;
            letter-spacing: -1.7px;
            text-align: center;
        }
        .subtitle {
            margin-bottom: 34px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
            text-align: center;
        }
        .error-message {
            margin-bottom: 22px;
            padding: 13px 15px;
            color: var(--danger);
            background: #fff1f0;
            border: 1px solid #f7c7c3;
            border-left: 4px solid var(--danger);
            border-radius: 11px;
            font-size: 14px;
            line-height: 1.5;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #344054;
            font-size: 14px;
            font-weight: 700;
        }
        .form-group input {
            width: 100%;
            height: 54px;
            padding: 0 16px;
            color: var(--text);
            background: #fbfcfe;
            border: 1px solid var(--line);
            border-radius: 12px;
            outline: none;
            font: inherit;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .form-group input::placeholder { color: #98a2b3; }
        .form-group input:hover { border-color: #b9c3d0; background: #fff; }
        .form-group input:focus {
            background: #fff;
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(79,124,255,.13);
        }
        .login-btn {
            width: 100%;
            height: 54px;
            margin-top: 7px;
            color: #fff;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            border: 0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 750;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(49,95,219,.25);
            transition: transform .18s, box-shadow .18s, filter .18s;
        }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 16px 28px rgba(49,95,219,.30); filter: brightness(1.04); }
        .login-btn:active { transform: translateY(0); }
        .login-btn:focus-visible { outline: 3px solid rgba(79,124,255,.28); outline-offset: 3px; }
        .login-hint {
            margin-top: 24px;
            padding: 14px 16px;
            color: #667085;
            background: #f7f9fc;
            border: 1px solid #e7ebf1;
            border-radius: 11px;
            font-size: 12px;
            line-height: 1.7;
        }
        .login-hint strong { color: #344054; }
        @media (max-width: 820px) {
            body { padding: 18px; }
            .container { min-height: auto; grid-template-columns: 1fr; border-radius: 22px; }
            .logo-section { min-height: 185px; padding: 30px; }
            .logo-section img { width: min(68%, 310px); }
            .login-section { padding: 44px 30px 38px; }
        }
        @media (max-width: 430px) {
            body { padding: 0; background: #fff; }
            .container { min-height: 100vh; border: 0; border-radius: 0; box-shadow: none; }
            .logo-section { min-height: 155px; }
            .login-section { justify-content: flex-start; padding: 34px 22px; }
            .login-section h1 { font-size: 36px; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="logo-section">
        <img src="logo_white.png" alt="TeamBox Logo">
    </div>

    <div class="login-section">

        <h1>Login</h1>
        <p class="subtitle">Log in with your TeamBox account</p>

        <?php if ($error !== ''): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

            <div class="form-group">
                <label for="userId">User ID</label>
                <input
                        type="text"
                        id="userId"
                        name="userId"
                        placeholder="Teacher: T48291 · Student: S48291537"
                        autocomplete="username"
                        required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                >
            </div>

            <button type="submit" class="login-btn">Log In</button>
        </form>

        <div class="login-hint">
            <strong>Test Accounts:</strong><br>
            Teacher → <strong>T48291</strong> / <strong>12345</strong><br>
            Student → <strong>S48291537</strong> / <strong>12345</strong>
        </div>

    </div>

</div>

</body>
</html>