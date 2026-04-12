<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Riwayat Kasir";
include '../includes/head.php';

$query = "
    SELECT 
        t.id,
        t.customer_name,
        t.channel,
        t.payment_method,
        t.status,
        t.total_price,
        t.created_at,
        COUNT(td.id) AS total_item
    FROM transactions t
    LEFT JOIN transaction_details td ON td.transaction_id = t.id
    GROUP BY 
        t.id,
        t.customer_name,
        t.channel,
        t.payment_method,
        t.status,
        t.total_price,
        t.created_at
    ORDER BY t.created_at DESC
";

$result = mysqli_query($conn, $query);
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
                <h2>Riwayat Transaksi Kasir</h2>
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

            <div class="card">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Pembeli</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Via</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result && mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?: '-'); ?></td>
                            <td><?= (int) $row['total_item']; ?></td>
                            <td>Rp <?= number_format((int) $row['total_price']); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['channel'] ?? '-')); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['payment_method'] ?? '-')); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                            <td>
                                <a href="detail_kasir.php?id=<?= (int) $row['id']; ?>" class="btn-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">Belum ada transaksi kasir.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

</div>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>