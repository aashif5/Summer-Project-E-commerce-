<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit();
}

// Handle send message
if(isset($_POST['send'])){
   $user_id = $_POST['user_id'];
   $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

   $image = '';
   if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      $max_size = 2 * 1024 * 1024; // 2MB
      $file_type = mime_content_type($_FILES['image']['tmp_name']);
      $file_size = $_FILES['image']['size'];

      if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
         $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
         $image_tmp_name = $_FILES['image']['tmp_name'];
         $image_folder = '../uploaded_img/' . $image;
         move_uploaded_file($image_tmp_name, $image_folder);
      } else {
         $message_sent = 'Invalid file type or size.';
      }
   }

   if (empty($message_sent)) {
      $send_message = $conn->prepare("INSERT INTO `chats` (user_id, admin_id, message, image, sender) VALUES (?, ?, ?, ?, 'admin')");
      $send_message->execute([$user_id, $admin_id, $message, $image]);
      header('Location: messages.php?user_id=' . $user_id);
      exit();
   }
}

// Handle delete chat
if(isset($_GET['delete_chat'])){
   $delete_user_id = $_GET['delete_chat'];
   $delete_chat = $conn->prepare("DELETE FROM `chats` WHERE user_id = ?");
   $delete_chat->execute([$delete_user_id]);
   header('Location: messages.php');
   exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
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
       .view-btn {
           background-color: #4CAF50;
           color: #fff;
           border: none;
           padding: 10px 20px;
           font-size: 16px;
           cursor: pointer;
       }
       .view-btn:hover {
           background-color: #3e8e41;
       }
       .chat-box {
      margin-top: 20px;
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 8px;
      background-color: #f9f9f9;
   }

   .chat-history {
      margin-top: 10px;
      max-height: 300px;
      overflow-y: auto;
      padding-right: 10px;
   }

   .chat-history p {
      margin: 10px 0;
      padding: 5px 10px;
      border-radius: 5px;
      word-wrap: break-word;
      font-size: 18px; /* Increased font size */
   }

   .chat-history img {
      max-width: 23%; /* Adjusted max-width to make images even smaller */
      border-radius: 5px;
      margin-top: 5px;
   }

   .box {
      margin-bottom: 10px;
   }

   .empty {
      color: #999;
      font-size: 18px; /* Increased font size */
   } .chat-box {
      margin-top: 20px;
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 8px;
      background-color: #f9f9f9;
   }

   .chat-history {
      margin-top: 10px;
      max-height: 300px;
      overflow-y: auto;
      padding-right: 10px;
   }

   .chat-history p {
      margin: 10px 0;
      padding: 5px 10px;
      border-radius: 5px;
      word-wrap: break-word;
      font-size: 18px; /* Increased font size */
   }

   .chat-history img {
      max-width: 23%; /* Adjusted max-width to make images even smaller */
      border-radius: 5px;
      margin-top: 5px;
   }

   .box {
      margin-bottom: 10px;
   }

   .empty {
      color: #999;
      font-size: 18px; /* Increased font size */
   }
   </style>
</head>
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
   <section class="contacts">
       <h1 class="heading">Messages</h1>
       <div class="box-container">
           <?php
               $select_users = $conn->prepare("SELECT DISTINCT user_id FROM `chats`");
               $select_users->execute();
               if($select_users->rowCount() > 0){
                   while($fetch_user = $select_users->fetch(PDO::FETCH_ASSOC)){
                       $user_id = $fetch_user['user_id'];
                       $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                       $select_user->execute([$user_id]);
                       $fetch_user_info = $select_user->fetch(PDO::FETCH_ASSOC);
                       
                       if($fetch_user_info){
                           ?>
                           <div class="box">
                               <p> User ID: <span><?= htmlspecialchars($fetch_user_info['id']); ?></span></p>
                               <p> Name: <span><?= htmlspecialchars($fetch_user_info['name']); ?></span></p>
                               <p> Email: <span><?= htmlspecialchars($fetch_user_info['email']); ?></span></p>
                               <a href="messages.php?user_id=<?= $user_id; ?>" class="view-btn">View Messages</a>
                               <a href="messages.php?delete_chat=<?= $user_id; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete all messages with this user?');">Delete Chat</a>
                           </div>
                           <?php
                       }
                   }
               }else{
                   echo '<p class="empty">No messages yet</p>';
               }
           ?>
       </div>

       <?php
       if(isset($_GET['user_id'])){
           $user_id = $_GET['user_id'];
           ?>
           <div class="chat-box">
               <h3>Chat with User ID: <?= htmlspecialchars($user_id); ?></h3>
               <div class="chat-history">
                   <?php
                   $select_chats = $conn->prepare("SELECT * FROM `chats` WHERE user_id = ? ORDER BY timestamp ASC");
                   $select_chats->execute([$user_id]);
                   if($select_chats->rowCount() > 0){
                       while($fetch_chat = $select_chats->fetch(PDO::FETCH_ASSOC)){
                           if($fetch_chat['sender'] == 'admin'){
                               echo '<p><strong>You:</strong> '.htmlspecialchars($fetch_chat['message']).'</p>';
                               if($fetch_chat['image']){
                                   echo '<img src="../uploaded_img/'.htmlspecialchars($fetch_chat['image']).'" alt="Image">';
                               }
                           } elseif ($fetch_chat['sender'] == 'user') {
                               echo '<p><strong>User:</strong> '.htmlspecialchars($fetch_chat['message']).'</p>';
                               if($fetch_chat['image']){
                                   echo '<img src="../uploaded_img/'.htmlspecialchars($fetch_chat['image']).'" alt="Image">';
                               }
                           }
                       }
                   } else {
                       echo '<p class="empty">No messages yet</p>';
                   }
                   ?>
               </div>

               <form action="" method="post" enctype="multipart/form-data">
                   <textarea name="message" class="box" placeholder="Enter your message" cols="30" rows="5" required></textarea>
                   <input type="file" name="image" accept="image/*" class="box">
                   <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id); ?>">
                   <input type="submit" value="Send Message" name="send" class="btn">
               </form>
           </div>
           <?php
       }
       ?>

   </section>
</div>

</body>
</html>
