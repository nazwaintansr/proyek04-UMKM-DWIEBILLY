<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../functions.php';

$seller_id = $_SESSION['user_id'];


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);


$stmt = $pdo->prepare("
    SELECT o.*, u.username AS customer_name, u.address AS customer_address, u.phone AS customer_phone
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ? AND o.id IN (
        SELECT DISTINCT order_id FROM order_items WHERE product_id IN (
            SELECT id FROM products WHERE seller_id = ?
        )
    )
");

$stmt->execute([$order_id, $seller_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {

    header('Location: orders.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT p.name, p.cost_price, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ? AND p.seller_id = ?
");
$stmt->execute([$order_id, $seller_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Detail Pesanan #<?= $order_id ?></title>
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
.btn {
    border-radius: 50px;
    border-width: 1.5px;
    font-weight: 550;
    background-color: var(--blue-light);
    border-color:var(--blue-dark);
    color: var(--text-dark);
}
.btn:hover {
    background-color: var(--blue-dark);
    color: var(--text-dark);
}

.table {
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
}
.table th {
    background-color: var(--yellow-light);
    color: var
}
.table td, .table th {
    vertical-align: middle;
}
.status-select {
    max-width: 180px;
    border-radius: 10px;
}


.alert-info {
    background-color: var(--pink-light);
    color: var(--text-muted);
    border: none;
}

h4 {
    font-weight: 700;
}

</style>

</head>
<body>
    <h1>Detail Pesanan #<?= $order_id ?></h1>
    <a href="orders.php" class="btn btn-secondary mb-3">Kembali</a>

    <div class="card p-4 mb-4">
        <h4>Info Customer & Pesanan</h4>
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Tanggal Pesanan:</strong> <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
        <p><strong>Status Pesanan:</strong> <span style="text-transform: capitalize;"><?= htmlspecialchars($order['status']) ?></span></p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
        <p><strong>Telepon:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>

    </div>

    <div class="card p-4">
        <h4>Daftar Produk dalam Pesanan</h4>
        <?php if (count($items) === 0): ?>
            <div class="alert alert-info">Tidak ada produk pada pesanan ini.</div>
        <?php else: ?>
        <!-- bagian tabel diubah: -->
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Jumlah</th>
            <th>Penjualan</th>
            <th>Biaya</th>
            <th>Keuntungan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_revenue = 0;
        $total_cost = 0;
        $total_profit = 0;

        foreach ($items as $index => $item):
            $subtotal_revenue = $item['price'] * $item['quantity'];
            $subtotal_cost = $item['cost_price'] * $item['quantity'];
            $profit = $subtotal_revenue - $subtotal_cost;

            $total_revenue += $subtotal_revenue;
            $total_cost += $subtotal_cost;
            $total_profit += $profit;
        ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>Rp <?= number_format($subtotal_revenue, 0, ',', '.') ?></td>
            <td>Rp <?= number_format($subtotal_cost, 0, ',', '.') ?></td>
            <td style="color: <?= $profit >= 0 ? 'green' : 'red' ?>">
                Rp <?= number_format($profit, 0, ',', '.') ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" class="text-end"><strong>Total</strong></td>
            <td><strong>Rp <?= number_format($total_revenue, 0, ',', '.') ?></strong></td>
            <td><strong>Rp <?= number_format($total_cost, 0, ',', '.') ?></strong></td>
            <td style="color: <?= $total_profit >= 0 ? 'green' : 'red' ?>">
                <strong>Rp <?= number_format($total_profit, 0, ',', '.') ?></strong>
            </td>
        </tr>
    </tbody>
</table>

        <?php endif; ?>
    </div>
</body>
</html>
