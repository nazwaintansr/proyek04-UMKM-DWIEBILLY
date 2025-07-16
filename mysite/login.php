<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'seller') {
        header('Location: seller/dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'customer') {
        header('Location: customer/products.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login MySite</title>

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
    background: linear-gradient(135deg, var(--pink-light), var(--blue-light), var(--yellow-light), var(--green-light));
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.login-card {
    background: var(--white);
    padding: 2.5rem 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 400px;
    backdrop-filter: blur(8px);
    color: #var;
}

h1 {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color: var(--text-dark); 
    text-align: center;
}

label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.form-control {
    border-radius: 50px;
    border: 1.5px solid #ddd;
    border-color: var(--pink-dark);
    padding: 10px 14px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background-color: var(--white);
}

.form-control:focus {
    background-color: var(--white);
    border-color:var(--blue-light); 
    box-shadow: 0 0 0 0.15rem rgba(126,196,207,0.25);
}

button.btn-primary {
    background-color: var(--blue-dark);
    border: 2px solid var(--blue-dark);
    font-weight: 550;
    font-size: 1rem;
    border-radius: 50px;
    padding: 0.5rem 0.75rem;
    transition: background-color 0.3s ease, border-color 0.3s ease;
    color: var(--text-dark);
}

button.btn-primary:hover {
    background-color: var(--blue-light);
    border-color: var(--blue-light);
    color: var(--text-dark);
}


a {
    color: #ff6f91;
    text-decoration: none;
    font-weight: 600;
}

a:hover {
    color: #d81b60;
    text-decoration: underline;
}

.text-center p {
    font-weight: 500;
    font-size: 1rem;
    margin-top: 1.5rem;
    color: #666;
}

.alert {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    background-color: var(--pink-light);
    border-radius: 10px;
    border-color: var(--pink-dark);
    color: #c2185b;
}


.role-options {
    display: flex;
    gap: 1rem;
}

.role-btn {
    display: inline-block;
    padding: 10px 25px;
    font-weight: 550;
    font-size: 1rem;
    color:var(--text-dark);
    background-color:var(--green-light);
    border: 1.5px;
    border-color: var(--green-dark);
    border-radius: 50px;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.3s ease;
    user-select: none;
    text-align: center;
    flex-grow: 1;
}

.role-btn:hover {
    background-color: var(--green-light);
    border-color: var(--green-dark);
    color: var(--text-dark);

}

input[type="radio"]:checked + .role-btn {
    background-color: var(--green-dark);
    border-color: var(--green-dark);
    color: var(--text-dark);
    border-color: var(--green-dark);
}

input[type="radio"] {
    display: none;
}



</style>
</head>
<body>
    <div class="login-card shadow-sm">

    <div style="text-align: center; margin-bottom: 20px;">
        <img src="uploads/logo.png" alt="Logo" style="max-height: 100px; max-width: 100%; height: auto;">
    </div>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="process_login.php" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input autofocus type="text" class="form-control" id="username" name="username" required />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required />
        </div>

        <div class="mb-4">
            <label class="form-label d-block mb-2">Login sebagai</label>
            <div class="role-options">
                <input type="radio" id="role_seller" name="role" value="seller" required />
                <label for="role_seller" class="role-btn">Seller</label>

                <input type="radio" id="role_customer" name="role" value="customer" required />
                <label for="role_customer" class="role-btn">Customer</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center">
        <p>Belum punya akun?</p>
        <a href="customer/register.php">Daftar Customer</a>
            <div class="text-center">
    </div>
</div>

</body>
</html>
