<?php
include 'config.php';

$name = $email = $password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check for empty fields
    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }

    // Insert admin if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            echo "<p class='text-green-600 text-center font-semibold'>Admin registered successfully!</p>";
            $name = $email = "";
        } else {
            $errors[] = "Error registering admin.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Register Admin</h2>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded-lg mb-4">
                <ul>
                    <?php foreach ($errors as $error) { echo "<li class='text-sm'>$error</li>"; } ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C5824B] focus:outline-none">
            <button type="submit" class="w-full bg-[#C5824B] text-white py-2 rounded-lg hover:bg-[#A97155] transition">Register Admin</button>
        </form>
    </div>
</body>
</html>
