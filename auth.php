<?php
session_start();

// Security: Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'config.php';

// Record login in activity log if not already recorded
if (!isset($_SESSION['login_recorded'])) {
    $admin_id = $_SESSION['admin_id'];
    $conn->query("INSERT INTO activity_log (admin_id, action) VALUES ('$admin_id', 'Logged in')");
    $_SESSION['login_recorded'] = true;
}
?>
