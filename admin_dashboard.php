<?php
include 'auth.php';
include 'config.php';

// Fetch total reservations based on status
$res_count = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE status='Pending'")->fetch_assoc();
$confirmed = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE status='Confirmed'")->fetch_assoc();
$paid_transactions = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE payment_status='Paid'")->fetch_assoc();
$cancelled_reservations = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE status='Cancelled'")->fetch_assoc();

// Fetch total revenue from paid reservations
$total_revenue = $conn->query("SELECT SUM(amount) AS total_revenue FROM reservations WHERE payment_status='Paid'")->fetch_assoc();
$formatted_revenue = "₱" . number_format($total_revenue['total_revenue'] ?? 0, 0);

// Fetch additional stats
$total_resorts = $conn->query("SELECT COUNT(*) AS count FROM resorts")->fetch_assoc();
$monthly_reservations = $conn->query("SELECT COUNT(*) AS count FROM reservations WHERE MONTH(start_date) = MONTH(CURRENT_DATE)")->fetch_assoc();
$total_guests = $conn->query("SELECT SUM(num_guests) AS guests FROM reservations WHERE MONTH(start_date) = MONTH(CURRENT_DATE)")->fetch_assoc();
$popular_event = $conn->query("SELECT event_type, COUNT(*) AS count FROM reservations GROUP BY event_type ORDER BY count DESC LIMIT 1")->fetch_assoc();
$most_popular_event = $popular_event ? $popular_event['event_type'] : 'N/A';

// Fetch chart data
$most_booked_resorts = $conn->query("SELECT res.name, COUNT(r.id) AS count FROM reservations r JOIN resorts res ON r.resort_id = res.id GROUP BY res.name ORDER BY count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$peak_booking_hours = $conn->query("SELECT HOUR(time_in) AS hour, COUNT(*) AS count FROM reservations WHERE status='Confirmed' GROUP BY hour ORDER BY count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
foreach ($peak_booking_hours as &$row) {
    $hour = (int)$row['hour'];
    $am_pm = $hour >= 12 ? 'PM' : 'AM';
    $row['hour'] = ($hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour)) . ' ' . $am_pm;
}

// Fetch payment method data for the new chart
$payment_method_data = $conn->query("SELECT payment_method, COUNT(*) AS count FROM reservations GROUP BY payment_method")->fetch_all(MYSQLI_ASSOC);

