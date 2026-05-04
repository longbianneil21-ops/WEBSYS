# QCU Portal - Complete Database Setup Guide

## 📋 Overview

Complete MySQL database with:
- ✅ Separate Admin & Student Accounts
- ✅ Class Schedules Management
- ✅ Grade Reports (Synced from Schedules)
- ✅ Enrollment Tracking
- ✅ Admin Activity Logging

---

## 🚀 Quick Setup

### Step 1: Import Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Select file: `qc_portal.sql` (from `/WEBSYS/` folder)
4. Click **Go**

**Expected Result**: Database `qc_portal` created with all tables

### Step 2: Verify Database
```sql
USE qc_portal;
SHOW TABLES;
```

Should display:
- `admin_accounts`
- `student_accounts`
- `class_schedules`
- `grade_reports`
- `enrollments`
- `admin_activity_log`

---

## 📊 Database Schema

### 1. ADMIN ACCOUNTS
```sql
admin_accounts (
  id,
  username (UNIQUE),
  email (UNIQUE),
  password (hashed),
  full_name,
  role: 'super_admin' | 'admin' | 'registrar',
  status: 'active' | 'inactive'
)
```

**Sample Admins**:
- `admin` / `admin@qcu.edu.ph` - Super Admin
- `registrar` / `registrar@qcu.edu.ph` - Registrar
- `gradesman` / `grades@qcu.edu.ph` - Grades Admin

### 2. STUDENT ACCOUNTS
```sql
student_accounts (
  id,
  student_id (UNIQUE): '2024-00001',
  first_name, last_name,
  email (UNIQUE),
  password (hashed),
  section: 'SBIT-1A' | 'SBIT-1B' | ... 'SBIT-1F',
  year_level: '1st Year' | '2nd Year' | '3rd Year' | '4th Year',
  program,
  enrollment_status: 'active' | 'inactive' | 'suspended'
)
```

**Sample Students**: 3 students in SBIT-1A (Neil, Maria, Juan)

### 3. CLASS SCHEDULES
```sql
class_schedules (
  id,
  section: 'SBIT-1A' (matches student section),
  course_code: 'CS 101',
  course_name: 'Introduction to Computer Science',
  course_section: 'A',
  units: 3,
  days: 'Monday, Wednesday, Friday',
  time: '9:00 AM - 10:30 AM',
  room: 'Room 301, Engineering Bldg',
  instructor_name: 'Dr. Maria Santos',
  instructor_email: 'maria.santos@qcu.edu.ph',
  semester: '1st Semester',
  academic_year: '2024-2025'
)
```

**Key Feature**: All schedule fields auto-sync to grade_reports

### 4. GRADE REPORTS (★ Core Table)
```sql
grade_reports (
  id,
  student_id → student_accounts(id),
  schedule_id → class_schedules(id),
  
  -- Auto-synced from schedule (read-only)
  course_code,
  course_name,
  course_section,
  units,
  instructor_name,
  time,
  
  -- Admin input fields
  grade: 'A', 'B', 'C', 'D', 'F',
  numeric_grade: 1.0 - 5.0,
  status: 'Passed' | 'Failed' | 'Incomplete' | 'Withdrawn',
  remarks: text,
  
  -- Metadata
  graded_by → admin_accounts(id),
  graded_at: timestamp,
  semester, academic_year
)
```

**Unique Constraint**: (student_id, schedule_id) - One grade per student per course

### 5. ENROLLMENTS
```sql
enrollments (
  id,
  student_id → student_accounts(id),
  schedule_id → class_schedules(id),
  section,
  enrollment_status: 'enrolled' | 'dropped' | 'completed'
)
```

### 6. ADMIN ACTIVITY LOG
```sql
admin_activity_log (
  id,
  admin_id → admin_accounts(id),
  action: string (e.g., 'create', 'update', 'delete'),
  table_name: string,
  record_id: int,
  description: text,
  ip_address,
  created_at
)
```

---

## 🔄 Grade Report Workflow

```
1. Admin creates/updates CLASS SCHEDULE
   ├─ Course Code, Course Name, Units
   ├─ Days, Time, Room
   └─ Professor Name, Email

2. GRADE REPORTS auto-populate from schedule
   ├─ (course_code, units, time, professor synced)
   └─ Unique entry per student per course

3. Admin inputs GRADES ONLY
   ├─ Letter Grade (A, B, C, D, F)
   ├─ Numeric Grade (1.0 - 5.0)
   ├─ Status (Passed/Failed/Incomplete/Withdrawn)
   └─ Optional Remarks

4. Student views grades on portal
   └─ All synced schedule info visible
```

---

## 📡 API Endpoints

### Class Schedule API
```
GET  /php/api/class-schedule.php?action=get&section=SBIT-1A
POST /php/api/class-schedule.php?action=add
PUT  /php/api/class-schedule.php?action=update
DELETE /php/api/class-schedule.php?action=delete
GET  /php/api/class-schedule.php?action=init
```

### Grade Management API
**File**: `/php/api/grades.php`

```
GET  /php/api/grades.php?action=get-grades&academic_year=2024-2025
GET  /php/api/grades.php?action=get-grades&academic_year=2024-2025&status=Passed
GET  /php/api/grades.php?action=get-grade&grade_id=1
GET  /php/api/grades.php?action=get-student-grades&student_id=1&academic_year=2024-2025
PUT  /php/api/grades.php?action=update-grade
POST /php/api/grades.php?action=batch-update-grades
GET  /php/api/grades.php?action=grade-stats&academic_year=2024-2025
```

