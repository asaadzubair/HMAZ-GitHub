<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

// Fetch stats
$blog_count = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$portfolio_count = $pdo->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
$service_count = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$message_count = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$media_count = $pdo->query("SELECT COUNT(*) FROM media")->fetchColumn();

// Fetch latest messages
$latest_messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h2 style="font-family: 'Outfit', sans-serif;">Welcome, Admin</h2>
    <p style="color: #64748B;">Here's what's happening on your site today.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Blog Posts</div>
        <div class="stat-value"><?php echo $blog_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Portfolio</div>
        <div class="stat-value"><?php echo $portfolio_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Services</div>
        <div class="stat-value"><?php echo $service_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Media</div>
        <div class="stat-value"><?php echo $media_count; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Recent Activity -->
    <div>
        <div class="table-card">
            <div class="table-header">
                <h3>Recent Enquiries</h3>
                <a href="messages.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_messages as $msg): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><span style="font-size: 0.8rem; color:#64748b;"><?php echo htmlspecialchars($msg['service_selected']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                        <td><span class="badge badge-<?php echo $msg['status']; ?>"><?php echo $msg['status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($latest_messages)): ?>
                    <tr><td colspan="5" style="text-align: center; color: #64748B;">No messages yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="table-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
            <div style="display: grid; gap: 0.75rem;">
                <a href="blogs.php?action=add" class="btn btn-outline" style="justify-content: flex-start;"><i data-lucide="plus"></i> New Blog Post</a>
                <a href="portfolio.php?action=add" class="btn btn-outline" style="justify-content: flex-start;"><i data-lucide="plus"></i> New Project</a>
                <a href="categories.php?type=blog" class="btn btn-outline" style="justify-content: flex-start;"><i data-lucide="tag"></i> Manage Categories</a>
                <a href="media.php" class="btn btn-outline" style="justify-content: flex-start;"><i data-lucide="upload"></i> Upload Media</a>
                <?php if ($is_super): ?>
                <a href="users.php?action=add" class="btn btn-primary" style="justify-content: flex-start; background: #10B981;"><i data-lucide="user-plus"></i> Create New User</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-card" style="padding: 1.5rem; background: #FFF7ED; border-color: #FED7AA;">
            <h3 style="margin-bottom: 0.5rem; color: #9A3412;">System Status</h3>
            <p style="font-size: 0.85rem; color: #C2410C; line-height: 1.4;">
                Website: <strong>Live</strong><br>
                PHP Version: <?php echo phpversion(); ?><br>
                New Enquiries: <?php echo $message_count; ?><br>
                Last Update: <?php echo date('M d, H:i'); ?>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
