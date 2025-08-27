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

$message_id = isset($_GET['message_id']) ? (int)$_GET['message_id'] : 0;
$recipient_id = 0;
$message = null;
$recipient = null;

// Fetch the message being replied to
if ($message_id) {
    $stmt = $conn->prepare("SELECT m.content, m.sender_id, u.username, u.image FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();
    if ($message) {
        $recipient_id = (int)$message['sender_id'];
        $recipient = [
            'username' => $message['username'],
            'image' => $message['image']
        ];
    }
}

// Fetch all users for the dropdown
$users = [];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'], $_POST['recipient_id'])) {
    $reply_content = $conn->real_escape_string($_POST['reply_content']);
    $reply_sender_id = $_SESSION['user_id'];
    $reply_recipient_id = (int)$_POST['recipient_id'];
    $conn->query("INSERT INTO messages (sender_id, recipient_id, content) VALUES ($reply_sender_id, $reply_recipient_id, '$reply_content')");
    header("Location: reply.php?message_id=$message_id");
    exit;
}

// Fetch chat history
$chat_history = [];
if ($recipient_id) {
    $stmt = $conn->prepare("
        SELECT m.content, m.sender_id, m.recipient_id, m.created_at, u.username, u.image 
        FROM messages m 
        LEFT JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?) 
        ORDER BY m.created_at ASC
    ");
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $chat_history[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reply</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reply-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .user-img {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 8px;
        }
        .chat-history {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            background: #f9f9f9;
        }
        .chat-message {
            margin-bottom: 12px;
        }
        .chat-message .sender {
            font-weight: bold;
        }
        .chat-message .content {
            margin-left: 40px;
            display: inline-block;
        }
        textarea {
            width: 100%;
            min-height: 80px;
            margin-bottom: 12px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background:#4caf50;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;
        }
    </style>
</head>
<body>
    <div class="reply-container">
        <a href="messages.php" style="text-decoration:none;color:#333;font-size:18px;margin-bottom:16px;display:inline-block;">
            <i class="fa fa-arrow-left"></i> Back to Messages
        </a>
        <?php if ($recipient): ?>
            <h3><i class="fa fa-comments"></i> Chat with <?php echo htmlspecialchars($recipient['username']); ?></h3>
            <div class="chat-history">
                <?php foreach ($chat_history as $chat): ?>
                    <div class="chat-message">
                        <img src="<?php echo htmlspecialchars($chat['image']); ?>" class="user-img" alt="User Image">
                        <span class="sender"><?php echo htmlspecialchars($chat['username']); ?>:</span>
                        <span class="content"><?php echo htmlspecialchars($chat['content']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post">
                <input type="hidden" name="recipient_id" value="<?php echo $recipient_id; ?>">
                <p><strong>Replying to:</strong> <?php echo htmlspecialchars($recipient['username']); ?></p>
                <textarea name="reply_content" required placeholder="Type your reply..."></textarea>
                <button type="submit"><i class="fa fa-paper-plane"></i> Send Reply</button>
            </form>
        <?php else: ?>
            <p><i class="fa fa-info-circle"></i> No recipient found. Please use a valid message link.</p>
        <?php endif; ?>
    </div>
</body>
</html>
