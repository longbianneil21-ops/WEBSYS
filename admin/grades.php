<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireAdmin();

// Handle Create/Update/Delete for Grades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create' || $action === 'update') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $course_code = trim($_POST['course_code'] ?? '');
        $course_name = trim($_POST['course_name'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $units = (int)($_POST['units'] ?? 0);
        $schedule = trim($_POST['schedule'] ?? '');
        $grade_letter = trim($_POST['grade_letter'] ?? '');
        $numeric_grade = (float)($_POST['numeric_grade'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $professor_name = trim($_POST['professor_name'] ?? '');
        
        if (!$student_id || !$course_code || !$course_name || !$semester) {
            $_SESSION['message'] = 'All required fields must be filled.';
            $_SESSION['msg_type'] = 'error';
            header('Location: grades.php');
            exit;
        }
        
        try {
            if ($action === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO grades (student_id, course_code, course_name, semester, units, schedule, grade_letter, numeric_grade, status, professor_name)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $student_id, $course_code, $course_name, $semester, $units, $schedule, $grade_letter, $numeric_grade, $status, $professor_name
                ]);
                $_SESSION['message'] = 'Grade record created successfully!';
            } else {
                $grade_id = (int)$_POST['grade_id'];
                $stmt = $pdo->prepare("
                    UPDATE grades 
                    SET student_id = ?, course_code = ?, course_name = ?, semester = ?, units = ?, schedule = ?, grade_letter = ?, numeric_grade = ?, status = ?, professor_name = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $student_id, $course_code, $course_name, $semester, $units, $schedule, $grade_letter, $numeric_grade, $status, $professor_name, $grade_id
                ]);
                $_SESSION['message'] = 'Grade record updated successfully!';
            }
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        header('Location: grades.php');
        exit;
    }
    
    // Handle Delete
    if ($action === 'delete') {
        $grade_id = (int)$_POST['grade_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
            $stmt->execute([$grade_id]);
            $_SESSION['message'] = 'Grade record deleted successfully!';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        header('Location: grades.php');
        exit;
    }
}

// Fetch all grades with student names
$stmt = $pdo->query("
    SELECT g.*, u.first_name, u.last_name, u.student_id as student_no 
    FROM grades g 
    JOIN users u ON g.student_id = u.id 
    ORDER BY g.semester DESC, g.student_id, g.course_code
");
$grades = $stmt->fetchAll();

// Get grade for editing
$edit_grade = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_grade = $stmt->fetch();
}

// Get students for dropdown
$stmt = $pdo->query("SELECT id, first_name, last_name, student_id FROM users WHERE role = 'student' ORDER BY first_name");
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Grades Management</title>
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
                <a href="grades.php" class="nav-item active">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="schedules.php" class="nav-item">
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
                <h1>Grades Management</h1>
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
                    <h2><?php echo $edit_grade ? 'Edit Grade' : 'Add New Grade'; ?></h2>
                    
                    <form method="POST" class="crud-form">
                        <input type="hidden" name="action" value="<?php echo $edit_grade ? 'update' : 'create'; ?>">
                        <?php if ($edit_grade): ?>
                            <input type="hidden" name="grade_id" value="<?php echo $edit_grade['id']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Student *</label>
                                <select name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" <?php echo ($edit_grade['student_id'] ?? '') == $student['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester *</label>
                                <select name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1st Semester 2025-2026" <?php echo ($edit_grade['semester'] ?? '') === '1st Semester 2025-2026' ? 'selected' : ''; ?>>1st Semester 2025-2026</option>
                                    <option value="2nd Semester 2025-2026" <?php echo ($edit_grade['semester'] ?? '') === '2nd Semester 2025-2026' ? 'selected' : ''; ?>>2nd Semester 2025-2026</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Course Code *</label>
                                <input type="text" name="course_code" value="<?php echo $edit_grade['course_code'] ?? ''; ?>" placeholder="e.g., CS 101" required>
                            </div>
                            <div class="form-group">
                                <label>Course Name *</label>
                                <input type="text" name="course_name" value="<?php echo $edit_grade['course_name'] ?? ''; ?>" placeholder="e.g., Introduction to Computer Science" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Units</label>
                                <input type="number" name="units" value="<?php echo $edit_grade['units'] ?? ''; ?>" min="1" max="5">
                            </div>
                            <div class="form-group">
                                <label>Schedule</label>
                                <input type="text" name="schedule" value="<?php echo $edit_grade['schedule'] ?? ''; ?>" placeholder="e.g., MWF 9:00 AM - 10:30 AM">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Grade Letter</label>
                                <select name="grade_letter">
                                    <option value="">Select Grade</option>
                                    <option value="A" <?php echo ($edit_grade['grade_letter'] ?? '') === 'A' ? 'selected' : ''; ?>>A (1.00)</option>
                                    <option value="B+" <?php echo ($edit_grade['grade_letter'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+ (1.25)</option>
                                    <option value="B" <?php echo ($edit_grade['grade_letter'] ?? '') === 'B' ? 'selected' : ''; ?>>B (1.50)</option>
                                    <option value="C+" <?php echo ($edit_grade['grade_letter'] ?? '') === 'C+' ? 'selected' : ''; ?>>C+ (2.00)</option>
                                    <option value="C" <?php echo ($edit_grade['grade_letter'] ?? '') === 'C' ? 'selected' : ''; ?>>C (2.25)</option>
                                    <option value="D" <?php echo ($edit_grade['grade_letter'] ?? '') === 'D' ? 'selected' : ''; ?>>D (2.75)</option>
                                    <option value="F" <?php echo ($edit_grade['grade_letter'] ?? '') === 'F' ? 'selected' : ''; ?>>F (5.00)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Numeric Grade</label>
                                <input type="number" name="numeric_grade" value="<?php echo $edit_grade['numeric_grade'] ?? ''; ?>" min="1.00" max="5.00" step="0.25">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="">Select Status</option>
                                    <option value="Passed" <?php echo ($edit_grade['status'] ?? '') === 'Passed' ? 'selected' : ''; ?>>Passed</option>
                                    <option value="Failed" <?php echo ($edit_grade['status'] ?? '') === 'Failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="Incomplete" <?php echo ($edit_grade['status'] ?? '') === 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Professor Name</label>
                                <input type="text" name="professor_name" value="<?php echo $edit_grade['professor_name'] ?? ''; ?>" placeholder="e.g., Dr. Maria Santos">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_grade ? 'Update Grade' : 'Add Grade'; ?>
                            </button>
                            <?php if ($edit_grade): ?>
                                <a href="grades.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <!-- Grades Table -->
                <section class="table-section">
                    <h2>All Grades (<?php echo count($grades); ?>)</h2>
                    
                    <div class="table-responsive">
                        <table class="crud-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Semester</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Professor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($grade['course_name'], 0, 40)); ?></td>
                                        <td><?php echo htmlspecialchars(substr($grade['semester'], 0, 20)); ?></td>
                                        <td>
                                            <?php if ($grade['grade_letter']): ?>
                                                <strong><?php echo htmlspecialchars($grade['grade_letter']); ?></strong> (<?php echo $grade['numeric_grade']; ?>)
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status <?php echo $grade['status'] === 'Passed' ? 'passed' : 'failed'; ?>">
                                                <?php echo $grade['status'] ?: '-'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $grade['professor_name'] ? htmlspecialchars(substr($grade['professor_name'], 0, 25)) : '-'; ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $grade['id']; ?>" class="btn-action edit">Edit</a>
                                            <button class="btn-action delete" onclick="confirmDelete(<?php echo $grade['id']; ?>)">Delete</button>
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
            <p>Are you sure you want to delete this grade record?</p>
            <div class="modal-actions">
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="gradeIdInput" name="grade_id">
                    <button type="submit" class="btn btn-delete">Delete</button>
                </form>
                <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(gradeId) {
            document.getElementById('gradeIdInput').value = gradeId;
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
