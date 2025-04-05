<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-[#C5824B] to-[#EFE1D1] h-screen flex items-center justify-center">

    <!-- Login Container -->
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full space-y-6">
        <h2 class="text-2xl font-semibold text-[#3C2317] text-center">Welcome, Admin</h2>

        <!-- Login Form -->
        <form method="POST" action="login_process.php" class="space-y-4">
            <div>
                <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#A97155] transition duration-300 ease-in-out placeholder-[#E4D1B9]">
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#A97155] transition duration-300 ease-in-out placeholder-[#E4D1B9]">
            </div>
            <div class="text-center">
                <button type="submit" class="w-full bg-[#A97155] text-white py-2 rounded-lg hover:bg-[#C5824B] transition duration-300 ease-in-out transform hover:scale-105">Login</button>
            </div>
        </form>
    </div>

</body>
</html>
