<?php
require_once '../includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
require_once '../includes/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    die('ID transaksi tidak valid.');
}

$queryTransaction = "
    SELECT
        t.id,
        t.invoice_number,
        t.customer_name,
        t.channel,
        t.payment_method,
        t.note,
        t.total_price,
        t.created_at,
        u.name AS user_name
    FROM transactions t
    LEFT JOIN users u ON u.id = t.created_by
    WHERE t.id = ?
    LIMIT 1
";
$stmtTransaction = mysqli_prepare($conn, $queryTransaction);
mysqli_stmt_bind_param($stmtTransaction, "i", $id);
mysqli_stmt_execute($stmtTransaction);
$resultTransaction = mysqli_stmt_get_result($stmtTransaction);
$transaction = mysqli_fetch_assoc($resultTransaction);

if (!$transaction) {
    die('Data transaksi tidak ditemukan.');
}

$queryDetail = "
    SELECT
        td.qty,
        td.price,
        td.subtotal,
        td.product_name,
        td.type_name,
        td.size_name,
        td.color_name,
        td.sku,
        pv.unit
    FROM transaction_details td
    LEFT JOIN product_variants pv ON pv.id = td.variant_id
    WHERE td.transaction_id = ?
    ORDER BY td.id ASC
";
$stmtDetail = mysqli_prepare($conn, $queryDetail);
mysqli_stmt_bind_param($stmtDetail, "i", $id);
mysqli_stmt_execute($stmtDetail);
$resultDetail = mysqli_stmt_get_result($stmtDetail);

function formatQtyStruk($qty): string
{
    return rtrim(rtrim(number_format((float) $qty, 2, '.', ''), '0'), '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.35;
        }

        .no-print {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
        }

        .no-print button {
            padding: 8px 12px;
            border: 1px solid #222;
            background: #fff;
            cursor: pointer;
        }

        .receipt {
            width: 58mm;
            margin: 0 auto;
            padding: 3mm 2mm;
        }

        .center {
            text-align: center;
        }

        .logo {
            width: 38px;
            height: auto;
            margin: 0 auto 4px;
            display: block;
        }

        .store-name {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .item {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: bold;
        }

        .line {
            display: flex;
            justify-content: space-between;
            gap: 6px;
        }

        .line .left {
            flex: 1;
            min-width: 0;
        }

        .line .right {
            white-space: nowrap;
            text-align: right;
        }

        .total {
            font-weight: bold;
            font-size: 12px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            html, body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }

            .receipt {
                width: 58mm;
                margin: 0;
                padding: 2mm 2mm;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Cetak Struk</button>
</div>

<div class="receipt">
    <div class="center">
        <div class="store-name">Yudhistira Craftstore</div>
        <div>Jl. Pusaka no. 48</div>
        <div>Garum, Blitar, Jawa Timur</div>
        <div>WA: 08xxxxxxxxxx</div>
    </div>

    <div class="divider"></div>

    <div>No Invoice : <?= htmlspecialchars($transaction['invoice_number']); ?></div>
    <div>Tanggal    : <?= date('d-m-Y H:i', strtotime($transaction['created_at'])); ?></div>
    <div>Pembeli    : <?= htmlspecialchars($transaction['customer_name'] ?: '-'); ?></div>
    <div>Via        : <?= htmlspecialchars(ucfirst($transaction['channel'] ?? '-')); ?></div>
    <div>Pembayaran : <?= htmlspecialchars(ucfirst($transaction['payment_method'] ?? '-')); ?></div>
    <div>Kasir      : <?= htmlspecialchars($transaction['user_name'] ?? '-'); ?></div>

    <div class="divider"></div>

    <?php while ($row = mysqli_fetch_assoc($resultDetail)): ?>
        <?php $qtyFormatted = formatQtyStruk($row['qty']); ?>
        <div class="item">
            <div class="item-name"><?= htmlspecialchars($row['product_name']); ?></div>
            <div>SKU: <?= htmlspecialchars($row['sku']); ?></div>
            <div class="line">
                <div class="left">
                    <?= htmlspecialchars($qtyFormatted . ' ' . ($row['unit'] ?? '')); ?> x Rp <?= number_format((int) $row['price'], 0, ',', '.'); ?>
                </div>
                <div class="right">
                    Rp <?= number_format((float) $row['subtotal'], 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <div class="divider"></div>

    <div class="line total">
        <div class="left">TOTAL</div>
        <div class="right">Rp <?= number_format((int) $transaction['total_price'], 0, ',', '.'); ?></div>
    </div>

    <?php if (!empty($transaction['note'])): ?>
        <div class="divider"></div>
        <div>Catatan: <?= nl2br(htmlspecialchars($transaction['note'])); ?></div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="center">
        Terima kasih sudah berbelanja<br>
        Barang yang sudah dibeli tidak dapat ditukar / dikembalikan
    </div>
</div>

</body>
</html>