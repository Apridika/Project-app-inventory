<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Stok Masuk";
include '../includes/head.php';

// ambil supplier aktif
$suppliers = mysqli_query($conn, "SELECT id, name FROM suppliers WHERE is_active = 1 ORDER BY name ASC");

// ambil varian aktif
$variantsQuery = "
    SELECT 
        pv.id,
        pv.sku,
        pv.stock,
        p.name AS product_name,
        t.name AS type_name,
        s.name AS size_name,
        c.name AS color_name
    FROM product_variants pv
    LEFT JOIN products p ON pv.product_id = p.id
    LEFT JOIN types t ON pv.type_id = t.id
    LEFT JOIN sizes s ON pv.size_id = s.id
    LEFT JOIN colors c ON pv.color_id = c.id
    WHERE pv.is_active = 1
    ORDER BY p.name ASC, pv.sku ASC
";
$variants = mysqli_query($conn, $variantsQuery);

// nomor pembelian sederhana
$purchaseNumber = 'PM-' . date('Ymd-His');
?>

<!DOCTYPE html>
<html lang="id">
<body>
<div class="layout">

    <?php include '../includes/sidebarai.php'; ?>

    <main class="main">
        <?php include '../includes/header.php'; ?>

        <?php if (isset($_GET['success'])): ?>
    <div class="alert-success">Stok masuk berhasil disimpan.</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert-error"><?= htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

        <div class="content-wrap">
            <div class="page-header">
                <h2>Stok Masuk</h2>
            </div>

            <div class="card form-card">
                <form action="proses_stok_masuk.php" method="POST" class="form-modern">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>No. Pembelian</label>
                            <input type="text" name="purchase_number" value="<?= htmlspecialchars($purchaseNumber); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">-- Pilih Supplier --</option>
                                <?php while ($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                    <option value="<?= (int)$supplier['id']; ?>">
                                        <?= htmlspecialchars($supplier['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Varian Barang</label>
                            <select name="variant_id" required>
                                <option value="">-- Pilih Varian --</option>
                                <?php while ($variant = mysqli_fetch_assoc($variants)): ?>
                                    <option value="<?= (int)$variant['id']; ?>">
                                        <?= htmlspecialchars($variant['product_name']); ?>
                                        | <?= htmlspecialchars($variant['type_name'] ?? '-'); ?>
                                        | <?= htmlspecialchars($variant['size_name'] ?? '-'); ?>
                                        | <?= htmlspecialchars($variant['color_name'] ?? '-'); ?>
                                        | SKU: <?= htmlspecialchars($variant['sku']); ?>
                                        | Stok: <?= (int)$variant['stock']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Qty Masuk</label>
                            <input type="number" name="qty" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Harga Modal</label>
                            <input type="number" name="cost_price" min="0" required>
                        </div>

                        <div class="form-group">
                            <label>Catatan</label>
                            <textarea name="note" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-secondary">Simpan</button>
                        <a href="data_barang.php" class="btn-secondary">Batal</a>
                    </div>

                </form>
            </div>
        </div>
    </main>
</div>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>