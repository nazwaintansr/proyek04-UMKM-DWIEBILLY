<?php
session_start();
require '../functions.php';


$cart = $_SESSION['cart'] ?? [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? '';

    if ($action && $product_id && isset($cart[$product_id])) {
        if ($action === 'update') {
            $qty = max(1, (int)$_POST['quantity']);
            
            $cart[$product_id] = $qty;
        } elseif ($action === 'remove') {
            unset($cart[$product_id]);
        }
        $_SESSION['cart'] = $cart;
       
        header('Location: cart.php');
        exit;
    }
}


if (empty($cart)) {
    $empty = true;
} else {
    $empty = false;

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    $productsById = [];
    foreach ($products as $p) {
        $productsById[$p['id']] = $p;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Keranjang Belanja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  body { padding: 20px; }
  .qty-input {
    width: 60px;
    text-align: center;
    display: inline-block;
  }
  .btn-qty {
    width: 30px;
  }
</style>
</head>
<body>

<h1>Keranjang Belanja</h1>

<?php if ($empty): ?>
    <p>Keranjang anda kosong. <a href="products.php">Belanja sekarang</a></p>
<?php else: ?>

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Harga Satuan</th>
            <th style="width: 180px;">Jumlah</th>
            <th>Subtotal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total = 0;
        foreach ($cart as $pid => $qty): 
            if (!isset($productsById[$pid])) continue; 
            $prod = $productsById[$pid];
            $subtotal = $prod['price'] * $qty;
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($prod['name']) ?></td>
            <td>Rp <?= number_format($prod['price'],0,',','.') ?></td>
            <td>
                <form method="post" class="d-flex align-items-center gap-1">
                    <input type="hidden" name="product_id" value="<?= $pid ?>">
                    <input type="hidden" name="action" value="update">
                    <button type="button" class="btn btn-outline-secondary btn-qty btn-decrement">-</button>
                    <input type="text" name="quantity" value="<?= $qty ?>" class="qty-input" readonly>
                    <button type="button" class="btn btn-outline-secondary btn-qty btn-increment">+</button>
                    <button type="submit" class="btn btn-primary btn-sm ms-2">Update</button>
                </form>
            </td>
            <td>Rp <?= number_format($subtotal,0,',','.') ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Hapus produk ini dari keranjang?');">
                    <input type="hidden" name="product_id" value="<?= $pid ?>">
                    <input type="hidden" name="action" value="remove">
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-end">Total</th>
            <th>Rp <?= number_format($total,0,',','.') ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>

<div class="d-flex justify-content-end">
    <a href="products.php" class="btn btn-secondary me-2">Lanjut Belanja</a>
    <a href="checkout.php" class="btn btn-success">Checkout</a>
</div>

<?php endif; ?>

<script>
$(function(){

    $('.btn-increment').click(function(){
        let input = $(this).siblings('input[name="quantity"]');
        let val = parseInt(input.val());
        input.val(val + 1);
    });
    $('.btn-decrement').click(function(){
        let input = $(this).siblings('input[name="quantity"]');
        let val = parseInt(input.val());
        if(val > 1) input.val(val - 1);
    });
});
</script>

</body>
</html>
