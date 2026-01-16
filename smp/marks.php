<?php
require 'config.php';
require 'includes/header.php';

$message = '';
$error = '';

// Fetch Classes
$classes = $pdo->query("SELECT DISTINCT class_section FROM students ORDER BY class_section")->fetchAll(PDO::FETCH_COLUMN);
$selected_class = $_GET['class'] ?? '';

// Fetch Subjects based on Class
$subjects = [];
if ($selected_class) {
    // Try to match specific class level (e.g. '9' from '9-A')
    $classLevel = explode('-', $selected_class)[0];
    if (ctype_digit($classLevel)) {
         // Use LIKE to match if subject has multiple levels e.g. "9, 10"
         $subjects = $pdo->prepare("SELECT * FROM subjects WHERE class_level LIKE ? OR class_level = 'All' ORDER BY name");
         $subjects->execute(["%$classLevel%"]);
         $subjects = $subjects->fetchAll();
    } else {
        // Fallback for non-numeric grade names or generic
        $subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_marks'])) {
    $exam_type = $_POST['exam_type'];
    $subject_id = $_POST['subject_id'];
    $total_marks = $_POST['total_marks'];

    if (!$subject_id || !$exam_type) {
        $error = "Please select Subject and Exam Type.";
    } else {
        try {
            $pdo->beginTransaction();
            foreach ($_POST['marks'] as $student_id => $val) {
                // If empty, we could either skip or set to null/0. Let's skip if strictly empty string, but allow 0.
                if ($val === '') continue;

                // Check existing
                $stmtCheck = $pdo->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND exam_type = ?");
                $stmtCheck->execute([$student_id, $subject_id, $exam_type]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    $stmtUpd = $pdo->prepare("UPDATE marks SET marks_obtained = ?, total_marks = ?, date_entered = NOW() WHERE id = ?");
                    $stmtUpd->execute([$val, $total_marks, $existing['id']]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO marks (student_id, subject_id, exam_type, marks_obtained, total_marks, date_entered) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmtIns->execute([$student_id, $subject_id, $exam_type, $val, $total_marks]);
                }
            }
            $pdo->commit();
            $message = "Marks saved successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error saving marks: " . $e->getMessage();
        }
    }
}

// Fetch Students & Existing Marks
$students = [];
$existing_marks = [];
$current_total = 100;

if ($selected_class && isset($_GET['subject_id']) && isset($_GET['exam_type'])) {
    $subject_id = $_GET['subject_id'];
    $exam_type = $_GET['exam_type'];
    
    // Fetch Students
    $stmtS = $pdo->prepare("SELECT * FROM students WHERE class_section = ? AND status='Active' ORDER BY roll_number");
    $stmtS->execute([$selected_class]);
    $students = $stmtS->fetchAll();

    // Fetch Existing Marks for this slot
    $stmtM = $pdo->prepare("SELECT student_id, marks_obtained, total_marks FROM marks WHERE subject_id = ? AND exam_type = ?");
    $stmtM->execute([$subject_id, $exam_type]);
    while ($row = $stmtM->fetch()) {
        $existing_marks[$row['student_id']] = $row['marks_obtained'];
        if ($row['total_marks']) $current_total = $row['total_marks'];
    }
}
?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2>Exams & Grades</h2>
        </div>

        <div class="page-content">
            <?php if ($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                 <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="table-container" style="padding: 1.5rem;">
                <!-- Selection Form -->
                <form method="GET" action="marks.php">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Class</label>
                            <select name="class" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Select Class --</option>
                                <?php foreach($classes as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo $selected_class === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($selected_class): ?>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">-- Select Subject --</option>
                                <?php foreach($subjects as $sub): ?>
                                    <option value="<?php echo $sub['id']; ?>" <?php echo (isset($_GET['subject_id']) && $_GET['subject_id'] == $sub['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sub['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Exam Type</label>
                            <select name="exam_type" class="form-control" required>
                                <?php 
                                $types = ['Monthly Test', 'Midterm', 'Final'];
                                foreach($types as $t): ?>
                                    <option value="<?php echo $t; ?>" <?php echo (isset($_GET['exam_type']) && $_GET['exam_type'] == $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                           <button type="submit" class="btn btn-primary" style="width:100%;">Load Students</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($students) && isset($_GET['subject_id'])): ?>
                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #eee;">
                
                <form method="POST">
                    <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($_GET['exam_type']); ?>">
                    <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($_GET['subject_id']); ?>">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="font-size: 1.1rem; color: var(--secondary-color);">Enter Marks</h3>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <label>Total Marks:</label>
                            <input type="number" name="total_marks" id="total_input" class="form-control" style="width: 80px;" value="<?php echo $current_total; ?>" onchange="updateGrades()">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Roll #</th>
                                    <th>Name</th>
                                    <th width="150">Marks Obtained</th>
                                    <th width="100">Grade</th>
                                    <th width="80">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $std): 
                                    $val = $existing_marks[$std['id']] ?? '';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($std['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($std['name']); ?></td>
                                    <td>
                                        <input type="number" step="0.5" min="0" 
                                               name="marks[<?php echo $std['id']; ?>]" 
                                               class="form-control mark-input" 
                                               value="<?php echo $val; ?>" 
                                               oninput="calcRow(this)">
                                    </td>
                                    <td class="grade-cell" style="font-weight:bold;">-</td>
                                    <td class="pct-cell" style="color:gray;">-</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: right;">
                        <button type="submit" name="save_marks" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function calcRow(input) {
    const row = input.closest('tr');
    const total = parseFloat(document.getElementById('total_input').value) || 100;
    const obtained = parseFloat(input.value);
    
    const gradeCell = row.querySelector('.grade-cell');
    const pctCell = row.querySelector('.pct-cell');

    if (input.value === '' || isNaN(obtained)) {
        gradeCell.innerText = '-';
        pctCell.innerText = '-';
        input.style.borderColor = '#e2e8f0';
        return;
    }

    if (obtained > total) {
        input.style.borderColor = 'red';
        // Optional: input.setCustomValidity('Cannot exceed total marks');
    } else {
        input.style.borderColor = '#10b981'; // green hint
    }

    const pct = (obtained / total) * 100;
    pctCell.innerText = pct.toFixed(1) + '%';
    
    let grade = 'F';
    if (pct >= 80) grade = 'A+';
    else if (pct >= 70) grade = 'A';
    else if (pct >= 60) grade = 'B';
    else if (pct >= 50) grade = 'C';
    else if (pct >= 40) grade = 'D';
    
    gradeCell.innerText = grade;
    
    // Color code grade
    if(grade === 'F') gradeCell.style.color = 'var(--danger-color)';
    else if(grade === 'A+' || grade === 'A') gradeCell.style.color = 'var(--success-color)';
    else gradeCell.style.color = 'var(--warning-color)';
}

function updateGrades() {
    const inputs = document.querySelectorAll('.mark-input');
    inputs.forEach(input => calcRow(input));
}

// Initial Calc
document.addEventListener('DOMContentLoaded', updateGrades);
</script>

<?php require 'includes/footer.php'; ?>
