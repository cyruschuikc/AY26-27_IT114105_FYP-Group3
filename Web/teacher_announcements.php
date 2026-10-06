<?php
$active = 'announcements';

// ===== session + 数据库 =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
/** @var mysqli $conn */

// 没登录 → 回登录页
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit;
}

$userID = (int) $_SESSION['userID'];

// 拿当前老师的 Teachers.id（Announcements.teacher_id 需要它）
$teacherRow = null;
$stmt = mysqli_prepare($conn, "SELECT id FROM `Teachers` WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
$teacherRow = mysqli_fetch_assoc($rs);
mysqli_stmt_close($stmt);

$teacherId = $teacherRow ? (int)$teacherRow['id'] : 0;


// =========================================================
// 1. 处理「Send Announcement」提交
// =========================================================
$sendMsg  = '';
$sendType = '';

if (isset($_POST['sendAnnouncement'])) {

    $title   = trim(isset($_POST['annTitle'])   ? $_POST['annTitle']   : '');
    $message = trim(isset($_POST['annMessage']) ? $_POST['annMessage'] : '');
    $sendTo  = isset($_POST['annSendTo']) ? $_POST['annSendTo'] : 'all';

    if ($title === '' || $message === '') {
        $sendMsg  = 'Title and message cannot be empty.';
        $sendType = 'error';

    } elseif ($teacherId === 0) {
        $sendMsg  = 'Your teacher profile was not found.';
        $sendType = 'error';

    } else {
        // sendTo: 'all' 或 group_id
        $targetGroupId   = null;
        $targetProjectId = null;

        if ($sendTo !== 'all' && $sendTo !== '') {
            $targetGroupId = (int)$sendTo;

            // 顺便把 group 对应的 project_id 也填上
            $stmt = mysqli_prepare($conn, "SELECT project_id FROM `Groups` WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $targetGroupId);
            mysqli_stmt_execute($stmt);
            $rs = mysqli_stmt_get_result($stmt);
            $gRow = mysqli_fetch_assoc($rs);
            mysqli_stmt_close($stmt);

            if ($gRow) {
                $targetProjectId = (int)$gRow['project_id'];
            }
        }

        // 插入
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `Announcements`
                (teacher_id, title, message, target_group_id, target_project_id)
            VALUES (?, ?, ?, ?, ?)
        ");

        // mysqli 绑定 NULL 需要特殊处理
        if ($targetGroupId === null && $targetProjectId === null) {
            // 用 "issss" 不行，改成用 bind_param 传 null
            $null = null;
            // 简单做法：改用 SQL 里判断
            $stmt2 = mysqli_prepare($conn, "
                INSERT INTO `Announcements`
                    (teacher_id, title, message, target_group_id, target_project_id)
                VALUES (?, ?, ?, NULL, NULL)
            ");
            mysqli_stmt_bind_param($stmt2, "iss", $teacherId, $title, $message);
            $ok = mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            mysqli_stmt_close($stmt);

        } else {
            $gid = $targetGroupId !== null ? $targetGroupId : null;
            $pid = $targetProjectId !== null ? $targetProjectId : null;

            // mysqli 绑定 null：用变量 + 类型 'i' 传 null 是可以的
            mysqli_stmt_bind_param($stmt, "issss", $teacherId, $title, $message, $gid, $pid);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if (!empty($ok)) {
            $sendMsg  = 'Announcement sent successfully.';
            $sendType = 'success';
        } else {
            $sendMsg  = 'Failed to send: ' . mysqli_error($conn);
            $sendType = 'error';
        }
    }
}


// =========================================================
// 2. 加载所有小组（给 "Send To" 下拉用）
// =========================================================
$groups = array();
$rs = mysqli_query($conn, "
    SELECT id, name
    FROM `Groups`
    ORDER BY id
");
if ($rs) {
    while ($row = mysqli_fetch_assoc($rs)) { $groups[] = $row; }
    mysqli_free_result($rs);
}


// =========================================================
// 3. 加载 Sent History（最近 20 条）
// =========================================================
$sentHistory = array();

$sql = "
    SELECT
        a.id,
        a.title,
        a.message,
        a.target_group_id,
        a.target_project_id,
        a.created_at,
        g.name AS target_group_name
    FROM `Announcements` a
    LEFT JOIN `Groups` g ON g.id = a.target_group_id
    WHERE a.teacher_id = ?
    ORDER BY a.created_at DESC
    LIMIT 20
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$rs = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($rs)) {
    $sentHistory[] = $row;
}
mysqli_stmt_close($stmt);


// =========================================================
// 4. 辅助函数：timeAgo
// =========================================================
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (empty($datetime)) return '—';
        $ts = strtotime($datetime);
        $diff = time() - $ts;

        if ($diff < 60)         return 'just now';
        if ($diff < 3600)       return floor($diff / 60) . 'm ago';
        if ($diff < 86400)      return floor($diff / 3600) . 'h ago';
        if ($diff < 86400 * 2)  return 'Yesterday';
        if ($diff < 86400 * 7)  return floor($diff / 86400) . 'd ago';
        return date('d M Y', $ts);
    }
}
?>
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

        <!-- =========================================================
             New Announcement
             ========================================================= -->
        <h2>New Announcement</h2>
        <div class="card">
            <form method="post" action="teacher_announcements.php" class="announcement-block stack">

                <?php if ($sendMsg !== ''): ?>
                    <div style="padding:12px 16px; margin-bottom:16px; border-radius:8px;
                            background: <?php echo $sendType==='success' ? '#eaf7ee' : '#fdeeee'; ?>;
                            color: <?php echo $sendType==='success' ? '#176b2c' : '#a21b1b'; ?>;
                            border-left: 4px solid <?php echo $sendType==='success' ? '#2e9d4d' : '#d32f2f'; ?>;">
                        <?php echo htmlspecialchars($sendMsg); ?>
                    </div>
                <?php endif; ?>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annTitle">Title</label>
                    <input
                            type="text"
                            id="annTitle"
                            name="annTitle"
                            placeholder="e.g. Progress Report Deadline"
                            required
                    >
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annMessage">Message</label>
                    <textarea
                            id="annMessage"
                            name="annMessage"
                            placeholder="Write your announcement here..."
                            required
                    ></textarea>
                </div>

                <div class="field" style="margin-bottom: 16px;">
                    <label for="annSendTo">Send To</label>
                    <select id="annSendTo" name="annSendTo">
                        <option value="all">All Teams</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?php echo (int)$g['id']; ?>">
                                <?php echo htmlspecialchars($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="sendAnnouncement" class="btn-primary">Send Announcement</button>

            </form>
        </div>

        <!-- =========================================================
             Sent History
             ========================================================= -->
        <h2>Sent History</h2>
        <div class="card">
            <table>
                <tr>
                    <th>Title</th>
                    <th>Sent To</th>
                    <th>Sent On</th>
                    <th></th>
                </tr>

                <?php if (count($sentHistory) === 0): ?>
                    <tr>
                        <td colspan="4" class="muted" style="text-align: center; padding: 30px;">
                            You haven't sent any announcements yet.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($sentHistory as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['title']); ?></td>
                        <td>
                            <?php if (!empty($a['target_group_name'])): ?>
                                <?php echo htmlspecialchars($a['target_group_name']); ?>
                            <?php else: ?>
                                All Teams
                            <?php endif; ?>
                        </td>
                        <td class="muted"><?php echo timeAgo($a['created_at']); ?></td>
                        <td><a href="#" class="link">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</main>

</body>
</html>