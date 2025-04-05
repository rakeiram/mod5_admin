<!-- usersManagement.php -->
<?php
include 'auth.php'; // Ensure user is authenticated
include 'config.php'; // Database connection

// Fetch all users
$sql = "SELECT id, first_name, middle_name, last_name, gender, age, birthday, address, contact_number, email 
        FROM users 
        ORDER BY id ASC";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch distinct birth years from the users table
$yearSql = "SELECT DISTINCT YEAR(birthday) AS birth_year FROM users WHERE birthday IS NOT NULL ORDER BY birth_year ASC";
$yearResult = $conn->query($yearSql);
$birthYears = [];
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $birthYears[] = $row['birth_year'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-select, .search-bar input {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            width: 100%;
            font-size: 0.875rem;
        }
        .filter-select:focus, .search-bar input:focus {
            outline: none;
            border-color: #C5824B;
            box-shadow: 0 0 0 2px rgba(197, 130, 75, 0.2);
        }
        .search-bar {
            max-width: 300px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 p-6 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-[#3C2317]">Users Management</h1>
            <div class="relative search-bar max-w-xs w-full">
                <input 
                    type="text" 
                    id="globalSearch" 
                    class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#C5824B] focus:border-[#C5824B] text-gray-700 placeholder-gray-400 transition-all duration-300" 
                    placeholder="Search all fields..."
                >
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-xl shadow-md mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <select id="filterGender" class="filter-select">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <select id="filterAge" class="filter-select">
                    <option value="">All Ages</option>
                    <?php for ($i = 18; $i <= 100; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <select id="filterBirthMonth" class="filter-select">
                    <option value="">All Birthday Months</option>
                    <?php
                    $months = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    foreach ($months as $num => $name) {
                        echo "<option value='$num'>$name</option>";
                    }
                    ?>
                </select>
                <select id="filterBirthYear" class="filter-select">
                    <option value="">All Years</option>
                    <?php foreach ($birthYears as $year): ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="clearFilters" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Clear</button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birthday</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['id'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['first_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['middle_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-3 whitespace-nowrap"><?php echo htmlspecialchars($user['last_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['birthday'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($users)): ?>
                <p class="text-center text-gray-500 mt-4">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Store initial user data
        const users = <?php echo json_encode($users); ?>;

        // Function to filter and display rows
        function filterTable() {
            const filters = {
                gender: document.getElementById('filterGender').value,
                age: document.getElementById('filterAge').value,
                birthMonth: document.getElementById('filterBirthMonth').value,
                birthYear: document.getElementById('filterBirthYear').value,
                search: document.getElementById('globalSearch').value.toLowerCase()
            };

            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';

            const filteredUsers = users.filter(user => {
                const birthday = user.birthday ? new Date(user.birthday) : null;
                const matchesFilters = 
                    (filters.gender === '' || (user.gender || 'N/A') === filters.gender) &&
                    (filters.age === '' || String(user.age || 'N/A') === filters.age) &&
                    (filters.birthMonth === '' || (birthday && birthday.getMonth() + 1 === parseInt(filters.birthMonth))) &&
                    (filters.birthYear === '' || (birthday && birthday.getFullYear() === parseInt(filters.birthYear)));

                const matchesSearch = filters.search === '' || Object.values(user).some(value => 
                    String(value || 'N/A').toLowerCase().includes(filters.search)
                );

                return matchesFilters && matchesSearch;
            });

            if (filteredUsers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">No users found.</td></tr>';
            } else {
                filteredUsers.forEach(user => {
                    const row = document.createElement('tr');
                    row.classList.add('user-row');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">${user.id || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.first_name || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.middle_name || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.last_name || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.gender || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.age || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.birthday || 'N/A'}</td>
                        <td class="px-6 py-4">${user.address || 'N/A'}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${user.contact_number || 'N/A'}</td>
                        <td class="px-6 py-4">${user.email || 'N/A'}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }

        // Add event listeners to filters and search bar
        document.querySelectorAll('.filter-select, #globalSearch').forEach(input => {
            input.addEventListener('change', filterTable);
            if (input.id === 'globalSearch') {
                input.addEventListener('input', filterTable); // Real-time search
            }
        });

        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', () => {
            document.getElementById('filterGender').value = '';
            document.getElementById('filterAge').value = '';
            document.getElementById('filterBirthMonth').value = '';
            document.getElementById('filterBirthYear').value = '';
            document.getElementById('globalSearch').value = '';
            filterTable();
        });

        // Initial call to display table
        filterTable();
    </script>
</body>
</html>