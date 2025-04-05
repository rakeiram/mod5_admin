<?php
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('No reservation selected.'); window.location.href='reservations.php';</script>";
    exit();
}

$id = $_GET['id'];
// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Reservation not found.'); window.location.href='reservations.php';</script>";
    exit();
}

$reservation = $result->fetch_assoc();
$stmt->close();

// Format datetime for input fields
$start_datetime = $reservation['start_date'] . 'T' . $reservation['time_in'];
$end_datetime = ($reservation['end_date'] && $reservation['time_out']) ? $reservation['end_date'] . 'T' . $reservation['time_out'] : '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];
    $full_name = $_POST['full_name'];
    $event_type = $_POST['event_type'];
    $num_guests = $_POST['num_guests'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $payment_status = $_POST['payment_status'];
    $payment_method = $_POST['payment_method'];

    // Split datetime into date and time components
    $start_date = date('Y-m-d', strtotime($start_datetime));
    $time_in = date('H:i:s', strtotime($start_datetime));
    $end_date = date('Y-m-d', strtotime($end_datetime));
    $time_out = date('H:i:s', strtotime($end_datetime));

    // Update reservation details with prepared statement
    $stmt = $conn->prepare("UPDATE reservations SET 
                            full_name = ?, 
                            event_type = ?, 
                            num_guests = ?, 
                            start_date = ?, 
                            time_in = ?, 
                            end_date = ?, 
                            time_out = ?, 
                            payment_status = ?, 
                            payment_method = ? 
                            WHERE id = ?");
    $stmt->bind_param("ssissssssi", $full_name, $event_type, $num_guests, $start_date, $time_in, $end_date, $time_out, $payment_status, $payment_method, $id);

    if ($stmt->execute()) {
        // Log the edit action
        $log_stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action) VALUES (?, ?)");
        $action = "Edited Reservation ID $id";
        $log_stmt->bind_param("is", $admin_id, $action);
        $log_stmt->execute();
        $log_stmt->close();

        echo "<script>alert('Reservation updated successfully.'); window.location.href='reservations.php';</script>";
    } else {
        echo "<script>alert('Error updating reservation: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="p-8">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6"><i class="fas fa-edit"></i> Edit Reservation</h1>

    <div class="bg-white p-8 rounded-xl shadow-lg">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($reservation['full_name']); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required readonly>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($reservation['phone']); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required readonly>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Event Type</label>
                    <select name="event_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                        <?php
                        $events = ["Wedding", "Corporate Event", "Birthday", "Reunion", "Conference"];
                        foreach ($events as $event) {
                            $selected = ($reservation['event_type'] == $event) ? 'selected' : '';
                            echo "<option value='$event' $selected>$event</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Number of Guests</label>
                    <input type="number" name="num_guests" value="<?php echo htmlspecialchars($reservation['num_guests']); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Start Date & Time</label>
                    <input type="datetime-local" name="start_datetime" value="<?php echo htmlspecialchars($start_datetime); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">End Date & Time</label>
                    <input type="datetime-local" name="end_datetime" value="<?php echo htmlspecialchars($end_datetime); ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Payment Status</label>
                    <select name="payment_status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                        <option value="Pending" <?php if ($reservation['payment_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Paid" <?php if ($reservation['payment_status'] == 'Paid') echo 'selected'; ?>>Paid</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Payment Method</label>
                    <select name="payment_method" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#C5824B]" required>
                        <option value="Cash" <?php if ($reservation['payment_method'] == 'Cash') echo 'selected'; ?>>Cash</option>
                        <option value="GCash" <?php if ($reservation['payment_method'] == 'GCash') echo 'selected'; ?>>GCash</option>
                        <option value="Credit Card" <?php if ($reservation['payment_method'] == 'Credit Card') echo 'selected'; ?>>Credit Card</option>
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex justify-between">
                <a href="reservations.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div class="space-x-4">
                    <button type="submit" class="bg-[#C5824B] text-white px-6 py-2 rounded-lg hover:bg-[#A97155] transition">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>