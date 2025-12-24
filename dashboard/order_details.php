<?php
require_once __DIR__ . '/init.php';
require_once 'includes/auth_check.php';
require_once __DIR__ . '/../backend/init.php';

$conn = db_connect();

$current_page = 'order_details';

// Get order ID
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['success_message'] = '';
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        header("Location: order_details.php?id=" . $order_id);
        exit();
    }

    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'shipped', 'completed', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $order_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = 'Statusi i porosisë u përditësua me sukses!';
            header("Location: order_details.php?id=" . $order_id);
            exit();
        }
    }
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $result->fetch_assoc();

$items_stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image, p.image_type as product_image_type 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Status labels
$status_labels = [
    'pending' => 'Në Pritje',
    'shipped' => 'Në Postë',
    'completed' => 'E Kompletuar',
    'cancelled' => 'E Anuluar'
];

require_once 'includes/header.php';
?>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden no-print"></div>

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen bg-gray-50">
        <!-- Top Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-8 py-4 sticky top-0 z-20 no-print">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button id="mobile-menu-button" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Porosia #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 hidden sm:block">Detajet e porosisë</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="orders.php" class="sm:hidden text-gray-600 hover:text-gray-900 p-2">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <button onclick="window.print()" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-print mr-0 sm:mr-2"></i>
                        <span class="hidden sm:inline">Printo</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 no-print">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Përmbledhje e Porosisë</h2>
                        <p class="text-sm text-gray-600">Detajet e porosisë dhe statusi</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            <?php 
                            switch($order['status']) {
                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                case 'shipped': echo 'bg-blue-100 text-blue-800'; break;
                                case 'completed': echo 'bg-green-100 text-green-800'; break;
                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                default: echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <i class="fas fa-<?php 
                                switch($order['status']) {
                                    case 'pending': echo 'clock'; break;
                                    case 'shipped': echo 'truck'; break;
                                    case 'completed': echo 'check-circle'; break;
                                    case 'cancelled': echo 'times-circle'; break;
                                    default: echo 'info-circle';
                                }
                            ?> mr-2"></i>
                            <?php echo $status_labels[$order['status']] ?? 'Panjohur'; ?>
                        </span>
                    </div>
                </div>

                <!-- Status Update Form -->
                <form method="POST" class="mb-6 no-print">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Duke u Procesuar</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Në Postë</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>E Kompletuar</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>E Anuluar</option>
                        </select>
                        <button type="submit" name="update_status" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            Përditëso Statusin
                        </button>
                    </div>
                </form>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            Informacioni i Klientit
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p class="flex items-start">
                                <span class="font-medium w-24">Emri:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Email:</span> 
                                <span class="text-gray-700 break-all"><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Telefoni:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-medium text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                            Adresa e Dërgimit
                        </h3>
                        <div class="space-y-2 text-sm">
                            <p class="flex items-start">
                                <span class="font-medium w-24">Adresa:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_address'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Qyteti:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_city'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Shteti:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_country'] ?? 'N/A'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <h3 class="font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-box-open mr-2 text-blue-600"></i>
                    Artikujt e Porosisë
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Madhësia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cmimi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sasia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shuma</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php
                                        // Render product image. Products may store images as BLOBs, data URLs, or remote/relative URLs.
                                        $imgSrc = '';
                                        if (isset($item['product_image']) && $item['product_image'] !== null && $item['product_image'] !== '') {
                                            $prodImg = $item['product_image'];
                                            // If it's already a data URL or an http(s) URL or starts with a slash, use as-is
                                            $startsWithData = is_string($prodImg) && strpos($prodImg, 'data:') === 0;
                                            $isHttp = is_string($prodImg) && preg_match('#^https?://#i', $prodImg);
                                            $isRelative = is_string($prodImg) && strpos($prodImg, '/') === 0;

                                            if ($startsWithData || $isHttp || $isRelative) {
                                                $imgSrc = $prodImg;
                                            } else {
                                                // Assume binary BLOB — convert to base64 using the provided image type if available
                                                $imgType = isset($item['product_image_type']) && $item['product_image_type'] ? $item['product_image_type'] : 'image/jpeg';
                                                $imgSrc = 'data:' . $imgType . ';base64,' . base64_encode($prodImg);
                                            }
                                        }

                                        if ($imgSrc) {
                                            echo '<img class="h-10 w-10 rounded-md object-cover mr-3" src="' . htmlspecialchars($imgSrc) . '" alt="' . htmlspecialchars($item['product_name']) . '">';
                                        } else {
                                            echo '<div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center mr-3"><i class="fas fa-image text-gray-500"></i></div>';
                                        }
                                        ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php
                                    // Support multiple possible size column names
                                    $possibleKeys = ['size', 'selected_size', 'selectedSize', 'variant_size', 'option_size'];
                                    $sizeVal = '';
                                    foreach ($possibleKeys as $k) {
                                        if (isset($item[$k]) && $item[$k] !== null && $item[$k] !== '') {
                                            $sizeVal = $item[$k];
                                            break;
                                        }
                                    }
                                    // Normalize and display
                                    if ($sizeVal !== '') {
                                        $sizeVal = trim((string)$sizeVal);
                                        echo '<span class="text-sm text-gray-700">' . htmlspecialchars($sizeVal) . '</span>';
                                    } else {
                                        echo '<span class="text-sm text-gray-400">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($item['product_price'], 2, ',', '.'); ?>€
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo number_format($item['product_price'] * $item['quantity'], 2, ',', '.'); ?>€
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Totals -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <h3 class="font-medium text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>
                    Totali i Porosisë
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-md">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nën Total:</span>
                        <span class="font-medium"><?php echo number_format($order['subtotal'], 2, ',', '.'); ?>€</span>
                    </div>
                    <!-- Tax removed as requested -->
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shuma e Transportit:</span>
                        <span class="font-medium"><?php echo number_format($order['shipping_cost'], 2, ',', '.'); ?>€</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Totali:</span>
                        <span class="font-bold text-lg"><?php echo number_format($order['total_amount'], 2, ',', '.'); ?>€</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = this;
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
