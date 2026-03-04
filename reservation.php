<?php
// reservation.php
// ไฟล์จัดการหน้าจอการจองโต๊ะของลูกค้า
// Reservation page file for customers

// เชื่อมต่อฐานข้อมูล (Connect to database)
require 'config/config.php';
// ดึงไฟล์ตรวจสอบเซสชันผู้ใช้ (Include user session verification)
require 'includes/auth_session.php';
// ตรวจสอบว่าเข้าระบบหรือยัง (Check if the user is already logged in)
checkLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    // ตรวจสอบว่ามีการส่งรหัสร้าน (pub_id) มาหรือไม่ (Check if pub_id parameter exists in URL)
    if (!isset($_GET['pub_id'])) {
        header("Location: index.php"); // ถ้าไม่มีให้กลับไปหน้าแรก (Redirect to index if missing)
        exit;
    }

    // แปลงรหัสร้านที่ได้เป็นตัวเลขเพื่อความปลอดภัย (Cast pub_id to integer for safety)
    $pub_id = (int)$_GET['pub_id'];

    // Fetch Pub Details
    // ดึงข้อมูลรายละเอียดของร้านสาขานั้นๆ จากฐานข้อมูล (Fetch the associated pub details from database)
    $stmt = $pdo->prepare("SELECT * FROM pubs WHERE pub_id = ?");
    $stmt->execute([$pub_id]);
    $pub = $stmt->fetch();

    // หากไม่พบร้านที่ระบุ ให้เด้งกลับหน้าแรก (Redirect back if the pub does not exist)
    if (!$pub) {
        header("Location: index.php");
        exit;
    }

    // กำหนดชื่อ Title ของหน้าเว็บให้สอดคล้องกับชื่อร้าน (Dynamic page title setting)
    $page_title = 'Reserve - ' . htmlspecialchars($pub['pub_name']);
    ?>
    <!-- เรียกแสดงผลส่วนหัว (HTML Head) จากไฟล์ส่วนกลาง -->
    <?php include 'includes/head.php'; ?>
    
    <script>
        // กำหนด global javascript variable สำหรับรหัสร้าน (Set global JS variable for pub ID)
        const CURRENT_PUB_ID = <?php echo $pub_id; ?>;
    </script>
