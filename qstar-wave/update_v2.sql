-- Database Enhancements for Qstar Wave Admin v2

-- 1. Media Library Table
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    filetype VARCHAR(50),
    filesize INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Update Admins Table for Roles
ALTER TABLE admins ADD COLUMN role ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin';
UPDATE admins SET role = 'superadmin' WHERE username = 'admin';

-- 3. Additional Settings for CTA Banners and Branding
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('cta_banner_title', 'Ready to scale your digital presence?'),
('cta_banner_text', 'Let\'s discuss your project and create a winning strategy together.'),
('cta_banner_button', 'Get a Free Quote'),
('site_primary_color', '#007BFF'),
('site_dark_mode', 'off');
