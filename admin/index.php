<?php
// admin/index.php
// หน้าผังหลังสำหรับจัดการคิวจองและภาพรวมระบบของผู้ดูแลระบบ (Admin backend root page managing reservations list)

// ดึงแฟ้มสำหรับเชื่อมฐานข้อมูลโดยถอยกลับไป 1 โฟลเดอร์ (Include relative path to database config)
require '../config/db_connect.php';
// ดึงแฟ้มตรวจสอบเซสชัน (Include session management)
require '../includes/auth_session.php';
// เรียกใช้งานคำสั่งเตะเปรียบเสมือนป้อมยาม หากสิทธิ์ไม่ใช่ 'admin' ให้เด้งออกไป (Guard clause ensuring only roles='admin' can execute further)
checkAdmin(); // Enforce Admin Only

// Handle Actions (ส่วนประมวลผลเมื่อแอดมินกดส่งคำสั่งจากปุ่ม)
// ตรวจสอบว่าแอดมินส่งคำสั่งใดมาด้วย Form POST หรือไม่ เช่น กดอัปเดต หรือลบ (Check if a form payload was sent requiring action via POST)
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id']; // รหัสรายการจองที่ต้องการจัดการ (Retrieve specific reservation ID)
    
    // หากเป็นการสั่งอัพเดตสถานะ (If the requested action is to 'update' a status)
    if ($_POST['action'] == 'update') {
        $status = $_POST['status']; // เก็บสถานะใหม่ที่แอดมินเลือกจาก Dropdown (Get new status selection)
        // เตรียมคำสั่งเปลี่ยนแปลงสถานะบนตาราง reservations ของ ID นี้ (Prepare SQL UPDATE query targeting specific row)
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]); // สั่งให้ฐานข้อมูลอัปเดตค่าทันที (Commit update query execution)
    } 
    // หากคำสั่งระบุให้ 'ลบ' รายการนี้ทิ้งอย่างถาวร (If the requested action is to completely 'delete' row)
    elseif ($_POST['action'] == 'delete') {
        // เตรียมคำสั่งลบข้อมูลแถวรหัสนั้นออกไป (Prepare standard DELETE record query)
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]); // สั่งให้ลบเรียบร้อย (Execute deletion)
    }
}

