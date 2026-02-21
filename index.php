<?php
// index.php
require 'config/db_connect.php';
require 'includes/auth_session.php';
// Don't enforce login here, just show different content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Home - NightOwl Pub'; ?>
    <?php include 'includes/head.php'; ?>
    <?php
    require 'config/db_connect.php';
    // Fetch Pubs
    $stmt = $pdo->query("SELECT * FROM pubs ORDER BY name");
    $pubs = $stmt->fetchAll();
    ?>
</head>
<body class="bg-darker text-white font-sans">
    <nav class="bg-surface border-b border-gray-800 p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Pub</a>
            
            <div class="space-x-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-300">Welcome, <span class="text-secondary font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
                    <a href="logout.php" class="text-sm text-error hover:text-white transition-colors ml-4">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login to Book</a>
                    <a href="register.php" class="text-gray-300 hover:text-primary transition-colors ml-4">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative bg-black h-96 flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-darker z-10"></div>
        <img src="assets/pub_hq.jpg" alt="Pub Atmosphere" class="absolute inset-0 w-full h-full object-cover opacity-50">
        <div class="relative z-20 text-center px-4">
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-4 drop-shadow-[0_0_10px_rgba(255,215,0,0.5)]">
                <span class="text-primary">Night</span>Owl
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-2xl mx-auto">
                Neon lights, cold drinks, and good vibes. <br>Find your spot in the city.
            </p>
        </div>
    </header>

    <!-- Pub Selection -->
    <main class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center text-secondary mb-12 uppercase tracking-widest">Select Your Location</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($pubs as $pub): ?>
            <div class="bg-surface rounded-lg overflow-hidden border border-gray-800 hover:border-primary transition-all hover:shadow-[0_0_20px_rgba(255,215,0,0.3)] group h-full flex flex-col">
                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo htmlspecialchars($pub['image_url']); ?>" alt="<?php echo htmlspecialchars($pub['name']); ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute top-0 right-0 bg-black/70 px-3 py-1 m-2 rounded text-xs text-secondary font-bold border border-secondary">
                        <?php echo htmlspecialchars($pub['location']); ?>
                    </div>
                </div>
                <div class="p-6 flex-grow flex flex-col">
                    <h3 class="text-2xl font-bold text-white mb-2 group-hover:text-primary transition-colors"><?php echo htmlspecialchars($pub['name']); ?></h3>
                    <p class="text-gray-400 mb-6 flex-grow"><?php echo htmlspecialchars($pub['description']); ?></p>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="reservation.php?pub_id=<?php echo $pub['id']; ?>" class="btn w-full block">Book Here</a>
                    <?php else: ?>
                        <a href="login.php" class="btn bg-gray-700 text-gray-300 hover:bg-gray-600 w-full block">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="bg-black py-8 text-center text-gray-500 text-sm border-t border-gray-900">
        <p>&copy; 2026 NightOwl Pub. All rights reserved.</p>
    </footer>
</body>
</html>
