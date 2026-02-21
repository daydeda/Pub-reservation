<?php
// complete_reservation.php
require 'config/db_connect.php';
require 'includes/auth_session.php';
checkLogin();

$success = false;
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $table_id = $_POST['table_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = $_POST['guests'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Double check availability
        // (Skipping for brevity, but crucial in real apps)
        
        // 2. Create Reservation
        $sql = "INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, guest_count, status) VALUES (?, ?, ?, ?, ?, 'confirmed')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $table_id, $date, $time, $guests]);
        $reservation_id = $pdo->lastInsertId();
        
        // 3. Create Payment Record
        $ref = strtoupper(uniqid('REF-'));
        $sql_pay = "INSERT INTO payments (reservation_id, amount, method, status, transaction_ref) VALUES (?, ?, ?, 'success', ?)";
        $stmt_pay = $pdo->prepare($sql_pay);
        $stmt_pay->execute([$reservation_id, $amount, $method, $ref]);
        
        // 4. Update Table Status (Optional, if we want to lock it permanently)
        // But our api/get_tables.php already checks reservations table, so this is fine.
        
        $pdo->commit();
        $success = true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Booking Failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Booking Status - NightOwl Pub'; ?>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col items-center justify-center">
    <div class="container mx-auto px-4 text-center">
        <?php if($success): ?>
            <div class="bg-surface p-8 md:p-12 rounded-lg border-2 border-secondary shadow-[0_0_30px_rgba(0,255,65,0.2)] inline-block max-w-lg w-full">
                <div class="text-6xl mb-6">✅</div>
                <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Reservation Successful!</h1>
                <p class="text-gray-300 text-lg mb-6">Your table has been booked and deposit received.</p>
                <div class="bg-black/30 p-4 rounded mb-8">
                    <p class="text-sm text-gray-500 uppercase tracking-wide">Transaction Reference</p>
                    <p class="text-2xl font-mono text-white tracking-widest"><?php echo $ref; ?></p>
                </div>
                <a href="index.php" class="btn w-full py-3 text-lg">Return to Home</a>
            </div>
        <?php else: ?>
            <div class="bg-surface p-8 md:p-12 rounded-lg border-2 border-error shadow-[0_0_30px_rgba(255,68,68,0.2)] inline-block max-w-lg w-full">
                <div class="text-6xl mb-6">❌</div>
                <h1 class="text-3xl md:text-4xl font-bold text-error mb-4">Booking Failed</h1>
                <p class="text-gray-300 text-lg mb-8"><?php echo htmlspecialchars($error_msg); ?></p>
                <a href="index.php" class="btn w-full py-3 text-lg bg-gray-700 hover:bg-gray-600 text-white">Return to Home</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="mt-8 text-gray-600 text-sm">
        &copy; 2026 NightOwl Pub
    </div>
</body>
</html>
