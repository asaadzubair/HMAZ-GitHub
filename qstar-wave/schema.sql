CREATE DATABASE IF NOT EXISTS qstar_wave;
USE qstar_wave;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings Table (KeyValue)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

-- Blogs Table
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    featured_image VARCHAR(255),
    content TEXT,
    category VARCHAR(100),
    tags VARCHAR(255),
    seo_title VARCHAR(255),
    seo_desc TEXT,
    status ENUM('draft', 'published', 'scheduled') DEFAULT 'published',
    publish_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Portfolio Table
CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    category VARCHAR(100),
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    wa_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages Table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    service_selected VARCHAR(100),
    message TEXT,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Data
INSERT IGNORE INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Default Settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('hero_title', 'Results-Driven Digital Solutions'),
('hero_tagline', 'Empowering brands with cutting-edge web development, expert SEO, and high-impact content writing to dominate the digital landscape.'),
('hero_cta_text', 'Chat on WhatsApp'),
('hero_wa_number', '+923091479392'),
('founder_name', 'Asaad Zubair'),
('founder_title', 'Founder & Strategist'),
('founder_story', 'Qstar Wave was founded by Asaad Zubair with a simple mission: to bridge the gap between businesses and their digital potential. We aren"t just a service provider; we are your strategic partner in growth.'),
('about_text_2', 'With deep expertise in modern web architectures, data-driven SEO strategies, and persuasive content writing, we help both local Pakistani and international clients build a presence that converts visitors into loyal customers.'),
('social_whatsapp', 'https://wa.me/923091479392'),
('social_linkedin', '#'),
('social_instagram', '#'),
('social_facebook', '#'),
('social_twitter', '#'),
('footer_text', 'Transforming complex digital challenges into simple, elegant, and results-driven solutions.');