</head>
<body class="bg-darker text-white font-sans flex flex-col min-h-screen">
    <!-- แถบนำทางด้านบน (Navigation Bar) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">
                <span class="text-sm text-gray-400 font-normal block -mb-1 text-white">Booking at</span>
                <?php echo htmlspecialchars($pub['pub_name']); // แสดงชื่อสาขา (Show current pub name) ?>
            </a>
            <div class="space-x-4">
                <a href="index.php" class="text-gray-300 hover:text-white transition-colors">Switch Location</a>
                <a href="logout.php" class="text-sm text-error hover:text-white transition-colors ml-4">Logout</a>
            </div>
        </div>
    </nav>

    <!-- พื้นที่แสดงผลลัพธ์หลัก (Main content container) -->
    <div class="container mx-auto px-4 py-6 flex-grow flex flex-col h-full">
        <div class="flex flex-col md:flex-row gap-6 h-full flex-grow">
            <!-- แถบควบคุมด้านข้างสำหรับกรอกรายละเอียดจอง (Sidebar Controls for reservation details) -->
            <div class="w-full md:w-1/3 lg:w-1/4 bg-[#fdf8f5] p-6 rounded-lg border border-[#f67280] flex flex-col shadow-lg">
                <h2 class="text-xl font-bold text-[#f67280] mb-4 border-b border-[#f67280] pb-2">Reservation Details</h2>

                <!-- ฟอร์มเพื่อเลือกข้อมูลวัน เวลา และจำนวนคน (Form grouping input criteria) -->
                <form id="bookingForm" action="payment.php" method="GET" class="space-y-4 flex-grow">
                    <!-- จำรหัสสาขาเพื่อส่งต่อให้หน้าถัดไป (Pass pub_id to payment page secretly) -->
                    <input type="hidden" name="pub_id" value="<?php echo $pub_id; ?>">
                    
                    <div>
                        <label class="block text-[#35477d] font-bold text-sm mb-1">Date</label>
                        <!-- ช่องเลือกวันที่ (Date picker) -->
                        <input type="date" id="date" name="date" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-[#35477d] font-bold text-sm mb-1">Time</label>
                        <!-- ช่องเลือกเวลา (Time picker) -->
                        <input type="time" id="time" name="time" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-[#35477d] font-bold text-sm mb-1">Guests</label>
                        <!-- ช่องกรอกจำนวนคน รองรับ 1-20 คน (Guests count input) -->
                        <input type="number" id="guests" name="guests" class="input-field" min="1" max="20" required>
                    </div>

                    <!-- ปุ่มสั่งให้ดึงข้อมูลแผนผังพร้อมอัปเดต (Trigger button for mapping load) -->
                    <button type="button" id="updateMapBtn" class="btn w-full mt-4">Update Map</button>

                    <hr class="border-gray-700 my-4">

                    <!-- พื้นที่แสดงข้อมูลเมื่อผู้ใช้คลืกเลือกโต๊ะจากแผนผัง (Selection info container, initially hidden) -->
                    <div id="selectionInfo" class="hidden animate-fade-in">
                        <p class="text-[#f67280] font-bold text-sm">Selected Table:</p>
                        <!-- ข้อความแสดงการคลิกเลือก (Selected table display text) -->
                        <p class="text-2xl font-bold text-[#35477d] mb-2" id="selectedTableDisplay">--</p>
                        <!-- รหัสโต๊ะที่จะถูกระบุค่าด้วย Javascript เมื่อถูกคลิกเลือก (Hidden table ID set via JS) -->
                        <input type="hidden" id="table_id" name="table_id">
                        
                        <!-- ปุ่มส่งข้อมูลเข้าระบบเพื่อดำเนินจ่ายเงินมัดจำ (Proceed button to payment page) -->
                        <button type="submit" id="submitBtn" class="btn w-full bg-secondary text-black hover:bg-green-400 font-extrabold shadow-[0_0_15px_rgba(0,255,65,0.4)]">Proceed to Payment</button>
                    </div>
                </form>
            </div>

            <!-- กล่องแสดงแผนผังร้านอาหาร/ผับ (Map Container area) -->
            <div class="w-full md:w-2/3 lg:w-3/4 bg-[#fffbfa] rounded-lg border-2 border-[#f67280] relative overflow-hidden shadow-inner flex-grow min-h-[500px]" id="mapContainer">
                <!-- สเตจที่มีการเปลี่ยนพื้นหลังเป็นสีเบจ (Stage marker with beige background) -->
                <div class="stage-marker !bg-[#f67280] !text-[#fdf8f5] !border-[#f67280]">STAGE</div>
                
                <!-- กล่องอธิบายสีโต๊ะที่มุมซ้ายบนของกระดาน (Legend toolbox indicating table statuses) -->
                <div class="absolute top-4 left-4 bg-white/90 p-2 rounded text-xs text-[#35477d] font-bold pointer-events-none z-10 border border-[#f67280] shadow">
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-green-500"></span> Available</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Reserved</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-gray-500"></span> Incompatible</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-secondary border border-white"></span> Selected</div>
                </div>
                <!-- Tables will be injected here via JS -->
                <!-- เลเยอร์ที่ไว้เรียงไอค่อนโต๊ะที่สร้างจาก JS (Layer where JS will dynamically mount tables) -->
                <div id="tablesLayer" class="w-full h-full relative text-white"></div>
            </div>
        </div>
    </div>
    
    <!-- ดึงไฟล์ javascript ไปใช้ โดยพ่วง Parameter Time เพื่อบังคับไม่ให้บราวเซอร์แคช (Include JS logic with cache-buster) -->
    <script src="js/reservation.js?v=<?php echo time(); ?>"></script>
    <!-- แทรก Footer template (Include the shared footer template) -->
    <?php include 'includes/footer.php'; ?>
