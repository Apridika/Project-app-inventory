<head>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<?php include "../includes/head.php"; ?>

<div class="layout">

    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">

        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">

            <div class="page-header">
                <h2>Tambah Master Data</h2>
            </div>

            <div class="card form-card">

                <div class="form-grid">

                    <!-- FORM TAMBAH PRODUK -->
                    <form action="proses_tambah_produk.php" method="POST" class="form-modern">

                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="name" placeholder="Masukkan nama produk" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">
                                Simpan Produk
                            </button>
                        </div>

                    </form>

                    <!-- FORM TAMBAH UKURAN -->
                    <form action="proses_tambah_size.php" method="POST" class="form-modern">

                        <div class="form-group">
                            <label>Ukuran</label>
                            <input type="text" name="name" placeholder="Masukkan ukuran" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">
                                Simpan Ukuran
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </main>

    <script src="../assets/app.js"></script>

</div>