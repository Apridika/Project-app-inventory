<head>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<div class="layout">

  <?php include "../includes/sidebarai.php"; 
include '../includes/koneksi.php';
  // ambil semua produk
$products = mysqli_query($conn, "SELECT * FROM products");

// ambil type, size, color
$types = mysqli_query($conn, "SELECT * FROM types");
$sizes = mysqli_query($conn, "SELECT * FROM sizes");
$colors = mysqli_query($conn, "SELECT * FROM colors");
  ?>

  <main class="main">

    <?php include "../includes/header.php"; ?>

    <div class="content-wrap">

      <div class="page-header">
        <h2>Tambah Produk</h2>
      </div>

      <div class="card form-card">
        <form action="proses_tambah_varian.php" method="POST" class="form-modern">

          <div class="form-grid">

            <div class="form-group">
              <label>Nama Produk</label>
              <select name="product_id">
                <?php while($p = mysqli_fetch_assoc($products)) { ?>
                <option value="<?= $p['id']; ?>">
                  <?= $p['name']; ?>
                </option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label>Jenis</label>
              <select name="type_id">
                <option value="">-- Tidak ada --</option>
                <?php while($t = mysqli_fetch_assoc($types)) { ?>
                <option value="<?= $t['id']; ?>">
                  <?= $t['name']; ?>
                </option>
                <?php } ?>
              </select>
            </div>
<div class="form-group">
  <label>Ukuran</label>
<select name="size_id">
              <?php while($s = mysqli_fetch_assoc($sizes)) { ?>
              <option value="<?= $s['id']; ?>">
                <?= $s['name']; ?>
              </option>
              <?php } ?>
            </select>
</div>
            
<div class="form-group">
  <label>Warna</label>
<select name="color_id">
              <?php while($c = mysqli_fetch_assoc($colors)) { ?>
              <option value="<?= $c['id']; ?>">
                <?= $c['name']; ?>
              </option>
              <?php } ?>
            </select>
</div>
            

            <div class="form-group">
              <label>SKU</label>
              <input type="text" name="sku">
            </div>

            <div class="form-group">
              <label>Harga</label>
              <input type="number" name="price" required>
            </div>

            <div class="form-group">
              <label>Stok</label>
              <input type="number" name="stock" required>
            </div>

          </div>

          <div class="form-actions">
            <button type="submit" class="btn-secondary">Simpan</button>
            <a href="data_barang.php" class="btn-secondary">Batal</a>
          </div>

        </form>
      </div>

    </div>

  </main>
  <script src="../assets/app.js"></script>
</div>