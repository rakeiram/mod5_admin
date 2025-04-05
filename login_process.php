<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: admin_dashboard.php"); // Redirect to Dashboard
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='admin_login.php';</script>";
        }
    } else {
        echo "<script>alert('Admin not found!'); window.location.href='admin_login.php';</script>";
    }
}
?>
