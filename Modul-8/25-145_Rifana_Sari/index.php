<?php
require_once 'session.php';
cek_login();
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Home - Sistem Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Sistem Penjualan</h2>
    <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?></strong></p>

    <?php if ($error === 'akses') { ?>
        <p class="pesan">Akses ditolak! Halaman tersebut hanya untuk Owner.</p>
    <?php } ?>

    <nav>
        <a href="index.php">Home</a> |
        <?php if ((int) $_SESSION['level'] === 1) { ?>
            Data Master (
            <a href="barang.php">Data Barang</a> |
            <a href="supplier.php">Data Supplier</a> |
            <a href="pelanggan.php">Data Pelanggan</a> |
            <a href="user.php">Data User</a>
            ) |
        <?php } ?>
        <a href="transaksi.php">Transaksi</a> |
        <a href="laporan.php">Laporan</a> |
        <a href="logout.php">Logout</a>
    </nav>

    <h3>Halaman Home</h3>
    <p>Level akun: <?php echo ((int) $_SESSION['level'] === 1) ? 'Owner (Level 1)' : 'Kasir (Level 2)'; ?></p>
</div>
</body>
</html>
