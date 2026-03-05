<?php
// register.php
// หน้าต่างสำหรับสมัครสมาชิกใหม่ (Registration page for new users)

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล (Require database connection logic)
require 'config/config.php';
// นำเข้าไฟล์ตรวจสอบเซสชันเพื่อเริ่มการทำงานของ session (Require auth session logic primarily to allow session_start)
require 'includes/auth_session.php'; // For session start

// ประกาศตัวแปรเก็บข้อความข้อผิดพลาดเริ่มต้นเป็นค่าว่าง (Initialize message variable to empty string)
$message = '';

// ตรวจสอบว่าผู้ใช้กดส่งแบบฟอร์มด้วยวิธี POST มาหรือไม่ (Check if the registration form was submitted via POST method)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);

    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($phone) || empty($dob) || empty($_FILES['id_card']['name'])) {
        $message = "Please fill in all required fields and upload your ID Card.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!preg_match('/^0[689]\d{8}$/', $phone)) {
        $message = "Phone number must be a valid Thai format starting with 06, 08, or 09.";
    } else {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ? OR email = ? UNION SELECT ad_username FROM admins WHERE ad_username = ?");
        $stmt->execute([$username, $email, $username]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Username or Email already exists.";
        } else {
            // Handle file upload
            $upload_dir = 'uploads/id_cards/';
            // Ensure directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['id_card']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (!in_array($file_ext, $allowed_exts)) {
                $message = "Invalid file type. Only JPG, PNG, and PDF are allowed.";
            } else {
                $new_filename = uniqid('id_card_') . '.' . $file_ext;
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['id_card']['tmp_name'], $target_file)) {
                    $sql = "INSERT INTO users (username, email, user_password, full_name, phone_number, dob, id_card) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$username, $email, $password, $full_name, $phone, $dob, $target_file])) {
                        $_SESSION['success_msg'] = "Registration successful! Please wait for admin approval.";
                        header("Location: login.php");
                        exit();
                    } else {
                        $message = "Registration failed.";
                    }
                } else {
                    $upload_err = $_FILES['id_card']['error'];
                    $message = "Failed to upload ID Card. Error code: " . $upload_err;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนดชื่อไตเติ้ลเฉพาะบนเว็บเบราว์เซอร์ (Dynamic Page Title) -->
    <?php $page_title = 'Register'; ?>
    <!-- แทรกชุดคำสั่ง Header HTML รวม (Include HTML Head meta data and CSS references) -->
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบเมนูด้านบน (Website Navigation Bar) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- ลิงก์ย้อนกลับไปหน้าแรก (Home linkage via logo) -->
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">MaoHub</a>
            <ul class="flex space-x-6">
                <!-- ลิงก์กระโดดไปยังหน้าล็อกอินกรณีมีบัญชีอยู่แล้ว (Menu link pointing to Login) -->
                <li><a href="login.php" class="text-gray-300 hover:text-secondary transition-colors">Login</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- กรอบครอบคลุมเนื้อหากล่องสมัครสมาชิก (Main flex container to center the registration box) -->
    <div class="container mx-auto px-4 py-8 flex-grow flex items-center justify-center">
        <!-- กล่องสำหรับป้อนข้อมูลแบบฟอร์มผู้ใช้งาน (Registration form card box) -->
        <div class="bg-[#fdf8f5] p-8 rounded-lg shadow-2xl w-full max-w-lg border border-gray-800">
            <h2 class="text-3xl font-bold text-center text-primary mb-6">Create Account</h2>
            
            <!-- กรณีถ้าตรวจพบข้อผิดพลาด เช่น อีเมลซ้ำ ให้แสดงกล่องตกใจเตือน (Condition to display error alert box) -->
            <?php if($message): ?>
                <div class="mb-4 p-3 rounded bg-red-900 text-red-200">
                    <!-- แสดงแบบ htmlspecialchars เพื่อกันการฝังไส้กรอกโค้ดอันตราย (Print message safely) -->
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- กำหนดฟอร์มข้อมูลชนิด POST เพื่อส่งในหน้าปัจจุบัน (HTML Form configured for POST submission) -->
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <!-- ตารางแบ่งเป็น 2 คอลัมน์ สำหรับหน้าจอใหญ่ (Grid layout providing 2 columns on medium+ screens) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- ช่องกรอกชื่อผู้ใช้สำหรับเข้าระบบ (Input for Username) -->
                    <div>
                        <label class="block text-gray-600 mb-1">Username</label>
                        <input type="text" name="username" required class="input-field">
                    </div>
                    <!-- ช่องกรอกชื่อจริงและนามสกุล (Input for Full Name) -->
                    <div>
                        <label class="block text-gray-600 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="input-field">
                    </div>
                </div>
                <!-- ช่องสำหรับอีเมลผู้ใช้งาน (Input for Email address) -->
                <div>
                    <label class="block text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" required class="input-field">
                </div>
                <!-- ช่องสำหรับเบอร์มือถือ (ตัวเลือกเสริมไม่ต้องกรอก็ได้ถ้ากำหนดไว้) (Input for Phone number, optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 mb-1">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="" required class="input-field transition-colors duration-300">
                    </div>
                    <div>
                        <label class="block text-gray-600 mb-1">Date of Birth</label>
                        <input type="date" name="dob" required class="input-field" max="<?php echo date('Y-m-d', strtotime('-20 years')); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-600 mb-1">ID Card (JPG, PNG, PDF)</label>
                    <input type="file" name="id_card" accept=".jpg,.jpeg,.png,.pdf" required class="input-field py-2 text-white">
                </div>
                <!-- ช่องกรอกรหัสผ่าน (Input for password credentials) -->
                <div>
                    <label class="block text-gray-600 mb-1">Password</label>
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
    
    <!-- แทรก Footer template (Include the shared footer template) -->
    <?php include 'includes/footer.php'; ?>

    <!-- Real-time Validation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input:not([type="file"])');
        
        function validateInput(input) {
            const value = input.value.trim();
            let isValid = false;
            
            // If empty, remove customized styles
            if (value === '') {
                input.style.removeProperty('border-color');
                input.style.removeProperty('background-color');
                return;
            }

            switch(input.name) {
                case 'username':
                case 'full_name':
                case 'password':
                case 'dob':
                    isValid = value.length > 0;
                    break;
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(value);
                    break;
                case 'phone':
                    const phoneRegex = /^0[689]\d{8}$/;
                    isValid = phoneRegex.test(value);
                    break;
                default:
                    isValid = true;
            }

            if (isValid) {
                // Set green styles with !important
                input.style.setProperty('border-color', '#96ceb4', 'important');
                input.style.setProperty('background-color', '#e8f5e9', 'important');
            } else {
                // Set red styles with !important
                input.style.setProperty('border-color', '#ff8599', 'important');
                input.style.setProperty('background-color', '#ffebee', 'important');
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', function() { validateInput(this); });
            input.addEventListener('blur', function() { validateInput(this); });
        });
    });
    </script>
</body>
</html>
