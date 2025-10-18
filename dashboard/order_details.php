<?php
session_start();
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Statusi i porosisë u përditësua me sukses!';
        header("Location: order_details.php?id=" . $order_id);
        exit;
    }
    $stmt->close();
}

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.*, 
           COALESCE(o.subtotal, 0) as subtotal,
           COALESCE(o.tax, 0) as tax,
           COALESCE(o.shipping_cost, 0) as shipping_cost,
           COALESCE(o.total_amount, 0) as total_amount
    FROM orders o 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch order items with product details
$stmt = $conn->prepare("
    SELECT oi.*, p.image as product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$status_labels = [
    'pending' => 'Në Pritje',
    'processing' => 'Në Proces',
    'shipped' => 'I Dërguar',
    'delivered' => 'I Livruar',
    'completed' => 'I Përfunduar',
    'cancelled' => 'Anuluar'
];

$page_title = 'Detajet e Porosisë';
include __DIR__ . '/includes/header.php';
?>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
        .sidebar {
            display: none !important;
        }
        main {
            margin-left: 0 !important;
        }
    }
</style>

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
                    <a href="orders.php" class="text-gray-600 hover:text-gray-900 hidden sm:block">
                        <i class="fas fa-arrow-left"></i>
                    </a>
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
                                case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                                case 'shipped': echo 'bg-purple-100 text-purple-800'; break;
                                case 'delivered': 
                                case 'completed': echo 'bg-green-100 text-green-800'; break;
                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                default: echo 'bg-gray-100 text-gray-800';
                            }
                            ?>">
                            <i class="fas fa-<?php 
                                switch($order['status']) {
                                    case 'pending': echo 'clock'; break;
                                    case 'processing': echo 'cog'; break;
                                    case 'shipped': echo 'truck'; break;
                                    case 'delivered': 
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
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Në Pritje</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Në Proces</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>I Dërguar</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>I Livruar</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>I Përfunduar</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Anuluar</option>
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
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Qyteti:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['shipping_city'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="flex items-start">
                                <span class="font-medium w-24">Zip:</span> 
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['shipping_zip'] ?? 'N/A'); ?></span>
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
                                        <?php if ($item['product_image']): ?>
                                            <img class="h-10 w-10 rounded-md object-cover mr-3" src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center mr-3">
                                                <i class="fas fa-image text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </div>
                                    </div>
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
                    <div class="flex justify-between">
                        <span class="text-gray-600">TVSH (20%):</span>
                        <span class="font-medium"><?php echo number_format($order['tax'], 2, ',', '.'); ?>€</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shpenzimet e Livrimut:</span>
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
        // Mobile menu toggle - Fixed
        const menuButton = document.getElementById('mobile-menu-button');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebar = document.getElementById('sidebar');

        if (menuButton) {
            menuButton.addEventListener('click', () => {
                if (sidebar) {
                    sidebar.classList.toggle('-translate-x-full');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('hidden');
                }
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                if (sidebar) {
                    sidebar.classList.add('-translate-x-full');
                }
                sidebarOverlay.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
