<?php
require_once '../php/config/db.php';

// Fetch all upcoming and past events, sorted by date (newest first)
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

// Separate upcoming and past events
$upcoming_events = [];
$past_events = [];
$today = date('Y-m-d');

foreach ($events as $event) {
    if ($event['event_date'] >= $today) {
        $upcoming_events[] = $event;
    } else {
        $past_events[] = $event;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/events-student-style.css">
</head>
<body>
    <div class="student-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="logo-icon">📚</div>
                <div>
                    <h2>QCU PORTAL</h2>
                    <p>Student Dashboard</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="../dashboard/dashboard.html" class="nav-item">
                    <span class="icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="events.php" class="nav-item active">
                    <span class="icon">📅</span>
                    <span>Events</span>
                </a>
                <a href="../dashboard/registration.html" class="nav-item">
                    <span class="icon">📋</span>
                    <span>Registration</span>
                </a>
                <a href="../dashboard/grades.html" class="nav-item">
                    <span class="icon">📝</span>
                    <span>Grades</span>
                </a>
                <a href="../dashboard/digital-id.html" class="nav-item">
                    <span class="icon">🪪</span>
                    <span>Digital ID</span>
                </a>
                <a href="../dashboard/account.html" class="nav-item">
                    <span class="icon">👤</span>
                    <span>Account</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="../landingpage/home.html" class="logout-link">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="header-left">
                    <h1>Events & Activities</h1>
                    <p class="subtitle">Stay updated with university events</p>
                </div>
                <div class="header-right">
                    <div class="profile-card">
                        <div class="avatar">NL</div>
                        <div class="profile-info">
                            <p class="name">Neil Longbian</p>
                            <p class="email">neillongbian@gmail.com</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Upcoming Events Section -->
                <section class="events-section">
                    <div class="section-header">
                        <h2>📅 Upcoming Events</h2>
                        <span class="event-badge"><?php echo count($upcoming_events); ?></span>
                    </div>

                    <?php if (count($upcoming_events) > 0): ?>
                        <div class="events-grid">
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="event-card upcoming">
                                    <?php if ($event['image']): ?>
                                        <div class="event-image">
                                            <img src="../uploads/<?php echo htmlspecialchars($event['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($event['location']); ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="event-image placeholder">
                                            <span>📅</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="event-content">
                                        <div class="event-meta">
                                            <span class="event-tag academic">
                                                <span class="tag-dot"></span>
                                                Academic
                                            </span>
                                            <span class="event-date">
                                                <?php echo date('M d', strtotime($event['event_date'])); ?>
                                            </span>
                                        </div>

                                        <h3 class="event-title"><?php echo htmlspecialchars(substr($event['location'], 0, 40)); ?></h3>

                                        <div class="event-details">
                                            <div class="detail-item">
                                                <span class="detail-icon">🕐</span>
                                                <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-icon">📍</span>
                                                <span><?php echo htmlspecialchars($event['location']); ?></span>
                                            </div>
                                        </div>

                                        <p class="event-description">
                                            <?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>
                                            <?php if (strlen($event['description']) > 100): ?>...<?php endif; ?>
                                        </p>

                                        <div class="event-actions">
                                            <button class="btn-view-details" onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                                View Details
                                            </button>
                                            <button class="btn-add-calendar">+ Calendar</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-events-message">
                            <p>🎉 No upcoming events at the moment.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Past Events Section -->
                <?php if (count($past_events) > 0): ?>
                    <section class="events-section past-section">
                        <div class="section-header">
                            <h2>📸 Past Events</h2>
                            <span class="event-badge secondary"><?php echo count($past_events); ?></span>
                        </div>

                        <div class="events-grid">
                            <?php foreach ($past_events as $event): ?>
                                <div class="event-card past">
                                    <?php if ($event['image']): ?>
                                        <div class="event-image">
                                            <img src="../uploads/<?php echo htmlspecialchars($event['image']); ?>" 
                                                alt="<?php echo htmlspecialchars($event['location']); ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="event-image placeholder">
                                            <span>📸</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="event-content">
                                        <div class="event-meta">
                                            <span class="event-tag past-tag">
                                                <span class="tag-dot"></span>
                                                Past Event
                                            </span>
                                            <span class="event-date">
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                            </span>
                                        </div>

                                        <h3 class="event-title"><?php echo htmlspecialchars(substr($event['location'], 0, 40)); ?></h3>

                                        <div class="event-details">
                                            <div class="detail-item">
                                                <span class="detail-icon">🕐</span>
                                                <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-icon">📍</span>
                                                <span><?php echo htmlspecialchars($event['location']); ?></span>
                                            </div>
                                        </div>

                                        <p class="event-description">
                                            <?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>
                                            <?php if (strlen($event['description']) > 100): ?>...<?php endif; ?>
                                        </p>

                                        <button class="btn-view-details" onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Event Details Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeEventModal()">&times;</button>
            
            <div id="modalBody">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function showEventDetails(event) {
            const modal = document.getElementById('eventModal');
            const modalBody = document.getElementById('modalBody');
            
            const eventDate = new Date(event.event_date);
            const formattedDate = eventDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            const formattedTime = new Date('2000-01-01 ' + event.event_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            
            let imageHtml = '';
            if (event.image) {
                imageHtml = `<img src="../uploads/${escapeHtml(event.image)}" alt="Event" class="modal-image">`;
            } else {
                imageHtml = '<div class="modal-image placeholder"><span>📅</span></div>';
            }
            
            modalBody.innerHTML = `
                ${imageHtml}
                <div class="modal-details">
                    <h2>${escapeHtml(event.location)}</h2>
                    
                    <div class="detail-block">
                        <strong>📅 Date:</strong>
                        <p>${formattedDate}</p>
                    </div>
                    
                    <div class="detail-block">
                        <strong>🕐 Time:</strong>
                        <p>${formattedTime}</p>
                    </div>
                    
                    <div class="detail-block">
                        <strong>📍 Location:</strong>
                        <p>${escapeHtml(event.location)}</p>
                    </div>
                    
                    <div class="detail-block">
                        <strong>📝 Description:</strong>
                        <p>${escapeHtml(event.description)}</p>
                    </div>
                    
                    <div class="detail-block">
                        <strong>👤 Created By:</strong>
                        <p>${escapeHtml(event.created_by)}</p>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="btn-add-calendar-large" onclick="addToCalendar('${escapeHtml(event.location)}', '${event.event_date}', '${event.event_time}')">
                            + Add to Calendar
                        </button>
                        <button class="btn-close-modal" onclick="closeEventModal()">Close</button>
                    </div>
                </div>
            `;
            
            modal.style.display = 'flex';
        }

        function closeEventModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function addToCalendar(title, date, time) {
            alert(`Added "${title}" to your calendar for ${date} at ${time}`);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
