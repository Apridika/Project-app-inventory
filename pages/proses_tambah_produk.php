<?php
include '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah.php");
    exit;
}

$name = trim($_POST['name'] ?? '');

if ($name === '') {
    die("Nama produk wajib diisi.");
}

$stmt = mysqli_prepare($conn, "INSERT INTO products (name) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $name);
if (mysqli_stmt_execute($stmt)) {
    header("Location: tambah_varian.php?success=produk_tambah");
    exit;
} else {
    die("Gagal menambahkan produk: " . mysqli_error($conn));
}
