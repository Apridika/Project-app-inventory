<head>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<?php
include '../includes/koneksi.php';
$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM product_variants WHERE id=$id");
$row = mysqli_fetch_assoc($data);
?>

<div class="layout">

    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">

        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">

            <div class="page-header">
                <h2>Edit Produk</h2>
            </div>

            <div class="card form-card">
                <form action="proses_edit.php" method="POST" class="form-modern">

                    <input type="hidden" name="id" value="<?= $row['id']; ?>">

                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku" value="<?= $row['sku']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="price" value="<?= $row['price']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stock" value="<?= $row['stock']; ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-secondary">Update</button>
                        <a href="data_barang.php" class="btn-secondary">Batal</a>
                    </div>

                </form>
            </div>

        </div>

    </main>
    <script src="../assets/app"></script>
</div>