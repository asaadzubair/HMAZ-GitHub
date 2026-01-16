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
    $icon = $_POST['icon'] ?? 'monitor';
    $wa_message = $_POST['wa_message'];
    $category_id = $_POST['category_id'] ?: null;
    $image_path = $_POST['featured_image_path'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, icon=?, wa_message=?, category_id=?, image_path=? WHERE id=?");
        $stmt->execute([$name, $description, $icon, $wa_message, $category_id, $image_path, $id]);
        $message = "Service updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, icon, wa_message, category_id, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $icon, $wa_message, $category_id, $image_path]);
        $message = "Service added successfully!";
    }
    $action = 'list';
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: services.php");
    exit();
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Services Management</h2>
    <?php if ($action === 'list'): ?>
        <a href="?action=add" class="btn btn-primary"><i data-lucide="plus"></i> Add New Service</a>
    <?php else: ?>
        <a href="services.php" class="btn btn-primary"><i data-lucide="arrow-left"></i> Back to List</a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div style="background: #DCFCE7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($action === 'list'): 
    $services = $pdo->query("SELECT s.*, c.name as category_name FROM services s LEFT JOIN service_categories c ON s.category_id = c.id ORDER BY s.created_at ASC")->fetchAll();
?>
<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Visual</th>
                <th>Service Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td>
                    <?php if($s['image_path']): ?>
                        <img src="../<?php echo $s['image_path']; ?>" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;">
                    <?php else: ?>
                        <i data-lucide="<?php echo $s['icon']; ?>" style="width: 24px;"></i>
                    <?php endif; ?>
                </td>
                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                <td><span style="font-size:0.8rem; color:#64748b;"><?php echo htmlspecialchars($s['category_name'] ?? 'Uncategorized'); ?></span></td>
                <td><?php echo htmlspecialchars(substr($s['description'], 0, 50)); ?>...</td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-sm" style="color: red;" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): 
    $s = ['id' => '', 'name' => '', 'description' => '', 'icon' => 'monitor', 'wa_message' => '', 'category_id' => '', 'image_path' => ''];
    if ($action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $s = $stmt->fetch();
    }
    $categories = $pdo->query("SELECT * FROM service_categories ORDER BY name ASC")->fetchAll();
?>
<div class="table-card" style="padding: 2rem;">
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($s['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($s['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>WhatsApp CTA Message</label>
                    <textarea name="wa_message" rows="2" placeholder="Hi! I am interested in this service..."><?php echo htmlspecialchars($s['wa_message']); ?></textarea>
                </div>
            </div>
            <div>
                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Category</h4>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $s['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 1rem;">Visual Icon/Image</h4>
                    <div class="form-group">
                        <label>Lucide Icon ID</label>
                        <input type="text" name="icon" value="<?php echo htmlspecialchars($s['icon']); ?>" placeholder="monitor, search, etc.">
                    </div>
                    <p style="text-align: center; color: #94a3b8; font-size: 0.8rem; margin: 0.5rem 0;">- OR -</p>
                    <div id="image_preview_box" style="margin-bottom: 1rem; border: 1px dashed var(--border); border-radius: 8px; overflow: hidden; background: #f8fafc; text-align: center;">
                        <?php if($s['image_path']): ?>
                            <img id="featured_image_path_preview" src="../<?php echo $s['image_path']; ?>" style="max-width: 100%; display: block;">
                        <?php else: ?>
                            <img id="featured_image_path_preview" src="" style="max-width: 100%; display: none;">
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="featured_image_path" id="featured_image_path" value="<?php echo htmlspecialchars($s['image_path']); ?>">
                    <button type="button" class="btn btn-outline" style="width: 100%;" onclick="openMediaModal('featured_image_path')">Pick Image from Library</button>
                    <small style="display:block; margin-top:0.5rem; color:#94a3b8;">If an image is picked, the icon will be ignored.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem;">Save Service</button>
            </div>
        </div>
    </form>
</div>

<script>
    // Observer for image preview
    const imgInput = document.getElementById('featured_image_path');
    const preview = document.getElementById('featured_image_path_preview');

    setInterval(() => {
        if(imgInput.value && (preview.src.indexOf(imgInput.value) === -1)) {
            preview.src = '../' + imgInput.value;
            preview.style.display = 'block';
        }
    }, 500);
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
