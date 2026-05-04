# QCU Grade Management System - Complete Integration Guide

## System Overview

This document explains the complete grade management system architecture, including how students view their grades, how admins input grades, and how the data is stored and secured.

### Key Features

✅ **Student Grade Viewing** - Students can view their grades by academic year  
✅ **Admin Grade Entry** - Admins can input grades linked to class schedules  
✅ **Referential Integrity** - Grades automatically reference course data from schedules  
✅ **Role-Based Access** - Separate admin and student interfaces  
✅ **Real-Time Updates** - Grade data syncs between admin entry and student viewing  
✅ **Academic Year Filtering** - Organize grades by academic year  

---

## Architecture & Data Flow

### Database Tables

#### 1. `users` Table (Students)
Stores student account information.
```sql
id | email | password | first_name | last_name | section | status | created_at
```

#### 2. `admins` Table
Stores admin account information.
```sql
id | email | password | first_name | last_name | role | created_at
```

#### 3. `class_schedules` Table
Stores class schedule data created by admins.
```sql
id | section | course_code | course_name | units | days | time | room | instructor_name | academic_year | created_at
```

#### 4. `grade_reports` Table
Stores grade data. **Foreign key to both users and class_schedules**.
```sql
id | student_id | schedule_id | grade | numeric_grade | status | academic_year | graded_by | graded_at | remarks
```

---

## API Endpoints

### Grade Management APIs

**Base URL:** `http://localhost/WEBSYS/WEBSYS/php/api/`

#### 1. Get Student's Grades (Read-Only)

**Endpoint:** `GET grades.php?action=get-student-grades`

**Parameters:**
- `student_id` (required) - Student's user ID
- `academic_year` (optional) - Format: "2024-2025"

**Request:**
```
GET /WEBSYS/php/api/grades.php?action=get-student-grades&student_id=1&academic_year=2024-2025
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "schedule_id": 5,
      "grade": "A",
      "numeric_grade": 3.5,
      "status": "Passed",
      "academic_year": "2024-2025",
      "course_code": "CS101",
      "course_name": "Introduction to Computer Science",
      "units": 3,
      "time": "MWF 10:00-11:00",
      "instructor_name": "Dr. Smith",
      "student_name": "John Doe"
    }
  ],
  "count": 1
}
```

**Usage in Frontend:**
```javascript
// In grades.html
async function loadGrades() {
  const academicYear = document.getElementById('yearSelector').value;
  const response = await fetch(
    `/WEBSYS/php/api/grades.php?action=get-student-grades&student_id=1&academic_year=${academicYear}`
  );
  const data = await response.json();
  // Display data in table
}
```

#### 2. Get Schedules for Admin Grade Entry

**Endpoint:** `GET class-schedule.php?action=get&section=SBIT-1A`

**Returns:** All classes for the specified section (used in admin grades interface)

#### 3. Get Students Enrolled in Course

