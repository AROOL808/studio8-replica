<?php

/**
 * Load environment variable from getenv() or .env file
 */
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

/**
 * Initialize Supabase Credentials
 */
$PROJECT_URL = rtrim(loadEnvValue('PROJECT_URL'), '/');
$SECRET_KEY  = loadEnvValue('SECRET_KEY');

if (!$PROJECT_URL || !$SECRET_KEY) {
    die("Missing PROJECT_URL or SECRET_KEY\n");
}

/**
 * Reusable Supabase Request Function
 */
function supabaseRequest($method, $endpoint, $params = [], $body = null)
{
    global $PROJECT_URL, $SECRET_KEY;

    // Build URL
    $url = $PROJECT_URL . "/rest/v1/" . $endpoint;

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    $headers = [
        "Authorization: Bearer " . $SECRET_KEY,
        "apikey: " . $SECRET_KEY,
        "Accept: application/json",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ["error" => $err];
    }

    $data = json_decode($response, true);

    return [
        "status" => $httpCode,
        "data"   => $data
    ];
}

/**
 * GET data from table
 * Example: get_data('paket')
 */
function get_data($table, $select = "*", $filters = [])
{
    return supabaseRequest("GET", $table, array_merge(["select" => $select], $filters));
}

/**
 * UPDATE data
 * Example: update_data('paket', ['id' => 'eq.1'], ['harga' => 150000]);
 */
function update_data($table, $filters = [], $body = [])
{
    return supabaseRequest("PATCH", $table, $filters, $body);
}

/**
 * DELETE data
 * Example: delete_data('paket', ['id' => 'eq.1']);
 */
function delete_data($table, $filters = [])
{
    return supabaseRequest("DELETE", $table, $filters);
}

function insert_data_order($order_id, $varian_id, $nama, $email, $nomor_hp, $tanggal, $waktu, $status =  'PENDING')

{
    $body = [
        "order_id"  => $order_id,
        "varian_id" => $varian_id,
        "nama"      => $nama,
        "email"     => $email,
        "nomor_hp"  => $nomor_hp,
        "tanggal"   => $tanggal,
        "waktu"     => $waktu,
        "status"    => $status
    ];

    return supabaseRequest("POST", "order", [], $body);
}

function insert_extra_order($order_id, $extra_id)
{
    $body = [
        "order_id" => $order_id,
        "extra_id" => $extra_id
    ];

    return supabaseRequest("POST", "extra_order", [], $body);
}

function get_order_data($filters = [])
{
    return supabaseRequest("GET", "order", array_merge([
        "select" => "*, varian(*, paket(*)), extra_order(*, extra(*))"
    ], $filters));
}

function get_order_detail($order_id, $filters = [])
{
    return supabaseRequest("GET", "order", array_merge([
        "select" => "*, varian(*, paket(*)), extra_order(*, extra(*))",
        "order_id" => "eq." . $order_id
    ], $filters));
}

function get_extra_prices($extras = [])
{
    return supabaseRequest("GET", "extra", array_merge([
        "select" => "extra_id, nama, harga",
        "extra_id" => "in.(" . implode(",", $extras) . ")"


    ]));
}

function get_data_varian($varian_id)
{
    return supabaseRequest("GET", "varian", [
        "select"   => "*",
        "paket_id" => "eq." . $varian_id
    ]);
}

function update_order_status($order_id, $status)
{
    return supabaseRequest("PATCH", "order", [
        "order_id" => "eq." . $order_id
    ], [
        "status" => $status
    ]);

    // Paket
    function get_all_package()
    {
        return supabaseRequest("GET", "paket", [], []);
    }

    function get_package_with_variants()
    {
        $packages = supabaseRequest("GET", "paket", ["select" => "*, varian(nama, harga, varian_id,deskripsi)"], []);
        if (isset($packages['error'])) {
            return $packages;
        }

        if (isset($packages['data'])) {
            foreach ($packages['data'] as &$package) {
                $package['variants'] = $package['varian'] ?? [];
                unset($package['varian']);
            }
        }
        return $packages;
    }

    function get_package_by_id($paket_id)
    {
        return supabaseRequest("GET", "paket", ["paket_id" => "eq." . $paket_id], []);
    }

    function create_package($nama, $deskripsi = "")
    {
        $body = [
            "nama" => $nama,
            "deskripsi" => $deskripsi,
            // "jenis_paket" => $jenis_paket,
            // "grup" => $grup,
            "created_at" => date("c")
        ];
        return supabaseRequest("POST", "paket", [], $body);
    }

    function update_package($paket_id, $nama, $deskripsi = "")
    {
        $body = [
            "nama" => $nama,
            "deskripsi" => $deskripsi,
            // "jenis_paket" => $jenis_paket,
            // "grup" => $grup
        ];
        return supabaseRequest("PATCH", "paket", ["paket_id" => "eq." . $paket_id], $body);
    }

    function delete_package($paket_id)
    {
        return supabaseRequest("DELETE", "paket", ["paket_id" => "eq." . $paket_id]);
    }

    // Varian
    function get_all_variant()
    {
        return supabaseRequest("GET", "varian", [], []);
    }

    function get_variants_by_package($paket_id)
    {
        return supabaseRequest("GET", "varian", ["paket_id" => "eq." . $paket_id], []);
    }

    function get_variant_by_id($varian_id)
    {
        return supabaseRequest("GET", "varian", ["varian_id" => "eq." . $varian_id], []);
    }

    function create_variant($paket_id, $nama, $harga, $deskripsi = "")
    {
        $body = [
            "paket_id" => $paket_id,
            "nama" => $nama,
            "harga" => $harga,
            "deskripsi" => $deskripsi,
            "created_at" => date("c")
        ];
        return supabaseRequest("POST", "varian", [], $body);
    }

    function update_variant($varian_id, $nama, $harga, $deskripsi = "", $paket_id = null)
    {
        $body = [
            "nama" => $nama,
            "harga" => $harga,
            "deskripsi" => $deskripsi
        ];

        if ($paket_id) {
            $body['paket_id'] = $paket_id;
        }
        return supabaseRequest("PATCH", "varian", ["varian_id" => "eq." . $varian_id], $body);
    }

    function delete_variant($varian_id)
    {
        return supabaseRequest("DELETE", "varian", ["varian_id" => "eq." . $varian_id]);
    }
}
/* ------------------------------------------------------
   Example usage (you can remove these examples)    
------------------------------------------------------- */

// 1. GET all paket
// $result = get_data("paket");
// var_dump($result);

// 2. UPDATE paket where id = 5
// $result = update_data("paket", ["id" => "eq.5"], ["nama" => "New Paket Name"]);
// var_dump($result);

// 3. DELETE paket where id = 10
// $result = delete_data("paket", ["id" => "eq.10"]);
// var_dump($result);

// $paket = get_data("extra", "*")['data'];
// var_dump($paket);
