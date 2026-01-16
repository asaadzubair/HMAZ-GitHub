<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

check_superadmin();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($id) {
        // Update
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username=?, password=?, role=? WHERE id=?");
            $stmt->execute([$username, $hashed, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET username=?, role=? WHERE id=?");
            $stmt->execute([$username, $role, $id]);
        }
        $message = "User updated successfully!";
    } else {
        // Create
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed, $role]);
            $message = "User created successfully!";
        } catch (PDOException $e) {
            $error = "Username already exists.";
        }
    }
    if (empty($error)) $action = 'list';
}

if ($action === 'delete' && isset($_GET['id'])) {
    if ($_GET['id'] != $_SESSION['admin_id']) {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: users.php");
        exit();
    } else {
        $error = "You cannot delete yourself!";
    }
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Admin User Management</h2>
    <?php if ($action === 'list'): ?>
        <a href="?action=add" class="btn btn-primary"><i data-lucide="user-plus"></i> Add New Admin</a>
    <?php else: ?>
        <a href="users.php" class="btn btn-primary"><i data-lucide="arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($action === 'list'): 
    $users = $pdo->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll();
?>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                <td><span class="badge badge-<?php echo $u['role'] == 'superadmin' ? 'read' : 'unread'; ?>"><?php echo $u['role']; ?></span></td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                    <a href="?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-sm" style="color: red;" onclick="return confirm('Are you sure?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): 
    $u = ['id' => '', 'username' => '', 'role' => 'admin'];
    if ($action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $u = $stmt->fetch();
    }
?>
<div class="table-card" style="padding: 2rem; max-width: 500px;">
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" required>
        </div>
        <div class="form-group">
            <label>Password <?php echo $action === 'edit' ? '(Leave blank to keep current)' : ''; ?></label>
            <input type="password" name="password" <?php echo $action === 'add' ? 'required' : ''; ?>>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="superadmin" <?php echo $u['role'] == 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="editor" <?php echo $u['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save User</button>
    </form>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
