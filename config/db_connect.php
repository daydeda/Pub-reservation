<?php
// config/db_connect.php
// กำหนดตัวแปรและออบเจ็กต์เพื่อสร้างการติดต่อส่วนกลางไปยังฐานข้อมูลของระบบ 
// Core configuration file used to connect the app with the MySQL database

// ที่อยู่ของฐานข้อมูล ซึ่งปกติจะเป็น localhost ถ้ารันเซิร์ฟเวอร์ด้วยตัวเอง (Hostname for database)
$host = 'localhost';
// ระบุชื่อก้อน Database ตรงตามที่เรากำหนดผ่าน schema.sql (Target Database schema name)
$db   = 'pub_reservation';
// ชื่อผู้ใช้งานจัดการดาต้าเบส (Database root username)
$user = 'root';
// รหัสผ่าน ของ XAMPP ส่วนมากปล่อยว่าง (Password for the DB user, default empty for XAMPP on Windows/Mac mostly)
$pass = ''; // Default for many local setups, user might need to change this
// เลือกรหัสความเข้ากันได้ภาษา เพื่อให้แสดงผลและเก็บค่า Text ภาษาไทย/อิโมจิได้ถูก (Specify Character set encoding to support emojis and Thai)
$charset = 'utf8mb4';

// เรียบเรียง String (Connection String Format) ส่งไปยัง PDO class (Format the DSN string required by PDO)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
// ปรับแต่งการยิง Error ตามมาตรฐานของ PHP (Setup robust PDO options)
$options = [
    // กำหนดให้เมื่อเกิดปัญหา ระบบจะโยนแจ้งเตือนโผงผางออกมาเพื่อให้นักพัฒนาแก้ไข (Throw fatal exceptions on error)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // สั่งให้ข้อมูลตารางดึงออกมาในรูป associative array ซึ่งดูง่ายกว่า (Fetch results into associative array natively avoiding indexed output)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // สั่งให้ตัวแปรปิดใช้งานการจำลอง prepare statement เพื่อความซับซ้อนแม่นยำขึ้นจากตัวเซิร์ฟเวอร์  (Turn off emulated prepares, force real prepared statements to counter SQL injection)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ใช้ชุดคำสั่ง Try-Catch เพื่อให้รันคำสั่ง หากเชื่อมต่อเจ้ง จะไม่ทำให้เว็บไซต์ Error เลอะเทอะ (Try catching any potential connection disaster)
try {
    // กำหนดออบเจ็กต์ pdo ตัวหลักที่จะไปถูกใช้งานโดยไฟล์ภายนอกอื่นๆ (Initialize the PDO instance assigned globally as $pdo)
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a real production environment, you'd log this, not echo it
    // But for this project, seeing the error is helpful
    
    // หากเกิดปัญหาการเชื่อมต่อ จะแจ้งชนิดรหัส Error ขอดาต้าเบส (If it throws due to config mismatches, emit the custom Database error directly)
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
