<?php
// login.php
// หน้าเข้าสู่ระบบ (Login page for users and admins)

// เรียกใช้งานไฟล์เชื่อมต่อฐานข้อมูล (Require database connection logic)
require 'config/db_connect.php';
// เรียกใช้งานระบบจัดการ Session ซึ่งเปิดใช้งาน session_start() (Require session management file which initializes session)
require 'includes/auth_session.php'; // Has session_start()

// กำหนดตัวแปรสำหรับเก็บข้อความแจ้งเตือน (Initialize a variable to store messages/errors)
$message = '';

// ตรวจสอบว่ามีข้อความความสำเร็จ (เช่น สมัครสมาชิกสำเร็จ) ถูกบันทึกมาใน Session หรือไม่ 
// Check if there is a success message in the session (e.g. from successful registration)
if (isset($_SESSION['success_msg'])) {
    $message = $_SESSION['success_msg']; // โอนข้อความมาใส่ในตัวแปร (Move the message to local variable)
    unset($_SESSION['success_msg']); // ล้างค่าออกเพื่อไม่ให้แสดงซ้ำ (Clear the session message to avoid duplicate display)
    $msg_class = "success"; // ตั้งค่าคลาส CSS ว่าเป็นความสำเร็จ (Set CSS class to 'success' for styling)
} else {
    $msg_class = "error"; // กำหนดให้ข้อความเริ่มต้นเป็นประเภทข้อผิดพลาด (Default styling to 'error' mode)
}

// ตรวจสอบว่าผู้ใช้คลิกปุ่มส่งแบบฟอร์มล็อกอินเข้ามาหรือไม่ (Check if the form has been submitted via POST method)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าและตัดช่องว่างตอนต้น/ท้ายออกจากชื่อผู้ใช้ (Get username input and trim whitespace)
    $username = trim($_POST['username']);
    // รับรหัสผ่านที่กรอกมา (Get password input)
    $password = $_POST['password'];

    // ตรวจสอบว่ากรอกข้อมูลมาครบ 2 ช่องหรือไม่ (Validate that both username and password fields are filled)
    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password."; // แจ้งเตือนให้ระบุข้อมูลให้ครบ (Prompt user to specify both fields)
        $msg_class = "error";
    } else {
        // ---- 1. Check Admins Table First (ตรวจสอบตารางแอดมินก่อน) ----
        // ค้นหา username นี้จากตารางผู้ดูแลระบบ (Search for the username in the admins table)
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(); // ดึงข้อมูลผู้ใช้งานถ้ามีข้อมูลตรง (Fetch admin data if found)

        // หากเจอแอดมิน และรหัสผ่านถูกต้อง (If admin exists and submitted password matches the database record)
        if ($admin && $password === $admin['password']) {
            $_SESSION['user_id'] = $admin['id']; // บันทึกไอดีผู้ใช้ลง Session (Save User ID in session)
            $_SESSION['role'] = 'admin'; // ระบุสิทธิ์ (Role) ว่าเป็นผู้ดูแลระบบ (Assign 'admin' role)
            $_SESSION['username'] = $admin['username']; // บันทึกชื่อเพื่อเอาไปแสดงผล (Save username for display)
            // พาผู้ใช้แอดมินเข้าไปหน้าผังหลังบ้าน (Redirect the admin to the admin dashboard)
            header("Location: admin/index.php"); 
            exit(); // หยุดการประมวลผลหลังสั่งหน้า Redirection (Terminate script execution smoothly)
        }

        // ---- 2. Check Users Table (ตรวจสอบตารางลูกค้าระบบทั่วไป) ----
        // ถ้าไม่ใช่แอดมิน ให้ค้นหาในตารางผู้ใช้งานทั่วไปแทน (If not an admin, check standard users table)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(); // ดึงข้อมูลลูกค้าถ้ามี (Fetch user details if found)

        // ถ้ารหัสผ่านลูกค้าถูกต้อง (If standard user exists and password is correct)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id']; // บันทึกไอดี (Save User ID in session)
            $_SESSION['role'] = 'general'; // กำหนดระดับสิทธิ์ลูกค้าทั่วไป Default role for users table
            $_SESSION['username'] = $user['username']; // บันทึกชื่อ (Save username in session)
            // ดึงผู้ใช้งานเข้าหน้าต่างเว็บไซต์หน้าแรก (Redirect regular user to the homepage)
            header("Location: index.php"); 
            exit();
        } else {
            // ถ้ารหัสผ่านผิดหรือไม่พบผู้ใช้งาน (Handle incorrect credentials)
            $message = "Invalid username or password."; // แจ้งว่ารหัสผ่านผิด (Show error message)
            $msg_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนดชื่อไตเติ้ลให้กับหน้านี้ (Set specific page title) -->
    <?php $page_title = 'Login - NightOwl Pub'; ?>
    <!-- ดึงข้อมูล head แบบรวมมิตร (Include common HTML Head elements) -->
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบนำทางด้านบน (Navigation Menu) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้เว็บไซต์ (Website string Logo linkage) -->
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            <ul class="flex space-x-6">
                <!-- ปุ่มสลับไปหน้าสมัครสมาชิก (Link to jump to registration page) -->
                <li><a href="register.php" class="text-gray-300 hover:text-secondary transition-colors">Register</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- พื้นที่แสดงกล่องรวมส่วนการล็อกอิน (Main layout wrapper for Login form box) -->
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <!-- กล่องสำหรับป้อนข้อมูลผู้ใช้ (Login form container) -->
        <div class="bg-surface p-8 rounded-lg shadow-2xl w-full max-w-md border border-gray-800">
            <h2 class="text-3xl font-bold text-center text-primary mb-6">Member Login</h2>
            
            <!-- หากมีข้อผิดพลาดหรือข้อความแจ้งเตือน ให้แสดงให้ลูกค้าเห็นตรงนี้ (Display alert box if a message exists) -->
            <?php if($message): ?>
                <!-- ตรวจสอบคลาสของกล่องข้อความเพื่อระบายสีเขียว(สำเร็จ) หรือ สีแดง (ผิดพลาด) 
                     (Toggle widget styles conditionally between success/error formats) -->
                <div class="mb-4 p-3 rounded <?php echo ($msg_class == 'success') ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200'; ?>">
                    <!-- แสดงเนื้อหาของข้อความอย่างปลอดภัยป้องกัน XSS (Display html-encoded message string) -->
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- ฟอร์มเข้าสู่ระบบ กำหนด method แบบ POST (Defining HTML Form posting logic) -->
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-gray-400 mb-1">Username</label>
                    <input type="text" name="username" required class="input-field">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required class="input-field">
                </div>
                <!-- ปุ่มส่งฟอร์ม (Submit Button) -->
                <button type="submit" class="btn w-full py-3 mt-4 text-darker font-bold text-lg hover:shadow-[0_0_15px_rgba(255,215,0,0.5)] transition-shadow">Login</button>
            </form>

            <p class="text-center mt-6 text-gray-400">
                <!-- หากยังไม่มีบัญชีให้กดไปหน้าลงสมัครสมาชิก (No account? provide link to Register instead) -->
                No account? <a href="register.php" class="text-secondary hover:text-primary transition-colors underline">Register here</a>
            </p>
        </div>
    </div>
    
    <!-- ส่วนของตอนท้ายของเว็บไซต์ (Website Footer section) -->
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
