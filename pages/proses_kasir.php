<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/kasir.php');
}

$invoiceNumber = trim($_POST['transaction_number'] ?? '');
$variantIds    = $_POST['variant_id'] ?? [];
$qtys          = $_POST['qty'] ?? [];
$createdBy     = (int) ($_SESSION['user_id'] ?? 0);

if ($invoiceNumber === '') {
    redirectTo('pages/kasir.php?error=' . urlencode('Nomor transaksi wajib ada.'));
}

if ($createdBy <= 0) {
    redirectTo('pages/kasir.php?error=' . urlencode('Session user tidak valid.'));
}

if (!is_array($variantIds) || !is_array($qtys) || count($variantIds) === 0) {
    redirectTo('pages/kasir.php?error=' . urlencode('Item transaksi tidak valid.'));
}

mysqli_begin_transaction($conn);

try {
    $items = [];
    $subtotalPrice = 0;

    for ($i = 0; $i < count($variantIds); $i++) {
        $variantId = (int) ($variantIds[$i] ?? 0);
        $qty = (int) ($qtys[$i] ?? 0);

        if ($variantId <= 0 || $qty <= 0) {
            continue;
        }

        $stmtVariant = mysqli_prepare($conn, "
            SELECT
                pv.id,
                pv.price,
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
        mysqli_stmt_bind_param($stmtVariant, "i", $variantId);
        mysqli_stmt_execute($stmtVariant);
        $variantResult = mysqli_stmt_get_result($stmtVariant);
        $variant = mysqli_fetch_assoc($variantResult);

        if (!$variant) {
            throw new Exception("Varian barang tidak ditemukan.");
        }

        $stockBefore = (int) $variant['stock'];

        if ($qty > $stockBefore) {
            throw new Exception("Stok tidak cukup untuk SKU " . $variant['sku']);
        }

        $price = (int) $variant['price'];
        $discount = 0;
        $subtotal = ($price - $discount) * $qty;
        $stockAfter = $stockBefore - $qty;

        $items[] = [
            'variant_id'    => $variantId,
            'qty'           => $qty,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'stock_before'  => $stockBefore,
            'stock_after'   => $stockAfter,
            'sku'           => $variant['sku'],
            'product_name'  => $variant['product_name'],
            'type_name'     => $variant['type_name'],
            'size_name'     => $variant['size_name'],
            'color_name'    => $variant['color_name'],
        ];

        $subtotalPrice += $subtotal;
    }

    if (count($items) === 0) {
        throw new Exception("Tidak ada item transaksi yang valid.");
    }

    $status = 'paid';
    $channel = 'offline';
    $paymentMethod = 'cash';
    $customerName = null;
    $customerPhone = null;
    $note = '';
    $discountTotal = 0;
    $totalPrice = $subtotalPrice;
    $paidAmount = $totalPrice;
    $changeAmount = 0;

    $stmtTransaction = mysqli_prepare($conn, "
        INSERT INTO transactions (
            invoice_number,
            customer_name,
            customer_phone,
            channel,
            status,
            payment_method,
            note,
            subtotal_price,
            discount_total,
            total_price,
            paid_amount,
            change_amount,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmtTransaction,
        "sssssssiiiiii",
        $invoiceNumber,
        $customerName,
        $customerPhone,
        $channel,
        $status,
        $paymentMethod,
        $note,
        $subtotalPrice,
        $discountTotal,
        $totalPrice,
        $paidAmount,
        $changeAmount,
        $createdBy
    );

    if (!mysqli_stmt_execute($stmtTransaction)) {
        throw new Exception("Gagal menyimpan transaksi kasir.");
    }

    $transactionId = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $productSnapshot = null;

        $stmtDetail = mysqli_prepare($conn, "
            INSERT INTO transaction_details (
                transaction_id,
                variant_id,
                qty,
                price,
                discount,
                subtotal,
                product_name,
                type_name,
                size_name,
                color_name,
                product_snapshot,
                sku
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmtDetail,
            "iiiiiissssss",
            $transactionId,
            $item['variant_id'],
            $item['qty'],
            $item['price'],
            $item['discount'],
            $item['subtotal'],
            $item['product_name'],
            $item['type_name'],
            $item['size_name'],
            $item['color_name'],
            $productSnapshot,
            $item['sku']
        );

        if (!mysqli_stmt_execute($stmtDetail)) {
            throw new Exception("Gagal menyimpan detail transaksi.");
        }

        $stmtUpdateStock = mysqli_prepare($conn, "
            UPDATE product_variants
            SET stock = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param(
            $stmtUpdateStock,
            "ii",
            $item['stock_after'],
            $item['variant_id']
        );

        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception("Gagal mengurangi stok barang.");
        }

        $type = 'OUT';
        $referenceType = 'sale';
        $stockNote = 'Transaksi kasir: ' . $invoiceNumber;

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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $stmtLog,
            "iisissisi",
            $item['variant_id'],
            $item['qty'],
            $type,
            $item['stock_before'],
            $item['stock_after'],
            $referenceType,
            $transactionId,
            $stockNote,
            $createdBy
        );

        if (!mysqli_stmt_execute($stmtLog)) {
            throw new Exception("Gagal menyimpan log stok keluar.");
        }
    }

    mysqli_commit($conn);

    redirectTo('pages/kasir.php?success=1');

} catch (Exception $e) {
    mysqli_rollback($conn);
    redirectTo('pages/kasir.php?error=' . urlencode($e->getMessage()));
}