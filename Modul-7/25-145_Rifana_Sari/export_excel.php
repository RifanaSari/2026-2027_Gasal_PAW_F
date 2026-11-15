
<?php
include 'koneksi.php';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

if (empty($tanggal_mulai) || empty($tanggal_selesai)) {
    die("Silakan pilih rentang tanggal terlebih dahulu.");
}

$mulai = mysqli_real_escape_string($conn, $tanggal_mulai);
$selesai = mysqli_real_escape_string($conn, $tanggal_selesai);

// Query laporan penjualan
$q = "SELECT DATE(waktu_transaksi) AS tanggal,
      SUM(total) AS total_harian
      FROM transaksi
      WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'
      GROUP BY DATE(waktu_transaksi)
      ORDER BY tanggal ASC";

$res = mysqli_query($conn, $q);

// Header untuk ekspor Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_penjualan.xls");

?>

<table border="1">

    <tr>
        <th>No</th>
        <th>Total Pendapatan</th>
        <th>Tanggal Transaksi</th>
    </tr>

    <?php
    $no = 1;
    $total_pendapatan = 0;

    while ($row = mysqli_fetch_assoc($res)):

        $total_pendapatan += $row['total_harian'];
    ?>

    <tr>
        <td><?= $no++; ?></td>

        <td>
            <?= $row['total_harian']; ?>
        </td>

        <td>
            <?= $row['tanggal']; ?>
        </td>
    </tr>

    <?php endwhile; ?>

    <tr>
        <th colspan="2">Total Pendapatan Keseluruhan</th>
        <th><?= $total_pendapatan; ?></th>
    </tr>

</table>
