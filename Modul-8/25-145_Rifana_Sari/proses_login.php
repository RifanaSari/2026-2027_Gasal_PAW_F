<?php
require_once 'session.php';
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header("Location: login.php?error=empty");
    exit;
}

$query = mysqli_prepare(
    $conn,
    "SELECT id_user, username, password, nama, level FROM user WHERE username = ? LIMIT 1"
);
mysqli_stmt_bind_param($query, "s", $username);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

$password_cocok = false;
if ($data) {
    $db_pass = (string) $data['password'];
    if (password_verify($password, $db_pass) || hash_equals($db_pass, (string) $password) || hash_equals($db_pass, md5($password))) {
        $password_cocok = true;
    }
}

if ($data && $password_cocok) {
    $level_database = strtolower(trim((string) $data['level']));

    if (in_array($level_database, ['1', 'owner', 'admin'], true)) {
        $level = 1;
    } elseif (in_array($level_database, ['2', 'kasir'], true)) {
        $level = 2;
    } else {
        mysqli_stmt_close($query);
        header("Location: login.php?error=invalid");
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['login'] = true;
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['level'] = $level;

    mysqli_stmt_close($query);
    header("Location: index.php");
    exit;
}

mysqli_stmt_close($query);
header("Location: login.php?error=invalid");
exit;
?>
