<?php
require_once 'session.php';
cek_login();
require_once 'config.php';

$pesan = "";

if (isset($_POST['simpan_transaksi'])) {
    $waktu_transaksi = $_POST['waktu_transaksi'] ?? date('Y-m-d');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $pelanggan_id = (int) ($_POST['pelanggan_id'] ?? 0);
    $barang_id = (int) ($_POST['barang_id'] ?? 0);
    $qty = (int) ($_POST['qty'] ?? 1);
    $user_id = (int) ($_SESSION['id_user'] ?? 0);

    $stmt_b = mysqli_prepare($conn, "SELECT harga, stok FROM barang WHERE id = ?");
    mysqli_stmt_bind_param($stmt_b, "i", $barang_id);
    mysqli_stmt_execute($stmt_b);
    $barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_b));

    if (!$barang || $qty <= 0) {
        $pesan = "Pilih barang dan jumlah yang valid.";
    } elseif ((int) $barang['stok'] < $qty) {
        $pesan = "Stok barang tidak mencukupi.";
    } else {
        $harga = (int) $barang['harga'];
        $total = $harga * $qty;

        $stmt_t = mysqli_prepare($conn, "INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id, user_id) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_t, "ssiii", $waktu_transaksi, $keterangan, $total, $pelanggan_id, $user_id);

        if (mysqli_stmt_execute($stmt_t)) {
            $transaksi_id = mysqli_insert_id($conn);

            $stmt_d = mysqli_prepare($conn, "INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_d, "iiii", $transaksi_id, $barang_id, $harga, $qty);
            mysqli_stmt_execute($stmt_d);

            $stmt_s = mysqli_prepare($conn, "UPDATE barang SET stok = stok - ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_s, "ii", $qty, $barang_id);
            mysqli_stmt_execute($stmt_s);

            header("Location: transaksi.php?sukses=1");
            exit;
        } else {
            $pesan = "Gagal menyimpan transaksi.";
        }
    }
}

$pelanggan_list = mysqli_query($conn, "SELECT id, nama FROM pelanggan ORDER BY nama ASC");
$barang_list = mysqli_query($conn, "SELECT id, nama_barang, harga, stok FROM barang ORDER BY nama_barang ASC");
$transaksi_list = mysqli_query(
    $conn,
    "SELECT t.*, p.nama AS nama_pelanggan, u.nama AS nama_kasir
     FROM transaksi t
     LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
     LEFT JOIN user u ON t.user_id = u.id_user
     ORDER BY t.id DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Transaksi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Data Transaksi</h2>
    <p>User aktif: <strong><?php echo htmlspecialchars($_SESSION['nama'] ?? ''); ?></strong></p>

    <?php if (isset($_GET['sukses'])) echo '<p class="pesan">Transaksi berhasil disimpan.</p>'; ?>
    <?php if ($pesan !== '') echo '<p class="pesan">' . htmlspecialchars($pesan) . '</p>'; ?>

    <h3>Tambah Transaksi Baru</h3>
    <form method="POST" action="transaksi.php">
        <div class="form-group">
            <label>Tanggal Transaksi</label>
            <input type="date" name="waktu_transaksi" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Pelanggan</label>
            <select name="pelanggan_id" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php while ($p = mysqli_fetch_assoc($pelanggan_list)) { ?>
                    <option value="<?php echo (int) $p['id']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Barang</label>
            <select name="barang_id" required>
                <option value="">-- Pilih Barang --</option>
                <?php while ($b = mysqli_fetch_assoc($barang_list)) { ?>
                    <option value="<?php echo (int) $b['id']; ?>">
                        <?php echo htmlspecialchars($b['nama_barang']); ?> (Rp <?php echo number_format((int) $b['harga'], 0, ',', '.'); ?> | Stok: <?php echo (int) $b['stok']; ?>)
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jumlah (Qty)</label>
            <input type="number" name="qty" min="1" value="1" required>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="keterangan" placeholder="Contoh: Pembelian tunai">
        </div>
        <div class="form-button">
            <button type="submit" name="simpan_transaksi">Simpan Transaksi</button>
        </div>
    </form>

    <h3>Daftar Transaksi</h3>
    <table>
        <tr>
            <th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Total</th><th>Petugas</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($transaksi_list)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['waktu_transaksi'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['nama_pelanggan'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['keterangan'] ?? ''); ?></td>
            <td>Rp <?php echo number_format((int) ($row['total'] ?? 0), 0, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($row['nama_kasir'] ?? '-'); ?></td>
        </tr>
        <?php } ?>
    </table>
    <p><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
