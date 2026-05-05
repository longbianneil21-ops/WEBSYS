# Student Portal Refactoring - Complete Update Guide

## Summary of Changes

This update completely refactors the student portal to:
1. ✅ Remove Neil Longbian default account
2. ✅ Make all student pages empty by default (events, dashboard, digital-id, grades)
3. ✅ Create admin interfaces to manage all content
4. ✅ Link everything via student-id as the primary key
5. ✅ Use 2025-2026 as the current academic year

---

## 🔄 Database Update Required

### Step 1: Delete Old Database
```sql
DROP DATABASE qc_portal;
```

### Step 2: Import New Database Schema
- File: **qc_portal_updated.sql** (in WEBSYS root)
- Location: `c:\xampp\htdocs\WEBSYS\qc_portal_updated.sql`
- Import via phpMyAdmin: Import tab → Choose file → Execute

### What's Changed in Database

**Removed:**
- Neil Longbian default account
- Hardcoded student data

**Added Tables:**
- `events` - Admin-managed events
- `dashboard_content` - Admin-managed announcements/notices
- `digital_id_content` - Per-student digital ID data

**Updated:**
- All tables now use student `id` as primary foreign key
- Academic year fixed to 2025-2026
- Sample data includes 5 test students (student1-5@qcu.edu.ph)

---

## 📁 File Changes

### Student Pages (Now PHP with Dynamic Loading)

| File | Changes |
|------|---------|
| `dashboard.html` | → Converted to PHP; loads events + dashboard content from API |
| `events.html` | → Converted to PHP; loads events from API |
| `digital-id.html` | → Converted to PHP; loads student's digital ID from API |
| `grades.html` | ✓ Already updated; loads from API |
| `SchoolSched.html` | ✓ Already updated; loads from API |

**Note:** Rename HTML files to PHP when deploying (or keep .html if server configured to run PHP in .html files)

### New Admin Pages (New Files)

| File | Purpose |
|------|---------|
| `admin-events.php` | ✨ NEW - Admin creates/deletes events |
| `admin-dashboard.php` | ✨ NEW - Admin manages dashboard content |
| `admin-digital-ids.php` | ✨ NEW - Admin generates digital IDs for students |
| `admin-class-schedule.html` | ✓ Existing - Admin manages class schedules |
| `admin-grades.html` | ✓ Existing - Admin enters grades |

### APIs (Updated/New)

| File | Type |
|------|------|
| `php/api/content.php` | ✨ NEW - Handles events, dashboard, digital ID |
| `php/api/grades.php` | ✓ Updated - Added get-student-grades endpoint |
| `php/api/class-schedule.php` | ✓ Existing - Schedule management |

---

## 🔑 How Student-ID Linking Works

### Current Flow (2025-2026)

1. **Student Logs In**
   ```
   Email: student1@qcu.edu.ph
   Password: (hashed in database)
   Result: student_id = 1
   ```

2. **Student Visits Dashboard.php**
   ```
   URL: dashboard.php?student_id=1&name=Juan%20Dela%20Cruz&email=student1@qcu.edu.ph
   ```

3. **Dashboard Loads Data**
   ```
   → API: content.php?action=get-events
   → API: content.php?action=get-dashboard
   → Displays admin-created content to all students
   ```

4. **Student Visits Digital-ID.php**
   ```
   → API: content.php?action=get-digital-id&student_id=1
   → Displays student's specific digital ID (if admin created one)
   ```

5. **Student Views Grades.html**
   ```
   → API: grades.php?action=get-student-grades&student_id=1&academic_year=2025-2026
   → Displays only student's grades
   ```

---

## 📊 Academic Year: 2025-2026

All data now uses **2025-2026** as the academic year:

- Class schedules: academic_year = '2025-2026'
- Grades: academic_year = '2025-2026'
- Grade student year selector: Only shows 2025-2026 (no dropdown)

To change academic year in the future:
1. Update `qc_portal_updated.sql` DEFAULT values
2. Update hardcoded year in `grades.html` JavaScript
3. Update class schedule creation defaults

