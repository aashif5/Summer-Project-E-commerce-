<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

$message_sent = '';

if (isset($_POST['send'])) {
   $message = $_POST['msg'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);
   
   $image = '';
   if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $image = $_FILES['image']['name'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/' . $image;
      move_uploaded_file($image_tmp_name, $image_folder);
   }

   $send_message = $conn->prepare("INSERT INTO `chats` (user_id, message, image, sender) VALUES (?, ?, ?, 'user')");
   $send_message->execute([$user_id, $message, $image]);
   $message_sent = 'Message sent successfully!';
   header('Location: contact.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
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
   }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="contact">
   <div class="chat-box">
      <h3>Chat with Admin</h3>
      <div class="chat-history">
         <?php
            $select_chats = $conn->prepare("SELECT * FROM `chats` WHERE user_id = ? ORDER BY timestamp ASC");
            $select_chats->execute([$user_id]);
            if($select_chats->rowCount() > 0){
               while($fetch_chat = $select_chats->fetch(PDO::FETCH_ASSOC)){
                  if($fetch_chat['sender'] == 'user'){
                     echo '<p><strong>You:</strong> '.htmlspecialchars($fetch_chat['message']).'</p>';
                     if($fetch_chat['image']){
                        echo '<img src="uploaded_img/'.htmlspecialchars($fetch_chat['image']).'" alt="Image">';
                     }
                  } elseif ($fetch_chat['sender'] == 'admin') {
                     echo '<p><strong>Admin:</strong> '.htmlspecialchars($fetch_chat['message']).'</p>';
                     if($fetch_chat['image']){
                        echo '<img src="uploaded_img/'.htmlspecialchars($fetch_chat['image']).'" alt="Image">';
                     }
                  }
               }
            } else {
               echo '<p class="empty">No messages yet</p>';
            }
         ?>
      </div>

      <form action="" method="post" enctype="multipart/form-data">
         <textarea name="msg" class="box" placeholder="enter your message" cols="30" rows="5" required></textarea>
         <input type="file" name="image" accept="image/*" class="box">
         <input type="submit" value="Send Message" name="send" class="btn">
      </form>
   </div>

   <?php if ($message_sent): ?>
      <p><?php echo $message_sent; ?></p>
   <?php endif; ?>
</section>

<script src="js/script.js"></script>

</body>
</html>
