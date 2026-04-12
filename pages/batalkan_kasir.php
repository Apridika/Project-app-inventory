<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/riwayat_kasir.php');
}

$transactionId = (int) ($_POST['transaction_id'] ?? 0);
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($transactionId <= 0) {
    redirectTo('pages/riwayat_kasir.php?error=' . urlencode('ID transaksi tidak valid.'));
}

if ($userId <= 0) {
    redirectTo('pages/riwayat_kasir.php?error=' . urlencode('Session user tidak valid.'));
}

mysqli_begin_transaction($conn);

try {
    // Lock transaksi
    $stmtTransaction = mysqli_prepare($conn, "
        SELECT id, invoice_number, status
        FROM transactions
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmtTransaction, "i", $transactionId);
    mysqli_stmt_execute($stmtTransaction);
    $transactionResult = mysqli_stmt_get_result($stmtTransaction);
    $transaction = mysqli_fetch_assoc($transactionResult);

    if (!$transaction) {
        throw new Exception("Transaksi tidak ditemukan.");
    }

    if ($transaction['status'] === 'cancelled') {
        throw new Exception("Transaksi ini sudah dibatalkan sebelumnya.");
    }

    if ($transaction['status'] !== 'paid') {
        throw new Exception("Hanya transaksi berstatus paid yang bisa dibatalkan.");
    }

    $stmtDetail = mysqli_prepare($conn, "
        SELECT variant_id, qty, sku, product_name
        FROM transaction_details
        WHERE transaction_id = ?
    ");
    mysqli_stmt_bind_param($stmtDetail, "i", $transactionId);
    mysqli_stmt_execute($stmtDetail);
    $detailResult = mysqli_stmt_get_result($stmtDetail);

    if (mysqli_num_rows($detailResult) === 0) {
        throw new Exception("Detail transaksi tidak ditemukan.");
    }

    $stmtVariant = mysqli_prepare($conn, "
        SELECT stock
        FROM product_variants
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmtUpdateStock = mysqli_prepare($conn, "
        UPDATE product_variants
        SET stock = ?
        WHERE id = ?
    ");

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

    while ($detail = mysqli_fetch_assoc($detailResult)) {
        $variantId = (int) ($detail['variant_id'] ?? 0);
        $qty = (int) ($detail['qty'] ?? 0);

        if ($variantId <= 0 || $qty <= 0) {
            continue;
        }

        mysqli_stmt_bind_param($stmtVariant, "i", $variantId);
        mysqli_stmt_execute($stmtVariant);
        $variantResult = mysqli_stmt_get_result($stmtVariant);
        $variant = mysqli_fetch_assoc($variantResult);

        if (!$variant) {
            throw new Exception("Varian barang tidak ditemukan untuk item: " . ($detail['sku'] ?? $detail['product_name']));
        }

        $stockBefore = (int) $variant['stock'];
        $stockAfter = $stockBefore + $qty;

        mysqli_stmt_bind_param($stmtUpdateStock, "ii", $stockAfter, $variantId);
        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception("Gagal mengembalikan stok.");
        }

        $type = 'IN';
        $referenceType = 'return';
        $note = 'Pembatalan transaksi kasir: ' . $transaction['invoice_number'];

        mysqli_stmt_bind_param(
            $stmtLog,
            "iisissisi",
            $variantId,
            $qty,
            $type,
            $stockBefore,
            $stockAfter,
            $referenceType,
            $transactionId,
            $note,
            $userId
        );

        if (!mysqli_stmt_execute($stmtLog)) {
            throw new Exception("Gagal menyimpan log pembatalan transaksi.");
        }
    }

    $cancelledStatus = 'cancelled';
    $stmtCancel = mysqli_prepare($conn, "
        UPDATE transactions
        SET status = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmtCancel, "si", $cancelledStatus, $transactionId);

    if (!mysqli_stmt_execute($stmtCancel)) {
        throw new Exception("Gagal mengubah status transaksi.");
    }

    mysqli_commit($conn);

    redirectTo('pages/detail_kasir.php?id=' . $transactionId . '&success=' . urlencode('Transaksi berhasil dibatalkan.'));
} catch (Exception $e) {
    mysqli_rollback($conn);
    redirectTo('pages/detail_kasir.php?id=' . $transactionId . '&error=' . urlencode($e->getMessage()));
}