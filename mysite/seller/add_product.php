<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php';

$seller_id = $_SESSION['user_id'];
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $cost_price = intval($_POST['cost_price']);
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $filePath)) {
            $imagePath = 'uploads/' . $fileName;
        }
    }

    if ($name !== '' && $price >= 0 && $cost_price >= 0) {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, name, description, price, cost_price, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$seller_id, $name, $description, $price, $cost_price, $imagePath])) {
            $message = "Produk berhasil ditambahkan.";
            $success = true;
        } else {
            $message = "Gagal menambahkan produk baru.";
        }
    } else {
        $message = "Isi semua field dengan benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Produk</title>
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
        --yellow-dark: #fff5a5;
        --white: #fffdfc;
        --text-dark: #212529;
        --text-muted: #555;
    }

    body {
        background-color: var(--white);
        font-family: 'Nunito', sans-serif;
        color: var(--text-dark);
        padding: 3rem 1rem;
    }

    .container {
        max-width: 800px;
    }

    h2 {
        font-weight: bold;
        margin-bottom: 1.5rem;
        text-align: center;
        color : var(--text-dark);
    }

    .btn {
        border-radius: 50px;
        font-weight: 550;
        padding: 5px 12px;
        color: var(--text-dark);
        border: 1.5px solid transparent;
    }

    .btn-success {
        background-color: var(--green-light);
        border-color: var(--green-dark);
        color: var(--text-dark);
    }

    .btn-success:hover,
    .btn-success:focus {
        background-color: var(--green-dark);
        border-color: var(--green-dark);
        outline: none;
        color: var(--text-dark);

    }

    .btn-secondary {
        background-color: var(--blue-light);
        border-color: var(--blue-dark);
        color: var(--text-dark);
    }

    .btn-secondary:hover,
    .btn-secondary:focus {
        background-color: var(--blue-dark);
        border-color: var(--blue-dark);
        outline: none;
        color: var(--text-dark);

    }

    .form-control {
        border-radius: 50px;
        padding: 0.6rem 0.9rem;
        border: 1.5px solid var(--pink-dark);
        font-weight: 500;
        padding: 5px 10px;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-section {
        background-color: var(--yellow-light);
        border: 1.5px solid var(--pink-dark);
        border-radius: 16px;
        padding: 2rem;
        margin-top: 2rem;
    }

    .form-control:focus {
    border-color: var(--pink-dark);
    box-shadow: 0 0 6px var(--pink-dark);
    outline: none;
    background-color: var(--white);
    color: var(--text-dark);
}


    .alert {
        margin-top: 1.5rem;
        margin-bottom: 2rem;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
    }

    .alert-success {
        background-color: var(--green-light);
        border-left: 5px solid var(--green-dark);
    }

    .alert-warning {
        background-color: var(--pink-light);
        border-left: 5px solid var(--pink-dark);
    }

    .form-actions {
        text-align: right;
        margin-top: 1.5rem;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1rem;
        color: var(--text-muted);
    }
</style>

</head>
<body>

<div class="container">
    <h2>TAMBAH PRODUK</h2>

    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-warning' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-section">
        <div class="row g-4">
            <div class="col-md-6">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" name="name" id="name" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label for="price" class="form-label">Harga Jual (Rp)</label>
                <input type="number" name="price" id="price" class="form-control" min="0" required />
            </div>
            <div class="col-md-6">
                <label for="cost_price" class="form-label">Harga Modal (Rp)</label>
                <input type="number" name="cost_price" id="cost_price" class="form-control" min="0" required />
            </div>
            <div class="col-md-6">
                <label for="image" class="form-label">Gambar Produk</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" />
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Deskripsi Produk</label>
                <input type="text" name="description" id="description" class="form-control" />
            </div>
        </div>

  <div class="form-actions">
            <button type="submit" class="btn btn-success">Tambah</button>
            <a href="products.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
    </form>
</div>

</body>
</html>
