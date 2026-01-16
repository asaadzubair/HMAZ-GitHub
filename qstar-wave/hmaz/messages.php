<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

// Handle Mark as Read
if (isset($_GET['mark_as_read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['mark_as_read']]);
    header("Location: messages.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: messages.php");
    exit();
}

// Handle CSV Export
if (isset($_GET['export'])) {
    $messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=enquiries_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Service', 'Message', 'Status', 'Date']);
    
    foreach ($messages as $m) {
        fputcsv($output, [$m['id'], $m['name'], $m['email'], $m['phone'], $m['service_selected'], $m['message'], $m['status'], $m['created_at']]);
    }
    fclose($output);
    exit();
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Contact Enquiries</h2>
    <a href="?export=1" class="btn btn-outline"><i data-lucide="download"></i> Export CSV</a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Name</th>
                <th>Email</th>
                <th>Service</th>
                <th>Message</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $enquiries = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
            foreach ($enquiries as $msg): 
            ?>
            <tr style="<?php echo $msg['status'] == 'unread' ? 'background: rgba(0, 123, 255, 0.02); font-weight: 500;' : ''; ?>">
                <td>
                    <span class="badge badge-<?php echo $msg['status']; ?>">
                        <?php echo $msg['status']; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                <td><?php echo htmlspecialchars($msg['email']); ?><br><small><?php echo htmlspecialchars($msg['phone']); ?></small></td>
                <td><?php echo htmlspecialchars($msg['service_selected']); ?></td>
                <td style="max-width: 300px;"><?php echo htmlspecialchars($msg['message']); ?></td>
                <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($msg['status'] == 'unread'): ?>
                            <a href="?mark_as_read=<?php echo $msg['id']; ?>" class="btn btn-sm btn-primary" title="Mark as Read"><i data-lucide="check"></i></a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-sm" style="color: #ef4444;" onclick="return confirm('Delete this enquiry?')"><i data-lucide="trash-2"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($enquiries)): ?>
            <tr><td colspan="7" style="text-align: center; padding: 2rem;">No enquiries found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
