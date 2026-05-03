<?php
require_once '../php/config/db.php';
require_once '../php/auth.php';

requireAdmin();

$admin = getCurrentUser();
$admin_user = $admin['first_name'] . ' ' . $admin['last_name'];

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $event_id = (int)$_POST['event_id'];
    
    try {
        // Get image path before deletion
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if ($event && $event['image'] && file_exists("../uploads/" . $event['image'])) {
            unlink("../uploads/" . $event['image']);
        }
        
        // Delete event
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        
        $_SESSION['message'] = 'Event deleted successfully!';
        $_SESSION['msg_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error deleting event: ' . $e->getMessage();
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: events.php');
    exit;
}

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['create', 'update'])) {
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : null;
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate inputs
    if (!$event_date || !$event_time || !$location || !$description) {
        $_SESSION['message'] = 'All fields are required!';
        $_SESSION['msg_type'] = 'error';
        header('Location: events.php');
        exit;
    }
    
    $image_name = null;
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $_SESSION['message'] = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.';
            $_SESSION['msg_type'] = 'error';
            header('Location: events.php');
            exit;
        }
        
        if ($_FILES['image']['size'] > $max_size) {
            $_SESSION['message'] = 'File size exceeds 5MB limit.';
            $_SESSION['msg_type'] = 'error';
            header('Location: events.php');
            exit;
        }
        
        $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['image']['name']));
        $upload_path = "../uploads/" . $image_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $_SESSION['message'] = 'Error uploading image.';
            $_SESSION['msg_type'] = 'error';
            header('Location: events.php');
            exit;
        }
    }
    
    try {
        if ($_POST['action'] === 'create') {
            $stmt = $pdo->prepare("
                INSERT INTO events (event_date, event_time, location, description, image, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$event_date, $event_time, $location, $description, $image_name, $admin_user]);
            $_SESSION['message'] = 'Event created successfully!';
        } else {
            // Update
            if ($image_name) {
                // Delete old image
                $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
                $stmt->execute([$event_id]);
                $old_event = $stmt->fetch();
                
                if ($old_event['image'] && file_exists("../uploads/" . $old_event['image'])) {
                    unlink("../uploads/" . $old_event['image']);
                }
                
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET event_date = ?, event_time = ?, location = ?, description = ?, image = ?
                    WHERE id = ?
                ");
                $stmt->execute([$event_date, $event_time, $location, $description, $image_name, $event_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET event_date = ?, event_time = ?, location = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([$event_date, $event_time, $location, $description, $event_id]);
            }
            $_SESSION['message'] = 'Event updated successfully!';
        }
        $_SESSION['msg_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = 'Database error: ' . $e->getMessage();
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: events.php');
    exit;
}

// Fetch all events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

// Get event for editing (if editing)
$edit_event = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_event = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Events Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/events-admin-style.css">
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
                <a href="events.php" class="nav-item active">
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
                <h1>Events Management</h1>
                <div class="admin-info">
                    <span><?php echo htmlspecialchars($admin_user); ?></span>
                </div>
            </header>

            <!-- Message Display -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="content-wrapper">
                <!-- Form Section -->
                <section class="form-section">
                    <div class="form-header">
                        <h2><?php echo $edit_event ? 'Edit Event' : 'Create New Event'; ?></h2>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="event-form">
                        <input type="hidden" name="action" value="<?php echo $edit_event ? 'update' : 'create'; ?>">
                        <?php if ($edit_event): ?>
                            <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="event_date">Event Date *</label>
                                <input type="date" id="event_date" name="event_date" 
                                    value="<?php echo $edit_event['event_date'] ?? ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="event_time">Event Time *</label>
                                <input type="time" id="event_time" name="event_time" 
                                    value="<?php echo $edit_event['event_time'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" placeholder="e.g., Room 301, Engineering Building"
                                value="<?php echo $edit_event['location'] ?? ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" placeholder="Event details..." rows="5" required><?php echo $edit_event['description'] ?? ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Event Image</label>
                            <div class="image-upload">
                                <input type="file" id="image" name="image" accept="image/*">
                                <small>Max size: 5MB. Formats: JPG, PNG, GIF, WEBP</small>
                                <?php if ($edit_event && $edit_event['image']): ?>
                                    <div class="current-image">
                                        <img src="../uploads/<?php echo htmlspecialchars($edit_event['image']); ?>" alt="Current image">
                                        <p>Current: <?php echo htmlspecialchars($edit_event['image']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_event ? 'Update Event' : 'Create Event'; ?>
                            </button>
                            <?php if ($edit_event): ?>
                                <a href="events.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <!-- Events Table Section -->
                <section class="table-section">
                    <div class="table-header">
                        <h2>All Events</h2>
                        <span class="event-count"><?php echo count($events); ?> events</span>
                    </div>

                    <?php if (count($events) > 0): ?>
                        <div class="table-responsive">
                            <table class="events-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td data-label="Date">
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                            </td>
                                            <td data-label="Time">
                                                <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                            </td>
                                            <td data-label="Location">
                                                <?php echo htmlspecialchars(substr($event['location'], 0, 30)); ?>
                                            </td>
                                            <td data-label="Description">
                                                <?php echo htmlspecialchars(substr($event['description'], 0, 50)); ?>...
                                            </td>
                                            <td data-label="Image">
                                                <?php if ($event['image']): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($event['image']); ?>" 
                                                        alt="Event image" class="table-image">
                                                <?php else: ?>
                                                    <span class="no-image">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Created By">
                                                <?php echo htmlspecialchars($event['created_by']); ?>
                                            </td>
                                            <td data-label="Actions">
                                                <a href="?edit=<?php echo $event['id']; ?>" class="btn-action edit">Edit</a>
                                                <button class="btn-action delete" 
                                                    onclick="confirmDelete(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['location']); ?>')">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-events">
                            <p>No events found. Create your first event!</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this event?</p>
            <p id="eventName" style="font-weight: bold; color: #f0a500;"></p>
            <div class="modal-actions">
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="eventIdInput" name="event_id">
                    <button type="submit" class="btn btn-delete">Delete</button>
                </form>
                <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(eventId, eventName) {
            document.getElementById('eventIdInput').value = eventId;
            document.getElementById('eventName').textContent = eventName;
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

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
