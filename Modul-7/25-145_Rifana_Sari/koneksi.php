<?php 
$host='localhost';
$username='root';
$password='';
$dbname='penjualan';

$conn=mysqli_connect($host,$username,$password,$dbname);

if(!$conn){
	die('koneksi gagal'.mysql_connect_error($conn));
}
echo "koneksi berhasil";
?>