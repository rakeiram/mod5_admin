<?php
include 'auth.php';
include 'config.php';

header('Content-Type: application/json');

$admin_id = $_SESSION['admin_id'];
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($old_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Both old and new passwords are required.']);
    exit();
}

// Fetch current password
$stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin || !password_verify($old_password, $admin['password'])) {
    echo json_encode(['success' => false, 'message' => 'Old password is incorrect.']);
    exit();
}

// Validate new password (server-side)
if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long and include an uppercase letter, a number, and a special character.']);
    exit();
}

// Update password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_password_hash, $admin_id);

if ($stmt->execute()) {
    // Log the action
    $log_stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action) VALUES (?, ?)");
    $action = "Changed Password";
    $log_stmt->bind_param("is", $admin_id, $action);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
}

$stmt->close();
$conn->close();
?>