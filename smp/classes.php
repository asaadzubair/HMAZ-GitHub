<?php
require 'config.php';
require 'includes/header.php';

// Only Admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

$message = '';
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? 0;
$edit_data = [];

// Handle Create/Update/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_class'])) {
        $grade = $_POST['grade'];
        $section = $_POST['section'];
        $id = $_POST['id'] ?? '';

        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE classes SET grade = ?, section = ? WHERE id = ?");
            try {
                $stmt->execute([$grade, $section, $id]);
                $message = "Class updated successfully!";
                $action = 'list';
            } catch (PDOException $e) { $message = "Error: Class likely already exists."; }
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO classes (grade, section) VALUES (?, ?)");
            try {
                $stmt->execute([$grade, $section]);
                $message = "Class added successfully!";
                $action = 'list';
            } catch (PDOException $e) { $message = "Error: Class already exists."; }
        }
    }
}

if ($action === 'delete' && $edit_id) {
    // Check for students? For now, we allow delete but maybe warn.
    // Ideally we should check foreign keys if we had them.
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$edit_id]);
    $message = "Class deleted successfully.";
    $action = 'list';
}

if ($action === 'edit' && $edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY grade, section")->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h2>Class Management</h2>
            <?php if($action === 'list'): ?>
                <a href="classes.php?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Class</a>
            <?php endif; ?>
        </div>
        <div class="page-content">
            <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="auth-card" style="max-width: 400px; margin: 0 auto; text-align: left;">
                    <h3><?php echo $action === 'edit' ? 'Edit Class' : 'Add New Class'; ?></h3>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                        <div class="form-group">
                            <label class="form-label">Grade / Class Level</label>
                            <select name="grade" class="form-control">
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($edit_data['grade'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" value="<?php echo $edit_data['section'] ?? ''; ?>" required placeholder="e.g. A, B, Green">
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                            <a href="classes.php" class="btn" style="background: #e2e8f0; margin-right: 0.5rem;">Cancel</a>
                            <button type="submit" name="save_class" class="btn btn-primary">Save Class</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Section</th>
                                    <th>Combined Label</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($classes as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['grade']); ?>th</td>
                                    <td><?php echo htmlspecialchars($c['section']); ?></td>
                                    <td><span class="badge badge-warning"><?php echo htmlspecialchars($c['grade'] . '-' . $c['section']); ?></span></td>
                                    <td>
                                        <a href="classes.php?action=edit&id=<?php echo $c['id']; ?>" style="color: var(--accent-color); margin-right: 0.5rem;"><i class="fas fa-edit"></i></a>
                                        <a href="classes.php?action=delete&id=<?php echo $c['id']; ?>" onclick="return confirm('Are you sure?');" style="color: var(--danger-color);"><i class="fas fa-trash"></i></a>
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
