<?php
// admin/layout.php
// หน้าผังหลังบ้านสำหรับผู้ดูแลระบบ จัดการตำแหน่งพิกัดโต๊ะของแต่ละสาขา
// Admin backend page to manage table coordinates for a specific pub

require '../config/config.php';
require '../includes/auth_session.php';
checkAdmin();

if (!isset($_GET['pub_id'])) {
    header("Location: pubs.php");
    exit;
}
$pub_id = (int)$_GET['pub_id'];

// ดึงข้อมูลร้าน (Fetch pub details)
$stmt = $pdo->prepare("SELECT * FROM pubs WHERE pub_id = ?");
$stmt->execute([$pub_id]);
$pub = $stmt->fetch();

if (!$pub) {
    header("Location: pubs.php");
    exit;
}

// Handle save layout (POST JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    if (isset($input['tables']) && is_array($input['tables'])) {
        $updateStmt = $pdo->prepare("UPDATE dining_tables SET coord_x = ?, coord_y = ? WHERE table_id = ? AND pub_id = ?");
        foreach ($input['tables'] as $t) {
            $updateStmt->execute([$t['x'], $t['y'], $t['id'], $pub_id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Handle Add Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_table') {
    $table_number = $_POST['table_number'];
    $capacity = $_POST['capacity'];
    $type = $_POST['type'];
    
    if ($table_number && $capacity) {
        $stmt = $pdo->prepare("INSERT INTO dining_tables (pub_id, table_number, capacity, type, coord_x, coord_y) VALUES (?, ?, ?, ?, 50, 50)");
        $stmt->execute([$pub_id, $table_number, $capacity, $type]);
        header("Location: layout.php?pub_id=" . $pub_id);
        exit;
    }
}

// Handle Delete Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_table') {
    $del_id = $_POST['table_id'];
    if ($del_id) {
        $stmt = $pdo->prepare("DELETE FROM dining_tables WHERE table_id = ? AND pub_id = ?");
        $stmt->execute([$del_id, $pub_id]);
        header("Location: layout.php?pub_id=" . $pub_id);
        exit;
    }
}

// Handle Rename Table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename_table') {
    $rename_id = $_POST['table_id'];
    $new_name = trim($_POST['new_name']);
    if ($rename_id && $new_name !== '') {
        $stmt = $pdo->prepare("UPDATE dining_tables SET table_number = ? WHERE table_id = ? AND pub_id = ?");
        $stmt->execute([$new_name, $rename_id, $pub_id]);
        header("Location: layout.php?pub_id=" . $pub_id);
        exit;
    }
}

// Fetch tables for this pub
$stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE pub_id = ?");
$stmt->execute([$pub_id]);
$tables = $stmt->fetchAll();

