<?php
// admin/pubs.php
// หน้าผังหลังบ้านสำหรับผู้ดูแลระบบ จัดการตารางรายชื่อสาขา
// Admin backend page to manage pub locations/branches

// เรียกใช้งานไฟล์ config เพื่อติดต่อฐานข้อมูลย้อนกลับไปหนึ่งโฟลเดอร์ (Include DB mapped back one directory)
require '../config/config.php';
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
        $stmt = $pdo->prepare("INSERT INTO pubs (pub_name, location, description, image_url) VALUES (?, ?, ?, ?)");
        
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

// Handle Edit Pub
// ส่วนประมวลผลการแก้ไขข้อมูลร้าน (Process form submission to edit pub details)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $pub_id = $_POST['pub_id'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    if ($name && $location) {
        $stmt = $pdo->prepare("UPDATE pubs SET pub_name = ?, location = ?, description = ?, image_url = ? WHERE pub_id = ?");
        if ($stmt->execute([$name, $location, $description, $image_url, $pub_id])) {
            $success_msg = "Pub updated successfully!";
        } else {
            $error_msg = "Failed to update pub.";
        }
    } else {
        $error_msg = "Name and Location are required.";
    }
}

// Handle Delete Pub
// ส่วนประมวลผลการลบร้าน (Process form submission to delete a pub)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $pub_id = $_POST['pub_id'];
    $stmt = $pdo->prepare("DELETE FROM pubs WHERE pub_id = ?");
    if ($stmt->execute([$pub_id])) {
        $success_msg = "Pub deleted successfully!";
    } else {
        $error_msg = "Failed to delete pub.";
    }
}

