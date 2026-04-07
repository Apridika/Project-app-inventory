<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';
?>
<?php

$allowed_tables = ['products', 'types', 'sizes', 'colors'];

$table = $_POST['table'] ?? '';
$id    = (int) ($_POST['id'] ?? 0);
$name  = trim($_POST['name'] ?? '');

if (!in_array($table, $allowed_tables)) {
    die('Table tidak valid');
}

if ($name === '') {
    die('Nama tidak boleh kosong');
}

$stmt = mysqli_prepare($conn, "SELECT id FROM $table WHERE name = ? AND id != ?");
mysqli_stmt_bind_param($stmt, "si", $name, $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    die('Nama sudah dipakai');
}

$stmt = mysqli_prepare($conn, "UPDATE $table SET name = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $name, $id);
mysqli_stmt_execute($stmt);

header("Location: master_data.php");
exit;