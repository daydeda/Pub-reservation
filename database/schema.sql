-- database/schema.sql
-- สร้างตารางฐานข้อมูลสำหรับระบบจองโต๊ะร้านอาหาร/ผับ
-- Create database schema for Pub/Restaurant reservation system

-- สร้างฐานข้อมูลหากยังไม่มีอยู่ (Create database if not exists)
CREATE DATABASE IF NOT EXISTS pub_reservation;
-- เลือกใช้งานฐานข้อมูลนี้ (Use this database)
USE pub_reservation;

-- 1. Users Table (ตารางผู้ใช้งานทั่วไป - 10 fields)
-- เก็บข้อมูลลูกค้าที่มาใช้บริการ (Store customer/user details)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสผู้ใช้งาน (User ID)
    username VARCHAR(50) NOT NULL UNIQUE, -- ชื่อผู้ใช้สำหรับล็อกอิน (Login username)
    email VARCHAR(100) NOT NULL UNIQUE, -- อีเมลผู้ใช้งาน (User email address)
    password VARCHAR(255) NOT NULL, -- รหัสผ่านที่เข้ารหัสแล้ว (Hashed password)
    full_name VARCHAR(100) NOT NULL, -- ชื่อ-นามสกุลจริง (Full name)
    phone_number VARCHAR(20), -- เบอร์โทรศัพท์ (Phone number)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- วันที่สร้างบัญชี (Account creation time)
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- วันที่แก้ไขข้อมูลล่าสุด (Last update time)
    is_active BOOLEAN DEFAULT TRUE, -- สถานะการใช้งาน 1=ปกติ, 0=ระงับ (Account status 1=Active, 0=Disabled)
    last_login DATETIME -- เวลาที่เข้าสู่ระบบครั้งล่าสุด (Last login timestamp)
);

-- 1.5 Admins Table (ตารางผู้ดูแลระบบ)
-- เก็บข้อมูลพนักงาน/แอดมินดูแลระบบ (Store administrator details)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสผู้ดูแลระบบ (Admin ID)
    username VARCHAR(50) NOT NULL UNIQUE, -- ชื่อผู้ใช้แอดมิน (Admin username)
    password VARCHAR(255) NOT NULL, -- รหัสผ่านแอดมิน (Admin password)
    full_name VARCHAR(100) NOT NULL, -- ชื่อจริงแอดมิน (Admin full name)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP -- เวลาที่เพิ่มเข้าระบบ (Creation timestamp)
);

-- 1.8 Pubs Table (ตารางสาขา/ร้าน)
-- เก็บข้อมูลร้านหรือสาขาต่างๆ ในระบบ (Store pub/branch information)
CREATE TABLE IF NOT EXISTS pubs (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสร้าน/สาขา (Pub/Branch ID)
    name VARCHAR(100) NOT NULL, -- ชื่อร้าน (Pub name)
    description TEXT, -- รายละเอียด/คำอธิบายร้าน (Pub description)
    image_url VARCHAR(255), -- ลิงก์รูปภาพประกอบร้าน (Image URL)
    location VARCHAR(100), -- ที่ตั้ง/สถานที่ (Location/Address)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP -- วันที่เพิ่มในระบบ (Record creation time)
);

-- 2. Tables Table (ตารางข้อมูลโต๊ะ - 9 fields)
-- เก็บข้อมูลโต๊ะภายในร้านแต่ละสาขา (Store dining tables info for each pub)
CREATE TABLE IF NOT EXISTS dining_tables (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสโต๊ะ (Table ID)
    pub_id INT DEFAULT 1, -- อ้างอิงรหัสร้าน (Reference to Pub ID)
    table_number VARCHAR(10) NOT NULL, -- หมายเลขโต๊ะ (Table number/name)
    capacity INT NOT NULL, -- จำนวนที่นั่งรองรับได้ (Seating capacity)
    type ENUM('standard', 'vip', 'large_group') DEFAULT 'standard', -- ประเภทโต๊ะ (Table type/category)
    zone VARCHAR(50) DEFAULT 'Main Hall', -- โซนที่ตั้งของโต๊ะ (Zone area in the venue)
    status ENUM('available', 'maintenance', 'reserved') DEFAULT 'available', -- สถานะของโต๊ะ ณ ปัจจุบัน (Current physical status)
    coord_x INT DEFAULT 0 COMMENT 'CSS left %', -- พิกัดแกน X สำหรับแสดงแผนที่ (X coordinate for map UI)
    coord_y INT DEFAULT 0 COMMENT 'CSS top %', -- พิกัดแกน Y สำหรับแสดงแผนที่ (Y coordinate for map UI)
    description TEXT, -- คำอธิบายโต๊ะเพิ่มเติม (Extra description)
    FOREIGN KEY (pub_id) REFERENCES pubs(id) ON DELETE CASCADE, -- เชื่อมกับตารางร้าน (Connect to pubs table)
    UNIQUE KEY unique_table_per_pub (pub_id, table_number) -- ป้องกันหมายเลขโต๊ะซ้ำในสาขาเดียวกัน (Prevent duplicate table names per pub)
);

