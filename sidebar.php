<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        #sidebarLogo {
            width: 322px;           /* Base width */
            height: 92px;          /* Base height */
            max-width: 100%;       /* Prevent overflow */
            max-height: 100%;      /* Prevent overflow */
            object-fit: contain;   /* Maintain aspect ratio */
            transition: all 0.3s ease-in-out;
        }

        /* When sidebar is collapsed */
        #sidebar.w-16 #logoContainer {
            display: none;        /* Hide completely when collapsed */
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-screen bg-gradient-to-b from-[#3C2317] to-[#A97155] text-white transition-all duration-300 ease-in-out z-20">
        <!-- Toggle Button -->
        <div class="p-4 pb-0 flex justify-center w-full" style="top: 0.5rem;">
            <button id="toggleBtn" class="text-2xl focus:outline-none text-white">
                <i id="toggleIcon" class="fas fa-times"></i>
            </button>
        </div>

        <!-- Logo -->
        <div id="logoContainer" class="flex justify-center">
            <img src="./assets/images/logo1.png" alt="System Logo" id="sidebarLogo" class="transition-all duration-300">
        </div>

        <!-- Sidebar Content -->
        <div class="flex flex-col h-full px-5">
            <!-- Admin Title -->
            <h2 id="sidebarTitle" class="text-center text-xl font-semibold transition-opacity duration-300"></h2>

            <!-- Navigation Items -->
            <ul id="navList" class="mt-6 space-y-4 flex-1 justify-center">
                <li><a href="dashboard.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-chart-line text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Dashboard</span>
                </a></li>
                <li><a href="reservations.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-calendar-check text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Reservations</span>
                </a></li>
                <li><a href="event_list.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-list text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Confirmed Events</span>
                </a></li>
                <li><a href="calendar.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-calendar-alt text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Calendar</span>
                </a></li>
                <li><a href="activity_log.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-history text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Activity Log</span>
                </a></li>
                <li><a href="admin_profile.php" class="flex font-bold items-center space-x-3 hover:bg-white/10 p-3 rounded-lg sidebar-item">
                    <i class="fas fa-user text-lg w-8 transition-all duration-300"></i>
                    <span class="nav-text transition-opacity duration-300">Profile</span>
                </a></li>

                <!-- Divider -->
                <li><hr class="border-t border-gray-500 my-4"></li>

                <!-- Logout Button -->
                <div class="mt-15 pb-5">
                    <button id="logoutButton" class="flex font-bold w-full justify-center items-center space-x-2 p-4 hover:bg-red-600 rounded-lg text-white sidebar-item">
                        <i class="fas fa-sign-out-alt h-5 w-8 transition-all duration-300"></i>
                        <span class="nav-text transition-opacity duration-300">Logout</span>
                    </button>
                </div>
            </ul>
        </div>
    </div>

    <!-- Confirmation Modal for Logout -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-30">
        <div class="bg-white p-8 rounded-lg shadow-lg w-11/12 sm:w-1/3">
            <h2 class="text-xl font-semibold mb-4">Are you sure you want to log out?</h2>
            <div class="flex justify-end space-x-4">
                <a href="logout.php" id="confirmLogout" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Confirm</a>
                <button id="cancelLogout" class="px-6 py-2 bg-gray-300 rounded-lg">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle Functionality
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        const navTexts = document.querySelectorAll('.nav-text');
        const sidebarTitle = document.getElementById('sidebarTitle');
        const sidebarLogo = document.getElementById('sidebarLogo');
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        const content = document.querySelector('.content') || document.body;

        // Load sidebar state from localStorage
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            collapseSidebar();
        } else {
            expandSidebar();
        }

        // Toggle Sidebar
        toggleBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('w-64')) {
                collapseSidebar();
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                expandSidebar();
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        });

        function collapseSidebar() {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-16');
            toggleIcon.classList.remove('fa-times');
            toggleIcon.classList.add('fa-bars');
            navTexts.forEach(text => {
                text.classList.add('opacity-0', 'hidden');
            });
            sidebarTitle.classList.add('opacity-0', 'hidden');
            // Logo will be hidden via CSS when w-16 is applied
            sidebarItems.forEach(item => {
                item.classList.remove('justify-start');
                item.classList.add('justify-center');
            });
            toggleBtn.parentElement.classList.remove('justify-end');
            toggleBtn.parentElement.classList.add('justify-center');
            adjustContentMargin('ml-16');
        }

        function expandSidebar() {
            sidebar.classList.remove('w-16');
            sidebar.classList.add('w-64');
            toggleIcon.classList.remove('fa-bars');
            toggleIcon.classList.add('fa-times');
            navTexts.forEach(text => {
                text.classList.remove('opacity-0', 'hidden');
            });
            sidebarTitle.classList.remove('opacity-0', 'hidden');
            // Logo will be shown via CSS when w-64 is applied
            sidebarItems.forEach(item => {
                item.classList.remove('justify-center');
                item.classList.add('justify-start');
            });
            toggleBtn.parentElement.classList.remove('justify-center');
            toggleBtn.parentElement.classList.add('justify-end');
            adjustContentMargin('ml-64');
        }

        function adjustContentMargin(marginClass) {
            content.classList.remove('ml-16', 'ml-64');
            content.classList.add(marginClass, 'transition-all', 'duration-300', 'ease-in-out');
        }

        // Highlight active sidebar item
        function highlightActiveSidebarItem() {
            const currentPage = window.location.pathname.split('/').pop();
            sidebarItems.forEach(item => {
                const link = item.tagName === 'A' ? item : item.querySelector('a');
                if (link && link.getAttribute('href') === currentPage) {
                    item.classList.add('bg-white/20', 'font-bold');
                    item.classList.remove('hover:bg-white/10');
                }
            });
        }

        // Logout Modal Functionality
        document.getElementById('logoutButton').addEventListener('click', function() {
            document.getElementById('logoutModal').classList.remove('hidden');
        });

        document.getElementById('cancelLogout').addEventListener('click', function() {
            document.getElementById('logoutModal').classList.add('hidden');
        });

        document.getElementById('confirmLogout').addEventListener('click', function() {
            window.location.href = "logout.php";
        });

        // Responsive Behavior
        function handleResize() {
            if (window.innerWidth < 640 && !localStorage.getItem('sidebarCollapsed')) {
                collapseSidebar();
            }
        }

        // Initialize
        window.addEventListener('resize', handleResize);
        handleResize();
        highlightActiveSidebarItem();
    </script>
</body>
</html>