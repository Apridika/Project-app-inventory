<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: stok_masuk.php");
    exit;
}

$purchaseNumber = trim($_POST['purchase_number'] ?? '');
$supplierName   = trim($_POST['supplier_name'] ?? '');
$note           = trim($_POST['note'] ?? '');
$variantIds     = $_POST['variant_id'] ?? [];
$qtys           = $_POST['qty'] ?? [];

$createdBy = (int)($_SESSION['user_id'] ?? 0);

if ($purchaseNumber === '') {
    die("Nomor pembelian wajib ada.");
}

if ($supplierName === '') {
    die("Nama supplier wajib diisi.");
}

if (!is_array($variantIds) || count($variantIds) === 0) {
    die("Minimal pilih 1 barang.");
}

if ($createdBy <= 0) {
    die("Session user tidak valid.");
}

mysqli_begin_transaction($conn);

try {

    /*
    =========================
    1. SIMPAN PURCHASE
    =========================
    */

    $status = 'received';

    $stmtPurchase = mysqli_prepare($conn, "
        INSERT INTO purchases (
            purchase_number,
            supplier_name,
            note,
            status,
            created_by
        ) VALUES (?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmtPurchase,
        "ssssi",
        $purchaseNumber,
        $supplierName,
        $note,
        $status,
        $createdBy
    );

    mysqli_stmt_execute($stmtPurchase);

    $purchaseId = mysqli_insert_id($conn);

    /*
    =========================
    LOOP ITEM
    =========================
    */

    for ($i = 0; $i < count($variantIds); $i++) {

        $variantId = (int)($variantIds[$i] ?? 0);
        $qty       = (int)($qtys[$i] ?? 0);

        if ($variantId <= 0 || $qty <= 0) {
            continue;
        }

        /*
        =========================
        AMBIL DATA VARIAN
        =========================
        */

        $stmtVariant = mysqli_prepare($conn, "
            SELECT 
                pv.stock,
                pv.sku,
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
        ");

        mysqli_stmt_bind_param(
            $stmtVariant,
            "i",
            $variantId
        );

        mysqli_stmt_execute($stmtVariant);

        $result = mysqli_stmt_get_result($stmtVariant);

        $variant = mysqli_fetch_assoc($result);

        if (!$variant) {
            throw new Exception("Varian tidak ditemukan.");
        }

        $stockBefore = (int)$variant['stock'];
        $stockAfter  = $stockBefore + $qty;

        /*
        =========================
        UPDATE STOK
        =========================
        */

        $stmtUpdateStock = mysqli_prepare($conn, "
            UPDATE product_variants
            SET stock = ?
            WHERE id = ?
        ");

        mysqli_stmt_bind_param(
            $stmtUpdateStock,
            "ii",
            $stockAfter,
            $variantId
        );

        mysqli_stmt_execute($stmtUpdateStock);

        /*
        =========================
        SIMPAN DETAIL
        =========================
        */

        $stmtDetail = mysqli_prepare($conn, "
            INSERT INTO purchase_details (
                purchase_id,
                variant_id,
                qty,
                product_name,
                type_name,
                size_name,
                color_name,
                sku
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmtDetail,
            "iiisssss",
            $purchaseId,
            $variantId,
            $qty,
            $variant['product_name'],
            $variant['type_name'],
            $variant['size_name'],
            $variant['color_name'],
            $variant['sku']
        );

        mysqli_stmt_execute($stmtDetail);

        /*
        =========================
        SIMPAN LOG STOK
        =========================
        */

        $logNote = "Stok masuk: " . $purchaseNumber;

        $stmtLog = mysqli_prepare($conn, "
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
            ) VALUES (?, ?, 'IN', ?, ?, 'purchase', ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmtLog,
            "iiiiisi",
            $variantId,
            $qty,
            $stockBefore,
            $stockAfter,
            $purchaseId,
            $logNote,
            $createdBy
        );

        mysqli_stmt_execute($stmtLog);
    }

    mysqli_commit($conn);

    header("Location: stok_masuk.php?success=1");
    exit;

} catch (Throwable $e) {

    mysqli_rollback($conn);

    header("Location: stok_masuk.php?error=" . urlencode($e->getMessage()));
    exit;
}