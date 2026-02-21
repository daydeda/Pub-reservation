<?php
// reservation.php
require 'config/db_connect.php';
require 'includes/auth_session.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php


    // Check if pub_id is set
    if (!isset($_GET['pub_id'])) {
        header("Location: index.php");
        exit;
    }

    $pub_id = (int)$_GET['pub_id'];

    // Fetch Pub Details
    $stmt = $pdo->prepare("SELECT * FROM pubs WHERE id = ?");
    $stmt->execute([$pub_id]);
    $pub = $stmt->fetch();

    if (!$pub) {
        header("Location: index.php");
        exit;
    }

    $page_title = 'Reserve - ' . htmlspecialchars($pub['name']);
    ?>
    <?php include 'includes/head.php'; ?>
    <script>
        const CURRENT_PUB_ID = <?php echo $pub_id; ?>;
    </script>
</head>
<body class="bg-darker text-white font-sans flex flex-col min-h-screen">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">
                <span class="text-sm text-gray-400 font-normal block -mb-1">Booking at</span>
                <?php echo htmlspecialchars($pub['name']); ?>
            </a>
            <div class="space-x-4">
                <a href="index.php" class="text-gray-300 hover:text-white transition-colors">Switch Location</a>
                <a href="logout.php" class="text-sm text-error hover:text-white transition-colors ml-4">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6 flex-grow flex flex-col h-full">
        <div class="flex flex-col md:flex-row gap-6 h-full flex-grow">
            <!-- Sidebar Controls -->
            <div class="w-full md:w-1/3 lg:w-1/4 bg-surface p-6 rounded-lg border border-gray-800 flex flex-col shadow-lg">
                <h2 class="text-xl font-bold text-secondary mb-4 border-b border-gray-700 pb-2">Reservation Details</h2>

                <form id="bookingForm" class="space-y-4 flex-grow">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Date</label>
                        <input type="date" id="date" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Time</label>
                        <input type="time" id="time" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Guests</label>
                        <input type="number" id="guests" class="input-field" min="1" max="20" required>
                    </div>

                    <button type="button" id="updateMapBtn" class="btn w-full mt-4">Update Map</button>

                    <hr class="border-gray-700 my-4">

                    <div id="selectionInfo" class="hidden animate-fade-in">
                        <p class="text-gray-400 text-sm">Selected Table:</p>
                        <p class="text-2xl font-bold text-primary mb-2" id="selectedTableDisplay">--</p>
                        <input type="hidden" id="table_id" name="table_id">
                        <button type="submit" id="submitBtn" class="btn w-full bg-secondary text-black hover:bg-green-400 font-extrabold shadow-[0_0_15px_rgba(0,255,65,0.4)]">Proceed to Payment</button>
                    </div>
                </form>
            </div>

            <!-- Map Container -->
            <div class="w-full md:w-2/3 lg:w-3/4 bg-black/50 rounded-lg border-2 border-gray-800 relative overflow-hidden shadow-inner flex-grow min-h-[500px]" id="mapContainer">
                <div class="stage-marker">STAGE</div>
                
                <div class="absolute top-4 left-4 bg-black/70 p-2 rounded text-xs text-gray-300 pointer-events-none z-10 border border-gray-700">
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-green-500"></span> Available</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Reserved</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-gray-500"></span> Incompatible</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-secondary border border-white"></span> Selected</div>
                </div>
                <!-- Tables will be injected here via JS -->
                 <div id="tablesLayer" class="w-full h-full relative"></div>
            </div>
        </div>
    </div>
    
    <script src="js/reservation.js?v=<?php echo time(); ?>"></script>
    <footer class="bg-black py-6 text-center text-gray-500 text-sm border-t border-gray-900">
        &copy; 2026 NightOwl Pub. All rights reserved.
    </footer>
</body>
</html>
