-- database/schema.sql

CREATE DATABASE IF NOT EXISTS pub_reservation;
USE pub_reservation;

-- 1. Users Table (10 fields)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME
);

-- 1.5 Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 1.8 Pubs Table
CREATE TABLE IF NOT EXISTS pubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    location VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tables Table (9 fields)
CREATE TABLE IF NOT EXISTS dining_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pub_id INT DEFAULT 1,
    table_number VARCHAR(10) NOT NULL,
    capacity INT NOT NULL,
    type ENUM('standard', 'vip', 'large_group') DEFAULT 'standard',
    zone VARCHAR(50) DEFAULT 'Main Hall',
    status ENUM('available', 'maintenance', 'reserved') DEFAULT 'available',
    coord_x INT DEFAULT 0 COMMENT 'CSS left %',
    coord_y INT DEFAULT 0 COMMENT 'CSS top %',
    description TEXT,
    FOREIGN KEY (pub_id) REFERENCES pubs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_table_per_pub (pub_id, table_number)
);

-- 3. Reservations Table (10 fields)
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    table_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guest_count INT NOT NULL,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    check_in_time DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (table_id) REFERENCES dining_tables(id)
);

-- 4. Payments Table (8 fields)
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('atm', 'qrcode', 'mobile_banking', 'cash') NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    transaction_ref VARCHAR(100),
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    receipt_url VARCHAR(255),
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- Initial Data Seeding

-- Admin User
INSERT INTO admins (username, password, full_name) VALUES
('admin', 'admin123', 'System Admin');

-- General User
INSERT INTO users (username, email, password, full_name, phone_number) VALUES
('user1', 'user@test.com', 'admin123', 'John Doe', '0898765432');

-- Pubs
INSERT INTO pubs (name, description, image_url, location) VALUES
('NightOwl HQ', 'The original cyberpunk experience. Neon lights, retro vibes, and the best synthwave beats.', 'assets/pub_hq.jpg', 'Downtown Core'),
('Cyber Bar', 'A sleek, modern lounge for the digital elite. High-end cocktails and panoramic city views.', 'assets/pub_cyber.jpg', 'Tech District');

-- Tables
-- Standard Tables (2-4 people) at HQ (pub_id=1)
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'T1', 4, 'standard', 'Main Hall', 10, 10),
(1, 'T2', 4, 'standard', 'Main Hall', 30, 10),
(1, 'T3', 4, 'standard', 'Main Hall', 50, 10),
(1, 'T4', 2, 'standard', 'Window View', 10, 40),
(1, 'T5', 2, 'standard', 'Window View', 30, 40);

-- VIP Tables (< 5 people, premium) at HQ (pub_id=1)
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'VIP1', 4, 'vip', 'VIP Lounge', 70, 10),
(1, 'VIP2', 4, 'vip', 'VIP Lounge', 90, 10);

-- Large Group Tables (> 10 people) at HQ (pub_id=1)
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'LG1', 12, 'large_group', 'Party Zone', 60, 60),
(1, 'LG2', 15, 'large_group', 'Party Zone', 80, 60);

-- Tables for Cyber Bar (pub_id=2)
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(2, 'CB1', 4, 'standard', 'Lounge Area', 20, 20),
(2, 'CB2', 6, 'standard', 'Lounge Area', 50, 20),
(2, 'VIP-CB', 8, 'vip', 'Sky Deck', 80, 50);

-- A few middle range for 5-10 people at HQ
INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
(1, 'M1', 8, 'standard', 'Main Hall', 20, 70),
(1, 'M2', 6, 'standard', 'Main Hall', 40, 70);
