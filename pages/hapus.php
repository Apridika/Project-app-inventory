<?php
include '../includes/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID tidak valid.");
}

$stmt = mysqli_prepare($conn, "DELETE FROM product_variants WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: data_barang.php?success=hapus");
    exit;
} else {
    die("Gagal menghapus data: " . mysqli_error($conn));
}