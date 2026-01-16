<?php
$role = $_SESSION['role'] ?? 'guest';
$activePage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-school"></i>
        <span>SMP Karyal</span>
    </div>
    
    <nav class="nav-links">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo $activePage == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <?php if($role === 'admin'): ?>
        <div class="nav-item">
            <a href="classes.php" class="nav-link <?php echo $activePage == 'classes.php' ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i>
                <span>Class Management</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="subjects.php" class="nav-link <?php echo $activePage == 'subjects.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>Subjects</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="students.php" class="nav-link <?php echo $activePage == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="teachers.php" class="nav-link <?php echo $activePage == 'teachers.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Teachers</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="fees.php" class="nav-link <?php echo $activePage == 'fees.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Fee Management</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="nav-item">
            <a href="attendance.php" class="nav-link <?php echo $activePage == 'attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Student Attendance</span>
            </a>
        </div>

        <?php if($role === 'admin'): ?>
        <div class="nav-item">
            <a href="teacher_attendance.php" class="nav-link <?php echo $activePage == 'teacher_attendance.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-clock"></i>
                <span>Teacher Attendance</span>
            </a>
        </div>
        <?php endif; ?>
        
        <div class="nav-item">
            <a href="marks.php" class="nav-link <?php echo $activePage == 'marks.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Exams & Grades</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="timetable.php" class="nav-link <?php echo $activePage == 'timetable.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i>
                <span>Timetable</span>
            </a>
        </div>

        <div class="nav-item" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
            <a href="profile.php" class="nav-link <?php echo $activePage == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
             <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>