-- 3. Reservations Table (ตารางการจองโต๊ะ - 10 fields)
-- เก็บประวัติและรายการการจองโต๊ะ (Store reservation bookings)
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสการจอง (Reservation ID)
    user_id INT NOT NULL, -- รหัสผู้ใช้ที่จอง (ID of user who booked)
    table_id INT NOT NULL, -- รหัสโต๊ะที่ถูกจอง (ID of the reserved table)
    reservation_date DATE NOT NULL, -- วันที่เข้าใช้บริการ (Date of reservation)
    reservation_time TIME NOT NULL, -- เวลาที่เข้าใช้บริการ (Time of reservation)
    guest_count INT NOT NULL, -- จำนวนลูกค้าที่มา (Number of guests expected)
    special_requests TEXT, -- ความต้องการพิเศษ (Special requests from user)
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending', -- สถานะการจอง (Booking status)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- วันเวลาที่ทำรายการจอง (Booking timestamp)
    check_in_time DATETIME, -- เวลาที่เช็คอินเข้าร้านจริง (Actual check-in time)
    FOREIGN KEY (user_id) REFERENCES users(id), -- เชื่อมโยงรหัสผู้ใช้ (Link to users)
    FOREIGN KEY (table_id) REFERENCES dining_tables(id) -- เชื่อมโยงรหัสโต๊ะ (Link to tables)
);

-- 4. Payments Table (ตารางการชำระเงิน - 8 fields)
-- เก็บข้อมูลการจ่ายเงินมัดจำ/บิล (Store deposit/bill payment details)
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY, -- รหัสบิลชำระเงิน (Payment ID)
    reservation_id INT NOT NULL, -- รหัสการจองที่เกี่ยวข้อง (Associated reservation ID)
    amount DECIMAL(10, 2) NOT NULL, -- จำนวนเงิน (Payment amount)
    method ENUM('atm', 'qrcode', 'mobile_banking', 'cash') NOT NULL, -- รูปแบบการจ่าย (Payment method)
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending', -- สถานะการจ่ายเงิน (Transaction status)
    transaction_ref VARCHAR(100), -- รหัสอ้างอิงธุรกรรม (Transaction reference code from gateway)
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- วันเวลาที่ชำระ (Payment timestamp)
    receipt_url VARCHAR(255), -- ลิงก์ดูใบเสร็จรับเงิน (URL to digital receipt)
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) -- เชื่อมโยงประวัติการจอง (Link to reservation)
);

-- ==============================================
-- Initial Data Seeding (การเพิ่มข้อมูลตั้งต้น)
-- Insert sample data into database
-- ==============================================

-- Admin User (เพิ่มข้อมูลแอดมิน)
INSERT INTO admins (username, password, full_name) VALUES
('admin', 'admin123', 'System Admin');

-- General User (เพิ่มข้อมูลลูกค้าทดสอบ)
INSERT INTO users (username, email, password, full_name, phone_number) VALUES
('user1', 'user@test.com', 'admin123', 'John Doe', '0898765432');

-- Pubs (เพิ่มข้อมูลร้าน/สาขาทั้ง 2 สาขา)
INSERT INTO pubs (name, description, image_url, location) VALUES
('NightOwl HQ', 'The original cyberpunk experience. Neon lights, retro vibes, and the best synthwave beats.', 'assets/pub_hq.jpg', 'Downtown Core'),
('Cyber Bar', 'A sleek, modern lounge for the digital elite. High-end cocktails and panoramic city views.', 'assets/pub_cyber.jpg', 'Tech District');

-- Tables (เพิ่มข้อมูลโต๊ะตั้งต้น)
-- Standard Tables (2-4 people) at HQ (pub_id=1) 
-- โต๊ะมาตรฐาน (2-4 คน) ที่สาขาแรก
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'T1', 4, 'standard', 'Main Hall', 10, 10),
(1, 'T2', 4, 'standard', 'Main Hall', 30, 10),
(1, 'T3', 4, 'standard', 'Main Hall', 50, 10),
(1, 'T4', 2, 'standard', 'Window View', 10, 40),
(1, 'T5', 2, 'standard', 'Window View', 30, 40);

-- VIP Tables (< 5 people, premium) at HQ (pub_id=1)
-- โต๊ะวีไอพีที่สาขาแรก
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'VIP1', 4, 'vip', 'VIP Lounge', 70, 10),
(1, 'VIP2', 4, 'vip', 'VIP Lounge', 90, 10);

-- Large Group Tables (> 10 people) at HQ (pub_id=1)
-- โต๊ะกลุ่มใหญ่ (มากกว่า 10 คน) ที่สาขาแรก
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'LG1', 12, 'large_group', 'Party Zone', 60, 60),
(1, 'LG2', 15, 'large_group', 'Party Zone', 80, 60);

-- Tables for Cyber Bar (pub_id=2)
-- กลุ่มโต๊ะที่เพิ่มสำหรับสาขา Cyber Bar
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(2, 'CB1', 4, 'standard', 'Lounge Area', 20, 20),
(2, 'CB2', 6, 'standard', 'Lounge Area', 50, 20),
(2, 'VIP-CB', 8, 'vip', 'Sky Deck', 80, 50);

-- A few middle range for 5-10 people at HQ
-- โต๊ะขนาดกลางรองรับ (5-10 คน) เพิ่มเติมที่สาขาแรก
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'M1', 8, 'standard', 'Main Hall', 20, 70),
(1, 'M2', 6, 'standard', 'Main Hall', 40, 70);
