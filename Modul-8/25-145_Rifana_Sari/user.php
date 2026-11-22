<?php
require_once 'session.php';
cek_owner();
require_once 'config.php';

$pesan = "";
$aksi = $_GET['aksi'] ?? '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($aksi === 'hapus' && $id) {
    if ($id === (int) ($_SESSION['id_user'] ?? 0)) {
        $pesan = "Akun yang sedang login tidak dapat dihapus.";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: user.php");
            exit;
        } else {
            $pesan = "Gagal menghapus data user.";
        }
    }
}

if (isset($_POST['simpan'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $hp = trim($_POST['hp'] ?? '');
    $level = (int) ($_POST['level'] ?? 2);

    if ($username === '' || $password === '' || $nama === '') {
        $pesan = "Username, password, dan nama wajib diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO user (username, password, nama, alamat, hp, level) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssi", $username, $password, $nama, $alamat, $hp, $level);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: user.php");
            exit;
        } else {
            $pesan = "Gagal menambahkan data user.";
        }
    }
}

if (isset($_POST['update'])) {
    $id_edit = (int) ($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $hp = trim($_POST['hp'] ?? '');
    $level = (int) ($_POST['level'] ?? 2);

    if ($username === '' || $nama === '') {
        $pesan = "Username dan nama wajib diisi.";
    } else {
        if ($password !== '') {
            $stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, password = ?, nama = ?, alamat = ?, hp = ?, level = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt, "sssssii", $username, $password, $nama, $alamat, $hp, $level, $id_edit);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE user SET username = ?, nama = ?, alamat = ?, hp = ?, level = ? WHERE id_user = ?");
            mysqli_stmt_bind_param($stmt, "ssssii", $username, $nama, $alamat, $hp, $level, $id_edit);
        }
        if (mysqli_stmt_execute($stmt)) {
            header("Location: user.php");
            exit;
        } else {
            $pesan = "Gagal mengubah data user.";
        }
    }
}

$edit_data = null;
if ($aksi === 'edit' && $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$query = mysqli_query($conn, "SELECT * FROM user ORDER BY id_user ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Master User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Data Master User</h2>
    <?php if ($pesan !== '') echo '<p class="pesan">' . htmlspecialchars($pesan) . '</p>'; ?>

    <?php if ($aksi === 'tambah' || $edit_data) { ?>
        <h3><?php echo $edit_data ? 'Edit Data User' : 'Tambah Data User'; ?></h3>
        <form method="POST" action="user.php">
            <?php if ($edit_data) { ?>
                <input type="hidden" name="id_user" value="<?php echo (int) $edit_data['id_user']; ?>">
            <?php } ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($edit_data['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password <?php echo $edit_data ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                <input type="password" name="password" <?php echo $edit_data ? '' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?php echo htmlspecialchars($edit_data['alamat'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="hp" value="<?php echo htmlspecialchars($edit_data['hp'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Level</label>
                <select name="level">
                    <option value="1" <?php echo ((int) ($edit_data['level'] ?? 2) === 1) ? 'selected' : ''; ?>>1 - Owner</option>
                    <option value="2" <?php echo ((int) ($edit_data['level'] ?? 2) === 2) ? 'selected' : ''; ?>>2 - Kasir</option>
                </select>
            </div>
            <div class="form-button">
                <button type="submit" name="<?php echo $edit_data ? 'update' : 'simpan'; ?>">
                    <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                </button>
                <a href="user.php">Batal</a>
            </div>
        </form>
    <?php } else { ?>
        <div class="tambah"><a href="user.php?aksi=tambah">Tambah Data</a></div>
    <?php } ?>

    <table>
        <tr>
            <th>No</th><th>Username</th><th>Nama</th><th>Alamat</th><th>No HP</th><th>Level</th><th>Tindakan</th>
        </tr>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['nama'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['alamat'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['hp'] ?? ''); ?></td>
            <td><?php echo ((int) ($row['level'] ?? 2) === 1) ? '1 (Owner)' : '2 (Kasir)'; ?></td>
            <td>
                <a class="edit" href="user.php?aksi=edit&id=<?php echo (int) $row['id_user']; ?>">Edit</a>
                <a class="hapus" href="user.php?aksi=hapus&id=<?php echo (int) $row['id_user']; ?>"
                   onclick="return confirm('Anda yakin akan menghapus user ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <p><a href="index.php">Kembali ke Home</a></p>
</div>
</body>
</html>
