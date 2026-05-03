<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireAdmin();

$admin = getCurrentUser();

// Get statistics
try {
    // Count users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $student_count = $stmt->fetch()['count'];

    // Count admins
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $admin_count = $stmt->fetch()['count'];

    // Count events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
    $event_count = $stmt->fetch()['count'];

    // Count grades
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM grades");
    $grade_count = $stmt->fetch()['count'];

    // Count schedules
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM schedules");
    $schedule_count = $stmt->fetch()['count'];

    // Recent events
    $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
    $recent_events = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error fetching statistics: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QCU Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin-dashboard-style.css">
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
                <a href="dashboard.php" class="nav-item active">
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
                <h1>Dashboard</h1>
                <div class="admin-info">
                    <span><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?> (Admin)</span>
                </div>
            </header>

            <div class="content-area">
                <!-- Statistics Cards -->
                <section class="stats-section">
                    <h2>Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-card student">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <p class="stat-label">Students</p>
                                <p class="stat-value"><?php echo $student_count; ?></p>
                            </div>
                        </div>

                        <div class="stat-card admin">
                            <div class="stat-icon">🔑</div>
                            <div class="stat-content">
                                <p class="stat-label">Admins</p>
                                <p class="stat-value"><?php echo $admin_count; ?></p>
                            </div>
                        </div>

                        <div class="stat-card event">
                            <div class="stat-icon">📅</div>
                            <div class="stat-content">
                                <p class="stat-label">Events</p>
                                <p class="stat-value"><?php echo $event_count; ?></p>
                            </div>
                        </div>

                        <div class="stat-card grade">
                            <div class="stat-icon">📝</div>
                            <div class="stat-content">
                                <p class="stat-label">Grades</p>
                                <p class="stat-value"><?php echo $grade_count; ?></p>
                            </div>
                        </div>

                        <div class="stat-card schedule">
                            <div class="stat-icon">⏰</div>
                            <div class="stat-content">
                                <p class="stat-label">Schedules</p>
                                <p class="stat-value"><?php echo $schedule_count; ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-grid">
                        <a href="users.php" class="action-card">
                            <span class="action-icon">➕</span>
                            <span class="action-text">Manage Users</span>
                        </a>
                        <a href="events.php" class="action-card">
                            <span class="action-icon">📅</span>
                            <span class="action-text">Manage Events</span>
                        </a>
                        <a href="grades.php" class="action-card">
                            <span class="action-icon">📊</span>
                            <span class="action-text">Manage Grades</span>
                        </a>
                        <a href="schedules.php" class="action-card">
                            <span class="action-icon">⏰</span>
                            <span class="action-text">Manage Schedules</span>
                        </a>
                    </div>
                </section>

                <!-- Recent Events -->
                <?php if (count($recent_events) > 0): ?>
                    <section class="recent-section">
                        <h2>Recent Events</h2>
                        <div class="recent-list">
                            <?php foreach ($recent_events as $event): ?>
                                <div class="recent-item">
                                    <div class="recent-content">
                                        <h3><?php echo htmlspecialchars($event['location']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($event['description'], 0, 60)); ?>...</p>
                                        <span class="recent-date">
                                            <?php echo date('M d, Y @ g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?>
                                        </span>
                                    </div>
                                    <a href="events.php?edit=<?php echo $event['id']; ?>" class="recent-action">Edit</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- System Info -->
                <section class="system-info">
                    <h2>System Information</h2>
                    <div class="info-grid">
                        <div class="info-card">
                            <p><strong>Version:</strong> 1.0</p>
                            <p><strong>Database:</strong> qc_portal</p>
                        </div>
                        <div class="info-card">
                            <p><strong>Last Updated:</strong> <?php echo date('M d, Y'); ?></p>
                            <p><strong>Server:</strong> PHP <?php echo phpversion(); ?></p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
