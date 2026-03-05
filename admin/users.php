<?php
// admin/users.php
// หน้าต่างสำหรับจัดการผู้ใช้งาน (Admin page for managing users)

require '../config/config.php';
require '../includes/auth_session.php';
checkAdmin();

// Handle Actions (ส่วนประมวลผลเมื่อแอดมินอัปเดตสถานะผู้ใช้)
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    if ($_POST['action'] == 'update_status') {
        $status = $_POST['status'];
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$status, $id]);
        }
    }
}

// Fetch All Users 
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $page_title = 'User Management'; ?>
    <?php include '../includes/head.php'; ?>
</head>
<body class="bg-darker text-white font-sans min-h-screen flex flex-col">
    <!-- แถบนำทางด้านบนสำหรับแอดมิน -->
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">MaoHub Admin</a>
            <ul class="flex space-x-6">
                <li><a href="index.php" class="text-gray-300 hover:text-secondary transition-colors">Reservations</a></li>
                <li><a href="pubs.php" class="text-gray-300 hover:text-secondary transition-colors">Pubs</a></li>
                <li><a href="users.php" class="text-secondary font-bold transition-colors">Users</a></li>
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow">
        <h1 class="text-3xl font-bold mb-8 text-primary border-l-4 border-primary pl-4">User Registration Management</h1>
        
        <div class="overflow-x-auto bg-[#fdf8f5] rounded-lg shadow-lg border border-primary">
            <table class="w-full text-left border-collapse">
                <thead class="bg-secondary text-white uppercase text-sm font-semibold">
                    <tr>
                        <th class="p-4 border-b border-primary/40">ID</th>
                        <th class="p-4 border-b border-primary/40">Username / Name</th>
                        <th class="p-4 border-b border-primary/40">Contact</th>
                        <th class="p-4 border-b border-primary/40">DOB</th>
                        <th class="p-4 border-b border-primary/40">ID Card</th>
                        <th class="p-4 border-b border-primary/40">Status</th>
                        <th class="p-4 border-b border-primary/40">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary/20">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-white/60 transition-colors">
                        <td class="p-4 text-darker font-bold">#<?php echo $u['user_id']; ?></td>
                        <td class="p-4">
                            <span class="font-bold text-darker block"><?php echo htmlspecialchars($u['full_name']); ?></span>
                            <span class="text-secondary font-bold text-sm">@<?php echo htmlspecialchars($u['username']); ?></span>
                        </td>
                        <td class="p-4 text-darker text-sm font-medium">
                            <?php echo htmlspecialchars($u['email']); ?><br>
                            <?php echo htmlspecialchars($u['phone_number']); ?>
                        </td>
                        <td class="p-4 text-darker font-medium">
                            <?php echo $u['dob'] ? htmlspecialchars($u['dob']) : '-'; ?>
                        </td>
                        <td class="p-4 text-darker">
                            <?php if($u['id_card']): ?>
                                <a href="../<?php echo htmlspecialchars($u['id_card']); ?>" target="_blank" class="text-blue-600 hover:underline">View File</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase
                                <?php 
                                    echo match($u['status']) {
                                        'approved' => 'bg-green-500 text-white',
                                        'pending' => 'bg-yellow-400 text-darker',
                                        'rejected' => 'bg-red-500 text-white',
                                        default => 'bg-gray-500 text-white'
                                    };
                                ?>">
                                <?php echo ucfirst($u['status'] ?? 'Approved'); ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <form method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="id" value="<?php echo $u['user_id']; ?>">
                                <input type="hidden" name="action" value="update_status">
                                
                                <select name="status" class="p-1 rounded bg-white text-darker border border-primary/50 text-sm font-medium focus:border-secondary focus:outline-none">
                                    <?php $status = $u['status'] ?? 'approved'; ?>
                                    <option value="pending" <?php if($status=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="approved" <?php if($status=='approved') echo 'selected'; ?>>Approved</option>
                                    <option value="rejected" <?php if($status=='rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="bg-primary text-white px-3 py-1 rounded text-sm font-bold hover:bg-secondary transition-colors shadow">Update</button>
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
