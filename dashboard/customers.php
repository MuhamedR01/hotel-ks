<?php
$current_page = 'customers';
require_once 'includes/auth_check.php';
// Only super_admin can view customers
requireRole(['super_admin']);

// Use the centralized backend DB helper so schema matches the rest of the app
require_once __DIR__ . '/../backend/init.php';
$conn = db_connect();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = "WHERE u.name LIKE ? OR u.email LIKE ? OR u.unique_id LIKE ? OR u.phone LIKE ?";
    $search_param = "%{$search}%";
    $search_params = [$search_param, $search_param, $search_param, $search_param];
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users u $search_condition";
$count_stmt = $conn->prepare($count_sql);
if (!empty($search_params)) {
    $count_stmt->bind_param("ssss", ...$search_params);
}
$count_stmt->execute();
$total_customers = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $items_per_page);

// Get customers with their order statistics
$sql = "SELECT 
    u.id,
    u.unique_id,
    u.name,
    u.email,
    u.phone,
    u.address,
    u.city,
    u.country,
    u.created_at,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    MAX(o.created_at) as last_order_date
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
$search_condition
GROUP BY u.id
ORDER BY u.created_at DESC
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($search_params)) {
    $params = array_merge($search_params, [$items_per_page, $offset]);
    $types = str_repeat('s', count($search_params)) . 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
?>

<div class="flex-1 ml-0 lg:ml-64 transition-all duration-300">
    <?php require_once 'includes/topbar.php'; ?>
    
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Klientët</h1>
            <p class="text-gray-600 mt-1">Menaxhoni dhe shikoni informacionin e klientëve</p>
        </div>

        <!-- (Statistics removed per request) -->

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Kërko sipas emrit, email, ID ose telefoni..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <i class="fas fa-search mr-2"></i>Kërko
                </button>
                <?php if (!empty($search)): ?>
                <a
                    href="customers.php"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center"
                >
                    <i class="fas fa-times mr-2"></i>Pastro
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Klienti
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontakti
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lokacioni
                                </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Regjistruar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Veprime
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-2"></i>
                                <p>Nuk u gjetën klientë</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold text-lg">
                                                <?php echo strtoupper(substr($customer['name'] ?? 'U', 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($customer['name'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            ID: <?php echo htmlspecialchars($customer['unique_id']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($customer['email']); ?>
                                </div>
                                <?php if (!empty($customer['phone'])): ?>
                                <div class="text-sm text-gray-500 mt-1">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($customer['phone']); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php echo htmlspecialchars($customer['address'] ?? ''); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?>
                                    <?php if (!empty($customer['country'])): ?>, <?php echo htmlspecialchars($customer['country']); ?><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i> Shiko
                                </a>
                                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">
                                    <i class="fas fa-edit"></i> Redakto
                                </a>
                                <a href="#" class="text-red-600 hover:text-red-900 delete-customer" data-id="<?php echo $customer['id']; ?>">
                                    <i class="fas fa-trash"></i> Fshi
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Duke shfaqur <span class="font-medium"><?php echo ($page - 1) * $items_per_page + 1; ?></span> deri në <span class="font-medium"><?php echo min($page * $items_per_page, $total_customers); ?></span> nga <span class="font-medium"><?php echo $total_customers; ?></span> rezultate
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-md <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="px-3 py-1 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete customer confirmation
    document.querySelectorAll('.delete-customer').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const customerId = this.getAttribute('data-id');
            if (confirm('A jeni të sigurt që dëshironi të fshini këtë klient?')) {
                // Here you would typically make an AJAX request to delete the customer
                alert('Fshirja e klientit është e implementuar në backend!');
            }
        });
    });
});
</script>
