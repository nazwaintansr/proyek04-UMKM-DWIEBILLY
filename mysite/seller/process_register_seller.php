<?php
session_start();
require '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        header('Location: seller/register.php?error=' . urlencode('Username dan password wajib diisi.'));
        exit;
    }

  
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: seller/register.php?error=' . urlencode('Username sudah digunakan.'));
        exit;
    }


    $password_hash = password_hash($password, PASSWORD_DEFAULT);


    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'seller')");
    $stmt->execute([$username, $password_hash]);

  
    header('Location: ../login.php?register=success');
    exit;
} else {
    header('Location: seller/register.php');
    exit;
}
