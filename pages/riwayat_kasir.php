<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Riwayat Kasir";
include '../includes/head.php';

$query = "
    SELECT 
        t.id,
        t.invoice_number,
        t.status,
        t.total_price,
        t.created_at,
        u.name AS user_name,
        COUNT(td.id) AS total_item
    FROM transactions t
    LEFT JOIN transaction_details td ON td.transaction_id = t.id
    LEFT JOIN users u ON u.id = t.created_by
    GROUP BY t.id, t.invoice_number, t.status, t.total_price, t.created_at, u.name
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
                            <th>No Invoice</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                            <td><?= (int)$row['total_item']; ?></td>
                            <td>Rp <?= number_format($row['total_price']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td><?= htmlspecialchars($row['user_name'] ?? '-'); ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="detail_kasir.php?id=<?= (int)$row['id']; ?>" class="btn-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Belum ada transaksi kasir.</td>
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