<?php
// register.php
require 'config/db_connect.php';
require 'includes/auth_session.php'; // For session start

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $message = "Please fill in all required fields.";
    } else {
        // Check if exists in users or admins
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ? OR email = ? UNION SELECT username FROM admins WHERE username = ?");
        $stmt->execute([$username, $email, $username]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Username or Email already exists.";
        } else {
            $sql = "INSERT INTO users (username, email, password, full_name, phone_number) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$username, $email, $password, $full_name, $phone])) {
                $_SESSION['success_msg'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Register - NightOwl Pub'; ?>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            <ul class="flex space-x-6">
                <li><a href="login.php" class="text-gray-300 hover:text-secondary transition-colors">Login</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <div class="bg-surface p-8 rounded-lg shadow-2xl w-full max-w-lg border border-gray-800">
            <h2 class="text-3xl font-bold text-center text-primary mb-6">Create Account</h2>
            
            <?php if($message): ?>
                <div class="mb-4 p-3 rounded bg-red-900 text-red-200">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1">Username</label>
                        <input type="text" name="username" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-gray-400 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="input-field">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" required class="input-field">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="input-field">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required class="input-field">
                </div>
                <button type="submit" class="btn w-full py-3 mt-4 text-darker font-bold text-lg hover:shadow-[0_0_15px_rgba(255,215,0,0.5)] transition-shadow">Register</button>
            </form>
            <p class="text-center mt-6 text-gray-400">
                Already have an account? <a href="login.php" class="text-secondary hover:text-primary transition-colors underline">Login here</a>
            </p>
        </div>
    </div>
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
