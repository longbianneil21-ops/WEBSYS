<?php
require_once '../config/config.php';

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in → redirect
$u = currentUser();
if ($u) redirect('WEBSYS/dashboard/dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim(htmlspecialchars($_POST['firstName'] ?? '', ENT_QUOTES, 'UTF-8'));
    $lastName = trim(htmlspecialchars($_POST['lastName'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $section = $_POST['section'] ?? 'SBIT-1A';
    
    // Validation
    if (!$firstName || !$lastName) {
        $error = 'First and last name are required.';
    } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!$password || strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered. Try logging in or use a different email.';
        } else {
            // Create account
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, section, status) VALUES (?, ?, ?, ?, ?, 'Active')");
            $stmt->bind_param('sssss', $email, $hashedPassword, $firstName, $lastName, $section);
            
            if ($stmt->execute()) {
                // Set session and redirect
                $newUserId = $conn->insert_id;
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['student_id'] = $email;
                $_SESSION['full_name'] = $firstName . ' ' . $lastName;
                $_SESSION['email'] = $email;
                $success = 'Account created! Redirecting...';
                redirect('WEBSYS/dashboard/dashboard.php');
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="../styles/signup-style.css">
  <link rel="icon" type="image/png" href="../images/QCU-logo.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QCU Student Portal — Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    .error-msg { background: #fee; border: 1px solid #fcc; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
    .success-msg { background: #ecfdf5; border: 1px solid #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
  </style>
</head>
<body>

<div class="wrapper">

  <!-- ── LEFT — FORM ── -->
  <div class="left">
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Fill in your details to get started</p>

    <?php if ($error): ?>
    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST">
      <!-- First & Last Name -->
      <div class="row-two">
        <div class="field-group">
          <label class="field-label" for="firstName">First Name <span class="req">*</span></label>
          <div class="input-wrap">
            <input class="field-input" type="text" id="firstName" name="firstName" placeholder="Juan" required />
          </div>
        </div>
        <div class="field-group">
          <label class="field-label" for="lastName">Last Name <span class="req">*</span></label>
          <div class="input-wrap">
            <input class="field-input" type="text" id="lastName" name="lastName" placeholder="Dela Cruz" required />
          </div>
        </div>
      </div>

      <!-- QCU Email -->
      <div class="field-group">
        <label class="field-label" for="email">QCU Email Address <span class="req">*</span></label>
        <div class="input-wrap">
          <input class="field-input" type="email" id="email" name="email" placeholder="your.name@qcu.edu.ph" required />
        </div>
        <p class="field-hint">Use your official school-issued email address</p>
      </div>

      <!-- Section -->
      <div class="field-group">
        <label class="field-label" for="section">Section <span class="req">*</span></label>
        <div class="input-wrap">
          <select class="field-input" id="section" name="section" required>
            <option value="SBIT-1A">SBIT-1A</option>
            <option value="SBIT-1B">SBIT-1B</option>
            <option value="SBIT-1C">SBIT-1C</option>
            <option value="SBIT-1D">SBIT-1D</option>
            <option value="SBIT-1E">SBIT-1E</option>
            <option value="SBIT-1F">SBIT-1F</option>
          </select>
        </div>
      </div>

      <!-- Password -->
      <div class="field-group">
        <label class="field-label" for="password">Password <span class="req">*</span></label>
        <div class="input-wrap">
          <input class="field-input" type="password" id="password" name="password" placeholder="Create a strong password" required />
          <button class="eye-btn" id="eyeBtn1" type="button" aria-label="Toggle password">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/></svg>
          </button>
        </div>
        <p class="field-hint">At least 8 characters</p>
      </div>

      <!-- Confirm Password -->
      <div class="field-group">
        <label class="field-label" for="confirmPassword">Confirm Password <span class="req">*</span></label>
        <div class="input-wrap">
          <input class="field-input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required />
          <button class="eye-btn" id="eyeBtn2" type="button" aria-label="Toggle confirm password">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/></svg>
          </button>
        </div>
      </div>

      <!-- Terms -->
      <div class="terms-row">
        <input type="checkbox" id="terms" name="terms" required />
        <span>I agree to the <a href="#">Terms and Conditions</a></span>
      </div>

      <button class="btn-create" type="submit">Create Account</button>
    </form>

    <div class="divider">Already have an account?</div>

    <a href="login.php">
      <button class="btn-login" type="button">Log In</button>
    </a>

    <a href="home.php" class="back-home">Back to Home</a>
  </div>

  <!-- ── RIGHT — INFO ── -->
  <div class="right">
    <img class="right-bg" src="../images/QCU-background.png" alt="" />
    <div class="right-overlay"></div>
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>

    <div class="logo-wrap">
      <div class="logo-circle">
        <img src="../images/QCU-logo.png" alt="University Logo" />
      </div>
    </div>

    <div class="right-content">
      <h1 class="join-title">Join QCU Portal!</h1>
      <p class="univ-name">Quezon City University</p>
      <p class="portal-label">Student Portal</p>

      <p class="steps-title">Get Started in 3 Steps:</p>
      <ul class="steps">
        <li>
          <span class="step-num">1</span>
          <div class="step-text">
            <strong>Fill the Sign-Up Form</strong>
            <span>Enter your details</span>
          </div>
        </li>
        <li>
          <span class="step-num">2</span>
          <div class="step-text">
            <strong>Verify Your Email</strong>
            <span>Check your QCU inbox</span>
          </div>
        </li>
        <li>
          <span class="step-num">3</span>
          <div class="step-text">
            <strong>Log In &amp; Explore</strong>
            <span>Access all features</span>
          </div>
        </li>
      </ul>
    </div>
  </div>

</div>

<script>
  // Password toggle
  const eyeBtn1 = document.getElementById('eyeBtn1');
  const eyeBtn2 = document.getElementById('eyeBtn2');
  const passwordField = document.getElementById('password');
  const confirmField = document.getElementById('confirmPassword');

  if (eyeBtn1) {
    eyeBtn1.addEventListener('click', (e) => {
      e.preventDefault();
      passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    });
  }

  if (eyeBtn2) {
    eyeBtn2.addEventListener('click', (e) => {
      e.preventDefault();
      confirmField.type = confirmField.type === 'password' ? 'text' : 'password';
    });
  }
</script>

</body>
</html>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../styles/signup-style.css">
    <link rel="icon" type="image/png" href="../images/QCU-logo.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QCU Student Portal — Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
  
  </style>
</head>
<body>

<div class="wrapper">

  <!-- ── LEFT — FORM ── -->
  <div class="left">
    <h2 class="form-title">Create Account</h2>
    <p class="form-subtitle">Fill in your details to get started</p>

    <!-- Student ID -->
    <div class="field-group">
      <label class="field-label" for="studentId">Student ID Number <span class="req">*</span></label>
      <div class="input-wrap">
        <input class="field-input" type="text" id="studentId" placeholder="2X-XXXX" autocomplete="off" />
      </div>
      <p class="field-hint">Check your admission documents or visit the Registrar's Office</p>
    </div>

    <!-- First & Last Name -->
    <div class="row-two">
      <div class="field-group">
        <label class="field-label" for="firstName">First Name <span class="req">*</span></label>
        <div class="input-wrap">
          <input class="field-input" type="text" id="firstName" placeholder="Juan" autocomplete="given-name" />
        </div>
      </div>
      <div class="field-group">
        <label class="field-label" for="lastName">Last Name <span class="req">*</span></label>
        <div class="input-wrap">
          <input class="field-input" type="text" id="lastName" placeholder="Dela Cruz" autocomplete="family-name" />
        </div>
      </div>
    </div>

    <!-- QCU Email -->
    <div class="field-group">
      <label class="field-label" for="email">QCU Email Address <span class="req">*</span></label>
      <div class="input-wrap">
        <input class="field-input" type="email" id="email" placeholder="your.name@qcu.edu.ph" autocomplete="email" />
      </div>
      <p class="field-hint">Use your official school-issued email address</p>
    </div>

    <!-- Password -->
    <div class="field-group">
      <label class="field-label" for="password">Password <span class="req">*</span></label>
      <div class="input-wrap">
        <input class="field-input" type="password" id="password" placeholder="Create a strong password" autocomplete="new-password" />
        <button class="eye-btn" id="eyeBtn1" type="button" aria-label="Toggle password">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/></svg>
        </button>
      </div>
      <p class="field-hint">At least 8 characters with numbers and symbols</p>
    </div>

    <!-- Confirm Password -->
    <div class="field-group">
      <label class="field-label" for="confirmPassword">Confirm Password <span class="req">*</span></label>
      <div class="input-wrap">
        <input class="field-input" type="password" id="confirmPassword" placeholder="Re-enter your password" autocomplete="new-password" />
        <button class="eye-btn" id="eyeBtn2" type="button" aria-label="Toggle confirm password">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/></svg>
        </button>
      </div>
    </div>

    <!-- Terms -->
    <div class="terms-row">
      <input type="checkbox" id="terms" />
      <span>I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a></span>
    </div>

    <button class="btn-create" type="button">Create Account</button>

    <div class="divider">Already have an account?</div>

    <a href="login.html">
        <button class="btn-login" type="button">Log In</button>
    </a>

    <a href="home.html" class="back-home">Back to Home</a>
  </div>

  <!-- ── RIGHT — INFO ── -->
  <div class="right">
    <!-- Replace src below with your background image path -->
    <img class="right-bg" src="../images/QCU-background.png" alt="" />
    <div class="right-overlay"></div>
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>

    <!-- Replace src below with your logo path -->
    <div class="logo-wrap">
      <div class="logo-circle">
        <img src="../images/QCU-logo.png" alt="University Logo" />
      </div>
    </div>

    <div class="right-content">
      <h1 class="join-title">Join QCU Portal!</h1>
      <p class="univ-name">Quezon City University</p>
      <p class="portal-label">Student Portal</p>

      <p class="steps-title">Get Started in 4 Steps:</p>
      <ul class="steps">
        <li>
          <span class="step-num">1</span>
          <div class="step-text">
            <strong>Get Your Student ID</strong>
            <span>From admission documents</span>
          </div>
        </li>
        <li>
          <span class="step-num">2</span>
          <div class="step-text">
            <strong>Fill the Sign-Up Form</strong>
            <span>Enter your details</span>
          </div>
        </li>
        <li>
          <span class="step-num">3</span>
          <div class="step-text">
            <strong>Verify Your Email</strong>
            <span>Check your QCU inbox</span>
          </div>
        </li>
        <li>
          <span class="step-num">4</span>
          <div class="step-text">
            <strong>Log In &amp; Explore</strong>
            <span>Access all features</span>
          </div>
        </li>
      </ul>
    </div>
  </div>

</div>

<script src="../scripts/singup-script.js">
</script>

</body>
</html>