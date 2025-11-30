<?php
require_once 'includes/auth_check.php';
require_once '../backend/db.php';

$current_page = 'products';

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: products.php?success=deleted");
        exit();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_clauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM products $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "SELECT * FROM products $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produktet - Hotel KS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-left: 4px solid #3b82f6;
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
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Produktet</h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 hidden sm:block">Menaxho produktet e dyqanit</p>
                    </div>
                </div>
                <a href="add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium transition-colors text-sm sm:text-base">
                    <i class="fas fa-plus mr-2"></i><span class="hidden sm:inline">Shto </span>Produkt
                </a>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <!-- Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php 
                    if ($_GET['success'] === 'added') echo 'Produkti u shtua me sukses!';
                    elseif ($_GET['success'] === 'updated') echo 'Produkti u përditësua me sukses!';
                    elseif ($_GET['success'] === 'deleted') echo 'Produkti u fshi me sukses!';
                    ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kërko</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Kërko produkte..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1 flex items-end space-x-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium transition-colors text-sm sm:text-base">
                                <i class="fas fa-search mr-2"></i>Kërko
                            </button>
                            <a href="products.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Products Grid for Mobile, Table for Desktop -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produkti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Çmimi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Stoku</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statusi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Veprime</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($products)): ?>
                                <?php foreach($products as $product): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="w-12 h-12 object-cover rounded-lg">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-semibold text-gray-900"><?php echo number_format($product['price'], 2); ?>€</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php 
                                            $stock = $product['stock'] ?? 0;
                                            $stock_class = $stock < 10 ? 'text-red-600' : 'text-green-600';
                                            ?>
                                            <span class="text-sm font-medium <?php echo $stock_class; ?>"><?php echo $stock; ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (($product['stock'] ?? 0) > 0): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-check-circle mr-1"></i>Ne stock
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    <i class="fas fa-times-circle mr-1"></i>Pa stok
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $product['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                                   class="text-red-600 hover:text-red-900"
                                                   onclick="return confirm('A jeni të sigurt që doni të fshini këtë produkt?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-box-open text-4xl mb-2"></i>
                                        <p>Nuk u gjetën produkte.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Grid View -->
                <div class="md:hidden">
                    <?php if (!empty($products)): ?>
                        <?php foreach($products as $product): ?>
                            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-16 h-16 object-cover rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-900"><?php echo number_format($product['price'], 2); ?>€</span>
                                            <span class="text-xs px-2 py-1 rounded-full 
                                                <?php echo ($product['stock'] ?? 0) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ($product['stock'] ?? 0) > 0 ? 'Ne stock' : 'Pa stok'; ?>
                                            </span>
                                        </div>
                                        <div class="mt-3 flex space-x-2">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-900 text-sm">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </a>
                                            <a href="?delete=<?php echo $product['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                               class="text-red-600 hover:text-red-900 text-sm"
                                               onclick="return confirm('A jeni të sigurt që doni të fshini këtë produkt?')">
                                                <i class="fas fa-trash mr-1"></i>Fshi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-2"></i>
                            <p>Nuk u gjetën produkte.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex justify-center">
                    <nav class="inline-flex rounded-md shadow">
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" 
                               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-l-md">
                                Para
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" 
                               class="px-4 py-2 text-sm font-medium <?php echo $i == $page ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700 border border-gray-300'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" 
                               class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-r-md">
                                Pas
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
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