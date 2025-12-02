<?php
include 'database_handle.php';
// Proses penghapusan order jika parameter delete ada di URL
if(isset($_GET['delete'])){
  $response = supabaseRequest("DELETE", "giftcard", [
    "giftcard_id" => "eq." . $_GET['delete']
  ]);
  // Redirect kembali ke halaman clients.php setelah penghapusan
  header("Location: giftcard-list.php");
  exit();
}

//fetch daftar clients
$giftcards = get_giftcard_data()['data'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Giftcards — Studio 8</title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-white text-dark">

  <!-- Header -->
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
    <section class="py-5">
      <div class="container">
        <div class="row g-4">
          <!-- Sidebar -->
          <aside class="col-md-3">
            <div class="card sidebar-card shadow-sm sticky-top">
              <div class="card-body">
                <h6 class="mb-3">Admin</h6>
                <nav class="nav flex-column">
                  <a href="clients.php" class="nav-link p-2">Daftar Client</a>
                  <a href="packet-varian.php" class="nav-link p-2">Edit Paket</a>
                  <a href="extra-edit.php" class="nav-link p-2 ">Edit Extra</a>
                  <a href="giftcard-list.php" class="nav-link p-2 fw-bold">Daftar Giftcard</a>
                  <a href="logout.php"><button id="logoutBtn" class="btn btn-outline-dark w-100 mt-3">Logout</button></a>
                </nav>
              </div>
            </div>
          </aside>

          <!-- Main: Clients list -->
          <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h3 class="fw-bold mb-0">Daftar Giftcard</h3>
                <p class="text-muted small mb-0">Semua booking terbaru dan statusnya. Klik "Detail" untuk membuka informasi lengkap.</p>
              </div>
              <div class="w-50 ms-3">
                <input id="clientSearch" class="form-control" placeholder="Cari nama, paket, atau status..." />
              </div>
            </div>

            <div id="clientsList" class="row g-3">
              <!-- daftar client -->
              <?php foreach($giftcards as $giftcard): ?>
               <div class="client-card d-flex gap-3 align-items-start">
                 <div class="flex-grow-1">
                   <div class="d-flex justify-content-between align-items-start">
                     <div>
                       <h5 class="mb-1"><?=$giftcard['code']?></h5>
                       <div class="client-meta"><?=$giftcard['varian']['paket']['nama']?> • <?=$giftcard['varian']['nama']?> • <strong> <?=$giftcard['giftcard_status']?></strong></div>
                       <div class="client-meta">Layanan Tambahan: | 
                        <?php foreach($giftcard['extra_order'] as $extra):?>
                         <?=$extra['extra']['nama']?> | 
                        <?php endforeach;?>
                        </div>
                     </div>
                     <div class="text-end">
                       <div class="mt-2">
                         <a href="?delete=<?=$giftcard['giftcard_id']?>"><button class="btn btn-sm btn-dark btn-action" data-id="${c.id}">Hapus</button></a>
                       </div>
                     </div>
                   </div>
   
                   <div id="detail-${c.id}" class="client-detail collapse">
                     <div><strong>Email:</strong> ${c.email}</div>
                     <div><strong>Telepon:</strong> ${c.phone}</div>
                     <div><strong>Pembayaran:</strong> ${c.payment}</div>
                     <div><strong>Catatan:</strong> ${c.note || '-'}</div>
                   </div>

                 </div>
               </div>
              <?php endforeach; ?>
            </div>

            <div id="emptyState" class="text-center text-muted mt-4" style="display:none;">
              Tidak ada klien yang cocok.
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Bootstrap Bundle (Popper + JS) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Reuse site script (script.js) -->
  <script src="script.js"></script>
</body>
</html>
