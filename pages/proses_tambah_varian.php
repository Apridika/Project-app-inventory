<?php
include '../includes/koneksi.php';

$product_id = $_POST['product_id'];
$type_id = $_POST['type_id'] ?: "NULL";
$size_id = $_POST['size_id'];
$color_id = $_POST['color_id'];
$sku = $_POST['sku'];
$price = $_POST['price'];
$stock = $_POST['stock'];

mysqli_query($conn, "INSERT INTO product_variants 
(product_id, type_id, size_id, color_id, sku, price, stock)
VALUES 
($product_id, $type_id, $size_id, $color_id, '$sku', $price, $stock)");

header("Location: data_barang.php");