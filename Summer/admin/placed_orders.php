<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit; // Ensure to exit after redirecting
}

// Initialize $fetch_profile to prevent undefined variable error
$fetch_profile = [];

if(isset($_POST['submit'])){
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $update_profile_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
   $update_profile_name->execute([$name, $admin_id]);

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass = $_POST['prev_pass'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $confirm_pass = sha1($_POST['confirm_pass']);
   $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

   if($old_pass == $empty_pass){
      $message[] = 'please enter old password!';
   }elseif($old_pass != $prev_pass){
      $message[] = 'old password not matched!';
   }elseif($new_pass != $confirm_pass){
      $message[] = 'confirm password not matched!';
   }else{
      if($new_pass != $empty_pass){
         $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
         $update_admin_pass->execute([$confirm_pass, $admin_id]);
         $message[] = 'password updated successfully!';
      }else{
         $message[] = 'please enter a new password!';
      }
   }
}

// Handle form submission for updating payment status
if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];

   $update_payment_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment_status->execute([$payment_status, $order_id]);

   $message[] = 'Payment status updated successfully!';
}

// Handle order deletion
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);

   $message[] = 'Order deleted successfully!';
}

// Fetch admin profile after form submission
$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

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
   
<section class="orders">

<h1 class="heading">Placed Orders</h1>

<div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> Placed On : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> Name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> Number : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> Address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> Total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> Total price : <span>Nrs.<?= $fetch_orders['total_price']; ?>/-</span> </p>
      <p> Payment method : <span><?= $fetch_orders['method']; ?></span> </p>
      <form action="" method="post">
         <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
         <select name="payment_status" class="select">
            <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
         </select>
        <div class="flex-btn">
         <input type="submit" value="update" class="option-btn" name="update_payment">
         <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
        </div>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
   ?>

</div>

</section>

</div>

<script src="../js/admin_script.js"></script>

</body>
</html>
