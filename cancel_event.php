<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid request.'); window.location.href='event_list.php';</script>";
    exit();
}

$reservation_id = $_GET['id'];
$admin_id = $_SESSION['admin_id']; // ✅ Get admin ID

// ✅ Update reservation status to 'Cancelled'
$sql = "UPDATE reservations SET status='Cancelled' WHERE id='$reservation_id'";
if ($conn->query($sql)) {
    // ✅ Record cancellation in Activity Log
    $conn->query("INSERT INTO activity_log (admin_id, action) VALUES ('$admin_id', 'Cancelled Reservation ID $reservation_id')");

    echo "<script>alert('Reservation cancelled successfully.'); window.location.href='event_list.php';</script>";
} else {
    echo "<script>alert('Error cancelling reservation.'); window.location.href='event_list.php';</script>";
}
?>
