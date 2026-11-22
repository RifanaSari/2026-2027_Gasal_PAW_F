<?php
require_once 'session.php';
$_SESSION = [];
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
