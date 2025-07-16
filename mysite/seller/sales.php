<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php'; 

$seller_id = $_SESSION['user_id'];


function getMonthlyReport($pdo, $seller_id) {
    
}


if (isset($_GET['download']) && $_GET['download'] === 'excel') {

    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        DATE(o.order_date) as order_day,
        COALESCE(SUM(oi.price * oi.quantity), 0) as daily_revenue,
        COALESCE(SUM(p.cost_price * oi.quantity), 0) as daily_cost
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
      AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      AND o.status = 'completed'
    GROUP BY order_day
    ORDER BY order_day DESC
");
$stmt->execute([$seller_id]);
$daily_profits_online = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        DATE(s.created_at) AS sale_day,
        COALESCE(SUM(p.price * s.quantity), 0) AS daily_revenue,
        COALESCE(SUM(p.cost_price * s.quantity), 0) AS daily_cost
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.seller_id = ?
      AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY sale_day
    ORDER BY sale_day DESC
");
$stmt->execute([$seller_id]);
$daily_profits_offline = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Laporan Keuntungan Penjualan</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
:root {
    --pink-light: #fef3f3;
    --pink-dark: #ffd6d6;
    --green-light: #e7fdf0;
    --green-dark: #b6e5c9;
    --blue-light: #e6f4fd;
    --blue-dark: #add6f5;
    --yellow-light: #fffde1;
    --yellow-dark: #fff5a5;
    --white: #fffdfc;
    --text-dark: #212529;
    --text-muted: #555;
}

body {
    font-family: 'Nunito', sans-serif;
    background-color: var(--pink-light);
    padding: 2rem;
    color: var(--text-dark);
}
h1 {
    font-weight: 700;
    margin-bottom: 1.5rem;
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    background-color: var(--blue-light);
    border: none;
}
.card h4 {
    color: var(--text-dark);
}
.btn-yellow {
    background-color: var(--blue-light);
    border-color: var(--blue-dark);
    padding: 5px 12px;
    font-weight: 550;
    color: var(--text-dark);
    border-radius: 50px;
    border: 1.5px solid var(--blue-dark);
}
.btn-yellow:hover {
    background-color: var(--blue-dark);
    color: var(--text-dark);
    border-radius: 50px;
    border: 1.5px solid var(--blue-dark);
}

.btn-green {
    background-color: var(--green-light);
    border-color: var(--green-dark);
    padding: 5px 12px;
    font-weight: 550;
    color: var(--text-dark);
    border-radius: 50px;
    border: 1.5px solid var(--green-dark);
}
.btn-green:hover {
    background-color: var(--green-dark);
    color: var(--text-dark);
    border-radius: 50px;
    border: 1.5px solid var(--green-dark);
}


.table {
    background-color: var(--white);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
}
.table th {
    background-color: var(--yellow-light);
    color: var(--text-dark);
}
.table td, .table th {
    vertical-align: middle;
}

.alert-warning {
    background-color: var(--pink-light);
    color: var(--text-muted);
    border: none;
}

.container > a.btn {
    margin-bottom: 1rem;
}

hr {
    border: none;
    border-top: 2px solid var(--pink-dark);
    margin: 3rem 0;
}
</style>
</head>
<body>

<div class="container">

<div class="d-flex justify-content-between align-items-start mt-3 mb-4">
    <div>
        <a href="dashboard.php" class="btn btn-green me-2">Kembali</a>
        <a href="add_orders.php" class="btn btn-green me-2">Input Penjualan</a>
    </div>
    <a href="download_report.php" class="btn btn-yellow">Download Laporan</a>
</div>

    <h1>Laporan Penjualan Offline</h1>

    <?php if (count($daily_profits_offline) === 0): ?>
        <div class="alert alert-warning">Belum ada data penjualan offline dalam 7 hari terakhir.</div>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pendapatan (Rp)</th>
                    <th>Biaya (Rp)</th>
                    <th>Keuntungan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_profits_offline as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['sale_day']) ?></td>
                    <td><?= number_format($row['daily_revenue'], 0, ',', '.') ?></td>
                    <td><?= number_format($row['daily_cost'], 0, ',', '.') ?></td>
                    <td><?= number_format($row['daily_revenue'] - $row['daily_cost'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h1>Laporan Penjualan Online</h1>

    <?php if (count($daily_profits_online) === 0): ?>
        <div class="alert alert-warning">Belum ada data penjualan online selesai dalam 7 hari terakhir.</div>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pendapatan (Rp)</th>
                    <th>Biaya (Rp)</th>
                    <th>Keuntungan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_profits_online as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['order_day']) ?></td>
                    <td><?= number_format($row['daily_revenue'], 0, ',', '.') ?></td>
                    <td><?= number_format($row['daily_cost'], 0, ',', '.') ?></td>
                    <td><?= number_format($row['daily_revenue'] - $row['daily_cost'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
