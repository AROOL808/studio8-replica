<?php 
// session_start();
include_once "database_handle.php";
function midtrans_status($order_id)
{
    $SERVER_KEY = loadEnvValue('SERVER_KEY');

    $url = "https://api.sandbox.midtrans.com/v2/{$order_id}/status";

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Basic " . base64_encode($SERVER_KEY . ":"),
            "Accept: application/json"
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // Print raw JSON response
    header('Content-Type: application/json');
    echo $response;
}

// midtrans_status($_GET['order_id']);

$order_detail = get_order_detail($_GET['order_id'])['data'][0];


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking Berhasil</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="success.css">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-0 success-card">

        <div class="card-header text-white bg-dark">
            <h3 class="mb-0">Booking Berhasil</h3>
        </div>

        <div class="card-body">
            <p class="lead fw-semibold">
                Terima kasih, <?= htmlspecialchars($order_detail['nama']); ?>!
            </p>
            <p>Booking Anda telah berhasil. Berikut detail pemesanan:</p>

            <ul class="list-group mb-4">
                <li class="list-group-item">
                    <strong>Order ID:</strong> <?= htmlspecialchars($order_detail['order_id']); ?>
                </li>

                <li class="list-group-item">
                    <strong>Paket:</strong> <?= htmlspecialchars($order_detail['varian']['paket']['nama']); ?>
                </li>

                <li class="list-group-item">
                    <strong>Varian:</strong> <?= htmlspecialchars($order_detail['varian']['nama']); ?>
                </li>

                <li class="list-group-item">
                    <strong>Extra Layanan:</strong>
                    <ul class="mt-2">
                        <?php foreach ($order_detail['extra_order'] as $extra): ?>
                            <li><?= htmlspecialchars($extra['extra']['nama']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="list-group-item">
                    <strong>Tanggal:</strong> <?= htmlspecialchars($order_detail['tanggal']); ?>
                </li>

                <li class="list-group-item">
                    <strong>Waktu:</strong> <?= htmlspecialchars($order_detail['waktu']); ?>
                </li>

                <li class="list-group-item">
                    <strong>Status Pembayaran:</strong> <?= htmlspecialchars($order_detail['status']); ?>
                </li>
            </ul>

            <div class="d-flex justify-content-between">
                <a href="/" class="btn btn-outline-dark px-4">Kembali ke Beranda</a>

                <!-- Tombol Download Invoice -->
                <a 
                    href="invoice.php?order_id=<?= urlencode($order_detail['order_id']); ?>" 
                    class="btn btn-dark px-4">
                    Download Invoice
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
