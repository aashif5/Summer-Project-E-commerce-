<?php
include 'components/connect.php';

session_start();

$response = array('success' => false, 'message' => 'Failed to send message');

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

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
   if ($send_message->execute([$user_id, $message, $image])) {
      $response['success'] = true;
      $response['message'] = 'Message sent successfully!';
      $response['message_text'] = $message;
      $response['message_image'] = $image ? $image_folder : null;
   } else {
      $response['message'] = 'Failed to send message';
   }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
