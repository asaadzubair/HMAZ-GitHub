<?php
require 'config.php';
require 'includes/header.php';

if ($_SESSION['role'] !== 'admin') {
     echo "<script>window.location.href='dashboard.php';</script>";
     exit;
}

$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? 0;
$message = '';
$edit_data = [];

// Handle POST Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_teacher'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $subjects = $_POST['subjects_taught'];
    $classes = $_POST['classes_handled'];
    $tid = $_POST['id'] ?? '';

    if ($tid) {
        // Update
        $stmt = $pdo->prepare("UPDATE teachers SET name=?, phone=?, subjects_taught=?, classes_handled=? WHERE id=?");
        $stmt->execute([$name, $phone, $subjects, $classes, $tid]);
        
        // Also update User login if exists? Not synced by name usually, but let's leave it decoupled or update 'users' table manually if we stored real name there.
        // For now, we only update teacher details.
        
        $message = "Teacher updated successfully.";
        $action = 'list';
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO teachers (name, phone, subjects_taught, classes_handled) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $phone, $subjects, $classes]);
            
            // Create Login
            $teacherId = $pdo->lastInsertId();
            $username = strtolower(str_replace(' ', '', $name)) . rand(10,99);
            $password = password_hash('123456', PASSWORD_BCRYPT);
            
            $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role, teacher_id, name) VALUES (?, ?, 'teacher', ?, ?)");
            $stmtUser->execute([$username, $password, $teacherId, $name]);
            
            $message = "Teacher added! Username: <strong>$username</strong>, Pass: 123456";
            $action = 'list';
        } catch(Exception $e) { $message = "Error: " . $e->getMessage(); }
    }
}

// Handle Delete
if ($action === 'delete' && $edit_id) {
    // Delete Teacher
    $pdo->prepare("DELETE FROM teachers WHERE id = ?")->execute([$edit_id]);
    // Delete User Login
    $pdo->prepare("DELETE FROM users WHERE teacher_id = ?")->execute([$edit_id]);
    $message = "Teacher deleted successfully.";
    $action = 'list';
}

// Handle Edit Fetch
if ($action === 'edit' && $edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();
}

$teachers = $pdo->query("SELECT * FROM teachers ORDER BY name")->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Teacher Management</h2>
            <?php if($action === 'list'): ?>
                <a href="teachers.php?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Teacher</a>
            <?php endif; ?>
        </div>

        <div class="page-content">
             <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo $message; // Allow HTML for username bold ?>
                </div>
            <?php endif; ?>

            <?php if($action === 'add' || $action === 'edit'): ?>
                <div class="auth-card" style="max-width: 600px; margin: 0 auto; text-align: left;">
                    <h3><?php echo $action === 'edit' ? 'Edit Teacher' : 'Add New Teacher'; ?></h3>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($edit_data['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subjects Taught</label>
                            <input type="text" name="subjects_taught" class="form-control" value="<?php echo htmlspecialchars($edit_data['subjects_taught'] ?? ''); ?>" placeholder="e.g. Maths, Physics">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Classes Handled</label>
                            <input type="text" name="classes_handled" class="form-control" value="<?php echo htmlspecialchars($edit_data['classes_handled'] ?? ''); ?>" placeholder="e.g. 9-A, 10-B">
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                              <a href="teachers.php" class="btn" style="background: #e2e8f0; margin-right: 0.5rem;">Cancel</a>
                            <button type="submit" name="save_teacher" class="btn btn-primary">Save Teacher</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subjects</th>
                                    <th>Classes</th>
                                    <th>Phone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($teachers as $t): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($t['name']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['subjects_taught']); ?></td>
                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($t['classes_handled']); ?></span></td>
                                    <td><?php echo htmlspecialchars($t['phone']); ?></td>
                                    <td>
                                        <a href="teachers.php?action=edit&id=<?php echo $t['id']; ?>" style="color: var(--accent-color); margin-right: 0.5rem;"><i class="fas fa-edit"></i></a>
                                        <a href="teachers.php?action=delete&id=<?php echo $t['id']; ?>" onclick="return confirm('Delete this teacher and their login?');" style="color: var(--danger-color);"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
