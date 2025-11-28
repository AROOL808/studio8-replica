<?php
include_once __DIR__ . '/payment-gateway/Midtrans.php';
include_once 'database_handle.php';


if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: /");
    exit();
}

\Midtrans\Config::$serverKey = loadEnvValue('SERVER_KEY');
\Midtrans\Config::$isProduction = false;

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$fraud = $notif->fraud_status;

error_log("Order ID $notif->order_id: "."transaction status = $transaction, fraud staus = $fraud");

if ($transaction == 'capture') {
    if ($fraud == 'challenge') {
      // TODO Set payment status in merchant's database to 'challenge'
    }
    else if ($fraud == 'accept') {
      // TODO Set payment status in merchant's database to 'success'
        
    }
}
else if ($transaction == 'settlement') {
    // TODO set payment status in merchant's database to 'success'
    update_order_status($notif->order_id, "BOOKED");
}
else if ($transaction == 'pending') {
    // TODO set payment status in merchant's database to 'pending'
    update_order_status($notif->order_id, "PENDING");
}
else if ($transaction == 'cancel') {
    if ($fraud == 'challenge') {
      // TODO Set payment status in merchant's database to 'failure'
    }
    else if ($fraud == 'accept') {
      // TODO Set payment status in merchant's database to 'failure'
    }
}
else if ($transaction == 'deny') {
pass;
}
?>