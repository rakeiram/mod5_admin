<?php
include 'auth.php';
include 'config.php';

// Capture login event
if (!isset($_SESSION['login_recorded'])) {
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("INSERT INTO activity_log (admin_id, action) VALUES (?, ?)");
    $action = "Logged in";
    $stmt->bind_param("is", $admin_id, $action);
    $stmt->execute();
    $stmt->close();
    $_SESSION['login_recorded'] = true;
}

// Pagination setup (initial values for JavaScript)
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            animation: fadeIn 0.5s ease-in;
            overflow-x: auto; 
        }
        .filter-section {
            transition: all 0.3s ease;
        }
        .filter-section.active {
            animation: slideIn 0.3s ease-out;
        }
        .filter-section:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .pagination-btn, .action-btn {
            transition: all 0.2s ease;
        }
        .pagination-btn:hover:not(.disabled), .action-btn:hover {
            transform: scale(1.05);
        }
        .table-row:nth-child(even) {
            background-color: #f9fafb;
        }
        .page-input {
            width: 60px;
            text-align: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

<?php include 'sidebar.php'; ?>

<div class="flex-1 p-6 md:p-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-[#3C2317]">Activity Log</h1>
        <div class="flex space-x-4">
            <button id="toggleFilters" class="bg-[#A97155] text-white px-4 py-2 rounded-lg hover:bg-[#C5824B] transition action-btn">
                <i class="fas fa-filter mr-2"></i>Filters
            </button>
            <button id="exportCSV" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition action-btn">
                <i class="fas fa-download mr-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-[#3C2317] font-semibold">Total Actions</h3>
            <p id="totalActions" class="text-2xl text-[#C5824B]"></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-[#3C2317] font-semibold">Logins</h3>
            <p id="totalLogins" class="text-2xl text-[#C5824B]"></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-[#3C2317] font-semibold">Logouts</h3>
            <p id="totalLogouts" class="text-2xl text-[#C5824B]"></p>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="filter-section bg-white p-6 rounded-xl shadow-lg mb-6 hidden">
        <h2 class="text-xl font-semibold text-[#3C2317] mb-4">Filters</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-[#3C2317]">From Date</label>
                <input type="date" id="filter_date_from" class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317]" 
                       value="<?php echo htmlspecialchars($_GET['filter_date_from'] ?? ''); ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#3C2317]">To Date</label>
                <input type="date" id="filter_date_to" class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317]" 
                       value="<?php echo htmlspecialchars($_GET['filter_date_to'] ?? ''); ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#3C2317]">Action Type</label>
                <select id="filter_action" class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317]">
                    <option value="">All Actions</option>
                    <option value="Logged in" <?php echo ($_GET['filter_action'] ?? '') == 'Logged in' ? 'selected' : ''; ?>>Logged in</option>
                    <option value="Logged out" <?php echo ($_GET['filter_action'] ?? '') == 'Logged out' ? 'selected' : ''; ?>>Logged out</option>
                    <option value="Confirmed Reservation" <?php echo ($_GET['filter_action'] ?? '') == 'Confirmed Reservation' ? 'selected' : ''; ?>>Confirmed Reservation</option>
                    <option value="Cancelled Reservation" <?php echo ($_GET['filter_action'] ?? '') == 'Cancelled Reservation' ? 'selected' : ''; ?>>Cancelled Reservation</option>
                    <option value="Edited Reservation" <?php echo ($_GET['filter_action'] ?? '') == 'Edited Reservation' ? 'selected' : ''; ?>>Edited Reservation</option>
                    <option value="Updated Profile" <?php echo ($_GET['filter_action'] ?? '') == 'Updated Profile' ? 'selected' : ''; ?>>Updated Profile</option>
                    <option value="Changed Password" <?php echo ($_GET['filter_action'] ?? '') == 'Changed Password' ? 'selected' : ''; ?>>Changed Password</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#3C2317]">Admin Name</label>
                <input type="text" id="filter_admin" class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317]" 
                       value="<?php echo htmlspecialchars($_GET['filter_admin'] ?? ''); ?>" placeholder="Search by name">
            </div>
            <div class="flex items-end space-x-2">
                <button id="clearFilters" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 transition action-btn w-full">Clear</button>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-red rounded-xl shadow-md table-container">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-[#C5824B] hover:bg-[#C5824B] text-white text-left rounded-t-lg">
                    <th class="p-4 text-sm font-semibold">Action ID</th>
                    <th class="p-4 text-sm font-semibold">Admin Name</th>
                    <th class="p-4 text-sm font-semibold">Action</th>
                    <th class="p-4 text-sm font-semibold">Timestamp</th>
                </tr>
            </thead>
            <tbody id="activityTableBody" class="text-[#3C2317]"></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-6">
        <div class="text-[#3C2317]" id="paginationInfo"></div>
        <div class="inline-flex items-center space-x-2" id="paginationControls"></div>
    </div>
</div>

<script>
const limit = <?php echo $limit; ?>;
let currentPage = <?php echo $page; ?>;
let totalPages = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Fetch initial data
    fetchLogs();

    // Toggle Filters
    document.getElementById('toggleFilters').addEventListener('click', function() {
        const filterSection = document.getElementById('filterSection');
        filterSection.classList.toggle('hidden');
        if (!filterSection.classList.contains('hidden')) filterSection.classList.add('active');
    });

    // Auto-filter on change
    const filters = ['filter_date_from', 'filter_date_to', 'filter_action', 'filter_admin'];
    filters.forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            currentPage = 1; // Reset to page 1 on filter change
            fetchLogs();
        });
    });

    // Clear Filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        filters.forEach(id => {
            const element = document.getElementById(id);
            if (element.tagName === 'SELECT') element.value = '';
            else element.value = '';
        });
        currentPage = 1;
        fetchLogs();
    });

    // Export to CSV
    document.getElementById('exportCSV').addEventListener('click', function() {
        // Fetch all logs for export without pagination
        const params = new URLSearchParams({
            filter_date_from: document.getElementById('filter_date_from').value,
            filter_date_to: document.getElementById('filter_date_to').value,
            filter_action: document.getElementById('filter_action').value,
            filter_admin: document.getElementById('filter_admin').value
        });
        fetch(`fetch_activity_log.php?${params}&export_all=true`)
            .then(response => response.json())
            .then(data => {
                let csvContent = "data:text/csv;charset=utf-8,Action ID,Admin Name,Action,Timestamp\n";
                data.logs.forEach(log => {
                    const formattedTime = new Date(log.timestamp).toLocaleString('en-US', { 
                        month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true 
                    });
                    csvContent += `"${log.id}","${log.admin_name.replace(/"/g, '""')}","${log.action.replace(/"/g, '""')}","${formattedTime}"\n`;
                });
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'activity_log_' + new Date().toISOString().replace(/[-:T]/g, '').split('.')[0] + '.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
    });
});