$page_title = 'Table Layout - ' . htmlspecialchars($pub['pub_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
    <style>
        .draggable-table {
            cursor: grab;
            user-select: none;
            touch-action: none;
        }
        .draggable-table:active {
            cursor: grabbing;
        }
        #mapContainer {
            touch-action: none; /* Prevent scrolling while dragging on touch devices */
        }
    </style>
</head>
<body class="bg-darker text-white font-sans flex flex-col min-h-screen">
    <nav class="bg-surface border-b border-gray-800 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-primary tracking-wider hover:text-white transition-colors">MaoHub Admin</a>
            <ul class="flex space-x-6">
                <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Reservations</a></li>
                <li><a href="pubs.php" class="text-secondary font-bold transition-colors">Pubs</a></li>
                <li><a href="users.php" class="text-gray-300 hover:text-secondary transition-colors">Users</a></li>
                <li><a href="../index.php" class="text-gray-300 hover:text-secondary transition-colors">View Site</a></li>
                <li><a href="../logout.php" class="text-gray-300 hover:text-error transition-colors">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8 flex-grow flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-primary border-l-4 border-primary pl-4 mb-2">Table Layout Dashboard</h1>
                <p class="text-gray-400">Editing layout for: <span class="text-white font-bold"><?php echo htmlspecialchars($pub['pub_name']); ?></span></p>
            </div>
            <div class="flex gap-4">
                <a href="pubs.php" class="btn bg-gray-700 text-white hover:bg-gray-600">Back</a>
                <button id="saveLayoutBtn" class="btn bg-green-600 hover:bg-green-500 text-white shadow-[0_0_15px_rgba(0,255,0,0.3)]">Save Layout</button>
            </div>
        </div>
        
        <div class="bg-[#fdf8f5] p-4 rounded-lg flex-grow flex flex-col border border-gray-800">
            <!-- Instructions -->
            <div class="mb-4 text-sm text-gray-600">
                <p>Drag and drop the tables below to position them. Click "Save Layout" when you are done.</p>
            </div>

            <!-- Map Container Area -->
            <div class="w-full bg-[#fffbfa] rounded-lg border-2 border-gray-800 relative overflow-hidden shadow-inner flex-grow min-h-[600px]" id="mapContainer">
                <div class="stage-marker !bg-[#f67280] !text-[#fdf8f5] !border-[#f67280]">STAGE</div>
                
                <!-- Legend -->
                <div class="absolute top-4 left-4 bg-[#fdf8f5] p-2 rounded text-xs text-gray-600 pointer-events-none z-10 border border-gray-700">
                    <div class="font-bold text-primary mb-2 ">Table Types:</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-green-500"></span> Standard</div>
                    <div class="flex items-center gap-2 mb-1"><span class="w-3 h-3 rounded-full bg-green-500 border-4 border-yellow-400"></span> VIP</div>
                </div>

                <!-- Tables Layer -->
                <div id="tablesLayer" class="w-full h-full absolute inset-0">
                    <?php foreach($tables as $t): ?>
                        <div class="table-marker draggable-table bg-green-500 <?php echo ($t['type'] === 'vip') ? 'vip border-4 border-yellow-400' : ''; ?>" 
                             style="left: <?php echo $t['coord_x']; ?>%; top: <?php echo $t['coord_y']; ?>%;"
                             data-id="<?php echo $t['table_id']; ?>"
                             data-x="<?php echo $t['coord_x']; ?>"
                             data-y="<?php echo $t['coord_y']; ?>">
                            <?php echo htmlspecialchars($t['table_number']); ?>
                            <form method="POST" class="absolute -top-3 -right-6 p-0 m-0 z-50 flex gap-1 transform translate-x-1/2">
                                <input type="hidden" name="action" value="">
                                <input type="hidden" name="table_id" value="<?php echo $t['table_id']; ?>">
                                <input type="hidden" name="new_name" id="new_name_<?php echo $t['table_id']; ?>" value="">
                                
                                <!-- Rename Button -->
                                <button type="button" class="bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none hover:bg-blue-600 shadow focus:outline-none" 
                                        onmousedown="event.stopPropagation();" 
                                        ontouchstart="event.stopPropagation();" 
                                        onclick="event.stopPropagation(); let n = prompt('Enter new table name:', '<?php echo htmlspecialchars($t['table_number']); ?>'); if(n && n.trim() !== '') { document.getElementById('new_name_<?php echo $t['table_id']; ?>').value = n; this.form.action.value='rename_table'; this.form.submit(); }" title="Rename Table">✎</button>
                                
                                <!-- Delete Button -->
                                <button type="button" class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none hover:bg-red-600 shadow focus:outline-none" 
                                        onmousedown="event.stopPropagation();" 
                                        ontouchstart="event.stopPropagation();" 
                                        onclick="event.stopPropagation(); if(confirm('Delete table <?php echo htmlspecialchars($t['table_number']); ?>?')) { this.form.action.value='delete_table'; this.form.submit(); }" title="Delete Table">&times;</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($tables)): ?>
                        <p class="text-center text-gray-500 mt-20">No tables found for this pub. Please add data in the database first.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Add Table Form -->
            <div class="mt-8 bg-[#EA7B7B]/30 p-4 rounded-lg border border-gray-700">
                <h2 class="text-xl font-bold text-primary mb-4">Add New Table</h2>
                <form method="POST" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="action" value="add_table">
                    <div>
                        <label class="block text-gray-600 text-xs mb-1">Table Number</label>
                        <input type="text" name="table_number" required class="p-2 rounded bg-[#ffe4e6] border border-gray-600 text-gray-600 focus:outline-none focus:border-secondary transition-colors" placeholder="e.g. T1">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs mb-1">Capacity</label>
                        <input type="number" name="capacity" required class="p-2 rounded bg-[#ffe4e6] border border-gray-600 text-gray-600 focus:outline-none focus:border-secondary transition-colors" value="4" min="1" max="20">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-xs mb-1">Type</label>
                        <select name="type" class="p-2 rounded bg-[#ffe4e6] border border-gray-600 text-gray-600 focus:outline-none focus:border-secondary transition-colors">
                            <option value="standard">Standard</option>
                            <option value="vip">VIP</option>
                            <option value="large_group">Large Group</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-500 transition-colors">Add Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
        Layout saved successfully!
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('mapContainer');
        const tables = document.querySelectorAll('.draggable-table');
        let activeTable = null;
        let isDragging = false;
        let containerRect = null;
        
        let initialMouseX = 0;
        let initialMouseY = 0;
        let startLeft = 0;
        let startTop = 0;

        tables.forEach(table => {
            table.addEventListener('mousedown', startDrag);
            table.addEventListener('touchstart', startDrag, {passive: false});
        });

        function startDrag(e) {
            if(e.type === 'touchstart') e.preventDefault();
            activeTable = e.target;
            isDragging = true;
            containerRect = container.getBoundingClientRect();
            
            startLeft = parseFloat(activeTable.style.left) || 0;
            startTop = parseFloat(activeTable.style.top) || 0;

            initialMouseX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            initialMouseY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
            
            // Bring to front
            activeTable.style.zIndex = '50';
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag, {passive: false});
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchend', endDrag);
        }

        function drag(e) {
            if(!isDragging || !activeTable) return;
            e.preventDefault();

            let clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            let clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

            // Delta in pixels
            let deltaX = clientX - initialMouseX;
            let deltaY = clientY - initialMouseY;

            // Convert delta to percentage relative to container
            let deltaPctX = (deltaX / containerRect.width) * 100;
            let deltaPctY = (deltaY / containerRect.height) * 100;

            let xPos = startLeft + deltaPctX;
            let yPos = startTop + deltaPctY;

            // Clamp between 0 and 100
            xPos = Math.max(0, Math.min(100, xPos));
            yPos = Math.max(0, Math.min(100, yPos));

            activeTable.style.left = xPos + '%';
            activeTable.style.top = yPos + '%';
            
            // Store new coords in dataset for saving
            activeTable.dataset.x = Math.round(xPos);
            activeTable.dataset.y = Math.round(yPos);
        }

        function endDrag(e) {
            if(!isDragging || !activeTable) return;
            isDragging = false;
            activeTable.style.zIndex = '';
            
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('touchmove', drag);
            document.removeEventListener('mouseup', endDrag);
            document.removeEventListener('touchend', endDrag);
            activeTable = null;
        }

        // Save Layout
        const saveBtn = document.getElementById('saveLayoutBtn');
        saveBtn.addEventListener('click', async () => {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
            
            const payload = { tables: [] };
            tables.forEach(t => {
                payload.tables.push({
                    id: t.dataset.id,
                    x: parseInt(t.dataset.x),
                    y: parseInt(t.dataset.y)
                });
            });

            try {
                const response = await fetch('layout.php?pub_id=<?php echo $pub_id; ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                if(data.success) {
                    showToast();
                } else {
                    alert('Error saving layout.');
                }
            } catch(e) {
                console.error(e);
                alert('Connection error.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Layout';
            }
        });

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }
    });
    </script>
</body>
</html>
