<?php
// includes/auth_session.php
// ฟังก์ชันใช้งานซ้ำสำหรับการตรวจสอบสถานะผู้ใช้งาน
// Reusable authentication and authorization functions

// บังคับให้เริ่มการทำงานของหน่วยความจำเซสชันทันทีที่ถูกเรียกใช้งาน (Start session whenever this file is included)
session_start();

// ฟังก์ชัน: ตรวจสอบว่าผู้ใช้อยู่ในระบบหรือไม่ (Function: Verify if a user is logged in)
function checkLogin() {
    // หากไม่พบไอดีในระบบ (If user_id is missing from session)
    if (!isset($_SESSION['user_id'])) {
        // ให้ตีกลับไปยังหน้าหลักล็อกอิน (Redirect the unauthorized user to login page)
        header("Location: login.php");
        exit(); // ยุติการทำงานของโค้ดที่เหลือ (Stop further script execution)
    }
}

// ฟังก์ชัน: ตรวจสอบระดับผู้ดูแลระบบเท่านั้น (Function: Allow access exclusively for Admins)
function checkAdmin() {
    // ต้องแน่ใจว่าล็อกอินผ่านด่านแรกมาก่อน (Enforce valid basic login first)
    checkLogin();
    // ถ้าสถานะระดับไม่ใช่นโยบายผู้ดูแลระบบ (If the role specifically isn't admin)
    if ($_SESSION['role'] !== 'admin') {
        // ให้พ่นข้อความเตือนบนหน้าจอว่าไม่มีสิทธิ์เข้าถึง (Display Access Denied string)
        echo "Access Denied. You do not have permission to view this page.";
        exit(); // ยุติการนำเสนอข้อมูลของแอดมิน (Terminate page loading to protect admin resources)
    }
}

// ฟังก์ชัน: ตรวจสอบรูปแบบ boolean (คืนค่า true ถ้าเข้าระบบมาแล้ว) (Function: Helper returning bool status of login state)
function isLoggedIn() {
    // ใช้ส่งค่าจริงเท็จเพื่อให้บรรทัดอื่นๆ เขียนเช็คง่ายขึ้น (Returns true if user_id exists in session, otherwise false)
    return isset($_SESSION['user_id']);
}
?>
