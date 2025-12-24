<?php
require_once __DIR__ . '/init.php';
require_once 'includes/auth_check.php';
require_once __DIR__ . '/../backend/init.php';

$conn = db_connect();

 $current_page = 'products';
// Require manager or super_admin
requireRole(['super_admin','manager']);

// Handle product deletion (require CSRF token)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $csrf = $_GET['csrf'] ?? '';
    if (!validateCSRFToken($csrf)) {
        http_response_code(400);
        die('Invalid CSRF token');
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: products.php?success=deleted");
        exit();
    }
}

// Handle availability toggle from dashboard (use `available` column if present, otherwise fallback to stock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_availability']) && isset($_POST['product_id'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(400);
        die('Invalid CSRF token');
    }
    $prod_id = intval($_POST['product_id']);
    $available = (isset($_POST['available']) && $_POST['available'] === '1') ? 1 : 0;

    // Check if `available` column exists
    $colCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'available'");
    $useAvailableCol = $colCheck && $colCheck->num_rows > 0;

    if ($useAvailableCol) {
        $updateSql = "UPDATE products SET available = ? WHERE id = ?";
    } else {
        // fallback to stock column (legacy)
        $updateSql = "UPDATE products SET stock = ? WHERE id = ?";
    }

    $updateStmt = $conn->prepare($updateSql);
    if ($updateStmt) {
        $updateStmt->bind_param('ii', $available, $prod_id);
        if ($updateStmt->execute()) {
            // Set a user-friendly session message
            if (session_status() === PHP_SESSION_NONE) session_start();
            if ($available) {
                $_SESSION['success_message'] = 'Produkti u vendos në stok.';
            } else {
                $_SESSION['success_message'] = 'Produkti u hoq nga stoku.';
            }

            // Preserve query string when redirecting back
            $qs = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
            $redirect = 'products.php';
            if ($qs !== '') {
                $redirect .= '?' . $qs;
            }
            header("Location: " . $redirect);
            exit();
        } else {
            $availability_error = 'Database execute error: ' . $conn->error;
        }
    } else {
        $availability_error = 'Database prepare error: ' . $conn->error;
    }
}

