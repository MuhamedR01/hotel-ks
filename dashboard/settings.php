<?php
require_once __DIR__ . '/init.php';
require_once 'includes/auth_check.php';
require_once __DIR__ . '/../backend/init.php';

$conn = db_connect();
$current_page = 'settings';
// Only super_admin
requireRole(['super_admin']);
$success_message = '';
$error_message = '';

// Handle add admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Invalid CSRF token. Please refresh and try again.';
    } else {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']); // Optional now
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate required fields (email is now optional)
    if (empty($username) || empty($name) || empty($password) || empty($role)) {
        $error_message = 'Emri i përdoruesit, emri, fjalëkalimi dhe roli janë të detyrueshme!';
    } elseif (strlen($password) < 6) {
        $error_message = 'Fjalëkalimi duhet të jetë të paktën 6 karaktere!';
    } elseif (!in_array($role, ['super_admin', 'manager', 'worker'])) {
        $error_message = 'Roli i pavlefshëm!';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Email i pavlefshëm!';
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = 'Ky emër përdoruesi është i regjistruar tashmë!';
        } else {
            // Prepare email to insert: if empty, generate a unique fallback email
            if (empty($email)) {
                $base = preg_replace('/[^a-z0-9_.-]/', '', strtolower($username));
                $candidate = $base . '@hotelks.com';
                $i = 0;
                while (true) {
                    $check_email = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                    $check_email->bind_param("s", $candidate);
                    $check_email->execute();
                    $email_result = $check_email->get_result();
                    if ($email_result->num_rows == 0) break;
                    $i++;
                    $candidate = $base . '+' . $i . '@hotelks.com';
                }
                $emailToInsert = $candidate;
            } else {
                $emailToInsert = $email;
                // Check if provided email already exists
                $check_email = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                $check_email->bind_param("s", $emailToInsert);
                $check_email->execute();
                $email_result = $check_email->get_result();
                if ($email_result->num_rows > 0) {
                    $error_message = 'Ky email është i regjistruar tashmë!';
                }
            }

            if (empty($error_message)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Always insert email (we generate one if it was not provided)
                $insert_stmt = $conn->prepare("INSERT INTO admins (username, name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $insert_stmt->bind_param("sssss", $username, $name, $emailToInsert, $hashed_password, $role);

                if ($insert_stmt->execute()) {
                    $success_message = 'Admini u shtua me sukses!';
                } else {
                    $error_message = 'Gabim në shtimin e adminit!';
                }
            }
        }
    }
}

// Handle delete admin (require CSRF token)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $csrf = $_GET['csrf'] ?? '';
    if (!validateCSRFToken($csrf)) {
        $error_message = 'Invalid CSRF token for delete operation.';
    } else {
    
    // Prevent deleting yourself
    if ($delete_id === $_SESSION['admin_id']) {
        $error_message = 'Nuk mund të fshini llogarinë tuaj!';
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        
        if ($delete_stmt->execute()) {
            $success_message = 'Admini u fshi me sukses!';
        } else {
            $error_message = 'Gabim në fshirjen e adminit!';
        }
    }
    }
}

// Get all admins
$admins_query = $conn->query("SELECT id, username, name, email, role, created_at FROM admins ORDER BY created_at DESC");
$admins = $admins_query->fetch_all(MYSQLI_ASSOC);

}

$role_labels = [
    'super_admin' => 'Super Admin',
    'manager' => 'Menaxher',
    'worker' => 'Punëtor'
];

$role_colors = [
    'super_admin' => 'bg-purple-100 text-purple-800',
    'manager' => 'bg-blue-100 text-blue-800',
    'worker' => 'bg-green-100 text-green-800'
];

// Now include the header and sidebar
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="flex-1 ml-0 lg:ml-64 transition-all duration-300">
    <?php require_once 'includes/topbar.php'; ?>
    
    <div class="p-6">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Cilësimet</h1>
            <p class="text-gray-600 mt-2">Menaxhoni administratorët dhe rolet e tyre</p>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Admin Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-600">
                        <h3 class="text-xl font-semibold text-white">
                            <i class="fas fa-user-plus mr-2"></i>Shto Admin
                        </h3>
                    </div>
                    <form method="POST" class="p-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                        <div class="space-y-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2"></i>Emri i Përdoruesit <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="username"
                                >
                                <p class="mt-1 text-xs text-gray-500">Përdoret për kyçje</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-id-card mr-2"></i>Emri i Plotë <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Emri Mbiemri"
                                >
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2"></i>Email <span class="text-gray-400">(Opsionale)</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="email@example.com"
                                >
                                <p class="mt-1 text-xs text-gray-500">Mund të lihet bosh</p>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock mr-2"></i>Fjalëkalimi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    minlength="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Min. 6 karaktere"
                                >
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user-tag mr-2"></i>Roli <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="role"
                                    name="role"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Zgjidhni rolin</option>
                                    <option value="super_admin">Super Admin - Qasje e plotë</option>
                                    <option value="manager">Menaxher - Produktet & Porositë</option>
                                    <option value="worker">Punëtor - Vetëm Porositë</option>
                                </select>
                            </div>

                            <div class="pt-2">
                                <button
                                    type="submit"
                                    name="add_admin"
                                    class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold"
                                >
                                    <i class="fas fa-plus mr-2"></i>Shto Admin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Role Descriptions -->
                <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">
                            <i class="fas fa-info-circle mr-2"></i>Përshkrimi i Roleve
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <div class="flex items-center mb-2">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                    Super Admin
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Qasje e plotë në të gjitha funksionet: Dashboard, Produktet, Porositë, Klientët, Cilësimet
                            </p>
                        </div>

                        <div>
                            <div class="flex items-center mb-2">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    Menaxher
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Mund të menaxhojë produktet (shto, edito, fshi) dhe porositë. Nuk ka qasje në Dashboard dhe Cilësimet
                            </p>
                        </div>

                        <div>
                            <div class="flex items-center mb-2">
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    Punëtor
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Vetëm mund të shohë porositë. Nuk ka qasje në produktet, dashboard apo cilësimet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">Administratorët</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri i Përdoruesit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roli</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data e Krijimit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($admin['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $role_colors[$admin['role']]; ?>">
                                            <?php echo $role_labels[$admin['role']]; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($admin['id'] !== $_SESSION['admin_id']): ?>
                                        <a href="?delete_id=<?php echo $admin['id']; ?>&csrf=<?php echo urlencode(generateCSRFToken()); ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Jeni i sigurt që doni të fshini këtë admin?')">
                                            <i class="fas fa-trash mr-1"></i>Fshi
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
