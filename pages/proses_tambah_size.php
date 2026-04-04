<?php
include '../includes/koneksi.php';

$name = $_POST['name'];

// cek duplikat
$cek = mysqli_query($conn, "SELECT * FROM sizes WHERE name='$name'");

if (mysqli_num_rows($cek) > 0) {
    echo "Ukuran sudah ada!";
} else {
    mysqli_query($conn, "INSERT INTO sizes (name) VALUES ('$name')");
    header("Location: tambah.php");
}