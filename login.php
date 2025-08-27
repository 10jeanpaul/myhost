<?php
// User login page
session_start();
$host = 'localhost';
$db = 'xxx';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $sql = "SELECT id, password FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($_POST['password'], $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post" style="max-width:400px;margin:40px auto;">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Username" required style="width:100%;margin-bottom:12px;padding:10px;">
        <input type="password" name="password" placeholder="Password" required style="width:100%;margin-bottom:12px;padding:10px;">
        <button type="submit">Login</button>
        <div style="margin-top:16px;color:red;"> <?php echo $error; ?> </div>
    </form>
</body>
</html>
