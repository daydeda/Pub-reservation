<?php
// api/get_tables.php
require '../config/db_connect.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;
$guests = $_GET['guests'] ?? 0;
$pub_id = $_GET['pub_id'] ?? 1; // Default to 1 (NightOwl HQ) if not specified

if (!$date || !$time) {
    echo json_encode(['error' => 'Missing date or time']);
    exit;
}

// 1. Get all tables for the selected pub
$stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE pub_id = ?");
$stmt->execute([$pub_id]);
$all_tables = $stmt->fetchAll();

// 2. Get reserved table IDs for this slot
// Simple logic: If reserved on that date, it's taken for the night (for simplicity of this project)
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
$reserved_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Process availability and compatibility
$response_tables = [];

foreach ($all_tables as $table) {
    $status = 'available';
    $is_reserved = in_array($table['id'], $reserved_ids);
    
    if ($is_reserved) {
        $status = 'reserved';
    } else {
        // Enforce guest count rules
        // Rule 1.3: < 5 people -> specific tables?
        // Let's interpret: < 5 people can only book 'standard' or 'vip' (not large_group)
        // > 10 people MUST book 'large_group'
        
        if ($guests > 10 && $table['type'] != 'large_group') {
            $status = 'incompatible'; // Too small
        } elseif ($guests < 5 && $table['type'] == 'large_group') {
            $status = 'incompatible'; // Too big
        } elseif ($guests > $table['capacity']) {
            $status = 'incompatible'; // Capacity check
        }
    }

    $response_tables[] = [
        'id' => $table['id'],
        'number' => $table['table_number'],
        'type' => $table['type'],
        'capacity' => $table['capacity'],
        'coord_x' => $table['coord_x'],
        'coord_y' => $table['coord_y'],
        'status' => $status,
        'zone' => $table['zone']
    ];
}

echo json_encode(['tables' => $response_tables]);
?>
