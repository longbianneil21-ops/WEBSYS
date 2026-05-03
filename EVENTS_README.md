# QCU Events Module - PHP/MySQL Setup Guide

## Overview
This is a complete Events Management module for the QCU Student Portal built with PHP, MySQL (PDO), and vanilla CSS/JavaScript. It includes both admin and student interfaces with a dark navy and gold color scheme.

---

## 📋 Database Setup

### Step 1: Create Database
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Click on **New** to create a new database
3. Enter database name: **`qc_portal`**
4. Collation: **utf8mb4_unicode_ci**
5. Click **Create**

### Step 2: Create Events Table
1. In phpMyAdmin, select the `qc_portal` database
2. Go to **SQL** tab
3. Copy and paste the SQL from `sql/events.sql`
4. Click **Go** to execute

**Alternative (Using MySQL CLI):**
```bash
mysql -u root -p
CREATE DATABASE qc_portal;
USE qc_portal;
-- Paste content from sql/events.sql
```

---

## 🔧 Configuration

### Database Connection
- **File:** `php/config/db.php`
- **Host:** `localhost`
- **Database:** `qc_portal`
- **Username:** `root`
- **Password:** (empty by default in XAMPP)

If your MySQL has a different username/password, update `php/config/db.php`:
```php
$username = 'your_username';
$password = 'your_password';
```

---

## 📁 Project Structure

```
WEBSYS/
├── php/
│   └── config/
│       └── db.php              (Database connection - PDO)
├── admin/
│   └── events.php              (Admin CRUD interface)
├── student/
│   └── events.php              (Student view-only interface)
├── uploads/                    (Event image storage)
├── styles/
│   ├── events-admin-style.css  (Admin styling)
│   └── events-student-style.css(Student styling)
└── sql/
    └── events.sql              (Database schema)
```

---

## 🚀 Running the Application

### Admin Panel
**URL:** `http://localhost/WEBSYS/admin/events.php`

**Features:**
- ✅ Create new events with image upload
- ✅ Edit existing events
- ✅ Delete events (with confirmation modal)
- ✅ View all events in table format
- ✅ Image validation (JPG, PNG, GIF, WEBP - Max 5MB)
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Automatic image cleanup on update/delete

### Student Portal
**URL:** `http://localhost/WEBSYS/student/events.php`

**Features:**
- 📅 View upcoming events in card grid
- 📸 View past events
- 📝 Event details modal
- 🎨 Responsive design
- ➕ Add to calendar functionality

---

## 🎨 Design Specifications

### Color Scheme
- **Primary Navy:** `#1a2a4a`
- **Accent Gold:** `#f0a500`
- **Light Gray:** `#f5f5f5`, `#e9e9e9`

### Typography
- **Font Family:** Poppins (Google Fonts)
- **Sizes:** 12px-32px (responsive)

### Layout
- **Fixed Sidebar:** 250px (admin), 260px (student)
- **Responsive:** Mobile (480px), Tablet (768px), Desktop (1024px+)

---

## 💾 Database Schema

### Events Table
```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    image VARCHAR(255),
    created_by VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_created_by (created_by)
);
```

---

## 🔐 Security Features

1. **PDO Prepared Statements** - Prevents SQL injection
2. **File Upload Validation** - Type and size checking
3. **XSS Prevention** - `htmlspecialchars()` for output
4. **Delete Confirmation** - Modal before deletion
5. **File Cleanup** - Automatic removal of old images

---

## 📝 Sample Test Data

To insert sample events, run this SQL:

```sql
INSERT INTO events (event_date, event_time, location, description, created_by) VALUES
('2026-05-15', '09:00:00', 'Room 301, Engineering Building', 'Midterm Examination for Computer Science 101. Please bring your student ID and required materials.', 'Admin'),
('2026-05-28', '08:00:00', 'QCU Main Quadrangle', 'Annual celebration of QCU\'s founding. Join us for a day of festivities, competitions, and performances.', 'Admin'),
('2026-05-30', '23:59:00', 'Online - Student Portal', 'Final project submission deadline for Web Development course. Submit through the student portal.', 'Admin');
```

---

## 🎯 Form Validation

### Admin Form
- **Event Date:** Required, valid date format
- **Event Time:** Required, valid time format
- **Location:** Required, text field
- **Description:** Required, long text
- **Image:** Optional, max 5MB, allowed formats: JPG, PNG, GIF, WEBP

### Image Upload
- Stored in: `uploads/` directory
- Naming: `timestamp_filename` (prevents conflicts)
- Automatic cleanup on edit/delete

---

## 🐛 Troubleshooting

### "Database Connection Error"
- Check MySQL is running in XAMPP
- Verify database name: `qc_portal`
- Check `php/config/db.php` credentials

### Images Not Uploading
- Check `uploads/` folder permissions (should be writable)
- Verify file size < 5MB
- Check file format (JPG, PNG, GIF, WEBP only)

### Events Not Displaying
- Verify events table exists and has data
- Check browser console for JavaScript errors
- Ensure PHP short tags are enabled in php.ini

### Sidebar Not Fixed
- Clear browser cache (Ctrl+Shift+Delete)
- Check CSS file is loading properly
- Verify no conflicting CSS from other files

---

## 📱 Responsive Breakpoints

- **Desktop:** 1024px and above
- **Tablet:** 768px - 1023px
- **Mobile:** 480px - 767px
- **Small Mobile:** Below 480px

---

## 🔄 File Permissions

Ensure proper permissions for production:

```bash
chmod 755 /path/to/uploads  # Directory writable
chmod 755 /path/to/admin    # Execute PHP
chmod 755 /path/to/student  # Execute PHP
chmod 755 /path/to/php      # Access config
```

---

## 📚 Technologies Used

- **Backend:** PHP 7.4+ (PDO MySQLi)
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Server:** XAMPP/Apache
- **Fonts:** Poppins (Google Fonts)

---

## ✨ Features Summary

### Admin (`admin/events.php`)
- ✅ Full CRUD operations
- ✅ Image upload & validation
- ✅ Responsive data table
- ✅ Delete confirmation modal
- ✅ Edit mode with image preview
- ✅ Auto-formatting of dates/times
- ✅ Event counter badge

### Student (`student/events.php`)
- ✅ Card grid layout (responsive)
- ✅ Upcoming/Past event separation
- ✅ Event detail modal
- ✅ Add to calendar (stub)
- ✅ Image lazy loading support
- ✅ Professional UI with animations

---

## 🎨 Customization

To change colors, update these CSS variables:
- Navy: `#1a2a4a`
- Gold: `#f0a500`

Files to modify:
- `styles/events-admin-style.css`
- `styles/events-student-style.css`

---

## 📞 Support

For issues or questions:
1. Check database connection in phpMyAdmin
2. Verify folder structure and file permissions
3. Review browser console for errors
4. Check PHP error logs in XAMPP

---

**Version:** 1.0  
**Last Updated:** May 2026  
**License:** MIT
