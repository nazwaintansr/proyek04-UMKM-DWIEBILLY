<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php'; 

$seller_id = $_SESSION['user_id'];
$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    $quantities = $_POST['quantity'];
    $errors = [];

    foreach ($quantities as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);

        if ($quantity > 0) {
            $stmt = $pdo->prepare("SELECT price, cost_price FROM products WHERE id = ? AND seller_id = ?");
            $stmt->execute([$product_id, $seller_id]);
            $product = $stmt->fetch();

            if ($product) {
                $profit = ($product['price'] - $product['cost_price']) * $quantity;

                $stmt = $pdo->prepare("INSERT INTO sales (product_id, seller_id, quantity, profit) VALUES (?, ?, ?, ?)");
                if (!$stmt->execute([$product_id, $seller_id, $quantity, $profit])) {
                    $errors[] = "Gagal menyimpan keuntungan untuk produk ID $product_id.";
                }
            } else {
                $errors[] = "Produk ID $product_id tidak ditemukan.";
            }
        }
    }

    $message = empty($errors) ? "Keuntungan berhasil disimpan." : implode('<br>', $errors);
}

$stmt = $pdo->prepare("SELECT id, name, price, cost_price FROM products WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Input Keuntungan Penjualan</title>
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
            --white: #fffdfc;
            --text-dark: #212529;
            --text-muted: #555;
        }

        body {
            background-color: var(--pink-light);
            font-family: 'Nunito', sans-serif;
            color: var(--text-dark);
            padding: 3rem 1rem;
        }

        h1 {
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text-dark);
        }

        .btn {
            border-radius: 50px;
            font-weight: 550;
             padding: 5px 12px;
            color: var(--text-dark);
            border: 1.5px solid transparent;
        }

        .btn-primary {
            background-color: var(--pink-light);
            border-color: var(--pink-dark);
        }

        .btn-primary:hover {
            background-color: var(--pink-dark);
            border-color: var(--pink-dark);
            color: var(--text-dark);
            outline: none;
        }

        .btn-primary:focus {
            background-color: var(--pink-dark);
            border-color: var(--pink-dark);
            outline: none;
        }

        .btn-secondary {
            background-color: var(--blue-light);
            border-color: var(--blue-dark);
        }

        .btn-secondary:hover {
            background-color: var(--blue-dark);
            border-color: var(--blue-dark);
            color: var(--text-dark);
            outline: none;
        }
        .btn-secondary:focus {
            background-color: var(--blue-dark);
            border-color: var(--blue-dark);
            outline: none;
        }

        .form-control {
            border-radius: 50px;
            padding: 5px 10px;
            border: 1.5px solid var(--pink-dark);
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--pink-dark);
            box-shadow: 0 0 6px var(--pink-dark);
            outline: none;
            background-color: var(--white);
        }

        .alert-info {
            background-color: var(--green-light);
            border-left: 5px solid var(--green-dark);
            font-weight: 500;
        }

        .alert-warning {
            background-color: var(--pink-light);
            border-left: 5px solid var(--pink-dark);
            font-weight: 500;
        }

        table {
            background-color: var(--white);
        }

        th {
            background-color: var(--yellow-light);
            font-weight: 600;
        }

        td {
            vertical-align: middle;
        }

        .actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Input Data Penjualan Offline</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <?php if (count($products) === 0): ?>
        <div class="alert alert-warning">Belum ada produk.</div>
    <?php else: ?>
        <form method="post" onsubmit="return confirm('Simpan keuntungan penjualan semua produk yang diinput?');">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga Jual (Rp)</th>
                        <th>Modal (Rp)</th>
                        <th>Jumlah Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= number_format($product['price'], 0, ',', '.') ?></td>
                        <td><?= number_format($product['cost_price'], 0, ',', '.') ?></td>
                        <td>
                            <input
                                type="number"
                                name="quantity[<?= $product['id'] ?>]"
                                min="0"
                                value="0"
                                class="form-control"
                            />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="actions">
                <a href="sales.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
