<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0 bg-gradient-to-b from-gray-900 to-gray-800 text-white">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-8 px-3">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 w-10 h-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hotel text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Hotel KS</h2>
                    <p class="text-xs text-gray-400">Admin Panel</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button id="close-sidebar" class="lg:hidden text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">
            <a href="index.php" class="sidebar-link <?php echo ($current_page == 'index') ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-home text-lg"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="products.php" class="sidebar-link <?php echo ($current_page == 'products') ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-box text-lg"></i>
                <span class="font-medium">Produktet</span>
            </a>
            
            <a href="orders.php" class="sidebar-link <?php echo ($current_page == 'orders') ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="font-medium">Porositë</span>
            </a>
            
            <a href="customers.php" class="sidebar-link <?php echo ($current_page == 'customers') ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-users text-lg"></i>
                <span class="font-medium">Klientët</span>
            </a>
            
            <a href="settings.php" class="sidebar-link <?php echo ($current_page == 'settings') ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-cog text-lg"></i>
                <span class="font-medium">Cilësimet</span>
            </a>
        </nav>

        <!-- Logout Button -->
        <div class="absolute bottom-4 left-0 right-0 px-6">
            <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span class="font-medium">Dilni</span>
            </a>
        </div>
    </div>
</aside>

<!-- Note: Mobile menu script is now in the main page file to avoid conflicts -->