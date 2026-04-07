<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/data_barang.php');
    exit;
}

$id    = (int) ($_POST['id'] ?? 0);
$sku   = trim($_POST['sku'] ?? '');
$price = (int) ($_POST['price'] ?? -1);

if ($id <= 0) {
    die("ID tidak valid.");
}

if ($sku === '') {
    die("SKU wajib diisi.");
}

if ($price < 0) {
    die("Harga tidak valid.");
}

// cek SKU dipakai varian lain atau tidak
$checkSku = mysqli_prepare($conn, "SELECT id FROM product_variants WHERE sku = ? AND id != ?");
mysqli_stmt_bind_param($checkSku, "si", $sku, $id);
mysqli_stmt_execute($checkSku);
$skuResult = mysqli_stmt_get_result($checkSku);

if (mysqli_num_rows($skuResult) > 0) {
    die("SKU sudah dipakai data lain.");
}

$stmt = mysqli_prepare($conn, "UPDATE product_variants SET sku = ?, price = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "sii", $sku, $price, $id);

if (mysqli_stmt_execute($stmt)) {
    redirectTo('pages/data_barang.php?success=update');
    exit;
} else {
    die("Gagal update data: " . mysqli_error($conn));
}