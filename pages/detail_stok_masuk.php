<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Detail Stok Masuk";
include '../includes/head.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID transaksi tidak valid.");
}

// ambil data header pembelian
$queryPurchase = "
    SELECT
        p.id,
        p.purchase_number,
        p.note,
        p.total_cost,
        p.status,
        p.created_at,
        s.name AS supplier_name,
        u.name AS user_name
    FROM purchases p
    LEFT JOIN suppliers s ON s.id = p.supplier_id
    LEFT JOIN users u ON u.id = p.created_by
    WHERE p.id = ?
    LIMIT 1
";

$stmtPurchase = mysqli_prepare($conn, $queryPurchase);
mysqli_stmt_bind_param($stmtPurchase, "i", $id);
mysqli_stmt_execute($stmtPurchase);
$resultPurchase = mysqli_stmt_get_result($stmtPurchase);
$purchase = mysqli_fetch_assoc($resultPurchase);

if (!$purchase) {
    die("Data stok masuk tidak ditemukan.");
}

// ambil detail item
$queryDetail = "
    SELECT
        pd.qty,
        pd.cost_price,
        pd.subtotal,
        pd.product_name,
        pd.type_name,
        pd.size_name,
        pd.color_name,
        pd.sku
    FROM purchase_details pd
    WHERE pd.purchase_id = ?
    ORDER BY pd.id ASC
";

$stmtDetail = mysqli_prepare($conn, $queryDetail);
mysqli_stmt_bind_param($stmtDetail, "i", $id);
mysqli_stmt_execute($stmtDetail);
$resultDetail = mysqli_stmt_get_result($stmtDetail);
?>

<!DOCTYPE html>
<html lang="id">
<body>

<div class="layout">

    <?php include '../includes/sidebarai.php'; ?>

    <main class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content-wrap">

            <div class="page-header">
                <h2>Detail Stok Masuk</h2>
            </div>

            <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

            <div class="card form-card" style="margin-bottom: 20px;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>No Pembelian</label>
                        <div class="form-static"><?= htmlspecialchars($purchase['purchase_number']); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Supplier</label>
                        <div class="form-static"><?= htmlspecialchars($purchase['supplier_name'] ?? '-'); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div class="form-static"><?= htmlspecialchars($purchase['status']); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Total Cost</label>
                        <div class="form-static">Rp <?= number_format($purchase['total_cost']); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Dibuat Oleh</label>
                        <div class="form-static"><?= htmlspecialchars($purchase['user_name'] ?? '-'); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <div class="form-static"><?= date('d-m-Y H:i', strtotime($purchase['created_at'])); ?></div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Catatan</label>
                        <div class="form-static"><?= nl2br(htmlspecialchars($purchase['note'] ?? '-')); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Harga Modal</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($resultDetail) > 0):
                            while ($row = mysqli_fetch_assoc($resultDetail)):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?= htmlspecialchars($row['product_name']); ?>
                                |
                                <?= htmlspecialchars($row['type_name'] ?? '-'); ?>
                                |
                                <?= htmlspecialchars($row['size_name'] ?? '-'); ?>
                                |
                                <?= htmlspecialchars($row['color_name'] ?? '-'); ?>
                                <br>
                                <small>SKU: <?= htmlspecialchars($row['sku']); ?></small>
                            </td>
                            <td><?= (int)$row['qty']; ?></td>
                            <td>Rp <?= number_format($row['cost_price']); ?></td>
                            <td>Rp <?= number_format($row['subtotal']); ?></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Tidak ada detail item.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="form-actions" style="padding: 20px;">
    <?php if ($purchase['status'] === 'received'): ?>
        <form action="batalkan_stok_masuk.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan stok masuk ini? Stok akan dikurangi kembali.');">
            <input type="hidden" name="purchase_id" value="<?= (int)$purchase['id']; ?>">
            <button type="submit" class="btn-secondary">Batalkan Transaksi</button>
        </form>
    <?php endif; ?>

    <a href="riwayat_stok_masuk.php" class="btn-secondary">Kembali</a>
</div>
            </div>

        </div>

    </main>

</div>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>