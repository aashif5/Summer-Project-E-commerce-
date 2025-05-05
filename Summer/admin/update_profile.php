<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Fetch the admin's profile details
$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {

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

    if ($old_pass == $empty_pass) {
        $message[] = 'please enter old password!';
    } elseif ($old_pass != $prev_pass) {
        $message[] = 'old password not matched!';
    } elseif ($new_pass != $confirm_pass) {
        $message[] = 'confirm password not matched!';
    } else {
        if ($new_pass != $empty_pass) {
            $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
            $update_admin_pass->execute([$confirm_pass, $admin_id]);
            $message[] = 'password updated successfully!';
        } else {
            $message[] = 'please enter a new password!';
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

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
   
   <section class="form-container">

   <form action="" method="post">
      <h3>Update Profile</h3>
      <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
      <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" required placeholder="enter your username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="old_pass" placeholder="enter old password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="enter new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_pass" placeholder="confirm new password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="update now" class="btn" name="submit">
   </form>

   </section>

</div>

<script src="../js/admin_script.js"></script>

</body>
</html>
