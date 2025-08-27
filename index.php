<?php
// Simple message sending using PHP and MySQL
// Update DB credentials as needed
$host = 'localhost';
$db = 'xxx';
$user = 'root';
$pass = '';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $sql = "INSERT INTO messages (content) VALUES ('$message')";
    if ($conn->query($sql)) {
        echo "Message sent!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post">
        <textarea name="message" required></textarea><br>
        <button type="submit">Send</button>
    </form>
</body>
</html>
