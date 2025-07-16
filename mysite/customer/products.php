<?php 
session_start();
require '../functions.php';


$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Daftar Produk</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

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
    background: linear-gradient(135deg, var(--pink-light), var(--blue-light), var(--yellow-light), var(--green-light));
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    margin: 0;
    color: var(--text-dark);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  #sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 220px;
    background-color : var(--pink-light); 
    color: var(--text-dark);
    padding-top: 80px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1000;
    box-shadow: 3px 0 8px rgba(0,0,0,0.1);
  }
  #sidebar.show {
    transform: translateX(0);
  }
  #sidebar a {
    color: var(--text-dark);
    text-decoration: none;
    display: block;
    padding: 2.5rem 1.5rem;
    font-weight: 700;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    border-radius: 0px;
  }
  #sidebar a:hover {
    background-color: var(--yellow-light);
  }


  #main-content {
    margin-left: 0;
    padding: 2rem 1.5rem 3rem 1.5rem;
    flex-grow: 1;
    margin-top: 70px;
    transition: margin-left 0.3s ease;
    max-width: 1200px;
    margin-right: auto;
    margin-left: auto;
  }
  #main-content.shifted {
    margin-left: 220px;
  }

 
  #header-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 80px;
    width: 100%;
    background: #ffffffcc;
    backdrop-filter: blur(8px);
    border-bottom: 1px solid #ddd;
    display: flex;
    align-items: center;
    padding: 0 3rem;
    box-shadow: 0 1px 6px rgb(0 0 0 / 0.1);
    z-index: 1100;
  }
  #menu-toggle {
    font-size: 1.8rem;
    cursor: pointer;
    color: #ff6f91 ;
    border: none;
    background: none;
    transition: color 0.3s ease;
  }
  #menu-toggle:hover {
    color: #4a9cb6;
  }
  #notif-button {
    margin-left: auto;
    font-size: 1.6rem;
    color: #ff6f91;
    cursor: pointer;
    background: none;
    border: none;
    transition: color 0.3s ease;
  }
  #notif-button:hover {
    color: #4a9cb6;
  }

  
  .products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.8rem;
  }
  @media (max-width: 1200px) {
    .products-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  @media (max-width: 768px) {
    .products-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 480px) {
    .products-grid {
      grid-template-columns: 1fr;
    }
  }

 
  .product-item {
    background: #ffffffcc;
    padding: 2rem 1.5rem 2.5rem 1.5rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    color: #333;
    font-size: 1.1rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
  }
  .product-item h5 {
    font-weight: 700;
    font-size: 1.3rem;
    margin-bottom: 0.8rem;
    color: #ff6f91; 
  }
  .product-item p {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    color: #555;
  }

 
  .quantity-group {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .quantity-group button {
    width: 36px;
    height: 36px;
    background-color: var(--pink-light); 
    border: 1.5px solid var(--pink-dark);
    color: var(--text-dark);
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.3rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .quantity-group button:hover {
    background-color:var(--pink-dark);
  }
  .quantity-group input {
    width: 60px;
    text-align: center;
    border-radius: 10px;
    border: 1.5px;
    border-color: var(--yellow-dark);
    padding: 8px 10px;
    font-size: 1rem;
    margin: 0 0.7rem;
    background-color: var(--yellow-light);
    color: var(--text-dark);
    font-weight: 600;
  }


  #checkout-button {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: var(--yellow-light);
  color: var(--text-dark);
  border: 2px solid var(--yellow-dark);
  border-radius: 50px;
  padding: 12px 20px;
  font-weight: 700;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.15);
  cursor: pointer;
  transition: transform 0.2s ease, background-color 0.3s ease;
  z-index: 999;
}

#checkout-button:hover {
  background-color: var(--yellow-dark);
  transform: scale(1.05);
}


.checkout-icon {
  background-color: white;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.checkout-text {
  font-size: 1.1rem;
  font-weight: bold;
}



</style>
</head>
<body>


<div id="header-bar" style="display: flex; align-items: center; justify-content: space-between; padding: 0 3rem; background-color: white;">

  <div style="display: flex; align-items: center;">

    <button id="menu-toggle" aria-label="Toggle sidebar menu" style="background: none; border: none; margin-right: 10px;">
      <i class="bi bi-list" style="font-size: 1.8rem;"></i>
    </button>


    <img src="/mysite/uploads/logo.png" alt="Logo Dwi Billy" style="height: 80px; object-fit: contain;">
  </div>

 
  <button id="notif-button" aria-label="Lihat notifikasi">
    <i class="bi bi-bell"></i>
  </button>
</div>


<div id="sidebar">
  <a href="profile.php">Akun Saya</a>
  <a href="../logout.php">Logout</a>
</div>

<div id="main-content">

  <form id="form-cart" method="post" action="checkout.php">
    <div class="products-grid">
      <?php foreach ($products as $product): 
        $pid = $product['id'];
        $qty = $cart[$pid] ?? 0;
      ?>
      <div class="product-item" tabindex="0" aria-label="Produk <?= htmlspecialchars($product['name']) ?> harga Rp <?= number_format($product['price'],0,',','.') ?>">
        
        <?php if (!empty($product['image'])): ?>
          <img src="/mysite/<?= htmlspecialchars($product['image']) ?>"
               alt="<?= htmlspecialchars($product['name']) ?>"
               style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:1rem;">
        <?php endif; ?>

        <h5><?= htmlspecialchars($product['name']) ?></h5>
        <p>Rp <?= number_format($product['price'],0,',','.') ?></p>

        <?php if (!empty($product['description'])): ?>
          <p style="font-size: 0.95rem; color:#666;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <?php endif; ?>

        <div class="quantity-group">
          <button type="button" class="btn-decrement" data-id="<?= $pid ?>" aria-label="Kurangi jumlah produk <?= htmlspecialchars($product['name']) ?>">-</button>
          <input type="text" class="quantity-input" name="quantities[<?= $pid ?>]" id="qty-<?= $pid ?>" value="<?= $qty ?>" readonly aria-live="polite" />
          <button type="button" class="btn-increment" data-id="<?= $pid ?>" aria-label="Tambah jumlah produk <?= htmlspecialchars($product['name']) ?>">+</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

<button id="checkout-button" aria-label="Lanjut Checkout">
  <div class="checkout-icon">
    🛒
  </div>
  <span class="checkout-text">Checkout</span>
</button>


  </form>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(function(){

    $('#menu-toggle').click(function(){
      $('#sidebar').toggleClass('show');
      $('#main-content').toggleClass('shifted');
    });


    $('#notif-button').click(function(){
      window.location.href = 'notifications.php';
    });


    $('.btn-increment').click(function(){
      const id = $(this).data('id');
      let $input = $('#qty-' + id);
      let qty = parseInt($input.val()) || 0;
      $input.val(qty + 1);
    });

    $('.btn-decrement').click(function(){
      const id = $(this).data('id');
      let $input = $('#qty-' + id);
      let qty = parseInt($input.val()) || 0;
      if (qty > 0) {
        $input.val(qty - 1);
      }
    });
  });
</script>

</body>
</html>
