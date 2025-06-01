<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_user->execute([$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_orders->execute([$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
   $delete_messages->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users accounts</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<style>
   <style>
       body {
           margin: 0;
           font-family: Arial, sans-serif;
           background-color: #f4f4f4;
           display: flex;
           min-height: 100vh;
       }
       .sidebar {
           width: 250px;
           background-color: #333;
           color: #fff;
           padding: 20px;
           height: 100vh;
           position: fixed;
           top: 0;
           left: 0;
           overflow-y: auto;
       }
       .sidebar h1 {
           font-size: 1.8rem;
           margin-bottom: 20px;
       }
       .sidebar ul {
           list-style-type: none;
           padding: 0;
       }
       .sidebar ul li {
           margin-bottom: 15px;
       }
       .sidebar ul li a {
           color: #fff;
           text-decoration: none;
           font-size: 1.2rem;
           display: block;
           padding: 10px;
           border-radius: 8px;
           transition: background 0.3s;
       }
       .sidebar ul li a:hover {
           background-color: #555;
       }
       .sidebar .btn {
           display: block;
           width: 100%;
           padding: 10px;
           background-color: #444;
           color: #fff;
           text-align: center;
           text-decoration: none;
           border-radius: 8px;
           margin-top: 10px;
           transition: background 0.3s;
       }
       .sidebar .btn:hover {
           background-color: #555;
       }
       .content {
           margin-left: 270px;
           padding: 20px;
           width: calc(100% - 270px);
       }
       .content .heading {
           margin-bottom: 20px;
           font-size: 2rem;
           color: #333;
       }
       .accounts .box-container {
           display: grid;
           grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
           gap: 20px;
       }
       .box {
           background-color: #fff;
           padding: 20px;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
       }
       .box p {
           margin: 10px 0;
           color: #333;
       }
       .box .delete-btn {
           display: inline-block;
           padding: 10px;
           background-color: #e74c3c;
           color: #fff;
           border-radius: 8px;
           text-decoration: none;
           transition: background 0.3s;
       }
       .box .delete-btn:hover {
           background-color: #c0392b;
       }
       .empty {
           text-align: center;
           padding: 20px;
           background-color: #fff;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
       }
   </style>
</style>
<body>



<div class="sidebar">
    <h1>Admin Dashboard</h1>
    <ul>
        <li><a href="update_profile.php">Update Profile</a></li>
        <li><a href="placed_orders.php">Orders</a></li>
        <li><a href="products.php">Products</a></li>
        <li><a href="users_accounts.php">Users</a></li>
        <li><a href="admin_accounts.php">Admins</a></li>
        <li><a href="messages.php">Messages</a></li>
    </ul>
    <a href="logout.php" class="btn">Logout</a>
</div>

<div class="content">
   
   <section class="accounts">

   <h1 class="heading">User accounts</h1>

   <div class="box-container">

   <?php
      $select_accounts = $conn->prepare("SELECT * FROM `users`");
      $select_accounts->execute();
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> User id : <span><?= $fetch_accounts['id']; ?></span> </p>
      <p> Username : <span><?= $fetch_accounts['name']; ?></span> </p>
      <p> Email : <span><?= $fetch_accounts['email']; ?></span> </p>
      <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('delete this account? the user related information will also be delete!')" class="delete-btn">delete</a>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no accounts available!</p>';
      }
   ?>

   </div>

</section>

</div>

<script src="../js/admin_script.js"></script>

</body>
</html>