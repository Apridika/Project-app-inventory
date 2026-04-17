<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Detail Kasir";
include '../includes/head.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID transaksi tidak valid.");
}


// ambil header transaksi
$queryTransaction = "
    SELECT
        t.id,
        t.invoice_number,
        t.customer_name,
        t.channel,
        t.status,
        t.payment_method,
        t.note,
        t.total_price,
        t.created_at,
        u.name AS user_name
    FROM transactions t
    LEFT JOIN users u ON u.id = t.created_by
    WHERE t.id = ?
    LIMIT 1
";

$stmtTransaction = mysqli_prepare($conn, $queryTransaction);
mysqli_stmt_bind_param($stmtTransaction, "i", $id);
mysqli_stmt_execute($stmtTransaction);
$resultTransaction = mysqli_stmt_get_result($stmtTransaction);
$transaction = mysqli_fetch_assoc($resultTransaction);

if (!$transaction) {
    die("Data transaksi tidak ditemukan.");
}

// ambil detail item
$queryDetail = "
    SELECT
        td.qty,
        td.price,
        td.discount,
        td.subtotal,
        td.product_name,
        td.type_name,
        td.size_name,
        td.color_name,
        td.sku,
        pv.unit
    FROM transaction_details td
    LEFT JOIN product_variants pv ON pv.id = td.variant_id
    WHERE td.transaction_id = ?
    ORDER BY td.id ASC
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
                <h2>Detail Transaksi Kasir</h2>
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
                        <label>No Invoice</label>
                        <div class="form-static"><?= htmlspecialchars($transaction['invoice_number']); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div class="form-static"><?= htmlspecialchars(ucfirst($transaction['status'])); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <div class="form-static"><?= htmlspecialchars(ucfirst($transaction['payment_method'] ?? '-')); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Via</label>
                        <div class="form-static"><?= htmlspecialchars(ucfirst($transaction['channel'] ?? '-')); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Pembeli</label>
                        <div class="form-static"><?= htmlspecialchars($transaction['customer_name'] ?: '-'); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Total</label>
                        <div class="form-static">Rp <?= number_format((int) $transaction['total_price']); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Dibuat Oleh</label>
                        <div class="form-static"><?= htmlspecialchars($transaction['user_name'] ?? '-'); ?></div>
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <div class="form-static"><?= date('d-m-Y H:i', strtotime($transaction['created_at'])); ?></div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Catatan</label>
                        <div class="form-static">
                            <?= !empty($transaction['note']) ? nl2br(htmlspecialchars($transaction['note'])) : '-'; ?>
                        </div>
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
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($resultDetail && mysqli_num_rows($resultDetail) > 0):
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
                            <td>
    <?php
    $qty = (float) $row['qty'];
    $qtyFormatted = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
    echo htmlspecialchars($qtyFormatted . ' ' . ($row['unit'] ?? ''));
    ?>
</td>
                            <td>Rp <?= number_format((int) $row['price']); ?></td>
                            <td>Rp <?= number_format((int) $row['subtotal']); ?></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Tidak ada detail transaksi.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="form-actions" style="padding: 20px;">
                    <?php if ($transaction['status'] === 'paid'): ?>
                        <form action="batalkan_kasir.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.');">
                            <input type="hidden" name="transaction_id" value="<?= (int) $transaction['id']; ?>">
                            <button type="submit" class="btn-secondary">Batalkan Transaksi</button>
                        </form>
                    <?php endif; ?>

                    <a href="riwayat_kasir.php" class="btn-secondary">Kembali</a>
                    <a href="cetak_struk.php?id=<?= (int) $transaction['id']; ?>" target="_blank" class="btn-secondary">
    Cetak Struk
</a>
                </div>
            </div>

        </div>

    </main>

</div>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>