// Handle sizes toggle from dashboard (set `has_sizes` flag if column exists)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_sizes']) && isset($_POST['product_id'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        http_response_code(400);
        die('Invalid CSRF token');
    }
    $prod_id = intval($_POST['product_id']);
    $has_sizes = (isset($_POST['has_sizes']) && $_POST['has_sizes'] === '1') ? 1 : 0;

    // Check if `has_sizes` column exists
    $colCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'has_sizes'");
    $useHasSizesCol = $colCheck && $colCheck->num_rows > 0;

    if ($useHasSizesCol) {
        $updateSql = "UPDATE products SET has_sizes = ? WHERE id = ?";
    } else {
        // Column doesn't exist — nothing to update, but avoid error
        $updateSql = null;
    }

    if ($updateSql) {
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param('ii', $has_sizes, $prod_id);
            if ($updateStmt->execute()) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                if ($has_sizes) {
                    $_SESSION['success_message'] = 'Sizes enabled for product.';
                } else {
                    $_SESSION['success_message'] = 'Sizes disabled for product.';
                }

                $qs = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
                $redirect = 'products.php';
                if ($qs !== '') {
                    $redirect .= '?' . $qs;
                }
                header("Location: " . $redirect);
                exit();
            } else {
                $availability_error = 'Database execute error: ' . $conn->error;
            }
        } else {
            $availability_error = 'Database prepare error: ' . $conn->error;
        }
    } else {
        // Column missing — set session message
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['success_message'] = 'Kolona has_sizes mungon në bazën e të dhënave.';
        $qs = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $redirect = 'products.php';
        if ($qs !== '') {
            $redirect .= '?' . $qs;
        }
        header("Location: " . $redirect);
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
            <?php if (!empty($availability_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($availability_error); ?>
                </div>
            <?php endif; ?>
            <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
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
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-36">Madhesia</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-28">Veprime</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($products)): ?>
                                <?php foreach($products as $product): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-semibold text-gray-900"><?php echo number_format($product['price'], 2); ?>€</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php 
                                            // Prefer explicit available flag if present
                                            $isAvailable = isset($product['available']) ? ($product['available'] > 0) : (($product['stock'] ?? 0) > 0);
                                            $switchId = 'avail-' . $product['id'];
                                            ?>
                                                                        <form method="POST" class="inline-block">
                                                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                                            <input type="hidden" name="set_availability" value="1">
                                                                            <input type="hidden" name="available" value="<?php echo $isAvailable ? '1' : '0'; ?>">
                                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                                                <label for="<?php echo $switchId; ?>" class="relative inline-flex items-center cursor-pointer">
                                                    <input id="<?php echo $switchId; ?>" type="checkbox" class="sr-only peer" <?php echo $isAvailable ? 'checked' : ''; ?> onchange="this.form.elements['available'].value = this.checked ? '1' : '0'; this.form.submit();">
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-green-300 peer-checked:bg-green-400 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                                </label>
                                            </form>
                                        </td>
                                                <!-- Sizes control column (switch + quick actions) -->
                                                <td class="px-6 py-4 text-center">
                                                    <?php $hasSizes = isset($product['has_sizes']) ? ($product['has_sizes'] > 0) : false; $sizesSwitchId = 'sizes-' . $product['id']; ?>
                                                    <div class="flex items-center justify-center">
                                                        <form method="POST" class="inline-block">
                                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                            <input type="hidden" name="set_sizes" value="1">
                                                            <input type="hidden" name="has_sizes" value="<?php echo $hasSizes ? '1' : '0'; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                                                            <label for="<?php echo $sizesSwitchId; ?>" class="relative inline-flex items-center cursor-pointer">
                                                                <input id="<?php echo $sizesSwitchId; ?>" type="checkbox" class="sr-only peer" <?php echo $hasSizes ? 'checked' : ''; ?> onchange="this.form.elements['has_sizes'].value = this.checked ? '1' : '0'; this.form.submit();">
                                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-indigo-300 peer-checked:bg-indigo-500 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                                            </label>
                                                        </form>
                                                    </div>
                                                </td>
                                        <td class="px-6 py-4 text-right align-middle">
                                            <div class="flex items-center justify-end space-x-3">
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                                <a href="?delete=<?php echo $product['id']; ?>&csrf=<?php echo urlencode(generateCSRFToken()); ?>&<?php echo http_build_query($_GET); ?>" 
                                                   class="text-red-600 hover:text-red-900 text-sm"
                                                   onclick="return confirm('A jeni të sigurt që doni të fshini këtë produkt?')">
                                                    <i class="fas fa-trash text-sm"></i>
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
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                                    <div class="flex-1 pr-4">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p class="text-sm text-gray-600 mt-1 sm:mt-0"><?php echo number_format($product['price'], 2); ?>€</p>
                                    </div>

                                    <div class="flex items-center space-x-3 mt-3 sm:mt-0 flex-shrink-0">
                                        <?php $isAvailableMobile = isset($product['available']) ? ($product['available'] > 0) : (($product['stock'] ?? 0) > 0); $switchIdMobile = 'avail-mobile-' . $product['id']; ?>
                                            <form method="POST" class="inline-flex items-center">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <input type="hidden" name="set_availability" value="1">
                                                <input type="hidden" name="available" value="<?php echo $isAvailableMobile ? '1' : '0'; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                                            <label for="<?php echo $switchIdMobile; ?>" class="relative inline-flex items-center cursor-pointer">
                                                <input id="<?php echo $switchIdMobile; ?>" type="checkbox" class="sr-only peer" <?php echo $isAvailableMobile ? 'checked' : ''; ?> onchange="this.form.elements['available'].value = this.checked ? '1' : '0'; this.form.submit();">
                                                <div class="w-9 h-4 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-green-300 peer-checked:bg-green-400 relative after:content-[''] after:absolute after:top-0.35 after:left-0.35 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:after:translate-x-full"></div>
                                            </label>
                                        </form>

                                        <?php $hasSizesMobile = isset($product['has_sizes']) ? ($product['has_sizes'] > 0) : false; $sizesSwitchIdMobile = 'sizes-mobile-' . $product['id']; ?>
                                        <form method="POST" class="inline-flex items-center">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="set_sizes" value="1">
                                            <input type="hidden" name="has_sizes" value="<?php echo $hasSizesMobile ? '1' : '0'; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                                            <label for="<?php echo $sizesSwitchIdMobile; ?>" class="relative inline-flex items-center cursor-pointer">
                                                <input id="<?php echo $sizesSwitchIdMobile; ?>" type="checkbox" class="sr-only peer" <?php echo $hasSizesMobile ? 'checked' : ''; ?> onchange="this.form.elements['has_sizes'].value = this.checked ? '1' : '0'; this.form.submit();">
                                                <div class="w-9 h-4 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-indigo-300 peer-checked:bg-indigo-500 relative after:content-[''] after:absolute after:top-0.35 after:left-0.35 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:after:translate-x-full"></div>
                                            </label>
                                        </form>

                                        <div class="flex items-center space-x-2 ml-2">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $product['id']; ?>&csrf=<?php echo urlencode(generateCSRFToken()); ?>&<?php echo http_build_query($_GET); ?>" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('A jeni të sigurt që doni të fshini këtë produkt?')">
                                                <i class="fas fa-trash"></i>
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