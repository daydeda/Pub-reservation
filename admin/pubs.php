<?php
// admin/pubs.php
// หน้าผังหลังบ้านสำหรับผู้ดูแลระบบ จัดการตารางรายชื่อสาขา
// Admin backend page to manage pub locations/branches

// เรียกใช้งานไฟล์ config เพื่อติดต่อฐานข้อมูลย้อนกลับไปหนึ่งโฟลเดอร์ (Include DB mapped back one directory)
require '../config/db_connect.php';
// เรียกใช้ระบบเซสชัน (Include session logic)
require '../includes/auth_session.php';
// ตรวจสอบเช็คสิทธิ์ว่าเป็น admin หรือไม่ (Verify if the current session role is 'admin')
checkAdmin();

// สร้างตัวแปรไว้เก็บข้อความแจ้งเตือน (Initialize status message variables)
$success_msg = '';
$error_msg = '';

// Handle Add Pub
// ส่วนประมวลผลการส่งฟอร์มเพื่อเพิ่มสาขาร้านอาหารใหม่ 
// (Process form submission to add new pub branch)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    // รับค่าและจัดเก็บเข้าตัวแปรล่วงหน้า (Assign POST inputs to specific local variables)
    $name = $_POST['name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    // ตรวจสอบข้อมูลจำเป็น (Validate required fields)
    if ($name && $location) {
        // เตรียม SQL สำหรับเพิ่มข้อมูลร้านใหม่ลงตาราง pubs (Prepare INSERT query for pubs table)
        $stmt = $pdo->prepare("INSERT INTO pubs (name, location, description, image_url) VALUES (?, ?, ?, ?)");
        
        // ถ้าสั่งทำงานแล้วสำเร็จจริง (Execution checks)
        if ($stmt->execute([$name, $location, $description, $image_url])) {
            $success_msg = "Pub added successfully!"; // สร้างคำความสำเร็จ (Update success text)
        } else {
            $error_msg = "Failed to add pub."; // แจ้งเตือนข้อผิดพลาดเกี่ยวกับการ Execute ล้ม (Update error text on DB fail)
        }
    } else {
        $error_msg = "Name and Location are required."; // แจ้งเตือนให้กรอกข้อมูลให้ครบถ้วน (Prompt required missing fields)
    }
}

