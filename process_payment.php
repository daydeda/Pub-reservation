<?php
// process_payment.php -> Renamed to payment.php for clarity, or keep as is.
// Let's call it process_payment.php as per the previous form action.
// ไฟล์ตัวกลางจัดการระบบการชำระเงินก่อนบันทึกข้อมูลการจองจริง 
// Middleware file handling the deposit payment process before confirming reservation

// โหลดการตั้งค่าและการเชื่อมต่อกับฐานข้อมูล (Import db connection)
require 'config/db_connect.php';
// โหลดฟังก์ชันตรวจสอบสถานะผู้ใช้งาน (Import session management)
require 'includes/auth_session.php';
// บังคับให้เฉพาะผู้ที่ Login เข้ามาเท่านั้นที่สามารถเข้าถึงหน้านี้ได้ (Enforce login check)
checkLogin();

// Validate inputs
// นำข้อมูลที่จำเป็นทั้งหมดไปตรวจสอบ หากขาดสิ่งใดสิ่งหนึ่ง ให้เด้งไปหน้าจอง (Redirect back if required data is missing)
if (!isset($_GET['date']) || !isset($_GET['time']) || !isset($_GET['table_id'])) {
    header("Location: reservation.php");
    exit();
}

// รับค่าจากตัวแปร URL และนำข้อมูลมาเก็บไว้ในตัวแปร PHP (Extract GET parameters into variables)
$date = $_GET['date'];
$time = $_GET['time'];
$guests = $_GET['guests'];
$table_id = $_GET['table_id'];

// Fetch Table Details for display
// นำ Table ID ไปดึงข้อมูลรายละเอียดเพิ่มเติมของโต๊ะบนฐานข้อมูล (Get table details from db to show to user)
$stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE id = ?");
$stmt->execute([$table_id]);
$table = $stmt->fetch();

// หากไม่เจอข้อมูลโต๊ะในระบบ ให้หยุดทำงานพร้อมแจ้งเตือน (Terminate script if table not found)
if (!$table) {
    die("Invalid table selected.");
}

