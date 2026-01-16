<?php
require_once 'hmaz/includes/db.php';

// Fetch Dynamic Content
$hero_title = get_setting('hero_title', 'Results-Driven Digital Solutions');
$hero_tagline = get_setting('hero_tagline', 'Empowering brands with cutting-edge web development, expert SEO, and high-impact content writing to dominate the digital landscape.');
$hero_cta = get_setting('hero_cta_text', 'Chat on WhatsApp');
$hero_wa = get_setting('hero_wa_number', '+923091479392');

$founder_name = get_setting('founder_name', 'Asaad Zubair');
$founder_title = get_setting('founder_title', 'Founder & Strategist');
$founder_story = get_setting('founder_story', 'Qstar Wave was founded by Asaad Zubair...');
$about_2 = get_setting('about_text_2', 'With deep expertise in modern web architectures...');

$facebook = get_setting('social_facebook', '#');
$twitter = get_setting('social_twitter', '#');
$linkedin = get_setting('social_linkedin', '#');
$instagram = get_setting('social_instagram', '#');

// CTA Banner
$cta_title = get_setting('cta_banner_title', 'Ready to scale your digital presence?');
$cta_text = get_setting('cta_banner_text', 'Let\'s discuss your project and create a winning strategy together.');
$cta_btn = get_setting('cta_banner_button', 'Get a Free Quote');

// Theme
$primary_color = get_setting('site_primary_color', '#007BFF');

// Fetch Dynamic Services
$services_db = $pdo->query("SELECT * FROM services ORDER BY created_at ASC")->fetchAll();

// Fetch Dynamic Portfolio
$portfolio_db = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC")->fetchAll();

// Fetch Dynamic Blogs
$blogs_db = $pdo->query("SELECT * FROM blogs WHERE status = 'published' ORDER BY publish_at DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qstar Wave | <?php echo $hero_title; ?></title>
    <meta name="description" content="Professional digital agency specializing in Web Development, SEO, and Content Writing. Get results-driven solutions with Qstar Wave.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: <?php echo $primary_color; ?>;
            --primary-light: <?php echo $primary_color; ?>15;
            --primary-hover: <?php echo $primary_color; ?>ee;
        }
    </style>
