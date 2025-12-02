<?php
session_start();
include 'database_handle.php';
require_once dirname(__FILE__) . '/payment-gateway/Midtrans.php';
//fetch nama paket
$daftar_paket = get_data("paket", "nama,paket_id,deskripsi")['data'];
$daftar_extra = get_data("extra", "*")['data'];

//fetch varian paket dimana paket id sesuai parameter di url
$varian_paket = get_data("varian", "*", [
    "paket_id" => "eq." . ($_GET['paket_id'] ?? 1)
])['data'];

function generateUniqueGiftcardCode($length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charLength = strlen($characters);

    while (true) {
        // Generate random string
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $charLength - 1)];
        }

        // CEK ke Supabase apakah kode sudah ada
        $check = supabaseRequest("GET", "giftcard", [
            "select" => "code",
            "code"   => "eq.$result"
        ]);

        // Jika tidak ada data -> aman, return kode
        if (empty($check["data"])) {
            return $result;
        }

        // Jika ada → loop akan ulang dan generate string baru
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Proses form submission
    $giftcard_id = "GC" . rand();
    $varian_id = $_POST['varian'];
    $paket_id = $_POST['paket_id'];

    $code = generateUniqueGiftcardCode(8);
    // Insert giftcard data
    $response = insert_data_giftcard($giftcard_id, $code, $varian_id, 'PENDING');
    $extras = [];

    // Loop semua $_POST untuk mencari key yang mulai dengan "extra"
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'extra') === 0) { // key dimulai dengan 'extra'
            $extras[] = $value; // value adalah extra_id
        }
    }
      // Insert setiap extra ke tabel extra_order
    foreach ($extras as $extra_id) {
      $response = insert_extra_order(null, $extra_id, $giftcard_id);
  }

  Midtrans\Config::$serverKey = loadEnvValue('SERVER_KEY');
  Midtrans\Config::$isProduction = false;
  Midtrans\Config::$is3ds = true;
  Midtrans\Config::$isSanitized = true;

  
  $total_extra = 0;
  if($extras != []){
    
      // Fetch harga extra secara bulk
      $data_harga_extra = get_extra_prices($extras);
    foreach ($data_harga_extra['data'] as $row) {
        $total_extra += $row['harga'];
    }
  }

  // hitung harga varian 
  $data_varian = get_data_varian($_POST['varian'])['data'][0];
  $harga_varian = intval($data_varian['harga']);
  // total harga
  $total_harga = $harga_varian + $total_extra;

  $params = array(
    'transaction_details' => array(
        'order_id' => $giftcard_id,
        'gross_amount' => $total_harga
    ),'items_details' => array(
        array(
            'id' => $_POST['varian'],
            'price' => $harga_varian,
            'quantity' => 1,
            'name' => $data_varian['nama']
        )
    )

  );

  try {
    // Get Snap Payment Page URL
    $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
    
    // Redirect to Snap Payment Page
    header('Location: ' . $paymentUrl);
  }
  catch (Exception $e) {
    echo $e->getMessage();
  }

}

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Buy Giftard — Studio 8</title>

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
          <!-- LEFT: Paket -->
          <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Pilih Paket</h3>

            <div class="list-group package-list"> 
              <?php foreach($daftar_paket as $paket):?>
              <a class="text-decoration-none" href='giftcard.php?paket_id=<?=$paket['paket_id']?>'>
                <label class="list-group-item package-card mb-3 <?= $_GET['paket_id'] == $paket['paket_id'] ? 'selected' : ''?>" data-package="graduation">
                  <div>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h5 class="mb-1"><?=$paket['nama']?></h5>
                        <small class="text-muted"><?=$paket['deskripsi']?></small>
                      </div>
                    </div>
                  </div>
                </label>
              </a>
              <?php endforeach;?>

              <!-- Tambah paket tambahan jika perlu -->
            </div>

            <p class="small text-muted mt-2">Pilih paket terlebih dahulu untuk melanjutkan pengisian detail di kanan.</p>
          </div>

          <!-- RIGHT: Form booking -->
          <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Form Giftcard</h3>
            <div>
              <div class="d-flex justify-content-end mb-3">
                <a href="giftcard-redeem.php" class="btn btn-dark text-white px-3 py-2">
                  Redeem Giftcard
                </a>
              </div>
            </div>
            <form id="booking-form" class="booking-form card p-4 shadow-sm" action="" method="POST">
              <!-- Variant (wajib) - DIUBAH: radio dengan deskripsi dan keterangan harga -->
              <div class="mb-3 form-section">
                <label class="form-label fw-semibold">Varian Paket <span class="text-danger">*</span></label>

                <div class="list-group">
                  <?php foreach($varian_paket as $varian):?>
                  <label class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="form-check">
                      <input type="hidden" name="paket_id" value="<?=$_GET['paket_id']?>">
                      <input class="form-check-input" type="radio" name="varian" id="varian-<?=$varian['varian_id']?>" value="<?=$varian['varian_id']?>" required>
                      <label class="form-check-label ms-2" for="varian-<?=$varian['varian_id']?>">
                        <strong><?=$varian['nama']?></strong>
                        <div class="text-muted small"><?= $varian['deskripsi']?></div>
                      </label>
                    </div>
                    <div class="text-end small text-muted" aria-hidden="true">Rp <?=$varian['harga']?></div>
                  </label>
                  <?php endforeach;?>

                </div>

                <small class="form-text text-muted">Keterangan harga: nilai yang tampil adalah tambahan pada harga paket dasar. Total akhir akan dikonfirmasi lewat email.</small>
              </div>

              <!-- Extra (opsional radio) -->
              <div class="mb-3 form-section">
                <label class="form-label fw-semibold">Extra (opsional)</label>
                <div>
                  <?php foreach($daftar_extra as $extra):?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="extra<?=$extra['extra_id']?>" id="extra-<?=$extra['extra_id']?>" value="<?=$extra['extra_id']?>">
                    <label class="form-check-label" for="extra-<?=$extra['extra_id']?>"><?=$extra['nama']?> (+Rp <?=$extra['harga']?>)</label>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <a href="index.html" class="btn btn-outline-dark">Kembali</a>
                <button type="submit" class="btn btn-dark">Konfirmasi Pembelian</button>
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
