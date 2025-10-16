<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit();
}

require_once '../backend/db.php';
require_once 'config.php';

$current_page = 'add_product';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $image_data = null;
    $image_type = null;

    // Validate required fields
    if (empty($name) || $price <= 0) {
        $error = 'Emri dhe çmimi janë të detyrueshëm!';
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['image']['size'];

            // Validate file
            if (!in_array($filetype, $allowed)) {
                $error = 'Formati i imazhit nuk është i lejuar. Përdorni: ' . implode(', ', $allowed);
            } elseif ($filesize > MAX_FILE_SIZE) {
                $error = 'Imazhi është shumë i madh. Maksimumi: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
            } else {
                // Read image file as binary data
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
                $image_type = $_FILES['image']['type'];
            }
        }

        // Insert product if no errors
        if (empty($error)) {
            if ($image_data !== null) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, image_type, category, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sdsbssi", $name, $price, $description, $image_data, $image_type, $category, $stock);
                // Send the blob data
                $stmt->send_long_data(3, $image_data);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, price, description, category, stock, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sdssi", $name, $price, $description, $category, $stock);
            }
            
            if ($stmt->execute()) {
                $msg = 'Produkti u shtua me sukses!';
                // Clear form
                $name = $price = $description = $category = $stock = '';
            } else {
                $error = 'Gabim në shtimin e produktit: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get admin info for sidebar
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shto Produkt - Hotel KS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Shto Produkt të Ri</h1>
                        <p class="text-sm text-gray-500">Plotësoni të dhënat e produktit</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="products.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kthehu te Produktet
                    </a>
                </div>

                <!-- Messages -->
                <?php if ($msg): ?>
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <p class="text-green-700"><?php echo htmlspecialchars($msg); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Informacioni i Produktit</h2>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6" id="productForm">
                        <!-- Product Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Emri i Produktit <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="Shënoni emrin e produktit"
                                required
                            >
                        </div>

                        <!-- Price and Stock -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Çmimi (€) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="price" 
                                    value="<?php echo htmlspecialchars($price ?? ''); ?>"
                                    step="0.01" 
                                    min="0"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="0.00"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sasia në Stok
                                </label>
                                <input 
                                    type="number" 
                                    name="stock" 
                                    value="<?php echo htmlspecialchars($stock ?? ''); ?>"
                                    min="0"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    placeholder="0"
                                >
                            </div>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kategoria
                            </label>
                            <select 
                                name="category"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                                <option value="">Zgjidhni kategorinë</option>
                                <option value="shtretër" <?php echo (isset($category) && $category == 'shtretër') ? 'selected' : ''; ?>>Shtretër</option>
                                <option value="banjo" <?php echo (isset($category) && $category == 'banjo') ? 'selected' : ''; ?>>Banjo</option>
                                <option value="dhoma" <?php echo (isset($category) && $category == 'dhoma') ? 'selected' : ''; ?>>Dhoma</option>
                                <option value="aksesorë" <?php echo (isset($category) && $category == 'aksesorë') ? 'selected' : ''; ?>>Aksesorë</option>
                                <option value="Tjetër" <?php echo (isset($category) && $category == 'Tjetër') ? 'selected' : ''; ?>>Tjetër</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Përshkrimi
                            </label>
                            <textarea 
                                name="description" 
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                placeholder="Shkruani një përshkrim të produktit"
                            ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imazhi i Produktit <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="flex-1 w-full">
                                    <input 
                                        type="file" 
                                        name="image" 
                                        accept="image/*"
                                        id="imageInput"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        required
                                    >
                                    <p class="mt-2 text-sm text-gray-500">
                                        Formatet e lejuara: JPG, JPEG, PNG, GIF, WEBP (Max: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB)
                                    </p>
                                </div>
                                <?php if (!empty($image)): ?>
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Preview" class="image-preview">
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button 
                                type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                            >
                                <i class="fas fa-plus-circle mr-2"></i>
                                Shto Produktin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('hidden');
        });

        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview mt-4';
                    img.alt = 'Image Preview';
                    
                    // Remove existing preview if any
                    const existingPreview = document.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Add new preview
                    const inputContainer = e.target.parentElement;
                    inputContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
