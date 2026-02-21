<?php
// admin/index.php
require '../config/db_connect.php';
require '../includes/auth_session.php';
checkAdmin(); // Enforce Admin Only

// Handle Actions
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    if ($_POST['action'] == 'update') {
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    } elseif ($_POST['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Fetch All Reservations
$stmt = $pdo->query("
    SELECT r.*, u.username, u.full_name, t.table_number, p.name as pub_name
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    JOIN dining_tables t ON r.table_id = t.id 
    JOIN pubs p ON t.pub_id = p.id
    ORDER BY r.reservation_date DESC, r.reservation_time ASC
");
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'Admin Dashboard - NightOwl Pub'; ?>
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">NightOwl Admin</a>
            <ul class="flex space-x-6">
                <li><a href="index.php" class="text-secondary font-bold transition-colors">Reservations</a></li>
                <li><a href="pubs.php" class="text-gray-300 hover:text-secondary transition-colors">Pubs</a></li>
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow">
        <h1 class="text-3xl font-bold mb-8 text-primary border-l-4 border-primary pl-4">Reservation Management</h1>
        
        <div class="overflow-x-auto bg-surface rounded-lg shadow-lg border border-gray-800">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-800 text-primary uppercase text-sm font-semibold">
                    <tr>
                        <th class="p-4 border-b border-gray-700">ID</th>
                        <th class="p-4 border-b border-gray-700">Pub / Table</th>
                        <th class="p-4 border-b border-gray-700">User</th>
                        <th class="p-4 border-b border-gray-700">Date/Time</th>
                        <th class="p-4 border-b border-gray-700">Guests</th>
                        <th class="p-4 border-b border-gray-700">Status</th>
                        <th class="p-4 border-b border-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach($reservations as $r): ?>
                    <tr class="hover:bg-gray-700/50 transition-colors">
                        <td class="p-4 text-gray-400">#<?php echo $r['id']; ?></td>
                        <td class="p-4">
                            <span class="font-bold text-white block"><?php echo htmlspecialchars($r['pub_name']); ?></span>
                            <span class="text-secondary font-mono text-sm">Table #<?php echo $r['table_number']; ?></span>
                        </td>
                        <td class="p-4">
                            <span class="font-bold text-white"><?php echo htmlspecialchars($r['full_name']); ?></span>
                            <br><span class="text-xs text-gray-500">@<?php echo $r['username']; ?></span>
                        </td>
                        <td class="p-4 text-gray-300"><?php echo $r['reservation_date'] . ' <span class="text-gray-500">at</span> ' . substr($r['reservation_time'], 0, 5); ?></td>
                        <td class="p-4 text-gray-300"><?php echo $r['guest_count']; ?></td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                <?php 
                                    echo match($r['status']) {
                                        'confirmed' => 'bg-green-900 text-green-300',
                                        'pending' => 'bg-yellow-900 text-yellow-300',
                                        'cancelled' => 'bg-red-900 text-red-300',
                                        'completed' => 'bg-blue-900 text-blue-300',
                                        default => 'bg-gray-700 text-gray-400'
                                    };
                                ?>">
                                <?php echo ucfirst($r['status']); ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <form method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="action" value="update">
                                <select name="status" class="p-1 rounded bg-black border border-gray-600 text-sm focus:border-secondary focus:outline-none">
                                    <option value="pending" <?php if($r['status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="confirmed" <?php if($r['status']=='confirmed') echo 'selected'; ?>>Confirm</option>
                                    <option value="cancelled" <?php if($r['status']=='cancelled') echo 'selected'; ?>>Cancel</option>
                                    <option value="completed" <?php if($r['status']=='completed') echo 'selected'; ?>>Complete</option>
                                </select>
                                <button type="submit" class="bg-primary text-black px-3 py-1 rounded text-sm font-bold hover:bg-yellow-400 transition-colors">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
