<?php
require 'config.php';

$student_id = $_GET['student_id'] ?? 0;
// Fetch Student
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if(!$student) die("Student not found");

// Check if Fee Record exists for this month, if not create/mock one
$currentMonth = date('Y-m-01');
$stmtFee = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? AND due_date >= ?");
$stmtFee->execute([$student_id, $currentMonth]);
$feeRecord = $stmtFee->fetch();

$invoiceNo = "INV-" . date('ym') . "-" . $student['roll_number'];
$date = date('d-M-Y');
$amount = 5000; // Default Fee
$paid = $feeRecord ? $feeRecord['paid_amount'] : 0;
$balance = $amount - $paid;

// Handle Mark Paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $paidAmount = $_POST['amount_paid'];
    $status = ($paidAmount >= $amount) ? 'Paid' : 'Partial';
    
    // Insert or Update
    // Simplification for demo: assuming one fee record per month
    if ($feeRecord) {
        $sql = "UPDATE fees SET paid_amount = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$paidAmount, $status, $feeRecord['id']]);
    } else {
        $sql = "INSERT INTO fees (student_id, title, total_amount, paid_amount, due_date, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, 'Monthly Tuition', $amount, $paidAmount, date('Y-m-10'), $status]);
    }
    
    // Refresh
    header("Location: invoice.php?student_id=$student_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $invoiceNo; ?></title>
    <!-- Use inline styles for better canvas rendering or link absolute -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body { background: #f1f5f9; padding: 2rem; display: flex; flex-direction: column; align-items: center; }
        .controls { margin-bottom: 2rem; display: flex; gap: 1rem; }
        .invoice-paper {
            background: white;
            width: 800px;
            padding: 3rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .inv-header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 2rem; margin-bottom: 2rem; }
        .inv-logo h1 { color: var(--primary-color); font-size: 1.8rem; margin-bottom: 0.5rem; }
        .inv-title { text-align: right; }
        .inv-details { display: flex; justify-content: space-between; margin-bottom: 3rem; }
        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        .inv-table th { background: #f8fafc; text-align: left; padding: 1rem; color: #64748b; }
        .inv-table td { padding: 1rem; border-bottom: 1px solid #eee; }
        .inv-total { text-align: right; margin-top: 1rem; }
        .inv-total p { margin-bottom: 0.5rem; font-size: 1.1rem; }
        .paid-stamp { 
            color: #10b981; border: 2px solid #10b981; padding: 0.5rem 1rem; 
            transform: rotate(-10deg); display: inline-block; font-weight: bold; font-size: 1.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="downloadInfo()" class="btn btn-primary"><i class="fas fa-download"></i> Download JPEG</button>
        <a href="fees.php" class="btn" style="background: white;">Back</a>
    </div>

    <!-- Invoice Area -->
    <div id="invoice-capture" class="invoice-paper">
        <div class="inv-header">
            <div class="inv-logo">
                <h1><?php echo defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name'; ?></h1>
                <p><?php echo defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address'; ?></p>
            </div>
            <div class="inv-title">
                <h2 style="color: #cbd5e1; font-size: 2.5rem;">INVOICE</h2>
                <p>#<?php echo $invoiceNo; ?></p>
                <p>Date: <?php echo $date; ?></p>
            </div>
        </div>

        <div class="inv-details">
            <div>
                <p style="color: #94a3b8; font-size: 0.9rem; text-transform: uppercase;">Bill To:</p>
                <h3 style="margin: 0.5rem 0;"><?php echo htmlspecialchars($student['name']); ?></h3>
                <p>Roll No: <?php echo htmlspecialchars($student['roll_number']); ?></p>
                <p>Class: <?php echo htmlspecialchars($student['class_section']); ?></p>
            </div>
            <div style="text-align: right;">
                 <?php if($paid >= $amount): ?>
                    <div class="paid-stamp">PAID</div>
                 <?php endif; ?>
            </div>
        </div>

        <table class="inv-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount (PKR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tuition Fee (<?php echo date('F Y'); ?>)</td>
                    <td style="text-align: right;"><?php echo number_format($amount); ?></td>
                </tr>
                <tr>
                    <td>Generators / Utilities</td>
                    <td style="text-align: right;">0</td>
                </tr>
            </tbody>
        </table>

        <div class="inv-total">
            <p>Subtotal: <strong><?php echo number_format($amount); ?></strong></p>
            <p style="color: #10b981;">Paid: <strong><?php echo number_format($paid); ?></strong></p>
            <div style="border-top: 2px solid #333; padding-top: 1rem; margin-top: 1rem;">
                <p style="font-size: 1.5rem;">Balance Due: <strong>PKR <?php echo number_format($balance); ?></strong></p>
            </div>
        </div>
        
        <div style="margin-top: 4rem; text-align: center; color: #94a3b8; font-size: 0.9rem;">
            <p>Thank you for your timely payment.</p>
            <p>Generated by SMP System</p>
        </div>
    </div>

    <!-- Payment Form (Admin Only) -->
    <?php if ($balance > 0): ?>
    <div style="margin-top: 3rem; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3>Update Payment Status</h3>
        <form method="POST" style="margin-top: 1rem; display: flex; gap: 1rem;">
            <input type="number" name="amount_paid" class="form-control" placeholder="Enter Amount Paid" value="<?php echo $amount; ?>">
            <button type="submit" name="mark_paid" class="btn btn-primary">Recieve Payment</button>
        </form>
    </div>
    <?php endif; ?>

    <script>
        function downloadInfo() {
            const invoice = document.getElementById('invoice-capture');
            html2canvas(invoice, { scale: 2 }).then(canvas => {
                const link = document.createElement('a');
                link.download = '<?php echo $invoiceNo; ?>.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            });
        }
    </script>
</body>
</html>
