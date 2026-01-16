<?php
require_once 'db.php';
require_once 'auth.php';
check_auth();

// Global Role Check
$stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$is_super = $stmt->fetchColumn() === 'superadmin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Qstar Wave Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
    <style>
        :root {
            --primary: #007BFF;
            --sidebar-bg: #1E293B;
            --sidebar-item-hover: #334155;
            --bg: #F1F5F9;
            --border: #E2E8F0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        /* Layout */
        .sidebar {
            width: 240px;
            background: var(--sidebar-bg);
            color: #CBD5E1;
            padding: 1.5rem 0;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            z-index: 100;
        }
        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            padding: 0 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }
        .sidebar-logo span { color: var(--primary); }
        .nav-list { list-style: none; flex-grow: 1; }
        .nav-item { position: relative; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1.5rem;
            color: #94A3B8;
            text-decoration: none;
            transition: 0.2s;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .nav-link:hover, .nav-item.active > .nav-link {
            background: var(--sidebar-item-hover);
            color: white;
        }
        .nav-link i { width: 18px; height: 18px; }
        
        /* Submenu */
        .submenu {
            list-style: none;
            background: rgba(0,0,0,0.2);
            padding: 0.5rem 0;
            display: none;
        }
        .nav-item.active .submenu { display: block; }
        .submenu-link {
            display: block;
            padding: 0.5rem 1.5rem 0.5rem 3.25rem;
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .submenu-link:hover, .submenu-link.active { color: white; }

        .main-content { flex-grow: 1; display: flex; flex-direction: column; width: calc(100% - 240px); }
        .top-bar {
            background: white;
            padding: 0.75rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .page-title { font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #1E293B; }
        
        .content-body { padding: 2rem; flex-grow: 1; }
        
        /* Stats & Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-label { color: #64748B; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #1E293B; font-family: 'Outfit', sans-serif; }

        .table-card { background: white; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .table-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem 1.5rem; background: #F8FAFC; font-size: 0.8rem; font-weight: 600; color: #647489; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: #334155; }
        
        /* Buttons */
        .btn { padding: 0.5rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: 0.2s; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #0069D9; }
        .btn-outline { background: white; border: 1px solid var(--border); color: #475569; }
        .btn-outline:hover { background: #F8FAFC; border-color: #CBD5E1; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .btn-block { width: 100%; justify-content: center; }

        /* Badges */
        .badge { padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-published, .badge-read { background: #DCFCE7; color: #166534; }
        .badge-draft, .badge-unread { background: #F1F5F9; color: #475569; }
        .badge-scheduled { background: #FEF9C3; color: #854D0E; }
        .badge-unread { background: #FEE2E2; color: #991B1B; }

        /* Forms */
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.95rem; outline: none; transition: 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1); }
        textarea { resize: vertical; }

        /* Media Modal */
        #mediaModal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            padding: 2rem;
        }
        .modal-content {
            background: white; max-width: 1000px; height: 100%; margin: 0 auto; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden;
        }
        .modal-header { padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 1.5rem; flex-grow: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
        .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); text-align: right; }
        
        .media-picker-item {
            aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 2px solid transparent; cursor: pointer; position: relative;
        }
        .media-picker-item.selected { border-color: var(--primary); }
        .media-picker-item img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i data-lucide="zap"></i> Qstar <span>Wave</span>
        </div>
        <ul class="nav-list">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'blogs.php' || (isset($_GET['type']) && $_GET['type'] == 'blog')) ? 'active' : ''; ?>">
                <a href="blogs.php" class="nav-link"><i data-lucide="file-text"></i> Posts</a>
                <ul class="submenu">
                    <li><a href="blogs.php" class="submenu-link">All Posts</a></li>
                    <li><a href="blogs.php?action=add" class="submenu-link">Add New</a></li>
                    <li><a href="categories.php?type=blog" class="submenu-link">Categories</a></li>
                </ul>
            </li>

            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'portfolio.php' || (isset($_GET['type']) && $_GET['type'] == 'portfolio')) ? 'active' : ''; ?>">
                <a href="portfolio.php" class="nav-link"><i data-lucide="briefcase"></i> Portfolio</a>
                <ul class="submenu">
                    <li><a href="portfolio.php" class="submenu-link">All Projects</a></li>
                    <li><a href="portfolio.php?action=add" class="submenu-link">Add New</a></li>
                    <li><a href="categories.php?type=portfolio" class="submenu-link">Categories</a></li>
                </ul>
            </li>

            <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php' || (isset($_GET['type']) && $_GET['type'] == 'service')) ? 'active' : ''; ?>">
                <a href="services.php" class="nav-link"><i data-lucide="layers"></i> Services</a>
                <ul class="submenu">
                    <li><a href="services.php" class="submenu-link">All Services</a></li>
                    <li><a href="services.php?action=add" class="submenu-link">Add New</a></li>
                    <li><a href="categories.php?type=service" class="submenu-link">Categories</a></li>
                </ul>
            </li>

            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">
                <a href="media.php" class="nav-link">
                    <i data-lucide="image"></i> Media Library
                </a>
            </li>

            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                <a href="messages.php" class="nav-link">
                    <i data-lucide="mail"></i> Messages
                </a>
            </li>

            <?php if ($is_super): ?>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php" class="nav-link">
                    <i data-lucide="users"></i> Users
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php" class="nav-link">
                    <i data-lucide="settings"></i> Settings
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <div class="user-menu" style="margin-top: auto; padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="logout.php" class="logout-btn" style="color: #94A3B8; text-decoration:none; font-size:0.9rem; display:flex; align-items:center; gap:0.5rem;">
                <i data-lucide="log-out"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Media Selection Modal -->
    <div id="mediaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Select Media</h3>
                <button class="btn" onclick="closeMediaModal()" style="padding:0.25rem;"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body" id="mediaGallery">
                <!-- Media will load here via AJAX -->
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeMediaModal()">Cancel</button>
                <button class="btn btn-primary" id="confirmMediaBtn">Use Selected</button>
            </div>
        </div>
    </div>
    <main class="main-content">
        <header class="top-bar">
            <div class="page-title">
                <?php
                $pages = [
                    'dashboard.php' => 'Dashboard Overview',
                    'blogs.php' => 'Manage Blog Posts',
                    'portfolio.php' => 'Portfolio Projects',
                    'services.php' => 'Our Services',
                    'messages.php' => 'Contact Enquiries',
                    'settings.php' => 'Site Customization',
                    'categories.php' => 'Categories Manager',
                    'media.php' => 'Media Library',
                    'users.php' => 'User Management'
                ];
                echo $pages[basename($_SERVER['PHP_SELF'])] ?? 'Admin Panel';
                ?>
            </div>
            <div class="user-info">
                <span style="font-size: 0.9rem; color: #64748B;">Logged in as <strong>Admin</strong></span>
            </div>
        </header>
        <div class="content-body">

