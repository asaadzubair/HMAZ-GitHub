<?php
require 'config.php';
require 'includes/header.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

$message = '';
$date_filter = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    $attendances = $_POST['attendance'] ?? [];
    
    foreach ($attendances as $teacher_id => $status) {
        $sql = "INSERT INTO teacher_attendance (teacher_id, date, status) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE status = VALUES(status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$teacher_id, $date, $status]);
    }
    $message = "Teacher Attendance marked successfully for $date!";
}

$teachers = $pdo->query("SELECT t.*, ta.status as current_status 
                         FROM teachers t 
                         LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.date = '$date_filter' 
                         ORDER BY t.name")->fetchAll();
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Teacher Attendance</h2>
        </div>

        <div class="page-content">
             <?php if ($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container" style="padding: 1.5rem;">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                    </div>
                </form>

                <form method="POST">
                    <input type="hidden" name="date" value="<?php echo $date_filter; ?>">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($teachers as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 1rem;">
                                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $t['id']; ?>]" value="Present" <?php echo ($t['current_status'] === 'Present' || !$t['current_status']) ? 'checked' : ''; ?>>
                                                <span style="color: var(--success-color); font-weight: 500;">Present</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $t['id']; ?>]" value="Absent" <?php echo $t['current_status'] === 'Absent' ? 'checked' : ''; ?>>
                                                <span style="color: var(--danger-color); font-weight: 500;">Absent</span>
                                            </label>
                                             <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $t['id']; ?>]" value="Leave" <?php echo $t['current_status'] === 'Leave' ? 'checked' : ''; ?>>
                                                <span style="color: var(--warning-color); font-weight: 500;">Leave</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 2rem; text-align: right;">
                        <button type="submit" name="save_attendance" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
