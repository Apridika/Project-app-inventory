<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/data_barang.php');
    exit;
}

$id    = (int) ($_POST['id'] ?? 0);
$price = (int) ($_POST['price'] ?? -1);

if ($id <= 0) {
    die("ID tidak valid.");
}

if ($price < 0) {
    die("Harga tidak valid.");
}

$stmt = mysqli_prepare($conn, "UPDATE product_variants SET price = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "ii", $price, $id);

if (mysqli_stmt_execute($stmt)) {
    redirectTo('pages/data_barang.php?success=update');
    exit;
} else {
    die("Gagal update data: " . mysqli_error($conn));
}