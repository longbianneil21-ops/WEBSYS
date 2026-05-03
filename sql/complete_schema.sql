-- Existing Events Table (already created)
-- CREATE TABLE IF NOT EXISTS events (...)

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) UNIQUE,
    phone VARCHAR(20),
    gender VARCHAR(20),
    birth_date DATE,
    year_level VARCHAR(50),
    program VARCHAR(150),
    address TEXT,
    role ENUM('student', 'admin') DEFAULT 'student',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades Table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    semester VARCHAR(50) NOT NULL,
    units INT,
    schedule VARCHAR(100),
    grade_letter VARCHAR(3),
    numeric_grade DECIMAL(5,2),
    status VARCHAR(20),
    professor_name VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_semester (semester),
    INDEX idx_course_code (course_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class Schedule Table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_code VARCHAR(50) NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    section VARCHAR(20),
    units INT,
    day_of_week VARCHAR(20),
    start_time TIME,
    end_time TIME,
    room_location VARCHAR(150),
    professor_name VARCHAR(150),
    semester VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_semester (semester),
    INDEX idx_day_of_week (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Digital ID Table (for future implementation)
CREATE TABLE IF NOT EXISTS digital_ids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNIQUE NOT NULL,
    qr_code_data VARCHAR(255),
    valid_from DATE,
    valid_until DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Admin User (password: admin123 - hashed with password_hash)
INSERT IGNORE INTO users (username, email, password, first_name, last_name, role, is_active) VALUES
('admin', 'admin@qcu.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gZvWQi', 'Admin', 'User', 'admin', 1);

-- Sample Student User (password: student123)
INSERT IGNORE INTO users (username, email, password, first_name, last_name, student_id, phone, gender, birth_date, year_level, program, address, role, is_active) VALUES
('neil.longbian', 'neillongbian@gmail.com', '$2y$10$2kTlFqQQbfEfU8y5oP.mFO0C2hBJVJ1dzYxmXwFJOC0OGJ6gZnmJK', 'Neil', 'Longbian', '2024-00001', '+63 912 345 6789', 'Male', '2003-01-15', '3rd Year', 'Bachelor of Science in Computer Science', '123 Main Street, Quezon City, Philippines', 'student', 1);

-- Sample Grades Data
INSERT IGNORE INTO grades (student_id, course_code, course_name, semester, units, schedule, grade_letter, numeric_grade, status, professor_name) VALUES
(2, 'CS 101', 'Introduction to Computer Science', '1st Semester 2025-2026', 3, 'MWF 9:00 AM - 10:30 AM', 'A', 1.25, 'Passed', 'Dr. Maria Santos'),
(2, 'MATH 201', 'Calculus II', '1st Semester 2025-2026', 3, 'TTh 10:45 AM - 12:15 PM', 'B+', 1.75, 'Passed', 'Prof. Juan Dela Cruz'),
(2, 'ENG 103', 'Technical Writing', '1st Semester 2025-2026', 3, 'MWF 1:00 PM - 2:30 PM', 'A', 1.25, 'Passed', 'Dr. Ana Reyes'),
(2, 'PHYS 101', 'Physics for Engineers', '1st Semester 2025-2026', 4, 'TTh 2:45 PM - 4:45 PM', 'B', 2.00, 'Passed', 'Prof. Roberto Garcia'),
(2, 'PE 101', 'Physical Education - Fitness', '1st Semester 2025-2026', 2, 'Sat 8:00 AM - 10:00 AM', 'A', 1.00, 'Passed', 'Coach Linda Fernandez'),
(2, 'CS 102L', 'Computer Programming Lab', '1st Semester 2025-2026', 1, 'F 5:00 PM - 8:00 PM', 'A', 1.25, 'Passed', 'Eng. Mark Villanueva');

-- Sample Schedules Data
INSERT IGNORE INTO schedules (student_id, course_code, course_name, section, units, day_of_week, start_time, end_time, room_location, professor_name, semester) VALUES
(2, 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Monday', '09:00:00', '10:30:00', 'Room 301, Engineering Bldg', 'Dr. Maria Santos', '2nd Semester 2025-2026'),
(2, 'CS 101', 'Introduction to Computer Science', 'A', 3, 'Wednesday', '09:00:00', '10:30:00', 'Room 301, Engineering Bldg', 'Dr. Maria Santos', '2nd Semester 2025-2026'),
(2, 'MATH 201', 'Calculus II', 'B', 3, 'Tuesday', '10:45:00', '12:15:00', 'Room 205, Math Bldg', 'Prof. Juan Dela Cruz', '2nd Semester 2025-2026'),
(2, 'MATH 201', 'Calculus II', 'B', 3, 'Thursday', '10:45:00', '12:15:00', 'Room 205, Math Bldg', 'Prof. Juan Dela Cruz', '2nd Semester 2025-2026'),
(2, 'ENG 103', 'Technical Writing', 'C', 3, 'Monday', '13:00:00', '14:30:00', 'Room 102, Liberal Arts Bldg', 'Dr. Ana Reyes', '2nd Semester 2025-2026'),
(2, 'PHYS 101', 'Physics for Engineers', 'A', 4, 'Tuesday', '14:45:00', '16:45:00', 'Room 401, Science Bldg', 'Prof. Roberto Garcia', '2nd Semester 2025-2026'),
(2, 'PE 101', 'Physical Education - Fitness', 'D', 2, 'Saturday', '08:00:00', '10:00:00', 'Sports Complex', 'Coach Linda Fernandez', '2nd Semester 2025-2026'),
(2, 'CS 102L', 'Computer Programming Lab', 'A1', 1, 'Friday', '17:00:00', '20:00:00', 'Computer Lab 3', 'Eng. Mark Villanueva', '2nd Semester 2025-2026');
