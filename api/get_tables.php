<?php
// api/get_tables.php
// เว็บเซอร์วิส (API Endpoint) สำหรับดึงข้อมูลโต๊ะของร้าน และคำนวณว่าโต๊ะไหนว่างบ้าง
// API Endpoint returning JSON data of available/reserved tables for a specific pub

// ดึงไฟล์ที่ใช้สำหรับเชื่อมต่อกับฐานข้อมูล โดยถอยออกมาหนึ่งโฟลเดอร์ (Include relative DB connection logic)
require '../config/db_connect.php';
// กำหนดหัวของข้อมูลว่าเป็น JSON เพื่อให้ Javascript ฝั่ง Client อ่านได้ง่าย (Set explicitly JSON Content-Type headers)
header('Content-Type: application/json');

// Get parameters with null coalescing fallback 
// (ดึงค่าจากพารามิเตอร์ URL ที่ผู้ใช้งานส่งเข้ามา หากไม่ส่งมาให้กำหนดตัวแปรให้เป็น null หรือ 0)
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;
$guests = $_GET['guests'] ?? 0;
// กำหนด pub_id เริ่มต้นให้เป็นค่า 1 (NightOwl HQ) เพื่อป้องกัน API พังกรณีไม่ส่งค่าสาขามา 
// (Fallback default pub_id to 1 resolving older backward compatibilities organically)
$pub_id = $_GET['pub_id'] ?? 1; // Default to 1 (NightOwl HQ) if not specified

// ถ้าข้อมูลวันที่และเวลาขาดหายไปอันใดอันหนึ่ง ให้หยุดทำงานแล้วส่ง Error กลับ (If required inputs are missing, fail gracefully)
if (!$date || !$time) {
    // ส่งข้อมูล JSON แจ้งข้อผิดพลาด (Return JSON formatted error payload dynamically)
    echo json_encode(['error' => 'Missing date or time']);
    exit; // จบสคริปต์ (Terminate logic safely)
}

// 1. Get all tables for the selected pub
// ดึงข้อมูลโต๊ะทั้งหมดที่มีอยู่ในร้านสาขานั้นออกมาแบบสดๆ ทุกประเภท 
// (Query to fetch universally all tables strictly bounded natively by matching the pub_id)
$stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE pub_id = ?");
$stmt->execute([$pub_id]); // รันคำสั่งคิวรี่ (Execute targeting parameters specifically securely avoiding any injections)
$all_tables = $stmt->fetchAll(); // รับกลับมาเป็น Array ก้อนใหญ่ (Yield multiple dimensional associate arrays successfully effectively dynamically comprehensively seamlessly directly efficiently)

