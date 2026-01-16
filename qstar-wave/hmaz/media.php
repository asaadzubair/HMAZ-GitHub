<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

$message = '';
$error = '';

// Handle Uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

    if (in_array($ext, $allowed)) {
        $filename = 'media_' . time() . '_' . uniqid() . '.' . $ext;
        $filepath = 'uploads/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $stmt = $pdo->prepare("INSERT INTO media (filename, filepath, filetype, filesize) VALUES (?, ?, ?, ?)");
            $stmt->execute([$file['name'], $filepath, $file['type'], $file['size']]);
            $message = "File uploaded successfully!";
        } else {
            $error = "Failed to move uploaded file.";
        }
    } else {
        $error = "Invalid file type. Allowed: " . implode(', ', $allowed);
    }
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT filepath FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();

    if ($file) {
        if (file_exists('../' . $file['filepath'])) {
            unlink('../' . $file['filepath']);
        }
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Media deleted successfully!";
    }
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Media Library</h2>
    <form id="upload-form" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem;">
        <input type="file" name="file" id="file-input" style="display: none;" onchange="this.form.submit()">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('file-input').click()">
            <i data-lucide="upload"></i> Upload New Media
        </button>
    </form>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $error; ?></div>
<?php endif; ?>

<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    .media-item {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        transition: 0.2s;
        position: relative;
    }
    .media-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .media-preview {
        height: 150px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-info {
        padding: 0.75rem;
    }
    .media-name {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #1e293b;
    }
    .media-meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }
    .media-actions {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: 0.2s;
    }
    .media-item:hover .media-actions {
        opacity: 1;
    }
    .media-btn {
        background: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        color: #64748b;
    }
    .media-btn:hover { color: var(--primary); }
    .media-btn.delete:hover { color: #ef4444; }

    /* Modal for Selection Mode (Will be used later) */
    .media-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .media-modal-content {
        background: white;
        width: 90%;
        max-width: 1000px;
        max-height: 80vh;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
    }
</style>

<div class="media-grid">
    <?php
    $media = $pdo->query("SELECT * FROM media ORDER BY created_at DESC")->fetchAll();
    foreach ($media as $m):
        $size = round($m['filesize'] / 1024, 2) . ' KB';
    ?>
    <div class="media-item">
        <div class="media-preview">
            <img src="../<?php echo $m['filepath']; ?>" alt="<?php echo htmlspecialchars($m['filename']); ?>">
        </div>
        <div class="media-info">
            <div class="media-name"><?php echo htmlspecialchars($m['filename']); ?></div>
            <div class="media-meta"><?php echo $size; ?> • <?php echo date('M d, Y', strtotime($m['created_at'])); ?></div>
        </div>
        <div class="media-actions">
            <button class="media-btn" onclick="copyToClipboard('<?php echo $m['filepath']; ?>')" title="Copy Path">
                <i data-lucide="copy"></i>
            </button>
            <a href="?delete=<?php echo $m['id']; ?>" class="media-btn delete" onclick="return confirm('Are you sure you want to delete this file?')" title="Delete">
                <i data-lucide="trash-2"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($media)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: white; border-radius: 12px; border: 1px dashed var(--border);">
            <i data-lucide="image" style="width: 48px; height: 48px; color: #94a3b8; margin-bottom: 1rem;"></i>
            <p style="color: #64748b;">No media files uploaded yet.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('File path copied to clipboard!');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