**Endpoint:** `GET grades.php?action=students&schedule_id=5`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "student@qcu.edu.ph",
      "first_name": "John",
      "last_name": "Doe",
      "section": "SBIT-1A"
    }
  ]
}
```

---

## User Workflows

### Student Workflow: View Grades

1. **Navigate to Grades Page**
   ```
   http://localhost/WEBSYS/WEBSYS/dashboard/grades.html
   ```

2. **Select Academic Year**
   - Dropdown shows: 2024-2025, 2023-2024, 2022-2023
   - Selection triggers `loadGrades()` function

3. **View Grade Table**
   - Displays: Course Code, Course Name, Units, Professor, Time, Grade, Numeric Score, Status
   - Shows stat cards: Total Courses, Courses Passed, Average Grade

4. **Data Flow:**
   ```
   grades.html 
   → API: grades.php?action=get-student-grades
   → Database: grade_reports JOIN class_schedules
   → Display: Course data + Student's grades
   ```

### Admin Workflow: Enter Grades (admin-grades.html)

1. **Navigate to Admin Grade Entry**
   ```
   http://localhost/WEBSYS/WEBSYS/dashboard/admin-grades.html
   ```

2. **Select Section and Course**
   - Section dropdown: SBIT-1A through 1F
   - Course dropdown: Auto-populated from schedules for that section
   - Triggers: Load enrolled students for that course

3. **View Enrolled Students**
   - Table shows all students in that course
   - Fields: Student ID, Name, Email, Section

4. **Enter/Update Grade**
   - Numeric Grade: 0-100 or 1.0-5.0 scale
   - Status: Passed, Failed, Incomplete
   - Schedule Reference: Auto-displays course details from schedule

5. **Save Grade**
   - Click "Save Grade" → POST to grades.php
   - Creates/updates grade_reports record
   - Automatically links to class_schedules via schedule_id

6. **Data Flow:**
   ```
   admin-grades.html
   → API: GET schedules (section-based)
   → API: GET enrolled students (course-based)
   → API: POST/PUT grade_reports
   → Database: Stores with foreign key to schedule_id
   ```

---

## Key Implementation Details

### Referential Integrity

**Requirement:** "Admin can input grades but course, units, time, and professor must match schedule"

**How It Works:**
1. Grade record has `schedule_id` (foreign key)
2. Schedule record has `course_code`, `course_name`, `units`, `time`, `instructor_name`
3. When grade is retrieved, it JOINs with class_schedules to get all course details
4. Database constraint ensures `schedule_id` is valid before grade is saved

**SQL:**
```sql
ALTER TABLE grade_reports
ADD CONSTRAINT fk_grading_schedule
FOREIGN KEY (schedule_id) REFERENCES class_schedules(id)
ON DELETE RESTRICT;
```

### Academic Year Management

All grade records include `academic_year` field (format: "2024-2025").

**Frontend Filter:** Year selector on both student and admin pages

**Database Query:** WHERE clause filters by academic_year before displaying data

### Student Enrollment Tracking

Students are matched to schedules via:
1. `student_section` in users table
2. `schedule_section` in class_schedules table
3. Both must match for student to have access to course grades

---

## Setup Instructions

### 1. Import Database

```sql
-- Import qc_portal.sql into your MySQL database
-- Contains: users, admins, class_schedules, grade_reports tables
-- Includes sample data for testing
```

**Via phpMyAdmin:**
1. Open http://localhost/phpmyadmin
2. Click "Import" tab
3. Upload `qc_portal.sql`
4. Execute

### 2. Verify Database Connection

Test file: `php/config/db.php`

```php
// Verify connection details:
$dbname = 'qc_portal';
$host = 'localhost';
$user = 'root';
$password = '';
```

### 3. Test Student Grade Viewing

**Test Account (Student):**
- Email: student@qcu.edu.ph
- Password: student123
- User ID: 1 (hardcoded in grades.html for testing)

**Steps:**
1. Navigate to grades.html
2. Select "2024-2025" from year dropdown
3. Should display grades (if sample data loaded properly)

### 4. Test Admin Grade Entry

**Test Account (Admin):**
- Email: admin@qcu.edu.ph
- Password: admin123

**Steps:**
1. Navigate to admin-grades.html
2. Select section: "SBIT-1A"
3. Select course from dropdown
4. Enter numeric grade (0-100) and status
5. Click "Save Grade"
6. Verify grade appears in student view

---

## File Structure

```
WEBSYS/
├── dashboard/
│   ├── grades.html               ← Student grade viewing interface
│   ├── admin-grades.html         ← Admin grade entry interface
│   ├── SchoolSched.html          ← Student schedule view
│   └── admin-class-schedule.html ← Admin schedule management
├── php/
│   ├── api/
│   │   ├── grades.php            ← Grade API (student view + admin entry)
│   │   └── class-schedule.php    ← Schedule API
│   └── config/
│       └── db.php                ← Database connection
└── sql/
    └── qc_portal.sql             ← Complete database schema + sample data
