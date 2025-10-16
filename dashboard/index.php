<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit();
}

require_once '../backend/db.php';
require_once 'config.php';

$current_page = 'index';
$page_title = 'Dashboard';

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';

// Get statistics
$stats = [
    'total_products' => 0,
    'total_orders' => 0,
    'total_customers' => 0,
    'total_revenue' => 0,
    'pending_orders' => 0,
    'low_stock' => 0
];

// Total Products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result) {
    $stats['total_products'] = $result->fetch_assoc()['count'];
}

// Check if orders table exists
$table_check = $conn->query("SHOW TABLES LIKE 'orders'");
if ($table_check && $table_check->num_rows > 0) {
    // Total Orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    if ($result) {
        $stats['total_orders'] = $result->fetch_assoc()['count'];
    }

    // Total Revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_revenue'] = $row['total'] ?? 0;
    }

    // Pending Orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    if ($result) {
        $stats['pending_orders'] = $result->fetch_assoc()['count'];
    }
}

// Check if users table exists and has role column
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check && $table_check->num_rows > 0) {
    // Check if role column exists
    $column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($column_check && $column_check->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    } else {
        // If no role column, count all users
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
    }
    if ($result) {
        $stats['total_customers'] = $result->fetch_assoc()['count'];
    }
}

// Check if stock column exists in products
$column_check = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($column_check && $column_check->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10");
    if ($result) {
        $stats['low_stock'] = $result->fetch_assoc()['count'];
    }
}

// Recent Orders
$recent_orders = [];
$table_check = $conn->query("SHOW TABLES LIKE 'orders'");
if ($table_check && $table_check->num_rows > 0) {
    $result = $conn->query("SELECT o.*, COALESCE(u.name, o.customer_name, 'Guest') as customer_name 
                           FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recent_orders[] = $row;
        }
    }
}

// Top Products
$top_products = [];
$table_check = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($table_check && $table_check->num_rows > 0) {
    $result = $conn->query("SELECT p.*, COALESCE(SUM(oi.quantity), 0) as total_sold 
                           FROM products p 
                           LEFT JOIN order_items oi ON p.id = oi.product_id 
                           GROUP BY p.id 
                           ORDER BY total_sold DESC 
                           LIMIT 5");
} else {
    // If no order_items table, just get recent products
    $result = $conn->query("SELECT *, 0 as total_sold FROM products ORDER BY created_at DESC LIMIT 5");
}
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo DASHBOARD_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @media (max-width: 640px) {
            .stat-card {
                padding: 1rem;
            }
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-20">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <button id="mobile-menu-button" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Dashboard</h1>
                        <p class="text-xs sm:text-sm text-gray-500 hidden sm:block">Mirë se vini përsëri, <?php echo htmlspecialchars($admin_name); ?>!</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-bell text-lg sm:text-xl"></i>
                        <?php if ($stats['pending_orders'] > 0): ?>
                            <span class="absolute top-0 right-0 w-4 h-4 sm:w-5 sm:h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                <?php echo $stats['pending_orders']; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="hidden sm:flex items-center space-x-3 pl-4 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($admin_name); ?></p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-full flex items-center justify-center font-semibold">
                            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Total Products -->
                <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div>
                            <p class="text-blue-100 text-xs sm:text-sm font-medium">Produktet</p>
                            <h3 class="text-2xl sm:text-3xl font-bold mt-1"><?php echo number_format($stats['total_products']); ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg">
                            <i class="fas fa-box text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-xs sm:text-sm text-blue-100">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Total në inventar</span>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div>
                            <p class="text-green-100 text-xs sm:text-sm font-medium">Porositë</p>
                            <h3 class="text-2xl sm:text-3xl font-bold mt-1"><?php echo number_format($stats['total_orders']); ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg">
                            <i class="fas fa-shopping-cart text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-xs sm:text-sm text-green-100">
                        <i class="fas fa-clock mr-1"></i>
                        <span><?php echo $stats['pending_orders']; ?> në pritje</span>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div>
                            <p class="text-purple-100 text-xs sm:text-sm font-medium">Klientët</p>
                            <h3 class="text-2xl sm:text-3xl font-bold mt-1"><?php echo number_format($stats['total_customers']); ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg">
                            <i class="fas fa-users text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-xs sm:text-sm text-purple-100">
                        <i class="fas fa-user-plus mr-1"></i>
                        <span>Total të regjistruar</span>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div>
                            <p class="text-orange-100 text-xs sm:text-sm font-medium">Të Ardhurat</p>
                            <h3 class="text-2xl sm:text-3xl font-bold mt-1"><?php echo number_format($stats['total_revenue'], 2); ?>€</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg">
                            <i class="fas fa-euro-sign text-xl sm:text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-800">
                                <i class="fas fa-shopping-bag mr-2 text-blue-600"></i>
                                Porositë e Fundit
                            </h2>
                            <a href="orders.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Shiko të gjitha <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Porosia</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Klienti</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Shuma</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statusi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach($recent_orders as $order): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-semibold text-gray-900"><?php echo number_format($order['total_amount'], 2); ?>€</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'processing' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800'
                                                ];
                                                $status_labels = [
                                                    'pending' => 'Në pritje',
                                                    'processing' => 'Duke u procesuar',
                                                    'completed' => 'E përfunduar',
                                                    'cancelled' => 'E anuluar'
                                                ];
                                                $status = $order['status'];
                                                ?>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $status_colors[$status]; ?>">
                                                    <?php echo $status_labels[$status]; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-2"></i>
                                            <p>Nuk ka porosi të fundit</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                            Produktet Më të Shpjetura
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($top_products)): ?>
                            <div class="space-y-4">
                                <?php foreach($top_products as $product): ?>
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="w-12 h-12 object-cover rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                                <p class="text-xs text-gray-500">Shpenzuar: <?php echo $product['total_sold']; ?> copë</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold text-blue-600"><?php echo number_format($product['price'], 2); ?>€</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-2"></i>
                                <p>Nuk ka të dhëna për produktet më të shpjetura</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-box-open mr-2 text-purple-600"></i>
                            Produktet e Fundit
                        </h2>
                        <a href="products.php" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Shiko të gjitha <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php
                        $recent_products = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
                        if ($recent_products && $recent_products->num_rows > 0):
                            while($product = $recent_products->fetch_assoc()):
                        ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-40 object-cover rounded-lg mb-3">
                                <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2 line-clamp-2"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-blue-600"><?php echo number_format($product['price'], 2); ?>€</span>
                                    <div class="flex space-x-2">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                           class="text-red-600 hover:text-red-700"
                                           onclick="return confirm('Jeni të sigurt që dëshironi të fshini këtë produkt?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div class="col-span-3 text-center py-12 text-gray-500">
                                <i class="fas fa-box text-5xl mb-3"></i>
                                <p>Nuk ka produkte të shtuar ende</p>
                                <a href="add_product.php" class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                    Shto Produkt
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
