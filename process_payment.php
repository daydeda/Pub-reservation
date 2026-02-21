<?php
// process_payment.php -> Renamed to payment.php for clarity, or keep as is.
// Let's call it process_payment.php as per the previous form action.
require 'config/db_connect.php';
require 'includes/auth_session.php';
checkLogin();

// Validate inputs
if (!isset($_GET['date']) || !isset($_GET['time']) || !isset($_GET['table_id'])) {
    header("Location: reservation.php");
    exit();
}

$date = $_GET['date'];
$time = $_GET['time'];
$guests = $_GET['guests'];
$table_id = $_GET['table_id'];

// Fetch Table Details for display
$stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE id = ?");
$stmt->execute([$table_id]);
$table = $stmt->fetch();

if (!$table) {
    die("Invalid table selected.");
}

// Mock Price Config
$base_price = 500; // Baht
$total_price = $base_price + ($guests * 100);
if ($table['type'] == 'vip') $total_price *= 1.5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Payment - NightOwl Pub'; ?>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow max-w-2xl">
        <h1 class="text-3xl font-bold text-center mb-8 text-primary">Confirm & Pay</h1>
        
        <div class="bg-surface p-6 rounded-lg border border-primary/30 shadow-lg mb-8">
            <h3 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2">Reservation Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-300">
                <p><strong class="text-gray-500">Date:</strong> <?php echo $date; ?></p>
                <p><strong class="text-gray-500">Time:</strong> <?php echo $time; ?></p>
                <p><strong class="text-gray-500">Guests:</strong> <?php echo $guests; ?></p>
                <p><strong class="text-gray-500">Table:</strong> <?php echo $table['table_number']; ?> (<?php echo ucfirst($table['type']); ?>)</p>
                <p><strong class="text-gray-500">Zone:</strong> <?php echo $table['zone']; ?></p>
            </div>
            <hr class="border-gray-700 my-4">
            <p class="text-2xl text-secondary font-bold text-center">Total Deposit: ฿<?php echo number_format($total_price, 2); ?></p>
        </div>
        
        <form action="complete_reservation.php" method="POST">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" name="time" value="<?php echo $time; ?>">
            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
            <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $total_price; ?>">
            <input type="hidden" name="payment_method" id="payment_method" required>
            
            <h3 class="text-xl font-bold text-white mb-4">Select Payment Method</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'atm')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">💳</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">ATM / Debit</div>
                </div>
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'qrcode')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">📱</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">QR Code</div>
                </div>
                <div class="method-card bg-surface border-2 border-gray-700 p-4 rounded-lg text-center cursor-pointer transition-all hover:border-secondary hover:bg-gray-800 flex flex-col items-center justify-center h-32 group" onclick="selectMethod(this, 'mobile_banking')">
                    <div class="text-4xl mb-2 group-hover:scale-110 transition-transform">🏦</div>
                    <div class="font-semibold text-gray-300 group-hover:text-white">Mobile Banking</div>
                </div>
            </div>
            
            <button type="submit" class="btn w-full py-4 text-xl font-bold shadow-lg" id="payBtn" disabled>Pay & Book</button>
        </form>
    </div>
    
    <script>
        function selectMethod(el, method) {
            document.getElementById('payment_method').value = method;
            
            // Reset all cards
            document.querySelectorAll('.method-card').forEach(card => {
                card.classList.remove('border-secondary', 'ring-2', 'ring-secondary', 'shadow-[0_0_15px_rgba(0,255,65,0.5)]', 'bg-gray-800');
                card.classList.add('border-gray-700');
            });
            
            // Highlight selected
            el.classList.remove('border-gray-700');
            el.classList.add('border-secondary', 'ring-2', 'ring-secondary', 'shadow-[0_0_15px_rgba(0,255,65,0.5)]', 'bg-gray-800');
            
            document.getElementById('payBtn').disabled = false;
        }
    </script>
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900 mt-auto">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
