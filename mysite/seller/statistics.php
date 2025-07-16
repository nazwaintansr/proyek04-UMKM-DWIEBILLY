<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php';
$seller_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT 
        DATE(o.order_date) as order_day,
        COUNT(DISTINCT o.id) as total_orders,
        SUM(oi.price * oi.quantity) as total_income,
        SUM(p.cost_price * oi.quantity) as total_cost
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
      AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY order_day
    ORDER BY order_day
");
$stmt->execute([$seller_id]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);


$labels = [];
$incomes = [];
$profits = [];
foreach ($stats as $s) {
    $labels[] = date('d M', strtotime($s['order_day']));
    $incomes[] = (float)$s['total_income'];
    $profits[] = (float)($s['total_income'] - $s['total_cost']);
}


$stmt = $pdo->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->execute([$seller_id]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Statistik Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    padding: 2rem;
    background-color: var(--pink-light);
    font-family: 'Nunito', sans-serif;
    color: var(--text-dark);
}
h1 {
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--text-dark);
}
.card {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    margin-bottom: 2rem;
    background-color: var(--yellow-light);
}
.table th, .table td {
    vertical-align: middle;
}


.chart-income {
    background-color: var(--green-light);
    border-color: var(--green-dark);
}
.chart-profit {
    background-color: var(--blue-light);
    border-color: var(--blue-dark);
}


h3 {
    color: var(--text-dark);
    font-weight: 700;
    margin-bottom: 1rem;
}


.table-top5 {
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.table-top5 thead {
    background-color: var(--pink-dark);
    color: var(--white);
    font-weight: 700;
}

.table-top5 thead th {
    padding: 12px 20px;
    text-align: left;
    color: var(--text-dark);
    background-color: var(--pink-light);
}

.table-top5 tbody tr:nth-child(odd) {
    background-color: var(--pink-light);
}

.table-top5 tbody tr:nth-child(even) {
    background-color: var(--pink-light);
}

.table-top5 tbody td {
    padding: 12px 20px;
    color: var(--text-dark);
    border-bottom: 1px solid var(--pink-dark);
}

.table-top5 tbody tr:hover {
    background-color: var(--pink-dark);
    color: var(--white);
    cursor: pointer;
}


.btn-secondary {
    background-color: var(--blue-light);
    border: 1.5px solid var(--blue-dark);
    border-radius: 50px;
    color: var(--text-dark);
    font-weight: 550;
}
.btn-secondary:hover {
    background-color: var(--blue-dark);
    border-color: var(--blue-dark);
    color: var(--text-dark);
}
</style>
</head>
<body>

<h1>Statistik Penjualan</h1>
<a href="dashboard.php" class="btn btn-secondary mb-4">Kembali</a>

<div class="card p-4">
    <canvas id="incomeChart" height="120"></canvas>
</div>

<div class="card p-4">
    <h3>Top 5 Produk Terlaris</h3>
    <?php if (count($top_products) > 0): ?>
    <table class="table-top5">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Total Terjual</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_products as $prod): ?>
            <tr>
                <td><?= htmlspecialchars($prod['name']) ?></td>
                <td><?= number_format($prod['total_sold']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color: var(--text-muted);">Tidak ada data produk terjual.</p>
    <?php endif; ?>
</div>

<script>
const ctx = document.getElementById('incomeChart').getContext('2d');
const incomeChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($incomes) ?>,
                backgroundColor: 'rgba(182, 229, 201, 0.5)',  
                borderColor: 'rgba(182, 229, 201, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
            {
                label: 'Laba Bersih (Rp)',
                data: <?= json_encode($profits) ?>,
                backgroundColor: 'rgba(173, 214, 245, 0.5)',  
                borderColor: 'rgba(173, 214, 245, 1)', 
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 7,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        stacked: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
