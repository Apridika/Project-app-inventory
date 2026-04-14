<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Riwayat Stok Masuk";
include '../includes/head.php';

$query = "
    SELECT
        p.id,
        p.purchase_number,
        p.supplier_name,
        p.note,
        p.status,
        p.created_at,
        COUNT(pd.id) AS total_item,
        COALESCE(SUM(pd.qty), 0) AS total_qty
    FROM purchases p
    LEFT JOIN purchase_details pd ON pd.purchase_id = p.id
    GROUP BY
        p.id,
        p.purchase_number,
        p.supplier_name,
        p.note,
        p.status,
        p.created_at
    ORDER BY p.created_at DESC
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
                <h2>Riwayat Stok Masuk</h2>
            </div>

            <div class="card">

                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Total Item</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;

                        if ($result && mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                
                                $status = strtolower($row['status']);
                                $badgeStyle = '';

                                if ($status === 'received') {
                                    $badgeStyle = 'background:#dcfce7;color:#166534;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;display:inline-block;';
                                } elseif ($status === 'cancelled') {
                                    $badgeStyle = 'background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;display:inline-block;';
                                } else {
                                    $badgeStyle = 'background:#e5e7eb;color:#374151;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600;display:inline-block;';
                                }
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= date('d-m-Y H:i', strtotime($row['created_at'])); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['supplier_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= (int)$row['total_item']; ?> barang
                                </td>

                                <td>
                                    <?= (int)$row['total_qty']; ?>
                                </td>

                                <td>
                                    <span style="<?= $badgeStyle; ?>">
                                        <?= htmlspecialchars(ucfirst($row['status'])); ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="detail_stok_masuk.php?id=<?= (int)$row['id']; ?>" class="btn-secondary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">
                                    Belum ada data stok masuk.
                                </td>
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