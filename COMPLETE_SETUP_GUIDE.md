# QCU Student Portal - Complete Setup Guide

## 📋 Overview

A comprehensive web-based student portal built with PHP, MySQL, and vanilla CSS/JavaScript. Features include:
- User authentication (Admin & Student roles)
- Events management with image uploads
- Grade tracking and reporting
- Class schedule management
- Student profile with Digital ID placeholder
- Full CRUD operations for admins
- Student view-only interfaces

---

## 🚀 Quick Start

### 1. Database Setup

**Option A: Using phpMyAdmin (Recommended)**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database: `qc_portal`
3. Import SQL files in order:
   - [sql/events.sql](sql/events.sql) - Events table
   - [sql/complete_schema.sql](sql/complete_schema.sql) - Users, grades, schedules, digital IDs

**Option B: Using MySQL CLI**
```bash
mysql -u root -p
CREATE DATABASE qc_portal;
USE qc_portal;
SOURCE /path/to/complete_schema.sql;
EXIT;
```

### 2. Configuration
- Database connection: [php/config/db.php](php/config/db.php)
  - Host: `localhost`
  - Database: `qc_portal`
  - Username: `root`
  - Password: (empty by default in XAMPP)

### 3. File Structure
```
WEBSYS/
├── auth/
│   ├── login.php              (Login page)
│   └── logout.php             (Logout handler)
├── admin/
│   ├── dashboard.php          (Admin dashboard)
│   ├── users.php              (User management CRUD)
│   ├── events.php             (Event management CRUD)
│   ├── grades.php             (Grade management CRUD)
│   └── schedules.php          (Schedule management CRUD)
├── student/
│   ├── events.php             (Event viewing - read-only)
│   ├── grades.php             (Grade viewing - read-only)
│   ├── schedules.php          (Schedule viewing - read-only)
│   └── profile.php            (Student profile with Digital ID)
├── dashboard/
│   └── dashboard.php          (Role-based dashboard)
├── php/
│   ├── config/
│   │   └── db.php             (PDO database connection)
│   └── auth.php               (Authentication helpers)
├── styles/
│   ├── admin-dashboard-style.css
│   ├── admin-crud-style.css
│   ├── events-admin-style.css
│   ├── events-student-style.css
│   └── student-view-style.css
├── uploads/                   (Event images)
└── sql/
    ├── events.sql
    └── complete_schema.sql
```

---

## 🔐 Authentication

### Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Student | neil.longbian | student123 |

### How It Works
1. User submits login form with username/email + password
2. System validates against database using PDO prepared statements
3. Password verified using `password_verify()` (bcrypt)
4. Session created with user role and ID
5. User redirected based on role:
   - Admin → `/admin/dashboard.php`
   - Student → `/dashboard/dashboard.php`

### Protected Pages
- All admin pages require `requireAdmin()` check
- All student pages require `requireLogin()` check
- Non-authenticated users redirected to login

---

## 📊 Database Schema

### Users Table
```sql
- id (PK)
- username, email, password (bcrypt)
- first_name, last_name
- role (admin/student)
- student_id (for students)
- phone, gender, birth_date
- year_level, program, address
- is_active
- timestamps
```

### Grades Table
```sql
- id (PK)
- student_id (FK)
- course_code, course_name
- semester
- units, schedule
- grade_letter, numeric_grade
- status, professor_name
- timestamps
```

### Schedules Table
```sql
- id (PK)
- student_id (FK)
- course_code, course_name, section
- units, day_of_week
- start_time, end_time
- room_location, professor_name
- semester
- timestamps
```

### Events Table
```sql
- id (PK)
- event_date, event_time
- location, description
- image
- created_by
- timestamps
```

### Digital IDs Table
```sql
- id (PK)
- student_id (FK, unique)
- qr_code_data
- valid_from, valid_until
- status
- timestamps
```

---

## 🎨 Design System

