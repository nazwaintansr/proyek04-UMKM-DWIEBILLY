<?php
session_start();
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$username || !$password || !$role) {
        header('Location: login.php?error=Lengkapi+semua+field');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($role === 'seller') {
            header('Location: seller/dashboard.php');
        } else {
            header('Location: customer/products.php');
        }
        exit;
    } else {
        header('Location: login.php?error=Username+atau+password+salah');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
