<?php
session_start();
include_once 'database_handle.php';

function redeemGiftcard($code, $fullName, $email, $phone, $date, $time)
{
    // 1. CEK GIFT CARD
    $gc = supabaseRequest("GET", "giftcard", [
        "select" => "*",
        "code"   => "eq.$code"
    ]);

    if (empty($gc['data'])) {
        return ["error" => "Giftcard tidak ditemukan"];
    }

    $giftcard = $gc['data'][0];

    if ($giftcard["giftcard_status"] !== "PAID") {
        return ["error" => "Giftcard belum dibayar atau sudah digunakan"];
    }

    $order_id = rand();
    // 2. BUAT ORDER BARU (STATUS = BOOKED)
    $newOrder = supabaseRequest("POST", "order", [], [
        "order_id"  => $order_id,
        "varian_id" => $giftcard["varian_id"],
        "nama"      => $fullName,
        "email"     => $email,
        "nomor_hp"  => $phone,
        "tanggal"   => $date,
        "waktu"     => $time,
        "giftcard_id" => $giftcard["giftcard_id"],
        "status" => "BOOKED"
    ]);

    if (empty($newOrder["data"])) {
        return ["error" => "Gagal membuat order"];
    }

    $order_id = $newOrder["data"][0]["order_id"];

    // 3. UPDATE TABEL EXTRA ORDER
    //    Set order_id berdasarkan giftcard_id
    $updateExtras = supabaseRequest("PATCH", "extra_order", [
        "giftcard_id" => "eq." . $giftcard["giftcard_id"]
    ], [
        "order_id" => $order_id
    ]);

    // optional: cek error
    // if (!empty($updateExtras["error"])) {}

    // 4. UPDATE STATUS GIFT CARD MENJADI "USED"
    $useGC = supabaseRequest("PATCH", "giftcard", [
        "giftcard_id" => "eq." . $giftcard["giftcard_id"]
    ], [
        "giftcard_status" => "USED"
    ]);

    return [
        "status" => 200,
        "message" => "Redeem giftcard berhasil",
        "order_id" => $order_id
    ];
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Proses redeem giftcard
    $result = redeemGiftcard(
        $_POST['giftcardCode'],
        $_POST['fullName'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['date'],
        $_POST['time']
    );

    if(isset($result['error'])){
        // Tangani error (misal: tampilkan pesan error)
        var_dump($result['error']);
    } else {
        // Redirect ke halaman success dengan order_id
        $orderId = $result['order_id'];
        header("Location: success.php?order_id=$orderId");
        exit();
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Booking — Studio 8</title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-white text-dark">

  <header class="site-header fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
          <div class="logo-box">8</div>
          <span class="brand-text">studio 8</span>
        </a>
      </div>
    </nav>
  </header>

  <main class="pt-5 mt-4">
    <section class="py-5 booking-section bg-gray">
      <div class="container">
        <div class="row g-4">
          <!--  Form booking (diperluas & dipusatkan) -->
          <div class="col-lg-8 offset-lg-2">
            <h3 class="fw-bold mb-3">Tukar Giftcard</h3>

            <form id="booking-form" class="booking-form card p-4 shadow-sm" action="" method="POST">
            <!-- Personal data -->
              <div id="personal-data" class="mb-3 form-section">
                <label class="form-label fw-semibold">Data Diri <span class="text-danger" id="pd-required">*</span></label>
                <div class="mb-2">
                  <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Nama lengkap" required>
                </div>
                <div class="mb-2">
                  <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="mb-0">
                  <input type="tel" class="form-control" id="phone" name="phone" placeholder="No. Handphone" required>
                </div>
              </div>

              <!-- Date & time (wajib jika not giftcard) -->
              <div id="schedule" class="mb-3 form-section">
                <label class="form-label fw-semibold">Tanggal & Waktu Booking <span class="text-danger" id="dt-required">*</span></label>
                <div class="d-flex gap-2">
                  <input type="date" id="date" name="date" class="form-control" required>
                  <input type="time" id="time" name="time" class="form-control" required>
                </div>
                <small class="form-text text-muted">Pilih tanggal dan jam yang tersedia. Konfirmasi akhir akan dikirim via email.</small>
              </div>

              <!-- Metode Pembayaran (diganti jadi input Kode Giftcard) -->
              <div class="mb-3 form-section">
                <label for="giftcardCode" class="form-label fw-semibold">Kode Giftcard <span class="text-danger">*</span></label>
                <input type="text" id="giftcardCode" name="giftcardCode" class="form-control" placeholder="Masukkan kode giftcard" required>
                <small class="form-text text-muted">Masukkan kode giftcard untuk menebus paket. Pastikan kode valid dan lengkap.</small>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <a href="index.html" class="btn btn-outline-dark">Kembali</a>
                <button type="submit" class="btn btn-dark">Konfirmasi Booking</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Bootstrap Bundle (Popper + JS) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Booking JS -->
  <script src="script.js"></script>
</body>
</html>
