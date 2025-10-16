<?php
// File: c:\xampp\htdocs\hotel-ks\dashboard\edit_product.php
$page_title = 'Ndrysho Produktin';
$current_page = 'products';
require_once 'includes/auth_check.php';
require_once 'config.php';
require_once '../backend/db.php';

$msg = '';
$error = '';

// Get product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$id = intval($_GET['id']);

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $current_image = $product['image'];

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
                // Create uploads directory if it doesn't exist
                $upload_dir = '../uploads/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generate unique filename
                $new_filename = uniqid() . '_' . time() . '.' . $filetype;
                $upload_path = $upload_dir . $new_filename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if (!empty($current_image) && file_exists('../' . $current_image)) {
                        unlink('../' . $current_image);
                    }
                    $current_image = 'uploads/products/' . $new_filename;
                } else {
                    $error = 'Gabim në ngarkimin e imazhit!';
                }
            }
        }

        // Update product if no errors
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, image=?, category=?, stock=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("sdsssii", $name, $price, $description, $current_image, $category, $stock, $id);
            
            if ($stmt->execute()) {
                $msg = 'Produkti u përditësua me sukses!';
                // Refresh product data
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Gabim në përditësimin e produktit: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel KS Dashboard</title>
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
                        <h1 class="text-2xl font-bold text-gray-800">Ndrysho Produktin</h1>
                        <p class="text-sm text-gray-500">Përditëso informacionin e produktit</p>
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
                                value="<?php echo htmlspecialchars($product['name']); ?>"
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
                                    value="<?php echo htmlspecialchars($product['price']); ?>"
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
                                    value="<?php echo htmlspecialchars($product['stock']); ?>"
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
                                <option value="shtretër" <?php echo ($product['category'] == 'shtretër') ? 'selected' : ''; ?>>Shtretër</option>
                                <option value="banjo" <?php echo ($product['category'] == 'banjo') ? 'selected' : ''; ?>>Banjo</option>
                                <option value="dhoma" <?php echo ($product['category'] == 'dhoma') ? 'selected' : ''; ?>>Dhoma</option>
                                <option value="aksesorë" <?php echo ($product['category'] == 'aksesorë') ? 'selected' : ''; ?>>Aksesorë</option>
                                <option value="Tjetër" <?php echo ($product['category'] == 'Tjetër') ? 'selected' : ''; ?>>Tjetër</option>
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
                            ><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imazhi i Produktit
                            </label>
                            
                            <!-- Current Image Preview -->
                            <?php if (!empty($product['image'])): ?>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Imazhi aktual:</p>
                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="Current product image" 
                                         class="image-preview">
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="flex-1 w-full">
                                    <input 
                                        type="file" 
                                        name="image" 
                                        accept="image/*"
                                        id="imageInput"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    >
                                    <p class="mt-2 text-sm text-gray-500">
                                        Formatet e lejuara: JPG, JPEG, PNG, GIF, WEBP (Lini bosh për të mbajtur imazhin aktual)
                                    </p>
                                </div>
                            </div>
                            
                            <!-- New Image Preview -->
                            <div id="newImagePreview" class="mt-4 hidden">
                                <p class="text-sm text-gray-600 mb-2">Pamja paraprake e imazhit të ri:</p>
                                <img id="previewImage" src="" alt="New image preview" class="image-preview">
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-4">
                            <button 
                                type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Ruaj Ndryshimet
                            </button>
                            <a 
                                href="products.php" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Anulo
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });

        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.getElementById('newImagePreview');
                    const previewImg = document.getElementById('previewImage');
                    previewImg.src = e.target.result;
                    previewDiv.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const price = parseFloat(document.querySelector('input[name="price"]').value);

            if (!name) {
                e.preventDefault();
                alert('Ju lutem shkruani emrin e produktit!');
                return false;
            }

            if (price <= 0 || isNaN(price)) {
                e.preventDefault();
                alert('Ju lutem shkruani një çmim të vlefshëm!');
                return false;
            }
        });
    </script>
</body>
</html>