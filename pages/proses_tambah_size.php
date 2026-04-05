<?php
include '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah.php");
    exit;
}

$name = trim($_POST['name'] ?? '');

if ($name <= 0) {
    die("stok tidak valid.");
}

if ($name === '') {
    die("Nama ukuran wajib diisi.");
}

$check = mysqli_prepare($conn, "SELECT id FROM sizes WHERE name = ?");
mysqli_stmt_bind_param($check, "s", $name);
mysqli_stmt_execute($check);
$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {
    die("Ukuran sudah ada!");
}

$stmt = mysqli_prepare($conn, "INSERT INTO sizes (name) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $name);

if (mysqli_stmt_execute($stmt)) {
    header("Location: tambah.php?success=size_tambah");
    exit;
} else {
    die("Gagal menambahkan ukuran: " . mysqli_error($conn));
}
