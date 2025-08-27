<?php
// Display sent messages for users
$host = 'localhost';
$db = 'xxx';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

session_start();

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_recipient_id'], $_POST['reply_content'])) {
    $reply_recipient_id = (int)$_POST['reply_recipient_id'];
    $reply_content = $conn->real_escape_string($_POST['reply_content']);
    $reply_sender_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO messages (sender_id, recipient_id, content) VALUES ($reply_sender_id, $reply_recipient_id, '$reply_content')");
}

$stmt = $conn->prepare("SELECT m.id, m.content, m.created_at, m.sender_id, u.username, u.image FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.recipient_id = ? ORDER BY m.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        .messages {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .message {
            border-bottom: 1px solid #eee;
            padding: 12px 0;
        }
        .message:last-child {
            border-bottom: none;
        }
        .timestamp {
            color: #888;
            font-size: 12px;
        }
        .user-img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="messages">
        <a href="dashboard.php" style="text-decoration:none;color:#333;font-size:18px;margin-bottom:16px;display:inline-block;">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h2><i class="fa fa-paper-plane"></i> Sent Messages</h2>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="message">
                    <div>
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" class="user-img">
                        <?php endif; ?>
                        <strong><i class="fa fa-user"></i> <?php echo htmlspecialchars($row['username']); ?></strong>: <?php echo htmlspecialchars($row['content']); ?>
                    </div>
                    <div class="timestamp"><i class="fa fa-clock"></i> Sent at: <?php echo $row['created_at']; ?></div>
                    <?php if (isset($row['sender_id']) && !empty($row['sender_id'])): ?>
                        <a href="reply.php?message_id=<?php echo htmlspecialchars($row['id']); ?>" style="background:#4caf50;color:#fff;border:none;padding:6.5px 16px;border-radius:6px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:8px;">
                            <i class="fa fa-reply"></i> Reply
                        </a>   <a href="delete_message.php?message_id=<?php echo htmlspecialchars($row['id']); ?>" style="color:rgb(7, 1, 1);border:none;padding:6px 6px;border-radius:6px;cursor:pointer; text-decoration:none;display:inline-block;margin-top:1px;" class="btn btn-danger">
                            <i class="fa fa-reply"></i> Delete  message
                        </a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p><i class="fa fa-inbox"></i> No messages found.</p>
        <?php endif; ?>
    </div>
</body>
</div>
          
    </div>

    <script>
        // Function to check for new messages
        function checkForNewMessages() {
            fetch('check_new_messages.php') // Endpoint to check for new messages
                .then(response => response.json())
                .then(data => {
                    if (data.newMessages) {
                        alert('You have new messages!');
                    }
                })
                .catch(error => console.error('Error checking for new messages:', error));
        }

        // Check for new messages every 30 seconds
        setInterval(checkForNewMessages, 30000);
    </script>
</body>
</html>

