<head>
    <link rel="stylesheet" href="/assets/style.css">
</head>

<div class="layout">

    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">

        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">

            <!-- HEADER HALAMAN -->
            <div class="page-header">
                <h2>Data Barang</h2>
                <button class="btn-primary">+ Tambah Barang</button>
            </div>

            <!-- TABLE -->
            <div class="card">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Tas Kulit</td>
                            <td>Rp 150.000</td>
                            <td>10</td>
                            <td>
                                <button class="btn-edit">Edit</button>
                                <button class="btn-delete">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

    <script src="../assets/app.js"></script>
</div>