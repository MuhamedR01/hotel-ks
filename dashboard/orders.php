<?php
require_once __DIR__ . '/init.php';
require_once 'includes/auth_check.php';
require_once 'config.php';
require_once __DIR__ . '/../backend/init.php';

$conn = db_connect();

// Get current page for sidebar
$current_page = 'orders';
// Allow super_admin, manager and worker
requireRole(['super_admin','manager','worker']);

// Get filters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query - Added item_count subquery
$query = "SELECT o.*, u.name as customer_name, u.email as customer_email,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];
$types = "";

if ($status) {
    $query .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($search) {
    $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$query .= " ORDER BY o.created_at DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Store orders in array
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Get order statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'],
    'processing' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'],
    'completed' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'],
    'cancelled' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Porositë - <?php echo DASHBOARD_TITLE; ?></title>
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
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen bg-gray-50">
        <!-- Top Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-8 py-4 sticky top-0 z-20">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button id="mobile-menu-button" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Porositë</h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 hidden sm:block">Menaxho porositë e klientëve</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600">Total</p>
                            <p class="text-lg sm:text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-2 sm:p-3 rounded-lg">
                            <i class="fas fa-shopping-cart text-blue-600 text-sm sm:text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600">Në pritje</p>
                            <p class="text-lg sm:text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                        </div>
                        <div class="bg-yellow-100 p-2 sm:p-3 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-sm sm:text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600">Duke procesuar</p>
                            <p class="text-lg sm:text-2xl font-bold text-blue-600"><?php echo $stats['processing']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-2 sm:p-3 rounded-lg">
                            <i class="fas fa-spinner text-blue-600 text-sm sm:text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600">Të përfunduara</p>
                            <p class="text-lg sm:text-2xl font-bold text-green-600"><?php echo $stats['completed']; ?></p>
                        </div>
                        <div class="bg-green-100 p-2 sm:p-3 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-sm sm:text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm text-gray-600">Të anuluara</p>
                            <p class="text-lg sm:text-2xl font-bold text-red-600"><?php echo $stats['cancelled']; ?></p>
                        </div>
                        <div class="bg-red-100 p-2 sm:p-3 rounded-lg">
                            <i class="fas fa-times-circle text-red-600 text-sm sm:text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kërko</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Kërko porosi..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Statusi</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                                <option value="">Të gjitha</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Në pritje</option>
                                <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Duke procesuar</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Të përfunduara</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Të anuluara</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-filter mr-2"></i>Filtro
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Numri</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Artikuj</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Totali</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statusi</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Veprime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <span class="font-semibold text-blue-600">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <span class="text-gray-900"><?php echo $order['item_count']; ?> artikuj</span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <span class="font-bold text-gray-900"><?php echo number_format($order['total_amount'], 2); ?>€</span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_classes = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800'
                                            ];
                                            $status_labels = [
                                                'pending' => 'Në pritje',
                                                'processing' => 'Duke procesuar',
                                                'completed' => 'Të përfunduara',
                                                'cancelled' => 'Të anuluara'
                                            ];
                                            $status_class = $status_classes[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                            $status_label = $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 font-semibold">
                                                <i class="fas fa-eye mr-1"></i>Shiko
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-4 sm:px-6 py-12 text-center">
                                        <div class="text-gray-400">
                                            <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                                            <p class="text-lg">Nuk ka porosi për këtë filtrim</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
