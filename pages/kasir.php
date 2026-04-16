<?php
require_once '../includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
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

                <h2>Kasir</h2>
                
                <!-- notifikasi -->
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

                    
                </div>

                <form action="proses_kasir.php" method="POST">

                    <div class="card form-card">

                        <div class="form-grid">

                            <div class="form-group">
                                <label>No Transaksi</label>
                                <input type="text" name="transaction_number"
                                    value="<?= htmlspecialchars($transactionNumber); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label>Nama Pembeli</label>
                                <input type="text" name="customer_name" id="customer_name"
                                    placeholder="Masukkan nama pembeli" required>
                            </div>

                            <div class="form-group">
                                <label>Dibeli Via</label>
                                <select name="channel" id="channel" onchange="handleChannelChange()" required>
                                    <option value="offline">Offline</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>

                            <div class="form-group" id="shopPlatformGroup" style="display: none;">
                                <label>Shop / Platform</label>
                                <select name="shop_platform" id="shop_platform" onchange="handleShopChange()">
                                    <option value="">-- Pilih Shop --</option>
                                    <option value="Shopee">Shopee</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="TikTok Shop">TikTok Shop</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select name="payment_method" id="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="shopeepay">ShopeePay</option>
                                </select>
                            </div>

                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Catatan</label>
                                <textarea name="note" rows="3" placeholder="Catatan transaksi (opsional)"></textarea>
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
                                    <td style="min-width: 320px;">
                                        <select name="variant_id[]" class="item-select" required
                                            onchange="updateRow(this)">
                                            <option value="">-- Pilih Barang --</option>
                                            <?php while ($v = mysqli_fetch_assoc($variants)): ?>
                                            <option value="<?= (int) $v['id']; ?>"
                                                data-stock="<?= (int) $v['stock']; ?>"
                                                data-price="<?= (int) $v['price']; ?>">
                                                <?= htmlspecialchars($v['product_name'] ?? '-'); ?>
                                                |
                                                <?= htmlspecialchars($v['type_name'] ?? '-'); ?>
                                                |
                                                <?= htmlspecialchars($v['size_name'] ?? '-'); ?>
                                                |
                                                <?= htmlspecialchars($v['color_name'] ?? '-'); ?>
                                                | SKU:
                                                <?= htmlspecialchars($v['sku'] ?? '-'); ?>
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

                    <div class="card form-card" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Total</label>
                            <input type="text" id="grandTotal" readonly value="0">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-secondary">
                                Simpan Transaksi
                            </button>

                            <a href="<?= url('dashboard.php') ?>" class="btn-secondary">
                                Batal
                            </a>
                        </div>
                    </div>

                </form>

            </div>

        </main>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container {
            width: 100% !important;
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

        .select2-dropdown {
            border-radius: 8px !important;
            overflow: hidden;
        }
    </style>

    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka || 0);
        }

        function initSelect2(target = '.item-select') {
            $(target).select2({
                placeholder: '-- Pilih Barang --',
                allowClear: true,
                width: '100%'
            });
        }

        function updateRow(el) {
            const row = el.closest("tr");
            const select = row.querySelector("select[name='variant_id[]']");
            const qtyInput = row.querySelector("input[name='qty[]']");

            const selectedOption = select.options[select.selectedIndex];
            const stock = selectedOption ? parseInt(selectedOption.getAttribute("data-stock")) || 0 : 0;
            const price = selectedOption ? parseInt(selectedOption.getAttribute("data-price")) || 0 : 0;

            let qty = parseInt(qtyInput.value) || 0;

            if (qty < 1) {
                qty = 1;
                qtyInput.value = 1;
            }

            const subtotal = qty * price;

            row.querySelector(".stock").innerText = stock;
            row.querySelector(".price").innerText = formatRupiah(price);
            row.querySelector(".subtotal").innerText = formatRupiah(subtotal);

            updateGrandTotal();
        }

        function updateGrandTotal() {
            let grandTotal = 0;

            document.querySelectorAll("#tableBody tr").forEach(function (row) {
                const qtyInput = row.querySelector("input[name='qty[]']");
                const select = row.querySelector("select[name='variant_id[]']");
                const selectedOption = select.options[select.selectedIndex];
                const price = selectedOption ? parseInt(selectedOption.getAttribute("data-price")) || 0 : 0;
                const qty = parseInt(qtyInput.value) || 0;

                grandTotal += (qty * price);
            });

            document.getElementById("grandTotal").value = formatRupiah(grandTotal);
        }

        function resetSelect2Element(select) {
            select.removeAttribute("data-select2-id");
            select.classList.remove("select2-hidden-accessible");
            select.removeAttribute("tabindex");
            select.removeAttribute("aria-hidden");
            select.style.display = "";

            select.querySelectorAll("option").forEach(function (option) {
                option.removeAttribute("data-select2-id");
                option.selected = false;
            });
        }

        function addRow() {
            const tableBody = document.getElementById("tableBody");
            const firstRow = tableBody.querySelector("tr");
            const newRow = firstRow.cloneNode(true);

            const clonedSelect2Container = newRow.querySelector(".select2");
            if (clonedSelect2Container) {
                clonedSelect2Container.remove();
            }

            const select = newRow.querySelector("select[name='variant_id[]']");
            const qtyInput = newRow.querySelector("input[name='qty[]']");

            resetSelect2Element(select);

            select.selectedIndex = 0;
            select.value = "";
            qtyInput.value = 1;

            newRow.querySelector(".stock").innerText = "0";
            newRow.querySelector(".price").innerText = "0";
            newRow.querySelector(".subtotal").innerText = "0";

            tableBody.appendChild(newRow);
            initSelect2(select);
            updateGrandTotal();
        }

        function removeRow(btn) {
            const tableBody = document.getElementById("tableBody");

            if (tableBody.rows.length > 1) {
                const row = btn.closest("tr");
                const select = row.querySelector("select[name='variant_id[]']");

                if ($(select).hasClass('select2-hidden-accessible')) {
                    $(select).select2('destroy');
                }

                row.remove();
                updateGrandTotal();
            }
        }

        function handleChannelChange() {
            const channel = document.getElementById("channel").value;
            const shopGroup = document.getElementById("shopPlatformGroup");
            const shopSelect = document.getElementById("shop_platform");
            const paymentMethod = document.getElementById("payment_method");

            if (channel === "online") {
                shopGroup.style.display = "block";
            } else {
                shopGroup.style.display = "none";
                shopSelect.value = "";
                paymentMethod.value = "cash";
                paymentMethod.disabled = false;
            }
        }

        function handleShopChange() {
            const shop = document.getElementById("shop_platform").value;
            const paymentMethod = document.getElementById("payment_method");

            if (shop && shop.toLowerCase() === "shopee") {
                paymentMethod.value = "shopeepay";
                paymentMethod.disabled = true;
            } else {
                paymentMethod.disabled = false;

                if (paymentMethod.value === "shopeepay") {
                    paymentMethod.value = "transfer";
                }
            }
        }

        $(document).ready(function () {
            initSelect2();
            handleChannelChange();
            handleShopChange();
            updateGrandTotal();

            $(document).on('change', "select[name='variant_id[]']", function () {
                updateRow(this);
            });

            $(document).on('input', "input[name='qty[]']", function () {
                updateRow(this);
            });
        });
    </script>

    <script src="<?= url('assets/app.js') ?>"></script>
</body>

</html>