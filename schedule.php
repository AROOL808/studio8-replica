<?php
// Fetch data dari Supabase via PHP
$supabase_url = 'https://hqmazwavlgfjgofpdwpb.supabase.co';
$supabase_key = 'sb_secret_WuVvAyWOWOxknywSkmL_0w_D9ZXMB1i';

function getBookingsFromSupabase() {
    global $supabase_url, $supabase_key;
    
    $url = $supabase_url . '/rest/v1/order?select=*';
    
    $options = [
        'http' => [
            'header' => [
                "apikey: " . $supabase_key,
                "Authorization: Bearer " . $supabase_key,
                "Content-Type: application/json"
            ],
            'method' => 'GET'
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        error_log("Failed to fetch from Supabase");
        return [];
    }
    
    return json_decode($result, true);
}

// Ambil data booking
$bookings = getBookingsFromSupabase();

// Debug: uncomment untuk lihat data
// echo "<pre>"; print_r($bookings); echo "</pre>"; exit;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kalender — Studio 8</title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="bg-white text-dark calendar-page">

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
            <button class="btn btn-outline-dark nav-btn">Tentang Kami</button>
            <a href="booking.php?paket_id=1"><button class="btn btn-outline-dark nav-btn">Booking</button></a>
            <a href="giftcard.html"><button class="btn btn-outline-dark nav-btn">Giftcard</button></a>
            <a href="schedule.php"><button class="btn btn-outline-dark nav-btn">Kalender</button></a>
            <a href="showcase.html"><button class="btn btn-outline-dark nav-btn">Showcase</button></a>
            <a href="clients.html"><button class="btn btn-dark text-white nav-btn">Login</button></a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <main class="pt-5 mt-4">
    <section id="main-content" class="py-4">
      <div class="calendar-hero">
        <h1>📸 Jadwal Studio</h1>
        <p>Pilih tanggal untuk melihat ketersediaan sesi fotografi</p>
      </div>

      <div class="container calendar-container">
        <div class="calendar-card">
          <div class="calendar-header">
            <div class="calendar-nav">
              <button id="prevBtn" type="button">←</button>
              <h2 id="monthLabel" class="month-year">Loading...</h2>
              <button id="nextBtn" type="button">→</button>
            </div>
            
            <div class="weekdays">
              <div class="weekday">Min</div>
              <div class="weekday">Sen</div>
              <div class="weekday">Sel</div>
              <div class="weekday">Rab</div>
              <div class="weekday">Kam</div>
              <div class="weekday">Jum</div>
              <div class="weekday">Sab</div>
            </div>
          </div>

          <div id="calendarGrid" class="calendar-grid"></div>

          <div class="calendar-footer">
            <div class="calendar-title">
              <span class="icon">📷</span>
              <strong>Ketersediaan Sesi Fotografi</strong>
            </div>
            <div class="legend">
              <div class="legend-item">
                <div class="legend-dot today"></div>
                <span><strong>Hari Ini</strong></span>
              </div>
              <div class="legend-item">
                <div class="legend-dot available"></div>
                <span>Slot Tersedia</span>
              </div>
              <div class="legend-item">
                <div class="legend-dot booked"></div>
                <span>📸 Ada Booking</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Pass PHP data to JavaScript -->
  <script>
    const bookingsFromPHP = <?php echo json_encode($bookings); ?>;
  </script>
  
  <script src="schedule.js"></script>
</body>
</html>