<?php
require 'config.php';
require 'includes/header.php';

$monthStart = date('Y-m-01');
$monthName = date('F');

// 1. Student Monthly Attendance
$stmtStd = $pdo->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present 
    FROM attendance 
    WHERE date >= '$monthStart'");
$stdStats = $stmtStd->fetch();
$stdPct = $stdStats['total'] > 0 ? ($stdStats['present'] / $stdStats['total']) * 100 : 0;

// 2. Teacher Monthly Attendance
$stmtTch = $pdo->query("SELECT 
    COUNT(*) as total, 
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present 
    FROM teacher_attendance 
    WHERE date >= '$monthStart'");
$tchStats = $stmtTch->fetch();
$tchPct = $tchStats['total'] > 0 ? ($tchStats['present'] / $tchStats['total']) * 100 : 0;

// Message
$message = '';
$class_filter = $_GET['class'] ?? '';
$date_filter = $_GET['date'] ?? date('Y-m-d');

// 1. Fetch Classes for Dropdown
$classes = $pdo->query("SELECT DISTINCT class_section FROM students ORDER BY class_section")->fetchAll(PDO::FETCH_COLUMN);

// 2. Handle Attendance Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    $attendances = $_POST['attendance'] ?? [];
    
    foreach ($attendances as $student_id => $status) {
        // Insert or Update
        $sql = "INSERT INTO attendance (student_id, date, status, marked_by) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $date, $status, $_SESSION['user_id']]);
    }
    $message = "Attendance marked successfully for $date!";
     // Refresh Stats
    header("Refresh:0");
}

// 3. Fetch Students for Selected Class
$students = [];
if ($class_filter) {
    $sql = "SELECT s.*, a.status as current_status,
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND date >= '$monthStart') as month_total,
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND date >= '$monthStart' AND status = 'Present') as month_present
            FROM students s 
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ? 
            WHERE s.class_section = ? AND s.status = 'Active' 
            ORDER BY s.roll_number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_filter, $class_filter]);
    $students = $stmt->fetchAll();
}
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Student Attendance</h2>
        </div>

        <div class="page-content">
            <!-- Overall Stats -->
             <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stdPct, 1); ?>%</h3>
                        <p>Students Attendance (<?php echo $monthName; ?>)</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success-color); background: rgba(16, 185, 129, 0.1);"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($tchPct, 1); ?>%</h3>
                        <p>Teachers Attendance (<?php echo $monthName; ?>)</p>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container" style="padding: 1.5rem;">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Select Class</label>
                        <select name="class" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Select Class --</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $class_filter === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                    </div>
                </form>

                <?php if ($class_filter && !empty($students)): ?>
                <form method="POST">
                    <input type="hidden" name="date" value="<?php echo $date_filter; ?>">
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Roll #</th>
                                    <th>Name</th>
                                    <th>Month %</th>
                                    <th>Status (<?php echo $date_filter; ?>)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $std): 
                                    $mTotal = $std['month_total'];
                                    $mPresent = $std['month_present'];
                                    $mPct = $mTotal > 0 ? ($mPresent / $mTotal) * 100 : 0;
                                    $pctColor = $mPct < 75 ? 'color: var(--danger-color); font-weight: bold;' : '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($std['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($std['name']); ?></td>
                                    <td style="<?php echo $pctColor; ?>"><?php echo number_format($mPct, 0); ?>%</td>
                                    <td>
                                        <div style="display: flex; gap: 1rem;">
                                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $std['id']; ?>]" value="Present" <?php echo ($std['current_status'] === 'Present' || !$std['current_status']) ? 'checked' : ''; ?>>
                                                <span style="color: var(--success-color); font-weight: 500;">Present</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $std['id']; ?>]" value="Absent" <?php echo $std['current_status'] === 'Absent' ? 'checked' : ''; ?>>
                                                <span style="color: var(--danger-color); font-weight: 500;">Absent</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                                <input type="radio" name="attendance[<?php echo $std['id']; ?>]" value="Leave" <?php echo $std['current_status'] === 'Leave' ? 'checked' : ''; ?>>
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
                <?php elseif ($class_filter): ?>
                    <p style="color: gray;">No students found in this class.</p>
                <?php else: ?>
                    <p style="color: gray;">Please select a class to mark attendance.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
