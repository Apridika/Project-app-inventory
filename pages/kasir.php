<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Kasir";
include '../includes/head.php';

// ambil semua varian aktif
$query = "
SELECT 
    pv.id,
    pv.sku,
    pv.price,
    pv.stock,
    p.name AS product_name,
    t.name AS type_name,
    s.name AS size_name,
    c.name AS color_name
FROM product_variants pv
LEFT JOIN products p ON pv.product_id = p.id
LEFT JOIN types t ON pv.type_id = t.id
LEFT JOIN sizes s ON pv.size_id = s.id
LEFT JOIN colors c ON pv.color_id = c.id
WHERE pv.is_active = 1
ORDER BY p.name ASC
";

$variants = mysqli_query($conn, $query);

$transactionNumber = 'TRX-' . date('Ymd-His');
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

                <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Transaksi kasir berhasil disimpan.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

                    <h2>Kasir</h2>
                </div>

                <form action="proses_kasir.php" method="POST">

                    <div class="card form-card">

                        <div class="form-grid">

                            <div class="form-group">
                                <label>No Transaksi</label>
                                <input type="text" name="transaction_number" value="<?= $transactionNumber ?>" readonly>
                            </div>

                        </div>

                    </div>

                    <div class="card">

                        <table class="product-table" id="kasirTable">

                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Stok</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody id="tableBody">

                                <tr>

                                    <td>
                                        <select name="variant_id[]" required onchange="updateRow(this)">
                                            <option value="">-- Pilih Barang --</option>

                                            <?php while ($v = mysqli_fetch_assoc($variants)): ?>

                                            <option value="<?= $v['id'] ?>" data-stock="<?= $v['stock'] ?>"
                                                data-price="<?= $v['price'] ?>">

                                                <?= htmlspecialchars($v['product_name']) ?>
                                                |
                                                <?= htmlspecialchars($v['type_name'] ?? '-') ?>
                                                |
                                                <?= htmlspecialchars($v['size_name'] ?? '-') ?>
                                                |
                                                <?= htmlspecialchars($v['color_name'] ?? '-') ?>
                                                | SKU:
                                                <?= htmlspecialchars($v['sku']) ?>

                                            </option>

                                            <?php endwhile; ?>

                                        </select>
                                    </td>

                                    <td class="stock">0</td>

                                    <td class="price">0</td>

                                    <td>
                                        <input type="number" name="qty[]" min="1" value="1" oninput="updateRow(this)">
                                    </td>

                                    <td class="subtotal">0</td>

                                    <td class="action-cell">
                                        <button class="btn-icon delete" type="button" onclick="removeRow(this)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                        <br>

                        <button class="btn-secondary" type="button" onclick="addRow()">
                            Tambah Item
                        </button>

                    </div>

                    <div class="card form-card">

                        <div class="form-group">
                            <label>Total</label>
                            <input type="text" id="grandTotal" readonly value="0">
                        </div>

                        <div class="form-actions">

                            <button type="submit" class="btn-secondary">
                                Simpan Transaksi
                            </button>

                            <a href="dashboard.php" class="btn-secondary">
                                Batal
                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

    <script>

        function updateRow(el) {

            let row = el.closest("tr");

            let select = row.querySelector("select");

            let stock = select.options[select.selectedIndex]
                .getAttribute("data-stock");

            let price = select.options[select.selectedIndex]
                .getAttribute("data-price");

            row.querySelector(".stock").innerText = stock || 0;

            row.querySelector(".price").innerText = price || 0;

            let qty = row.querySelector("input").value;

            let subtotal = qty * price;

            row.querySelector(".subtotal").innerText = subtotal;

            updateTotal();

        }

        function updateTotal() {

            let total = 0;

            document.querySelectorAll(".subtotal")
                .forEach(function (el) {

                    total += parseInt(el.innerText) || 0;

                });

            document.getElementById("grandTotal").value = total;

        }

        function addRow() {

            let table = document.getElementById("tableBody");

            let row = table.rows[0].cloneNode(true);

            row.querySelector(".stock").innerText = 0;

            row.querySelector(".price").innerText = 0;

            row.querySelector(".subtotal").innerText = 0;

            row.querySelector("select").selectedIndex = 0;

            row.querySelector("input").value = 1;

            table.appendChild(row);

        }

        function removeRow(btn) {

            let table = document.getElementById("tableBody");

            if (table.rows.length > 1) {

                btn.closest("tr").remove();

                updateTotal();

            }

        }

    </script>

    <script src="<?= url('assets/app.js') ?>"></script>

</body>

</html>