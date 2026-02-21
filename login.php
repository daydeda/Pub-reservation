<?php
// login.php
require 'config/db_connect.php';
require 'includes/auth_session.php'; // Has session_start()

$message = '';

if (isset($_SESSION['success_msg'])) {
    $message = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
    $msg_class = "success";
} else {
    $msg_class = "error";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
        $msg_class = "error";
    } else {
        // Check Admins Table First
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && $password === $admin['password']) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $admin['username'];
            header("Location: admin/index.php");
            exit();
        }

        // Check Users Table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'general'; // Default role for users table
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid username or password.";
            $msg_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Login - NightOwl Pub'; ?>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            <ul class="flex space-x-6">
                <li><a href="register.php" class="text-gray-300 hover:text-secondary transition-colors">Register</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <div class="bg-surface p-8 rounded-lg shadow-2xl w-full max-w-md border border-gray-800">
            <h2 class="text-3xl font-bold text-center text-primary mb-6">Member Login</h2>
            
            <?php if($message): ?>
                <div class="mb-4 p-3 rounded <?php echo ($msg_class == 'success') ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-gray-400 mb-1">Username</label>
                    <input type="text" name="username" required class="input-field">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required class="input-field">
                </div>
                <button type="submit" class="btn w-full py-3 mt-4 text-darker font-bold text-lg hover:shadow-[0_0_15px_rgba(255,215,0,0.5)] transition-shadow">Login</button>
            </form>
            <p class="text-center mt-6 text-gray-400">
                No account? <a href="register.php" class="text-secondary hover:text-primary transition-colors underline">Register here</a>
            </p>
        </div>
    </div>
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
