# Class Schedule PHP Backend Setup Guide

## ✅ What's Been Set Up:

### 1. **Database & Backend**
- Created `php/api/class-schedule.php` - REST API for CRUD operations
- Database table: `class_schedules` with fields for:
  - section (SBIT-1A through SBIT-1F)
  - course_code, course_name, course_section
  - units, days, time, room, instructor

### 2. **Admin Interface**
- **File**: `dashboard/admin-class-schedule.html`
- **Features**:
  - Section selector (SBIT-1A through SBIT-1F)
  - Add new classes with complete information
  - Edit existing classes
  - Delete classes
  - Real-time table updates from database

### 3. **Student View**
- **File**: `dashboard/SchoolSched.html`
- **Features**:
  - Displays classes for user's section
  - Shows total units and course count
  - Fetches data from database (updated by admin)

---

## 🚀 Setup Steps:

### Step 1: Initialize Database
1. Start XAMPP (Apache + MySQL)
2. Open browser and visit:
   ```
   http://localhost/WEBSYS/php/api/class-schedule.php?action=init
   ```
   You should see: `{"success":true,"message":"Database initialized"}`

### Step 2: Set User Section (for Testing)
Open browser console (F12) and run:
```javascript
localStorage.setItem('userSection', 'SBIT-1A');
```

### Step 3: Add Classes (Admin)
1. Go to: `http://localhost/WEBSYS/dashboard/admin-class-schedule.html`
2. Select section (SBIT-1A, 1B, etc.)
3. Fill in course details and click "Save Class"
4. Classes appear in table below

### Step 4: View Classes (Student)
1. Go to: `http://localhost/WEBSYS/dashboard/SchoolSched.html`
2. You'll see classes for the section set in localStorage
3. Stats auto-calculate (total units, course count)

---

## 📚 API Endpoints:

### Initialize Database
```
GET /php/api/class-schedule.php?action=init
```

### Get Classes by Section
```
GET /php/api/class-schedule.php?action=get&section=SBIT-1A
```

### Add Class
```
POST /php/api/class-schedule.php?action=add
Content-Type: application/json

{
  "section": "SBIT-1A",
  "course_code": "CS 101",
  "course_name": "Introduction to Computer Science",
  "course_section": "A",
  "units": 3,
  "days": "Monday, Wednesday, Friday",
  "time": "9:00 AM - 10:30 AM",
  "room": "Room 301, Engineering Bldg",
  "instructor": "Dr. Maria Santos"
}
```

### Update Class
```
PUT /php/api/class-schedule.php?action=update
Content-Type: application/json

{
  "id": 1,
  "section": "SBIT-1A",
  "course_code": "CS 101",
  ... (same fields as add)
}
```

### Delete Class
```
DELETE /php/api/class-schedule.php?action=delete
Content-Type: application/json

{
  "id": 1
}
```

---

## 📁 File Structure:

```
WEBSYS/
├── dashboard/
│   ├── admin-class-schedule.html  (Admin interface)
│   ├── SchoolSched.html           (Student view)
│   └── account.html               (Link to admin panel)
├── php/
│   ├── config/
│   │   └── db.php                 (Database connection)
│   └── api/
│       └── class-schedule.php     (REST API)
└── sql/
    └── class_schedules.sql        (Database schema)
```

---

## ✨ Features:

✅ Persistent data storage in MySQL  
✅ CRUD operations (Create, Read, Update, Delete)  
✅ Section-based organization  
✅ Real-time updates  
✅ Error handling & validation  
✅ Responsive design  
✅ Admin authentication ready (can add login checks)  

---

## 🔒 Security Notes:

For production, add:
- Input sanitization (already using prepared statements)
- Admin authentication check
- Rate limiting
- CORS restrictions (currently allows all)
- SQL injection prevention (using PDO prepared statements ✓)

---

## 📞 Troubleshooting:

**Classes not loading?**
- Check browser console for errors
- Verify XAMPP MySQL is running
- Make sure database exists: `qc_portal`

**API returning 400 error?**
- Check JSON request format
- Verify all required fields are present
- Check browser console for error message

**Permission denied error?**
- Ensure `C:\xampp\htdocs` has write permissions
- Restart Apache/MySQL

