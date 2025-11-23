<?php
include 'database_handle.php';
//fetch nama paket
$daftar_paket = get_data("paket", "nama,paket_id,deskripsi")['data'];
$daftar_extra = get_data("extra", "*")['data'];

//fetch varian paket dimana paket id sesuai parameter di url
$varian_paket = get_data("varian", "*", [
    "paket_id" => "eq." . ($_GET['paket_id'] ?? 1)
])['data'];


if(isset($_POST)){
    $response = insert_data_order(
        $_POST['varian'],
        $_POST['fullName'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['date'],
        $_POST['time']
    );
    // var_dump($response);
    // Lakukan proses insert extra jika ada
    $extras = [];

    // Loop semua $_POST untuk mencari key yang mulai dengan "extra"
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'extra') === 0) { // key dimulai dengan 'extra'
            $extras[] = $value; // value adalah extra_id
        }
    }

    // Setelah order berhasil dibuat
    $order_id = $response['data'][0]['order_id'];

    // Insert setiap extra ke tabel extra_order
    foreach ($extras as $extra_id) {
      $response = insert_extra_order($order_id, $extra_id);
      var_dump($response);
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
          <!-- LEFT: Paket -->
          <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Pilih Paket</h3>

            <div class="list-group package-list"> 
              <?php foreach($daftar_paket as $paket):?>
              <a class="text-decoration-none" href='booking.php?paket_id=<?=$paket['paket_id']?>'>
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
            <h3 class="fw-bold mb-3">Form Booking</h3>

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

              <!-- Metode Pembayaran (dipindah & diubah jadi dropdown) -->
              <div class="mb-3 form-section">
                <label for="payment" class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                <select id="payment" name="payment" class="form-select" required>
                  <option value="" selected disabled>Pilih metode pembayaran</option>
                  <option value="bank">Transfer Bank</option>
                  <option value="bank">Dana</option>
                  <option value="bank">Spay</option>
                  <option value="bank">OVO</option>
                  <option value="bank">Gopay</option>
                </select>
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
