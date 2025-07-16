<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php';

$seller_id = $_SESSION['user_id'];

// Total produk seller
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$total_products = $stmt->fetchColumn();

// Pesanan baru masuk (pending)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'pending'
");
$stmt->execute([$seller_id]);
$orders_pending = $stmt->fetchColumn();

// Pesanan diproses (processing)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'processing'
");
$stmt->execute([$seller_id]);
$orders_processing = $stmt->fetchColumn();

// Pesanan dikirim 
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'on_delivery'
");
$stmt->execute([$seller_id]);
$orders_on_delivery = $stmt->fetchColumn();

// Pesanan selesai 
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'completed'
");
$stmt->execute([$seller_id]);
$orders_completed = $stmt->fetchColumn();

// Pesanan dibatalkan 
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.id)
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ? AND o.status = 'canceled'
");
$stmt->execute([$seller_id]);
$orders_canceled = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT 
        sale_day,
        SUM(daily_revenue) AS daily_revenue,
        SUM(daily_cost) AS daily_cost
    FROM (
        -- Penjualan online
        SELECT 
            DATE(o.order_date) AS sale_day,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS daily_revenue,
            COALESCE(SUM(p.cost_price * oi.quantity), 0) AS daily_cost
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
          AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND o.status = 'completed'
        GROUP BY sale_day

        UNION ALL

        -- Penjualan offline
        SELECT 
            DATE(s.created_at) AS sale_day,
            COALESCE(SUM(p.price * s.quantity), 0) AS daily_revenue,
            COALESCE(SUM(p.cost_price * s.quantity), 0) AS daily_cost
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.seller_id = ?
          AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY sale_day
    ) AS combined
    GROUP BY sale_day
    ORDER BY sale_day DESC
");

$stmt->execute([$seller_id, $seller_id]);
$daily_profits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Seller</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>

body {
    font-family: 'Nunito', sans-serif;
    background-color: #fffdfc;
    color: #444444;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.main-container {
    display: flex;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}


.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 230px;
    height: 100vh;
    background:  #fef3f3;
    color: #333;
    padding-top: 3rem;
    box-shadow: 3px 0 8px rgba(0,0,0,0.05);
    transform: translateX(-230px);
    transition: transform 0.3s ease;
    z-index: 1000;
}
.sidebar:not(.closed) {
    transform: translateX(0);
}

.sidebar ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}
.sidebar ul li {
    padding: 1rem 2rem;
    cursor: pointer;
    font-weight: 600;
    user-select: none;
    transition: background-color 0.2s ease;
}
.sidebar ul li:hover {
    background-color: #ffe7ec;
}
.sidebar ul li a {
    color: #444;
    text-decoration: none;
    display: block;
}


.hamburger {
    position: fixed;
    top: 15px;
    left: 15px;
    width: 30px;
    height: 22px;
    cursor: pointer;
    z-index: 1100;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.hamburger div {
    height: 4px;
    background-color: #ffd6d6;
    border-radius: 2px;
    transition: all 0.3s ease;
}


.content {
    flex-grow: 1;
    padding: 2rem;
    margin-left: 0;
    transition: margin-left 0.3s ease;
}
.content.shifted {
    margin-left: 230px;
}


h1 {
    font-weight: 700;
    margin-bottom: 2rem;
    color: #555;
    text-align: center;
}


.status-cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
}
.status-card {
    background-color: #fef3f3;
    border-left: 6px solid #ffd6d6;
    color: #555;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    min-width: 130px;
    max-width: 140px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}
.status-card:nth-child(2) {
    background-color: #e7fdf0;
    border-left: 6px solid #b6e5c9;
}
.status-card:nth-child(3) {
    background-color: #e6f4fd;
    border-left: 6px solid #add6f5;
}
.status-card:nth-child(4) {
    background-color: #fffde1;
    border-left: 6px solid #fff5a5;
}
.status-card:nth-child(6) {
    background-color: #e7fdf0;
    border-left: 6px solid #b6e5c9;
}


.status-card:hover {
    box-shadow: 0 5px 12px rgba(0,0,0,0.1);
}
.status-card .number {
    font-size: 1.6rem;
    font-weight: 700;
}


h3 {
    margin-top: 3rem;
    font-weight: 700;
    color: #444;
    text-align: center;
    margin-bottom: 1.5rem;
}

table {
    max-width: 700px;
    margin: 0 auto 3rem auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0,0,0,0.06);
    font-weight: 600;
    font-size: 1rem;
}
.table {
    border-collapse: separate !important;
    border-spacing: 0 0.4rem !important;
}
.table thead tr {
    background-color: #ffe0e9;
    color: #444;
}
.table thead th {
    border: none !important;
    padding: 0.75rem 1rem !important;
    text-align: center;
}
.table tbody tr {
    background-color: #fdfdfd;
}
.table tbody td {
    vertical-align: middle !important;
    text-align: center;
    border: none !important;
}

