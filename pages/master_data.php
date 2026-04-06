<?php
require_once '../includes/auth_check.php';
require_once '../includes/koneksi.php';
?>

<?php
include '../includes/head.php';

$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
$types    = mysqli_query($conn, "SELECT * FROM types ORDER BY id DESC");
$sizes    = mysqli_query($conn, "SELECT * FROM sizes ORDER BY id DESC");
$colors   = mysqli_query($conn, "SELECT * FROM colors ORDER BY id DESC");
?>

<div class="layout">
    <?php include "../includes/sidebarai.php"; ?>

    <main class="main">
        <?php include "../includes/header.php"; ?>

        <div class="content-wrap">
            <div class="page-header">
                <h2>Master Data</h2>
            </div>

            <div class="form-grid">

                <!-- Produk -->
                <div class="card">
                    <h3>Master Produk</h3>
                    <br>
                    <form action="proses_tambah_master.php" method="POST" class="form-modern">
                        <input type="hidden" name="table" value="products">
                        <div class="form-group">
                            <label>Nama Produk</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">Simpan</button>
                        </div>
                    </form>

                    <br>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($products)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td class="action-cell">
                                    <a href="edit_master.php?table=products&id=<?= $row['id']; ?>" class="btn-icon edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="hapus_master.php?table=products&id=<?= $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Jenis -->
                <div class="card">
                    <h3>Master Jenis</h3>
                    <br>
                    <form action="proses_tambah_master.php" method="POST" class="form-modern">
                        <input type="hidden" name="table" value="types">
                        <div class="form-group">
                            <label>Nama Jenis</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">Simpan</button>
                        </div>
                    </form>

                    <br>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($types)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td class="action-cell">
                                    <a href="edit_master.php?table=types&id=<?= $row['id']; ?>" class="btn-icon edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="hapus_master.php?table=types&id=<?= $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Ukuran -->
                <div class="card">
                    <h3>Master Ukuran</h3>
                    <br>
                    <form action="proses_tambah_master.php" method="POST" class="form-modern">
                        <input type="hidden" name="table" value="sizes">
                        <div class="form-group">
                            <label>Nama Ukuran</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">Simpan</button>
                        </div>
                    </form>

                    <br>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($sizes)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td class="action-cell">
                                    <a href="edit_master.php?table=sizes&id=<?= $row['id']; ?>" class="btn-icon edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="hapus_master.php?table=sizes&id=<?= $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Warna -->
                <div class="card">
                    <h3>Master Warna</h3>
                    <br>
                    <form action="proses_tambah_master.php" method="POST" class="form-modern">
                        <input type="hidden" name="table" value="colors">
                        <div class="form-group">
                            <label>Nama Warna</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">Simpan</button>
                        </div>
                    </form>

                    <br>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($colors)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td class="action-cell">
                                    <a href="edit_master.php?table=colors&id=<?= $row['id']; ?>" class="btn-icon edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="hapus_master.php?table=colors&id=<?= $row['id']; ?>" class="btn-icon delete" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>

    <script src="../assets/app.js"></script>
</div>