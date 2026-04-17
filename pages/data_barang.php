<?php
require_once '../includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
requireLogin();
require_once '../includes/koneksi.php';

$title = "Data Barang";
include '../includes/head.php';

$variantId = isset($_GET['variant_id']) ? (int) $_GET['variant_id'] : 0;

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$keyword = mysqli_real_escape_string($conn, $keyword);
$filter = mysqli_real_escape_string($conn, $filter);

// query dropdown barang
$queryOptions = "
    SELECT 
        pv.id,
        p.name AS product_name,
        t.name AS type,
        s.name AS size,
        c.name AS color,
        pv.sku
    FROM product_variants pv
    LEFT JOIN products p ON pv.product_id = p.id
    LEFT JOIN types t ON pv.type_id = t.id
    LEFT JOIN sizes s ON pv.size_id = s.id
    LEFT JOIN colors c ON pv.color_id = c.id
    ORDER BY p.name ASC, pv.id ASC
";
$resultOptions = mysqli_query($conn, $queryOptions);

// query tabel data barang
$query = "
SELECT 
    pv.id,
    p.name AS product_name,
    t.name AS type,
    s.name AS size,
    c.name AS color,
    pv.sku,
    pv.price,
    pv.stock,
    pv.min_stock,
    pv.unit
FROM product_variants pv
LEFT JOIN products p ON pv.product_id = p.id
LEFT JOIN types t ON pv.type_id = t.id
LEFT JOIN sizes s ON pv.size_id = s.id
LEFT JOIN colors c ON pv.color_id = c.id
WHERE 1=1
";

// Filter stok menipis
if ($filter === 'menipis') {
    $query .= " AND pv.stock > 0 AND pv.stock <= pv.min_stock ";
}

// Filter stok habis
if ($filter === 'habis') {
    $query .= " AND pv.stock <= 0 ";
}

if ($variantId > 0) {
    $query .= " WHERE pv.id = $variantId ";
}

$query .= " ORDER BY p.name ASC, pv.id ASC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<body>

    <div class="layout">

        <?php include "../includes/sidebarai.php"; ?>

        <main class="main">

            <?php include "../includes/header.php"; ?>

            <div class="content-wrap">

                <div class="page-header">
                    <h2>Data Barang</h2>

                    <form method="GET" class="search-box"
                        style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">

                        <select name="variant_id" id="variant_id" style="min-width: 350px;">
                            <option value="">Cari barang...</option>
                            <?php while ($opt = mysqli_fetch_assoc($resultOptions)) : ?>
                            <option value="<?= (int) $opt['id']; ?>" <?=$variantId===(int) $opt['id'] ? 'selected' : ''
                                ; ?>>
                                <?= htmlspecialchars($opt['product_name']); ?>
                                |
                                <?= htmlspecialchars($opt['type'] ?? '-'); ?>
                                |
                                <?= htmlspecialchars($opt['size'] ?? '-'); ?>
                                |
                                <?= htmlspecialchars($opt['color'] ?? '-'); ?>
                                | SKU:
                                <?= htmlspecialchars($opt['sku']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>

                        <button type="submit">Search</button>

                        <a href="data_barang.php" class="btn-secondary"
                            style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; padding:8px 12px;">
                            Reset
                        </a>
                    </form>
                </div>

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
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
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
                                    <?= htmlspecialchars($row['size'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['color'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['sku']); ?>
                                </td>
                                <td>
                                    <?= 'Rp ' . number_format((int) $row['price'], 0, ',', '.'); ?>
                                </td>
                                <td>
    <?php
    $stockFormatted = rtrim(rtrim(number_format((float) $row['stock'], 2, '.', ''), '0'), '.');
    echo htmlspecialchars($stockFormatted . ' ' . ($row['unit'] ?? 'pcs'));
    ?>
</td>

                                <?php $role = $_SESSION['role'] ?? ''; ?>

                                <td class="action-cell">
                                
                                    <a href="edit.php?id=<?= (int) $row['id']; ?>" class="btn-icon edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if (in_array($role, ['admin', 'owner'], true)): ?>
                                    <a href="hapus.php?id=<?= (int) $row['id']; ?>" class="btn-icon delete"
                                        onclick="return confirm('Yakin?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center;">Data barang tidak ditemukan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container {
            width: 350px !important;
        }

        .select2-container .select2-selection--single {
            height: 42px !important;
            border: 1px solid #ccc !important;
            border-radius: 8px !important;
            padding: 6px 10px !important;
            display: flex !important;
            align-items: center !important;
            background: #fff !important;
        }

        .select2-container .select2-selection__rendered {
            line-height: 28px !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
        }

        .select2-container .select2-selection__arrow {
            height: 40px !important;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#variant_id').select2({
                placeholder: 'Cari barang...',
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>

    <script src="<?= url('assets/app.js') ?>"></script>
</body>

</html>