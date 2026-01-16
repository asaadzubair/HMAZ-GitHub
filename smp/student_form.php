<?php
require 'config.php';
require 'includes/header.php';

// Only Admin
if ($_SESSION['role'] !== 'admin') {
     echo "<script>window.location.href='dashboard.php';</script>";
     exit;
}

$id = $_GET['id'] ?? 0;
$student = [];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
}

// Fetch Classes for Dropdown
$classes = $pdo->query("SELECT * FROM classes ORDER BY grade, section")->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $roll = $_POST['roll_number'];
    // Construct class string from selected ID or values
    // Actually, we should store class_id if possible, but keeping string for compat "9-A"
    // Let's grab the class details from the ID passed
    $class_id = $_POST['class_id'];
    $stmtC = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmtC->execute([$class_id]);
    $cls = $stmtC->fetch();
    $class_section = $cls['grade'] . '-' . $cls['section'];

    $dob = $_POST['dob'];
    $parent = $_POST['parent_name'];
    $contact = $_POST['contact_student'];
    $p_contact = $_POST['parent_contact'];
    $address = $_POST['address'];
    $adm_date = $_POST['admission_date'];
    $status = $_POST['status'];

    if ($id) {
        // Update
        $sql = "UPDATE students SET name=?, roll_number=?, class_section=?, dob=?, parent_name=?, contact_student=?, parent_contact=?, address=?, admission_date=?, status=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$name, $roll, $class_section, $dob, $parent, $contact, $p_contact, $address, $adm_date, $status, $id]);
            $message = "Student updated successfully!";
            // Refresh
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $student = $stmt->fetch();
        } catch(PDOException $e) { $message = "Error: " . $e->getMessage(); }
    } else {
        // Insert
        $sql = "INSERT INTO students (name, roll_number, class_section, dob, parent_name, contact_student, parent_contact, address, admission_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$name, $roll, $class_section, $dob, $parent, $contact, $p_contact, $address, $adm_date, $status]);
            header("Location: students.php?msg=Student Added Successfully");
            exit;
        } catch(PDOException $e) { $message = "Error: " . $e->getMessage(); }
    }
}
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h2><?php echo $id ? 'Edit Student' : 'Add New Student'; ?></h2>
            <a href="students.php" class="btn btn-sm" style="background: #eee;">Back to List</a>
        </div>
        <div class="page-content">
            <?php if($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="auth-card" style="max-width: 800px; margin: 0 auto; text-align: left;">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" validation="required" value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_number" class="form-control" required value="<?php echo htmlspecialchars($student['roll_number'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Class/Section</label>
                            <select name="class_id" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                <?php foreach($classes as $c): 
                                    $val = $c['grade'].'-'.$c['section'];
                                    $sel = ($student['class_section'] ?? '') == $val ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $sel; ?>><?php echo $val; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?php echo $student['dob'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admission Date</label>
                            <input type="date" name="admission_date" class="form-control" value="<?php echo $student['admission_date'] ?? date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Active" <?php echo ($student['status'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Graduated" <?php echo ($student['status'] ?? '') == 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                                <option value="Left" <?php echo ($student['status'] ?? '') == 'Left' ? 'selected' : ''; ?>>Left</option>
                            </select>
                        </div>
                    </div>

                    <h3 style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Contact Information</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                         <div class="form-group">
                            <label class="form-label">Student Contact</label>
                            <input type="text" name="contact_student" class="form-control" value="<?php echo htmlspecialchars($student['contact_student'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parent/Guardian Name</label>
                            <input type="text" name="parent_name" class="form-control" value="<?php echo htmlspecialchars($student['parent_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parent Contact</label>
                            <input type="text" name="parent_contact" class="form-control" value="<?php echo htmlspecialchars($student['parent_contact'] ?? ''); ?>">
                        </div>
                         <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; text-align: right;">
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
