<?php
require_once 'session.php';
cek_owner();
require_once 'config.php';

$pesan = "";
$aksi = $_GET['aksi'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($aksi === 'hapus' && $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM barang WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: barang.php");
        exit;
    } else {
        $pesan = "Gagal menghapus data barang.";
    }
}

if (isset($_POST['simpan'])) {
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);
    $supplier_id = (int) ($_POST['supplier_id'] ?? 0);

    if ($nama_barang === '') {
        $pesan = "Nama barang wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiii", $kode_barang, $nama_barang, $harga, $stok, $supplier_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: barang.php");
            exit;
        } else {
            $pesan = "Gagal menambahkan data barang.";
        }
    }
}

if (isset($_POST['update'])) {
    $id_edit = (int) ($_POST['id'] ?? 0);
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);
    $supplier_id = (int) ($_POST['supplier_id'] ?? 0);

    if ($nama_barang === '') {
        $pesan = "Nama barang wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE barang SET kode_barang = ?, nama_barang = ?, harga = ?, stok = ?, supplier_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssiiii", $kode_barang, $nama_barang, $harga, $stok, $supplier_id, $id_edit);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: barang.php");
            exit;
        } else {
            $pesan = "Gagal mengubah data barang.";
        }
    }
}

$edit_data = null;
if ($aksi === 'edit' && $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM barang WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$suppliers = mysqli_query($conn, "SELECT id, nama FROM supplier ORDER BY nama ASC");
$query = mysqli_query($conn, "SELECT b.*, s.nama AS nama_supplier FROM barang b LEFT JOIN supplier s ON b.supplier_id = s.id ORDER BY b.id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Master Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Data Master Barang</h2>
    <?php if ($pesan !== '') echo '<p class="pesan">' . htmlspecialchars($pesan) . '</p>'; ?>

    <?php if ($aksi === 'tambah' || $edit_data) { ?>
        <h3><?php echo $edit_data ? 'Edit Data Barang' : 'Tambah Data Barang'; ?></h3>
        <form method="POST" action="barang.php">
            <?php if ($edit_data) { ?>
                <input type="hidden" name="id" value="<?php echo (int) $edit_data['id']; ?>">
            <?php } ?>
            <div class="form-group">
                <label>Kode Barang</label>
                <input type="text" name="kode_barang" value="<?php echo htmlspecialchars($edit_data['kode_barang'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" value="<?php echo htmlspecialchars($edit_data['nama_barang'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="harga" value="<?php echo htmlspecialchars($edit_data['harga'] ?? '0'); ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" value="<?php echo htmlspecialchars($edit_data['stok'] ?? '0'); ?>" required>
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id">
                    <option value="0">-- Pilih Supplier --</option>
                    <?php while ($s = mysqli_fetch_assoc($suppliers)) { ?>
                        <option value="<?php echo (int) $s['id']; ?>"
                            <?php echo (isset($edit_data['supplier_id']) && (int) $edit_data['supplier_id'] === (int) $s['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['nama']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-button">
                <button type="submit" name="<?php echo $edit_data ? 'update' : 'simpan'; ?>">
                    <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                </button>
                <a href="barang.php">Batal</a>
            </div>
        </form>
    <?php } else { ?>
        <div class="tambah"><a href="barang.php?aksi=tambah">Tambah Data</a></div>
    <?php } ?>

    <table>
        <tr>
            <th>No</th><th>Kode</th><th>Nama Barang</th><th>Harga</th><th>Stok</th><th>Supplier</th><th>Tindakan</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['kode_barang'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['nama_barang'] ?? ''); ?></td>
            <td>Rp <?php echo number_format((int) ($row['harga'] ?? 0), 0, ',', '.'); ?></td>
            <td><?php echo (int) ($row['stok'] ?? 0); ?></td>
            <td><?php echo htmlspecialchars($row['nama_supplier'] ?? '-'); ?></td>
            <td>
                <a class="edit" href="barang.php?aksi=edit&id=<?php echo (int) $row['id']; ?>">Edit</a>
                <a class="hapus" href="barang.php?aksi=hapus&id=<?php echo (int) $row['id']; ?>"
                   onclick="return confirm('Anda yakin akan menghapus barang ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <p><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
