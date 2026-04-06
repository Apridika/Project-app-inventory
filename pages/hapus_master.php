<?php
include '../includes/koneksi.php';

$allowed_tables = ['products', 'types', 'sizes', 'colors'];

$table = $_GET['table'] ?? '';
$id    = (int) ($_GET['id'] ?? 0);

if (!in_array($table, $allowed_tables)) {
    die('Table tidak valid');
}

$stmt = mysqli_prepare($conn, "DELETE FROM $table WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    die('Data gagal dihapus. Kemungkinan sedang dipakai di data varian.');
}

header("Location: master_data.php");
exit;