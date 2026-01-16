-- WP-Admin Style Categories and Enhancements

-- 1. Blog Categories
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Portfolio Categories
CREATE TABLE IF NOT EXISTS portfolio_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Service Categories
CREATE TABLE IF NOT EXISTS service_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Linking existing tables to categories
ALTER TABLE blogs ADD COLUMN category_id INT;
ALTER TABLE portfolio ADD COLUMN category_id INT;
ALTER TABLE services ADD COLUMN category_id INT;

-- 5. Foreign Key relations (Optional but good for integrity)
-- Note: Skipping strict FKs for now to avoid issues with existing data, but will handle logic in PHP.

-- 6. Insert Default Categories
INSERT IGNORE INTO blog_categories (name, slug) VALUES ('News', 'news'), ('SEO Tips', 'seo-tips'), ('Web Trends', 'web-trends');
INSERT IGNORE INTO portfolio_categories (name, slug) VALUES ('Web Development', 'web-dev'), ('SEO', 'seo'), ('Content Writing', 'content');
INSERT IGNORE INTO service_categories (name, slug) VALUES ('Digital Marketing', 'digital-marketing'), ('Development', 'development');

-- 7. Add SEO global settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('site_logo', ''),
('site_favicon', ''),
('home_seo_title', 'Qstar Wave | Results-Driven Digital agency'),
('home_seo_desc', 'Premium Digital agency providing Web Development, SEO and Content Writing services.'),
('footer_copyright', '© 2026 Qstar Wave. All Rights Reserved.');
