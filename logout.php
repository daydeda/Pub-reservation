<?php
// logout.php
// สำหรับทำลายเซสชันออกจากระบบทั้งหมด (Endpoint script designed to safely end a user's session)

// สั่งให้ PHP ดึงข้อมูล Session ที่มีอยู่ (Initialize or resume existing session array memory)
session_start();
// ลบตัวแปรค่าทั้งหมด เช่น user_id ออกจากหน่วยความจำ Session ทันที (Unset all existing session variables)
session_unset();
// ทำลายข้อมูลเซสชันตัวนี้ทิ้งอย่างสมบูรณ์แบบเพื่อไม่ให้ถูกดึงข้อมูลไปใช้ซ้ำ (Destroy the session completely)
session_destroy();

// เด้งผู้ใช้งานที่ถูกเตะออกไปที่หน้าล๊อคอินใหม่อีกครั้ง (Redirect the guest seamlessly back to the login gateway)
header("Location: login.php");
exit(); // หยุดการทำงานของสคริปต์นี้ (Ensure no further PHP code executes beyond this point)
?>
