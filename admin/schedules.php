<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireAdmin();

// Handle Create/Update/Delete for Schedules
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create' || $action === 'update') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $course_code = trim($_POST['course_code'] ?? '');
        $course_name = trim($_POST['course_name'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $units = (int)($_POST['units'] ?? 0);
        $day_of_week = trim($_POST['day_of_week'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $room_location = trim($_POST['room_location'] ?? '');
        $professor_name = trim($_POST['professor_name'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        
        if (!$student_id || !$course_code || !$course_name || !$semester) {
            $_SESSION['message'] = 'All required fields must be filled.';
            $_SESSION['msg_type'] = 'error';
            header('Location: schedules.php');
            exit;
        }
        
        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO schedules (student_id, course_code, course_name, section, units, day_of_week, start_time, end_time, room_location, professor_name, semester)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $student_id, $course_code, $course_name, $section, $units, $day_of_week, $start_time, $end_time, $room_location, $professor_name, $semester
                ]);
                $_SESSION['message'] = 'Schedule record created successfully!';
            } else {
                $schedule_id = (int)$_POST['schedule_id'];
                $stmt = $pdo->prepare("
                    UPDATE schedules 
                    SET student_id = ?, course_code = ?, course_name = ?, section = ?, units = ?, day_of_week = ?, start_time = ?, end_time = ?, room_location = ?, professor_name = ?, semester = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $student_id, $course_code, $course_name, $section, $units, $day_of_week, $start_time, $end_time, $room_location, $professor_name, $semester, $schedule_id
                ]);
                $_SESSION['message'] = 'Schedule record updated successfully!';
            }
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        header('Location: schedules.php');
        exit;
    }
    
    // Handle Delete
    if ($action === 'delete') {
        $schedule_id = (int)$_POST['schedule_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$schedule_id]);
            $_SESSION['message'] = 'Schedule record deleted successfully!';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        header('Location: schedules.php');
        exit;
    }
}

// Fetch all schedules with student names
$stmt = $pdo->query("
    SELECT s.*, u.first_name, u.last_name, u.student_id as student_no 
    FROM schedules s 
    JOIN users u ON s.student_id = u.id 
    ORDER BY s.semester DESC, s.day_of_week, s.start_time
");
$schedules = $stmt->fetchAll();

// Get schedule for editing
$edit_schedule = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_schedule = $stmt->fetch();
}

// Get students for dropdown
$stmt = $pdo->query("SELECT id, first_name, last_name, student_id FROM users WHERE role = 'student' ORDER BY first_name");
$students = $stmt->fetchAll();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Schedules Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin-crud-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2>QCU ADMIN</h2>
                <p>Management Panel</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-item">
                    <span class="icon">👥</span>
                    <span>Users</span>
                </a>
                <a href="events.php" class="nav-item">
                    <span class="icon">📅</span>
                    <span>Events</span>
                </a>
                <a href="grades.php" class="nav-item">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="schedules.php" class="nav-item active">
                    <span class="icon">⏰</span>
                    <span>Schedules</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="../auth/logout.php" class="logout-link">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h1>Schedules Management</h1>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="content-wrapper">
                <!-- Form Section -->
                <section class="form-section">
                    <h2><?php echo $edit_schedule ? 'Edit Schedule' : 'Add New Schedule'; ?></h2>
                    
                    <form method="POST" class="crud-form">
                        <input type="hidden" name="action" value="<?php echo $edit_schedule ? 'update' : 'create'; ?>">
                        <?php if ($edit_schedule): ?>
                            <input type="hidden" name="schedule_id" value="<?php echo $edit_schedule['id']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Student *</label>
                                <select name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" <?php echo ($edit_schedule['student_id'] ?? '') == $student['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester *</label>
                                <select name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1st Semester 2025-2026" <?php echo ($edit_schedule['semester'] ?? '') === '1st Semester 2025-2026' ? 'selected' : ''; ?>>1st Semester 2025-2026</option>
                                    <option value="2nd Semester 2025-2026" <?php echo ($edit_schedule['semester'] ?? '') === '2nd Semester 2025-2026' ? 'selected' : ''; ?>>2nd Semester 2025-2026</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Course Code *</label>
                                <input type="text" name="course_code" value="<?php echo $edit_schedule['course_code'] ?? ''; ?>" placeholder="e.g., CS 101" required>
                            </div>
                            <div class="form-group">
                                <label>Course Name *</label>
                                <input type="text" name="course_name" value="<?php echo $edit_schedule['course_name'] ?? ''; ?>" placeholder="e.g., Introduction to Computer Science" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Section</label>
                                <input type="text" name="section" value="<?php echo $edit_schedule['section'] ?? ''; ?>" placeholder="e.g., A">
                            </div>
                            <div class="form-group">
                                <label>Units</label>
                                <input type="number" name="units" value="<?php echo $edit_schedule['units'] ?? ''; ?>" min="1" max="5">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Day of Week</label>
                                <select name="day_of_week">
                                    <option value="">Select Day</option>
                                    <?php foreach ($days as $day): ?>
                                        <option value="<?php echo $day; ?>" <?php echo ($edit_schedule['day_of_week'] ?? '') === $day ? 'selected' : ''; ?>>
                                            <?php echo $day; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" value="<?php echo $edit_schedule['start_time'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" value="<?php echo $edit_schedule['end_time'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Room Location</label>
                                <input type="text" name="room_location" value="<?php echo $edit_schedule['room_location'] ?? ''; ?>" placeholder="e.g., Room 301, Engineering Bldg">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Professor Name</label>
                                <input type="text" name="professor_name" value="<?php echo $edit_schedule['professor_name'] ?? ''; ?>" placeholder="e.g., Dr. Maria Santos">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_schedule ? 'Update Schedule' : 'Add Schedule'; ?>
                            </button>
                            <?php if ($edit_schedule): ?>
                                <a href="schedules.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <!-- Schedules Table -->
                <section class="table-section">
                    <h2>All Schedules (<?php echo count($schedules); ?>)</h2>
                    
                    <div class="table-responsive">
                        <table class="crud-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>Professor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($schedule['first_name'] . ' ' . $schedule['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($schedule['course_name'], 0, 40)); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                        <td>
                                            <?php if ($schedule['start_time'] && $schedule['end_time']): ?>
                                                <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> - <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $schedule['room_location'] ? htmlspecialchars(substr($schedule['room_location'], 0, 30)) : '-'; ?></td>
                                        <td><?php echo $schedule['professor_name'] ? htmlspecialchars(substr($schedule['professor_name'], 0, 25)) : '-'; ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $schedule['id']; ?>" class="btn-action edit">Edit</a>
                                            <button class="btn-action delete" onclick="confirmDelete(<?php echo $schedule['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this schedule record?</p>
            <div class="modal-actions">
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="scheduleIdInput" name="schedule_id">
                    <button type="submit" class="btn btn-delete">Delete</button>
                </form>
                <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(scheduleId) {
            document.getElementById('scheduleIdInput').value = scheduleId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
