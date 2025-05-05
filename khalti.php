<?php
session_start();
include('components/connect.php');
$user_id = $_SESSION['user_id'];
echo  $user_id;
if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'];
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $method = $_POST['method'];
    $method = filter_var($method, FILTER_SANITIZE_STRING);
    $address = $_POST['address'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $total_products = $_POST['total_products'];
    $total_price = $_POST['total_price'];

    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $check_cart->execute([$user_id]);

    if($check_cart->rowCount() > 0){
        if($address == ''){
            $message[] = 'please add your address!';
        }else{
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

            $order_id = $conn->lastInsertId();
            $_SESSION['order_id'] = $order_id;

            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);

            $message[] = 'order placed successfully!';
        }
    }else{
        $message[] = 'your cart is empty';
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $fullname = $user['name'];
    $email = $user['email'];
}
$_SESSION['order_id'] = 9;


if(isset($_SESSION['order_id'])) {
    $order_id = $_SESSION['order_id'];

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order_details) {
        $grand_total = 10000;

        $data = array(
            "return_url" => "http://localhost/summer/home.php",
            "website_url" => "http://localhost/summer/home.php",
            "amount" => $grand_total,
            "purchase_order_id" => "Order01",
            "purchase_order_name" => "Test Order",
            "customer_info" => array(
                "name" => $fullname,
                "email" => $email,
            ),
        );

        $post_data = json_encode($data);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                ' Authorization: Key 36b6d129cecd40e9864f0abc27fe7ddf', 
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            echo curl_error($curl);
        } else {
            $response_array = json_decode($response, true);
            if (!empty($response_array['payment_url'])) {
                unset($_SESSION['cart']);
                header("Location: " . $response_array['payment_url']);
                exit;
            } else {
                echo "Payment initiation failed or payment URL is empty.";
            }
        }

        curl_close($curl);

        echo $response;
    } else {
        die('Order ID not provided.');
    }
} else {
    die('User not found.');
}
?>
