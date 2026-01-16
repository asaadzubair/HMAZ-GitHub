<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['teacher_id'] = $user['teacher_id'];
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMP</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2 style="margin-bottom: 0.5rem; color: var(--text-main);">Welcome Back</h2>
            <p style="margin-bottom: 2rem; color: var(--text-muted);">Sign in to your account</p>

            <?php if($error): ?>
                <div style="background: #fee2e2; color: #ef4444; padding: 0.75rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Sign In
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                <p>Principal: <strong>principal</strong> / <strong>admin123</strong></p>
                <p>Teacher: <strong>aliraza</strong> / <strong>123456</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