</head>
<body>

    <!-- Sticky Header -->
    <header id="header">
        <nav class="container">
            <a href="#" class="logo">
                <img src="assets/logo-icon.png" alt="Qstar Wave Logo" class="logo-img">
                <span class="logo-text">Qstar <span class="thin">Wave</span></span>
            </a>
            <ul class="nav-links">
                <li><a href="#hero">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#portfolio">Portfolio</a></li>
                <li><a href="#blog">Blog</a></li>
                <li><a href="#contact" class="btn btn-primary">Contact Us</a></li>
            </ul>
            <div class="mobile-menu-btn">
                <i data-lucide="menu"></i>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <span class="badge">Innovation & Growth</span>
                <h1><?php echo str_replace('Digital Solutions', '<span class="text-gradient">Digital Solutions</span>', htmlspecialchars($hero_title)); ?></h1>
                <p><?php echo htmlspecialchars($hero_tagline); ?></p>
                <div class="hero-btns">
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hello%20Qstar%20Wave!%20I'm%20interested%20in%20your%20digital%20solutions." class="btn btn-primary btn-lg">
                        <i data-lucide="message-circle"></i> <?php echo htmlspecialchars($hero_cta); ?>
                    </a>
                    <a href="#services" class="btn btn-outline btn-lg">Explore Services</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="counter" data-target="50">0</span>+
                        <span class="stat-label">Projects Done</span>
                    </div>
                    <div class="stat-item">
                        <span class="counter" data-target="30">0</span>+
                        <span class="stat-label">Happy Clients</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="assets/hero-bg.png" alt="Digital Solutions Illustration" class="hero-img">
                    <div class="floating-card SEO">
                        <i data-lucide="trending-up"></i>
                        <span>SEO Growth</span>
                    </div>
                    <div class="floating-card DEV">
                        <i data-lucide="code"></i>
                        <span>Web Dev</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-bg-accent"></div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section-padding">
        <div class="container grid-2">
            <div class="about-image">
                <div class="founder-card">
                    <div class="avatar-placeholder"><?php echo substr($founder_name, 0, 1) . substr($founder_name, strpos($founder_name, ' ')+1, 1); ?></div>
                    <div class="founder-info">
                        <h3><?php echo htmlspecialchars($founder_name); ?></h3>
                        <p><?php echo htmlspecialchars($founder_title); ?></p>
                    </div>
                </div>
                <div class="about-shapes">
                    <div class="shape s1"></div>
                    <div class="shape s2"></div>
                </div>
            </div>
            <div class="about-content">
                <h2 class="section-title">Crafting Digital <span class="text-gradient">Excellence</span></h2>
                <p><?php echo nl2br(htmlspecialchars($founder_story)); ?></p>
                <p><?php echo nl2br(htmlspecialchars($about_2)); ?></p>
                <ul class="feature-list">
                    <li><i data-lucide="check-circle" class="text-primary"></i> Custom & WordPress Development</li>
                    <li><i data-lucide="check-circle" class="text-primary"></i> Technical & Local SEO Mastery</li>
                    <li><i data-lucide="check-circle" class="text-primary"></i> Conversion-Optimized Content</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Our <span class="text-gradient">Core Services</span></h2>
                <p class="section-subtitle">We provide a comprehensive suite of digital services designed to scale your business.</p>
            </div>
            
            <div class="services-grid">
                <?php if (!empty($services_db)): ?>
                    <?php foreach ($services_db as $s): ?>
                    <div class="service-card primary">
                        <div class="service-icon"><i data-lucide="<?php echo $s['icon']; ?>"></i></div>
                        <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                        <p><?php echo htmlspecialchars($s['description']); ?></p>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=<?php echo urlencode($s['wa_message']); ?>" class="btn btn-text">Contact on WhatsApp <i data-lucide="arrow-right"></i></a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback Static Services if DB is empty -->
                    <div class="service-card primary">
                        <div class="service-icon"><i data-lucide="monitor"></i></div>
                        <h3>Website Development</h3>
                        <p>High-performance, responsive websites built with Custom code or WordPress. Scalable, secure, and SEO-ready.</p>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hi!%20I'm%20interested%20in%20Website%20Development%20services." class="btn btn-text">Contact on WhatsApp <i data-lucide="arrow-right"></i></a>
                    </div>
                    <div class="service-card primary">
                        <div class="service-icon"><i data-lucide="search"></i></div>
                        <h3>Search Engine Optimization</h3>
                        <p>Rank higher and get more organic traffic. From technical audits to local SEO and quality backlink building.</p>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hi!%20I'm%20interested%20in%20SEO%20services." class="btn btn-text">Contact on WhatsApp <i data-lucide="arrow-right"></i></a>
                    </div>
                    <div class="service-card primary">
                        <div class="service-icon"><i data-lucide="pen-tool"></i></div>
                        <h3>Content Writing</h3>
                        <p>Compelling SEO articles and professional copy that engages your audience and drives conversions across platforms.</p>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hi!%20I'm%20interested%20in%20Content%20Writing%20services." class="btn btn-text">Contact on WhatsApp <i data-lucide="arrow-right"></i></a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="secondary-services">
                <h4 class="text-center mt-4">More Ways We Can Help</h4>
                <div class="secondary-grid">
                    <div class="mini-card"><i data-lucide="palette"></i> <span>Logo Design</span></div>
                    <div class="mini-card"><i data-lucide="share-2"></i> <span>Social Media Posts</span></div>
                    <div class="mini-card"><i data-lucide="users"></i> <span>SMM</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="portfolio section-padding">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Built with <span class="text-gradient">Precision</span></h2>
                <div class="filter-btns">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="web">Web</button>
                    <button class="filter-btn" data-filter="seo">SEO</button>
                    <button class="filter-btn" data-filter="content">Content</button>
                </div>
            </div>

            <div class="portfolio-grid">
                <?php foreach ($portfolio_db as $p): ?>
                <div class="portfolio-item" data-category="<?php echo strtolower($p['category']); ?>">
                    <div class="portfolio-thumb">
                        <div class="thumb-overlay"><i data-lucide="external-link"></i></div>
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </div>
                    <div class="portfolio-info">
                        <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                        <p><?php echo htmlspecialchars($p['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($portfolio_db)): ?>
                    <!-- Static Fallback Projects -->
                    <div class="portfolio-item" data-category="web">
                        <div class="portfolio-thumb">
                            <div class="thumb-overlay"><i data-lucide="external-link"></i></div>
                            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80" alt="SaaS Dashboard">
                        </div>
                        <div class="portfolio-info"><h4>FinTech Dashboard</h4><p>Custom React Development</p></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="container banner-inner">
            <h3><?php echo htmlspecialchars($cta_title); ?></h3>
            <p><?php echo htmlspecialchars($cta_text); ?></p>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hello!%20I'm%20ready%20to%20scale%20my%20business.%20Let's%20talk!" class="btn btn-white">
                <i data-lucide="phone"></i> <?php echo htmlspecialchars($cta_btn); ?>
            </a>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="blog section-padding bg-light">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">The <span class="text-gradient">Latest Insights</span></h2>
                <a href="#" class="view-all">View All Articles <i data-lucide="chevron-right"></i></a>
            </div>
            <div class="blog-grid">
                <?php foreach ($blogs_db as $b): ?>
                <article class="blog-card">
                    <div class="blog-img">
                        <img src="<?php echo htmlspecialchars($b['featured_image']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
                    </div>
                    <div class="blog-content">
                        <span class="blog-tag"><?php echo htmlspecialchars($b['category']); ?></span>
                        <h4><?php echo htmlspecialchars($b['title']); ?></h4>
                        <p><?php echo substr(strip_tags($b['content']), 0, 100); ?>...</p>
                        <a href="blog-post.php?slug=<?php echo $b['slug']; ?>" class="read-more">Read More</a>
                    </div>
                </article>
                <?php endforeach; ?>
                
                <?php if (empty($blogs_db)): ?>
                    <p style="text-align: center; color: #64748B;">New insights coming soon!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq section-padding">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Frequently Asked <span class="text-gradient">Questions</span></h2>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question"><span>How long does a website project take?</span><i data-lucide="plus"></i></div>
                    <div class="faq-answer"><p>Typically, a standard WordPress site takes 1-2 weeks, while custom development projects can take 3-6 weeks depending on complexity.</p></div>
                </div>
                <div class="faq-item">
                    <div class="faq-question"><span>Do you provide ongoing SEO maintenance?</span><i data-lucide="plus"></i></div>
                    <div class="faq-answer"><p>Yes, SEO is an ongoing process. We offer monthly maintenance packages to ensure your rankings continue to grow and adapt to algorithm changes.</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section-padding bg-light">
        <div class="container grid-2">
            <div class="contact-info">
                <h2 class="section-title">Let's <span class="text-gradient">Connect</span></h2>
                <p>Have a question or a project idea? Reach out to us through any of these channels.</p>
                <div class="contact-methods">
                    <div class="method">
                        <i data-lucide="mail"></i>
                        <div><h5>Email Us</h5><p>contact@qstarwave.com</p></div>
                    </div>
                    <div class="method">
                        <i data-lucide="phone"></i>
                        <div><h5>Call / WhatsApp</h5><p><?php echo htmlspecialchars($hero_wa); ?></p></div>
                    </div>
                </div>
            </div>
            <div class="contact-form-wrapper">
                <form id="quote-form-api">
                    <div id="form-message"></div>
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
                    <div class="form-group">
                        <label>Interested Service</label>
                        <select name="service">
                            <option value="web">Web Development</option>
                            <option value="seo">SEO Optimization</option>
                            <option value="content">Content Writing</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Message</label><textarea name="message" rows="4"></textarea></div>
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="#" class="logo">
                    <img src="assets/logo-icon.png" alt="Qstar Wave Logo" class="logo-img">
                    <span class="logo-text">Qstar <span class="thin">Wave</span></span>
                </a>
                <p><?php echo htmlspecialchars(get_setting('footer_text', 'Transforming complex digital challenges...')); ?></p>
                <div class="social-links">
                    <a href="<?php echo $facebook; ?>"><i data-lucide="facebook"></i></a>
                    <a href="<?php echo $twitter; ?>"><i data-lucide="twitter"></i></a>
                    <a href="<?php echo $linkedin; ?>"><i data-lucide="linkedin"></i></a>
                    <a href="<?php echo $instagram; ?>"><i data-lucide="instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2026 Qstar Wave. All Rights Reserved. Founded by <?php echo htmlspecialchars($founder_name); ?>.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp -->
    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $hero_wa); ?>?text=Hello%20Qstar%20Wave!..." class="floating-wa" id="wa-button">
        <i data-lucide="message-circle"></i>
        <span class="wa-tooltip">Chat with us!</span>
    </a>

    <script src="script.js"></script>
    <script>
        // Contact Form Handling
        document.getElementById('quote-form-api').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('button');
            const messageDiv = document.getElementById('form-message');
            
            btn.disabled = true;
            btn.innerText = 'Sending...';
            
            const formData = new FormData(form);
            
            fetch('api/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                messageDiv.innerHTML = `<div style="padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: ${data.status === 'success' ? '#DCFCE7' : '#FEE2E2'}; color: ${data.status === 'success' ? '#166534' : '#991B1B'};">${data.message}</div>`;
                if(data.status === 'success') form.reset();
            })
            .catch(() => {
                messageDiv.innerHTML = '<div style="padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: #FEE2E2; color: #991B1B;">Something went wrong.</div>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Send Message';
            });
        });
    </script>
</body>
</html>
