
<header class="bg-white shadow-sm sticky top-0 z-30">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-button" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <!-- Page Title (optional, can be overridden) -->
        <div class="hidden lg:block">
            <h2 class="text-xl font-semibold text-gray-800">
                <?php 
                $page_titles = [
                    'index' => 'Dashboard',
                    'products' => 'Produktet',
                    'add_product' => 'Shto Produkt',
                    'edit_product' => 'Redakto Produkt',
                    'orders' => 'Porositë',
                    'order_details' => 'Detajet e Porosisë',
                    'customers' => 'Klientët',
                    'customer_details' => 'Detajet e Klientit',
                    'settings' => 'Cilësimet'
                ];
                echo $page_titles[$current_page] ?? 'Admin Panel';
                ?>
            </h2>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative">
                <button class="text-gray-600 hover:text-gray-900 focus:outline-none relative">
                    <i class="fas fa-bell text-xl"></i>
                    <?php
                    // Get unread notifications count
                    $notif_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
                    $notif_result = $conn->query($notif_query);
                    $notif_count = $notif_result->fetch_assoc()['count'];
                    
                    if ($notif_count > 0):
                    ?>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        <?php echo $notif_count > 9 ? '9+' : $notif_count; ?>
                    </span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Chat Icon -->
            <div class="relative">
                <button class="text-gray-600 hover:text-gray-900 focus:outline-none relative">
                    <i class="fas fa-comments text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        2
                    </span>
                </button>
            </div>

            <!-- User Profile -->
            <div class="flex items-center space-x-3">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-semibold text-gray-900">Admin</p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                    A
                </div>
            </div>

            <!-- Logout -->
            <a href="logout.php" class="text-gray-600 hover:text-red-600 focus:outline-none" title="Logout">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>
</header>

<script>
// Mobile menu toggle
document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar?.classList.toggle('-translate-x-full');
});
</script>