function fetchLogs() {
    const params = new URLSearchParams({
        filter_date_from: document.getElementById('filter_date_from').value,
        filter_date_to: document.getElementById('filter_date_to').value,
        filter_action: document.getElementById('filter_action').value,
        filter_admin: document.getElementById('filter_admin').value,
        page: currentPage,
        limit: limit
    });

    fetch(`fetch_activity_log.php?${params}`)
        .then(response => response.json())
        .then(data => {
            const logs = data.logs;
            totalPages = data.total_pages;
            document.getElementById('totalActions').textContent = data.total_records;
            document.getElementById('totalLogins').textContent = data.logins;
            document.getElementById('totalLogouts').textContent = data.logouts;
            renderTable(logs);
            updatePagination();
        })
        .catch(error => console.error('Error fetching logs:', error));
}

function renderTable(logs) {
    const tbody = document.getElementById('activityTableBody');
    tbody.innerHTML = '';

    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center p-6 text-[#3C2317]">No activity logs found.</td></tr>';
    } else {
        logs.forEach(log => {
            const formattedTime = new Date(log.timestamp).toLocaleString('en-US', { 
                month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true 
            });
            tbody.innerHTML += `
                <tr class="table-row border-b" data-id="${log.id}" data-admin="${log.admin_name}" data-action="${log.action}" data-timestamp="${log.timestamp}">
                    <td class="p-4">${log.id}</td>
                    <td class="p-4">${log.admin_name}</td>
                    <td class="p-4">${log.action}</td>
                    <td class="p-4">${formattedTime}</td>
                </tr>`;
        });
    }

    updatePaginationInfo(logs.length);
}

function updatePaginationInfo(total) {
    const start = (currentPage - 1) * limit + 1;
    const end = Math.min(start + limit - 1, total);
    document.getElementById('paginationInfo').innerText = `Showing ${start} to ${end} of ${total} entries`;
}

function updatePagination() {
    const controls = document.getElementById('paginationControls');
    controls.innerHTML = `
        <button onclick="goToPage(1)" class="pagination-btn px-4 py-2 border rounded-lg ${currentPage === 1 ? 'bg-gray-300 text-gray-500 disabled' : 'bg-[#C5824B] text-white'}">First</button>
        <button onclick="goToPage(${currentPage - 1})" class="pagination-btn px-4 py-2 border rounded-lg ${currentPage === 1 ? 'bg-gray-300 text-gray-500 disabled' : 'bg-[#C5824B] text-white'}"><i class="fas fa-chevron-left"></i></button>
        <div class="flex items-center">
            <input type="number" id="pageInput" min="1" max="${totalPages}" value="${currentPage}" class="page-input border border-[#A97155] rounded-lg px-2 py-1 text-[#3C2317] focus:ring-2 focus:ring-[#C5824B]">
            <span class="mx-2 text-[#3C2317]">of ${totalPages}</span>
        </div>
        <button onclick="goToPage(${currentPage + 1})" class="pagination-btn px-4 py-2 border rounded-lg ${currentPage === totalPages ? 'bg-gray-300 text-gray-500 disabled' : 'bg-[#C5824B] text-white'}"><i class="fas fa-chevron-right"></i></button>
        <button onclick="goToPage(${totalPages})" class="pagination-btn px-4 py-2 border rounded-lg ${currentPage === totalPages ? 'bg-gray-300 text-gray-500 disabled' : 'bg-[#C5824B] text-white'}">Last</button>
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
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        fetchLogs(); // Fetch new data from server with updated page
    }
}

</script>

</body>
</html>