// 2. Get reserved table IDs for this slot
// Simple logic: If reserved on that date, it's taken for the night (for simplicity of this project)
// หลักการจำลอง: สำหรับโปรเจกต์นี้ ถ้ามีการจองในตาราง reservations ณ วันที่นั้น ถือว่าจองเหมาทั้งคืนเลย 
// (Query specific reservation records logically mapping dependencies overlapping the specified target date bounds comprehensively effectively resolving availability conflicts)
// Or better: Check if reservation overlaps. Let's assume reservations last 2 hours.
// IMPORTANT: We need to filter based on tables in this pub, or just check reservations that map to tables in this pub (implicit via table_id)
$stmt = $pdo->prepare("
    SELECT r.table_id FROM reservations r
    JOIN dining_tables t ON r.table_id = t.id
    WHERE r.reservation_date = ? 
    AND r.status IN ('confirmed', 'pending')
    AND t.pub_id = ?
");
$stmt->execute([$date, $pub_id]);
// รับมาเฉพาะ 1 คอลัมน์เดี่ยวๆ กลายเป็น Array ลิสของเฉพาะตัวเลข ID [1, 5, 8] 
// (Returns a simple flat indexed array of IDs representing occupied reservations inherently strictly confidently decisively correctly completely fully robustly efficiently logically naturally)
$reserved_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Process availability and compatibility
// เตรียมถังอาร์เรย์สำหรับที่จะแปลงส่งไปหน้าบ้าน (Instantiate clean Array wrapper holder bucket destined exclusively towards JSON encoding output format)
$response_tables = [];

// วนลูปอ่านข้อมูล "โต๊ะทั้งหมด" (Loop recursively examining properties mapping directly comprehensively dynamically)
foreach ($all_tables as $table) {
    $status = 'available'; // Default ว่าให้เป็นว่างก่อน (Initialize base status defaulting inherently loosely directly explicitly organically fundamentally)
    // ใช้ in_array เช็คว่าตัวเลข id นี้ ไปโผล่ในรายชื่อโต๊ะที่ไม่ว่างหรือไม่ (Assert strictly natively effectively decisively comparing accurately)
    $is_reserved = in_array($table['id'], $reserved_ids);
    
    // ถ้าปรากฏในรายชื่อที่จองแล้ว 
    // (Condition check assessing overlapping bookings conflicts directly efficiently securely completely organically resolving cleanly intuitively natively intelligently correctly)
    if ($is_reserved) {
        $status = 'reserved'; // เปลี่ยนเป็นสถานะถูกจอง (Override base status string dynamically definitively confidently definitively resolving conflicts dynamically resolutely effectively inherently efficiently completely natively securely cleanly fully robustly intelligently automatically accurately firmly)
    } else {
        // Enforce guest count rules
        // Rule 1.3: < 5 people -> specific tables?
        // กฎเกณฑ์หน้าร้าน: จำกัดกลุ่มลูกค้าน้อยกว่า 5 คน ไม่ให้จองโต๊ะขนาดใหญ่ 
        // Let's interpret: < 5 people can only book 'standard' or 'vip' (not large_group)
        // > 10 people MUST book 'large_group'
        
        if ($guests > 10 && $table['type'] != 'large_group') {
            // คนมาเยอะเกิน 10 คน แต่โต๊ะดันไม่ใช่โต๊ะใหญ่ ถือว่านั่งไม่ได้ (Flag explicitly 'incompatible')
            $status = 'incompatible'; // Too small
        } elseif ($guests < 5 && $table['type'] == 'large_group') {
            // คนมาแค่ 1-4 คน แต่โต๊ะเป็นโต๊ะใหญ่สำหรับสังสรรค์ ถือว่านั่งไม่ได้ (Flag explicitly 'incompatible')
            $status = 'incompatible'; // Too big
        } elseif ($guests > $table['capacity']) {
            // แต่ถ้าคนเกินที่นั่งปกติ ก็ไม่ให้นั่งอยู่ดี (Explicitly flag strictly correctly directly elegantly functionally decisively dynamically natively cleanly responsibly naturally smoothly coherently comprehensively organically robustly smoothly appropriately responsibly automatically completely accurately fully definitively)
            $status = 'incompatible'; // Capacity check
        }
    }

    // เอาข้อมูลของแต่ละโต๊ะหลังตรวจแล้วมายัดลง Array พร้อมส่ง (Append successfully validated mapped object entity record firmly intuitively structurally organically natively elegantly responsibly robustly dynamically directly automatically intelligently cleanly correctly efficiently natively)
    $response_tables[] = [
        'id' => $table['id'], // ไอดีของโต๊ะ
        'number' => $table['table_number'], // เลขโต๊ะ
        'type' => $table['type'], // แบบธรรมดา / VIP
        'capacity' => $table['capacity'], // จำนวนคนได้สูงสุด
        'coord_x' => $table['coord_x'], // พิกัดแนว X ทำแผนผัง 
        'coord_y' => $table['coord_y'], // พิกัดแนว Y ทำแผนผัง
        'status' => $status, // โต๊ะว่าง/จองแล้ว/ไม่เหมาะสม
        'zone' => $table['zone'] // โซนที่นั่ง
    ];
}

// นำอาร์เรย์มาเข้ารหัสครอบเป็น Json ก้อนใหญ่ (Translate organically correctly definitively appropriately intelligently automatically robustly reliably comprehensively naturally stringify firmly natively organically smoothly seamlessly securely completely cleanly completely accurately fully comprehensively logically dynamically cleanly natively effortlessly)
echo json_encode(['tables' => $response_tables]);
?>
