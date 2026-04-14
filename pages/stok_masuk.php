<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

$title = "Stok Masuk";
include '../includes/head.php';

// ambil varian aktif
$variantsQuery = "
    SELECT 
        pv.id,
        pv.sku,
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
    ORDER BY p.name ASC, pv.sku ASC
";
$variants = mysqli_query($conn, $variantsQuery);

$variantList = [];
while ($row = mysqli_fetch_assoc($variants)) {
    $label = $row['product_name']
        . ' | ' . ($row['type_name'] ?? '-')
        . ' | ' . ($row['size_name'] ?? '-')
        . ' | ' . ($row['color_name'] ?? '-')
        . ' | SKU: ' . $row['sku']
        . ' | Stok: ' . (int)$row['stock'];

    $variantList[] = [
        'id' => (int)$row['id'],
        'label' => $label,
        'product_name' => $row['product_name'],
        'type_name' => $row['type_name'] ?? '-',
        'size_name' => $row['size_name'] ?? '-',
        'color_name' => $row['color_name'] ?? '-',
        'sku' => $row['sku'],
        'stock' => (int)$row['stock'],
    ];
}

$purchaseNumber = 'PM-' . date('Ymd-His');
?>

<!DOCTYPE html>
<html lang="id">
<body>
<div class="layout">

    <?php include '../includes/sidebarai.php'; ?>

    <main class="main">
        <?php include '../includes/header.php'; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">Stok masuk berhasil disimpan.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="content-wrap">
            <div class="page-header">
                <h2>Stok Masuk</h2>
            </div>

            <div class="card form-card">
                <form action="proses_stok_masuk.php" method="POST" class="form-modern" id="stokMasukForm">

                    <div class="form-grid">
                        <div class="form-group">
                            <label>No. Pembelian</label>
                            <input type="text" name="purchase_number" value="<?= htmlspecialchars($purchaseNumber); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Nama Supplier</label>
                            <input type="text" name="supplier_name" placeholder="Masukkan nama supplier" required>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Catatan</label>
                            <textarea name="note" rows="3" placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>

                    <div class="multi-items-wrap">
                        <div class="multi-items-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin:18px 0 12px;">
                            <h3 style="margin:0;">Daftar Barang Masuk</h3>
                            <button type="button" class="btn-secondary" id="addItemBtn">+ Tambah Barang</button>
                        </div>

                        <div id="itemsContainer">
                            
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
</div>

<script>
const variantData = <?= json_encode($variantList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const itemsContainer = document.getElementById('itemsContainer');
const addItemBtn = document.getElementById('addItemBtn');

function createItemRow() {
    const row = document.createElement('div');
    row.className = 'item-row';
    row.style.border = '1px solid #ddd';
    row.style.borderRadius = '10px';
    row.style.padding = '14px';
    row.style.marginBottom = '14px';
    row.style.background = '#060505';
    row.style.position = 'relative';

    row.innerHTML = `
        <button type="button" class="remove-item-btn" style="position:absolute;top:10px;right:10px;">Hapus</button>

        <div class="form-group" style="margin-bottom:10px;">
            <label>Cari Barang</label>
            <input type="text" class="barang-search" placeholder="Ketik nama barang / SKU / warna..." autocomplete="off">
            <div class="search-results" style="border:1px solid #ddd;border-top:none;display:none;max-height:200px;overflow-y:auto;background:#fff;position:relative;z-index:10;"></div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Barang Terpilih</label>
                <input type="text" class="selected-label" placeholder="Belum ada barang dipilih" readonly>
                <input type="hidden" name="variant_id[]" class="variant-id" required>
            </div>

            <div class="form-group">
                <label>Qty Masuk</label>
                <input type="number" name="qty[]" class="qty-input" min="1" required>
            </div>
        </div>
    `;

    const searchInput = row.querySelector('.barang-search');
    const searchResults = row.querySelector('.search-results');
    const selectedLabel = row.querySelector('.selected-label');
    const variantIdInput = row.querySelector('.variant-id');
    const removeBtn = row.querySelector('.remove-item-btn');

    function renderResults(keyword = '') {
        const q = keyword.toLowerCase().trim();

        const filtered = variantData.filter(item => {
            return item.label.toLowerCase().includes(q)
                || item.product_name.toLowerCase().includes(q)
                || item.sku.toLowerCase().includes(q)
                || item.color_name.toLowerCase().includes(q);
        });

        searchResults.innerHTML = '';

        if (filtered.length === 0) {
            searchResults.innerHTML = `<div style="padding:10px;">Barang tidak ditemukan</div>`;
            searchResults.style.display = 'block';
            return;
        }

        filtered.forEach(item => {
            const option = document.createElement('div');
            option.style.padding = '10px';
            option.style.cursor = 'pointer';
            option.style.borderTop = '1px solid #eee';
            option.textContent = item.label;

            option.addEventListener('click', function () {
                variantIdInput.value = item.id;
                selectedLabel.value = item.label;
                searchInput.value = item.product_name + ' / ' + item.sku;
                searchResults.style.display = 'none';
            });

            searchResults.appendChild(option);
        });

        searchResults.style.display = 'block';
    }

    searchInput.addEventListener('focus', function () {
        renderResults(this.value);
    });

    searchInput.addEventListener('input', function () {
        renderResults(this.value);
    });

    document.addEventListener('click', function(e) {
        if (!row.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    removeBtn.addEventListener('click', function () {
        row.remove();
        ensureAtLeastOneRow();
    });

    return row;
}

function addItemRow() {
    itemsContainer.appendChild(createItemRow());
}

function ensureAtLeastOneRow() {
    const rows = itemsContainer.querySelectorAll('.item-row');
    if (rows.length === 0) {
        addItemRow();
    }
}

addItemBtn.addEventListener('click', addItemRow);

// row pertama
addItemRow();
</script>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>