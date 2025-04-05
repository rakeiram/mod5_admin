<?php
include 'auth.php';
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Ensure the ID exists
    if (!isset($_POST['id'])) {
        echo "<script>alert('Invalid reservation.'); window.location.href='reservations.php';</script>";
        exit();
    }

    $admin_id = $_SESSION['admin_id']; // ✅ Get admin ID
    $id = $_POST['id']; // ✅ Get Reservation ID
    $full_name = $_POST['full_name'] ?? ''; // ✅ Prevents undefined key warnings
    $email = $_POST['email'] ?? ''; // ✅ Ensure email is present
    $phone = $_POST['phone'] ?? '';
    $event_type = $_POST['event_type'] ?? '';
    $num_guests = $_POST['num_guests'] ?? 0;
    $date_in = $_POST['date_in'] ?? '';
    $date_out = $_POST['date_out'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // ✅ Update reservation
    $sql_update = "UPDATE reservations SET 
                   full_name='$full_name', 
                   email='$email', 
                   phone='$phone', 
                   event_type='$event_type', 
                   num_guests='$num_guests', 
                   date_in='$date_in', 
                   date_out='$date_out', 
                   payment_status='$payment_status', 
                   payment_method='$payment_method' 
                   WHERE id='$id'";

    if ($conn->query($sql_update)) {
        // ✅ Log the edit action
        $conn->query("INSERT INTO activity_log (admin_id, action) 
                      VALUES ('$admin_id', 'Edited Reservation ID $id')");

        echo "<script>alert('Reservation updated successfully.'); window.location.href='reservations.php';</script>";
    } else {
        echo "<script>alert('Error updating reservation.');</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='reservations.php';</script>";
}
?>
