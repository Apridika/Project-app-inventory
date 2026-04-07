<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<?php
$title = "Edit Varian";
include '../includes/head.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID tidak valid.");
}

$query = "
    SELECT 
        pv.id,
        pv.sku,
        pv.price,
        pv.stock,
        p.name AS product_name,
        t.name AS type_name,
        s.name AS sizes_name,
        c.name AS color_name
    FROM product_variants pv
    LEFT JOIN products p ON pv.product_id = p.id
    LEFT JOIN types t ON pv.type_id = t.id
    LEFT JOIN sizes s ON pv.size_id = s.id
    LEFT JOIN colors c ON pv.color_id = c.id
    WHERE pv.id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Data tidak ditemukan.");
}
?>

<body>

    <div class="layout">

        <?php include "../includes/sidebarai.php"; ?>

        <main class="main">

            <?php include "../includes/header.php"; ?>

            <div class="content-wrap">

                <div class="page-header">
                    <h2>Edit Produk</h2>
                </div>

                <div class="card form-card">
                    <form action="proses_edit.php" method="POST" class="form-modern">

                        <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                        <div class="form-grid">

                            <div class="form-group">
                                <label>Nama Produk</label>
                                <div class="form-static">
                                    <?= htmlspecialchars($row['product_name']); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Jenis</label>
                                <div class="form-static">
                                    <?= htmlspecialchars($row['type_name'] ?? '-'); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Ukuran</label>
                                <div class="form-static">
                                    <?= htmlspecialchars($row['sizes_name'] ?? '-'); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Warna</label>
                                <div class="form-static">
                                    <?= htmlspecialchars($row['color_name'] ?? '-'); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>SKU</label>
                                <input type="text" name="sku" value="<?= htmlspecialchars($row['sku']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Harga</label>
                                <input type="number" name="price" value="<?= (int)$row['price']; ?>" required min="0">
                            </div>

                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" value="<?= (int)$row['stock']; ?>" readonly>
                                <small style="color:white">Stok tidak bisa diedit dari form ini.</small>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">Update</button>
                            <a href="data_barang.php" class="btn-secondary">Batal</a>
                        </div>

                    </form>
                </div>

            </div>

        </main>
        <script src="<?= url('assets/app.js') ?>"></script>
    </div>
</body>

</html>