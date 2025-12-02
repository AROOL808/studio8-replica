<?php
session_start();
if(!isset($_SESSION["admin"])){
  header("Location: login.php");
  exit();
}
include 'database_handle.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $result = [];
    switch ($action) {

        // Paket
        case 'create_package':
            $result = create_package(
                $_POST['nama'],
                $_POST['deskripsi'] ?? '',
            );
            break;

        case 'update_package':
            $result = update_package(
                $_POST['paket_id'],
                $_POST['nama'],
                $_POST['deskripsi'] ?? '',
            );
            break;

        case 'delete_package':
            $result = delete_package($_POST['paket_id'] ?? $_GET['paket_id']);
            break;

        case 'create_variant':
            $result = create_variant(
                $_POST['paket_id'],
                $_POST['nama'],
                $_POST['harga'],
                $_POST['deskripsi'] ?? ''
            );
            break;

        case 'update_variant':
            $result = update_variant(
                $_POST['varian_id'],
                $_POST['nama'],
                $_POST['harga'],
                $_POST['deskripsi'] ?? '',
                $_POST['paket_id'] ?? null
            );
            break;

        case 'delete_variant':
            $result = delete_variant($_POST['varian_id'] ?? $_GET['varian_id']);
            break;

        default:
            $result = ['error' => 'Invalid action'];
            break;
    }
    echo json_encode($result);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        // paket 
        case 'get_all_packages':
            $result = get_all_package();
            break;

        case 'get_package_with_variants':
            $result = get_package_with_variants();
            break;

        case 'get_variants_by_package':
            $result = get_variants_by_package($_GET['paket_id']);
            break;

        default:
            $result = ['error' => 'Invalid action'];
            break;
    }
    echo json_encode($result);
    exit;
}

$package_list = get_package_with_variants()['data'] ?? [];
// $variant_list = get_all_variant()['data'] ?? [];
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clients — Studio 8</title>

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
</head>

