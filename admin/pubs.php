<?php
// admin/pubs.php
require '../config/db_connect.php';
require '../includes/auth_session.php';
checkAdmin();

$success_msg = '';
$error_msg = '';

// Handle Add Pub
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    if ($name && $location) {
        $stmt = $pdo->prepare("INSERT INTO pubs (name, location, description, image_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $location, $description, $image_url])) {
            $success_msg = "Pub added successfully!";
        } else {
            $error_msg = "Failed to add pub.";
        }
    } else {
        $error_msg = "Name and Location are required.";
    }
}

// Fetch Pubs
$stmt = $pdo->query("SELECT * FROM pubs ORDER BY created_at DESC");
$pubs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Manage Pubs - NightOwl Admin'; ?>
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Admin</a>
            <ul class="flex space-x-6">
                <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Reservations</a></li>
                <li><a href="pubs.php" class="text-secondary font-bold transition-colors">Pubs</a></li>
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow">
        <h1 class="text-3xl font-bold mb-8 text-primary border-l-4 border-primary pl-4">Manage Pub Locations</h1>
        
        <?php if($success_msg): ?>
            <div class="bg-green-900 text-green-300 p-4 rounded mb-6 border border-green-700"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="bg-red-900 text-red-300 p-4 rounded mb-6 border border-red-700"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Add Pub Form -->
        <div class="bg-surface p-6 rounded-lg border border-gray-800 mb-8 max-w-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Add New Pub</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Pub Name</label>
                        <input type="text" name="name" required class="input-field" placeholder="e.g. NightOwl Downtown">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-1">Location / District</label>
                        <input type="text" name="location" required class="input-field" placeholder="e.g. Central District">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Description</label>
                    <textarea name="description" rows="3" class="input-field" placeholder="Brief description of the vibe..."></textarea>
                </div>
                <div>
                    <label class="block text-gray-400 text-sm mb-1">Image URL</label>
                    <input type="text" name="image_url" class="input-field" placeholder="assets/pub_default.jpg" value="assets/pub_hq.jpg">
                </div>
                <button type="submit" class="btn">Add Pub</button>
            </form>
        </div>

        <!-- Pub List -->
        <h2 class="text-2xl font-bold text-white mb-4">Existing Pubs</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($pubs as $pub): ?>
            <div class="bg-surface rounded-lg overflow-hidden border border-gray-800 flex flex-col">
                <div class="h-40 overflow-hidden relative">
                    <img src="../<?php echo htmlspecialchars($pub['image_url']); ?>" alt="<?php echo htmlspecialchars($pub['name']); ?>" class="w-full h-full object-cover">
                    <div class="absolute top-0 right-0 bg-black/70 px-2 py-1 m-2 rounded text-xs text-secondary border border-secondary">
                        ID: <?php echo $pub['id']; ?>
                    </div>
                </div>
                <div class="p-4 flex-grow">
                    <h3 class="text-xl font-bold text-white mb-1"><?php echo htmlspecialchars($pub['name']); ?></h3>
                    <p class="text-sm text-secondary mb-3"><?php echo htmlspecialchars($pub['location']); ?></p>
                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($pub['description']); ?></p>
                </div>
                <div class="p-4 border-t border-gray-700 bg-black/20">
                    <span class="text-xs text-gray-500">Created: <?php echo $pub['created_at']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
