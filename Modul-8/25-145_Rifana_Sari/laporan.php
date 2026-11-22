<?php
require_once 'session.php';
cek_login();
require_once 'config.php';

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$stmt = mysqli_prepare(
    $conn,
    "SELECT t.*, p.nama AS nama_pelanggan
     FROM transaksi t
     LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
     WHERE DATE(t.waktu_transaksi) BETWEEN ? AND ?
     ORDER BY t.waktu_transaksi ASC"
);
mysqli_stmt_bind_param($stmt, "ss", $tgl_awal, $tgl_akhir);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data_laporan = [];
$total_pendapatan = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $data_laporan[] = $row;
    $total_pendapatan += (int) ($row['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Laporan Penjualan</h2>
    <p>User aktif: <strong><?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?></strong></p>

    <form method="GET" action="laporan.php">
        <div class="form-group">
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_awal" value="<?php echo htmlspecialchars($tgl_awal); ?>" required>
        </div>
        <div class="form-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_akhir" value="<?php echo htmlspecialchars($tgl_akhir); ?>" required>
        </div>
        <div class="form-button">
            <button type="submit">Tampilkan</button>
            <button type="button" onclick="window.print()">Cetak Laporan</button>
        </div>
    </form>

    <table>
        <tr>
            <th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Total</th>
        </tr>
        <?php
        $no = 1;
        foreach ($data_laporan as $row) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['waktu_transaksi'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['nama_pelanggan'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['keterangan'] ?? ''); ?></td>
            <td>Rp <?php echo number_format((int) ($row['total'] ?? 0), 0, ',', '.'); ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="4">Total Pendapatan</th>
            <th>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></th>
        </tr>
    </table>
    <p><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
