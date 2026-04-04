<?php
include '../includes/koneksi.php';

$id = $_POST['id'];
$price = $_POST['price'];
$stock = $_POST['stock'];

mysqli_query($conn, "UPDATE product_variants 
SET price=$price, stock=$stock ,sku='{$_POST['sku']}'
WHERE id=$id");

header("Location: data_barang.php");