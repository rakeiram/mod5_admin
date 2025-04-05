<?php
// Start the session
session_start();

// Include database configuration
include 'config.php';

// Record logout in activity_log if admin_id exists
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action) VALUES (?, ?)");
    if ($stmt) {
        $action = "Logged out";
        $stmt->bind_param("ss", $admin_id, $action);
        $stmt->execute();
        $stmt->close();
    } else {
        // Optional: Log error if statement preparation fails
        error_log("Failed to prepare statement in logout.php: " . $conn->error);
    }
}

// Destroy the session
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: login.php");
exit();
?>