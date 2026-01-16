<?php
require 'config.php';
require 'includes/header.php';

// Check Role
$isAdmin = ($_SESSION['role'] === 'admin');
$teacherId = $_SESSION['teacher_id'] ?? null;

// Handle Form Submission (Admin Only)
$message = '';
$error = '';
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        // Delete Entry
        $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $message = "Entry deleted successfully.";
    } elseif (isset($_POST['save_entry'])) {
        // Add / Edit Entry
        $id = $_POST['entry_id'] ?? '';
        $class_section = $_POST['class_section'];
        $day = $_POST['day'];
        $subject_id = $_POST['subject_id'];
        $teacher_id = $_POST['teacher_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        if ($start_time >= $end_time) {
            $error = "Start time must be before end time.";
        } else {
            // Conflict Check
            // 1. Teacher Conflict
            $sqlT = "SELECT COUNT(*) FROM timetable WHERE teacher_id = ? AND day_of_week = ? 
                     AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
            $paramsT = [$teacher_id, $day, $end_time, $start_time, $start_time, $end_time]; // Overlap logic: (StartA < EndB) and (EndA > StartB)
            // Simpler: NOT (EndA <= StartB OR StartA >= EndB)
            // SQL: (start_time < ? AND end_time > ?) -> overlapping new entry
            $sqlConflict = "SELECT * FROM timetable WHERE day_of_week = ? AND id != ? AND (
                (start_time < ? AND end_time > ?) -- Overlaps
            )";
            
            // Check Teacher
            $stmt = $pdo->prepare($sqlConflict . " AND teacher_id = ?");
            $stmt->execute([$day, $id ?: 0, $end_time, $start_time, $teacher_id]);
            if ($stmt->fetch()) {
                $error = "Conflict: This teacher is already booked at this time!";
            }

            // Check Class
            if (!$error) {
                $stmt = $pdo->prepare($sqlConflict . " AND class_section = ?");
                $stmt->execute([$day, $id ?: 0, $end_time, $start_time, $class_section]);
                if ($stmt->fetch()) {
                    $error = "Conflict: This class already has a subject at this time!";
                }
            }

            if (!$error) {
                if ($id) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE timetable SET class_section=?, day_of_week=?, start_time=?, end_time=?, subject_id=?, teacher_id=? WHERE id=?");
                    $stmt->execute([$class_section, $day, $start_time, $end_time, $subject_id, $teacher_id, $id]);
                    $message = "Timetable updated successfully.";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO timetable (class_section, day_of_week, start_time, end_time, subject_id, teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$class_section, $day, $start_time, $end_time, $subject_id, $teacher_id]);
                    $message = "Entry added successfully.";
                }
            }
        }
    }
}

