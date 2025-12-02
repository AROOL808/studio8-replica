<?php
session_start();
if(!isset($_SESSION["admin"])){
  header("Location: login.php");
  exit();
}
// config.php - Konfigurasi database Supabase
define('SUPABASE_URL', loadEnvValue('SUPABASE_URL'));
define('SUPABASE_KEY', loadEnvValue('SECRET_KEY'));

function loadEnvValue($key)
{
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;

    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) return '';

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, $key . '=') === 0) {
            return trim(substr($line, strlen($key) + 1), "\"' ");
        }
    }
    return '';
}
// Fungsi untuk melakukan request ke Supabase REST API
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && in_array($method, ['POST', 'PATCH', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    throw new Exception('Request failed: ' . $response);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'load':
                $extras = supabaseRequest('extra?select=*&order=created_at.desc');
                echo json_encode(['success' => true, 'data' => $extras]);
                break;
                
            case 'add':
                $newExtra = [
                    'nama' => $_POST['name'],
                    'deskripsi' => $_POST['description'],
                    'harga' => (int)$_POST['price']
                ];
                $result = supabaseRequest('extra', 'POST', $newExtra);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'update':
                $updateData = [
                    'nama' => $_POST['name'],
                    'deskripsi' => $_POST['description'],
                    'harga' => (int)$_POST['price']
                ];
                $result = supabaseRequest('extra?extra_id=eq.' . (int)$_POST['id'], 'PATCH', $updateData);
                echo json_encode(['success' => true, 'data' => $result]);
                break;
                
            case 'delete':
                supabaseRequest('extra?extra_id=eq.' . (int)$_POST['id'], 'DELETE');
                echo json_encode(['success' => true]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Extra — Studio 8</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="bg-white text-dark extra-edit-page">

  <!-- Header -->
  <header class="site-header fixed-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
          <div class="logo-box">8</div>
          <span class="brand-text">studio 8</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
          <div class="navbar-nav gap-2">
            <a href="index.html"><button class="btn btn-outline-dark nav-btn">Home</button></a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main class="pt-5 mt-4">
    <section class="py-5">
      <div class="container">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <div class="row g-4">
          <!-- Sidebar -->
          <aside class="col-md-3">
            <div class="card sidebar-card shadow-sm sticky-top">
              <div class="card-body">
                <h6 class="mb-3">Admin</h6>
                <nav class="nav flex-column">
                  <a href="clients.php" class="nav-link p-2">Daftar Client</a>
                  <a href="packet-varian.php" class="nav-link p-2">Edit Paket</a>
                  <a href="extra-edit.php" class="nav-link p-2 fw-bold">Edit Extra</a>
                  <a href="giftcard-list.php" class="nav-link p-2">Daftar Giftcard</a>
                  <a href="logout.php"><button id="logoutBtn" class="btn btn-outline-dark w-100 mt-3">Logout</button></a>
                </nav>
              </div>
            </div>
          </aside>

          <!-- Main Content -->
          <div class="col-md-9">
            <h3 class="page-title">Edit Extra</h3>

            <!-- Add New Extra Form -->
            <div class="add-extra-card">
              <h5>Tambah Extra Baru</h5>
              
              <form id="addExtraForm">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Nama Extra</label>
                    <input type="text" class="form-control" id="extraName" placeholder="Contoh: Cetak Foto 10R" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" id="extraPrice" placeholder="50000" min="0" required>
                  </div>
                  
                  <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="extraDescription" rows="3" placeholder="Deskripsi detail tentang extra ini..."></textarea>
                  </div>
                  
                  <div class="col-12">
                    <button type="submit" class="btn btn-add-extra" id="submitBtn">
                      <span class="btn-text">Tambah Extra</span>
                      <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <!-- Extra List -->
            <div class="section-header">
              <h5>Daftar Extra</h5>
              <span class="badge-count" id="extraCount">0 Item</span>
            </div>
            
            <div id="extraList">
              <!-- Loading state -->
              <div class="loading-state">
                <div class="spinner-border" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Memuat data...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Edit Modal -->
  <div class="modal fade" id="editExtraModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Extra</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editExtraForm">
            <input type="hidden" id="editExtraId">
            
            <div class="mb-3">
              <label class="form-label">Nama Extra</label>
              <input type="text" class="form-control" id="editExtraName" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Harga (Rp)</label>
              <input type="number" class="form-control" id="editExtraPrice" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Deskripsi</label>
              <textarea class="form-control" id="editExtraDescription" rows="3"></textarea>
            </div>
            
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-add-extra" id="updateBtn">
                <span class="btn-text">Simpan Perubahan</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JavaScript for Extra Edit -->
  <script src="extra-edit.js?v=<?php echo time(); ?>"></script>
</body>
</html>