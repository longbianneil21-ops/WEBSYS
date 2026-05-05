-- QCU Portal Database Schema (Updated 2026)
-- Includes: Users, Admins, Class Schedules, Grade Reports, Events, Dashboard Content, Digital IDs

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS `qc_portal`;
CREATE DATABASE IF NOT EXISTS `qc_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qc_portal`;

-- ========== USERS TABLE (Students) ==========
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `section` VARCHAR(10) NOT NULL,
    `status` ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_section (section),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ADMINS TABLE ==========
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `role` ENUM('SuperAdmin', 'Admin', 'Moderator') DEFAULT 'Admin',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CLASS SCHEDULES TABLE ==========
CREATE TABLE `class_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(10) NOT NULL,
    `course_code` VARCHAR(20) NOT NULL,
    `course_name` VARCHAR(255) NOT NULL,
    `units` INT NOT NULL,
    `days` VARCHAR(50),
    `time` VARCHAR(100),
    `room` VARCHAR(50),
    `instructor_name` VARCHAR(100),
    `academic_year` VARCHAR(9) DEFAULT '2025-2026',
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_section (section),
    INDEX idx_course_code (course_code),
    INDEX idx_academic_year (academic_year),
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== GRADE REPORTS TABLE ==========
CREATE TABLE `grade_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `schedule_id` INT NOT NULL,
    `grade` VARCHAR(3),
    `numeric_grade` DECIMAL(3,2),
    `status` ENUM('Passed', 'Failed', 'Incomplete') DEFAULT 'Incomplete',
    `academic_year` VARCHAR(9) DEFAULT '2025-2026',
    `graded_by` INT,
    `graded_at` TIMESTAMP NULL,
    `remarks` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_id (student_id),
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_academic_year (academic_year),
    INDEX idx_status (status),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES class_schedules(id) ON DELETE RESTRICT,
    FOREIGN KEY (graded_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== EVENTS TABLE ==========
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `event_date` DATE NOT NULL,
    `location` VARCHAR(255),
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== DASHBOARD CONTENT TABLE ==========
CREATE TABLE `dashboard_content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `content_type` ENUM('announcement', 'update', 'notice', 'alert') DEFAULT 'announcement',
    `display_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order),
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== DIGITAL ID CONTENT TABLE ==========
CREATE TABLE `digital_id_content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL UNIQUE,
    `id_number` VARCHAR(20) UNIQUE,
    `student_status` ENUM('Active', 'Inactive', 'Graduated', 'On Leave') DEFAULT 'Active',
    `academic_status` ENUM('Regular', 'Probation', 'Dean\'s List', 'Semester Off') DEFAULT 'Regular',
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_id (student_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== SAMPLE DATA ==========

-- Sample Admin Accounts
INSERT INTO `admins` (`email`, `password`, `first_name`, `last_name`, `role`) VALUES
('admin@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Admin', 'Account', 'SuperAdmin'),
('moderator@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Moderator', 'Account', 'Moderator');

-- Sample Student Accounts
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `section`) VALUES
('student1@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Juan', 'Dela Cruz', 'SBIT-1A'),
('student2@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Maria', 'Garcia', 'SBIT-1A'),
('student3@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Pedro', 'Santos', 'SBIT-1B'),
('student4@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Ana', 'Lopez', 'SBIT-1B'),
('student5@qcu.edu.ph', '$2y$10$YIjlrIdqDHqZDh5RA/6V1eIc8n/xB8rN4qM3B2V5C6D7E8F9G0H1I2', 'Carlo', 'Martinez', 'SBIT-1C');

-- Sample Class Schedules (SBIT-1A)
INSERT INTO `class_schedules` (`section`, `course_code`, `course_name`, `units`, `days`, `time`, `room`, `instructor_name`, `academic_year`, `created_by`) VALUES
('SBIT-1A', 'CS101', 'Introduction to Computer Science', 3, 'MWF', '09:00-10:00', 'Lab 1', 'Dr. Smith', '2025-2026', 1),
('SBIT-1A', 'MATH101', 'Calculus I', 4, 'TTh', '10:00-11:30', 'Room 101', 'Prof. Johnson', '2025-2026', 1),
('SBIT-1A', 'PHYS101', 'Physics I', 3, 'MWF', '14:00-15:00', 'Lab 2', 'Dr. Martinez', '2025-2026', 1);

-- Sample Class Schedules (SBIT-1B)
INSERT INTO `class_schedules` (`section`, `course_code`, `course_name`, `units`, `days`, `time`, `room`, `instructor_name`, `academic_year`, `created_by`) VALUES
('SBIT-1B', 'CS101', 'Introduction to Computer Science', 3, 'TTh', '09:00-10:30', 'Lab 1', 'Dr. Smith', '2025-2026', 1),
('SBIT-1B', 'MATH101', 'Calculus I', 4, 'MWF', '10:00-11:00', 'Room 102', 'Prof. Johnson', '2025-2026', 1);

-- Sample Events
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `created_by`) VALUES
('Orientation Day', 'Welcome orientation for new students', '2025-06-15', 'Main Auditorium', 1),
('Mid-term Exams', 'Mid-term examination period for all courses', '2025-07-01', 'Various Rooms', 1),
('Sports Festival', 'Annual sports festival and competition', '2025-08-20', 'Sports Complex', 1);

-- Sample Dashboard Content
INSERT INTO `dashboard_content` (`title`, `description`, `content_type`, `display_order`, `is_active`, `created_by`) VALUES
('Welcome to QCU Portal', 'Welcome to the Quezon City University Student Portal. Navigate through your classes, grades, and events.', 'announcement', 1, TRUE, 1),
('Registration Open', 'Course registration for the next semester is now open. Please register your courses before the deadline.', 'notice', 2, TRUE, 1),
('System Maintenance', 'The portal will undergo maintenance on Sunday from 12 AM to 6 AM.', 'alert', 3, TRUE, 1);

-- Sample Digital ID (only for student 1)
INSERT INTO `digital_id_content` (`student_id`, `id_number`, `student_status`, `academic_status`, `created_by`) VALUES
(1, 'QCU-000001', 'Active', 'Regular', 1);

-- Sample Grade Data (for student 1)
INSERT INTO `grade_reports` (`student_id`, `schedule_id`, `grade`, `numeric_grade`, `status`, `academic_year`, `graded_by`, `graded_at`) VALUES
(1, 1, 'A', 3.75, 'Passed', '2025-2026', 1, NOW()),
(1, 2, 'B+', 3.25, 'Passed', '2025-2026', 1, NOW()),
(1, 3, 'A-', 3.50, 'Passed', '2025-2026', 1, NOW());

COMMIT;
