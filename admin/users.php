<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireAdmin();

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create' || $action === 'update') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $role = trim($_POST['role'] ?? 'student');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // For student role
        $student_id = trim($_POST['student_id'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '');
        $year_level = trim($_POST['year_level'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if (!$username || !$email || !$first_name || !$last_name) {
            $_SESSION['message'] = 'All required fields must be filled.';
            $_SESSION['msg_type'] = 'error';
            header('Location: users.php');
            exit;
        }
        
        try {
            if ($action === 'create') {
                $password = isset($_POST['password']) ? $_POST['password'] : bin2hex(random_bytes(4));
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, role, is_active, 
                        student_id, phone, gender, birth_date, year_level, program, address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $username, $email, $hashed_password, $first_name, $last_name, $role, $is_active,
                    $student_id ?: null, $phone ?: null, $gender ?: null, $birth_date ?: null, 
                    $year_level ?: null, $program ?: null, $address ?: null
                ]);
                $_SESSION['message'] = 'User created successfully! Password: ' . (isset($_POST['password']) ? $password : 'Check email');
            } else {
                $user_id = (int)$_POST['user_id'];
                $update_password = isset($_POST['update_password']) && $_POST['update_password'] === 'yes';
                
                if ($update_password) {
                    $password = $_POST['password'];
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, password = ?, first_name = ?, last_name = ?, role = ?, is_active = ?,
                            student_id = ?, phone = ?, gender = ?, birth_date = ?, year_level = ?, program = ?, address = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $username, $email, $hashed_password, $first_name, $last_name, $role, $is_active,
                        $student_id ?: null, $phone ?: null, $gender ?: null, $birth_date ?: null, 
                        $year_level ?: null, $program ?: null, $address ?: null, $user_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, is_active = ?,
                            student_id = ?, phone = ?, gender = ?, birth_date = ?, year_level = ?, program = ?, address = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $username, $email, $first_name, $last_name, $role, $is_active,
                        $student_id ?: null, $phone ?: null, $gender ?: null, $birth_date ?: null, 
                        $year_level ?: null, $program ?: null, $address ?: null, $user_id
                    ]);
                }
                $_SESSION['message'] = 'User updated successfully!';
            }
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }
        header('Location: users.php');
        exit;
    }
    
    // Handle Delete
    if ($action === 'delete') {
        $user_id = (int)$_POST['user_id'];
        
        // Don't allow deletion of own account
        if ($user_id === $_SESSION['user_id']) {
            $_SESSION['message'] = 'You cannot delete your own account.';
            $_SESSION['msg_type'] = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['message'] = 'User deleted successfully!';
                $_SESSION['msg_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['message'] = 'Error: ' . $e->getMessage();
                $_SESSION['msg_type'] = 'error';
            }
        }
        header('Location: users.php');
        exit;
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Users Management</title>
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
                <a href="users.php" class="nav-item active">
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
                <h1>Users Management</h1>
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
                    <h2><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h2>
                    
                    <form method="POST" class="crud-form">
                        <input type="hidden" name="action" value="<?php echo $edit_user ? 'update' : 'create'; ?>">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="text" name="username" value="<?php echo $edit_user['username'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="first_name" value="<?php echo $edit_user['first_name'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" value="<?php echo $edit_user['last_name'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Role *</label>
                                <select name="role" required onchange="toggleStudentFields()">
                                    <option value="student" <?php echo ($edit_user['role'] ?? 'student') === 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_active" <?php echo ($edit_user['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    Active
                                </label>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <?php if (!$edit_user): ?>
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label>Password *</label>
                                    <input type="password" name="password" placeholder="Leave empty for random password" required>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="update_password" value="yes" onchange="togglePasswordField()">
                                        Change Password
                                    </label>
                                </div>
                            </div>
                            <div class="form-row" id="passwordField" style="display: none;">
                                <div class="form-group full-width">
                                    <label>New Password</label>
                                    <input type="password" name="password">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Student Fields -->
                        <div id="studentFields" style="display: <?php echo ($edit_user['role'] ?? 'student') === 'student' ? 'block' : 'none'; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Student ID</label>
                                    <input type="text" name="student_id" value="<?php echo $edit_user['student_id'] ?? ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" value="<?php echo $edit_user['phone'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($edit_user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($edit_user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($edit_user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Birth Date</label>
                                    <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Year Level</label>
                                    <select name="year_level">
                                        <option value="">Select Year</option>
                                        <option value="1st Year" <?php echo ($edit_user['year_level'] ?? '') === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2nd Year" <?php echo ($edit_user['year_level'] ?? '') === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3rd Year" <?php echo ($edit_user['year_level'] ?? '') === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4th Year" <?php echo ($edit_user['year_level'] ?? '') === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Program</label>
                                    <input type="text" name="program" value="<?php echo $edit_user['program'] ?? ''; ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label>Address</label>
                                    <textarea name="address" rows="2"><?php echo $edit_user['address'] ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                            </button>
                            <?php if ($edit_user): ?>
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <!-- Users Table -->
                <section class="table-section">
                    <h2>All Users (<?php echo count($users); ?>)</h2>
                    
                    <div class="table-responsive">
                        <table class="crud-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Student ID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['student_id'] ? htmlspecialchars($user['student_id']) : '-'; ?></td>
                                        <td>
                                            <span class="status <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $user['id']; ?>" class="btn-action edit">Edit</a>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <button class="btn-action delete" onclick="confirmDelete(<?php echo $user['id']; ?>)">Delete</button>
                                            <?php else: ?>
                                                <span class="btn-action disabled">Delete</span>
                                            <?php endif; ?>
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
            <p>Are you sure you want to delete this user?</p>
            <div class="modal-actions">
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="userIdInput" name="user_id">
                    <button type="submit" class="btn btn-delete">Delete</button>
                </form>
                <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function toggleStudentFields() {
            const role = document.querySelector('select[name="role"]').value;
            const studentFields = document.getElementById('studentFields');
            studentFields.style.display = role === 'student' ? 'block' : 'none';
        }

        function togglePasswordField() {
            const checkbox = document.querySelector('input[name="update_password"]');
            const passwordField = document.getElementById('passwordField');
            passwordField.style.display = checkbox.checked ? 'block' : 'none';
        }

        function confirmDelete(userId) {
            document.getElementById('userIdInput').value = userId;
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
