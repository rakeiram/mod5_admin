<?php 
include 'auth.php';
include 'config.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('No event selected.'); window.location.href='calendar.php';</script>";
    exit();
}

$id = $_GET['id'];
// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT r.*, res.name AS resort_name FROM reservations r 
                        JOIN resorts res ON r.resort_id = res.id 
                        WHERE r.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Event not found.'); window.location.href='calendar.php';</script>";
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Format start and end date/time for display
$start_datetime = $event['start_date'] . ' ' . $event['time_in'];
$end_datetime = ($event['end_date'] && $event['time_out']) ? $event['end_date'] . ' ' . $event['time_out'] : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details - <?php echo htmlspecialchars($event['full_name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .event-card {
            animation: fadeIn 0.5s ease-in;
            transition: all 0.3s ease;
        }
        .event-card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .action-btn {
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .status-paid { background-color: #2ecc71; color: white; }
        .status-pending { background-color: #e67e22; color: white; }
        .status-confirmed { background-color: #3498db; color: white; }
        .status-cancelled { background-color: #e74c3c; color: white; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<div class="flex-1 p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-[#3C2317]">Event Details</h1>
        <button onclick="exportToPDF()" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] action-btn flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </button>
    </div>

    <div id="eventDetails" class="event-card bg-white p-6 rounded-xl shadow-md max-w-3xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <p><span class="font-semibold text-[#3C2317]">Event ID:</span> <?php echo htmlspecialchars($event['id']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Full Name:</span> <?php echo htmlspecialchars($event['full_name']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Email:</span> <?php echo htmlspecialchars($event['email']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Phone:</span> <?php echo htmlspecialchars($event['phone']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Resort:</span> <?php echo htmlspecialchars($event['resort_name']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Event Type:</span> <?php echo htmlspecialchars($event['event_type']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Guests:</span> <?php echo htmlspecialchars($event['num_guests']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Amount:</span> ₱<?php echo number_format($event['amount'], 2); ?></p>
            </div>
            <div class="space-y-4">
                <p><span class="font-semibold text-[#3C2317]">Start Date & Time:</span> <?php echo htmlspecialchars($start_datetime); ?></p>
                <p><span class="font-semibold text-[#3C2317]">End Date & Time:</span> <?php echo htmlspecialchars($end_datetime); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Amenities:</span> <?php echo htmlspecialchars($event['amenities']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Notes:</span> <?php echo htmlspecialchars($event['notes'] ?: 'None'); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Payment Method:</span> <?php echo htmlspecialchars($event['payment_method']); ?></p>
                <p><span class="font-semibold text-[#3C2317]">Payment Status:</span> 
                    <span class="status-badge status-<?php echo strtolower($event['payment_status']); ?>">
                        <?php echo htmlspecialchars($event['payment_status']); ?>
                    </span>
                </p>
                <p><span class="font-semibold text-[#3C2317]">Status:</span> 
                    <span class="status-badge status-<?php echo strtolower($event['status']); ?>">
                        <?php echo htmlspecialchars($event['status']); ?>
                    </span>
                </p>
                <p><span class="font-semibold text-[#3C2317]">Created At:</span> <?php echo htmlspecialchars($event['created_at']); ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-end gap-4">
            <?php if ($event['status'] == 'Pending'): ?>
                <button onclick="confirmEvent(<?php echo $event['id']; ?>)" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 action-btn flex items-center gap-2">
                    <i class="fas fa-check"></i> Confirm
                </button>
                <button onclick="cancelEvent(<?php echo $event['id']; ?>)" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 action-btn flex items-center gap-2">
                    <i class="fas fa-times"></i> Cancel
                </button>
            <?php elseif ($event['status'] == 'Confirmed'): ?>
                <button onclick="cancelEvent(<?php echo $event['id']; ?>)" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 action-btn flex items-center gap-2">
                    <i class="fas fa-times"></i> Cancel Event
                </button>
            <?php endif; ?>
            <button onclick="editEvent(<?php echo $event['id']; ?>)" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] action-btn flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit
            </button>
            <a href="calendar.php" class="bg-gray-300 text-[#3C2317] px-4 py-2 rounded-lg hover:bg-gray-400 action-btn flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Calendar
            </a>
        </div>
    </div>
</div>

<script>
    function editEvent(id) {
        window.location.href = `edit_reservation.php?id=${id}`;
    }

    function confirmEvent(id) {
        if (confirm('Are you sure you want to confirm this event?')) {
            window.location.href = `confirm_reservation.php?id=${id}`;
        }
    }

    function cancelEvent(id) {
        if (confirm('Are you sure you want to cancel this event?')) {
            window.location.href = `cancel_reservation.php?id=${id}`;
        }
    }

    function exportToPDF() {
        const element = document.getElementById('eventDetails');
        const opt = {
            margin: 0.5,
            filename: `event_${<?php echo $event['id']; ?>}_details.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

</body>
</html>