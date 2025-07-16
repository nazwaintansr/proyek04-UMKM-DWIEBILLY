<?php
session_start();
require_once '../functions.php';  


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo "<p class='text-center mt-5'>Anda harus login sebagai customer. <a href='../login.php'>Login di sini</a></p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    $quantities = $_POST['quantities'];

    $_SESSION['cart'] = [];

    foreach ($quantities as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty = (int)$qty;
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {

    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Keranjang Kosong</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');

            :root {
            --pink-light: #f9d5e5;   
            --pink-dark: #d0f0fd;
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
                background: linear-gradient(135deg, #f9d5e5, #d0f0fd, #fff6c3, #d9fdd3);
                background-size: 400% 400%;
                animation: gradientBG 15s ease infinite;
                margin: 0;
                color: var(--text-dark);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            @keyframes gradientBG {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            .empty-cart-container {
                max-width: 400px;
                text-align: center;
                padding: 2rem;
                background: var(--white);
                border-radius: 12px;
                box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
            }

            .custom-btn {
                background-color: var(--green-light);
                border: 2px solid var(--green-dark);
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                color: var(--text-dark);
                border-radius: 50px;
                transition: 0.3s ease;
                text-decoration: none;
                display: inline-block;
            }

            .custom-btn:hover {
                background-color: var(--green-dark);
                color: var(--text-dark);
                transform: scale(1.05);
            }

        </style>
    </head>
    <body>
        <div class="empty-cart-container">
            <h2 class="mb-4" style="font-weight: 700;">Mau kemana sih?</h2>
            <p class="mb-4">Kamu belum memilih produk apapun untuk dibayar nih 🤔</p>
            <a href="products.php" class="custom-btn">Kembali pilih</a>

        </div>
    </body>
    </html>
    <?php
    exit;
}

$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($cart));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($products as &$product) {
    $pid = $product['id'];
    $product['quantity'] = $cart[$pid];
    $product['subtotal'] = $product['price'] * $product['quantity'];
    $total += $product['subtotal'];
}
unset($product);


$customer_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, address, phone FROM users WHERE id = ?");
$stmt->execute([$customer_id]);
$customer_info = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    if (!in_array($payment_method, ['cod', 'pickup'])) {
        $error = "Metode pembayaran tidak valid.";
    } else {
        $customer_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_price, payment_method) VALUES (?, ?, ?)");
        $stmt->execute([$customer_id, $total, $payment_method]);
        $order_id = $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmtDetail->execute([$order_id, $p['id'], $p['quantity'], $p['price']]);
        }

      
        $phone = preg_replace('/[^0-9]/', '', $customer_info['phone']);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+6282275387649';
        }


        $pesan = "Halo, saya memesan minuman ini ya:\n\n";
        foreach ($products as $p) {
            $pesan .= "- " . $p['name'] . " x " . $p['quantity'] . " = Rp " . number_format($p['subtotal'], 0, ',', '.') . "\n";
        }
        $pesan .= "\nTotal: Rp " . number_format($total, 0, ',', '.');
        $pesan .= "\nMetode: " . ($payment_method === 'cod' ? "Dihantar ke Alamat" : "Ambil di Tempat");
        $pesan .= "\n\nNama: " . $customer_info['username'];
        $pesan .= "\nAlamat: " . $customer_info['address'];
        $pesan .= "\nNomor: " . $customer_info['phone'];


        $pesan_encoded = urlencode($pesan);


        unset($_SESSION['cart']);

        header("Location: https://wa.me/$phone?text=$pesan_encoded");
        exit;

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
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
            background: linear-gradient(135deg, #f9d5e5, #d0f0fd, #fff6c3, #d9fdd3);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .container {
            max-width: 800px;
            background: white;
            border-radius: 12px;
            padding: 2rem 2.5rem;
            box-shadow: 0 4px 16px rgb(0 0 0 / 0.1);
        }

        h1 {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        table {
            margin-bottom: 1.5rem;
        }

        .payment-options {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .payment-option {
            position: relative;
            cursor: pointer;
            padding: 0.5rem 1.5rem;
            border: 2px var(--pink-dark) solid;
            border-radius: 50px;
            background-color: var(--pink-light);
            flex: 1 1 200px;
            text-align: center;
            font-weight: 600;
            color: var(--text-dark);    
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color:var(--pink-dark);
            background-color: var(--pink-dark);
            color: var(--text-dark);
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-option.selected {
            border-color: var(--pink-dark);
            background-color: var(--pink-dark);
            color: var(--text-dark);
            border-radius: 50px;
        }

        .btn-md-custom {
            padding: 0.3rem 1.25rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            min-width: 160px;
        }

        .btn-primary {
            background-color: var(--blue-light);
            color: var(--text-dark);
            border: 1.5px solid var(--blue-dark);
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--blue-dark);
            color: var(--text-dark);
            border-color: var(--blue-dark);
        }

        .btn-secondary {
            background-color: var(--green-light);
            color: var(--text-dark);
            border: 1.5px solid var(--green-dark);
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: var(--green-dark); 
            color: var(--text-dark);
            border-color: var(--green-dark);
        }

        .btn-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
            align-items: flex-start; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        <?php if ($customer_info): ?>
    <div class="mb-4">
        <p><strong>Nama:</strong> <?= htmlspecialchars($customer_info['username']) ?></p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($customer_info['address']) ?></p>
        <p><strong>No. Telepon:</strong> <?= htmlspecialchars($customer_info['phone']) ?></p>
    </div>
<?php endif; ?>


        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                        <td><?= $p['quantity'] ?></td>
                        <td>Rp <?= number_format($p['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th>Rp <?= number_format($total, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <form method="post" onsubmit="return validateSelection();">
            <div class="mb-3">
                <label class="form-label">Bagaimana Anda Ingin Menerima Pesanan?</label>
                <div class="payment-options">
                    <label class="payment-option" id="option-cod">
                        <input type="radio" name="payment_method" value="cod" />
                       Dihantar ke Alamat
                    </label>
                    <label class="payment-option" id="option-pickup">
                        <input type="radio" name="payment_method" value="pickup" />
                        Ambil di Tempat
                    </label>
                </div>
            </div>

            <div class="btn-wrapper">
                <button type="submit" class="btn btn-primary btn-md-custom">Pesan</button>
                <a href="products.php" class="btn btn-secondary btn-md-custom">Kembali</a>
            </div>
        </form>
    </div>

    <script>

        const options = document.querySelectorAll('.payment-option');
        options.forEach(option => {
            option.addEventListener('click', () => {
                options.forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                option.querySelector('input[type="radio"]').checked = true;
            });
        });

        function validateSelection() {
            const checked = document.querySelector('input[name="payment_method"]:checked');
            if (!checked) {
                alert('Silakan pilih metode pembayaran terlebih dahulu.');
                return false;
            }
            return true;
        }
    </script>

    
</body>
</html>
