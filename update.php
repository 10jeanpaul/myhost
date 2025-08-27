<?php
$conn=mysqli_connect("localhost","root","","xxx");
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
        $conn->query("UPDATE users SET image='" . $conn->real_escape_string($target_file) . "' WHERE id='id'");
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
    $conn->query("UPDATE users SET image=NULL WHERE id='id'");
    header('Location: admin_dashboard.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
   
</head>
<body>
    <form action=""  method="post">
        <input type="hidden" name="id" value="<?=$row['user_id'];?>">
        <label for="">Username</label>
        <input type="text" name="name" value="<?=$row['name'];?>"><br><br>
        
        
        <label for="">User image</label>
        <input type="email" name="email" value="<?=$row['image'];?>"><br><br>
        <label for="">password</label>
        <input type="password" name="password" value="<?=$row['password'];?>"><br><br>
        <input type="submit" value="update" name="update">
    </form>
</body>
</html>
<?php
if(isset($_POST['update'])){
    $username=$_POST["name"];
    $email=$_POST["email"];
    $password=$_POST["password"];
   // $password_hash=password_hash($password,PASSWORD_BCRYPT);
    $id=$_POST["id"];
    $sql="UPDATE `users` SET `name`='$username',`email`='$email', `password`='$password'
    WHERE `user_id`='$id'";
    $update=mysqli_query($conn,$sql);

    if($update)

    {
        header("location:lost.php"); 
    }
    else{
        echo"usernotupdated";
    }
}
?>