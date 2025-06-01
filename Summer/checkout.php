<?php
include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

if(isset($_POST['order']) || isset($_POST['khalti_payment_token'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if($check_cart->rowCount() > 0){
      if ($method === 'khalti' && isset($_POST['khalti_payment_token'])) {
         // Verify Khalti Payment
         $khalti_token = $_POST['khalti_payment_token'];
         $url = 'https://a.khalti.com/api/v2/epayment/initiate/';
         $data = [
            'token' => $khalti_token,
            'amount' => $total_price * 100
         ];
         $headers = [
            "Authorization: 36b6d129cecd40e9864f0abc27fe7ddf"
         ];

         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

         $response = curl_exec($ch);
         $error = curl_error($ch);
         curl_close($ch);

         if ($error) {
            $message[] = 'Payment Verification Failed!';
         } else {
            $response_data = json_decode($response, true);
            if ($response_data['state']['name'] === "Completed") {
               // Payment is verified successfully
               $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
               $insert_order->execute([$user_id, $name, $number, $email, $method, $total_products, $total_price]);

               $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
               $delete_cart->execute([$user_id]);

               $message[] = 'Order placed successfully!';
            } else {
               $message[] = 'Payment not completed.';
            }
         }
      } else {
         // Handle Cash on Delivery or other payment methods
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $total_products, $total_price]);

         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = 'Order placed successfully!';
      }
   } else {
      $message[] = 'Your cart is empty';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Khalti Checkout Script -->
   <script src="https://khalti.com/static/khalti-checkout.js"></script>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">

   <form action="" method="POST" id="order-form">

   <h3>Your Orders</h3>

      <div class="display-orders">
      <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'].' ('.$fetch_cart['price'].' x '. $fetch_cart['quantity'].') - ';
               $total_products = implode(', ', $cart_items);
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      ?>
         <p> <?= $fetch_cart['name']; ?> <span>(<?= 'Rs.'.$fetch_cart['price'].'/- x '. $fetch_cart['quantity']; ?>)</span> </p>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
      ?>
         <input type="hidden" name="total_products" value="<?= $total_products; ?>">
         <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
         <div class="grand-total">Grand Total : <span>Rs.<?= $grand_total; ?>/-</span></div>
      </div>

      <h3>place your orders</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" placeholder="enter your name" class="box" maxlength="20" required>
         </div>
         <div class="inputBox">
            <span>Your Number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
         </div>
         <div class="inputBox">
            <span>Your Email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Method of payment:</span>
            <select name="method" id="payment-method" class="box" required>
               <option value="cash on delivery">Cash On Delivery</option>
               <option value="khalti">Khalti</option>
            </select>
         </div>
         <div class="inputBox">
            <span>City :</span>
            <input type="text" name="city" placeholder="Kathmandu" class="box" maxlength="50" required>
         </div>
         <div class="inputBox">
            <span>Province:</span>
            <input type="text" name="state" placeholder="Bagmati" class="box" maxlength="50" required>
         </div>
         
      </div>

   
      <input type="submit" id="order-button" name="order" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" value="place order">

   </form>

</section>

<script src="js/script.js"></script>
<script>
   document.getElementById('order-button').addEventListener('click', function (event) {
      var method = document.getElementById('payment-method').value;
      if (method === 'khalti') {
         event.preventDefault(); // Prevent form from submitting
         // Khalti Checkout Configuration
         var config = {
            // Replace this key with your Public Test Key
            "publicKey": "36b6d129cecd40e9864f0abc27fe7ddf",
            "productIdentity": "1234567890",
            "productName": "Order Payment",
            "productUrl": "http://example.com/product",
            "eventHandler": {
               onSuccess(payload) {
                  // Payment successful, now submit the form with the payload
                  var form = document.getElementById('order-form');
                  var input = document.createElement('input');
                  input.type = 'hidden';
                  input.name = 'khalti_payment_token';
                  input.value = payload.token;
                  form.appendChild(input);
                  form.submit();
               },
               onError(error) {
                  console.error(error);
                  alert('Payment Failed!');
               },
               onClose() {
                  console.log('Widget is closing');
               }
            }
         };

         var checkout = new KhaltiCheckout(config);
         checkout.show({amount: <?= $grand_total; ?> * 100}); // Amount in paisa
      } 
   });
</script>
</body>
</html>
