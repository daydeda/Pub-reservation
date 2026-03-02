<?php
// index.php
// หน้าแรกของเว็บไซต์ (Homepage entry point)

// เรียกใช้งานไฟล์เชื่อมต่อฐานข้อมูล (Require database connection logic)
require 'config/db_connect.php';
// เรียกใช้งานระบบจัดการ Session (Require session management for authentication)
require 'includes/auth_session.php';
// Don't enforce login here, just show different content
// หมายเหตุ: หน้าแรกจะไม่มีการพ่นเช็คบังคับล็อกอิน (checkLogin) เพื่อให้ผู้เยี่ยมชมทั่วไปสามารถเห็นหน้าร้านได้ (Guest users are allowed to see the homepage without being redirected)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนด Title สำหรับแต่ละหน้า (Set page title injected into the head) -->
    <?php $page_title = 'Home - NightOwl Pub'; ?>
    <!-- ดึงไลบรารี่และส่วนเสริมของ HTML Head มาจากไฟล์ส่วนกลาง (Include common HTML Head elements) -->
    <?php include 'includes/head.php'; ?>
    <?php
    // คำสั่ง SQL เพื่อดึงรายการร้าน (สาขา) ทั้งหมดออกมาแสดง (SQL query to retrieve all pubs ordered by name)
    $stmt = $pdo->query("SELECT * FROM pubs ORDER BY name");
    // ฟอร์แมตข้อมูลดึดงออกมาเก็บในรูปแบบอาร์เรย์ (Fetch all rows into an associative array)
    $pubs = $stmt->fetchAll();
    ?>
</head>
<body class="bg-darker text-white font-sans">
    <!-- แถบเมนูด้านบน (Sticky Navigation Bar) -->
    <nav class="bg-surface border-b border-gray-800 p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้เว็บไซต์ (Website Logo / Title linkage) -->
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            
            <div class="space-x-4">
                <!-- ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่ (Check if user is currently logged in via session) -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- แสดงชื่อผู้ใช้ (Display greeting message with username) -->
                    <span class="text-gray-300">Welcome, <span class="text-secondary font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                    <!-- แสดงปุ่มออกจากระบบ (Logout link) -->
                    <a href="logout.php" class="text-sm text-error hover:text-white transition-colors ml-4">Logout</a>
                <?php else: ?>
                    <!-- กรณีที่ยังไม่ล็อกอิน ให้แสดงปุ่มล็อกอิน (Login button for guests) -->
                    <a href="login.php" class="btn">Login to Book</a>
                    <!-- ปุ่มสำหรับสมัครสมาชิกใหม่ (Registration link) -->
                    <a href="register.php" class="text-gray-300 hover:text-primary transition-colors ml-4">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <!-- แถบด้านบนแสดงแคปชันและบรรยากาศ (Large decorative banner demonstrating the pub theme) -->
    <header class="relative bg-black h-96 flex items-center justify-center overflow-hidden">
        <!-- เอฟเฟกต์ไล่ระดับการแสดงสีดำ (Gradient overlay for better text readability) -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-darker z-10"></div>
        <!-- รูปภาพบรรยากาศพื้นหลัง (Background atmospheric image) -->
        <img src="assets/pub_hq.jpg" alt="Pub Atmosphere" class="absolute inset-0 w-full h-full object-cover opacity-50">
        
        <!-- ข้อความบนแบนเนอร์ (Hero text content) -->
        <div class="relative z-20 text-center px-4">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-4 drop-shadow-[0_0_10px_rgba(255,215,0,0.5)]">
                <span class="text-primary">Night</span>Owl
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto">
                Neon lights, cold drinks, and good vibes. <br>Find your spot in the city.
            </p>
        </div>
    </header>

    <!-- Pub Selection -->
    <!-- พื้นที่แสดงสาขาของร้านทั้งหมด (Main section containing the list of pub locations) -->
    <main class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center text-secondary mb-12 uppercase tracking-widest">Select Your Location</h2>
        
        <!-- รูปแบบตารางยืดหยุ่นที่แสดงผลต่างๆ (Responsive grid layout for pub cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- วนลูปอ่านค่าร้านทุกแห่งที่ดึงมาจากฐานข้อมูล (Iterate through the fetched pubs data) -->
            <?php foreach($pubs as $pub): ?>
            <!-- ตกแต่งขอบเขตของการ์ด (Applying styling to individual pub card container) -->
            <div class="bg-surface rounded-lg overflow-hidden border border-gray-800 hover:border-primary transition-all hover:shadow-[0_0_20px_rgba(255,215,0,0.3)] group h-full flex flex-col">
                <!-- ส่วนกล่องภาพร้าน (Image container for pub) -->
                <div class="h-48 overflow-hidden relative">
                    <!-- แสดงภาพประกอบของร้านนั้น (Display pub image) -->
                    <img src="<?php echo htmlspecialchars($pub['image_url']); ?>" alt="<?php echo htmlspecialchars($pub['name']); ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <!-- แสดงป้ายแท็กเป็นย่านที่ตั้งของร้าน (Badge showing the pub location) -->
                    <div class="absolute top-0 right-0 bg-black/70 px-3 py-1 m-2 rounded text-xs text-secondary font-bold border border-secondary">
                        <?php echo htmlspecialchars($pub['location']); ?>
                    </div>
                </div>
                <!-- ส่วนข้อมูงรายละเอียดร้านภายในกล่อง (Card body for text and buttons) -->
                <div class="p-6 flex-grow flex flex-col">
                    <!-- แสดงชื่อของสาขา (Pub name display) -->
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($pub['name']); ?></h3>
                    <!-- แสดงรายละเอียดต่างๆ ของสาขานั้น (Pub description) -->
                    <p class="text-gray-400 mb-6 flex-grow"><?php echo htmlspecialchars($pub['description']); ?></p>
                    
                    <!-- ตรวจสอบว่าล็อกอินแล้วหรือยัง (Check login state to toggle button link) -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- เลือกรหัสร้านและส่งไปยังหน้าการจอง (Provide reservation link with the selected pub ID) -->
                        <a href="reservation.php?pub_id=<?php echo $pub['id']; ?>" class="btn w-full block">Book Here</a>
                    <?php else: ?>
                        <!-- หากยังไม่ล็อกอิน ให้ปุ่มส่งไปหน้าล็อกอิน (Direct guest users to login page instead) -->
                        <a href="login.php" class="btn bg-gray-700 text-gray-300 hover:bg-gray-600 w-full block">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- ส่วนของ Footer เว็บไซต์ (Website Footer area) -->
    <footer class="bg-black py-8 text-center text-gray-500 text-sm border-t border-gray-900">
        <p>&copy; 2026 NightOwl Pub. All rights reserved.</p>
    </footer>
</body>
</html>
