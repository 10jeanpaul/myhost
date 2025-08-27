<?php
session_start();
$host = 'localhost';
$db = 'xxx';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user's info
$user = null;
$res = $conn->query("SELECT username, image FROM users WHERE id=" . $_SESSION['user_id']);
if ($res && $row = $res->fetch_assoc()) {
    $user = $row;
}

// Handle image update
if (isset($_POST['update_image']) && isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
    $img_name = basename($_FILES['new_image']['name']);
    $target_dir = 'uploads/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . uniqid() . '_' . $img_name;
    if (move_uploaded_file($_FILES['new_image']['tmp_name'], $target_file)) {
        // Delete old image file if exists
        $old_img = $user['image'];
        if ($old_img && file_exists($old_img)) {
            unlink($old_img);
        }
        $conn->query("UPDATE users SET image='" . $conn->real_escape_string($target_file) . "' WHERE id=" . $_SESSION['user_id']);
        header('Location: dashboard.php');
        exit;
    }
}
// Handle image delete
if (isset($_POST['delete_image'])) {
    $old_img = $user['image'];
    if ($old_img && file_exists($old_img)) {
        unlink($old_img);
    }
    $conn->query("UPDATE users SET image=NULL WHERE id=" . $_SESSION['user_id']);
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard {
            max-width: 550px;
            margin: 40px auto;
            background: #fff;
            padding: 37px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        .user-img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 12px;
        }
        .nav {
            margin: 24px 0;
        }
        .nav a {
            display: inline-block;
            margin: 0 12px;
            padding: 10px 24px;
            background: #f5e5e5ff;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav a:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php if ($user && $user['image']): ?>
            <div style="margin-bottom:16px;">
                <img src="<?php echo htmlspecialchars($user['image']); ?>" style="width:220px;height:220px;object-fit:cover;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                <div style="margin-top:12px;">
                    <form method="post" enctype="multipart/form-data" style="display:inline-block;">
                        <label for="img-upload" style="cursor:pointer;">
                            <i class="fa fa-upload"></i>
                        </label>
                        <input id="img-upload" type="file" name="new_image" accept="image/*" required style="display:none;">
                        <button type="submit" name="update_image"><i class="fa fa-sync-alt"></i> Update</button>
                    </form>
                    <form method="post" style="display:inline-block;">
                        <button type="submit" name="delete_image" onclick="return confirm('Delete your profile image?');">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                    <a href="<?php echo htmlspecialchars($user['image']); ?>" download style="display:inline-block;margin-left:8px; width:120px; height:24px; border:1px solid blue; text-decoration:none;padding:5px;color:black;border-radius:10px;background:blue;">
                        <i class="fa fa-download"></i> Download
                    </a>
                </div>
            </div>
        <?php endif; ?>
        <h2>Welcome, <?php echo htmlspecialchars($user ? $user['username'] : 'User'); ?>!</h2>
        <div class="nav">
            <a href="send.php"><i class="fa fa-paper-plane"></i> Send Message</a>
            <a href="messages.php"><i class="fa fa-envelope"></i> View Messages</a>
            <a href="register.php"><i class="fa fa-user-plus"></i> Register New User</a>
            <a href="#"><i class="fa fa-comment-dots"></i> Simple Message</a>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="nav">
            
        </div>
        <p>Use the navigation above to access different features of your account.</p>
    </div>
</body>
</html>