### Color Scheme
- **Primary Navy:** `#1a2a4a`
- **Accent Gold:** `#f0a500`
- **Light Gray:** `#f5f5f5`, `#e9e9e9`
- **Success Green:** `#2ecc71`
- **Error Red:** `#e74c3c`

### Typography
- **Font:** Poppins (Google Fonts)
- **Sizes:** 12px to 32px (responsive)
- **Weight:** 400, 500, 600, 700

### Layout
- **Fixed Sidebar:** 250px width
- **No Framework:** Vanilla CSS Grid & Flexbox
- **Responsive:** Mobile (480px), Tablet (768px), Desktop (1024px+)

---

## 🔄 Admin Features

### User Management
- ✅ Create users (admin/student with auto-generated passwords)
- ✅ Edit user details (with password reset option)
- ✅ Delete users (cannot delete self)
- ✅ Activate/deactivate accounts
- ✅ View all users with role badges

### Events Management
- ✅ Full CRUD operations
- ✅ Image upload (JPG, PNG, GIF, WEBP - Max 5MB)
- ✅ Automatic image cleanup on delete/update
- ✅ Delete confirmation modal
- ✅ Responsive table view

### Grades Management
- ✅ Add grades for students
- ✅ Edit existing grades
- ✅ Delete grade records
- ✅ Grade scale reference
- ✅ Semester-based organization

### Schedules Management
- ✅ Create class schedules
- ✅ Edit schedule details
- ✅ Delete schedules
- ✅ Support for all weekdays
- ✅ Time-based sorting

### Dashboard
- ✅ System statistics (counts)
- ✅ Quick action cards
- ✅ Recent events preview
- ✅ System information

---

## 👥 Student Features

### Grade Viewing
- 📊 View personal grades by semester
- 📈 GPA calculation
- 📋 Grade breakdown by courses
- 🎓 Grading scale reference

### Schedule Viewing
- 📅 Weekly calendar view
- ⏰ Time-organized schedules
- 📍 Room locations
- 👨‍🏫 Professor information

### Profile Management
- 👤 View personal information
- 🎓 Academic details
- 📱 Contact information
- 🪪 Digital ID placeholder

### Events Access
- 📅 View upcoming events
- 📸 View past events
- 📝 Event details modal
- ➕ Add to calendar (stub)

---

## 🔒 Security Features

1. **SQL Injection Prevention**
   - PDO prepared statements on all queries
   - Parameter binding for all user inputs

2. **Password Security**
   - bcrypt hashing with `password_hash()`
   - Verification with `password_verify()`

3. **XSS Prevention**
   - `htmlspecialchars()` on all output
   - Proper escaping in HTML context

4. **Session Management**
   - Session-based authentication
   - User role verification on each request
   - Logout destroys session completely

5. **File Upload Security**
   - File type validation (MIME checking)
   - File size limits (5MB max)
   - Random filename generation
   - Stored outside web root (uploads/)

6. **Delete Confirmation**
   - Modal confirmation before deletion
   - CSRF-like protection with form validation

---

## 📱 Responsive Breakpoints

| Device | Breakpoint | Layout |
|--------|-----------|--------|
| Desktop | 1024px+ | Multi-column, full sidebar |
| Tablet | 768px-1023px | Adjusted columns, fixed sidebar |
| Mobile | 480px-767px | Single column, collapsible sidebar |
| Small Mobile | <480px | Full width, stacked elements |

---

## ⚙️ Configuration & Customization

### Change Database Credentials
Edit [php/config/db.php](php/config/db.php):
```php
$host = 'localhost';
$dbname = 'qc_portal';
$username = 'root';
$password = '';
```

### Change Color Scheme
Update colors in CSS files:
```css
/* Primary Navy */
#1a2a4a

/* Accent Gold */
#f0a500

/* Search and replace in:
- styles/admin-*.css
- styles/student-*.css
- styles/events-*.css
*/
```

### Add New User Roles
1. Update user table schema
2. Modify `auth.php` role checking functions
3. Create role-specific templates/views

### Customize Grading Scale
Edit in [student/grades.php](student/grades.php) grading reference section

---