---

## 👨‍💼 Admin Workflows

### Admin: Create Events
1. Navigate to: `admin-events.php`
2. Fill event form (title, description, date, location)
3. Click "Create Event"
4. Event appears on all students' dashboard event list
5. All students see the same events (not per-section)

### Admin: Manage Dashboard Content
1. Navigate to: `admin-dashboard.php`
2. Add announcements/notices/alerts
3. Set display order (determines order on dashboard)
4. Content appears in all students' dashboards
5. Types: announcement, update, notice, alert

### Admin: Generate Digital IDs
1. Navigate to: `admin-digital-ids.php`
2. Enter student ID (1-5 for test accounts)
3. Select student status (Active, etc.)
4. Select academic status (Regular, etc.)
5. System auto-generates ID number (QCU-000001)
6. Student can then view their digital ID card

### Admin: Manage Grades (Existing)
1. Navigate to: `admin-grades.html`
2. Select section → course → student
3. Enter numeric grade + status
4. Grades auto-linked to schedules
5. Students see in grades.html page

---

## 👨‍🎓 Student Workflows

### Student: View Dashboard
1. Login → Redirected to `dashboard.php?student_id=X`
2. Sees: Events list + Dashboard announcements
3. All students see same content (admin-managed)

### Student: View Events
1. Click "Events" in sidebar
2. Navigates to `events.php?student_id=X`
3. Lists all events created by admin
4. Sorted by date (newest first)

### Student: View Digital ID
1. Click "Digital ID" in sidebar
2. Navigates to `digital-id.php?student_id=X`
3. Shows their specific ID card (if admin created one)
4. If not created: "Please contact admin office"

### Student: View Grades
1. Click "Grades" in sidebar
2. Navigates to `grades.html?student_id=X`
3. Select academic year: **Only 2025-2026** (no selector)
4. Displays: Courses, grades, average, stats
5. Data from admin-entered grades

### Student: View Schedule
1. Click "Class Schedule" in sidebar
2. Navigates to `SchoolSched.html`
3. Shows only classes for their section
4. Empty if admin hasn't added classes yet

---

## 🧪 Testing Checklist

### Pre-Flight
- [ ] MySQL database backup created
- [ ] qc_portal_updated.sql reviewed

### Database Import
- [ ] Import qc_portal_updated.sql successfully
- [ ] Database `qc_portal` created
- [ ] 8 tables exist: users, admins, class_schedules, grade_reports, events, dashboard_content, digital_id_content
- [ ] Sample data loaded (5 test students)

### Student Pages (Empty State)
- [ ] `dashboard.php?student_id=1` loads (no hardcoded data)
- [ ] `events.php?student_id=1` loads (empty if no events created)
- [ ] `digital-id.php?student_id=1` shows error (no ID created yet)
- [ ] `grades.html?student_id=1` shows stat cards only (no grades yet)
- [ ] `SchoolSched.html` empty (no schedules created yet)

### Admin: Create Content
- [ ] `admin-events.php` - Create test event → Appears in student dashboard
- [ ] `admin-dashboard.php` - Add test announcement → Appears in all student dashboards
- [ ] `admin-digital-ids.php` - Generate ID for student 1 → Appears in their digital ID page

### Student: View Created Content
- [ ] Student 1 dashboard shows event
- [ ] Student 1 dashboard shows announcement
- [ ] Student 1 digital ID page shows ID card

### Database Integrity
- [ ] Admin account exists (admin@qcu.edu.ph)
- [ ] Student accounts exist (student1-5@qcu.edu.ph)
- [ ] No Neil Longbian references anywhere
- [ ] Foreign keys prevent orphaned records

---

## 🔒 Security Notes

### Current Implementation (Testing Only)
- Student ID passed in URL query string
- No session/authentication implemented yet
- Admin pages accessible without login

### TODO: For Production
```php
// Add session-based authentication:
if (!isset($_SESSION['admin_id']) || !$_SESSION['is_admin']) {
    header('Location: ../landingpage/login.html');
    exit;
}
```

