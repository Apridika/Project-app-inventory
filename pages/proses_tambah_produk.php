<?php
include '../includes/koneksi.php';

$name = $_POST['name'];

mysqli_query($conn, "INSERT INTO products (name) VALUES ('$name')");

header("Location: tambah_varian.php");