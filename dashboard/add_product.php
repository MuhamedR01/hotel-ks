<?php
require_once __DIR__ . '/init.php';
require_once 'includes/auth_check.php';
require_once __DIR__ . '/../backend/init.php';
require_once 'config.php';

$conn = db_connect();

$current_page = 'add_product';
// Only manager and super_admin can add products
requireRole(['super_admin','manager']);
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    // Prefer boolean availability when provided; otherwise fallback to numeric stock if present
    if (isset($_POST['available'])) {
        $available = ($_POST['available'] === '1') ? 1 : 0;
    } else {
        $available = isset($_POST['stock']) ? (intval($_POST['stock']) > 0 ? 1 : 0) : 1;
    }

    // Validate required fields
    if (empty($name) || $price <= 0) {
        $error = 'Emri dhe çmimi janë të detyrueshëm!';
    } else {
        // Handle multiple image uploads (up to 5)
        $images = [];
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $file_count = count($_FILES['images']['name']);
            
            if ($file_count > 5) {
                $error = 'Mund të ngarkoni maksimum 5 imazhe!';
            } else {
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['images']['error'][$i] == 0) {
                        $filename = $_FILES['images']['name'][$i];
                        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $filesize = $_FILES['images']['size'][$i];
                        $tmp_name = $_FILES['images']['tmp_name'][$i];
                        $mime_type = $_FILES['images']['type'][$i];

                        // Validate file
                        if (!in_array($filetype, $allowed)) {
                            $error = "Imazhi " . ($i + 1) . ": Formati nuk është i lejuar. Përdorni: " . implode(', ', $allowed);
                            break;
                        } elseif ($filesize > MAX_FILE_SIZE) {
                            $error = "Imazhi " . ($i + 1) . ": Është shumë i madh. Maksimumi: " . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
                            break;
                        } else {
                            // Read image file as binary data
                            $image_data = file_get_contents($tmp_name);
                            $images[] = [
                                'data' => $image_data,
                                'name' => $filename,
                                'size' => $filesize,
                                'type' => $mime_type
                            ];
                        }
                    }
                }
            }
        }

        // Insert product if no errors
        if (empty($error)) {
            // Build the SQL query dynamically based on number of images
            // Detect whether `available` column exists
            $colCheck = $conn->query("SHOW COLUMNS FROM products LIKE 'available'");
            $useAvailableCol = $colCheck && $colCheck->num_rows > 0;

            if ($useAvailableCol) {
                $columns = ['name', 'price', 'description', 'available', 'created_at'];
                $placeholders = ['?', '?', '?', '?', 'NOW()'];
                $types = 'sdsi';
                $params = [$name, $price, $description, $available];
            } else {
                $columns = ['name', 'price', 'description', 'stock', 'created_at'];
                $placeholders = ['?', '?', '?', '?', 'NOW()'];
                $types = 'sdsi';
                $params = [$name, $price, $description, ($available ? 1 : 0)];
            }
            
            // Add image columns
            for ($i = 0; $i < count($images); $i++) {
                $img_num = $i == 0 ? '' : '_' . ($i + 1);
                $columns[] = "image{$img_num}";
                $columns[] = "image{$img_num}_name";
                $columns[] = "image{$img_num}_size";
                $columns[] = "image{$img_num}_type";
                
                $placeholders[] = '?';
                $placeholders[] = '?';
                $placeholders[] = '?';
                $placeholders[] = '?';
                
                $types .= 'bsis';
                $params[] = $images[$i]['data'];
                $params[] = $images[$i]['name'];
                $params[] = $images[$i]['size'];
                $params[] = $images[$i]['type'];
            }
            
            $sql = "INSERT INTO products (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                // Bind parameters
                $bind_params = [$types];
                for ($i = 0; $i < count($params); $i++) {
                    $bind_params[] = &$params[$i];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_params);
                
                // Send blob data for each image
                if (!empty($images)) {
                    $blob_index = 4; // Start after the first 4 non-blob parameters (name, price, description, stock)
                    for ($i = 0; $i < count($images); $i++) {
                        $stmt->send_long_data($blob_index, $images[$i]['data']);
                        $blob_index += 4; // Skip to next blob parameter
                    }
                }
                
                if ($stmt->execute()) {
                    $msg = 'Produkti u shtua me sukses' . (!empty($images) ? ' me ' . count($images) . ' imazhe!' : '!');
                    // Clear form
                    $_POST = [];
                } else {
                    $error = 'Gabim në shtimin e produktit: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Gabim në përgatitjen e query: ' . $conn->error;
            }
        }
$extra = "";
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
    <title>Shto Produkt - <?php echo DASHBOARD_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .preview-item {
            position: relative;
            display: inline-block;
        }
        .image-preview {
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .remove-image:hover {
            background: #dc2626;
        }
        .image-number {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
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
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Shto Produkt të Ri</h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 hidden sm:block">Plotëso të dhënat për produktin e ri</p>
                    </div>
                </div>
                <a href="products.php" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i><span class="hidden sm:inline">Kthehu</span>
                </a>
            </div>
        </div>

        <div class="p-4 lg:p-8">
            <!-- Messages -->
            <?php if ($msg): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Emri i Produktit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="Shënoni emrin e produktit" required>
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Çmimi (€) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="0.00" required>
                        </div>

                        <!-- Availability / Stock -->
                        <div>
                            <label for="available" class="block text-sm font-medium text-gray-700 mb-1">
                                Stoku
                            </label>
                            <?php $currentAvailable = isset($_POST['available']) ? intval($_POST['available']) : (isset($_POST['stock']) ? (intval($_POST['stock']) > 0 ? 1 : 0) : 1); ?>
                            <select id="available" name="available" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1" <?php echo $currentAvailable ? 'selected' : ''; ?>>Ne stok</option>
                                <option value="0" <?php echo !$currentAvailable ? 'selected' : ''; ?>>Pa stok</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Përshkrimi
                            </label>
                            <textarea id="description" name="description" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" 
                                      placeholder="Shkruani një përshkrim të produktit"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Imazhet e Produktit <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="flex-1 w-full">
                                    <input 
                                        type="file" 
                                        name="images[]" 
                                        accept="image/*"
                                        id="imageInput"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        multiple
                                        required
                                    >
                                    <p class="mt-2 text-sm text-gray-500">
                                        Formatet e lejuara: JPG, JPEG, PNG, GIF, WEBP (Max: <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB per imazh)
                                    </p>
                                </div>
                                <div id="imagePreviews" class="preview-container"></div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="md:col-span-2 pt-4">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Shto Produktin
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('hidden');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('hidden');
            this.classList.add('hidden');
        });

        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const files = e.target.files;
            const previewContainer = document.getElementById('imagePreviews');
            previewContainer.innerHTML = '';

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        img.alt = 'Image Preview';
                        img.style.width = '150px';
                        img.style.height = '150px';
                        
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.appendChild(img);
                        
                        const removeBtn = document.createElement('div');
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '&times;';
                        removeBtn.onclick = function() {
                            previewItem.remove();
                        };
                        previewItem.appendChild(removeBtn);
                        
                        const imageNumber = document.createElement('div');
                        imageNumber.className = 'image-number';
                        imageNumber.textContent = i + 1;
                        previewItem.appendChild(imageNumber);
                        
                        previewContainer.appendChild(previewItem);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>
