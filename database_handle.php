<?php

/**
 * Load environment variable from getenv() or .env file
 */
function loadEnvValue($key) {
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

function insert_data_order($varian_id ,$nama, $email, $nomor_hp, $tanggal, $waktu)
{
    $body = [
        "varian_id" => $varian_id,
        "nama"      => $nama,
        "email"     => $email,
        "nomor_hp"  => $nomor_hp,
        "tanggal"   => $tanggal,
        "waktu"     => $waktu
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

?>