// Fetch Data for Filters/Form
$classes = $pdo->query("SELECT * FROM classes ORDER BY grade, section")->fetchAll();
// Fallback if classes table empty/error?
if (empty($classes)) {
    // try to get from students just in case
    $classesRaw = $pdo->query("SELECT DISTINCT class_section FROM students ORDER BY class_section")->fetchAll();
    // format to match structure if possible, but let's stick to standard if update_db run
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY name")->fetchAll();

// Determine View Filter
$selected_class = $_GET['class'] ?? '';
if (!$selected_class && !empty($classes) && $isAdmin) {
    $selected_class = $classes[0]['grade'] . '-' . $classes[0]['section'];
}

// Fetch Timetable Entries
$tableParams = [];
$tableSql = "SELECT t.*, s.name as subject_name, te.name as teacher_name, te.photo as teacher_photo 
             FROM timetable t 
             JOIN subjects s ON t.subject_id = s.id 
             JOIN teachers te ON t.teacher_id = te.id 
             WHERE 1=1";

if ($isAdmin) {
    if ($selected_class) {
        $tableSql .= " AND t.class_section = ?";
        $tableParams[] = $selected_class;
    }
} else {
    // Teacher View -> Show THEIR timetable
    $tableSql .= " AND t.teacher_id = ?";
    $tableParams[] = $teacherId;
}

$tableSql .= " ORDER BY start_time"; 
$entries = $pdo->prepare($tableSql);
$entries->execute($tableParams);
$all_entries = $entries->fetchAll();

// Group by Day
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$timetable = [];
foreach ($days as $d) $timetable[$d] = [];

foreach ($all_entries as $e) {
    $timetable[$e['day_of_week']][] = $e;
}

?>

<div class="dashboard-layout">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h2><?php echo $isAdmin ? 'Manage Timetable' : 'My Schedule'; ?></h2>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <?php if ($isAdmin): ?>
                    <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Add Entry</button>
                    <!-- Print Button -->
                    <button class="btn btn-sm" onclick="window.print()" style="background:var(--secondary-color); color:white;"><i class="fas fa-print"></i> Print</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="page-content">
            <!-- Messages -->
            <?php if ($message): ?>
                <div style="padding: 1rem; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Filters (Admin Only) -->
            <?php if ($isAdmin): ?>
            <div class="table-container" style="padding: 1rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <label style="font-weight: 500;">Select Class:</label>
                <form method="GET" style="display:flex; align-items:center; gap:0.5rem; flex:1;">
                    <select name="class" class="form-control" style="width: 200px;" onchange="this.form.submit()">
                        <?php foreach($classes as $c): 
                            $val = $c['grade'].'-'.$c['section'];
                        ?>
                            <option value="<?php echo $val; ?>" <?php echo $selected_class === $val ? 'selected' : ''; ?>>
                                Class <?php echo $val; ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if(empty($classes)): ?>
                            <option value="">No Classes Found</option>
                        <?php endif; ?>
                    </select>
                </form>
                <span class="badge badge-warning">Viewing: <?php echo htmlspecialchars($selected_class ?: 'All'); ?></span>
            </div>
            <?php endif; ?>

            <!-- Timetable Grid -->
            <!-- We use a CSS Grid for columns Mon-Sat -->
            <div class="timetable-wrapper">
                <style>
                    .tt-grid {
                        display: grid;
                        grid-template-columns: repeat(6, 1fr);
                        gap: 1rem;
                    }
                    .tt-day-col {
                        background: white;
                        border-radius: var(--radius);
                        box-shadow: var(--shadow-sm);
                        overflow: hidden;
                        border: 1px solid var(--border-color);
                    }
                    .tt-header {
                        background: var(--primary-color);
                        color: white;
                        text-align: center;
                        padding: 0.75rem;
                        font-weight: 600;
                    }
                    .tt-body {
                        padding: 0.5rem;
                        min-height: 200px;
                        background: #f8fafc;
                    }
                    .tt-card {
                        background: white;
                        border-left: 4px solid var(--accent-color);
                        padding: 0.75rem;
                        margin-bottom: 0.75rem;
                        border-radius: 4px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
                        position: relative;
                        transition: transform 0.2s;
                    }
                    .tt-card:hover { /* transform: scale(1.02); */ }
                    .tt-time { font-size: 0.75rem; font-weight: 700; color: var(--accent-color); margin-bottom: 0.25rem; display: block; }
                    .tt-subject { font-weight: 600; color: var(--text-main); font-size: 0.95rem; }
                    .tt-meta { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
                    .tt-actions {
                         position: absolute; top: 0.5rem; right: 0.5rem; display: none; 
                    }
                    .tt-card:hover .tt-actions { display: flex; gap: 0.25rem; }
                    .btn-icon { padding: 2px; font-size: 0.8rem; background: transparent; border: none; cursor: pointer; color: var(--text-muted); }
                    .btn-icon:hover { color: var(--accent-color); }
                    .btn-icon.del:hover { color: var(--danger-color); }

                    @media (max-width: 1024px) {
                        .tt-grid { grid-template-columns: repeat(3, 1fr); }
                    }
                    @media (max-width: 768px) {
                        .tt-grid { grid-template-columns: 1fr; }
                    }
                    @media print {
                        .sidebar, .topbar, .btn { display: none !important; }
                        .dashboard-layout { display: block; }
                        .main-content { margin: 0; }
                        .tt-grid { grid-template-columns: repeat(6, 1fr); gap: 0.5rem; }
                        .tt-day-col { border: 1px solid #ddd; }
                        .tt-header { background: #eee; color: black; border-bottom: 1px solid #ddd; }
                        .tt-card { border: 1px solid #ccc; box-shadow: none; break-inside: avoid; }
                    }
                </style>

                <div class="tt-grid">
                    <?php foreach ($days as $day): ?>
                    <div class="tt-day-col">
                        <div class="tt-header"><?php echo $day; ?></div>
                        <div class="tt-body">
                            <?php if (empty($timetable[$day])): ?>
                                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 2rem 0;">Free</div>
                            <?php else: ?>
                                <?php foreach ($timetable[$day] as $slot): ?>
                                    <div class="tt-card">
                                        <?php if ($isAdmin): ?>
                                            <div class="tt-actions">
                                                <button class="btn-icon" onclick='editEntry(<?php echo json_encode($slot); ?>)'><i class="fas fa-edit"></i></button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this slot?');">
                                                    <input type="hidden" name="delete_id" value="<?php echo $slot['id']; ?>">
                                                    <button type="submit" class="btn-icon del"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                        <span class="tt-time">
                                            <?php echo date('h:i A', strtotime($slot['start_time'])); ?> - 
                                            <?php echo date('h:i A', strtotime($slot['end_time'])); ?>
                                        </span>
                                        <div class="tt-subject"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                        <div class="tt-meta">
                                            <?php if ($isAdmin): ?>
                                                <i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($slot['teacher_name']); ?>
                                            <?php else: ?>
                                                <i class="fas fa-users"></i> <?php echo htmlspecialchars($slot['class_section']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="entryModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100; align-items:center; justify-content:center;">
    <div class="auth-card" style="position:relative; max-width:500px; text-align:left;">
        <button onclick="closeModal()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.2rem; cursor:pointer;">&times;</button>
        <h3 id="modalTitle" style="margin-bottom:1.5rem;">Add Timetable Entry</h3>
        
        <form method="POST">
            <input type="hidden" name="save_entry" value="1">
            <input type="hidden" name="entry_id" id="entry_id" value="">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Class</label>
                    <select name="class_section" id="class_section" class="form-control" required>
                        <?php foreach($classes as $c): $v=$c['grade'].'-'.$c['section']; ?>
                            <option value="<?php echo $v; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Day</label>
                    <select name="day" id="day" class="form-control" required>
                        <?php foreach($days as $d): ?>
                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject_id" id="subject_id" class="form-control" required>
                    <?php foreach($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?> (Class <?php echo $s['class_level']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" id="teacher_id" class="form-control" required>
                    <?php foreach($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="text-align: right; margin-top: 1rem;">
                <button type="button" onclick="closeModal()" class="btn" style="background:#e2e8f0; margin-right:0.5rem;">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('entryModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add Timetable Entry';
    document.getElementById('entry_id').value = '';
    // Reset form fields if needed or keep last default
}
function closeModal() {
    document.getElementById('entryModal').style.display = 'none';
}
function editEntry(data) {
    openModal();
    document.getElementById('modalTitle').innerText = 'Edit Timetable Entry';
    document.getElementById('entry_id').value = data.id;
    document.getElementById('class_section').value = data.class_section;
    document.getElementById('day').value = data.day_of_week;
    document.getElementById('start_time').value = data.start_time.substring(0, 5);
    document.getElementById('end_time').value = data.end_time.substring(0, 5);
    document.getElementById('subject_id').value = data.subject_id;
    document.getElementById('teacher_id').value = data.teacher_id;
}
</script>

<?php require 'includes/footer.php'; ?>
