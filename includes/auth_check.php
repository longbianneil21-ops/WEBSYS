<?php
// ============================================================
//  includes/auth_check.php
//  Session guard — include this at the top of every
//  student page BEFORE any HTML output.
//
//  Usage:
//    require_once '../includes/auth_check.php';
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If student is not logged in, redirect to login page
if (!isset($_SESSION['student_id'])) {
    header('Location: ../landingpage/login.php');
    exit();
}

// Make session data easy to access on any page
$session_student_id = $_SESSION['student_id'];   // e.g. 25-0134
$session_user_id    = $_SESSION['user_id'];       // DB primary key (id)
$session_name       = $_SESSION['full_name'];     // e.g. Juan Dela Cruz
?>