// Mock Price Config
// การจำลองการคำนวณเงินมัดจำ (Mockup logic to calculate booking deposit)
$base_price = 500; // ราคาฐานขั้นต่ำ 500 บาท (Base fee: 500 Baht)
$total_price = $base_price + ($guests * 100); // บวกค่าบริการเพิ่มคนละ 100 บาท (Additional 100 Baht per guest)
// หากเป็นโต๊ะประเภท VIP (If it is a VIP table, bump up the total price by 50%)
if ($table['type'] == 'vip') $total_price *= 1.5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- กำหนด Title เฉพาะหน้า (Set specific page title) -->
    <?php $page_title = 'Payment - NightOwl Pub'; ?>
    <!-- ดึงข้อมูล head แบบรวมมิตร (Included standard HTML Head definition) -->
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบเมนูด้านบน (Start of Navbar) -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- โลโก้เว็บไซต์ (Website Logo / Title linkage) -->
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
        </div>
    </nav>
    
    <!-- กรอบหลักสำหรับหน้าการชำระเงิน (Main Payment container wrapper) -->
    <div class="container mx-auto px-4 py-8 flex-grow max-w-2xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-primary">Confirm & Pay</h1>
        
        <!-- ส่วนสรุปรายการจองของลูกค้า (Reservation summary box) -->
        <div class="bg-surface p-6 rounded-lg border border-primary/30 shadow-lg mb-8">
            <h3 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Reservation Summary</h3>
            <!-- ข้อมูลที่รับมาแสดงเป็นตารางกริด (Grid display of reservation details) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-300">
                <p><strong class="text-gray-500">Date:</strong> <?php echo $date; ?></p>
                <p><strong class="text-gray-500">Time:</strong> <?php echo $time; ?></p>
                <p><strong class="text-gray-500">Guests:</strong> <?php echo $guests; ?></p>
                <!-- แสดงข้อมูลเบอร์โต๊ะและประเภทของโต๊ะ (Display Table # and Type) -->
                <p><strong class="text-gray-500">Table:</strong> <?php echo $table['table_number']; ?> (<?php echo ucfirst($table['type']); ?>)</p>
                <!-- แสดงตำแหน่งโซน (Display Zone string) -->
                <p><strong class="text-gray-500">Zone:</strong> <?php echo $table['zone']; ?></p>
            </div>
            <!-- เส้นคั่นบาง (Horizontal Separator) -->
            <hr class="border-gray-700 my-4">
            <!-- แสดงยอดเงินรวมที่ต้องชำระ (Show final total price for the deposit) -->
            <p class="text-2xl text-secondary font-bold text-center">Total Deposit: ฿<?php echo number_format($total_price, 2); ?></p>
        </div>
        
        <!-- ฟอร์มที่จะเรียกเมื่อกดปุ่มชำระเงิน (Form that posts data to complete_reservation) -->
        <form action="complete_reservation.php" method="POST">
            <!-- เก็บข้อมูลการจองไว้ในช่องลับไม่ให้เห็น เพื่อส่งต่อไปเซฟรวมทั้งหมด (Hidden inputs to carry reservation info along with payment POST) -->
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" name="time" value="<?php echo $time; ?>">
            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
            <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $total_price; ?>">
            <!-- ตัวแปรที่จะเก็บข้อมูลวิธีจ่ายเงินจาก Javascript (Hidden input holding final payment method via JS update) -->
            <input type="hidden" name="payment_method" id="payment_method" required>
            
            <h3 class="text-xl font-bold text-white mb-4">Select Payment Method</h3>
            <!-- ตัวเลือกในการชำระเงิน (A Grid featuring cards for different payment options) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <!-- ทางเลือก 1: บัตร ATM ข้อมูลจะส่งว่า ATM ไปที่ JS (Option 1: ATM) -->
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'atm')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">💳</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">ATM / Debit</div>
                </div>
                <!-- ทางเลือก 2: จ่ายคิวอาร์ (Option 2: QR Code) -->
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'qrcode')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">📱</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">QR Code</div>
                </div>
                <!-- ทางเลือก 3: แอปบัญชีธนาคาร (Option 3: Mobile Banking Application) -->
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'mobile_banking')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">🏦</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">Mobile Banking</div>
                </div>
            </div>
            
            <!-- ปุ่มกดตกลงเพื่อจ่ายเงิน (จะปิดใช้งานไว้จนกว่าจะกดเลือกวิธีจ่ายก่อน) (Payment button, kept disabled initially until selection is done) -->
            <button type="submit" class="btn w-full py-4 text-xl font-bold shadow-lg" id="payBtn" disabled>Pay & Book</button>
        </form>
    </div>
    
    <script>
        // ฟังก์ชันจาวาสคริปจัดการส่วนการคลิกเลือกรูปชำระเงินบนบราวเซอร์ลูกค้า (JS Function invoked when a user clicks a specific payment card)
        function selectMethod(el, method) {
            // ดึงค่าการชำระเงินลงใน hidden input (Set the payment hidden input to the chosen method string)
            document.getElementById('payment_method').value = method;
            
            // Reset all cards
            // ทำการเคลียร์ style ของการกดเลือกออกจากทุกอันก่อนหน้า (Clear active CSS states from all payment cards)
            document.querySelectorAll('.method-card').forEach(card => {
                card.classList.remove('border-secondary', 'ring-2', 'ring-secondary', 'shadow-[0_0_15px_rgba(0,255,65,0.5)]', 'bg-gray-800');
                card.classList.add('border-gray-700'); // ให้กลับไปเป้นเส้นสีเทาปกติ (Reset to default borders)
            });
            
            // Highlight selected
            // เพิ่ม style สีเขียวให้เฉพาะอันที่เพิ่งกดเลือก (Highlight the clicked element uniquely with active CSS classes)
            el.classList.remove('border-gray-700');
            el.classList.add('border-secondary', 'ring-2', 'ring-secondary', 'shadow-[0_0_15px_rgba(0,255,65,0.5)]', 'bg-gray-800');
            
            // หลังจากที่ลูกค้าเลือกวิธีจ่ายได้ ก็อนุญาตให้กดปุ่ม Pay & Book ส่งฟอร์มได้ (Enable the final Proceed to payment button now)
            document.getElementById('payBtn').disabled = false;
        }
    </script>
    <!-- ส่วนของ Footer เว็บไซต์ (Website Footer area) -->
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900 mt-auto">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
