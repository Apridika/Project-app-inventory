<?php
include '../includes/koneksi.php';

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM product_variants WHERE id=$id");

header("Location: data_barang.php");