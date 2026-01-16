<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

$type = $_GET['type'] ?? 'blog';
$table = '';
$page_title = '';

switch($type) {
    case 'portfolio':
        $table = 'portfolio_categories';
        $page_title = 'Portfolio Categories';
        break;
    case 'service':
        $table = 'service_categories';
        $page_title = 'Service Categories';
        break;
    default:
        $table = 'blog_categories';
        $page_title = 'Blog Categories';
}

$message = '';
$error = '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $slug = $_POST['slug'] ?: strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $description = $_POST['description'] ?? '';

    if ($id) {
        if ($type === 'blog') {
            $stmt = $pdo->prepare("UPDATE $table SET name=?, slug=?, description=? WHERE id=?");
            $stmt->execute([$name, $slug, $description, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE $table SET name=?, slug=? WHERE id=?");
            $stmt->execute([$name, $slug, $id]);
        }
        $message = "Category updated!";
    } else {
        try {
            if ($type === 'blog') {
                $stmt = $pdo->prepare("INSERT INTO $table (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $description]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO $table (name, slug) VALUES (?, ?)");
                $stmt->execute([$name, $slug]);
            }
            $message = "Category added!";
        } catch (PDOException $e) {
            $error = "Slug already exists.";
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: categories.php?type=$type");
    exit();
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing = $stmt->fetch();
}

include 'includes/header.php';
?>

<div style="display: flex; gap: 2rem;">
    <!-- Form Side -->
    <div style="flex: 1; max-width: 400px;">
        <h3><?php echo $editing ? 'Edit' : 'Add New'; ?> Category</h3>
        <div class="table-card" style="padding: 1.5rem; margin-top: 1rem;">
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $editing['id'] ?? ''; ?>">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" value="<?php echo htmlspecialchars($editing['slug'] ?? ''); ?>" placeholder="Auto-generated if blank">
                </div>
                <?php if ($type === 'blog'): ?>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-block"><?php echo $editing ? 'Update' : 'Add New'; ?> Category</button>
                <?php if ($editing): ?>
                    <a href="categories.php?type=<?php echo $type; ?>" style="display: block; text-align: center; margin-top: 1rem; color: #64748b;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- List Side -->
    <div style="flex: 2;">
        <h3>Existing <?php echo $page_title; ?></h3>
        <div class="table-card" style="margin-top: 1rem;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <?php if ($type === 'blog'): ?><th>Description</th><?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $categories = $pdo->query("SELECT * FROM $table ORDER BY name ASC")->fetchAll();
                    foreach ($categories as $cat):
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                        <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                        <?php if ($type === 'blog'): ?><td><?php echo htmlspecialchars(substr($cat['description'] ?? '', 0, 50)); ?></td><?php endif; ?>
                        <td>
                            <a href="?type=<?php echo $type; ?>&edit=<?php echo $cat['id']; ?>" class="btn btn-sm" style="background: #f1f5f9; color: #475569;">Edit</a>
                            <a href="?type=<?php echo $type; ?>&delete=<?php echo $cat['id']; ?>" class="btn btn-sm" style="color: #ef4444;" onclick="return confirm('Delete this category?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