// Fetch All Reservations (ส่วนดึงข้อมูลมาทำตารางสรุปทั้งหมด)
// สร้าง Query แบบ JOIN เพื่อไปดึงชื่อของ user, หมายเลขโต๊ะ(จาก dining_tables) และชื่อร้าน(pubs) มาทีเดียว 
// Complex SQL string using INNER JOIN across 4 tables to assemble comprehensive reservation row data
$stmt = $pdo->query("
    SELECT r.*, u.username, u.full_name, t.table_number, p.name as pub_name
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    JOIN dining_tables t ON r.table_id = t.id 
    JOIN pubs p ON t.pub_id = p.id
    ORDER BY r.reservation_date DESC, r.reservation_time ASC
");
// จัดเก็บข้อมูลรายชื่อทั้งหมดที่ Join มาได้เป็นอาร์เรย์เก็บให้ตัวแปร (Parse executed multi-dimensional output array)
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนดชื่อไตเติ้ลให้กับหน้าแอดมิน (Set static admin dashboard title) -->
    <?php $page_title = 'Admin Dashboard - NightOwl Pub'; ?>
    <!-- ดึงไฟล์ HTML Head กลางแบบย้อนถอยหน้าไปอีก 1 Folder (Reference to global head HTML file structure relative to root) -->
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบนำทางด้านบนสำหรับแอดมิน (Admin Navigation Menu area) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้แอดมินเชื่อมลิงก์กลับมาหน้าหลักนี้ (Admin Logo text string linkage backwards) -->
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Admin</a>
            <ul class="flex space-x-6">
                <!-- เนื่องจากอยู่หน้านี้ จึงใช้ตัวอักษรหนา(bold) และสีเด่นแทนตัวอื่น (Highlight 'Reservations' to indicate current scope) -->
                <li><a href="index.php" class="text-secondary font-bold transition-colors">Reservations</a></li>
                <!-- ลิงก์ไปหน้าร้าน pub (Link pointing towards Pubs administration page) -->
                <li><a href="pubs.php" class="text-gray-300 hover:text-secondary transition-colors">Pubs</a></li>
                <!-- กลับไปหน้าหลักของลูกค้า (Link to visit user-facing front-end pages) -->
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <!-- ปุ่ม Logout (Universal Logout path resolver element) -->
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- กรอบเนื้อหาหน้ารายการใหญ่ (Body wrapper layout containing large responsive Table view) -->
    <div class="container mx-auto px-4 py-8 flex-grow">
        <!-- ชื่อหัวข้อตาราง (List table prominent header element) -->
        <h1 class="text-3xl font-bold mb-8 text-primary border-l-4 border-primary pl-4">Reservation Management</h1>
        
        <!-- กรอบสำหรับทำให้ตารางสามารถเลื่อนซ้ายขวาได้หากเปิดในมือถือ (Scrollable outer container matching responsive layout rules) -->
        <div class="overflow-x-auto bg-surface rounded-lg shadow-lg border border-gray-800">
            <!-- แท็กคำสั่งสร้างตารางแสดงข้อมูลบรรทัดต่างๆ (HTML native table block initialization) -->
            <table class="w-full text-left border-collapse">
                <!-- แถวหัวตาราง (Table Head headers) -->
                <thead class="bg-gray-800 text-primary uppercase text-sm font-semibold">
                    <tr>
                        <th class="p-4 border-b border-gray-700">ID</th>
                        <th class="p-4 border-b border-gray-700">Pub / Table</th>
                        <th class="p-4 border-b border-gray-700">User</th>
                        <th class="p-4 border-b border-gray-700">Date/Time</th>
                        <th class="p-4 border-b border-gray-700">Guests</th>
                        <th class="p-4 border-b border-gray-700">Status</th>
                        <th class="p-4 border-b border-gray-700">Actions</th>
                    </tr>
                </thead>
                <!-- ส่วนแสดงผลข้อมูลเนื้อในทีได้จาก Arrays (Render container for loops displaying Table body contents) -->
                <tbody class="divide-y divide-gray-700">
                    <!-- ลูปรายการแต่ละแถวที่ไปดึง Database มาแล้ว (Apply iteration looping over fetched reservation strings) -->
                    <?php foreach($reservations as $r): ?>
                    <!-- เอฟเฟคเมื่อนำเมาส์ไปชี้จะเป็นไฮไลท์แถวให้สว่างกระพริบได้เล็กน้อย (Highlight hover element per row item) -->
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <!-- ID ของการจอง (Booking Index code) -->
                        <td class="p-4 text-gray-400">#<?php echo $r['id']; ?></td>
                        <td class="p-4">
                            <!-- ชื่อร้าน/สาขา (Venue Branch text display) -->
                            <span class="font-bold text-white block"><?php echo htmlspecialchars($r['pub_name']); ?></span>
                            <!-- รหัสโต๊ะ (Seat/Table code rendering field) -->
                            <span class="text-secondary font-mono text-sm">Table #<?php echo $r['table_number']; ?></span>
                        </td>
                        <td class="p-4">
                            <!-- ชื่อเจ้าของบัญชีผู้ทำรายการ (Real customer full name printed robust against injections) -->
                            <span class="font-bold text-white"><?php echo htmlspecialchars($r['full_name']); ?></span>
                            <br><span class="text-xs text-gray-500">@<?php echo $r['username']; ?></span>
                        </td>
                        <!-- วันและเวลาการจอง (Concatenate Date string and cropped short Time string view) -->
                        <td class="p-4 text-gray-300"><?php echo $r['reservation_date'] . ' <span class="text-gray-500">at</span> ' . substr($r['reservation_time'], 0, 5); ?></td>
                        <!-- จำนวนคนจอง (Aggregated numeric total guest tally rendering output) -->
                        <td class="p-4 text-gray-300"><?php echo $r['guest_count']; ?></td>
                        <td class="p-4">
                            <!-- แสดงฉลากที่มีสีแตกต่างกันไปตามสถานะปัจจุบันด้วย match() keyword ของ PHP8+ (Styled dynamically via associative switch match against enum value status mappings) -->
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                <?php 
                                    // กำหนดคลาสสีให้แต่ละสถานะโดยใช้ match (Assign distinct tailwind classes correlating logically relative strings matching db status values format rules logic mapping)
                                    echo match($r['status']) {
                                        'confirmed' => 'bg-green-900 text-green-300',
                                        'pending' => 'bg-yellow-900 text-yellow-300',
                                        'cancelled' => 'bg-red-900 text-red-300',
                                        'completed' => 'bg-blue-900 text-blue-300',
                                        default => 'bg-gray-700 text-gray-400'
                                    };
                                ?>">
                                <!-- พิมพ์สตริงป้ายสถานะด้วยอักษรตัวใหญ่ตัวแรก (Capitalize output status rendering) -->
                                <?php echo ucfirst($r['status']); ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <!-- ฟอร์มรวมปุ่มกดอัปเดตแบบ POST ตัวเล็กๆ ซ่อนอยู่ท้ายแถวตารางแต่ละบรรทัด (Small actionable HTML DOM Form posting updates per row item independently) -->
                            <form method="POST" class="flex items-center gap-2">
                                <!-- แอบส่งรหัสการจองไปด้วย (Hidden element holding explicit DB reservation row ID linking scope mapping logic parameter bindings context contextually implicitly tightly coupled with payload delivery form constraints logically consistently coherently seamlessly functionally ) -->
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <!-- ส่งแอคชั่นว่า update นะ (Explicit 'action=update' field) -->
                                <input type="hidden" name="action" value="update">
                                
                                <!-- Dropdown สำหรับเลือกสถานะใหม่ จะมี condition check ยุ่งยากนิดหน่อยเพื่อแสดงสถานะล่าสุดเป็นตัวถูก select ก่อน 
                                     (Dropdown box populating preset selections, utilizing inline ternary echo conditional testing checking comparing asserting assigning implicitly checking logically 'selected' boolean properties attributes respectively responsively functionally context ) -->
                                <select name="status" class="p-1 rounded bg-black border border-gray-600 text-sm focus:border-secondary focus:outline-none">
                                    <option value="pending" <?php if($r['status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="confirmed" <?php if($r['status']=='confirmed') echo 'selected'; ?>>Confirm</option>
                                    <option value="cancelled" <?php if($r['status']=='cancelled') echo 'selected'; ?>>Cancel</option>
                                    <option value="completed" <?php if($r['status']=='completed') echo 'selected'; ?>>Complete</option>
                                </select>
                                <!-- ปุ่มคำสั่งเซฟลงฐานข้อมูล Submit request Button (Stylized form actionable button rendering mapping bindings functionally seamlessly effectively elegantly contextually assertively coherently natively intuitively dependably responsibly successfully effectively correctly) -->
                                <button type="submit" class="bg-primary text-black px-3 py-1 rounded text-sm font-bold hover:bg-yellow-400 transition-colors">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
