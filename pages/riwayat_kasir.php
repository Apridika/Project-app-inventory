<?php
require_once '../includes/auth_check.php';
requireRole(['admin', 'owner', 'kasir']);
requireLogin();
require_once '../includes/koneksi.php';

$title = "Riwayat Kasir";
include '../includes/head.php';

$filterType = $_GET['filter_type'] ?? 'hari';
$selectedDate = $_GET['selected_date'] ?? date('Y-m-d');
$selectedMonth = $_GET['selected_month'] ?? date('Y-m');

if (!in_array($filterType, ['hari', 'bulan'], true)) {
    $filterType = 'hari';
}

$whereClause = "";
$params = [];
$types = "";

if ($filterType === 'bulan') {
    $whereClause = "WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?";
    $params[] = $selectedMonth;
    $types .= "s";
} else {
    $whereClause = "WHERE DATE(t.created_at) = ?";
    $params[] = $selectedDate;
    $types .= "s";
}

$query = "
    SELECT 
        t.id,
        t.customer_name,
        t.channel,
        t.payment_method,
        t.status,
        t.total_price,
        t.created_at,
        COUNT(td.id) AS total_item
    FROM transactions t
    LEFT JOIN transaction_details td ON td.transaction_id = t.id
    $whereClause
    GROUP BY 
        t.id,
        t.customer_name,
        t.channel,
        t.payment_method,
        t.status,
        t.total_price,
        t.created_at
    ORDER BY t.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Query error: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function getMonthNameIndo($monthNumber)
{
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    return $months[(int)$monthNumber] ?? '-';
}
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
                <h2>Riwayat Transaksi Kasir</h2>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <style>
                .status-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 999px;
                    font-size: 12px;
                    font-weight: 600;
                    color: #fff;
                }

                .status-paid {
                    background-color: #16a34a;
                }

                .status-cancelled {
                    background-color: #dc2626;
                }

                .status-draft,
                .status-completed {
                    background-color: #6b7280;
                }

                .filter-box {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 12px;
                    align-items: end;
                }

                .filter-group {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                }

                .filter-group label {
                    font-weight: 600;
                }

                .filter-group select,
                .filter-group input {
                    min-width: 180px;
                    padding: 10px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    background: #fff;
                }

                .hidden {
                    display: none;
                }
            </style>

            <div class="card" style="margin-bottom: 16px;">
                <form method="GET" class="filter-box">
                    <div style="color: #d1d5db;" class="filter-group">
                        <label for="filter_type">Filter Berdasarkan</label>
                        <select name="filter_type" id="filter_type" onchange="toggleFilterInput()">
                            <option value="hari" <?= $filterType === 'hari' ? 'selected' : ''; ?>>Hari</option>
                            <option value="bulan" <?= $filterType === 'bulan' ? 'selected' : ''; ?>>Bulan</option>
                        </select>
                    </div>

                    <div style="color: #d1d5db;" class="filter-group" id="date_filter_group">
                        <label for="selected_date">Pilih Tanggal</label>
                        <input 
                            type="date" 
                            name="selected_date" 
                            id="selected_date"
                            value="<?= htmlspecialchars($selectedDate); ?>"
                        >
                    </div>

                    <div style="color: #d1d5db;" class="filter-group" id="month_filter_group">
                        <label for="selected_month">Pilih Bulan</label>
                        <input 
                            type="month" 
                            name="selected_month" 
                            id="selected_month"
                            value="<?= htmlspecialchars($selectedMonth); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-secondary">Tampilkan</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-bottom: 16px;">
                <strong style="color:white;">
                    <?php if ($filterType === 'bulan'): ?>
                        Menampilkan transaksi bulan 
                        <?php
                        $monthParts = explode('-', $selectedMonth);
                        $monthText = count($monthParts) === 2
                            ? getMonthNameIndo($monthParts[1]) . ' ' . $monthParts[0]
                            : htmlspecialchars($selectedMonth);
                        echo $monthText;
                        ?>
                    <?php else: ?>
                        Menampilkan transaksi tanggal <?= date('d-m-Y', strtotime($selectedDate)); ?>
                    <?php endif; ?>
                </strong>
            </div>

            <div class="card">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Pembeli</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Via</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result && mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                $status = strtolower($row['status'] ?? '');
                                $statusClass = 'status-draft';

                                if ($status === 'paid') {
                                    $statusClass = 'status-paid';
                                } elseif ($status === 'cancelled') {
                                    $statusClass = 'status-cancelled';
                                } elseif ($status === 'completed') {
                                    $statusClass = 'status-completed';
                                }
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><?= htmlspecialchars($row['customer_name'] ?: '-'); ?></td>
                            <td><?= (int) $row['total_item']; ?></td>
                            <td>Rp <?= number_format((int) $row['total_price']); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['channel'] ?? '-')); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['payment_method'] ?? '-')); ?></td>
                            <td>
                                <span class="status-badge <?= $statusClass; ?>">
                                    <?= htmlspecialchars(ucfirst($row['status'] ?? '-')); ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_kasir.php?id=<?= (int) $row['id']; ?>" class="btn-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">Tidak ada transaksi pada filter yang dipilih.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

</div>

<script>
    function toggleFilterInput() {
        const filterType = document.getElementById('filter_type').value;
        const dateGroup = document.getElementById('date_filter_group');
        const monthGroup = document.getElementById('month_filter_group');

        if (filterType === 'bulan') {
            dateGroup.style.display = 'none';
            monthGroup.style.display = 'flex';
        } else {
            dateGroup.style.display = 'flex';
            monthGroup.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleFilterInput();
    });
</script>

<script src="<?= url('assets/app.js') ?>"></script>
</body>
</html>