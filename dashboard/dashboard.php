<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireLogin();

$user = getCurrentUser();

// Redirect based on role
if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Student dashboard
$student = $user;

// Get student's data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student['id']]);
$student_data = $stmt->fetch();

// Get recent grades
$stmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$student['id']]);
$recent_grades = $stmt->fetchAll();

// Get today's schedule
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE student_id = ? AND day_of_week = ? ORDER BY start_time");
$today = date('l'); // Get current day name
$stmt->execute([$student['id'], $today]);
$todays_schedule = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM grades WHERE student_id = ? AND status = 'Passed'");
$stmt->execute([$student['id']]);
$passed_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM schedules WHERE student_id = ?");
$stmt->execute([$student['id']]);
$schedule_count = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - QCU Portal</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="../student/grades.php" class="nav-item">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="../student/schedules.php" class="nav-item">
                    <span class="icon">⏰</span>
                    <span>Schedule</span>
                </a>
                <a href="../student/profile.php" class="nav-item">
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
                    <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $student['first_name'])[0]); ?>!</h1>
                </div>
                <div class="header-right">
                    <div class="profile-card">
                        <div class="avatar"><?php echo substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1); ?></div>
                        <div class="profile-info">
                            <p class="name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                            <p class="email"><?php echo htmlspecialchars($student_data['program'] ?: 'Student'); ?></p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Quick Stats -->
                <section class="stats-section">
                    <div class="stat-card">
                        <p class="stat-label">Courses Passed</p>
                        <p class="stat-value"><?php echo $passed_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Total Classes</p>
                        <p class="stat-value"><?php echo $schedule_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Year Level</p>
                        <p class="stat-value"><?php echo htmlspecialchars(substr($student_data['year_level'], 0, 1) . $student_data['year_level'] ? substr($student_data['year_level'], 0, 3) . '.' : '-'); ?></p>
                    </div>
                </section>

                <!-- Today's Schedule -->
                <section class="schedule-section">
                    <h2>Today's Classes (<?php echo $today; ?>)</h2>
                    <?php if (count($todays_schedule) > 0): ?>
                        <div class="schedule-list">
                            <?php foreach ($todays_schedule as $schedule): ?>
                                <div class="schedule-item">
                                    <div class="schedule-time">
                                        <span class="time-badge"><?php echo date('g:i A', strtotime($schedule['start_time'])); ?></span>
                                    </div>
                                    <div class="schedule-details">
                                        <h4><?php echo htmlspecialchars($schedule['course_code']); ?></h4>
                                        <p class="course-name"><?php echo htmlspecialchars(substr($schedule['course_name'], 0, 50)); ?></p>
                                        <p class="location">
                                            <span class="icon">📍</span>
                                            <?php echo htmlspecialchars($schedule['room_location'] ?: 'TBA'); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-classes">
                            <p>No classes scheduled for today</p>
                        </div>
                    <?php endif; ?>
                    <div style="margin-top: 15px;">
                        <a href="../student/schedules.php" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background: #f0a500; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">View Full Schedule</a>
                    </div>
                </section>

                <!-- Recent Grades -->
                <?php if (count($recent_grades) > 0): ?>
                    <section class="semester-section">
                        <div class="semester-header">
                            <h2>Recent Grades</h2>
                        </div>
                        <div class="table-responsive">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_grades as $grade): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grade['course_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($grade['course_name'], 0, 40)); ?></td>
                                            <td>
                                                <?php if ($grade['grade_letter']): ?>
                                                    <strong><?php echo htmlspecialchars($grade['grade_letter']); ?></strong>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($grade['status'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars($grade['status'] ?: 'N/A'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 15px;">
                            <a href="../student/grades.php" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background: #f0a500; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">View All Grades</a>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Quick Links -->
                <section class="info-section">
                    <h2>Quick Links</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <a href="../student/grades.php" style="padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; text-align: center; color: #1a2a4a; font-weight: 600; border-left: 3px solid #f0a500;">
                            📝 My Grades
                        </a>
                        <a href="../student/schedules.php" style="padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; text-align: center; color: #1a2a4a; font-weight: 600; border-left: 3px solid #f0a500;">
                            ⏰ My Schedule
                        </a>
                        <a href="../student/profile.php" style="padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; text-align: center; color: #1a2a4a; font-weight: 600; border-left: 3px solid #f0a500;">
                            👤 My Profile
                        </a>
                        <a href="../student/events.php" style="padding: 15px; background: #f9f9f9; border-radius: 8px; text-decoration: none; text-align: center; color: #1a2a4a; font-weight: 600; border-left: 3px solid #f0a500;">
                            📅 Events
                        </a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