---

## 🖥️ Admin Interfaces

### 1. Class Schedule Management
**File**: `dashboard/admin-class-schedule.html`
- Select section
- Add/Edit/Delete courses
- View all classes for section

### 2. Grade Management
**File**: `dashboard/admin-grades.html`
- Filter by academic year & status
- View statistics (total, graded, ungraded, passed, failed)
- Edit individual grades via modal
- Displays synced schedule info (read-only)

---

## 👨‍🎓 Student Interfaces

### 1. Class Schedule
**File**: `dashboard/SchoolSched.html`
- View enrolled courses
- Shows units, time, room, professor (from schedule)

### 2. Grades (Create as needed)
**File**: `dashboard/grades.html` (to be created)
- View grades for all courses
- Shows same info as admin (course, units, time, professor)
- Plus: grade, numeric, status

---

## 🔐 Security Features

✅ **Prepared Statements** - SQL injection prevention  
✅ **Foreign Keys** - Data integrity  
✅ **Unique Constraints** - Prevent duplicates  
✅ **Indexes** - Query optimization  
✅ **Role-Based Access** - Admin roles (super_admin, admin, registrar)  
✅ **Activity Logging** - Track admin operations  
✅ **Password Hashing** - (Use bcrypt in PHP)

---

## 📋 Sample Data

**3 Students** in SBIT-1A:
1. Neil Longbian (2024-00001)
2. Maria Santos (2024-00002)
3. Juan Dela Rosa (2024-00003)

**4 Courses** for SBIT-1A:
1. CS 101 - Introduction to Computer Science (Dr. Maria Santos)
2. MATH 101 - Calculus I (Prof. Juan Dela Cruz)
3. ENG 101 - English Composition (Dr. Ana Reyes)
4. PHYS 101 - Physics for Engineers (Prof. Roberto Garcia)

**12 Grade Reports** (1 per student per course) - Empty, ready for grading

---

## 🔗 Relationships

```
admin_accounts ←
                ├→ grade_reports (graded_by)
                └→ admin_activity_log
                
student_accounts ←
                 ├→ enrollments
                 ├→ grade_reports
                 └→ admin_activity_log
                 
class_schedules ←
                ├→ enrollments
                └→ grade_reports (synced fields)
```

---

## 🎯 Key Features

### ✨ Auto-Sync from Schedule
When admin updates schedule:
- Course Code → Grade Report
- Course Name → Grade Report
- Units → Grade Report
- Instructor Name → Grade Report
- Time → Grade Report

### 📊 Grade Statistics
- Total grades
- Graded count
- Ungraded count
- Passed count
- Failed count
- Average grade

### 🔍 Filtering & Searching
- By academic year
- By student
- By status (Passed/Failed/Incomplete)
- By grading status (Graded/Ungraded)

### 📝 Audit Trail
- Admin activity logged
- Who graded, when
- Track all changes

---

## 🆙 Upgrading/Extending

### Add More Sections
```sql
-- Add students to SBIT-1B
INSERT INTO student_accounts (student_id, first_name, last_name, email, password, section, year_level, enrollment_status)
VALUES ('2024-00004', 'John', 'Doe', 'john.doe@qcu.edu.ph', 'hashed_pwd', 'SBIT-1B', '1st Year', 'active');
```

### Add More Courses
```sql
-- Add course to SBIT-1A
INSERT INTO class_schedules (section, course_code, course_name, course_section, units, days, time, room, instructor_name, semester, academic_year)
VALUES ('SBIT-1A', 'CS 102', 'Data Structures', 'A', 3, 'Tuesday, Thursday', '...', 'Room 302', 'Dr. Name', '1st Semester', '2024-2025');
```

### Create Enrollments for New Courses
```sql
-- Auto-enroll all SBIT-1A students
INSERT INTO enrollments (student_id, schedule_id, section)
SELECT sa.id, cs.id, 'SBIT-1A'
FROM student_accounts sa, class_schedules cs
WHERE sa.section = 'SBIT-1A' AND cs.section = 'SBIT-1A' AND cs.course_code = 'CS 102';

-- Create grade reports
CALL create_grade_for_enrollment(1, 5);  -- student_id=1, schedule_id=5
```

---

## 🐛 Troubleshooting

**Q: Grades not syncing with schedule changes?**  
A: Call the stored procedure:
```sql
CALL sync_schedule_to_grades(1);  -- where 1 is schedule_id
```

**Q: Unique constraint error when adding grade?**  
A: Student already has a grade for this course. Use UPDATE instead of INSERT.

**Q: Foreign key constraint error?**  
A: Course schedule must exist before creating grade report.

---

## 📞 Support

**Files Location**:
- Database: `WEBSYS/qc_portal.sql`
- Class API: `WEBSYS/php/api/class-schedule.php`
- Grades API: `WEBSYS/php/api/grades.php`
- Admin Grades: `WEBSYS/dashboard/admin-grades.html`
- Admin Schedule: `WEBSYS/dashboard/admin-class-schedule.html`

**Database**: `qc_portal` on localhost (MySQL)
**Connection**: Check `WEBSYS/php/config/db.php`
