<?php
require "database_handle.php"; // your supabaseRequest() file

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $response = supabaseRequest("GET", "admin", [
        "select" => "username,password",
        "username" => "eq." . $username
    ]);

    if (empty($response["data"])) {
        $error = "Username tidak ditemukan";
    } else {
        $admin = $response["data"][0];

        if ($password === $admin["password"]) {
            $_SESSION["admin"] = $admin["username"];
            header("Location: clients.php");
            exit;
        } else {
            $error = "Password salah";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
   

</head>
<body>

<div class="login-card">

    <div class="login-title">Admin Login</div>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" type="text" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
        </div>

        <button class="btn btn-dark-custom w-100 mt-2">Login</button>

    </form>
</div>

</body>
</html>
