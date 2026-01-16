# Qstar Wave Admin Panel Documentation

The admin panel for Qstar Wave is a custom-built, secure, and dynamic backend designed to manage all content of your agency website.

## 1. Accessing the Backend
- **URL**: `http://yourdomain.com/qstar-wave/hmaz` (or `http://localhost/qstar-wave/hmaz`)
- **Login Credentials**:
  - **Username**: `admin`
  - **Password**: `password`

## 2. Managing Content

### Dashboard
The dashboard provides a quick snapshot of your business activity, including the number of blogs, portfolio items, and unread messages.

### Site Settings
Located under **Site Settings**, you can update:
- **Hero Section**: Title, tagline, and CTA button text.
- **WhatsApp Integration**: Update the global WhatsApp number used for all chat buttons.
- **About/Founder**: Change the founder story, name, and expertise.
- **Social Links**: Update your LinkedIn, Facebook, Instagram, and X (Twitter) profile URLs.

### Blog Management
Create high-quality SEO articles:
- **Featured Image**: Upload an image for each post.
- **Slug**: Custom URL (e.g., `modern-seo-tips`).
- **SEO Optimization**: Set specific meta titles and descriptions for search engines.
- **Status**: Save as "Draft" if you're still working or "Published" to go live.

### Portfolio & Projects
Showcase your results:
- Add projects with titles, short descriptions, and categories.
- Each project supports a display image and an optional external link.

### Services
Modify your service offerings:
- Each service has a dedicated icon (using Lucide Icons).
- **Custom WhatsApp Message**: Define a unique pre-filled message for each service CTA.

### Messages / Leads
All enquiries from the contact form are saved here:
- View respondent details (Name, Email, Phone).
- See which service they are interested in.
- Mark as "Read" to keep your inbox organized.

## 3. Technical Notes
- **Database**: mysql (dbname: `qstar_wave`).
- **Media**: All uploaded images are stored in the `/uploads` folder.
- **Security**: All management pages are protected by a session-based auth check.

*Note: Ensure your Laragon web server and MySQL are running to access the site and admin panel.*
