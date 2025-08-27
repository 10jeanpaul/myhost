<?php
$conn=new mysqli("localhost","root","","xxx");
if(isset($_GET['id'])){
    $id=$_GET['id'];
    $query="DELETE FROM `users` WHERE `users`.`id` = $id";
    $result=mysqli_query($conn,$query);
    if($result){
        header("location:admin_dashboard.php");
    }
    else{
        echo 'user not deleted';
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin dashboard</title>
    <link rel="stylesheet" href="bootstrap.css">
    <style>
      table{
        border-collapse:collapse;
        width: 600px;
        height:auto;
        margin:50px;
        
      }
       .user-img {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 8px;
        }
        td{
            padding-left:30px;
        }
        th{
            background:olive;
            color:black;
            font-weight: bold;
        }
    </style>
</head>
<body>
   <table border="1px">
    <tr>
        <th>USER ID</th>

        <th>USER NAME</th>
        <th>IMAGE </th>
        <th>PREFERENCES</th>
    </tr>

<?php
$conn=new mysqli("localhost","root","","xxx");

$select="SELECT * FROM users";
$stmt=mysqli_query($conn,$select);
while($row=mysqli_fetch_assoc($stmt)){
$id=$row['id'];


?>
<tr>
<td><?=$row['id']?></td>
<td><?=$row['username']?></td>
<td>
     <?php if (!empty($row['image'])): ?>
     <img src="<?php echo htmlspecialchars($row['image']); ?>" class="user-img">
     <?php endif; ?>
</td>

<td>
    
    <a href="#" class="btn btn-success">Update user</a>
    <a href="admin_dashboard.php?id=<?php echo $row['id']?>" class="btn btn-danger">Delete user</a>
</td>
</tr>
<?php
}

?>

   </table> 
</body>
</html>
