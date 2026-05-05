<?php
require_once '../includes/auth_check.php';
require_once '../config/config.php';

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in → redirect
$u = currentUser();
if ($u) redirect('dashboard.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        // Check users table first
        $stmt = $conn->prepare("SELECT id, email, first_name, last_name, password FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['student_id'] = $email;
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['email'] = $user['email'];
                redirect('WEBSYS/dashboard/dashboard.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            // Check admins table
            $stmt = $conn->prepare("SELECT id, email, first_name, last_name, password FROM admins WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Set session for admin
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['is_admin'] = true;
                    redirect('WEBSYS/dashboard/admin-class-schedule.php');
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'No account found with that email.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="../images/QCU-logo.png">
  <link rel="stylesheet" href="../styles/login-style.css">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QCU Student Portal — Log In</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    .error-msg { background: #fee; border: 1px solid #fcc; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
  </style>
</head>
<body>

<div class="card">

  <!-- LEFT -->
  <div class="left">
    <div class="deco-arrows">
      <img src="../images/QCU-background.png" alt="">
    </div>

    <div class="logo-wrap">
      <div class="logo-circle">
        <img src="../images/QCU-logo.png" alt="University Logo" />
      </div>
    </div>

    <div class="left-content">
      <h1 class="welcome-title">Welcome Back!</h1>
      <p class="univ-name">Quezon City University</p>
      <p class="portal-label">Student Portal</p>

      <ul class="features">
        <li>
          <span class="check-circle">
            <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Access your grades &amp; schedules
        </li>
        <li>
          <span class="check-circle">
            <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Request documents online
        </li>
        <li>
          <span class="check-circle">
            <svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          Secure &amp; transparent transactions
        </li>
      </ul>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right">
    <h2 class="form-title">Log In</h2>
    <p class="form-subtitle">Enter your credentials to access your account</p>

    <?php if ($error): ?>
    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field-group">
        <label class="field-label" for="email">Email Address</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
              <rect x="2" y="4" width="12" height="8" rx="1"/>
              <path d="M2 4l6 4 6-4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <input class="field-input" type="email" id="email" name="email" placeholder="you@qcu.edu.ph" required value="<?php echo htmlspecialchars($email); ?>" />
        </div>
      </div>

      <div class="field-group">
        <label class="field-label" for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="7" width="10" height="8" rx="2"/>
              <path d="M5.5 7V5a2.5 2.5 0 015 0v2" stroke-linecap="round"/>
            </svg>
          </span>
          <input class="field-input" type="password" id="password" name="password" placeholder="Enter your password" required />
          <button class="eye-btn" id="eyeBtn" type="button" aria-label="Toggle password visibility">
            <svg id="eyeIcon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/>
              <circle cx="8" cy="8" r="2"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-login">Log In</button>
    </form>

    <p class="form-footer">
      Don't have an account? <a href="sign-up.php">Create one here</a>
    </p>
  </div>
</div>

<script>
  // Password toggle
  const eyeBtn = document.getElementById('eyeBtn');
  const passwordField = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  if (eyeBtn) {
    eyeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isPassword = passwordField.type === 'password';
      passwordField.type = isPassword ? 'text' : 'password';
      eyeIcon.style.opacity = isPassword ? 0.5 : 1;
    });
  }
</script>
</body>
</html>
