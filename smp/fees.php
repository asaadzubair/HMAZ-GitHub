<?php
require 'config.php';
require 'includes/header.php';

// Only Admin access
if ($_SESSION['role'] !== 'admin') {
    // header("Location: dashboard.php");
}

$search = $_GET['search'] ?? '';

// Fetch Students and their FEE status for current month (simplified)
$currentMonth = date('Y-m'); // e.g. 2026-01

// In a real app, you'd have a fees table linkage left join
// Here we just list students and allow generating an invoice for 'Current Month'
$sql = "SELECT s.*, f.status as fee_status, f.paid_amount, f.total_amount 
        FROM students s 
        LEFT JOIN fees f ON s.id = f.student_id AND DATE_FORMAT(f.due_date, '%Y-%m') = ?
        WHERE s.status = 'Active' AND (s.name LIKE ? OR s.roll_number LIKE ?)
        ORDER BY s.class_section, s.roll_number";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentMonth, "%$search%", "%$search%"]);
$students = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Fee Management (<?php echo date('F Y'); ?>)</h2>
        </div>

        <div class="page-content">
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
                                <th>Month Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $std): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($std['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($std['name']); ?></td>
                                <td><?php echo htmlspecialchars($std['class_section']); ?></td>
                                <td>
                                    <?php if($std['fee_status'] == 'Paid'): ?>
                                        <span class="badge badge-success">Paid</span>
                                    <?php elseif($std['fee_status'] == 'Partial'): ?>
                                        <span class="badge badge-warning">Partial</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="invoice.php?student_id=<?php echo $std['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-file-invoice"></i> Generate Invoice
                                    </a>
                                </td>
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
