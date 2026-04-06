<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tes_db";


$conn = mysqli_connect("localhost", "root", "", "tes_db");

date_default_timezone_set('Asia/Jakarta');

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
else {
    // echo "Koneksi berhasil";
}
mysqli_set_charset($conn, "utf8mb4");
?>