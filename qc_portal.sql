-- ============================================
-- QCU STUDENT PORTAL DATABASE
-- Complete MySQL Schema with Admin & Users
-- ============================================

-- Drop existing database if it exists
DROP DATABASE IF EXISTS qc_portal;
CREATE DATABASE qc_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qc_portal;

-- ============================================
-- 1. ADMIN ACCOUNTS TABLE
-- ============================================
CREATE TABLE admin_accounts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('super_admin', 'admin', 'registrar') DEFAULT 'admin',
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. STUDENT ACCOUNTS TABLE
-- ============================================
CREATE TABLE student_accounts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id VARCHAR(20) UNIQUE NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(15),
  date_of_birth DATE,
  gender ENUM('Male', 'Female', 'Other'),
  address TEXT,
  program VARCHAR(100),
  year_level ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') DEFAULT '1st Year',
  section VARCHAR(20) NOT NULL,
  enrollment_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_student (student_id),
  UNIQUE KEY unique_email (email),
  INDEX idx_section (section),
  INDEX idx_student_id (student_id),
  INDEX idx_enrollment_status (enrollment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. CLASS SCHEDULES TABLE
-- ============================================
CREATE TABLE class_schedules (
  id INT PRIMARY KEY AUTO_INCREMENT,
  section VARCHAR(20) NOT NULL,
  course_code VARCHAR(50) NOT NULL,
  course_name VARCHAR(255) NOT NULL,
  course_section VARCHAR(10) NOT NULL,
  units INT NOT NULL,
  days VARCHAR(100) NOT NULL,
  time VARCHAR(50) NOT NULL,
  room VARCHAR(100) NOT NULL,
  instructor_name VARCHAR(100) NOT NULL,
  instructor_email VARCHAR(100),
  semester VARCHAR(50),
  academic_year VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_schedule (section, course_code, course_section),
  INDEX idx_section (section),
  INDEX idx_course_code (course_code),
  INDEX idx_academic_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. GRADE REPORTS TABLE
-- Links to class_schedules (course info synced)
-- ============================================
CREATE TABLE grade_reports (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  schedule_id INT NOT NULL,
  
  -- Synced from class_schedules (read-only for reference)
  course_code VARCHAR(50) NOT NULL,
  course_name VARCHAR(255) NOT NULL,
  course_section VARCHAR(10) NOT NULL,
  units INT NOT NULL,
  instructor_name VARCHAR(100) NOT NULL,
  time VARCHAR(50) NOT NULL,
  
  -- Admin input fields
  grade VARCHAR(5),
  numeric_grade DECIMAL(5, 2),
  status ENUM('Passed', 'Failed', 'Incomplete', 'Withdrawn') DEFAULT 'Incomplete',
  remarks TEXT,
  
  -- Metadata
  semester VARCHAR(50),
  academic_year VARCHAR(20),
  graded_by INT,
  graded_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Constraints
  FOREIGN KEY (student_id) REFERENCES student_accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES class_schedules(id) ON DELETE RESTRICT,
  FOREIGN KEY (graded_by) REFERENCES admin_accounts(id) ON DELETE SET NULL,
  
  UNIQUE KEY unique_grade (student_id, schedule_id),
  INDEX idx_student_id (student_id),
  INDEX idx_schedule_id (schedule_id),
  INDEX idx_academic_year (academic_year),
  INDEX idx_status (status),
  INDEX idx_graded_by (graded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. ENROLLMENT TABLE
-- Links students to courses
-- ============================================
CREATE TABLE enrollments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  schedule_id INT NOT NULL,
  section VARCHAR(20) NOT NULL,
  enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  enrollment_status ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
  
  FOREIGN KEY (student_id) REFERENCES student_accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (schedule_id) REFERENCES class_schedules(id) ON DELETE CASCADE,
  
  UNIQUE KEY unique_enrollment (student_id, schedule_id),
  INDEX idx_student_id (student_id),
  INDEX idx_schedule_id (schedule_id),
  INDEX idx_section (section),
  INDEX idx_enrollment_status (enrollment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. ADMIN ACTIVITY LOG
-- ============================================
CREATE TABLE admin_activity_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  admin_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  table_name VARCHAR(50),
  record_id INT,
  description TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
  INDEX idx_admin_id (admin_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Sample Admin Accounts
INSERT INTO admin_accounts (username, email, password, full_name, role, status) VALUES
('admin', 'admin@qcu.edu.ph', 'hashed_password_admin_123', 'System Administrator', 'super_admin', 'active'),
('registrar', 'registrar@qcu.edu.ph', 'hashed_password_registrar_456', 'Dr. Maria De Guzman', 'registrar', 'active'),
('gradesman', 'grades@qcu.edu.ph', 'hashed_password_grades_789', 'Prof. Juan Dela Cruz', 'admin', 'active');

-- Sample Students (Section SBIT-1A)
INSERT INTO student_accounts (student_id, first_name, last_name, email, password, phone, date_of_birth, gender, program, year_level, section, enrollment_status) VALUES
('2024-00001', 'Neil', 'Longbian', 'neil.longbian@qcu.edu.ph', 'hashed_password_neil', '+63912345678', '2003-01-15', 'Male', 'Bachelor of Science in Information Technology', '1st Year', 'SBIT-1A', 'active'),
('2024-00002', 'Maria', 'Santos', 'maria.santos@qcu.edu.ph', 'hashed_password_maria', '+63912345679', '2003-03-20', 'Female', 'Bachelor of Science in Information Technology', '1st Year', 'SBIT-1A', 'active'),
('2024-00003', 'Juan', 'Dela Rosa', 'juan.delarosa@qcu.edu.ph', 'hashed_password_juan', '+63912345680', '2003-05-10', 'Male', 'Bachelor of Science in Information Technology', '1st Year', 'SBIT-1A', 'active');

-- Sample Class Schedules (SBIT-1A)
INSERT INTO class_schedules (section, course_code, course_name, course_section, units, days, time, room, instructor_name, instructor_email, semester, academic_year) VALUES
('SBIT-1A', 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Monday, Wednesday, Friday', '9:00 AM - 10:30 AM', 'Room 301, Engineering Bldg', 'Dr. Maria Santos', 'maria.santos@qcu.edu.ph', '1st Semester', '2024-2025'),
('SBIT-1A', 'MATH 101', 'Calculus I', 'A', 3, 'Tuesday, Thursday', '10:45 AM - 12:15 PM', 'Room 205, Math Bldg', 'Prof. Juan Dela Cruz', 'juan.delacruz@qcu.edu.ph', '1st Semester', '2024-2025'),
('SBIT-1A', 'ENG 101', 'English Composition', 'A', 3, 'Monday, Wednesday, Friday', '1:00 PM - 2:30 PM', 'Room 102, Liberal Arts Bldg', 'Dr. Ana Reyes', 'ana.reyes@qcu.edu.ph', '1st Semester', '2024-2025'),
('SBIT-1A', 'PHYS 101', 'Physics for Engineers', 'A', 4, 'Tuesday, Thursday', '2:45 PM - 4:45 PM', 'Room 401, Science Bldg', 'Prof. Roberto Garcia', 'roberto.garcia@qcu.edu.ph', '1st Semester', '2024-2025');

-- Sample Enrollments
INSERT INTO enrollments (student_id, schedule_id, section, enrollment_status) VALUES
(1, 1, 'SBIT-1A', 'enrolled'),
(1, 2, 'SBIT-1A', 'enrolled'),
(1, 3, 'SBIT-1A', 'enrolled'),
(1, 4, 'SBIT-1A', 'enrolled'),
(2, 1, 'SBIT-1A', 'enrolled'),
(2, 2, 'SBIT-1A', 'enrolled'),
(2, 3, 'SBIT-1A', 'enrolled'),
(2, 4, 'SBIT-1A', 'enrolled'),
(3, 1, 'SBIT-1A', 'enrolled'),
(3, 2, 'SBIT-1A', 'enrolled'),
(3, 3, 'SBIT-1A', 'enrolled'),
(3, 4, 'SBIT-1A', 'enrolled');

-- Sample Grade Reports (synced from schedules)
INSERT INTO grade_reports (student_id, schedule_id, course_code, course_name, course_section, units, instructor_name, time, semester, academic_year) VALUES
(1, 1, 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Dr. Maria Santos', '9:00 AM - 10:30 AM', '1st Semester', '2024-2025'),
(1, 2, 'MATH 101', 'Calculus I', 'A', 3, 'Prof. Juan Dela Cruz', '10:45 AM - 12:15 PM', '1st Semester', '2024-2025'),
(1, 3, 'ENG 101', 'English Composition', 'A', 3, 'Dr. Ana Reyes', '1:00 PM - 2:30 PM', '1st Semester', '2024-2025'),
(1, 4, 'PHYS 101', 'Physics for Engineers', 'A', 4, 'Prof. Roberto Garcia', '2:45 PM - 4:45 PM', '1st Semester', '2024-2025'),
(2, 1, 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Dr. Maria Santos', '9:00 AM - 10:30 AM', '1st Semester', '2024-2025'),
(2, 2, 'MATH 101', 'Calculus I', 'A', 3, 'Prof. Juan Dela Cruz', '10:45 AM - 12:15 PM', '1st Semester', '2024-2025'),
(2, 3, 'ENG 101', 'English Composition', 'A', 3, 'Dr. Ana Reyes', '1:00 PM - 2:30 PM', '1st Semester', '2024-2025'),
(2, 4, 'PHYS 101', 'Physics for Engineers', 'A', 4, 'Prof. Roberto Garcia', '2:45 PM - 4:45 PM', '1st Semester', '2024-2025'),
(3, 1, 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Dr. Maria Santos', '9:00 AM - 10:30 AM', '1st Semester', '2024-2025'),
(3, 2, 'MATH 101', 'Calculus I', 'A', 3, 'Prof. Juan Dela Cruz', '10:45 AM - 12:15 PM', '1st Semester', '2024-2025'),
(3, 3, 'ENG 101', 'English Composition', 'A', 3, 'Dr. Ana Reyes', '1:00 PM - 2:30 PM', '1st Semester', '2024-2025'),
(3, 4, 'PHYS 101', 'Physics for Engineers', 'A', 4, 'Prof. Roberto Garcia', '2:45 PM - 4:45 PM', '1st Semester', '2024-2025');

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Auto-sync grades when schedule is updated
DELIMITER //

CREATE PROCEDURE sync_schedule_to_grades(
  IN p_schedule_id INT
)
BEGIN
  UPDATE grade_reports gr
  JOIN class_schedules cs ON gr.schedule_id = cs.id
  SET 
    gr.course_code = cs.course_code,
    gr.course_name = cs.course_name,
    gr.course_section = cs.course_section,
    gr.units = cs.units,
    gr.instructor_name = cs.instructor_name,
    gr.time = cs.time
  WHERE cs.id = p_schedule_id;
END //

-- Create grade report for a student enrollment
CREATE PROCEDURE create_grade_for_enrollment(
  IN p_student_id INT,
  IN p_schedule_id INT
)
BEGIN
  INSERT IGNORE INTO grade_reports (student_id, schedule_id, course_code, course_name, course_section, units, instructor_name, time, semester, academic_year)
  SELECT p_student_id, cs.id, cs.course_code, cs.course_name, cs.course_section, cs.units, cs.instructor_name, cs.time, cs.semester, cs.academic_year
  FROM class_schedules cs
  WHERE cs.id = p_schedule_id;
END //

DELIMITER ;

-- ============================================
-- VIEWS
-- ============================================

-- View: Student Grade Summary
CREATE VIEW student_grade_summary AS
SELECT 
  sa.student_id,
  sa.first_name,
  sa.last_name,
  gr.course_code,
  gr.course_name,
  gr.units,
  gr.instructor_name,
  gr.numeric_grade,
  gr.grade,
  gr.status,
  gr.academic_year
FROM grade_reports gr
JOIN student_accounts sa ON gr.student_id = sa.id
WHERE gr.status IS NOT NULL
ORDER BY gr.academic_year DESC, gr.course_code;

-- View: Admin Grade Management Dashboard
CREATE VIEW admin_grade_dashboard AS
SELECT 
  gr.id,
  sa.student_id,
  CONCAT(sa.first_name, ' ', sa.last_name) AS student_name,
  gr.course_code,
  gr.course_name,
  gr.units,
  gr.instructor_name,
  gr.numeric_grade,
  gr.grade,
  gr.status,
  gr.academic_year,
  IF(gr.grade IS NULL, 'Not Graded', 'Graded') AS grading_status,
  aa.full_name AS graded_by_name
FROM grade_reports gr
JOIN student_accounts sa ON gr.student_id = sa.id
LEFT JOIN admin_accounts aa ON gr.graded_by = aa.id
ORDER BY gr.academic_year DESC, sa.student_id;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

CREATE INDEX idx_grade_reports_by_admin ON grade_reports(graded_by, academic_year);
CREATE INDEX idx_student_grades_by_year ON grade_reports(student_id, academic_year);
CREATE INDEX idx_schedule_by_year ON class_schedules(academic_year, semester);

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
