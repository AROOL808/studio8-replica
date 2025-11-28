<?php
require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once 'database_handle.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();

// Ambil data booking (contoh)
$order = get_order_detail($_GET['order_id'])['data'][0]; // pakai variabel kamu

// HTML INVOICE
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; padding: 20px; }
    .header {
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 3px solid #000;
    }
    .title { font-size: 22px; font-weight: bold; margin-top: 10px; }
    .business {
        margin-top: 5px;
        font-size: 12px;
        color: #555;
    }
    .section-title {
        margin-top: 25px;
        font-size: 16px;
        font-weight: bold;
        border-left: 4px solid black;
        padding-left: 8px;
        color: black;
    }
    table {
        width: 100%;
        margin-top: 10px;
        border-collapse: collapse;
        font-size: 14px;
    }
    td {
        padding: 6px 4px;
        vertical-align: top;
    }
    ul {
        margin-top: 5px;
    }
    .footer {
        margin-top: 40px;
        text-align: center;
        font-size: 12px;
        color: #666;
    }
</style>
</head>
<body>

<div class="header">
    <div class="title">INVOICE PEMESANAN</div>
    <div class="business">
        <strong>Studio8 Photography</strong><br>
        Jl. Banjar - Pangandaran (Depan SMK 4 Banjar, Sukamukti, Kec. Pataruman, Kota Banjar, Jawa Barat 46323<br>
        Telp: 0812-3456-7890 — Email: studiodelapan@example.com
    </div>
</div>

<div class="section-title">Data Pemesan</div>
<table>
    <tr><td>Nama</td><td>: '.$order["nama"].'</td></tr>
    <tr><td>Order ID</td><td>: '.$order["order_id"].'</td></tr>
</table>

<div class="section-title">Detail Pemesanan</div>
<table>
    <tr><td>Paket</td><td>: '.$order["varian"]["paket"]["nama"].'</td></tr>
    <tr><td>Varian</td><td>: '.$order["varian"]["nama"].'</td></tr>
    <tr><td>Tanggal</td><td>: '.$order["tanggal"].'</td></tr>
    <tr><td>Waktu</td><td>: '.$order["waktu"].'</td></tr>
    <tr><td>Status Pembayaran</td><td>: '.$order["status"].'</td></tr>
</table>

<div class="section-title">Extra Layanan</div>
<ul>';
foreach ($order["extra_order"] as $extra) {
    $html .= "<li>" . $extra["extra"]["nama"] . "</li>";
}
$html .= '
</ul>

<div class="footer">
    Terima kasih telah mempercayai StudioDelapan Photography.
</div>

</body>
</html>
';

// Render PDF
$dompdf->loadHtml($html);
$dompdf->setPaper("A4");
$dompdf->render();
$dompdf->stream("invoice-".$order["order_id"].".pdf", ["Attachment" => true]);
