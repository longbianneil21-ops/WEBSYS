<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireLogin();

// Get current student data
$student = getCurrentUser();

// Fetch current student's grades
$stmt = $pdo->prepare("
    SELECT * FROM grades 
    WHERE student_id = ? 
    ORDER BY semester DESC, course_code
");
$stmt->execute([$student['id']]);
$grades = $stmt->fetchAll();

// Calculate GPA and other stats
$total_units = 0;
$passed_units = 0;
$failed_units = 0;
$current_gpa = 0;
$gpa_sum = 0;
$semester_breakdown = [];

foreach ($grades as $grade) {
    if ($grade['units']) {
        $total_units += $grade['units'];
        
        if ($grade['status'] === 'Passed') {
            $passed_units += $grade['units'];
        } else if ($grade['status'] === 'Failed') {
            $failed_units += $grade['units'];
        }
        
        if ($grade['numeric_grade']) {
            $gpa_sum += ($grade['numeric_grade'] * $grade['units']);
        }
    }
    
    // Semester breakdown
    $sem = $grade['semester'] ?: 'N/A';
    if (!isset($semester_breakdown[$sem])) {
        $semester_breakdown[$sem] = [];
    }
    $semester_breakdown[$sem][] = $grade;
}

$current_gpa = $total_units > 0 ? number_format($gpa_sum / $total_units, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - My Grades</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/student-view-style.css">
</head>
<body>
    <div class="student-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2>📚 QCU PORTAL</h2>
                <p>Student Dashboard</p>
            </div>

            <nav class="sidebar-nav">
                <a href="../dashboard/dashboard.php" class="nav-item">
                    <span class="icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="grades.php" class="nav-item active">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="schedules.php" class="nav-item">
                    <span class="icon">⏰</span>
                    <span>Schedule</span>
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span>
                    <span>Profile</span>
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
                <div class="header-left">
                    <h1>My Grades</h1>
                </div>
                <div class="header-right">
                    <div class="profile-card">
                        <div class="avatar"><?php echo substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1); ?></div>
                        <div class="profile-info">
                            <p class="name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                            <p class="email"><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Grade Statistics -->
                <section class="stats-section">
                    <div class="stat-card">
                        <p class="stat-label">Current GPA</p>
                        <p class="stat-value"><?php echo $current_gpa; ?></p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Total Units</p>
                        <p class="stat-value"><?php echo $total_units; ?></p>
                    </div>
                    <div class="stat-card passed">
                        <p class="stat-label">Units Passed</p>
                        <p class="stat-value"><?php echo $passed_units; ?></p>
                    </div>
                    <div class="stat-card failed">
                        <p class="stat-label">Units Failed</p>
                        <p class="stat-value"><?php echo $failed_units; ?></p>
                    </div>
                </section>

                <?php if (count($grades) > 0): ?>
                    <?php foreach ($semester_breakdown as $semester => $semester_grades): ?>
                        <!-- Semester Section -->
                        <section class="semester-section">
                            <div class="semester-header">
                                <h2><?php echo htmlspecialchars($semester); ?></h2>
                                <span class="course-count"><?php echo count($semester_grades); ?> courses</span>
                            </div>

                            <div class="table-responsive">
                                <table class="grades-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Units</th>
                                            <th>Grade</th>
                                            <th>Status</th>
                                            <th>Professor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semester_grades as $grade): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($grade['course_code']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                                <td><?php echo $grade['units'] ?: '-'; ?></td>
                                                <td>
                                                    <?php if ($grade['grade_letter']): ?>
                                                        <strong><?php echo htmlspecialchars($grade['grade_letter']); ?></strong>
                                                        <span class="numeric">(<?php echo $grade['numeric_grade']; ?>)</span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo strtolower($grade['status'] ?? ''); ?>">
                                                        <?php echo htmlspecialchars($grade['status'] ?: 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $grade['professor_name'] ? htmlspecialchars($grade['professor_name']) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>📚 No grade records found yet.</p>
                    </div>
                <?php endif; ?>

                <!-- Grading Scale Reference -->
                <section class="reference-section">
                    <h2>Grading Scale</h2>
                    <div class="grading-scale">
                        <div class="scale-item">
                            <span class="grade-letter">A</span>
                            <span class="grade-range">1.00</span>
                            <span class="grade-desc">Excellent</span>
                        </div>
                        <div class="scale-item">
                            <span class="grade-letter">B+</span>
                            <span class="grade-range">1.25</span>
                            <span class="grade-desc">Very Good</span>
                        </div>
                        <div class="scale-item">
                            <span class="grade-letter">B</span>
                            <span class="grade-range">1.50</span>
                            <span class="grade-desc">Good</span>
                        </div>
                        <div class="scale-item">
                            <span class="grade-letter">C+</span>
                            <span class="grade-range">2.00</span>
                            <span class="grade-desc">Satisfactory</span>
                        </div>
                        <div class="scale-item">
                            <span class="grade-letter">C</span>
                            <span class="grade-range">2.25</span>
                            <span class="grade-desc">Fair</span>
                        </div>
                        <div class="scale-item">
                            <span class="grade-letter">D</span>
                            <span class="grade-range">2.75</span>
                            <span class="grade-desc">Passing</span>
                        </div>
                        <div class="scale-item failed">
                            <span class="grade-letter">F</span>
                            <span class="grade-range">5.00</span>
                            <span class="grade-desc">Failed</span>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
