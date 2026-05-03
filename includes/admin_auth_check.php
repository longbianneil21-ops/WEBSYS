<?php
// ============================================================
//  includes/admin_auth_check.php
//  Session guard — include this at the top of every
//  admin page BEFORE any HTML output.
//
//  Usage:
//    require_once '../includes/admin_auth_check.php';
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin is not logged in, redirect to admin login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/login.php');
    exit();
}

// Make session data easy to access on any admin page
$session_admin_id   = $_SESSION['admin_id'];    // e.g. admin01
$session_admin_uid  = $_SESSION['admin_uid'];   // DB primary key (id)
$session_admin_name = $_SESSION['admin_name'];  // e.g. Portal Administrator
?>