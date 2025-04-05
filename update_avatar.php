<?php
include 'auth.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];
    
    // Handle avatar upload
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["avatar"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                // Update avatar_url in database
                $sql = "UPDATE admins SET avatar_url = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $target_file, $admin_id);
                $stmt->execute();
                $stmt->close();
                
                // Record Activity Log
                $conn->query("INSERT INTO activity_log (admin_id, action) VALUES ('$admin_id', 'Updated Avatar')");
                
                $_SESSION['success_message'] = "Avatar updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error uploading image.";
            }
        } else {
            $_SESSION['error_message'] = "File is not an image.";
        }
    } else {
        $_SESSION['error_message'] = "No file uploaded.";
    }
    
    header("Location: admin_profile.php");
    exit();
}
?>