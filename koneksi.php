<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_pesantren";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi format rupiah
function rupiah($angka){
    return "Rp " . number_format($angka,2,',','.');
}
?>