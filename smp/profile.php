<?php
require 'config.php';
require 'includes/header.php';

$message = '';
$user_id = $_SESSION['user_id'];
// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['new_password'];

    $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
    $params = [$name, $email, $phone];

    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $user_id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = "Profile updated successfully!";
        // Refresh User Data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h2>My Profile</h2>
        </div>
        <div class="page-content">
            <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="auth-card" style="max-width: 600px; margin: 0 auto; text-align: left;">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username (Login ID)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #f1f5f9;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 2rem 0;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Change Password</h3>
                    <div class="form-group">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
