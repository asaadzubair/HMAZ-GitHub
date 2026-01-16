<?php
require 'config.php';
require 'includes/header.php';

// Access Control
if ($_SESSION['role'] !== 'admin') {
    // Determine permissions (Teachers view only?)
    // header("Location: dashboard.php"); 
}

// Handle DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $message = "Student deleted successfully.";
    } else {
        $message = "Only Admin can delete students.";
    }
}

$message = $_GET['msg'] ?? ($message ?? '');

// Fetch Students
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM students WHERE name LIKE ? OR roll_number LIKE ? ORDER BY class_section, roll_number";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$students = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Student Management</h2>
            <?php if($_SESSION['role'] === 'admin'): ?>
            <div style="display: flex; gap: 1rem;">
                <a href="student_form.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Student</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="page-content">
            <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <form style="display: flex; gap: 0.5rem; width: 100%; max-width: 400px;">
                        <input type="text" name="search" placeholder="Search by name or roll no..." value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Roll #</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Parent Info</th>
                                <th>Contacts</th>
                                <th>Status</th>
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $std): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($std['roll_number']); ?></td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($std['name']); ?></div>
                                    <div style="font-size: 0.8rem; color: gray;">DOB: <?php echo $std['dob']; ?></div>
                                </td>
                                <td><span class="badge badge-warning" style="color: #b45309; background: #fffbeb;"><?php echo htmlspecialchars($std['class_section']); ?></span></td>
                                <td>
                                    <?php echo htmlspecialchars($std['parent_name']); ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        <i class="fas fa-phone" style="font-size: 0.7rem;"></i> S: <?php echo htmlspecialchars($std['contact_student'] ?? '-'); ?><br>
                                        <i class="fas fa-phone" style="font-size: 0.7rem;"></i> P: <?php echo htmlspecialchars($std['parent_contact'] ?? '-'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $std['status'] === 'Active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo htmlspecialchars($std['status']); ?>
                                    </span>
                                </td>
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                <td>
                                    <a href="student_form.php?id=<?php echo $std['id']; ?>" style="color: var(--accent-color); margin-right: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <a href="students.php?action=delete&id=<?php echo $std['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');" style="color: var(--danger-color);"><i class="fas fa-trash"></i></a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
