<?php
require_once '../includes/auth_check.php';
requireLogin();
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('pages/riwayat_stok_masuk.php');
}

$purchaseId = (int) ($_POST['purchase_id'] ?? 0);
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($purchaseId <= 0) {
    redirectTo('pages/riwayat_stok_masuk.php?error=' . urlencode('ID transaksi tidak valid.'));
}

if ($userId <= 0) {
    redirectTo('pages/riwayat_stok_masuk.php?error=' . urlencode('Session user tidak valid.'));
}

mysqli_begin_transaction($conn);

try {
    // Ambil header pembelian
    $stmtPurchase = mysqli_prepare($conn, "
        SELECT id, purchase_number, status, note
        FROM purchases
        WHERE id = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmtPurchase, "i", $purchaseId);
    mysqli_stmt_execute($stmtPurchase);
    $purchaseResult = mysqli_stmt_get_result($stmtPurchase);
    $purchase = mysqli_fetch_assoc($purchaseResult);

    if (!$purchase) {
        throw new Exception("Transaksi pembelian tidak ditemukan.");
    }

    if ($purchase['status'] === 'cancelled') {
        throw new Exception("Transaksi ini sudah dibatalkan sebelumnya.");
    }

    // Ambil detail pembelian
    $stmtDetail = mysqli_prepare($conn, "
        SELECT variant_id, qty, sku, product_name
        FROM purchase_details
        WHERE purchase_id = ?
    ");
    mysqli_stmt_bind_param($stmtDetail, "i", $purchaseId);
    mysqli_stmt_execute($stmtDetail);
    $detailResult = mysqli_stmt_get_result($stmtDetail);

    if (mysqli_num_rows($detailResult) === 0) {
        throw new Exception("Detail pembelian tidak ditemukan.");
    }

    while ($detail = mysqli_fetch_assoc($detailResult)) {
        $variantId = (int) $detail['variant_id'];
        $qty = (int) $detail['qty'];

        // Ambil stok saat ini
        $stmtVariant = mysqli_prepare($conn, "
            SELECT stock
            FROM product_variants
            WHERE id = ?
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
        $stockAfter = $stockBefore - $qty;

        if ($stockAfter < 0) {
            throw new Exception("Stok tidak cukup untuk membatalkan transaksi.");
        }

        // Kurangi stok
        $stmtUpdateStock = mysqli_prepare($conn, "
            UPDATE product_variants
            SET stock = ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmtUpdateStock, "ii", $stockAfter, $variantId);

        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception("Gagal mengurangi stok.");
        }

        // Simpan log pembalik
        $type = 'OUT';
        $referenceType = 'purchase';
        $note = 'Pembatalan stok masuk: ' . $purchase['purchase_number'];

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
            $variantId,
            $qty,
            $type,
            $stockBefore,
            $stockAfter,
            $referenceType,
            $purchaseId,
            $note,
            $userId
        );

        if (!mysqli_stmt_execute($stmtLog)) {
            throw new Exception("Gagal menyimpan log pembatalan.");
        }
    }

    // Ubah status pembelian jadi cancelled
    $cancelledStatus = 'cancelled';
    $stmtCancel = mysqli_prepare($conn, "
        UPDATE purchases
        SET status = ?
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmtCancel, "si", $cancelledStatus, $purchaseId);

    if (!mysqli_stmt_execute($stmtCancel)) {
        throw new Exception("Gagal mengubah status transaksi.");
    }

    mysqli_commit($conn);

    redirectTo('pages/detail_stok_masuk.php?id=' . $purchaseId . '&success=' . urlencode('Transaksi berhasil dibatalkan.'));
} catch (Exception $e) {
    mysqli_rollback($conn);
    redirectTo('pages/detail_stok_masuk.php?id=' . $purchaseId . '&error=' . urlencode($e->getMessage()));
}