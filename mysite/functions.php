<?php
// functions.php
// $host = 'sql300.infinityfree.com';
// $db   = 'if0_39140713_umkm';
// $user = 'if0_39140713';
// $pass = 'Dwibilly123';
$host = 'localhost';
$db   = 'umkm2';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
