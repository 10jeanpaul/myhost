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
    $stmt ="DELETE FROM `messages` WHERE `messages`.`id` = $message_id";
    $result=mysqli_query($conn,$stmt);
    if($result){
        header("location:messages.php");
    }
    
    
   
}