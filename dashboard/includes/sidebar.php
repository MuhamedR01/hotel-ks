<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 text-white w-10 h-10 rounded-lg flex items-center justify-center font-bold text-lg">
                    KS
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Hotel KS</h1>
                    <p class="text-xs text-gray-500">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4">
            <div class="px-4 space-y-1">
                <a href="index.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="products.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 <?php echo $current_page === 'products' || $current_page === 'add_product' || $current_page === 'edit_product' ? 'active' : ''; ?>">
                    <i class="fas fa-box w-5"></i>
                    <span class="font-medium">Produktet</span>
                </a>

                <a href="orders.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 <?php echo $current_page === 'orders' || $current_page === 'order_details' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="font-medium">Porositë</span>
                </a>

                <a href="customers.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 <?php echo $current_page === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span class="font-medium">Klientët</span>
                </a>

                <a href="settings.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Cilësimet</span>
                </a>
            </div>
        </nav>

        <!-- User Info & Logout -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center space-x-3 mb-3 px-4">
                <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-full flex items-center justify-center font-semibold">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                    <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="font-medium">Dil</span>
            </a>
        </div>
    </div>
</aside>

<script>
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (mobileMenuButton && sidebar && sidebarOverlay) {
        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });
    }
</script>