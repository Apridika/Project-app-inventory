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
    p.created_at,
    p.total_cost,
    u.name AS user_name,
    s.name AS supplier_name,
    pd.qty,
    pd.cost_price,
    pd.subtotal,
    pd.product_name,
    pd.type_name,
    pd.size_name,
    pd.color_name,
    pd.sku
FROM purchases p
LEFT JOIN purchase_details pd ON pd.purchase_id = p.id
LEFT JOIN users u ON u.id = p.created_by
LEFT JOIN suppliers s ON s.id = p.supplier_id
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
                                <th>No Pembelian</th>
                                <th>Supplier</th>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
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
                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['purchase_number']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['supplier_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['product_name']); ?>
                                    |
                                    <?= htmlspecialchars($row['type_name'] ?? '-'); ?>
                                    |
                                    <?= htmlspecialchars($row['size_name'] ?? '-'); ?>
                                    |
                                    <?= htmlspecialchars($row['color_name'] ?? '-'); ?>
                                    <br>
                                    <small>SKU:
                                        <?= htmlspecialchars($row['sku']); ?>
                                    </small>
                                </td>

                                <td>
                                    <?= (int)$row['qty']; ?>
                                </td>

                                <td>
                                    Rp
                                    <?= number_format($row['cost_price']); ?>
                                </td>

                                <td>
                                    Rp
                                    <?= number_format($row['subtotal']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['user_name']); ?>
                                </td>

                                <td>
                                    <?= date('d-m-Y H:i', strtotime($row['created_at'])); ?>
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
                                <td colspan="10" style="text-align:center;">
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