<?php
// ============================================================
//  config/db.php
//  Database connection file for QCUS-PORTAL
//  Include this at the top of any PHP file that needs the DB:
//    require_once '../config/db.php';
// ============================================================
 
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Default XAMPP MySQL username
define('DB_PASS', '');            // Default XAMPP MySQL password (empty)
define('DB_NAME', 'qc_portal');   // Updated to use new database schema
 
// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'error' => 'Database connection failed: ' . $conn->connect_error
    ]));
}
 
// Set character encoding to UTF-8
$conn->set_charset('utf8mb4');

// Helper function to get current user from session
function currentUser() {
    return isset($_SESSION['student_id']) ? [
        'id' => $_SESSION['user_id'],
        'student_id' => $_SESSION['student_id'],
        'name' => $_SESSION['full_name'],
        'email' => $_SESSION['email']
    ] : null;
}

// Helper function to redirect
function redirect($url) {
    header('Location: ' . $url);
    exit();
}
?>