<?php
require 'config.php';
require 'includes/header.php';

// Fetch Basic Stats
$stats = [];
if ($_SESSION['role'] === 'admin') {
    $stats['students'] = $pdo->query("SELECT COUNT(*) FROM students WHERE status='Active'")->fetchColumn();
    $stats['teachers'] = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $stats['classes'] = $pdo->query("SELECT COUNT(DISTINCT class_section) FROM students")->fetchColumn();
    
    // Low Attendance Students (<75% this month)
    $monthStart = date('Y-m-01');
    $sqlLowAtt = "SELECT s.name, s.class_section, 
                  (SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as percentage 
                  FROM attendance a 
                  JOIN students s ON a.student_id = s.id 
                  WHERE a.date >= '$monthStart' 
                  GROUP BY s.id 
                  HAVING percentage < 75 
                  LIMIT 5";
    $lowAttStudents = $pdo->query($sqlLowAtt)->fetchAll();

} else {
    // Teacher Stats (Classes handled by them)
    // For simplicity, just showing generic counts or only students in their class?
    // Let's show total students for now as general info
    $stats['students'] = $pdo->query("SELECT COUNT(*) FROM students WHERE status='Active'")->fetchColumn();
    $stats['classes'] = "N/A"; // Logic to parse 'classes_handled' string is complex for quick stat
}

?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-weight: 500; color: var(--secondary-color);">
                    <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)
                </span>
                <div style="width: 32px; height: 32px; background: var(--accent-color); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>

        <div class="page-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['students']; ?></h3>
                        <p>Active Students</p>
                    </div>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success-color); background: rgba(16, 185, 129, 0.1);"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['teachers']; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--warning-color); background: rgba(245, 158, 11, 0.1);"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['classes']; ?></h3>
                        <p>Active Classes</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Main Chart Section -->
                <div class="table-container" style="padding: 1.5rem;">
                     <h3>Attendance Overview (This Month)</h3>
                     <canvas id="attendanceChart" height="150"></canvas>
                </div>

                <!-- Low Attendance / Sidebar Widget -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="table-container">
                    <div class="table-header">
                        <h2 style="font-size: 1.1rem;">Low Attendance Alerts</h2>
                    </div>
                    <?php if(!empty($lowAttStudents)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="padding: 0.75rem;">Name</th>
                                <th style="padding: 0.75rem;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lowAttStudents as $std): ?>
                            <tr>
                                <td style="padding: 0.75rem;"><?php echo htmlspecialchars($std['name']); ?> <small style="display:block; color: gray;"><?php echo htmlspecialchars($std['class_section']); ?></small></td>
                                <td style="padding: 0.75rem;"><span class="badge badge-danger"><?php echo number_format($std['percentage'], 1); ?>%</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="padding: 1.5rem; color: gray;">No alerts.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
// Mock data for initial render or simple logic
// In real app, fetch via PHP
const attendanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
            label: 'Student Attendance %',
            data: [85, 88, 82, 90], // Placeholder
            borderColor: '#3b82f6',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
        }
    }
});
</script>

<?php require 'includes/footer.php'; ?>
