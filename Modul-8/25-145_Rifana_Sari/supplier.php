<?php
require_once 'session.php';
cek_owner();
require_once 'config.php';

$pesan = "";
$aksi = $_GET['aksi'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Hapus data
if ($aksi === 'hapus' && $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM supplier WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: supplier.php");
        exit;
    } else {
        $pesan = "Gagal menghapus data supplier.";
    }
}

// Tambah data
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama'] ?? '');
    $telp = trim($_POST['telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $pesan = "Nama supplier wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO supplier (nama, telp, alamat) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $telp, $alamat);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: supplier.php");
            exit;
        } else {
            $pesan = "Gagal menambahkan data supplier.";
        }
    }
}

// Update data
if (isset($_POST['update'])) {
    $id_edit = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $telp = trim($_POST['telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $pesan = "Nama supplier wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE supplier SET nama = ?, telp = ?, alamat = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $telp, $alamat, $id_edit);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: supplier.php");
            exit;
        } else {
            $pesan = "Gagal mengubah data supplier.";
        }
    }
}

// Ambil data untuk form edit jika aksi=edit
$edit_data = null;
if ($aksi === 'edit' && $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM supplier WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$query = mysqli_query($conn, "SELECT * FROM supplier ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Master Supplier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Data Master Supplier</h2>
    <?php if ($pesan !== '') echo '<p class="pesan">' . htmlspecialchars($pesan) . '</p>'; ?>

    <?php if ($aksi === 'tambah' || $edit_data) { ?>
        <h3><?php echo $edit_data ? 'Edit Data Supplier' : 'Tambah Data Supplier'; ?></h3>
        <form method="POST" action="supplier.php">
            <?php if ($edit_data) { ?>
                <input type="hidden" name="id" value="<?php echo (int) $edit_data['id']; ?>">
            <?php } ?>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Telp</label>
                <input type="text" name="telp" value="<?php echo htmlspecialchars($edit_data['telp'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?php echo htmlspecialchars($edit_data['alamat'] ?? ''); ?>">
            </div>
            <div class="form-button">
                <button type="submit" name="<?php echo $edit_data ? 'update' : 'simpan'; ?>">
                    <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                </button>
                <a href="supplier.php">Batal</a>
            </div>
        </form>
    <?php } else { ?>
        <div class="tambah"><a href="supplier.php?aksi=tambah">Tambah Data</a></div>
    <?php } ?>

    <table>
        <tr>
            <th>No</th><th>Nama</th><th>Telp</th><th>Alamat</th><th>Tindakan</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td><?php echo htmlspecialchars($row['telp'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['alamat'] ?? ''); ?></td>
            <td>
                <a class="edit" href="supplier.php?aksi=edit&id=<?php echo (int) $row['id']; ?>">Edit</a>
                <a class="hapus" href="supplier.php?aksi=hapus&id=<?php echo (int) $row['id']; ?>"
                   onclick="return confirm('Anda yakin akan menghapus supplier ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <p><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
