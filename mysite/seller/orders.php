<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../functions.php';

$seller_id = $_SESSION['user_id'];
$message = '';
$error = '';


$valid_statuses = [
    'pending' => 'Pending',
    'processing' => 'Diproses',
    'on_delivery' => 'Sedang Dikirim',
    'completed' => 'Selesai',
    'canceled' => 'Batal'
];


$filter_status = $_GET['status'] ?? 'all';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);

    if (array_key_exists($status, $valid_statuses)) {
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(*) FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ? AND p.seller_id = ?
        ");
        $stmtCheck->execute([$order_id, $seller_id]);
        $hasAccess = $stmtCheck->fetchColumn() > 0;

        if ($hasAccess) {
         
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $success = $stmt->execute([$status, $order_id]);

            if ($success) {
                $message = "Status pesanan berhasil diubah menjadi '" . $valid_statuses[$status] . "'.";

          
                $stmtCustomer = $pdo->prepare("SELECT customer_id FROM orders WHERE id = ?");
                $stmtCustomer->execute([$order_id]);
                $order = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

                if ($order) {
                    $customer_id = $order['customer_id'];
                    $notif_message = '';

                    switch ($status) {
                        case 'pending':
                            $notif_message = 'Pesanan Anda sedang menunggu konfirmasi.';
                            break;
                        case 'processing':
                            $notif_message = 'Pesanan Anda sedang diproses oleh penjual.';
                            break;
                        case 'on_delivery':
                            $notif_message = 'Pesanan Anda sedang dikirim.';
                            break;
                        case 'completed':
                            $notif_message = 'Pesanan Anda telah selesai.';
                            break;
                        case 'canceled':
                            $notif_message = 'Pesanan Anda dibatalkan oleh penjual.';
                            break;
                    }

                    if ($notif_message !== '') {
                        $stmtNotif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $stmtNotif->execute([$customer_id, $notif_message]);
                    }
                }
            } else {
                $error = "Gagal mengubah status pesanan. Silakan coba lagi.";
            }
        } else {
            $error = "Anda tidak punya akses untuk mengubah status pesanan ini.";
        }
    } else {
        $error = "Status yang dipilih tidak valid.";
    }
}


$sql = "
    SELECT DISTINCT o.id, o.customer_id, o.order_date, o.status, u.username AS customer_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.customer_id = u.id
    WHERE p.seller_id = ?
";

$params = [$seller_id];
if ($filter_status !== 'all' && array_key_exists($filter_status, $valid_statuses)) {
    $sql .= " AND o.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Kelola Pesanan</title>
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
    font-family: 'Nunito', sans-serif;
    background-color: var(--blue-light); 
    padding: 2rem;
    color: #333;
}
h1 {
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #333;
}
.card {
    border-radius: 12px;
    background-color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #add6f5;
}
.btn {
    border-radius: 10px;
    font-weight: 550;
    padding: 5px 12px;
}



.btn-update {
    background-color: var(--pink-light);
    border-color: var(--pink-dark);
    border-radius: 50px;
    border: 1.5px   solid var(--pink-dark);
    color: var(--text-dark);
}
.btn-update:hover {
    background-color: var(--pink-dark);
    border-color: var(--pink-dark);
    color: var(--text-dark);
    outline: none;
}

.btn-detail {
    background-color: var(--blue-light);
    border-color: var(--blue-dark);
    border: 1.5px solid var(--blue-dark);
    border-radius: 50px;
    color: var(--text-dark);

}
.btn-detail:hover {
    background-color: var(--blue-dark);
    border-color: var(--blue-dark);
    color: var(--text-dark);
    outline: none;
}


.btn-secondary {
    background-color: var(--green-light);
    border:1.5px solid var(--green-dark);
    color: var(--text-dark);
    border-radius: 50px;
}
.btn-secondary:hover {
    background-color: var(--green-dark);
    border-color: var(--green-dark);
    color: var(--text-dark);
    outline: none;
}

.table {
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
}
.table th {
    background-color: var(--pink-light);
}
.table td, .table th {
    vertical-align: middle;
}

.status-select {
    max-width: 180px;
    border-radius: 10px;
}

.filter-form {
    margin-bottom: 1rem;
    background-color: var(--yellow-light);
    padding: 1.5rem;
    border-radius: 10px;
    border: 1px solid var(--yellow-dark);
}

.alert-success {
    background-color:var(--green-light);
    border-color: var(--green-dark);
    color: var(--text-dark);
    border-radius: 10px;
}

.alert-danger {
    background-color: var(--pink-light);
    border-color: var(--pink-dark);
    color: #a94442;
    border-radius: 10px;
}

.alert-info {
    background-color: var(--yellow-light);
    border-color: var(--yellow-dark);
    color: var(--text-dark);
    border-radius: 10px;
}
</style>

</head>
<body>
    <h1>Kelola Pesanan</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Kembali</a>

    <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="get" class="filter-form">
        <label for="status" class="form-label">Filter Status:</label>
        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua</option>
            <?php foreach ($valid_statuses as $value => $label): ?>
                <option value="<?= $value ?>" <?= $filter_status === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (count($orders) === 0): ?>
        <div class="alert alert-info">Belum ada pesanan.</div>
    <?php else: ?>
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>ID Pesanan</th>
                <th>Nama Customer</th>
                <th>Tanggal Pesanan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $index => $order): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td><?= date('d M Y H:i', strtotime($order['order_date'])) ?></td>
                <td>
                    <form method="post" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-select form-select-sm status-select" required>
                            <?php foreach ($valid_statuses as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-update btn btn-sm">Ubah</button>
                    </form>
                </td>
                <td>
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn-detail btn btn-sm">Detail</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