// Fetch filter options
$event_types = $conn->query("SELECT DISTINCT event_type FROM reservations")->fetch_all(MYSQLI_ASSOC);
$years = $conn->query("SELECT DISTINCT YEAR(start_date) AS year FROM reservations ORDER BY year DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-4px); }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<?php include 'sidebar.php'; ?>

<div class="p-6">
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl lg:text-3xl font-bold text-[#3C2317]">Dashboard</h1>
            <button id="exportBtn" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition flex items-center gap-2">
                <i class="fas fa-download"></i> Export Data
            </button>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#A97155]">Pending</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $res_count['count']; ?></p>
                </div>
                <i class="fas fa-clock text-4xl text-[#A97155]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#C5824B]">Confirmed</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $confirmed['count']; ?></p>
                </div>
                <i class="fas fa-check-circle text-4xl text-[#C5824B]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#E84E40]">Cancelled</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $cancelled_reservations['count']; ?></p>
                </div>
                <i class="fas fa-times-circle text-4xl text-[#E84E40]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#A97155]">Revenue</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $formatted_revenue; ?></p>
                </div>
                <i class="fas fa-cash-register text-4xl text-[#A97155]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#C5824B]">This Month</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $monthly_reservations['count']; ?></p>
                </div>
                <i class="fas fa-calendar-day text-4xl text-[#C5824B]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#A97155]">Top Event</h2>
                    <p class="text-xl font-bold text-[#3C2317] truncate"><?php echo $most_popular_event; ?></p>
                </div>
                <i class="fas fa-star text-4xl text-[#A97155]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#3C2317]">Guests (Month)</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $total_guests['guests'] ?? 0; ?></p>
                </div>
                <i class="fas fa-users text-4xl text-[#3C2317]"></i>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md stat-card flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-[#C5824B]">Resorts</h2>
                    <p class="text-3xl font-bold text-[#3C2317]"><?php echo $total_resorts['count']; ?></p>
                </div>
                <i class="fas fa-hotel text-4xl text-[#C5824B]"></i>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold text-[#3C2317] mb-4">Peak Booking Hours</h2>
                <div class="chart-container"><canvas id="peakBookingHoursChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold text-[#3C2317] mb-4">Most Booked Resorts</h2>
                <div class="chart-container"><canvas id="mostBookedResortsChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold text-[#3C2317] mb-4">Payment Methods</h2>
                <div class="chart-container"><canvas id="paymentMethodChart"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-4">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                    <h2 class="text-lg font-semibold text-[#3C2317]">Monthly Reservations</h2>
                    <div class="flex gap-4">
                        <select id="eventTypeFilter" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                            <option value="">All Event Types</option>
                            <?php foreach ($event_types as $type): ?>
                                <option value="<?php echo $type['event_type']; ?>"><?php echo $type['event_type']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="yearFilter" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                            <option value="">All Years</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year['year']; ?>"><?php echo $year['year']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="chart-container"><canvas id="monthlyEventChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Peak Booking Hours Chart
    const peakData = <?php echo json_encode($peak_booking_hours); ?>;
    if (peakData.length) {
        new Chart(document.getElementById('peakBookingHoursChart'), {
            type: 'line',
            data: {
                labels: peakData.map(d => d.hour),
                datasets: [{
                    label: 'Bookings',
                    data: peakData.map(d => d.count),
                    borderColor: '#C5824B',
                    backgroundColor: 'rgba(197, 130, 75, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Most Booked Resorts Chart
    const resortData = <?php echo json_encode($most_booked_resorts); ?>;
    if (resortData.length) {
        new Chart(document.getElementById('mostBookedResortsChart'), {
            type: 'bar',
            data: {
                labels: resortData.map(d => d.name),
                datasets: [{
                    label: 'Bookings',
                    data: resortData.map(d => d.count),
                    backgroundColor: '#A97155',
                    borderColor: '#A97155',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Payment Method Breakdown Chart
    const paymentData = <?php echo json_encode($payment_method_data); ?>;
    if (paymentData.length) {
        new Chart(document.getElementById('paymentMethodChart'), {
            type: 'doughnut',
            data: {
                labels: paymentData.map(d => d.payment_method || 'Unknown'),
                datasets: [{
                    data: paymentData.map(d => d.count),
                    backgroundColor: ['#C5824B', '#A97155', '#D9A074', '#3C2317'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Export Functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        let csv = 'Metric,Value\n';
        csv += `Pending Reservations,${<?php echo $res_count['count']; ?>}\n`;
        csv += `Confirmed Reservations,${<?php echo $confirmed['count']; ?>}\n`;
        csv += `Cancelled Reservations,${<?php echo $cancelled_reservations['count']; ?>}\n`;
        csv += `Total Revenue,"${<?php echo json_encode($formatted_revenue); ?>}"\n`;
        csv += `This Month's Reservations,${<?php echo $monthly_reservations['count']; ?>}\n`;
        csv += `Most Popular Event,"${<?php echo json_encode($most_popular_event); ?>}"\n`;
        csv += `Total Guests This Month,${<?php echo $total_guests['guests'] ?? 0; ?>}\n`;
        csv += `Total Resorts,${<?php echo $total_resorts['count']; ?>}\n`;
        csv += 'Payment Methods\n';
        paymentData.forEach(d => {
            csv += `${d.payment_method || 'Unknown'},${d.count}\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'dashboard_stats.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
});

$(document).ready(function() {
    let chartInstance;
    function fetchChartData(eventType, year) {
        $.ajax({
            url: 'fetch_chart_data.php',
            type: 'POST',
            data: { event_type: eventType, year: year },
            dataType: 'json',
            success: function(data) {
                if (chartInstance) chartInstance.destroy();
                const ctx = document.getElementById('monthlyEventChart').getContext('2d');
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.months,
                        datasets: [
                            { label: 'Pending', data: data.pending, backgroundColor: '#A97155' },
                            { label: 'Confirmed', data: data.confirmed, backgroundColor: '#C5824B' },
                            { label: 'Cancelled', data: data.cancelled, backgroundColor: '#E84E40' }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching chart data:', error);
            }
        });
    }
    fetchChartData('', '');
    $('#eventTypeFilter, #yearFilter').change(function() {
        fetchChartData($('#eventTypeFilter').val(), $('#yearFilter').val());
    });
});
</script>

</body>
</html>