<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Qstar Wave</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #007BFF;
            --bg: #F8FAFC;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1E293B;
        }
        .logo span { color: var(--primary); }
        h1 { font-size: 1.25rem; font-weight: 600; text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500; }
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            box-sizing: border-box;
        }
        input:focus { border-color: var(--primary); }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn:hover { background: #0056b3; }
        .error { color: #DC2626; font-size: 0.85rem; margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">Qstar <span>Wave</span></div>
        <h1>Admin Portal</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
