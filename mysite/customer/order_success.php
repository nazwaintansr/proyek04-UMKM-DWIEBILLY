<?php
session_start();
require_once '../functions.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo "<p class='text-center mt-5'>Anda harus login sebagai customer. <a href='../login.php'>Login di sini</a></p>";
    exit;
}


$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo "<p class='text-center mt-5'>ID pesanan tidak valid. <a href='products.php'>Kembali ke produk</a></p>";
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p class='text-center mt-5'>Pesanan tidak ditemukan. <a href='products.php'>Kembali ke produk</a></p>";
    exit;
}


$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Pesanan Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
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
            background: linear-gradient(135deg, var(--pink-light), var(--blue-light), var(--yellow-light), var(--green-light));
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            padding: 2rem 1rem;
        }

        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 800px;
            margin: auto;
        }
        .alert-success {
            background-color:var(--white);
            border: 2px solid var(--pink-dark);
            color: var(--text-dark);
            border-radius: 15px;
            padding: 2rem 2rem 1.5rem;
            font-weight: 550;
            box-shadow: 0 0 12px rgba(107, 73, 106, 0.2);
            text-align: center;
            animation: fadeSlideIn 0.7s ease forwards;
            position: relative;
        }

        .alert-success .icon-check {
            font-size: 5rem;
            color:rgb(0, 152, 69);
            margin-bottom: 0.5rem;
            animation: popIn 0.6s ease forwards;
        }
        h4 {
            font-weight: 550;
            margin-bottom: 0.3rem;
            color: var(--text-dark);
        }
        p {
            margin-bottom: 0.6rem;
        }
        h5 {
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }
       table {
    background-color: var(--pink-dark);
    border-radius: 10px;
    border: 2px solid var(--pink-dark);
    box-shadow: 0 0 12px rgba(68, 68, 68, 0.12);
    animation: fadeSlideIn 0.7s ease forwards;
    width: 100%;
    overflow: hidden;
}

thead th {
    background-color: var(--yellow-dark);
    color: var(--text-dark);
    font-weight: bold;
    padding: 1rem;
    text-align: left;
}

tbody tr:nth-child(even) {
    background-color: var(--yellow-light);
}

tbody tr:hover {
    background-color: #fffbe8;
}

tbody td {
    padding: 0.9rem 1.1rem;
    vertical-align: middle;
    font-size: 0.97rem;
    color: var(--text-dark);
}

tbody td.text-end {
    text-align: right;
}

tbody td.text-center {
    text-align: center;
    font-weight: 550;
    color: var(--text-dark);
}

tfoot th {
    background-color: var(--yellow-dark);
    font-weight: bold;
    font-size: 1rem;
    padding: 1rem;
    text-align: right;
    color: var(--text-dark);
}

        .btn-primary {
            background-color: var(--green-light);
            border: 1.5px solid var(--green-dark);
            font-weight: 550;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            animation: fadeSlideIn 0.7s ease forwards;
            margin-top: 2rem;
            color: var(--text-dark);
        }
        .btn-primary:hover {
            background-color: var(--green-dark);
            border-color: var(--green-dark);
            transform: scale(1.05);
            color: var(--text-dark);
        }
        .text-end {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }


        @keyframes fadeSlideIn {
            0% {
                opacity: 0;
                transform: translateY(15px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill icon-check"></i>
        <h4>Terima kasih!</h4>
        <p>Pesanan Anda telah berhasil diproses.</p>
        <p><strong>ID Pesanan: #<?= htmlspecialchars($order_id) ?></strong></p>
    </div>

    <h5>Detail Pesanan</h5>
    <table class="table shadow-sm">
        <thead>
            <tr>
                <th style="width: 45%;">Nama Produk</th>
                <th class="text-end" style="width: 20%;">Harga Satuan</th>
                <th class="text-center" style="width: 15%;">Jumlah</th>
                <th class="text-end" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></th>
            </tr>
            <tr>
                <th colspan="3">Metode Pembayaran</th>
                <th><?= htmlspecialchars(strtoupper($order['payment_method'])) ?></th>
            </tr>
        </tfoot>
    </table>

<div style="text-align: left;">
  <a href="products.php" class="btn btn-primary">Kembali</a>
</div>

</div>
</body>
</html>
