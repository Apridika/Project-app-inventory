<?php
require_once '../includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/kasir.php');
}

$invoiceNumber = trim($_POST['transaction_number'] ?? '');
$variantIds    = $_POST['variant_id'] ?? [];
$qtys          = $_POST['qty'] ?? [];
$createdBy     = (int) ($_SESSION['user_id'] ?? 0);

$customerName  = trim($_POST['customer_name'] ?? '');
$channel       = trim($_POST['channel'] ?? 'offline');
$paymentMethod = trim($_POST['payment_method'] ?? 'cash');
$note          = trim($_POST['note'] ?? '');
$shopPlatform  = trim($_POST['shop_platform'] ?? '');

if ($invoiceNumber === '') {
    redirectTo('pages/kasir.php?error=' . urlencode('Nomor transaksi wajib ada.'));
}

if ($createdBy <= 0) {
    redirectTo('pages/kasir.php?error=' . urlencode('Session user tidak valid.'));
}

if ($customerName === '') {
    redirectTo('pages/kasir.php?error=' . urlencode('Nama pembeli wajib diisi.'));
}

if (!is_array($variantIds) || !is_array($qtys) || count($variantIds) === 0) {
    redirectTo('pages/kasir.php?error=' . urlencode('Item transaksi tidak valid.'));
}

$allowedChannels = ['offline', 'online', 'shopee'];
$allowedPayments = ['cash', 'transfer', 'shopeepay'];

if (!in_array($channel, $allowedChannels, true)) {
    $channel = 'offline';
}

if (!in_array($paymentMethod, $allowedPayments, true)) {
    $paymentMethod = 'cash';
}

if ($channel === 'online' && strtolower($shopPlatform) === 'shopee') {
    $channel = 'shopee';
    $paymentMethod = 'shopeepay';
}

if ($shopPlatform !== '') {
    $platformNote = 'Platform: ' . $shopPlatform;
    $note = ($note !== '') ? ($platformNote . ' | ' . $note) : $platformNote;
}

mysqli_begin_transaction($conn);

try {
    // gabungkan qty jika barang yang sama dipilih lebih dari sekali
    $groupedQty = [];

    for ($i = 0; $i < count($variantIds); $i++) {
        $variantId = (int) ($variantIds[$i] ?? 0);
        $qtyRaw    = (float) ($qtys[$i] ?? 0);

        if ($variantId <= 0 || $qtyRaw <= 0) {
            continue;
        }

        if (!isset($groupedQty[$variantId])) {
            $groupedQty[$variantId] = 0;
        }

        $groupedQty[$variantId] += $qtyRaw;
    }

    if (count($groupedQty) === 0) {
        throw new Exception('Tidak ada item transaksi yang valid.');
    }

    $items = [];
    $totalPrice = 0;

    $stmtVariant = mysqli_prepare($conn, "
        SELECT
            pv.id,
            pv.price,
            pv.stock,
            pv.unit,
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
        FOR UPDATE
    ");

    if (!$stmtVariant) {
        throw new Exception('Gagal menyiapkan query varian: ' . mysqli_error($conn));
    }

    foreach ($groupedQty as $variantId => $qty) {
        mysqli_stmt_bind_param($stmtVariant, "i", $variantId);
        mysqli_stmt_execute($stmtVariant);
        $variantResult = mysqli_stmt_get_result($stmtVariant);
        $variant = mysqli_fetch_assoc($variantResult);

        if (!$variant) {
            throw new Exception("Varian barang tidak ditemukan. ID: " . $variantId);
        }

        $unit = $variant['unit'] ?? 'pcs';

        // meter boleh desimal, selain meter dibulatkan
        if ($unit !== 'meter') {
            $qty = max(1, round($qty));
        } else {
            $qty = round($qty, 2);
        }

        if ($qty <= 0) {
            continue;
        }

        $stockBefore = (float) $variant['stock'];

        if ($qty > $stockBefore) {
            throw new Exception(
                "Stok tidak cukup untuk SKU " . $variant['sku'] .
                ". Stok tersedia: " . number_format($stockBefore, 2, '.', '')
            );
        }

        $price      = (int) $variant['price'];
        $discount   = 0;
        $subtotal   = $price * $qty;
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
            'unit'          => $unit,
        ];

        $totalPrice += $subtotal;
    }

    if (count($items) === 0) {
        throw new Exception("Tidak ada item transaksi yang valid.");
    }

    $status = 'paid';

    $stmtTransaction = mysqli_prepare($conn, "
        INSERT INTO transactions (
            invoice_number,
            customer_name,
            channel,
            status,
            payment_method,
            note,
            total_price,
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtTransaction) {
        throw new Exception('Gagal menyiapkan query transaksi: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmtTransaction,
        "ssssssii",
        $invoiceNumber,
        $customerName,
        $channel,
        $status,
        $paymentMethod,
        $note,
        $totalPrice,
        $createdBy
    );

    if (!mysqli_stmt_execute($stmtTransaction)) {
        throw new Exception("Gagal menyimpan transaksi kasir: " . mysqli_error($conn));
    }

    $transactionId = mysqli_insert_id($conn);

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

    if (!$stmtDetail) {
        throw new Exception('Gagal menyiapkan query detail transaksi: ' . mysqli_error($conn));
    }

    $stmtUpdateStock = mysqli_prepare($conn, "
        UPDATE product_variants
        SET stock = ?
        WHERE id = ?
    ");

    if (!$stmtUpdateStock) {
        throw new Exception('Gagal menyiapkan query update stok: ' . mysqli_error($conn));
    }

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

    if (!$stmtLog) {
        throw new Exception('Gagal menyiapkan query log stok: ' . mysqli_error($conn));
    }

    foreach ($items as $item) {
        $productSnapshot = null;

        mysqli_stmt_bind_param(
            $stmtDetail,
            "iidiiissssss",
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
            throw new Exception("Gagal menyimpan detail transaksi: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $stmtUpdateStock,
            "di",
            $item['stock_after'],
            $item['variant_id']
        );

        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception("Gagal mengurangi stok barang: " . mysqli_error($conn));
        }

        $type = 'OUT';
        $referenceType = 'sale';
        $stockNote = 'Transaksi kasir: ' . $invoiceNumber;

        mysqli_stmt_bind_param(
            $stmtLog,
            "idsddsisi",
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
            throw new Exception("Gagal menyimpan log stok keluar: " . mysqli_error($conn));
        }
    }

    mysqli_commit($conn);
    redirectTo('pages/kasir.php?success=1');

} catch (Exception $e) {
    mysqli_rollback($conn);
    redirectTo('pages/kasir.php?error=' . urlencode($e->getMessage()));
}