// Fetch Pubs
// ส่วนของการดึงข้อมูลทุกร้านจากตาราง pubs เรียงตามรหัส (Fetch all pubs ordered by ID)
$stmt = $pdo->query("SELECT * FROM pubs ORDER BY pub_id ASC");
$pubs = $stmt->fetchAll(); // รับค่าเป็น Array 2 มิติ (Extract fully as associative nested array)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ตั้งชื่อ Page Title (Define static page title specific to admin pages) -->
    <?php $page_title = 'Manage Pubs'; ?>
    <!-- ดึงไฟล์ Header รวมมิตรที่ตั้งไว้ใน Folder includes (Include common header logic linking one dir backwards) -->
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบเมนูด้านบนของแอดมิน (Admin Navbar logic) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้แอดมินย้อนกลับไปหน้าแรก (Admin Logo shortcut back to index) -->
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">MaoHub Admin</a>
            <!-- ลิงก์ต่างๆ ของฝั่งแอดมิน (Admin navigation links array) -->
            <ul class="flex space-x-6">
                <!-- สลับไปหน้ารวมการจอง (Switch to reservation dashboard overview) -->
                <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Reservations</a></li>
                <!-- ปัจจุบันอยู่หน้านี้ จึงเปลี่ยนสีเน้นเป็นสีหลัก (Current active page highlighted with secondary color) -->
                <li><a href="pubs.php" class="text-secondary font-bold transition-colors">Pubs</a></li>
                <li><a href="users.php" class="text-gray-300 hover:text-secondary transition-colors">Users</a></li>
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
        <div class="bg-[#fdf8f5] p-6 rounded-lg border border-primary mb-8 max-w-2xl shadow-lg">
            <h2 class="text-xl font-bold text-darker mb-4">Add New Pub</h2>
            <form method="POST" class="space-y-4">
                <!-- ตัวแปรแอบซ่อนส่งข้อความแอคชั่นว่าเป็นการเพิ่มร้าน (Hidden payload specifying 'add' action context) -->
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <!-- ชื่อร้าน (Pub title setting) -->
                        <label class="block text-darker font-bold text-sm mb-1">Pub Name</label>
                        <input type="text" name="name" required class="input-field" placeholder="e.g. NightOwl Downtown">
                    </div>
                    <div>
                        <!-- ย่านของร้าน (Pub location district) -->
                        <label class="block text-darker font-bold text-sm mb-1">Location / District</label>
                        <input type="text" name="location" required class="input-field" placeholder="e.g. Central District">
                    </div>
                </div>
                <div>
                    <!-- รายละเอียดยาวๆ ของร้าน (Contextual detailed description text paragraph area) -->
                    <label class="block text-darker font-bold text-sm mb-1">Description</label>
                    <textarea name="description" rows="3" class="input-field" placeholder="Brief description of the vibe..."></textarea>
                </div>
                <div>
                    <!-- ลิงก์ที่เก็บไฟล์รูปภาพ (String reference to static folder containing mockup images) -->
                    <label class="block text-darker font-bold text-sm mb-1">Image URL</label>
                    <input type="text" name="image_url" class="input-field" placeholder="assets/pub_default.jpg" value="assets/pub_hq.jpg">
                </div>
                <!-- ปุ่มส่งคำร้องแอดข้อมูล (Confirm form insertion action) -->
                <button type="submit" class="btn">Add Pub</button>
            </form>
        </div>

        <!-- Pub List -->
        <!-- ส่วนรายการโชว์ข้อมูลร้านที่มีในฐานข้อมูลแบบการ์ดสวยงาม (Mapping array containing fetched Pub database rows over styled HTML blocks) -->
        <h2 class="text-2xl font-bold text-primary mb-4 border-l-4 border-primary pl-4">Existing Pubs</h2>
        <!-- ใช้กริด 3 คอลัมน์ (Display 3 columns wide per desktop resolution row) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- วนนำข้อมูลมาฉาย (Loop structure for populating dynamic array list blocks) -->
            <?php foreach($pubs as $pub): ?>
            <!-- ดีไซน์กล่องครอบด้านนอกของแต่ละสาขา (Outer wrapper card for independent pub box item) -->
            <div class="bg-[#fdf8f5] rounded-lg overflow-hidden border border-primary flex flex-col shadow-lg" id="pub-card-<?php echo $pub['pub_id']; ?>">
                <!-- ครอบรูปหน้าปก (Hero inner image wrapper) -->
                <div class="h-40 overflow-hidden relative">
                    <!-- รูปภาพหลัก (Hero preview cover picture parsing local folder routing logic) -->
                    <img src="../<?php echo htmlspecialchars($pub['image_url']); ?>" alt="<?php echo htmlspecialchars($pub['pub_name']); ?>" class="w-full h-full object-cover">
                    <!-- แท็กป้ายรหัสไอดีร้านเอาไว้โชว์ (Absolute corner tag displaying DB Pub ID) -->
                    <div class="absolute top-0 right-0 bg-secondary/90 px-2 py-1 m-2 rounded text-xs text-white font-bold shadow">
                        ID: <?php echo $pub['pub_id']; ?>
                    </div>
                </div>

                <!-- ส่วนแสดงข้อมูลปกติ (Normal display view) -->
                <div class="p-4 flex-grow pub-display" id="pub-display-<?php echo $pub['pub_id']; ?>">
                    <h3 class="text-xl font-bold text-darker mb-1"><?php echo htmlspecialchars($pub['pub_name']); ?></h3>
                    <p class="text-sm text-secondary font-bold mb-3"><?php echo htmlspecialchars($pub['location']); ?></p>
                    <p class="text-dark font-medium text-sm"><?php echo htmlspecialchars($pub['description']); ?></p>
                </div>

                <!-- ส่วนฟอร์มแก้ไข (Edit form, hidden by default) -->
                <div class="p-4 flex-grow pub-edit-form hidden" id="pub-edit-<?php echo $pub['pub_id']; ?>">
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="pub_id" value="<?php echo $pub['pub_id']; ?>">
                        <div>
                            <label class="block text-darker font-bold text-xs mb-1">Pub Name</label>
                            <input type="text" name="name" required class="input-field" value="<?php echo htmlspecialchars($pub['pub_name']); ?>">
                        </div>
                        <div>
                            <label class="block text-darker font-bold text-xs mb-1">Location</label>
                            <input type="text" name="location" required class="input-field" value="<?php echo htmlspecialchars($pub['location']); ?>">
                        </div>
                        <div>
                            <label class="block text-darker font-bold text-xs mb-1">Description</label>
                            <textarea name="description" rows="2" class="input-field"><?php echo htmlspecialchars($pub['description']); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-darker font-bold text-xs mb-1">Image URL</label>
                            <input type="text" name="image_url" class="input-field" value="<?php echo htmlspecialchars($pub['image_url']); ?>">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-primary text-black px-4 py-2 rounded text-sm font-bold hover:bg-yellow-400 transition-colors">Save</button>
                            <button type="button" onclick="toggleEdit(<?php echo $pub['pub_id']; ?>)" class="bg-gray-700 text-white px-4 py-2 rounded text-sm font-bold hover:bg-gray-600 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- ส่วนแสดงผลท้ายการ์ด พร้อมปุ่ม Edit / Delete (Card footer with action buttons) -->
                <div class="p-4 border-t border-primary/30 bg-white/50 flex flex-wrap justify-between items-center rounded-b-lg gap-2">
                    <span class="text-xs text-darker font-medium">Created: <?php echo $pub['created_at']; ?></span>
                    <div class="flex flex-wrap gap-2">
                        <!-- ปุ่มแก้ Layout (Edit Layout logic) -->
                        <a href="layout.php?pub_id=<?php echo $pub['pub_id']; ?>" class="bg-purple-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-purple-500 transition-colors">
                            Layout
                        </a>
                        <!-- ปุ่มแก้ไข (Edit toggle button) -->
                        <button onclick="toggleEdit(<?php echo $pub['pub_id']; ?>)" class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-blue-500 transition-colors" id="edit-btn-<?php echo $pub['pub_id']; ?>">
                            Edit
                        </button>
                        <!-- ปุ่มลบพร้อมยืนยัน (Delete button with confirmation) -->
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this pub?');" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="pub_id" value="<?php echo $pub['pub_id']; ?>">
                            <button type="submit" class="bg-red-700 text-white px-3 py-1 rounded text-xs font-bold hover:bg-red-600 transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JavaScript สำหรับสลับโหมดแสดงผล/แก้ไข (Toggle between display and edit views) -->
    <script>
        function toggleEdit(pubId) {
            const display = document.getElementById('pub-display-' + pubId);
            const editForm = document.getElementById('pub-edit-' + pubId);
            const editBtn = document.getElementById('edit-btn-' + pubId);

            if (editForm.classList.contains('hidden')) {
                // Show edit form, hide display
                display.classList.add('hidden');
                editForm.classList.remove('hidden');
                editBtn.textContent = 'Cancel';
                editBtn.classList.remove('bg-blue-600', 'hover:bg-blue-500');
                editBtn.classList.add('bg-gray-600', 'hover:bg-gray-500');
            } else {
                // Show display, hide edit form
                display.classList.remove('hidden');
                editForm.classList.add('hidden');
                editBtn.textContent = 'Edit';
                editBtn.classList.remove('bg-gray-600', 'hover:bg-gray-500');
                editBtn.classList.add('bg-blue-600', 'hover:bg-blue-500');
            }
        }
    </script>
</body>
</html>
