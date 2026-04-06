<?php
require_once '../includes/auth_check.php';
require_once '../includes/koneksi.php';
?>
<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_varian.php");
    exit;
}

$product_id = (int) ($_POST['product_id'] ?? 0);
$type_id    = $_POST['type_id'] ?? '';
$size_id    = (int) ($_POST['size_id'] ?? 0);
$color_id   = (int) ($_POST['color_id'] ?? 0);
$sku        = trim($_POST['sku'] ?? '');
$price      = (int) ($_POST['price'] ?? -1);
$stock      = (int) ($_POST['stock'] ?? -1);

// validasi dasar
if ($product_id <= 0) {
    die("Produk wajib dipilih.");
}

if ($size_id <= 0) {
    die("Ukuran wajib dipilih.");
}

if ($color_id <= 0) {
    die("Warna wajib dipilih.");
}

if ($sku === '') {
    die("SKU wajib diisi.");
}

if ($price < 0) {
    die("Harga tidak valid.");
}

if ($stock < 0) {
    die("Stok tidak valid.");
}

// type_id boleh null
$type_id = ($type_id === '' ? null : (int)$type_id);

// cek SKU duplikat
$checkSku = mysqli_prepare($conn, "SELECT id FROM product_variants WHERE sku = ?");
mysqli_stmt_bind_param($checkSku, "s", $sku);
mysqli_stmt_execute($checkSku);
$skuResult = mysqli_stmt_get_result($checkSku);

if (mysqli_num_rows($skuResult) > 0) {
    die("SKU sudah digunakan.");
}

$query = "INSERT INTO product_variants 
(product_id, type_id, size_id, color_id, sku, price, stock)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param(
    $stmt,
    "iiiisii",
    $product_id,
    $type_id,
    $size_id,
    $color_id,
    $sku,
    $price,
    $stock
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: data_barang.php?success=varian_tambah");
    exit;
} else {
    die("Gagal menambahkan varian: " . mysqli_error($conn));
}