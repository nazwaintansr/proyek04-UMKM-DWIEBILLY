<?php
session_start();
header('Content-Type: application/json');
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $total += (int)$qty;
    }
}
echo json_encode(['total' => $total]);
