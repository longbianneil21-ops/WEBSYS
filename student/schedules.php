<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireLogin();

$student = getCurrentUser();

// Fetch current student's schedules
$stmt = $pdo->prepare("
    SELECT * FROM schedules 
    WHERE student_id = ? 
    ORDER BY 
        FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        start_time
");
$stmt->execute([$student['id']]);
$schedules = $stmt->fetchAll();

// Group by day
$by_day = [];
$days_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

foreach ($schedules as $schedule) {
    $day = $schedule['day_of_week'] ?: 'Unscheduled';
    if (!isset($by_day[$day])) {
        $by_day[$day] = [];
    }
    $by_day[$by_day[$day]] = $schedule;
}

// Sort by day order
$sorted_days = [];
foreach ($days_order as $day) {
    if (isset($by_day[$day])) {
        $sorted_days[$day] = $by_day[$day];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - My Schedule</title>
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
                <a href="grades.php" class="nav-item">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="schedules.php" class="nav-item active">
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
                    <h1>My Class Schedule</h1>
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
                <?php if (count($schedules) > 0): ?>
                    <!-- Schedule Overview -->
                    <section class="schedule-overview">
                        <div class="stat-card">
                            <p class="stat-label">Total Courses</p>
                            <p class="stat-value"><?php echo count($schedules); ?></p>
                        </div>
                        <div class="stat-card">
                            <p class="stat-label">Total Units</p>
                            <p class="stat-value">
                                <?php 
                                    $total_units = 0;
                                    foreach ($schedules as $s) {
                                        $total_units += ($s['units'] ?? 0);
                                    }
                                    echo $total_units;
                                ?>
                            </p>
                        </div>
                    </section>

                    <!-- Weekly Schedule -->
                    <section class="schedule-section">
                        <h2>Weekly Schedule</h2>
                        <div class="schedule-grid">
                            <?php foreach ($days_order as $day): ?>
                                <div class="day-column">
                                    <h3 class="day-header"><?php echo $day; ?></h3>
                                    <div class="schedule-list">
                                        <?php 
                                            if (isset($by_day[$day])) {
                                                foreach ($by_day[$day] as $schedule) {
                                                    $start_time = $schedule['start_time'] ? date('g:i A', strtotime($schedule['start_time'])) : '-';
                                                    $end_time = $schedule['end_time'] ? date('g:i A', strtotime($schedule['end_time'])) : '-';
                                                    ?>
                                                    <div class="schedule-item">
                                                        <div class="schedule-time">
                                                            <span class="time-badge"><?php echo $start_time; ?></span>
                                                            <p class="time-range"><?php echo $start_time; ?> - <?php echo $end_time; ?></p>
                                                        </div>
                                                        <div class="schedule-details">
                                                            <h4><?php echo htmlspecialchars($schedule['course_code']); ?></h4>
                                                            <p class="course-name"><?php echo htmlspecialchars(substr($schedule['course_name'], 0, 50)); ?></p>
                                                            <p class="location">
                                                                <span class="icon">📍</span>
                                                                <?php echo htmlspecialchars($schedule['room_location'] ?: 'TBA'); ?>
                                                            </p>
                                                            <p class="professor">
                                                                <span class="icon">👨‍🏫</span>
                                                                <?php echo htmlspecialchars($schedule['professor_name'] ?: 'TBA'); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                echo '<div class="no-classes"><p>No classes</p></div>';
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- All Courses Table -->
                    <section class="table-section">
                        <h2>All Courses</h2>
                        <div class="table-responsive">
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Course Name</th>
                                        <th>Day & Time</th>
                                        <th>Room</th>
                                        <th>Professor</th>
                                        <th>Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $schedule): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($schedule['course_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($schedule['course_name'], 0, 50)); ?></td>
                                            <td>
                                                <?php 
                                                    $day = htmlspecialchars(substr($schedule['day_of_week'], 0, 3));
                                                    $time = '';
                                                    if ($schedule['start_time']) {
                                                        $time = date('g:i A', strtotime($schedule['start_time']));
                                                    }
                                                    echo $day ? ($day . ' ' . $time) : '-';
                                                ?>
                                            </td>
                                            <td><?php echo $schedule['room_location'] ? htmlspecialchars($schedule['room_location']) : 'TBA'; ?></td>
                                            <td><?php echo $schedule['professor_name'] ? htmlspecialchars($schedule['professor_name']) : 'TBA'; ?></td>
                                            <td><?php echo $schedule['units'] ?: '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php else: ?>
                    <div class="no-data">
                        <p>📅 No schedule records found yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
