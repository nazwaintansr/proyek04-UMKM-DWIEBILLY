<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register Customer</title>

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
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--text-dark);
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.register-card {
    background: var(--white);
    padding: 2.5rem 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    max-width: 400px;
    width: 100%;
    backdrop-filter: blur(8px);
}

h1 {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color:var(--text-dark); 
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
    padding: 10px 14px;
    font-size: 1rem;
    background-color: var(--white);
    color: var(--text-dark);

}

.form-control:focus {
    border-color: var(--blue-dark); 
    border: 2px solid var(--blue-dark);
    box-shadow: none;
}

button.btn-primary {
    background-color: var(--yellow-dark);
    border: none;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 50px;
    padding: 0.5rem 0.75rem;
    color: var(--text-dark);
    transition: background-color 0.3s ease;
    width: 100%;
}

button.btn-primary:hover {
    background-color: var(--yellow-light);
    color: var(--text-dark);
    border-color: var(--yellow-dark);
    border-width: 1.5px;
    border: 1.5px solid var(--yellow-dark);  
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
    font-size: 0.9rem;
    margin-top: 1.5rem;
    color: #666;
}

.alert {
    font-size: 0.9rem;
    border-radius: 8px;
    background-color: var(--pink-light);
    border-color: var(--pink-dark);
    color: #c2185b;
}
</style>
</head>
<body>
    <div class="register-card shadow-sm">
        <h1>Register Customer</h1>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="process_register_customer.php" novalidate>
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