// Fetch Pubs
// ส่วนของการดึงข้อมูลทุกร้านจากตาราง pubs เรียงตามเวลาที่สร้างล่าสุด 
// (Fetch all pubs ordered by their creation timestamp descending)
$stmt = $pdo->query("SELECT * FROM pubs ORDER BY created_at DESC");
$pubs = $stmt->fetchAll(); // รับค่าเป็น Array 2 มิติ (Extract fully as associative nested array)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ตั้งชื่อ Page Title (Define static page title specific to admin pages) -->
    <?php $page_title = 'Manage Pubs - NightOwl Admin'; ?>
    <!-- ดึงไฟล์ Header รวมมิตรที่ตั้งไว้ใน Folder includes (Include common header logic linking one dir backwards) -->
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบเมนูด้านบนของแอดมิน (Admin Navbar logic) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้แอดมินย้อนกลับไปหน้าแรก (Admin Logo shortcut back to index) -->
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Admin</a>
            <!-- ลิงก์ต่างๆ ของฝั่งแอดมิน (Admin navigation links array) -->
            <ul class="flex space-x-6">
                <!-- สลับไปหน้ารวมการจอง (Switch to reservation dashboard overview) -->
                <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Reservations</a></li>
                <!-- ปัจจุบันอยู่หน้านี้ จึงเปลี่ยนสีเน้นเป็นสีหลัก (Current active page highlighted with secondary color) -->
                <li><a href="pubs.php" class="text-secondary font-bold transition-colors">Pubs</a></li>
                <!-- ย้อนจากหลังบ้านไปดูหน้าบ้าน (Access front-end user portal directly) -->
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <!-- ปุ่มลอคเอ้าท์ (Invoke global logout logic) -->
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- พื้นที่แสดงเนื้อหาจัดการ (Main administrative content area framework) -->
    <div class="container mx-auto px-4 py-8 flex-grow">
        <!-- หัวข้อหลักมีแถบสีเหลืองด้านซ้าย (Admin Dashboard internal page header title) -->
        <h1 class="text-3xl font-bold mb-8 text-primary border-l-4 border-primary pl-4">Manage Pub Locations</h1>
        
        <!-- เช็คและแสดงข้อความแจ้งเตือน (Conditionally render success/error boxes based on logic results above) -->
        <?php if($success_msg): ?>
            <!-- กล่องข้อความแจ้งเดือนสำเร็จสีเขียว (Success widget Box styled positively) -->
            <div class="bg-green-900 text-green-300 p-4 rounded mb-6 border border-green-700"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <!-- กล่องข้อความปฏิเสธสีแดง (Error widget Box styled negatively) -->
            <div class="bg-red-900 text-red-300 p-4 rounded mb-6 border border-red-700"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Add Pub Form -->
        <!-- ฟอร์มช่องกรอกข้อมูลสร้างสาขาใหม่ (Form container mapped toward database insertions) -->
        <div class="bg-surface p-6 rounded-lg border border-gray-800 mb-8 max-w-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Add New Pub</h2>
            <form method="POST" class="space-y-4">
                <!-- ตัวแปรแอบซ่อนส่งข้อความแอคชั่นว่าเป็นการเพิ่มร้าน (Hidden payload specifying 'add' action context) -->
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <!-- ชื่อร้าน (Pub title setting) -->
                        <label class="block text-gray-400 text-sm mb-1">Pub Name</label>
                        <input type="text" name="name" required class="input-field" placeholder="e.g. NightOwl Downtown">
                    </div>
                    <div>
                        <!-- ย่านของร้าน (Pub location district) -->
                        <label class="block text-gray-400 text-sm mb-1">Location / District</label>
                        <input type="text" name="location" required class="input-field" placeholder="e.g. Central District">
                    </div>
                </div>
                <div>
                    <!-- รายละเอียดยาวๆ ของร้าน (Contextual detailed description text paragraph area) -->
                    <label class="block text-gray-400 text-sm mb-1">Description</label>
                    <textarea name="description" rows="3" class="input-field" placeholder="Brief description of the vibe..."></textarea>
                </div>
                <div>
                    <!-- ลิงก์ที่เก็บไฟล์รูปภาพ (String reference to static folder containing mockup images) -->
                    <label class="block text-gray-400 text-sm mb-1">Image URL</label>
                    <input type="text" name="image_url" class="input-field" placeholder="assets/pub_default.jpg" value="assets/pub_hq.jpg">
                </div>
                <!-- ปุ่มส่งคำร้องแอดข้อมูล (Confirm form insertion action) -->
                <button type="submit" class="btn">Add Pub</button>
            </form>
        </div>

        <!-- Pub List -->
        <!-- ส่วนรายการโชว์ข้อมูลร้านที่มีในฐานข้อมูลแบบการ์ดสวยงาม (Mapping array containing fetched Pub database rows over styled HTML blocks) -->
        <h2 class="text-2xl font-bold text-white mb-4">Existing Pubs</h2>
        <!-- ใช้กริด 3 คอลัมน์ (Display 3 columns wide per desktop resolution row) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- วนนำข้อมูลมาฉาย (Loop structure for populating dynamic array list blocks) -->
            <?php foreach($pubs as $pub): ?>
            <!-- ดีไซน์กล่องครอบด้านนอกของแต่ละสาขา (Outer wrapper card for independent pub box item) -->
            <div class="bg-surface rounded-lg overflow-hidden border border-gray-800 flex flex-col">
                <!-- ครอบรูปหน้าปก (Hero inner image wrapper) -->
                <div class="h-40 overflow-hidden relative">
                    <!-- รูปภาพหลัก (Hero preview cover picture parsing local folder routing logic) -->
                    <img src="../<?php echo htmlspecialchars($pub['image_url']); ?>" alt="<?php echo htmlspecialchars($pub['name']); ?>" class="w-full h-full object-cover">
                    <!-- แท็กป้ายรหัสไอดีร้านเอาไว้โชว์ (Absolute corner tag displaying DB Pub ID) -->
                    <div class="absolute top-0 right-0 bg-black/70 px-2 py-1 m-2 rounded text-xs text-secondary border border-secondary">
                        ID: <?php echo $pub['id']; ?>
                    </div>
                </div>
                <!-- ส่วนเนื้อหาข้อความ (Inner information rendering mapping data sets onto structural strings) -->
                <div class="p-4 flex-grow">
                    <h3 class="text-xl font-bold text-white mb-1"><?php echo htmlspecialchars($pub['name']); ?></h3>
                    <p class="text-sm text-secondary mb-3"><?php echo htmlspecialchars($pub['location']); ?></p>
                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($pub['description']); ?></p>
                </div>
                <!-- ส่วนแสดงผลท้ายการ์ด (Tail card footer indicating record lifespan status) -->
                <div class="p-4 border-t border-gray-700 bg-black/20">
                    <span class="text-xs text-gray-500">Created: <?php echo $pub['created_at']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
