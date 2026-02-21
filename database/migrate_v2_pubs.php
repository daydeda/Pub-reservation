<?php
// database/migrate_v2_pubs.php
require __DIR__ . '/../config/db_connect.php';

try {
    echo "Starting migration...\n";

    // 1. Create pubs table
    $sql = "CREATE TABLE IF NOT EXISTS pubs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        location VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "1. Pubs table checked/created.\n";

    // 2. Seed Pubs (Need IDs 1 and 2 to ensure defaults work)
    $pubs = [
        1 => ['NightOwl HQ', 'The original cyberpunk experience.', 'assets/pub_hq.jpg', 'Downtown Core'],
        2 => ['Cyber Bar', 'A sleek, modern lounge.', 'assets/pub_cyber.jpg', 'Tech District']
    ];

    foreach ($pubs as $id => $pub) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pubs WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO pubs (id, name, description, image_url, location) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $pub[0], $pub[1], $pub[2], $pub[3]]);
            echo "   -> Inserted pub: {$pub[0]}\n";
        }
    }
    echo "2. Pubs seeded.\n";

    // 3. Add pub_id to dining_tables
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM dining_tables LIKE 'pub_id'");
    if ($stmt->rowCount() == 0) {
        echo "3. Adding pub_id column...\n";
        // Default to 1 (NightOwl HQ)
        $pdo->exec("ALTER TABLE dining_tables ADD COLUMN pub_id INT DEFAULT 1 AFTER id");
        
        // Add FK
        try {
             $pdo->exec("ALTER TABLE dining_tables ADD CONSTRAINT fk_dining_tables_pubs FOREIGN KEY (pub_id) REFERENCES pubs(id) ON DELETE CASCADE");
             echo "   -> FK Constraint added.\n";
        } catch (Exception $e) { echo "   -> Warning: Could not add FK: " . $e->getMessage() . "\n"; }
        
        // Drop global unique index on table_number if it exists
        // Usually named 'table_number'
        try {
            $pdo->exec("ALTER TABLE dining_tables DROP INDEX table_number");
            echo "   -> Dropped legacy global unique index on table_number.\n";
        } catch (Exception $e) { 
             // Maybe it was named differently or doesn't exist? Try verifying constraints later.
             echo "   -> Note: Could not drop index 'table_number' (might not exist): " . $e->getMessage() . "\n";
        }

        // Add composite unique index
        try {
            $pdo->exec("ALTER TABLE dining_tables ADD UNIQUE KEY unique_table_per_pub (pub_id, table_number)");
            echo "   -> Added composite unique key (pub_id, table_number).\n";
        } catch (Exception $e) { echo "   -> Warning: Could not add composite unique key: " . $e->getMessage() . "\n"; }

    } else {
        echo "3. pub_id column already exists.\n";
    }

    // 4. Seed new tables for Cyber Bar (Pub 2)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dining_tables WHERE pub_id = 2");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO dining_tables (pub_id, table_number, capacity, type, zone, coord_x, coord_y) VALUES
            (2, 'CB1', 4, 'standard', 'Lounge Area', 20, 20),
            (2, 'CB2', 6, 'standard', 'Lounge Area', 50, 20),
            (2, 'VIP-CB', 8, 'vip', 'Sky Deck', 80, 50)");
        echo "4. Inserted Cyber Bar tables.\n";
    } else {
        echo "4. Cyber Bar tables already exist.\n";
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
