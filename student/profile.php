<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireLogin();

$student = getCurrentUser();

// Fetch full student details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student['id']]);
$student_data = $stmt->fetch();

// Check for Digital ID
$stmt = $pdo->prepare("SELECT * FROM digital_ids WHERE student_id = ?");
$stmt->execute([$student['id']]);
$digital_id = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - My Profile</title>
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
                <a href="schedules.php" class="nav-item">
                    <span class="icon">⏰</span>
                    <span>Schedule</span>
                </a>
                <a href="profile.php" class="nav-item active">
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
                    <h1>My Profile</h1>
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
                <!-- Profile Card Section -->
                <section class="profile-section">
                    <div class="profile-header">
                        <div class="profile-avatar-large">
                            <?php echo substr($student_data['first_name'], 0, 1) . substr($student_data['last_name'], 0, 1); ?>
                        </div>
                        <div class="profile-name-card">
                            <h2><?php echo htmlspecialchars($student_data['first_name'] . ' ' . $student_data['last_name']); ?></h2>
                            <p class="program"><?php echo htmlspecialchars($student_data['program'] ?: 'Program not set'); ?></p>
                            <p class="year-level"><?php echo htmlspecialchars($student_data['year_level'] ?: 'Year level not set'); ?></p>
                        </div>
                    </div>
                </section>

                <!-- Personal Information -->
                <section class="info-section">
                    <h2>Personal Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>First Name</label>
                            <p><?php echo htmlspecialchars($student_data['first_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Last Name</label>
                            <p><?php echo htmlspecialchars($student_data['last_name']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email Address</label>
                            <p><?php echo htmlspecialchars($student_data['email']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Username</label>
                            <p><?php echo htmlspecialchars($student_data['username']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Gender</label>
                            <p><?php echo htmlspecialchars($student_data['gender'] ?: 'Not specified'); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Birth Date</label>
                            <p>
                                <?php 
                                    if ($student_data['birth_date']) {
                                        echo date('M d, Y', strtotime($student_data['birth_date']));
                                    } else {
                                        echo 'Not specified';
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Academic Information -->
                <section class="info-section">
                    <h2>Academic Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Student ID</label>
                            <p><?php echo htmlspecialchars($student_data['student_id'] ?: 'N/A'); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Program</label>
                            <p><?php echo htmlspecialchars($student_data['program'] ?: 'Not specified'); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Year Level</label>
                            <p><?php echo htmlspecialchars($student_data['year_level'] ?: 'Not specified'); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Phone Number</label>
                            <p><?php echo htmlspecialchars($student_data['phone'] ?: 'Not specified'); ?></p>
                        </div>
                    </div>
                </section>

                <!-- Address Information -->
                <section class="info-section">
                    <h2>Address</h2>
                    <div class="address-card">
                        <p><?php echo htmlspecialchars($student_data['address'] ?: 'Address not specified'); ?></p>
                    </div>
                </section>

                <!-- Digital ID Section (Placeholder) -->
                <section class="digital-id-section">
                    <div class="digital-id-card">
                        <h2>🪪 Digital Student ID</h2>
                        <div class="digital-id-placeholder">
                            <?php if ($digital_id && $digital_id['status'] === 'active'): ?>
                                <div class="id-content">
                                    <p class="id-status active">✓ Active</p>
                                    <div class="qr-code">
                                        <p>📱 QR Code Placeholder</p>
                                        <p class="qr-data"><?php echo htmlspecialchars($digital_id['qr_code_data'] ?: 'ID-' . $student_data['student_id']); ?></p>
                                    </div>
                                    <div class="id-validity">
                                        <p><strong>Valid From:</strong> <?php echo $digital_id['valid_from'] ? date('M d, Y', strtotime($digital_id['valid_from'])) : 'N/A'; ?></p>
                                        <p><strong>Valid Until:</strong> <?php echo $digital_id['valid_until'] ? date('M d, Y', strtotime($digital_id['valid_until'])) : 'N/A'; ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="id-content">
                                    <p class="id-status pending">⚠ Pending Generation</p>
                                    <div class="qr-code">
                                        <p>📱 QR Code Will Appear Here</p>
                                        <p class="qr-placeholder">(Awaiting admin setup)</p>
                                    </div>
                                    <p class="info-text">
                                        Your Digital Student ID is being prepared. 
                                        Once generated, you'll be able to download and use it for campus access and transactions.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Account Information -->
                <section class="info-section">
                    <h2>Account Information</h2>
                    <div class="account-info">
                        <div class="info-item">
                            <label>Account Status</label>
                            <p>
                                <span class="badge <?php echo $student_data['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $student_data['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <p><?php echo date('M d, Y', strtotime($student_data['created_at'])); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Last Updated</label>
                            <p><?php echo date('M d, Y @ g:i A', strtotime($student_data['updated_at'])); ?></p>
                        </div>
                    </div>
                </section>

                <!-- Info Message -->
                <section class="info-message">
                    <p>💡 <strong>Note:</strong> To update your profile information, please contact the Student Services Office or your academic advisor.</p>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
