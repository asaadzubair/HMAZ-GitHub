<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['admin_id']);
}

function check_auth() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function check_superadmin() {
    global $pdo;
    check_auth();
    $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $role = $stmt->fetchColumn();
    if ($role !== 'superadmin') {
        die("Access denied. This section requires Superadmin privileges.");
    }
}
?>