```

---

## API Response Examples

### Getting Student Grades

**Request:**
```
GET /WEBSYS/php/api/grades.php?action=get-student-grades&student_id=1&academic_year=2024-2025
```

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student_id": 1,
      "schedule_id": 5,
      "grade": "A",
      "numeric_grade": 3.75,
      "status": "Passed",
      "academic_year": "2024-2025",
      "course_code": "MATH101",
      "course_name": "Calculus I",
      "units": 4,
      "time": "MWF 09:00-10:00",
      "instructor_name": "Prof. Johnson",
      "student_name": "John Doe",
      "graded_at": "2024-11-15 14:30:00"
    },
    {
      "id": 2,
      "student_id": 1,
      "schedule_id": 6,
      "grade": "B+",
      "numeric_grade": 3.25,
      "status": "Passed",
      "academic_year": "2024-2025",
      "course_code": "PHYS101",
      "course_name": "Physics I",
      "units": 3,
      "time": "TTh 10:00-11:30",
      "instructor_name": "Dr. Martinez",
      "student_name": "John Doe",
      "graded_at": "2024-11-20 10:15:00"
    }
  ],
  "count": 2
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Student ID is required"
}
```

---

## Troubleshooting

### Issue: Grades not loading in student view

**Diagnosis:**
1. Check browser console for JavaScript errors
2. Verify API endpoint is correct: `/WEBSYS/php/api/grades.php`
3. Confirm `qc_portal` database exists and has tables

**Solution:**
```javascript
// Debug in browser console:
fetch('/WEBSYS/php/api/grades.php?action=get-student-grades&student_id=1&academic_year=2024-2025')
  .then(r => r.json())
  .then(d => console.log(d))
```

### Issue: Admin can't enter grades

**Diagnosis:**
1. Verify schedule was created first (courses must exist)
2. Check that schedule section matches student section
3. Ensure class_schedules table has data

**Solution:**
```sql
-- Check schedules exist:
SELECT * FROM class_schedules WHERE section = 'SBIT-1A';

-- Check students assigned to section:
SELECT * FROM users WHERE section = 'SBIT-1A';
```

### Issue: Grade not appearing after saving

**Diagnosis:**
1. Check that schedule_id was saved correctly
2. Verify student_id and schedule_id are valid foreign keys
3. Confirm academic_year matches filter

**Solution:**
```sql
-- Check saved grade:
SELECT * FROM grade_reports WHERE student_id = 1 ORDER BY graded_at DESC LIMIT 1;
```

---

## Security Considerations

### Admin-Only Grade Entry

Currently, grade entry is restricted via:
1. Separate admin-grades.html interface (client-side only)
2. **TODO:** Server-side admin authentication in grades.php

**Recommendation:** Add session-based admin verification:
```php
// TODO: Add to grades.php for POST/PUT/DELETE operations
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (!isset($_SESSION['is_admin'])) {
        throw new Exception('Admin access required');
    }
}
```

### Student Privacy

- Students can only view their own grades (implement with session user ID)
- Grades API should validate `student_id` matches logged-in user

**Recommendation:** Replace hardcoded `student_id=1` with session:
```javascript
// Get student ID from session/localStorage:
const studentId = parseInt(localStorage.getItem('userId'));
```

---

## Performance Optimization

### Database Indexing

Recommended indices for fast queries:
```sql
CREATE INDEX idx_grades_student ON grade_reports(student_id);
CREATE INDEX idx_grades_schedule ON grade_reports(schedule_id);
CREATE INDEX idx_grades_academic_year ON grade_reports(academic_year);
CREATE INDEX idx_schedule_section ON class_schedules(section);
```

### Query Optimization

Current grade retrieval query uses LEFT JOINs to gracefully handle missing schedule data.

### Pagination

For large grade result sets, consider adding pagination:
```
grades.php?action=get-student-grades&student_id=1&page=1&limit=10
```

---

## Future Enhancements

1. **Login System Integration**
   - Replace hardcoded student/admin IDs with session variables
   - Implement proper authentication on grade APIs

2. **Grade Statistics**
   - Class average, median grades
   - Grade distribution charts

3. **Bulk Operations**
   - Import grades from CSV
   - Batch grade entry for entire class

4. **Audit Trail**
   - Track who graded and when
   - Grade change history

5. **Grade Appeals**
   - System for students to request grade review
   - Admin workflow to handle appeals

---

## Support & Updates

For issues or questions about this system:
1. Check database tables exist: `users`, `admins`, `class_schedules`, `grade_reports`
2. Verify PHP APIs return JSON responses as documented
3. Check browser developer console for client-side errors
4. Review server error logs in XAMPP

Last Updated: 2024
System Version: 1.0 (Beta)
