<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit;
}
require_once __DIR__ . '/../functions.php';

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Notifikasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
        :root {
            --pink-light: #f9d5e5;   
            --pink-dark: #d0f0fd;
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
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            margin: 0;
            padding: 2rem 1rem;
            color: #444444;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 700px;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        .btn-secondary {
            background-color: var(--blue-light);
            color: var(--text-dark);
            border: 1.5px solid var(--blue-dark);
            border-radius: 50px;
            font-weight: 550;
        }

        .btn-secondary:hover {
            background-color: var(--blue-dark);
            border-color:  var(--blue-dark);
            color: var(--text-dark);
        }

        .notification-card {
            background-color: #fffafc;
            border-left: 6px solid #f7d99c;
            margin-bottom: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .notification-card.unread {
            border-left-color: #ffb3c1;
            background-color: #fff0f4;
        }

        .notification-time {
            font-size: 0.875rem;
            color: #888;
            margin-bottom: 0.25rem;
        }

        .notification-message {
            font-size: 1rem;
            color: #444;
        }

        .badge-new {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: #7ab793;
            color: white;
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .no-notif {
            background-color: #e3f7f0;
            color: #2c5845;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        @media (max-width: 576px) {
            .notification-card {
                font-size: 0.95rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body class="d-flex justify-content-center">
    <div class="container">
        <h1>Notifikasi</h1>
        <a href="products.php" class="btn btn-secondary mb-4">Kembali</a>

        <?php if (empty($notifications)): ?>
            <div class="no-notif">Belum ada notifikasi.</div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-card <?= $notif['is_read'] ? '' : 'unread' ?>">
                    <?php if (!$notif['is_read']): ?>
                        <div class="badge-new">Baru</div>
                    <?php endif; ?>
                    <div class="notification-time">
                        <?= date('d M Y H:i', strtotime($notif['created_at'])) ?>
                    </div>
                    <div class="notification-message">
                        <?= htmlspecialchars($notif['message']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
