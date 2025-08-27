<?php
// User registration page
$host = 'localhost';
$db = 'xxx';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = 'uploads/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . uniqid() . '_' . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $conn->real_escape_string($target_file);
        }
    }

    if ($image_path) {
        $sql = "INSERT INTO users (username, password, image) VALUES ('$username', '$password', '$image_path')";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    }

    if ($conn->query($sql)) {
        $message = 'Account created! <a href="login.php">Login here</a>.';
    } else {
        $message = 'Error: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <form method="post" enctype="multipart/form-data" style="max-width:400px;margin:40px auto;">
        <h2><i class="fa fa-user-plus"></i> Create Account</h2>
        <div style="position:relative;">
            <i class="fa fa-user" style="position:absolute;left:10px;top:14px;color:#888;"></i>
            <input type="text" name="username" placeholder="Username" required style="width:100%;margin-bottom:12px;padding:10px 10px 10px 34px;">
        </div>
        <div style="position:relative;">
            <i class="fa fa-lock" style="position:absolute;left:10px;top:14px;color:#888;"></i>
            <input type="password" name="password" placeholder="Password" required style="width:100%;margin-bottom:12px;padding:10px 10px 10px 34px;">
        </div>
        <div style="position:relative;">
            <i class="fa fa-image" style="position:absolute;left:10px;top:14px;color:#888;"></i>
            <input type="file" name="image" accept="image/*" style="width:100%;margin-bottom:12px;padding-left:34px;">
        </div>
        <button type="submit"><i class="fa fa-user-plus"></i> Register</button>
        <div style="margin-top:16px;color:green;"> <?php echo $message; ?> </div>
    </form>
</body>
</html>
