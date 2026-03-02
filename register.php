<?php
// register.php
// หน้าต่างสำหรับสมัครสมาชิกใหม่ (Registration page for new users)

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล (Require database connection logic)
require 'config/db_connect.php';
// นำเข้าไฟล์ตรวจสอบเซสชันเพื่อเริ่มการทำงานของ session (Require auth session logic primarily to allow session_start)
require 'includes/auth_session.php'; // For session start

// ประกาศตัวแปรเก็บข้อความข้อผิดพลาดเริ่มต้นเป็นค่าว่าง (Initialize message variable to empty string)
$message = '';

// ตรวจสอบว่าผู้ใช้กดส่งแบบฟอร์มด้วยวิธี POST มาหรือไม่ (Check if the registration form was submitted via POST method)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าและเว้นวรรคช่องว่างซ้ายขวาออก (Retrieve POST values and trim whitespace)
    $username = trim($_POST['username']); // ค่าชื่อผู้ใช้ (Username)
    $email = trim($_POST['email']); // ค่าอีเมล (Email)
    $password = $_POST['password']; // รหัสผ่าน (Password)
    $full_name = trim($_POST['full_name']); // ชื่อ-นามสกุลจริง (Full name)
    $phone = trim($_POST['phone']); // เบอร์โทรศัพท์ (Phone number)

    // ตรวจสอบว่ากรอกข้อมูลจำเป็นครบถ้วนหรือไม่ (Check if any required fields are missing)
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        // หากขาดฟิลด์ใดไป ให้ขึ้นข้อความแจ้งเตือน (If required fields are missing, display error)
        $message = "Please fill in all required fields.";
    } else {
        // ---- Check if exists in users or admins ----
        // 1. ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลนี้มีคนใช้งานไปแล้วหรือยัง (Verify if username or email already exists in DB)
        // โดยเช็คทั้งจากตารางลูกค้า (users) และผู้ดูแลระบบ (admins) (Checking across both users and admins tables using UNION)
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ? OR email = ? UNION SELECT username FROM admins WHERE username = ?");
        // สั่งประมวลผลคำสั่ง SQL โดยส่งค่าพารามิเตอร์ 3 ตัว (Execute query binding the 3 placeholders)
        $stmt->execute([$username, $email, $username]);
        
        // ถ้านับแถวที่ค้นเจอแล้วพบรายการมากกว่า 0 แสดงว่าซ้ำซ้อน (If row count > 0, it means the username/email is taken)
        if ($stmt->rowCount() > 0) {
            $message = "Username or Email already exists."; // ตั้งค่าข้อความแจ้งเตือนว่าอีเมลหรือชื่อผู้ใช้ซ้ำ (Set error message)
        } else {
            // ---- ดำเนินการเพิ่มผู้ใช้งานลงระบบ ----
            // 2. ถ้าไม่ซ้ำ ก็ทำการเตรียม SQL สำหรับเพิ่มข้อมูลลงตาราง users (Prepare SQL query to INSERT new user)
            $sql = "INSERT INTO users (username, email, password, full_name, phone_number) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            // หากระบบสั่งรันคำสั่ง SQL สร้างข้อมูลได้สำเร็จ (Execute query, if successful proceed inside)
            if ($stmt->execute([$username, $email, $password, $full_name, $phone])) {
                // เก็บข้อความสำเร็จไว้ใน Session เพื่อดึงไปแสดงในหน้า login.php (Store success string in session to be flash-displayed on login page)
                $_SESSION['success_msg'] = "Registration successful! Please login.";
                // นำทางผู้ใช้กลับไปที่หน้าล็อกอิน (Redirect the user to the login page)
                header("Location: login.php");
                exit(); // หยุดการทงานส่วนด้านล่างหลังจากเปลี่ยนหน้า (Terminate script post-redirect)
            } else {
                // หากเซฟลงฐานข้อมูลแล้วเกิดปัญหาขัดข้องส่วนอื่นๆ (If database insert fails for other reasons)
                $message = "Registration failed."; // แจ้งเตือนข้อผิดพลาด (Show generic error)
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนดชื่อไตเติ้ลเฉพาะบนเว็บเบราว์เซอร์ (Dynamic Page Title) -->
    <?php $page_title = 'Register - NightOwl Pub'; ?>
    <!-- แทรกชุดคำสั่ง Header HTML รวม (Include HTML Head meta data and CSS references) -->
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบเมนูด้านบน (Website Navigation Bar) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- ลิงก์ย้อนกลับไปหน้าแรก (Home linkage via logo) -->
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            <ul class="flex space-x-6">
                <!-- ลิงก์กระโดดไปยังหน้าล็อกอินกรณีมีบัญชีอยู่แล้ว (Menu link pointing to Login) -->
                <li><a href="login.php" class="text-gray-300 hover:text-secondary transition-colors">Login</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- กรอบครอบคลุมเนื้อหากล่องสมัครสมาชิก (Main flex container to center the registration box) -->
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <!-- กล่องสำหรับป้อนข้อมูลแบบฟอร์มผู้ใช้งาน (Registration form card box) -->
        <div class="bg-surface p-8 rounded-lg shadow-2xl w-full max-w-lg border border-gray-800">
            <h2 class="text-3xl font-bold text-center text-primary mb-6">Create Account</h2>
            
            <!-- กรณีถ้าตรวจพบข้อผิดพลาด เช่น อีเมลซ้ำ ให้แสดงกล่องตกใจเตือน (Condition to display error alert box) -->
            <?php if($message): ?>
                <div class="mb-4 p-3 rounded bg-red-900 text-red-200">
                    <!-- แสดงแบบ htmlspecialchars เพื่อกันการฝังไส้กรอกโค้ดอันตราย (Print message safely) -->
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- กำหนดฟอร์มข้อมูลชนิด POST เพื่อส่งในหน้าปัจจุบัน (HTML Form configured for POST submission) -->
            <form method="POST" action="" class="space-y-4">
                <!-- ตารางแบ่งเป็น 2 คอลัมน์ สำหรับหน้าจอใหญ่ (Grid layout providing 2 columns on medium+ screens) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- ช่องกรอกชื่อผู้ใช้สำหรับเข้าระบบ (Input for Username) -->
                    <div>
                        <label class="block text-gray-400 mb-1">Username</label>
                        <input type="text" name="username" required class="input-field">
                    </div>
                    <!-- ช่องกรอกชื่อจริงและนามสกุล (Input for Full Name) -->
                    <div>
                        <label class="block text-gray-400 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="input-field">
                    </div>
                </div>
                <!-- ช่องสำหรับอีเมลผู้ใช้งาน (Input for Email address) -->
                <div>
                    <label class="block text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" required class="input-field">
                </div>
                <!-- ช่องสำหรับเบอร์มือถือ (ตัวเลือกเสริมไม่ต้องกรอก็ได้ถ้ากำหนดไว้) (Input for Phone number, optional) -->
                <div>
                    <label class="block text-gray-400 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="input-field">
                </div>
                <!-- ช่องกรอกรหัสผ่าน (Input for password credentials) -->
                <div>
                    <label class="block text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required class="input-field">
                </div>
                <!-- ปุ่มส่งคำขอยืนยันการสมัคร (Submit Registration Button) -->
                <button type="submit" class="btn w-full py-3 mt-4 text-darker font-bold text-lg hover:shadow-[0_0_15px_rgba(255,215,0,0.5)] transition-shadow">Register</button>
            </form>

            <p class="text-center mt-6 text-gray-400">
                <!-- ลิงก์หากมีแอคเคาท์แล้ว ไม่ต้องสมัครซ้ำ (Provide a simple shortcut Link to the Login page) -->
                Already have an account? <a href="login.php" class="text-secondary hover:text-primary transition-colors underline">Login here</a>
            </p>
        </div>
    </div>
    
    <!-- เพิ่มสคริปต์ท้ายเว็บเพจ (Appended Footer) -->
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
