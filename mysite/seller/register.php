<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register Seller</title>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

<style>
body {
    font-family: 'Nunito', sans-serif;
    background-color: #fafafa;
    height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #333333;
}
.register-card {
    background: #ffffff;
    padding: 2.5rem 3rem;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    max-width: 400px;
    width: 100%;
}
h1 {
    font-weight: 700;
    font-size: 1.9rem;
    margin-bottom: 1.5rem;
    color: #212529;
    text-align: center;
}
label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #555555;
}
.form-control {
    border-radius: 8px;
    border: 1.5px solid #ddd;
    padding: 10px 14px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}
.form-control:focus {
    border-color: #555555;
    box-shadow: none;
}
button.btn-primary {
    background-color: #444444;
    border: none;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 10px;
    padding: 12px 0;
    color: white;
    transition: background-color 0.3s ease;
    width: 100%;
}
button.btn-primary:hover {
    background-color: #222222;
}
a {
    color: #555555;
    text-decoration: none;
    font-weight: 600;
}
a:hover {
    color: #000000;
    text-decoration: underline;
}
.text-center p {
    font-weight: 500;
    font-size: 0.9rem;
    margin-top: 1.5rem;
    color: #666666;
}
.alert {
    font-size: 0.9rem;
    border-radius: 8px;
}
</style>
</head>
<body>
    <div class="register-card shadow-sm">
        <h1>Register Seller</h1>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="process_register_seller.php" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input autofocus type="text" class="form-control" id="username" name="username" required />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required />
            </div>

            <button type="submit" class="btn btn-primary">Daftar</button>
        </form>

        <div class="text-center">
            <p>Sudah punya akun?</p>
            <a href="../login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>
