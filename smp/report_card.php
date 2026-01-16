<?php
require 'config.php';

$student_id = $_GET['student_id'] ?? 0;
// Fetch Student Info with Attendance Summary
$stmt = $pdo->prepare("SELECT s.*, 
    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 'Present') as present_days,
    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id) as total_days
    FROM students s WHERE s.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) die("Student not found");

// Fetch Marks Grouped by Exam Type
$stmtMarks = $pdo->prepare("
    SELECT m.*, s.name as subject_name 
    FROM marks m 
    JOIN subjects s ON m.subject_id = s.id 
    WHERE m.student_id = ? 
    ORDER BY FIELD(m.exam_type, 'Monthly Test', 'Midterm', 'Final'), s.name
");
$stmtMarks->execute([$student_id]);
$marksRaw = $stmtMarks->fetchAll();

$groupedMarks = [];
foreach($marksRaw as $m) {
    $groupedMarks[$m['exam_type']][] = $m;
}

$attendance_pct = 0;
if ($student['total_days'] > 0) {
    $attendance_pct = ($student['present_days'] / $student['total_days']) * 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - <?php echo htmlspecialchars($student['name']); ?></title>
    <!-- Include html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #e2e8f0; 
            color: #1e293b; 
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover { opacity: 0.9; }
        .btn-outline { background: transparent; border: 2px solid #3b82f6; color: #3b82f6; }

        .report-card {
            background: white;
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            position: relative;
        }

        .header { text-align: center; border-bottom: 3px solid #1e293b; padding-bottom: 20px; margin-bottom: 40px; }
        .school-name { font-size: 28px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1e293b; }
        .school-address { color: #64748b; font-size: 14px; margin-top: 5px; }
        .report-title { 
            background: #1e293b; color: white; display: inline-block; 
            padding: 8px 30px; border-radius: 50px; margin-top: 20px; 
            font-weight: 600; font-size: 16px; 
        }

        .student-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .detail-col { flex: 1; }
        .detail-row { display: flex; margin-bottom: 10px; font-size: 14px; }
        .detail-label { width: 100px; font-weight: 600; color: #64748b; }
        .detail-val { font-weight: 600; color: #1e293b; }

        .section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 15px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px; }
        th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: center; }
        th { background: #f1f5f9; color: #475569; font-weight: 600; }
        tr:nth-child(even) { background: #f8fafc; }
        
        .grade-pass { color: #10b981; font-weight: 700; }
        .grade-fail { color: #ef4444; font-weight: 700; }

        .attendance-box {
            display: inline-block;
            border: 2px solid #e2e8f0;
            padding: 15px 30px;
            border-radius: 12px;
            text-align: center;
        }
        .att-val { font-size: 24px; font-weight: 800; color: #3b82f6; }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            padding-top: 20px;
        }
        .sig-box { text-align: center; width: 200px; }
        .sig-line { border-top: 1px solid #94a3b8; margin-top: 40px; padding-top: 10px; color: #64748b; font-size: 14px; }

        @media print {
            body { background: white; padding: 0; }
            .controls { display: none; }
            .report-card { box-shadow: none; width: 100%; min-height: auto; }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn">Print PDF</button>
        <button onclick="downloadImage()" class="btn btn-outline">Download JPEG</button>
    </div>

    <div class="report-card" id="reportCard">
        <div class="header">
            <div class="school-name"><?php echo defined('SCHOOL_NAME') ? SCHOOL_NAME : 'Government High School Karyal'; ?></div>
            <div class="school-address">Raiwind Road, District Lahore</div>
            <div class="report-title">Academic Progress Report</div>
        </div>

        <div class="student-details">
            <div class="detail-col">
                <div class="detail-row"><div class="detail-label">Name:</div> <div class="detail-val"><?php echo htmlspecialchars($student['name']); ?></div></div>
                <div class="detail-row"><div class="detail-label">Father Name:</div> <div class="detail-val"><?php echo htmlspecialchars($student['parent_name']); ?></div></div>
                <div class="detail-row"><div class="detail-label">DOB:</div> <div class="detail-val"><?php echo htmlspecialchars($student['dob']); ?></div></div>
            </div>
            <div class="detail-col" style="padding-left: 40px;">
                <div class="detail-row"><div class="detail-label">Roll No:</div> <div class="detail-val"><?php echo htmlspecialchars($student['roll_number']); ?></div></div>
                <div class="detail-row"><div class="detail-label">Class:</div> <div class="detail-val"><?php echo htmlspecialchars($student['class_section']); ?></div></div>
                <div class="detail-row"><div class="detail-label">Session:</div> <div class="detail-val"><?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></div></div>
            </div>
        </div>

        <?php if ($groupedMarks): ?>
            <?php foreach($groupedMarks as $examType => $examMarks): 
                $totalObt = 0; $totalMax = 0;
            ?>
            <div class="section-title"><?php echo htmlspecialchars($examType); ?> Result</div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left; width: 40%;">Subject</th>
                        <th>Total Marks</th>
                        <th>Obtained</th>
                        <th>%</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($examMarks as $m): 
                        $totalObt += $m['marks_obtained'];
                        $totalMax += $m['total_marks'];
                        $pct = ($m['marks_obtained'] / $m['total_marks']) * 100;
                        $grade = $pct >= 80 ? 'A+' : ($pct >= 70 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : ($pct >= 40 ? 'D' : 'F'))));
                        $gradeClass = ($grade === 'F') ? 'grade-fail' : 'grade-pass';
                    ?>
                    <tr>
                        <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($m['subject_name']); ?></td>
                        <td><?php echo number_format($m['total_marks'], 0); ?></td>
                        <td><?php echo number_format($m['marks_obtained'], 1); ?></td>
                        <td><?php echo number_format($pct, 1); ?>%</td>
                        <td class="<?php echo $gradeClass; ?>"><?php echo $grade; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background: #e2e8f0; font-weight: bold;">
                        <td style="text-align: left;">Overall Total</td>
                        <td><?php echo number_format($totalMax, 0); ?></td>
                        <td><?php echo number_format($totalObt, 1); ?></td>
                        <td><?php echo $totalMax > 0 ? number_format(($totalObt/$totalMax)*100, 1) : 0; ?>%</td>
                        <td>
                            <?php 
                                $finalPct = $totalMax > 0 ? ($totalObt/$totalMax)*100 : 0;
                                echo $finalPct >= 40 ? 'PASS' : 'FAIL';
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #64748b; background: white; border: 1px dashed #cbd5e1; border-radius: 8px;">
                No examination records found for this student.
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <div>
                <div class="section-title">Attendance Summary</div>
                <div class="attendance-box">
                    <div class="att-val"><?php echo number_format($attendance_pct, 0); ?>%</div>
                    <div style="font-size: 12px; color: #64748b;">Present</div>
                </div>
            </div>
            
            <div style="flex: 1; margin-left: 40px;">
                <div class="section-title">Remarks</div>
                <div style="border-bottom: 1px solid #cbd5e1; height: 30px; margin-bottom: 20px;"></div>
                <div style="border-bottom: 1px solid #cbd5e1; height: 30px;"></div>
            </div>
        </div>

        <div class="footer">
            <div class="sig-box"><div class="sig-line">Class Teacher</div></div>
            <div class="sig-box"><div class="sig-line">Principal</div></div>
            <div class="sig-box"><div class="sig-line">Parent / Guardian</div></div>
        </div>
    </div>

    <script>
        function downloadImage() {
            const element = document.getElementById('reportCard');
            html2canvas(element, { scale: 2 }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'ReportCard_<?php echo $student['roll_number']; ?>.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            });
        }
    </script>
</body>
</html>
