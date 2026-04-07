<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: stok_masuk.php");
    exit;
}

$purchaseNumber = trim($_POST['purchase_number'] ?? '');
$supplierId     = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$variantId      = (int)($_POST['variant_id'] ?? 0);
$qty            = (int)($_POST['qty'] ?? 0);
$costPrice      = (int)($_POST['cost_price'] ?? -1);
$note           = trim($_POST['note'] ?? '');
$createdBy      = (int)($_SESSION['user_id'] ?? 0);

if ($purchaseNumber === '') {
    die("Nomor pembelian wajib ada.");
}

if ($variantId <= 0) {
    die("Varian barang wajib dipilih.");
}

if ($qty <= 0) {
    die("Qty masuk harus lebih dari 0.");
}

if ($costPrice < 0) {
    die("Harga modal tidak valid.");
}

if ($createdBy <= 0) {
    die("Session user tidak valid.");
}

mysqli_begin_transaction($conn);

try {
    // Ambil data varian
    $variantQuery = "
        SELECT 
            pv.id,
            pv.product_id,
            pv.type_id,
            pv.size_id,
            pv.color_id,
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
        WHERE pv.id = ?
        LIMIT 1
    ";

    $stmtVariant = mysqli_prepare($conn, $variantQuery);
    mysqli_stmt_bind_param($stmtVariant, "i", $variantId);
    mysqli_stmt_execute($stmtVariant);
    $variantResult = mysqli_stmt_get_result($stmtVariant);
    $variant = mysqli_fetch_assoc($variantResult);

    if (!$variant) {
        throw new Exception("Data varian tidak ditemukan.");
    }

    $stockBefore = (int)$variant['stock'];
    $stockAfter  = $stockBefore + $qty;
    $subtotal    = $qty * $costPrice;

    // 1. Simpan ke purchases
    $status = 'received';
    $stmtPurchase = mysqli_prepare($conn, "
        INSERT INTO purchases (
            purchase_number,
            supplier_id,
            note,
            total_cost,
            status,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmtPurchase,
        "sisisi",
        $purchaseNumber,
        $supplierId,
        $note,
        $subtotal,
        $status,
        $createdBy
    );

    if (!mysqli_stmt_execute($stmtPurchase)) {
        throw new Exception("Gagal menyimpan data pembelian.");
    }

    $purchaseId = mysqli_insert_id($conn);

    // 2. Simpan ke purchase_details
    $productName = $variant['product_name'];
    $typeName    = $variant['type_name'];
    $sizeName    = $variant['size_name'];
    $colorName   = $variant['color_name'];
    $sku         = $variant['sku'];

    $stmtDetail = mysqli_prepare($conn, "
        INSERT INTO purchase_details (
            purchase_id,
            variant_id,
            qty,
            cost_price,
            subtotal,
            product_name,
            type_name,
            size_name,
            color_name,
            sku
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmtDetail,
        "iiiiisssss",
        $purchaseId,
        $variantId,
        $qty,
        $costPrice,
        $subtotal,
        $productName,
        $typeName,
        $sizeName,
        $colorName,
        $sku
    );

    if (!mysqli_stmt_execute($stmtDetail)) {
        throw new Exception("Gagal menyimpan detail pembelian.");
    }

    // 3. Update stok product_variants
    $stmtUpdateStock = mysqli_prepare($conn, "
        UPDATE product_variants
        SET stock = ?
        WHERE id = ?
    ");

    mysqli_stmt_bind_param($stmtUpdateStock, "ii", $stockAfter, $variantId);

    if (!mysqli_stmt_execute($stmtUpdateStock)) {
        throw new Exception("Gagal mengupdate stok barang.");
    }

    // 4. Simpan ke stock_logs
    $type = 'IN';
    $referenceType = 'purchase';

    $stmtStockLog = mysqli_prepare($conn, "
        INSERT INTO stock_logs (
            variant_id,
            qty,
            type,
            stock_before,
            stock_after,
            reference_type,
            reference_id,
            note,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmtStockLog,
        "iisissisi",
        $variantId,
        $qty,
        $type,
        $stockBefore,
        $stockAfter,
        $referenceType,
        $purchaseId,
        $note,
        $createdBy
    );

    if (!mysqli_stmt_execute($stmtStockLog)) {
        throw new Exception("Gagal menyimpan log stok.");
    }

    mysqli_commit($conn);

    redirectTo('pages/stok_masuk.php?success=1');
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    redirectTo('pages/stok_masuk.php?error=' . urlencode($e->getMessage()));
}
