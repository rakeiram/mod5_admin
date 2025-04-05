<?php
include 'auth.php';
include 'config.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch distinct years from the database
$year_query = "SELECT DISTINCT YEAR(start_date) AS year FROM reservations WHERE status = 'Pending' ORDER BY year ASC";
$year_result = $conn->query($year_query);
$years = [];
while ($row = $year_result->fetch_assoc()) {
    $years[] = $row['year'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Reservations</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-paid { color: #2ecc71; font-weight: 600; }
        .status-pending { color: #e67e22; font-weight: 600; }
        .action-btn { transition: all 0.2s; }
        .action-btn:hover { transform: scale(1.1); }
        .confirm { color: #2ecc71; }
        .cancel { color: #e74c3c; }
        .edit { color: #3498db; }
        .table-container { overflow-x: auto; }
        .page-input { width: 60px; text-align: center; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<?php include 'sidebar.php'; ?>

<div class="p-6">
    <div>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">Pending Reservations</h1>
            <div class="flex items-center gap-4">
                <input type="text" id="searchInput" placeholder="Search reservations..." 
                       class="w-full sm:w-64 px-4 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                <button id="exportBtn" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition flex items-center gap-2">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-xl shadow-md mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <select id="filter_month" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                    <option value="">All Months</option>
                    <?php
                    $months = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    foreach ($months as $num => $name) {
                        $selected = ($_GET['filter_month'] ?? '') == $num ? 'selected' : '';
                        echo "<option value='$num' $selected>$name</option>";
                    }
                    ?>
                </select>
                <select id="filter_year" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                    <option value="">All Years</option>
                    <?php
                    foreach ($years as $year) {
                        $selected = ($_GET['filter_year'] ?? '') == $year ? 'selected' : '';
                        echo "<option value='$year' $selected>$year</option>";
                    }
                    ?>
                </select>
                <select id="filter_resort" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                    <option value="">All Resorts</option>
                    <?php
                    $resorts = $conn->query("SELECT * FROM resorts");
                    while ($resort = $resorts->fetch_assoc()) {
                        $selected = ($_GET['filter_resort'] ?? '') == $resort['id'] ? 'selected' : '';
                        echo "<option value='{$resort['id']}' $selected>{$resort['name']}</option>";
                    }
                    ?>
                </select>
                <select id="filter_status" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                    <option value="">All Payment Status</option>
                    <option value="Paid" <?php echo ($_GET['filter_status'] ?? '') == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="Pending" <?php echo ($_GET['filter_status'] ?? '') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                <select id="filter_payment" class="border p-2 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
                    <option value="">All Payment Methods</option>
                    <option value="Cash" <?php echo ($_GET['filter_payment'] ?? '') == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="GCash" <?php echo ($_GET['filter_payment'] ?? '') == 'GCash' ? 'selected' : ''; ?>>GCash</option>
                    <option value="Credit Card" <?php echo ($_GET['filter_payment'] ?? '') == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                </select>
                <button id="clearFilters" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Clear</button>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-xl shadow-md table-container">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#C5824B] hover:bg-[#C5824B] text-white text-left">
                        <th class="p-4 text-sm font-semibold">Full Name</th>
                        <th class="p-4 text-sm font-semibold">Resort</th>
                        <th class="p-4 text-sm font-semibold">Event Type</th>
                        <th class="p-4 text-sm font-semibold">Guests</th>
                        <th class="p-4 text-sm font-semibold">Start Date</th>
                        <th class="p-4 text-sm font-semibold">Time In</th>
                        <th class="p-4 text-sm font-semibold">End Date</th>
                        <th class="p-4 text-sm font-semibold">Time Out</th>
                        <th class="p-4 text-sm font-semibold">Payment Method</th>
                        <th class="p-4 text-sm font-semibold">Status</th>
                        <th class="p-4 text-sm font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-600" id="paginationInfo"></div>
            <div class="flex items-center gap-2" id="paginationControls"></div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md text-center">
        <h2 id="modalTitle" class="text-xl font-semibold mb-4 text-gray-800"></h2>
        <p id="modalMessage" class="text-gray-600 mb-6"></p>
        <div class="flex justify-center gap-4">
            <button id="confirmActionBtn" class="px-6 py-2 rounded-lg text-white transition"></button>
            <button onclick="closeModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">No</button>
        </div>
    </div>
</div>

<script>
const limit = <?php echo $limit; ?>;
let currentPage = <?php echo $page; ?>;
let totalPages = 0;
let allReservations = [];

document.addEventListener('DOMContentLoaded', function() {
    // Fetch initial data
    fetchReservations();

    // Auto-filter on change
    const filters = ['filter_month', 'filter_year', 'filter_resort', 'filter_status', 'filter_payment'];
    filters.forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            currentPage = 1; // Reset to page 1 on filter change
            fetchReservations();
        });
    });

    // Clear Filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        filters.forEach(id => document.getElementById(id).value = '');
        currentPage = 1;
        fetchReservations();
    });

    // Real-time Search
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        renderTable(allReservations.filter(res => {
            return (
                res.full_name.toLowerCase().includes(filter) ||
                res.resort_name.toLowerCase().includes(filter) ||
                res.event_type.toLowerCase().includes(filter) ||
                res.num_guests.toString().includes(filter) ||
                res.start_date.toLowerCase().includes(filter) ||
                res.time_in.toLowerCase().includes(filter) ||
                res.end_date.toLowerCase().includes(filter) ||
                res.time_out.toLowerCase().includes(filter) ||
                res.payment_method.toLowerCase().includes(filter) ||
                res.payment_status.toLowerCase().includes(filter)
            );
        }));
    });

    // Export Functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        let csv = 'Full Name,Resort,Event Type,Guests,Start Date,Time In,End Date,Time Out,Payment Method,Status\n';
        allReservations.forEach(res => {
            const startDateFormatted = new Date(res.start_date).toLocaleDateString('en-US', { 
                month: '2-digit', day: '2-digit', year: 'numeric' 
            });
            const timeInFormatted = res.time_in;
            const endDateFormatted = new Date(res.end_date).toLocaleDateString('en-US', { 
                month: '2-digit', day: '2-digit', year: 'numeric' 
            });
            const timeOutFormatted = res.time_out;
            csv += `"${res.full_name.replace(/"/g, '""')}","${res.resort_name.replace(/"/g, '""')}","${res.event_type.replace(/"/g, '""')}",${res.num_guests},"${startDateFormatted}","${timeInFormatted}","${endDateFormatted}","${timeOutFormatted}","${res.payment_method}","${res.payment_status}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'pending_reservations.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    });
});

function fetchReservations() {
    const params = new URLSearchParams({
        filter_month: document.getElementById('filter_month').value,
        filter_year: document.getElementById('filter_year').value,
        filter_resort: document.getElementById('filter_resort').value,
        filter_status: document.getElementById('filter_status').value,
        filter_payment: document.getElementById('filter_payment').value,
        page: currentPage,
        limit: limit
    });

    fetch(`fetch_reservations.php?${params}`)
        .then(response => response.json())
        .then(data => {
            allReservations = data.reservations;
            totalPages = data.total_pages;
            renderTable(allReservations);
            updatePagination();
        })
        .catch(error => console.error('Error fetching reservations:', error));
}

function renderTable(reservations) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    const start = (currentPage - 1) * limit;
    const end = start + limit;
    const paginatedReservations = reservations.slice(start, end);

    if (paginatedReservations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center p-6 text-gray-500">No pending reservations found.</td></tr>';
    } else {
        paginatedReservations.forEach(res => {
            tbody.innerHTML += `
                <tr class="border-b">
                    <td class="p-4 text-gray-700">${res.full_name}</td>
                    <td class="p-4 text-gray-700">${res.resort_name}</td>
                    <td class="p-4 text-gray-700">${res.event_type}</td>
                    <td class="p-4 text-gray-700">${res.num_guests}</td>
                    <td class="p-4 text-gray-700">${res.start_date}</td>
                    <td class="p-4 text-gray-700">${res.time_in}</td>
                    <td class="p-4 text-gray-700">${res.end_date}</td>
                    <td class="p-4 text-gray-700">${res.time_out}</td>
                    <td class="p-4 text-gray-700">${res.payment_method}</td>
                    <td class="p-4 status-${res.payment_status.toLowerCase()}">${res.payment_status}</td>
                    <td class="p-4 flex justify-center gap-3">
                        <button onclick="openModal('confirm', ${res.id})" class="action-btn confirm text-xl" title="Confirm"><i class="fas fa-check-circle"></i></button>
                        <button onclick="openModal('cancel', ${res.id})" class="action-btn cancel text-xl" title="Cancel"><i class="fas fa-times-circle"></i></button>
                        <button onclick="editReservation(${res.id})" class="action-btn edit text-xl" title="Edit"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>`;
        });
    }

    updatePaginationInfo(reservations.length);
}

function updatePaginationInfo(total) {
    const start = (currentPage - 1) * limit + 1;
    const end = Math.min(start + limit - 1, total);
    document.getElementById('paginationInfo').innerText = `Showing ${start} to ${end} of ${total} entries`;
}

function updatePagination() {
    const controls = document.getElementById('paginationControls');
    controls.innerHTML = `
        <button onclick="goToPage(1)" class="px-3 py-1 border rounded-lg ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-[#C5824B] text-white hover:bg-[#A97155]'}">First</button>
        <button onclick="goToPage(${currentPage - 1})" class="px-3 py-1 border rounded-lg ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-[#C5824B] text-white hover:bg-[#A97155]'}"><</button>
        <div class="flex items-center">
            <input type="number" id="pageInput" min="1" max="${totalPages}" value="${currentPage}" class="page-input border border-[#A97155] rounded-lg px-2 py-1 text-gray-700 focus:ring-2 focus:ring-[#C5824B]">
            <span class="mx-2 text-gray-700">of ${totalPages}</span>
        </div>
        <button onclick="goToPage(${currentPage + 1})" class="px-3 py-1 border rounded-lg ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-[#C5824B] text-white hover:bg-[#A97155]'}">></button>
        <button onclick="goToPage(${totalPages})" class="px-3 py-1 border rounded-lg ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-[#C5824B] text-white hover:bg-[#A97155]'}">Last</button>
    `;

    document.getElementById('pageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            let pageNum = parseInt(this.value);
            if (isNaN(pageNum) || pageNum < 1) pageNum = 1;
            if (pageNum > totalPages) pageNum = totalPages;
            goToPage(pageNum);
        }
    });
}

function goToPage(page) {
    currentPage = page;
    fetchReservations();
}

function openModal(action, id) {
    const modal = document.getElementById('confirmationModal');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('confirmActionBtn');

    confirmBtn.classList.remove('bg-green-500', 'bg-red-500', 'hover:bg-green-600', 'hover:bg-red-600');

    if (action === 'confirm') {
        title.textContent = 'Confirm Reservation';
        message.textContent = 'Are you sure you want to approve this reservation?';
        confirmBtn.textContent = 'Yes';
        confirmBtn.classList.add('bg-green-500', 'hover:bg-green-600');
        confirmBtn.onclick = () => window.location.href = `confirm_reservation.php?id=${id}`;
    } else if (action === 'cancel') {
        title.textContent = 'Cancel Reservation';
        message.textContent = 'Are you sure you want to cancel this reservation?';
        confirmBtn.textContent = 'Yes';
        confirmBtn.classList.add('bg-red-500', 'hover:bg-red-600');
        confirmBtn.onclick = () => window.location.href = `cancel_reservation.php?id=${id}`;
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
}

function editReservation(id) {
    window.location.href = `edit_reservation.php?id=${id}`;
}
</script>

</body>
</html>