<body class="bg-white text-dark" tabindex="-1" style="outline: none;">

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
                            <a href="packet-varian.php" class="nav-link p-2 fw-bold">Edit Paket</a>
                            <a href="extra-edit.php" class="nav-link p-2 ">Edit Extra</a>
                            <a href="giftcard-list.php" class="nav-link p-2">Daftar Giftcard</a>
                            <a href="logout.php"><button id="logoutBtn" class="btn btn-outline-dark w-100 mt-3">Logout</button></a>

                        </nav>
                        </div>
                    </div>
                    </aside>

                    <!-- Main: Clients list -->
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="fw-bold mb-0">Paket Foto</h3>
                                <p class="text-muted small mb-0">Kelola seluruh paket foto dan varian paket dalam satu
                                    halaman terpusat, sehingga semuanya lebih rapi dan mudah dipantau.</p>
                            </div>
                            <div class="w-50 ms-3 text-end">
                                <button type="button" id="addModalPackage" class="btn btn-dark">Tambah Paket</button>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div id="booking-form" class="booking-form p-4 shadow-sm" novalidate>
                                <!-- Paket -->
                                <div>
                                    <?php foreach ($package_list as $package): ?>
                                        <div class="package-section mb-4 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-center form-section">
                                                <div class="flex-grow-1">
                                                    <p class="fw-semibold mb-0 fs-5"><?= $package['nama'] ?></p>
                                                    <p class="text-muted small mb-2 me-2"><?= $package['deskripsi'] ?></p>
                                                </div>
                                                <div class="mt-2 d-flex justify-content-end gap-2">
                                                    <button type="button"
                                                        class="updateModalPackage btn btn-sm btn-outline-dark me-1 btn-detail"
                                                        data-id="<?= $package['paket_id'] ?>"
                                                        data-nama="<?= $package['nama'] ?>"
                                                        data-deskripsi="<?= $package['deskripsi'] ?>">Edit</button>
                                                    <button type="button"
                                                        class="deletePackage btn btn-sm btn-dark btn-action"
                                                        data-id="<?= $package['paket_id'] ?>"
                                                        data-nama="<?= $package['nama'] ?>">Hapus</button>
                                                </div>
                                            </div>
                                            <!-- Varian -->
                                            <div class="m-5 variant-section ps-3 border-start border-2">
                                                <?php if (!empty($package['variants'])): ?>
                                                    <?php foreach ($package['variants'] as $variant) : ?>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="flex-grow-1">
                                                                <p class="fw-semibold mb-0"><?= $variant['nama'] ?></p>
                                                                <p class="text-muted small mb-0">Rp <?= number_format($variant['harga'], 0, ',', '.' ) ?></p>
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="button"
                                                                    class="updateModalVariant btn btn-sm btn-outline-dark me-1 btn-detail"
                                                                    data-id="<?= $variant['varian_id'] ?>"
                                                                    data-package_id="<?= $package['paket_id'] ?>"
                                                                    data-nama="<?= $variant['nama'] ?>"
                                                                    data-harga="<?= $variant['harga'] ?>"
                                                                    data-deskripsi="<?= $variant['deskripsi'] ?>">Edit</button>
                                                                <button class="deleteVariant btn btn-sm btn-dark btn-action"
                                                                    data-id="<?= $variant['varian_id'] ?>"
                                                                    data-nama="<?= $variant['nama'] ?>">Hapus</button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="text-muted small">Belum ada varian untuk paket ini.</p>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-center align-items-center mt-3">
                                                    <button type="button"
                                                        class="addVariantToPackage btn btn-outline-dark px-5"
                                                        data-package-id="<?= $package['paket_id'] ?>"
                                                        data-package-name="<?= $package['nama'] ?>">
                                                        Tambah Varian
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Pop up form tambah edit paket -->
    <div id="modalTambahPaket" class="modal fade" tabindex="-1" aria-labelledby="modalPackageTitle">
        <div class="modal-dialog modal-dialog-scrollable d-flex align-items-center justify-content-center">
            <div class="modal-content p-4 rounded-4 shadow-lg" style="max-width:32rem; opacity: 1; transform: none;">
                <div class="modal-header border-0 d-flex align-items-start justify-content-between mb-3">
                    <h2 id="modalPackageTitle" class="fs-4 fw-bold text-dark">Tambah Paket</h2>
                    <button type="button" data-bs-dismiss="modal" aria-label="Close"
                        class="p-1 btn-close text-secondary rounded-circle btn btn-light transition"></button>
                </div>
                <div class="modal-body p-0">
                    <form id="formPackage" method="POST">
                        <input type="hidden" name="paket_id" id="packageId">

                        <div>
                            <label class="form-label" for="packageName">Nama</label>
                            <input type="text" id="packageName" name="nama" required="" class="form-control" value="">
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label" for="packageDescription">Deskripsi (Opsional)</label>
                            </div>
                            <textarea id="packageDescription" name="deskripsi" rows="3" class="form-control"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-3 pt-4">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light px-4 py-2">
                                Batal
                            </button>
                            <button type="submit" id="btnSavePackage" class="btn btn-primary px-4 py-2">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pop up form tambah edit varian -->
    <div id="modalTambahVarian" class="modal fade" tabindex="-1" aria-labelledby="modalVariantTitle">
        <div class="modal-dialog modal-dialog-scrollable d-flex align-items-center justify-content-center">
            <div class="modal-content p-4 rounded-4 shadow-lg" style="max-width:32rem; opacity: 1; transform: none;">
                <div class="modal-header border-0 d-flex align-items-start justify-content-between mb-3">
                    <h2 id="modalVariantTitle" class="fs-4 fw-bold text-dark">Tambah Varian</h2>
                    <button type="button" data-bs-dismiss="modal"
                        class="btn-close p-1 text-secondary rounded-circle btn btn-light transition"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <form id="formVariant" method="POST" action="packet-varian.php?action=create_variant">
                        <input type="hidden" id="variantId" name="varian_id">
                        <input type="hidden" id="variantPackageId" name="paket_id">
                        <div>
                            <label class="form-label" for="variantName">Nama</label>
                            <input id="variantName" name="nama" type="text" required="" class="form-control" value="">
                        </div>
                        <div>
                            <label class="form-label" for="variantPrice">Harga</label>
                            <input id="variantPrice" type="number" name="harga" required="" class="form-control" value="">
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label" for="variantDescription">Deskripsi (Opsional)</label>
                            </div>
                            <textarea id="variantDescription" rows="3" name="deskripsi" class="form-control"></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-3 pt-4">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light px-4 py-2">
                                Batal
                            </button>
                            <button type="submit" id="btnSaveVariant" class="btn btn-primary px-4 py-2">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle (Popper + JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Reuse site script (script.js) -->
    <script src="script.js"></script>
</body>

</html>