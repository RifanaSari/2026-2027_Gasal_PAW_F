<?php
require_once 'session.php';

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container login">
    <h2>Login Sistem Penjualan</h2>

    <?php if ($error === 'invalid') { ?>
        <p class="pesan">Username atau password salah, atau level akun tidak memiliki akses.</p>
    <?php } elseif ($error === 'empty') { ?>
        <p class="pesan">Username dan password harus diisi.</p>
    <?php } ?>

    <form action="proses_login.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-button">
            <button type="submit">Login</button>
        </div>
    </form>
</div>
</body>
</html>
