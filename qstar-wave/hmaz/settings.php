<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_superadmin();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            set_setting($key, $value);
        }
    }
    $message = "Settings updated successfully!";
}

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Site Customization</h2>
    <?php if ($message): ?>
        <div style="background: #DCFCE7; color: #166534; padding: 0.5rem 1rem; border-radius: 8px;"><?php echo $message; ?></div>
    <?php endif; ?>
</div>

<form method="POST">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Branding -->
        <div class="table-card" style="padding: 2rem;">
            <h3>Branding & Identity</h3>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border);">
            
            <div class="form-group">
                <label>Site Logo</label>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                    <img id="site_logo_preview" src="../<?php echo get_setting('site_logo'); ?>" style="height: 40px; background: #f1f5f9; border-radius: 4px; border: 1px solid var(--border); <?php echo !get_setting('site_logo') ? 'display:none;' : ''; ?>">
                    <input type="hidden" name="site_logo" id="site_logo_input" value="<?php echo htmlspecialchars(get_setting('site_logo')); ?>">
                    <button type="button" class="btn btn-sm btn-outline" onclick="openMediaModal('site_logo_input')">Select Logo</button>
                </div>
            </div>

            <div class="form-group">
                <label>Favicon</label>
                <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                    <img id="site_favicon_preview" src="../<?php echo get_setting('site_favicon'); ?>" style="height: 32px; width: 32px; border-radius: 4px; border: 1px solid var(--border); <?php echo !get_setting('site_favicon') ? 'display:none;' : ''; ?>">
                    <input type="hidden" name="site_favicon" id="site_favicon_input" value="<?php echo htmlspecialchars(get_setting('site_favicon')); ?>">
                    <button type="button" class="btn btn-sm btn-outline" onclick="openMediaModal('site_favicon_input')">Select Favicon</button>
                </div>
            </div>

            <div class="form-group">
                <label>Site Theme Color</label>
                <input type="color" name="site_primary_color" value="<?php echo htmlspecialchars(get_setting('site_primary_color', '#007BFF')); ?>" style="height: 45px; cursor: pointer; border: none; background: none; padding: 0;">
            </div>
        </div>

        <!-- Global SEO -->
        <div class="table-card" style="padding: 2rem;">
            <h3>Search Engine Optimization</h3>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border);">
            <div class="form-group">
                <label>Home SEO Title</label>
                <input type="text" name="home_seo_title" value="<?php echo htmlspecialchars(get_setting('home_seo_title')); ?>" placeholder="Qstar Wave | Digital Agency">
            </div>
            <div class="form-group">
                <label>Home Meta Description</label>
                <textarea name="home_seo_desc" rows="3"><?php echo htmlspecialchars(get_setting('home_seo_desc')); ?></textarea>
            </div>
            <div class="form-group">
                <label>Global Footer Text</label>
                <input type="text" name="footer_copyright" value="<?php echo htmlspecialchars(get_setting('footer_copyright')); ?>">
            </div>
        </div>

        <!-- Hero Section -->
        <div class="table-card" style="padding: 2rem;">
            <h3>Hero Section</h3>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border);">
            <div class="form-group">
                <label>Main Title</label>
                <input type="text" name="hero_title" value="<?php echo htmlspecialchars(get_setting('hero_title')); ?>">
            </div>
            <div class="form-group">
                <label>Tagline</label>
                <textarea name="hero_tagline" rows="3"><?php echo htmlspecialchars(get_setting('hero_tagline')); ?></textarea>
            </div>
            <div class="form-group">
                <label>WhatsApp Number</label>
                <input type="text" name="hero_wa_number" value="<?php echo htmlspecialchars(get_setting('hero_wa_number')); ?>">
            </div>
        </div>

        <!-- Content & Social -->
        <div class="table-card" style="padding: 2rem;">
            <h3>Story & Socials</h3>
            <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border);">
            <div class="form-group">
                <label>About Paragraph 1</label>
                <textarea name="founder_story" rows="3"><?php echo htmlspecialchars(get_setting('founder_story')); ?></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>WhatsApp URL</label>
                    <input type="text" name="social_whatsapp" value="<?php echo htmlspecialchars(get_setting('social_whatsapp')); ?>">
                </div>
                <div class="form-group">
                    <label>Instagram URL</label>
                    <input type="text" name="social_instagram" value="<?php echo htmlspecialchars(get_setting('social_instagram')); ?>">
                </div>
                <div class="form-group">
                    <label>LinkedIn URL</label>
                    <input type="text" name="social_linkedin" value="<?php echo htmlspecialchars(get_setting('social_linkedin')); ?>">
                </div>
                <div class="form-group">
                    <label>Facebook URL</label>
                    <input type="text" name="social_facebook" value="<?php echo htmlspecialchars(get_setting('social_facebook')); ?>">
                </div>
            </div>
        </div>

    </div>
    <div style="margin-top: 2rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 1.25rem; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);">Save All Global Settings</button>
    </div>
</form>

<script>
    // Preview watchers
    function setupPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        setInterval(() => {
            if(input.value && (preview.src.indexOf(input.value) === -1)) {
                preview.src = '../' + input.value;
                preview.style.display = 'block';
            }
        }, 500);
    }
    setupPreview('site_logo_input', 'site_logo_preview');
    setupPreview('site_favicon_input', 'site_favicon_preview');
</script>

<?php include 'includes/footer.php'; ?>
