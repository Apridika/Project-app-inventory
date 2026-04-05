<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tes_db";


$conn = mysqli_connect("localhost", "root", "", "tes_db");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
else {
    // echo "Koneksi berhasil";
}
mysqli_set_charset($conn, "utf8mb4");
?>