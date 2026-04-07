<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">


<?php
$title = "Data Barang";
include '../includes/head.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$keyword = mysqli_real_escape_string($conn, $keyword);

$query = "
SELECT 
    pv.id,
    p.name AS product_name,
    t.name AS type,
    s.name AS size,
    c.name AS color,
    pv.sku,
    pv.price,
    pv.stock
FROM product_variants pv
LEFT JOIN products p ON pv.product_id = p.id
LEFT JOIN types t ON pv.type_id = t.id
LEFT JOIN sizes s ON pv.size_id = s.id
LEFT JOIN colors c ON pv.color_id = c.id
";

if ($keyword != '') {
    $query .= " WHERE 
        p.name LIKE '%$keyword%' OR
        pv.sku LIKE '%$keyword%' OR
        t.name LIKE '%$keyword%' OR
        s.name LIKE '%$keyword%' OR
        c.name LIKE '%$keyword%'
    ";
}

$result = mysqli_query($conn, $query);
?>



<div class="layout">

    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">

        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">

            <!-- HEADER HALAMAN -->
            <div class="page-header">
                <h2>Data Barang</h2>
                <form method="GET" class="search-box">
                    <input type="text" name="keyword" placeholder="Cari..."
                        value="<?= isset($_GET['keyword']) ? $_GET['keyword'] : '' ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Jenis</th>
                            <th>Ukuran</th>
                            <th>Warna</th>
                            <th>SKU</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>

                        <tr>
                            <td>
                                <?= $no++; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['product_name']); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['type'] ?? '-'); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['size']); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['color']); ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['sku']); ?>
                            </td>
                            <td>
                                <?= 'Rp ' . number_format($row['price'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?= (int) $row['stock']; ?>
                            </td>
                            <td class="action-cell">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn-icon edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="hapus.php?id=<?= $row['id']; ?>" class="btn-icon delete"
                                    onclick="return confirm('Yakin?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    <?php } ?>
                </table>
            </div>

        </div>

    </main>

    <script src="<?= url('assets/app.js') ?>"></script>
</div>
</html>