<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $slug = $_POST['slug'] ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $content = $_POST['content'];
    $category_id = $_POST['category_id'] ?: null;
    $status = $_POST['status'];
    $seo_title = $_POST['seo_title'];
    $seo_desc = $_POST['seo_desc'];
    $publish_at = $_POST['publish_at'] ?? date('Y-m-d H:i:s');
    
    // Image Handling
    $featured_image = $_POST['featured_image_path'] ?? '';
    if (isset($_FILES['file_image']) && $_FILES['file_image']['error'] === 0) {
        $ext = pathinfo($_FILES['file_image']['name'], PATHINFO_EXTENSION);
        $filename = 'blog_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['file_image']['tmp_name'], '../uploads/' . $filename)) {
            $featured_image = 'uploads/' . $filename;
            // Also add to media library
            $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $featured_image, 'image/' . $ext]);
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE blogs SET title=?, slug=?, content=?, category_id=?, featured_image=?, status=?, publish_at=?, seo_title=?, seo_desc=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $category_id, $featured_image, $status, $publish_at, $seo_title, $seo_desc, $id]);
        $message = "Post updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, category_id, featured_image, status, publish_at, seo_title, seo_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $category_id, $featured_image, $status, $publish_at, $seo_title, $seo_desc]);
        $message = "Post created successfully!";
    }
    $action = 'list';
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: blogs.php");
    exit();
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Blog Management</h2>
    <?php if ($action === 'list'): ?>
        <a href="?action=add" class="btn btn-primary"><i data-lucide="plus"></i> Add New Post</a>
    <?php else: ?>
        <a href="blogs.php" class="btn btn-primary"><i data-lucide="arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($action === 'list'): 
    $blogs = $pdo->query("SELECT b.*, c.name as category_name FROM blogs b LEFT JOIN blog_categories c ON b.category_id = c.id ORDER BY b.created_at DESC")->fetchAll();
?>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th width="80">Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blogs as $b): ?>
            <tr>
                <td><?php if($b['featured_image']): ?><img src="../<?php echo $b['featured_image']; ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;"><?php endif; ?></td>
                <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                <td><span style="font-size:0.8rem; color:#64748b;"><?php echo htmlspecialchars($b['category_name'] ?? 'Uncategorized'); ?></span></td>
                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo $b['status']; ?></span></td>
                <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="?action=edit&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm" style="color: #ef4444;" onclick="return confirm('Delete this post?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($blogs)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 2rem;">No blog posts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): 
    $post = ['id' => '', 'title' => '', 'slug' => '', 'content' => '', 'category_id' => '', 'featured_image' => '', 'status' => 'published', 'publish_at' => date('Y-m-d H:i'), 'seo_title' => '', 'seo_desc' => ''];
    if ($action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch();
    }
    $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();
?>
<div class="table-card" style="padding: 2rem;">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <div class="form-group">
                    <label>Post Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug (URL Hook)</label>
                    <input type="text" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="leave-blank-to-auto-generate">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" class="editor" id="blogEditor" rows="15"><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
            </div>
            <div>
                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Publish</h4>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="scheduled" <?php echo $post['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Publish Date</label>
                        <input type="datetime-local" name="publish_at" value="<?php echo date('Y-m-d\TH:i', strtotime($post['publish_at'] ?? 'now')); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Save Post</button>
                </div>

                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Category</h4>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $post['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Featured Image</h4>
                    <div id="image_preview_box" style="margin-bottom: 1rem; border: 1px dashed var(--border); border-radius: 8px; overflow: hidden; background: #f8fafc; text-align: center;">
                        <?php if($post['featured_image']): ?>
                            <img id="featured_image_path_preview" src="../<?php echo $post['featured_image']; ?>" style="max-width: 100%; display: block;">
                        <?php else: ?>
                            <img id="featured_image_path_preview" src="" style="max-width: 100%; display: none;">
                            <div id="no_image_placeholder" style="padding: 2rem; color: #94a3b8;"><i data-lucide="image" style="width: 48px; height: 48px; margin-bottom: 0.5rem; opacity: 0.5;"></i><br>No image selected</div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="featured_image_path" id="featured_image_path" value="<?php echo htmlspecialchars($post['featured_image']); ?>">
                    <div style="display: grid; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline" style="width: 100%;" onclick="openMediaModal('featured_image_path')">Select from Library</button>
                        <p style="text-align: center; color: #94a3b8; font-size: 0.8rem;">- OR -</p>
                        <input type="file" name="file_image" style="font-size: 0.8rem;">
                    </div>
                </div>

                <div class="table-card" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">SEO Settings</h4>
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="seo_title" value="<?php echo htmlspecialchars($post['seo_title']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="seo_desc" rows="3"><?php echo htmlspecialchars($post['seo_desc']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    ClassicEditor
        .create( document.querySelector( '#blogEditor' ) )
        .catch( error => { console.error( error ); } );

    // Observer for image preview
    const imgInput = document.getElementById('featured_image_path');
    const preview = document.getElementById('featured_image_path_preview');
    const placeholder = document.getElementById('no_image_placeholder');

    setInterval(() => {
        if(imgInput.value && (preview.src.indexOf(imgInput.value) === -1)) {
            preview.src = '../' + imgInput.value;
            preview.style.display = 'block';
            if(placeholder) placeholder.style.display = 'none';
        }
    }, 500);
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
