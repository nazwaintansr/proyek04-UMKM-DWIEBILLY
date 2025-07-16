<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require '../functions.php';

$seller_id = $_SESSION['user_id'];
$message = '';


if (isset($_POST['action']) && $_POST['action'] === 'create') {
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
            $message = "Produk baru berhasil ditambahkan.";
        } else {
            $message = "Gagal menambahkan produk baru.";
        }
    } else {
        $message = "Isi semua field dengan benar.";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $cost_price = intval($_POST['cost_price']);
    $description = trim($_POST['description'] ?? '');
    $imagePath = null;

    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $currentProduct = $stmt->fetch();
    $imagePath = $currentProduct['image'] ?? null;

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
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, cost_price = ?, image = ? WHERE id = ? AND seller_id = ?");
        if ($stmt->execute([$name, $description, $price, $cost_price, $imagePath, $product_id, $seller_id])) {
            $message = "Produk berhasil diperbarui.";
        } else {
            $message = "Gagal memperbarui produk.";
        }
    } else {
        $message = "Isi semua field dengan benar.";
    }
}


if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    if ($stmt->execute([$product_id, $seller_id])) {
        $message = "Produk berhasil dihapus.";
    } else {
        $message = "Gagal menghapus produk.";
    }
}

$stmt = $pdo->prepare("SELECT id, name, description, price, cost_price, image FROM products WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Kelola Produk</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
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
        font-family: 'Nunito', sans-serif;
        background: var(--yellow-light);
        padding: 2rem;
        color: var(--text-dark);
    }

    h1 {
        font-weight: 700;
        font-size: 2.2rem;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
        text-align: center;
        text-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    a.btn-secondary {
        background-color: var(--green-light);
        border-color: var(--green-dark);
        color: var(--text-dark);
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    a.btn-secondary:hover {
        background-color: var(--green-dark);
        color: var(--text-dark);
        border-color: var(--green-dark);
    }

    .card {
        background: var(--yellow-light);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.07);
        margin-bottom: 3rem;
        border: 2px solid var(--yellow-dark);
    }

    h4 {
        color: var(--text-dark);
        margin-bottom: 1.2rem;
        font-weight: 700;
    }

    .form-control {
        border-radius: 12px;
        border: 2px solid var(--pink-dark);
        padding: 12px 16px;
        font-size: 1rem;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
        color: var(--text-dark);
    }
    .form-control:focus {
        border-color: var(--blue-dark);
        box-shadow: 0 0 6px var(--blue-dark);
        outline: none;
    }

    input[type="file"].form-control {
        padding: 5px 12px;
        background-color: var(--pink-light);
        border-color: var(--pink-dark);
        color: var(--text-dark);
        border-radius: 50px;

    }


.btn {
    border-radius: 50px;
    font-weight: 550;
    padding: 5px 10px;
    color: var(--text-dark);
    border: 1.5px solid transparent;
    cursor: pointer;
    display: inline-block;
    user-select: none;
    text-align: center;
}


.btn-success {
    background-color: var(--green-light);
    border-color: var(--green-dark);
    color: var(--text-dark);
}

.btn-success:hover,
.btn-success:focus {
    background-color: var(--pink-dark);
    border-color: var(--yellow-light);
    color: var(--text-dark);
    outline: none;
}


.btn-primary {
    background-color: var(--blue-light);
    border-color: var(--blue-dark);
    color: var(--text-dark);
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--blue-dark);
    border-color: var(--blue-light);
    color: var(--text-dark);
    outline: none;
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
    color: var(--text-dark);
    outline: none;
}


.btn-danger {
    background-color: var(--pink-light);
    border: 1.5px solid var(--pink-dark);
    color: var(--text-dark);
}

.btn-danger:hover,
.btn-danger:focus {
    background-color:var(--pink-dark);
    border-color: var(--pink-light);
    color: var(--text-dark);
    outline: none;
}


    table {
        background: var(--pink-dark);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border: none;
        width: 100%;
    }
    table.table thead {
        background: var(--pink-light) !important;
    }
    table.table thead th {
        background: var(--pink-light) !important;
        color: var(--text-dark) !important;
        font-weight: 700;
        text-align: center;
        vertical-align: middle;
        padding: 14px 10px;
        border: none;
    }

    table tbody tr {
        background: var(--pink-light);
        border-bottom: 1px solid var(--pink-dark);
    }
    table tbody tr:hover {
        background: var(--pink-light);
    }
    table tbody td {
        background: var(--pink-light);
        padding: 12px 8px;
        vertical-align: middle;
        text-align: center;
        color: var(--text-dark);
    }
    table tbody input[type="text"],
    table tbody input[type="number"] {
        border: 1.8px;
        border-radius: 50px;
        padding: 6px 10px;
        width: 100%;
        font-size: 1rem;
        color: var(--text-dark);
    }
    table tbody input[type="text"]:focus,
    table tbody input[type="number"]:focus {
        border-color: var(--blue-light);
        outline: none;
        background: #fff;
    }

    table.table,
    table.table th,
    table.table td {
        border: 1.5px solid var(--pink-dark) !important;
    }

    table.table thead th {
        border-bottom: 2px solid var(--pink-dark) !important;
    }

    table.table tbody tr {
        border-bottom: 1px solid var(--pink-dark) !important;
    }


    img {
        max-width: 70px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .alert {
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 550;
        color: var(--text-dark);
        background-color: var(--green-light);
        border: 1px solid var(--green-dark);
        padding: 12px 18px;
        margin-bottom: 2rem;
        text-align: left;
    }

    .d-flex.gap-2 > button {
        min-width: 70px;
    }

</style>
</head>
<body>
<div class="d-flex flex-column align-items-start mb-4">
    <h1 class="mb-3">Kelola Produk</h1>
    <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        <a href="add_product.php" class="btn btn-secondary">Tambah Produk</a>
    </div>
</div>


<?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<?php if (count($products) === 0): ?>
    <div class="alert">Belum ada produk.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Deskripsi</th>
            <th>Harga Jual (Rp)</th>
            <th>Modal (Rp)</th>
            <th>Gambar</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $index => $product): ?>
        <tr>
            <form method="post" enctype="multipart/form-data" onsubmit="return confirm('Yakin ingin simpan perubahan produk ini?');">
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
                <td><?= $index + 1 ?></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required /></td>
                <td><input type="text" name="description" value="<?= htmlspecialchars($product['description']) ?>" /></td>
                <td><input type="number" name="price" value="<?= $product['price'] ?>" min="0" required /></td>
                <td><input type="number" name="cost_price" value="<?= $product['cost_price'] ?>" min="0" required /></td>
                <td>
                    <?php if (!empty($product['image'])): ?>
                        <img src="../<?= htmlspecialchars($product['image']) ?>" alt="gambar" />
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="form-control mt-1" />
                </td>
                <td>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
            <form method="post" onsubmit="return confirm('Yakin ingin hapus produk ini?');">
                <input type="hidden" name="action" value="delete" />
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>" />
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </div>
            </form>
                </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
</body>
</html>