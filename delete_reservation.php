<?php
include 'auth.php';
include 'config.php';

$reservation_id = $_GET['id'];
$admin_id = $_SESSION['admin_id'];

$conn->query("DELETE FROM reservations WHERE id='$reservation_id'");
$conn->query("INSERT INTO activity_log (admin_id, action) VALUES ('$admin_id', 'Deleted Reservation ID $reservation_id')");

header("Location: reservations.php");
exit();

?>
