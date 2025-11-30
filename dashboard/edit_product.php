<?php
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
    exit;
}

$id = intval($_GET['id']);

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();

// Helper function to get image source
function getImageSrc($image_data, $mime_type = 'image/jpeg') {
    if (empty($image_data)) {
        return 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';
    }
    return 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);

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

        // Update product if no errors
        if (empty($error)) {
            // Build the SQL query dynamically
            $columns = ['name=?', 'price=?', 'description=?', 'stock=?', 'updated_at=NOW()'];
            $types = 'sdsi';
            $params = [$name, $price, $description, $stock];
            
            // Add image columns if new images are uploaded
            if (!empty($images)) {
                for ($i = 0; $i < count($images); $i++) {
                    $img_num = $i == 0 ? '' : '_' . ($i + 1);
                    $columns[] = "image{$img_num}=?";
                    $columns[] = "image{$img_num}_name=?";
                    $columns[] = "image{$img_num}_size=?";
                    $columns[] = "image{$img_num}_type=?";
                    
                    $types .= 'bsis';
                    $params[] = $images[$i]['data'];
                    $params[] = $images[$i]['name'];
                    $params[] = $images[$i]['size'];
                    $params[] = $images[$i]['type'];
                }
            }
            
            $params[] = $id;
            $types .= 'i';
            
            $sql = "UPDATE products SET " . implode(', ', $columns) . " WHERE id=?";
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
                    $blob_index = 4; // Start after the first 4 non-blob parameters
                    for ($i = 0; $i < count($images); $i++) {
                        $stmt->send_long_data($blob_index, $images[$i]['data']);
                        $blob_index += 4; // Skip to next blob parameter
                    }
                }
                
                if ($stmt->execute()) {
                    $msg = 'Produkti u përditësua me sukses' . (!empty($images) ? ' me ' . count($images) . ' imazhe të reja!' : '!');
                    // Refresh product data
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $product = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = 'Gabim në përditësimin e produktit: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Gabim në përgatitjen e query: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo DASHBOARD_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .image-preview {
            position: relative;
            display: inline-block;
        }
        .image-preview img {
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
            font-size: 12px;
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
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800"><?php echo $page_title; ?></h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 hidden sm:block">Përditëso informacionet e produktit</p>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Emri i Produktit *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Çmimi *</label>
                            <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stoku</label>
                            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Përshkrimi</label>
                            <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <!-- Images -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imazhet</label>
                            <div class="mb-3">
                                <input type="file" id="images" name="images[]" multiple accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Ngarko deri në 5 imazhe (jpg, jpeg, png, gif, webp)</p>
                            </div>
                            
                            <!-- Current Images Preview -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                <?php
                                for ($i = 0; $i < 5; $i++) {
                                    $img_field = 'image' . ($i == 0 ? '' : '_' . ($i + 1));
                                    $img_name_field = 'image' . ($i == 0 ? '' : '_' . ($i + 1)) . '_name';
                                    $img_size_field = 'image' . ($i == 0 ? '' : '_' . ($i + 1)) . '_size';
                                    $img_type_field = 'image' . ($i == 0 ? '' : '_' . ($i + 1)) . '_type';
                                    
                                    if (!empty($product[$img_field])) {
                                        $img_src = getImageSrc($product[$img_field], $product[$img_type_field] ?? 'image/jpeg');
                                        echo '<div class="image-preview">';
                                        echo '<img src="' . $img_src . '" alt="Preview" class="w-full h-32 object-cover rounded-md border border-gray-200">';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="products.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Anulo
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Përditëso Produktin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>
</html>