.profit {
    color: #3cb371;
    font-weight: 700;
}
.loss {
    color: #e06666;
    font-weight: 700;
}

@media (max-width: 768px) {
    .status-cards {
        gap: 0.7rem;
    }
    .status-card {
        min-width: 120px;
        max-width: 130px;
        padding: 0.6rem 0.8rem;
        font-size: 0.8rem;
    }
    .status-card .number {
        font-size: 1.4rem;
    }
    h3 {
        font-size: 1.2rem;
    }
    table {
        max-width: 100%;
        font-size: 0.9rem;
    }
    .sidebar {
        width: 200px;
    }
    .content.shifted {
        margin-left: 200px;
    }
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
    background-color: #bbb;
    border-radius: 3px;
}
</style>

</head>
<body>


<div class="hamburger" id="hamburgerBtn" aria-label="Toggle menu" role="button" tabindex="0">
    <div></div>
    <div></div>
    <div></div>
</div>

<div class="main-container">

    <nav class="sidebar closed" id="sidebarMenu" aria-label="Sidebar menu">
        <ul>
           
            <li><a href="products.php">Produk</a></li>
            <li><a href="orders.php">Pesanan</a></li>
            <li><a href="sales.php">Penjualan</a></li> 
            <li><a href="statistics.php">Statistik</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>


    <main class="content" id="mainContent">

        <div class="status-cards" role="region" aria-label="Ringkasan status produk dan pesanan">
            <div class="status-card" tabindex="0" aria-live="polite">
                Produk<br />
                <span class="number"><?= htmlspecialchars($total_products) ?></span>
            </div>
            <div class="status-card" tabindex="0" aria-live="polite">
                Pesanan Baru<br />
                <span class="number"><?= htmlspecialchars($orders_pending) ?></span>
            </div>
            <div class="status-card" tabindex="0" aria-live="polite">
                Diproses<br />
                <span class="number"><?= htmlspecialchars($orders_processing) ?></span>
            </div>
            <div class="status-card" tabindex="0" aria-live="polite">
                Dikirim<br />
                <span class="number"><?= htmlspecialchars($orders_on_delivery) ?></span>
            </div>
            <div class="status-card" tabindex="0" aria-live="polite">
                Selesai<br />
                <span class="number"><?= htmlspecialchars($orders_completed) ?></span>
            </div>
            <div class="status-card" tabindex="0" aria-live="polite">
                Dibatalkan<br />
                <span class="number"><?= htmlspecialchars($orders_canceled) ?></span>
            </div>
        </div>

        <h3>Keuntungan Mingguan</h3>
<table class="table table-bordered table-striped table-hover" aria-describedby="Keuntungan bersih setiap hari selama 7 hari terakhir" style="background-color: var(--white); border-radius: 10px; overflow: hidden;">
    <thead style="background-color: var(--blue-dark); color: white;">
        <tr>
            <th>Tanggal</th>
            <th>Pendapatan</th>
            <th>Biaya</th>
            <th>Keuntungan</th>
        </tr>
    </thead>
    <tbody>
<?php
$total_revenue = 0;
$total_cost = 0;
$total_profit = 0;

foreach ($daily_profits as $row): 
    $date = date('d M Y', strtotime($row['sale_day']));
    $revenue = $row['daily_revenue'];
    $cost = $row['daily_cost'];
    $profit = $revenue - $cost;

    $total_revenue += $revenue;
    $total_cost += $cost;
    $total_profit += $profit;

    $profit_class = $profit >= 0 ? 'text-success' : 'text-danger';
    $profit_text = ($profit >= 0 ? 'Rp ' : '-Rp ') . number_format(abs($profit), 0, ',', '.');
?>

        <tr>
            <td><?= htmlspecialchars($date) ?></td>
            <td>Rp <?= number_format($revenue, 0, ',', '.') ?></td>
            <td>Rp <?= number_format($cost, 0, ',', '.') ?></td>
            <td class="<?= $profit_class ?> fw-semibold"><?= $profit_text ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (count($daily_profits) === 0): ?>
        <tr><td colspan="4" class="text-muted text-center">Belum ada data keuntungan.</td></tr>
    <?php else: ?>
        <tr style="background-color: var(--yellow-light); font-weight: 700;">
            <td>Total</td>
            <td>Rp <?= number_format($total_revenue, 0, ',', '.') ?></td>
            <td>Rp <?= number_format($total_cost, 0, ',', '.') ?></td>
            <td class="<?= $total_profit >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= ($total_profit >= 0 ? 'Rp ' : '-Rp ') . number_format(abs($total_profit), 0, ',', '.') ?>
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

    </main>
</div>

<script>

const hamburger = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebarMenu');
const content = document.getElementById('mainContent');

hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('closed');
    hamburger.classList.toggle('active');
    content.classList.toggle('shifted');
});
</script>

</body>
</html>
