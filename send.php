<?php
// Send message to selected user
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

// Handle image upload
$image_message = '';
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $img_name = basename($_FILES['profile_image']['name']);
    $target_dir = 'uploads/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . uniqid() . '_' . $img_name;
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
        $conn->query("UPDATE users SET image='" . $conn->real_escape_string($target_file) . "' WHERE id=" . $_SESSION['user_id']);
        $image_message = 'Image uploaded!';
    } else {
        $image_message = 'Image upload failed.';
    }
}

// Fetch users for selection (with image)
$users = $conn->query("SELECT id, username, image FROM users WHERE id != " . $_SESSION['user_id']);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient_id'], $_POST['message']) && !isset($_FILES['profile_image'])) {
    $recipient_id = (int)$_POST['recipient_id'];
    $content = $conn->real_escape_string($_POST['message']);
    $emoji = isset($_POST['emoji']) ? $conn->real_escape_string($_POST['emoji']) : '';
    if ($emoji) {
        $content .= ' ' . $emoji;
    }
    $sender_id = $_SESSION['user_id'];
    $sql = "INSERT INTO messages (sender_id, recipient_id, content) VALUES ($sender_id, $recipient_id, '$content')";
    if ($conn->query($sql)) {
        $message = 'Message sent!'.header("location:dashboard.php");
    } else {
        $message = 'Error: ' . $conn->error;
    }
}

// Get current user's image
$user_img = '';
$res = $conn->query("SELECT image FROM users WHERE id=" . $_SESSION['user_id']);
if ($res && $row = $res->fetch_assoc()) {
    $user_img = $row['image'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-img { width:32px; 
            height:32px;
             object-fit:cover; 
             border-radius:50%;
              vertical-align:middle;
               margin-right:8px;
             }
    </style>
</head>
<body>
    <div style="max-width:400px;margin:40px auto;">
        <h2><i class="fa fa-paper-plane"></i> Welcome, </h2>
        <?php if ($user_img): ?>
            <img src="<?php echo htmlspecialchars($user_img); ?>" class="user-img"> Your current profile image<br><br>
        <?php endif; ?>
        <form method="post" style="margin-bottom:24px;">
            <h3><i class="fa fa-envelope"></i> Send Message</h3>
            <label for="recipient_id"><i class="fa fa-user"></i> Select User:</label>
            <select name="recipient_id" required style="width:100%;margin-bottom:12px;padding:10px;background:skyblue;">
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>No other users found</option>
                <?php endif; ?>
            </select>
            <textarea name="message" required placeholder="Type your message here..."></textarea><br>
            <button type="submit"><i class="fa fa-paper-plane"></i> Send</button>
            <div style="margin-top:16px;color:green;"> <?php echo $message; ?> </div>
        </form>
    </div>
</body>
</html>
</body>
</html>
</body>
</html>
