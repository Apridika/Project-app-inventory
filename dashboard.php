<?php
require_once 'includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
requireLogin();
require_once 'includes/koneksi.php';

$title = "Dashboard";
include "includes/head.php";

// Total Barang
$queryTotalBarang = "SELECT COUNT(*) AS total_barang FROM product_variants";
$resultTotalBarang = mysqli_query($conn, $queryTotalBarang);
$totalBarang = mysqli_fetch_assoc($resultTotalBarang)['total_barang'] ?? 0;

// Stok Menipis (stock > 0 dan <= 10)
$queryStokMenipis = "SELECT COUNT(*) AS stok_menipis FROM product_variants WHERE stock > 0 AND stock <= 10";
$resultStokMenipis = mysqli_query($conn, $queryStokMenipis);
$stokMenipis = mysqli_fetch_assoc($resultStokMenipis)['stok_menipis'] ?? 0;

// Stok Habis
$queryStokHabis = "SELECT COUNT(*) AS stok_habis FROM product_variants WHERE stock <= 0";
$resultStokHabis = mysqli_query($conn, $queryStokHabis);
$stokHabis = mysqli_fetch_assoc($resultStokHabis)['stok_habis'] ?? 0;

// Transaksi Hari Ini (paid saja)
$queryTransaksiHariIni = "
    SELECT COUNT(*) AS transaksi_hari_ini
    FROM transactions
    WHERE DATE(created_at) = CURDATE()
    AND status = 'paid'
";
$resultTransaksiHariIni = mysqli_query($conn, $queryTransaksiHariIni);
$transaksiHariIni = mysqli_fetch_assoc($resultTransaksiHariIni)['transaksi_hari_ini'] ?? 0;

// Pendapatan Hari Ini (paid saja)
$queryPendapatanHariIni = "
    SELECT COALESCE(SUM(total_price), 0) AS pendapatan_hari_ini
    FROM transactions
    WHERE DATE(created_at) = CURDATE()
    AND status = 'paid'
";
$resultPendapatanHariIni = mysqli_query($conn, $queryPendapatanHariIni);
$pendapatanHariIni = mysqli_fetch_assoc($resultPendapatanHariIni)['pendapatan_hari_ini'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">

<body>
    
    <div class="layout">

        <?php include "includes/sidebarai.php"; ?>

        <main class="main" id="main">

            <?php include "includes/header.php"; ?>

            <div class="content-wrap">
                <div class="page-header">
                    <h2>Dashboard</h2>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-card total-barang">
                        <h3>Total Barang</h3>
                        <div class="value"><?= number_format((int) $totalBarang); ?></div>
                    </div>

                    <div class="dashboard-card stok-menipis">
                        <h3>Stok Menipis</h3>
                        <div class="value"><?= number_format((int) $stokMenipis); ?></div>
                    </div>

                    <div class="dashboard-card stok-habis">
                        <h3>Stok Habis</h3>
                        <div class="value"><?= number_format((int) $stokHabis); ?></div>
                    </div>

                    <div class="dashboard-card transaksi-hari-ini">
                        <h3>Transaksi Hari Ini</h3>
                        <div class="value"><?= number_format((int) $transaksiHariIni); ?></div>
                    </div>

                    <div class="dashboard-card pendapatan-hari-ini">
                        <h3>Pendapatan Hari Ini</h3>
                        <div class="value">Rp <?= number_format((int) $pendapatanHariIni, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="<?= url('assets/app.js') ?>"></script>
</body>

</html>