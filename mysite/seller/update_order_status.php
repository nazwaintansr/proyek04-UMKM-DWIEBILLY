<?php
session_start();
require '../functions.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';

    $allowed_status = ['processing', 'on_delivery', 'completed', 'canceled'];
    if (!in_array($new_status, $allowed_status)) {
        die('Status tidak valid');
    }

    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

   
    $stmt2 = $pdo->prepare("SELECT customer_id FROM orders WHERE id = ?");
    $stmt2->execute([$order_id]);
    $customer_id = $stmt2->fetchColumn();

    
    if ($new_status === 'processing') {
        $message = 'Pesanan Anda sedang dibuat oleh seller.';
    } elseif ($new_status === 'on_delivery') {
        $message = 'Pesanan Anda sedang dalam perjalanan.';
    } elseif ($new_status === 'completed') {
        $message = 'Pesanan Anda sudah selesai. Terima kasih telah berbelanja.';
    } elseif ($new_status === 'canceled') {
        $message = 'Maaf pesanan anda telah dibatalkan.';
    }

  
    $stmt3 = $pdo->prepare("INSERT INTO notifications (user_id, order_id, message, is_read) VALUES (?, ?, ?, 0)");
    $stmt3->execute([$customer_id, $order_id, $message]);

   
    header('Location: seller_orders.php');
    exit;
}
?>
