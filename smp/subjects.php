<?php
require 'config.php';
require 'includes/header.php';

// Only Admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$edit_id = $_GET['id'] ?? 0;
$edit_data = [];

// Handle Create/Update/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_subject'])) {
        $name = trim($_POST['name']);
        $class_level = trim($_POST['class_level']); // e.g., "9, 10" or "All"
        $id = $_POST['id'] ?? '';

        if (empty($name)) {
            $error = "Subject name is required.";
        } else {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE subjects SET name = ?, class_level = ? WHERE id = ?");
                try {
                    $stmt->execute([$name, $class_level, $id]);
                    $message = "Subject updated successfully!";
                    $action = 'list';
                } catch (PDOException $e) { $error = "Error updating subject."; }
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO subjects (name, class_level) VALUES (?, ?)");
                try {
                    $stmt->execute([$name, $class_level]);
                    $message = "Subject added successfully!";
                    $action = 'list';
                } catch (PDOException $e) { $error = "Error adding subject."; }
            }
        }
    }
}

if ($action === 'delete' && $edit_id) {
    // Check if used in timetable or marks (optional check, but good for safety)
    // For now, standard delete
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$edit_id]);
        $message = "Subject deleted successfully.";
    } catch (PDOException $e) {
        $error = "Cannot delete subject because it is linked to other records.";
    }
    $action = 'list';
}

if ($action === 'edit' && $edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name, class_level")->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h2>Subject Management</h2>
            <?php if($action === 'list'): ?>
                <a href="subjects.php?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Subject</a>
            <?php endif; ?>
        </div>
        <div class="page-content">
            <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="auth-card" style="max-width: 500px; margin: 0 auto; text-align: left;">
                    <h3><?php echo $action === 'edit' ? 'Edit Subject' : 'Add New Subject'; ?></h3>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                        <div class="form-group">
                            <label class="form-label">Subject Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" required placeholder="e.g. Mathematics">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Class Level / Tags</label>
                            <input type="text" name="class_level" class="form-control" value="<?php echo htmlspecialchars($edit_data['class_level'] ?? ''); ?>" required placeholder="e.g. 9, 10 or 'Primary'">
                            <small style="color: gray;">Which classes is this subject for? (Just for reference)</small>
                        </div>
                        <div style="text-align: right; margin-top: 1rem;">
                            <a href="subjects.php" class="btn" style="background: #e2e8f0; margin-right: 0.5rem;">Cancel</a>
                            <button type="submit" name="save_subject" class="btn btn-primary">Save Subject</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Subject Name</th>
                                    <th>Class Level</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($subjects)): ?>
                                    <tr><td colspan="3" style="text-align:center; padding: 2rem; color: gray;">No subjects found. Please add some.</td></tr>
                                <?php else: ?>
                                    <?php foreach($subjects as $s): ?>
                                    <tr>
                                        <td style="font-weight: 500;"><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><span class="badge badge-warning"><?php echo htmlspecialchars($s['class_level']); ?></span></td>
                                        <td>
                                            <a href="subjects.php?action=edit&id=<?php echo $s['id']; ?>" style="color: var(--accent-color); margin-right: 0.5rem;"><i class="fas fa-edit"></i></a>
                                            <a href="subjects.php?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure? This might affect existing timetables.');" style="color: var(--danger-color);"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
