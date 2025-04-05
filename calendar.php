<?php
include 'auth.php';
include 'config.php';

// Fetch resorts and event types for filters
$resorts = $conn->query("SELECT id, name FROM resorts");
$event_types = $conn->query("SELECT DISTINCT event_type FROM reservations WHERE status = 'Confirmed'");
$years = $conn->query("SELECT DISTINCT YEAR(start_date) as year FROM reservations WHERE status = 'Confirmed' ORDER BY year ASC");
$months = [
    '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', 
    '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', 
    '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Calendar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    .fc {
        height: 100%;
        width: 100%;
    }
    .fc-scroller {
        overflow: hidden !important;
    }
    .calendar-container {
        height: calc(100vh - 160px); /* Adjusted for header and filters */
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
    }
    .tooltip {
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 1000;
        display: none;
    }
    /* Custom styles for header toolbar buttons */
    .fc-button {
        background-color: #C5824B !important; /* Default background */
        color: white !important; /* Text color */
        border: none !important; /* Remove default border */
        border-radius: 0.5rem !important; /* Rounded corners (matches Tailwind's 'rounded-lg') */
        padding: 0.5rem 1rem !important; /* Padding */
        font-weight: 500 !important; /* Slightly bold text */
        transition: background-color 0.2s ease !important; /* Smooth hover transition */
    }
    .fc-button:hover:not(:disabled) {
        background-color: #A97155 !important; /* Hover state */
    }
    .fc-button.fc-button-active {
        background-color: #A97155 !important; /* Active view button (e.g., current view) */
        opacity: 1 !important; /* Ensure active state is fully opaque */
    }
    .fc-button:disabled {
        background-color: #e5e7eb !important; /* Gray for disabled state (e.g., prev/next when at limit) */
        color: #6b7280 !important; /* Gray text */
        opacity: 0.7 !important;
    }
</style>
</head>
<body class="bg-gray-50">

<?php include 'sidebar.php'; ?>

<div class="p-6 h-screen flex flex-col">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">Reservation Calendar</h1>
        <div class="flex items-center gap-4">
            <button id="toggleFilters" class="bg-[#A97155] text-white px-4 py-2 rounded-lg hover:bg-[#C5824B] transition action-btn">
                <i class="fas fa-filter"></i> Filters
            </button>
            <button id="exportBtn" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition flex items-center gap-2">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filter Panel (Hidden by default) -->
    <div id="filterPanel" class="bg-white p-4 rounded-xl shadow-md mb-4 hidden">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <select id="filter_resort" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                <option value="">All Resorts</option>
                <?php while ($resort = $resorts->fetch_assoc()): ?>
                    <option value="<?php echo $resort['id']; ?>">
                        <?php echo htmlspecialchars($resort['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select id="filter_event_type" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                <option value="">All Event Types</option>
                <?php while ($type = $event_types->fetch_assoc()): ?>
                    <option value="<?php echo $type['event_type']; ?>">
                        <?php echo htmlspecialchars($type['event_type']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select id="filter_year" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                <option value="">All Years</option>
                <?php while ($year = $years->fetch_assoc()): ?>
                    <option value="<?php echo $year['year']; ?>" <?php echo $year['year'] == date('Y') ? 'selected' : ''; ?>>
                        <?php echo $year['year']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select id="filter_month" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                <option value="">All Months</option>
                <?php foreach ($months as $num => $name): ?>
                    <option value="<?php echo $num; ?>">
                        <?php echo $name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white p-6 rounded-xl shadow-md flex-grow calendar-container relative">
        <div id="calendar"></div>
        <div id="tooltip" class="tooltip"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const tooltip = document.getElementById('tooltip');
    const filterResort = document.getElementById('filter_resort');
    const filterEventType = document.getElementById('filter_event_type');
    const filterYear = document.getElementById('filter_year');
    const filterMonth = document.getElementById('filter_month');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: '100%',
        expandRows: true,
        stickyHeaderDates: true,
        dayMaxEvents: true,
        eventClick: function(info) {
            window.location.href = "view_event.php?id=" + info.event.id;
        },
        eventMouseEnter: function(info) {
            const event = info.event;
            tooltip.innerHTML = `
                <strong>${event.title}</strong><br>
                Start: ${event.start.toLocaleString()}<br>
                End: ${event.end ? event.end.toLocaleString() : 'N/A'}
            `;
            tooltip.style.display = 'block';
            tooltip.style.left = (info.jsEvent.pageX + 10) + 'px';
            tooltip.style.top = (info.jsEvent.pageY + 10) + 'px';
        },
        eventMouseLeave: function() {
            tooltip.style.display = 'none';
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });
    calendar.render();

    // Function to fetch and update events
    function fetchEvents() {
        const resort = filterResort.value;
        const eventType = filterEventType.value;
        const year = filterYear.value;
        const month = filterMonth.value;

        let url = 'fetch_calendar.php';
        const params = new URLSearchParams();
        if (resort) params.append('filter_resort', resort);
        if (eventType) params.append('filter_event_type', eventType);
        if (year) params.append('filter_year', year);
        if (month) params.append('filter_month', month);
        if (params.toString()) url += '?' + params.toString();

        fetch(url)
            .then(response => response.json())
            .then(events => {
                calendar.getEvents().forEach(event => event.remove()); // Clear existing events
                calendar.addEventSource(events); // Add new events
                const today = new Date();
                const currentYear = today.getFullYear();
                const currentMonth = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based, add 1
                if (!year && !month) {
                    calendar.gotoDate(`${currentYear}-${currentMonth}-01`); // Default to current month/year
                } else if (year && month) {
                    calendar.gotoDate(`${year}-${month.padStart(2, '0')}-01`);
                } else if (year) {
                    calendar.gotoDate(`${year}-${currentMonth}-01`);
                }
            })
            .catch(error => console.error('Error fetching events:', error));
    }

    // Initial fetch with default year (current year)
    fetchEvents();

    // Add change listeners to filters
    [filterResort, filterEventType, filterYear, filterMonth].forEach(filter => {
        filter.addEventListener('change', fetchEvents);
    });

    // Toggle Filters
    document.getElementById('toggleFilters').addEventListener('click', function() {
        const panel = document.getElementById('filterPanel');
        panel.classList.toggle('hidden');
    });

    // Export to CSV
    document.getElementById('exportBtn').addEventListener('click', function() {
        const events = calendar.getEvents();
        let csv = 'ID,Full Name - Resort,Start Date,Time In,End Date,Time Out\n';
        events.forEach(event => {
            const startDate = event.start.toISOString().split('T')[0];
            const timeIn = event.extendedProps.time_in;
            const endDate = event.end ? event.end.toISOString().split('T')[0] : 'N/A';
            const timeOut = event.extendedProps.time_out || 'N/A';
            csv += `${event.id},"${event.title}",${startDate},${timeIn},${endDate},${timeOut}\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reservation_calendar.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
});
</script>

</body>
</html>