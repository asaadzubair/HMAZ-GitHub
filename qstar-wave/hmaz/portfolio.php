<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'] ?: null;
    $link = $_POST['link'];
    
    $image = $_POST['featured_image_path'] ?? '';
    if (isset($_FILES['file_image']) && $_FILES['file_image']['error'] === 0) {
        $ext = pathinfo($_FILES['file_image']['name'], PATHINFO_EXTENSION);
        $filename = 'port_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['file_image']['tmp_name'], '../uploads/' . $filename)) {
            $image = 'uploads/' . $filename;
            // Add to media library
            $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $image, 'image/' . $ext]);
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE portfolio SET name=?, description=?, category_id=?, image=?, link=? WHERE id=?");
        $stmt->execute([$name, $description, $category_id, $image, $link, $id]);
        $message = "Project updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO portfolio (name, description, category_id, image, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $category_id, $image, $link]);
        $message = "Project added successfully!";
    }
    $action = 'list';
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: portfolio.php");
    exit();
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Portfolio Management</h2>
    <?php if ($action === 'list'): ?>
        <a href="?action=add" class="btn btn-primary"><i data-lucide="plus"></i> Add New Project</a>
    <?php else: ?>
        <a href="portfolio.php" class="btn btn-primary"><i data-lucide="arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($action === 'list'): 
    $projects = $pdo->query("SELECT p.*, c.name as category_name FROM portfolio p LEFT JOIN portfolio_categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
?>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th width="80">Image</th>
                <th>Project Name</th>
                <th>Category</th>
                <th>Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td><?php if($p['image']): ?><img src="../<?php echo $p['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"><?php endif; ?></td>
                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                <td><span style="font-size:0.8rem; color:#64748b;"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span></td>
                <td><a href="<?php echo htmlspecialchars($p['link']); ?>" target="_blank" style="color: var(--primary);"><i data-lucide="external-link" style="width:14px;"></i></a></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-sm" style="color: red;" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): 
    $p = ['id' => '', 'name' => '', 'description' => '', 'category_id' => '', 'image' => '', 'link' => ''];
    if ($action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $p = $stmt->fetch();
    }
    $categories = $pdo->query("SELECT * FROM portfolio_categories ORDER BY name ASC")->fetchAll();
?>
<div class="table-card" style="padding: 2rem;">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5"><?php echo htmlspecialchars($p['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Project Link</label>
                    <input type="text" name="link" value="<?php echo htmlspecialchars($p['link']); ?>" placeholder="https://example.com">
                </div>
            </div>
            <div>
                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Category</h4>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $p['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Cover Image</h4>
                    <div id="image_preview_box" style="margin-bottom: 1rem; border: 1px dashed var(--border); border-radius: 8px; overflow: hidden; background: #f8fafc; text-align: center;">
                        <?php if($p['image']): ?>
                            <img id="featured_image_path_preview" src="../<?php echo $p['image']; ?>" style="max-width: 100%; display: block;">
                        <?php else: ?>
                            <img id="featured_image_path_preview" src="" style="max-width: 100%; display: none;">
                            <div id="no_image_placeholder" style="padding: 2rem; color: #94a3b8;"><i data-lucide="image" style="width: 48px; height: 48px; margin-bottom: 0.5rem; opacity: 0.5;"></i><br>No image selected</div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="featured_image_path" id="featured_image_path" value="<?php echo htmlspecialchars($p['image']); ?>">
                    <div style="display: grid; gap: 0.5rem;">
                        <button type="button" class="btn btn-outline" style="width: 100%;" onclick="openMediaModal('featured_image_path')">Select from Library</button>
                        <p style="text-align: center; color: #94a3b8; font-size: 0.8rem;">- OR -</p>
                        <input type="file" name="file_image" style="font-size: 0.8rem;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem;">Save Project</button>
            </div>
        </div>
    </form>
</div>

<script>
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
