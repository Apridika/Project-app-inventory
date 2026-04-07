<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';
?>

<?php
include '../includes/head.php';

$allowed_tables = ['products', 'types', 'sizes', 'colors'];

$table = $_GET['table'] ?? '';
$id    = (int) ($_GET['id'] ?? 0);

if (!in_array($table, $allowed_tables)) {
    die('Table tidak valid');
}

$stmt = mysqli_prepare($conn, "SELECT * FROM $table WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die('Data tidak ditemukan');
}
?>

<div class="layout">
    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">
        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">
            <div class="page-header">
                <h2>Edit Master Data</h2>
            </div>

            <div class="card form-card">
                <form action="proses_edit_master.php" method="POST" class="form-modern">
                    <input type="hidden" name="table" value="<?= htmlspecialchars($table); ?>">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">

                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-secondary">Update</button>
                        <a href="master_data.php" class="btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="<?= url('assets/app.js') ?>"></script>
</div>