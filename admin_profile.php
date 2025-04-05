<?php
include 'auth.php';
include 'config.php';

// Fetch admin profile
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Fetch last login
$sql_last_login = "SELECT timestamp FROM activity_log WHERE admin_id = ? AND action = 'Logged in' ORDER BY timestamp DESC LIMIT 1";
$stmt_last_login = $conn->prepare($sql_last_login);
$stmt_last_login->bind_param("i", $admin_id);
$stmt_last_login->execute();
$last_login_result = $stmt_last_login->get_result();
$last_login = $last_login_result->fetch_assoc()['timestamp'] ?? 'Never';
$stmt_last_login->close();

// Default avatar
$default_avatar = './assets/images/avatar.jpg';
$current_avatar = !empty($admin['avatar_url']) ? $admin['avatar_url'] : $default_avatar;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container { background: linear-gradient(135deg, #ffffff, #f9fafb); border: 1px solid #e5e7eb; animation: fadeIn 0.5s ease-in; }
        .form-section { transition: all 0.3s ease; }
        .form-section:hover { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .form-section.active { animation: slideIn 0.3s ease-out; }
        .input-disabled { background-color: #f3f4f6; cursor: not-allowed; }
        .avatar-overlay { transition: opacity 0.2s ease, transform 0.2s ease; }
        .slide-in { animation: slideIn 0.3s ease-out; }
        .pulse { animation: pulse 1.5s infinite; }
        .loading::after { content: '\f021'; font-family: 'Font Awesome 6 Free'; font-weight: 900; display: inline-block; animation: spin 1s linear infinite; margin-left: 8px; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.03); } 100% { transform: scale(1); } }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include 'sidebar.php'; ?>

    <div class="flex-1 p-4 md:p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl md:text-2xl font-bold text-[#3C2317]">Admin Profile</h1>
            <span class="text-xs md:text-sm text-[#3C2317]">Last Login: <?php echo date("M d, Y - h:i A", strtotime($last_login)); ?></span>
        </div>

        <div class="profile-container p-4 md:p-6 rounded-xl shadow-lg max-w-3xl mx-auto space-y-6">
            <!-- Profile Header -->
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-2 sm:space-y-0 sm:space-x-3 bg-gray-50 p-3 rounded-lg">
                <div class="relative group">
                    <img id="avatarPreview" src="<?php echo htmlspecialchars($current_avatar); ?>" 
                         alt="Admin Avatar" class="rounded-full border-3 border-[#C5824B] w-14 h-14 md:w-20 md:h-20 object-cover shadow-sm">
                    <div class="avatar-overlay absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 group-hover:scale-105">
                        <label for="avatarInput" class="text-white text-xs font-medium cursor-pointer">Change</label>
                        <input type="file" id="avatarInput" accept="image/*" class="hidden" onchange="previewAvatar(event)">
                    </div>
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-base md:text-lg font-semibold text-[#3C2317] leading-tight">Hello, <?php echo htmlspecialchars($admin['name']); ?>!</h2>
                    <p class="text-xs md:text-sm text-[#3C2317] mt-0.5">Manage your profile settings and security preferences.</p>
                </div>
            </div>

            <!-- Avatar Controls -->
            <div id="avatarControls" class="hidden space-y-3 slide-in">
                <div class="flex justify-center">
                    <img id="newAvatarPreview" class="rounded-full w-16 h-16 md:w-20 md:h-20 object-cover border-3 border-[#C5824B]" alt="New Avatar Preview">
                </div>
                <div class="flex justify-center space-x-3">
                    <button id="saveAvatar" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition duration-300 transform hover:scale-105">Save</button>
                    <button id="cancelAvatar" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300 transform hover:scale-105">Cancel</button>
                </div>
            </div>

            <!-- Profile Update Form -->
            <div class="form-section space-y-4 p-4 bg-white rounded-lg">
                <h3 class="text-lg font-semibold text-[#3C2317]">Profile Information</h3>
                <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="avatarUrl" name="avatar_url">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#3C2317]">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" 
                                   class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg input-disabled text-[#3C2317] focus:outline-none" 
                                   disabled required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#3C2317]">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" 
                                   class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg input-disabled text-[#3C2317] focus:outline-none" 
                                   disabled required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" id="editButton" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition duration-300 transform hover:scale-105">Edit</button>
                        <button type="submit" id="saveButton" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition duration-300 transform hover:scale-105 hidden">Save</button>
                        <button type="button" id="cancelButton" class="bg-[#E84E40] text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300 transform hover:scale-105 hidden">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="form-section space-y-4 p-4 bg-white rounded-lg">
                <h3 class="text-lg font-semibold text-[#3C2317]">Change Password</h3>
                <form id="passwordForm" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#3C2317]">Old Password</label>
                            <input type="password" name="old_password" id="oldPassword" 
                                   class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317] placeholder:text-[#A97155]" 
                                   placeholder="Enter current password" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#3C2317]">New Password</label>
                            <input type="password" name="new_password" id="newPassword" 
                                   class="mt-1 w-full px-3 py-2 border border-[#A97155] rounded-lg focus:ring-2 focus:ring-[#C5824B] text-[#3C2317] placeholder:text-[#A97155]" 
                                   placeholder="Enter new password" required>
                            <div id="passwordStrength" class="text-sm mt-1"></div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" id="updatePasswordBtn" class="bg-[#C5824B] text-white px-4 py-2 rounded-lg hover:bg-[#A97155] transition duration-300 transform hover:scale-105">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Notification -->
    <div id="notificationModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-xl shadow-2xl text-center transition-all transform opacity-0 scale-50 max-w-sm w-full" id="modalContent">
            <h2 class="text-xl font-semibold text-[#3C2317] mb-4" id="modalMessage"></h2>
            <button onclick="closeModal()" class="bg-[#A97155] text-white px-6 py-2 rounded-lg hover:bg-[#C5824B] transition duration-300 transform hover:scale-105">OK</button>
        </div>
    </div>

    <script>
    let originalAvatar = document.getElementById('avatarPreview').src;
    let selectedFile = null;
    let originalFormValues = {};

    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            selectedFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('newAvatarPreview').src = e.target.result;
                document.getElementById('avatarControls').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    document.getElementById('cancelAvatar').addEventListener('click', function() {
        document.getElementById('avatarPreview').src = originalAvatar;
        document.getElementById('avatarControls').classList.add('hidden');
        document.getElementById('avatarInput').value = '';
        selectedFile = null;
    });

    document.getElementById('saveAvatar').addEventListener('click', function() {
        if (selectedFile) {
            this.classList.add('loading');
            const formData = new FormData(document.getElementById('profileForm'));
            formData.append('avatar', selectedFile);
            
            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                document.getElementById('avatarPreview').src = document.getElementById('newAvatarPreview').src;
                originalAvatar = document.getElementById('avatarPreview').src;
                document.getElementById('avatarControls').classList.add('hidden');
                this.classList.remove('loading');
                showModal('Avatar updated successfully!');
            })
            .catch(error => {
                console.error('Error:', error);
                this.classList.remove('loading');
                showModal('Error updating avatar.');
            });
        }
    });

    const editButton = document.getElementById('editButton');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    const inputs = document.querySelectorAll('#profileForm input:not([type="hidden"])');

    inputs.forEach(input => {
        originalFormValues[input.name] = input.value;
    });

    editButton.addEventListener('click', function() {
        inputs.forEach(input => {
            input.disabled = false;
            input.classList.remove('input-disabled');
            input.classList.add('focus:ring-2', 'focus:ring-[#C5824B]');
        });
        editButton.classList.add('hidden');
        saveButton.classList.remove('hidden');
        cancelButton.classList.remove('hidden');
        document.querySelector('.form-section').classList.add('active');
    });

    cancelButton.addEventListener('click', function() {
        inputs.forEach(input => {
            input.value = originalFormValues[input.name];
            input.disabled = true;
            input.classList.add('input-disabled');
            input.classList.remove('focus:ring-2', 'focus:ring-[#C5824B]');
        });
        editButton.classList.remove('hidden');
        saveButton.classList.add('hidden');
        cancelButton.classList.add('hidden');
        document.querySelector('.form-section').classList.remove('active');
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            showModal('Full Name and Email cannot be empty.');
            return;
        }

        const saveBtn = document.getElementById('saveButton');
        saveBtn.classList.add('loading');
        const formData = new FormData(this);
        
        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            saveBtn.classList.remove('loading');
            showModal('Profile updated successfully!');
            inputs.forEach(input => {
                originalFormValues[input.name] = input.value;
                input.disabled = true;
                input.classList.add('input-disabled');
                input.classList.remove('focus:ring-2', 'focus:ring-[#C5824B]');
            });
            editButton.classList.remove('hidden');
            saveButton.classList.add('hidden');
            cancelButton.classList.add('hidden');
            document.querySelector('.form-section').classList.remove('active');
        })
        .catch(error => {
            console.error('Error:', error);
            saveBtn.classList.remove('loading');
            showModal('Error updating profile.');
        });
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const updateBtn = document.getElementById('updatePasswordBtn');

        // Client-side validation
        if (newPassword.length < 8 || !/[A-Z]/.test(newPassword) || !/[0-9]/.test(newPassword) || !/[^A-Za-z0-9]/.test(newPassword)) {
            showModal('New password must be at least 8 characters long and include an uppercase letter, a number, and a special character.');
            return;
        }

        updateBtn.classList.add('loading');
        const formData = new FormData(this);

        fetch('update_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Expect JSON response from server
        .then(data => {
            updateBtn.classList.remove('loading');
            if (data.success) {
                showModal('Password updated successfully!');
                document.getElementById('passwordForm').reset();
                document.getElementById('passwordStrength').innerText = '';
            } else {
                showModal(data.message || 'Error updating password.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            updateBtn.classList.remove('loading');
            showModal('Error updating password.');
        });
    });

    document.getElementById('newPassword').addEventListener('input', function() {
        const password = this.value;
        const strengthDiv = document.getElementById('passwordStrength');
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;

        if (password.length === 0) {
            strengthDiv.innerText = '';
        } else if (strength <= 50) {
            strengthDiv.innerText = 'Weak';
            strengthDiv.style.color = '#E84E40';
        } else if (strength <= 75) {
            strengthDiv.innerText = 'Moderate';
            strengthDiv.style.color = '#A97155';
        } else {
            strengthDiv.innerText = 'Strong';
            strengthDiv.style.color = '#C5824B';
        }
    });

    function showModal(message) {
        document.getElementById('modalMessage').innerText = message;
        const modal = document.getElementById('notificationModal');
        const modalContent = document.getElementById('modalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('opacity-0', 'scale-50');
            modalContent.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('notificationModal');
        const modalContent = document.getElementById('modalContent');
        modalContent.classList.remove('opacity-100', 'scale-100');
        modalContent.classList.add('opacity-0', 'scale-50');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    <?php if (isset($_SESSION['success_message'])): ?>
        showModal("<?php echo $_SESSION['success_message']; ?>");
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        showModal("<?php echo $_SESSION['error_message']; ?>");
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    </script>
</body>
</html>