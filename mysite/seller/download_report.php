<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php';
$seller_id = $_SESSION['user_id'];

$sql = "
    SELECT
        DATE_FORMAT(order_date, '%Y-%m') AS month,
        p.name AS product_name,
        SUM(oi.quantity) AS quantity,
        SUM(oi.price * oi.quantity) AS revenue,
        SUM(p.cost_price * oi.quantity) AS cost
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.seller_id = ?
      AND o.status = 'completed'
      AND order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month, product_name

    UNION ALL

    SELECT
        DATE_FORMAT(s.created_at, '%Y-%m') AS month,
        p.name AS product_name,
        SUM(s.quantity) AS quantity,
        SUM(p.price * s.quantity) AS revenue,
        SUM(p.cost_price * s.quantity) AS cost
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.seller_id = ?
      AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month, product_name
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$seller_id, $seller_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


$mergedData = [];
foreach ($data as $row) {
    $key = $row['month'] . '|' . $row['product_name'];
    if (!isset($mergedData[$key])) {
        $mergedData[$key] = [
            'month' => $row['month'],
            'product_name' => $row['product_name'],
            'quantity' => 0,
            'revenue' => 0,
            'cost' => 0,
        ];
    }
    $mergedData[$key]['quantity'] += $row['quantity'];
    $mergedData[$key]['revenue'] += $row['revenue'];
    $mergedData[$key]['cost'] += $row['cost'];
}


header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_penjualan_bulanan.xls"');
header('Pragma: no-cache');
header('Expires: 0');


echo "Bulan\tNama Produk\tJumlah Terjual\tPendapatan (Rp)\tModal (Rp)\tKeuntungan (Rp)\n";


foreach ($mergedData as $row) {
    $profit = $row['revenue'] - $row['cost'];
    echo $row['month'] . "\t" .
         $row['product_name'] . "\t" .
         $row['quantity'] . "\t" .
         $row['revenue'] . "\t" .
         $row['cost'] . "\t" .
         $profit . "\n";
}

exit;