### TODO: Student Page Protection
```javascript
// Validate student_id matches logged-in user:
const loggedInStudentId = parseInt(localStorage.getItem('userId'));
if (STUDENT_ID !== loggedInStudentId) {
    alert('Unauthorized access');
    location.href = '../landingpage/login.html';
}
```

---

## 📝 API Endpoints Reference

### Events API (`content.php`)
```
GET /api/content.php?action=get-events
→ Returns: All events

POST /api/content.php?action=create-event
Body: {title, description, event_date, location, admin_id}
→ Creates event

DELETE /api/content.php?action=delete-event&event_id=1
→ Deletes event
```

### Dashboard Content API
```
GET /api/content.php?action=get-dashboard
→ Returns: All active dashboard content

POST /api/content.php?action=create-dashboard-content
Body: {title, description, content_type, display_order, admin_id}
→ Creates content

DELETE /api/content.php?action=delete-dashboard-content&content_id=1
→ Deletes content
```

### Digital ID API
```
GET /api/content.php?action=get-digital-id&student_id=1
→ Returns: Student's digital ID

POST /api/content.php?action=create-digital-id
Body: {student_id, id_number, student_status, academic_status, admin_id}
→ Creates digital ID (one per student)

PUT /api/content.php?action=update-digital-id
Body: {id, student_status, academic_status}
→ Updates digital ID
```

---

## 🚀 Deployment Steps

1. **Backup current database**
   ```sql
   mysqldump -u root qc_portal > backup_old.sql
   ```

2. **Drop old database**
   ```sql
   DROP DATABASE qc_portal;
   ```

3. **Import new schema**
   - Open phpMyAdmin
   - Import → qc_portal_updated.sql
   - Execute

4. **Deploy updated files**
   - Replace dashboard.html with updated version (or rename to dashboard.php)
   - Replace events.html with updated version (or rename to events.php)
   - Replace digital-id.html with updated version (or rename to digital-id.php)
   - Add new API file: php/api/content.php
   - Add new admin pages: admin-events.php, admin-dashboard.php, admin-digital-ids.php

5. **Update navigation links**
   - All sidebar links updated from .html to .php
   - Verified all internal links work

6. **Test workflow**
   - Create admin account login
   - Create test events/content
   - Navigate student page to verify content loads

---

## ✅ Verification Commands

```sql
-- Verify new tables exist
SHOW TABLES;

-- Verify Neil Longbian removed
SELECT * FROM users WHERE first_name = 'Neil';
-- Should return: Empty result

-- Verify test students exist
SELECT id, email, first_name, last_name FROM users;
-- Should return: 5 students (student1-5@qcu.edu.ph)

-- Verify admin account
SELECT id, email, first_name FROM admins;

-- Verify sample data
SELECT * FROM events;
SELECT * FROM dashboard_content;
```

---

## 🆘 Troubleshooting

### "Table not found" error
**Solution:** Re-import qc_portal_updated.sql file

### Student page shows "Error loading events"
**Solution:** Check php/api/content.php exists and is accessible

### Digital ID shows error for all students
**Solution:** Admin hasn't generated digital IDs yet; use admin-digital-ids.php

### Neil Longbian still showing
**Solution:** Old HTML still cached; hard refresh (Ctrl+Shift+R) or clear browser cache

### Academic year shows wrong year in grades selector
**Solution:** Update JavaScript in grades.html (search for "2024-2025" and replace with "2025-2026")

---

## 📞 Support

All files are ready for deployment. The system now uses:
- ✅ Student-ID as primary key for all linking
- ✅ Academic Year: 2025-2026 (fixed, no selector)
- ✅ Zero hardcoded student data (all from database)
- ✅ Admin control over events, dashboard, and digital IDs
- ✅ Clean separation of student pages (read-only) vs admin pages (CRUD)

Questions? Check the relevant API file or admin page documentation in the code.

Last Updated: May 5, 2026
