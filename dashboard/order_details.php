<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get order ID
$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Get current page for sidebar
$current_page = 'orders';

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT name, email FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
$admin_name = $admin['name'] ?? 'Admin';
$admin_email = $admin['email'] ?? '';

// Get order details with all fields
$query = "SELECT o.* FROM orders o WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items with product details
$query = "SELECT oi.*, p.name as product_name, p.image as product_image, p.price as product_price
          FROM order_items oi 
          LEFT JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Statusi u përditësua me sukses!";
        header("Location: order_details.php?id=" . $order_id);
        exit();
    }
}

// Status labels
$status_labels = [
    'pending' => 'Në Pritje',
    'processing' => 'Në Proces',
    'shipped' => 'I Dërguar',
    'delivered' => 'I Livruar',
    'completed' => 'I Përfunduar',
    'cancelled' => 'Anuluar'
];
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detajet e Porosisë #<?php echo htmlspecialchars($order['order_number']); ?> - <?php echo DASHBOARD_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar-link:hover:not(.active) {
            background-color: #f3f4f6;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
    </style>
</head>
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
                        <div class="flex items-center space-x-2">
                            <a href="orders.php" class="text-gray-600 hover:text-gray-900">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Porosia #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Detajet e porosisë</p>
                    </div>
                </div>
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>Printo
                </button>
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Përmbledhje e Porosisë</h2>
                        <p class="text-gray-600">Detajet e porosisë dhe statusi</p>
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
                        <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Në Pritje</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Në Proces</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>I Dërguar</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>I Livruar</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>I Përfunduar</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Anuluar</option>
                        </select>
                        <button type="submit" name="update_status" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
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
                                <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></span>
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
                            Adresa e Livrimut
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
        
        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
