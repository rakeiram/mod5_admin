<?php
include 'auth.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];

    // Fetch current admin data to preserve existing values
    $sql = "SELECT name, email, avatar_url FROM admins WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // Use existing values as defaults
    $name = $admin['name'];
    $email = $admin['email'];
    $avatar_url = $admin['avatar_url'] ?? '';

    // Update only if new values are provided
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
    }

    // Handle avatar upload
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create uploads directory if it doesn't exist
        }
        $file_name = time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate Image File
        $check = getimagesize($_FILES["avatar"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $avatar_url = $target_file; // Store file path in avatar_url
            } else {
                $_SESSION['error_message'] = "Error uploading image.";
                header("Location: admin_profile.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "File is not an image.";
            header("Location: admin_profile.php");
            exit();
        }
    }

    // Prepare the SQL statement to update name, email, and avatar_url
    $sql = "UPDATE admins SET name = ?, email = ?, avatar_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $avatar_url, $admin_id);

    if ($stmt->execute()) {
        // Record Activity Log
        $conn->query("INSERT INTO activity_log (admin_id, action) VALUES ('$admin_id', 'Updated Profile')");
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: admin_profile.php");
    exit();
}
?>