## 🐛 Troubleshooting

### "Database Connection Error"
- ✓ Check MySQL is running in XAMPP
- ✓ Verify database name: `qc_portal`
- ✓ Check credentials in [php/config/db.php](php/config/db.php)

### Login Not Working
- ✓ Check demo credentials (admin/admin123 or neil.longbian/student123)
- ✓ Verify users table has data
- ✓ Check browser console for JavaScript errors

### Images Not Uploading
- ✓ Check `uploads/` folder exists and is writable (chmod 755)
- ✓ Verify file size < 5MB
- ✓ Check file format (JPG, PNG, GIF, WEBP only)

### Schedules Not Displaying Correctly
- ✓ Ensure `day_of_week` contains exact names (Monday-Sunday)
- ✓ Verify time format is HH:MM:SS
- ✓ Check student_id foreign key is valid

### CSS Not Loading
- ✓ Clear browser cache (Ctrl+Shift+Delete)
- ✓ Check file paths are correct and relative
- ✓ Verify no JavaScript console errors

### Session Issues
- ✓ Check PHP session.save_path is writable
- ✓ Verify cookies are enabled
- ✓ Check session timeout in php.ini

---

## 📚 File Permissions

For production deployment, ensure proper permissions:

```bash
# Directories
chmod 755 /uploads
chmod 755 /admin
chmod 755 /student
chmod 755 /dashboard
chmod 755 /php
chmod 755 /auth

# Files (if needed)
chmod 644 /php/config/db.php
chmod 644 /*.css
chmod 644 /*.js
```

---

## 🚀 Performance Tips

1. **Database Optimization**
   - Indexes on frequently queried columns
   - Use LIMIT for large result sets
   - Prepare statements for repeated queries

2. **Caching**
   - Browser cache CSS/JS files
   - Cache computed statistics

3. **Image Optimization**
   - Resize images on upload
   - Use WebP format where possible
   - Implement lazy loading

4. **Code Optimization**
   - Minimize CSS/JS files
   - Use CDN for external fonts
   - Implement gzip compression

---

## 📞 Support & Maintenance

### Regular Maintenance
- ✅ Backup database weekly
- ✅ Monitor uploads/ folder size
- ✅ Review error logs monthly
- ✅ Update user passwords periodically

### Common Issues Checklist
- [ ] Database running
- [ ] Database credentials correct
- [ ] File permissions set
- [ ] Uploads folder writable
- [ ] Session save path writable
- [ ] PHP short tags enabled
- [ ] Display errors in php.ini

### Admin Utilities
- Create new admin: Use Users page CRUD
- Reset password: Edit user, check "Change Password"
- Backup: Use phpMyAdmin Export feature
- Restore: Use phpMyAdmin Import feature

---

## 📋 Feature Roadmap

### Future Enhancements
- [ ] Generate actual QR codes for Digital IDs
- [ ] Email notifications for events
- [ ] Student enrollment system
- [ ] Transcript generation (PDF)
- [ ] Payment integration
- [ ] Two-factor authentication
- [ ] Activity logging
- [ ] Advanced reporting

---

## 🎓 Technologies Used

- **Backend:** PHP 7.4+ (PDO)
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Server:** Apache/XAMPP
- **Fonts:** Poppins (Google Fonts)
- **Icons:** Emojis (Unicode)

---

## 📄 License

This project is part of the QCU Student Portal system.

---

## 👨‍💼 Admin Guide

### First Time Setup
1. Run complete SQL schema
2. Add demo admin user (already included)
3. Add student records in Users page
4. Create grades and schedules for students
5. Add events

### Weekly Tasks
- Review new user registrations
- Add weekly/monthly events
- Update student grades
- Monitor system logs

### Semester Tasks
- Update grading scales if needed
- Reset schedules for new semester
- Archive old grades
- Update student year levels

---

## 🎯 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | May 2026 | Initial release with all core features |

---

**Last Updated:** May 3, 2026  
**Status:** Active  
**Support Email:** support@qcu.edu.ph
