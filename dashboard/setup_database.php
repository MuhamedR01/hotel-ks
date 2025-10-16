<?php
require_once '../backend/db.php';

echo "<h2>Database Setup</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    table { border-collapse: collapse; margin: 20px 0; }
    table td, table th { border: 1px solid #ddd; padding: 8px; }
    table th { background-color: #f2f2f2; }
</style>";

// Create users table
$users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($users_table)) {
    echo "<p class='success'>✓ Users table created/verified</p>";
} else {
    echo "<p class='error'>✗ Error with users table: " . $conn->error . "</p>";
}

// Create products table
$products_table = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($products_table)) {
    echo "<p class='success'>✓ Products table created/verified</p>";
} else {
    echo "<p class='error'>✗ Error with products table: " . $conn->error . "</p>";
}

// Create orders table
$orders_table = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50),
    shipping_country VARCHAR(50),
    shipping_postal_code VARCHAR(20),
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($orders_table)) {
    echo "<p class='success'>✓ Orders table created/verified</p>";
} else {
    echo "<p class='error'>✗ Error with orders table: " . $conn->error . "</p>";
}

// Check if orders table has all required columns
$check_columns = $conn->query("SHOW COLUMNS FROM orders");
$existing_columns = [];
while ($col = $check_columns->fetch_assoc()) {
    $existing_columns[] = $col['Field'];
}

// Add missing columns to orders table
$required_columns = [
    'order_number' => "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) UNIQUE NOT NULL AFTER id",
    'customer_name' => "ALTER TABLE orders ADD COLUMN customer_name VARCHAR(100) NOT NULL AFTER user_id",
    'customer_email' => "ALTER TABLE orders ADD COLUMN customer_email VARCHAR(100) NOT NULL AFTER customer_name",
    'customer_phone' => "ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) AFTER customer_email",
    'shipping_address' => "ALTER TABLE orders ADD COLUMN shipping_address TEXT NOT NULL AFTER customer_phone",
    'shipping_city' => "ALTER TABLE orders ADD COLUMN shipping_city VARCHAR(50) AFTER shipping_address",
    'shipping_country' => "ALTER TABLE orders ADD COLUMN shipping_country VARCHAR(50) AFTER shipping_city",
    'shipping_postal_code' => "ALTER TABLE orders ADD COLUMN shipping_postal_code VARCHAR(20) AFTER shipping_country",
    'total_amount' => "ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10,2) NOT NULL AFTER shipping_postal_code",
    'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER status",
    'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER payment_method",
    'notes' => "ALTER TABLE orders ADD COLUMN notes TEXT AFTER payment_status"
];

foreach ($required_columns as $column => $sql) {
    if (!in_array($column, $existing_columns)) {
        if ($conn->query($sql)) {
            echo "<p class='info'>→ Added column '$column' to orders table</p>";
        } else {
            echo "<p class='error'>✗ Error adding column '$column': " . $conn->error . "</p>";
        }
    }
}

// Create order_items table
$order_items_table = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
)";

if ($conn->query($order_items_table)) {
    echo "<p class='success'>✓ Order items table created/verified</p>";
} else {
    echo "<p class='error'>✗ Error with order_items table: " . $conn->error . "</p>";
}

// Create admins table
$admins_table = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL DEFAULT 'Administrator',
    email VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if ($conn->query($admins_table)) {
    echo "<p class='success'>✓ Admins table created/verified</p>";
} else {
    echo "<p class='error'>✗ Error with admins table: " . $conn->error . "</p>";
}

// Insert sample data if tables are empty
$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
if ($product_count == 0) {
    echo "<h3>Adding Sample Products...</h3>";
    
    $sample_products = [
        ['Çarçafë Premium Pambuku', 'Çarçafë luksoze 100% pambuk egjiptian', 89.99, 'shtretër', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800', 50],
        ['Set Peshqirësh Cilësi Hoteli', 'Set peshqirësh me cilësi profesionale', 49.99, 'banjo', 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=800', 30],
        ['Jastëk Memory Foam', 'Jastëk ergonomik memory foam', 39.99, 'shtretër', 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=800', 75],
        ['Batanije Leshi', 'Batanije e butë dhe e ngrohtë', 69.99, 'shtretër', 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800', 40],
        ['Peshqir Plazhi', 'Peshqir i madh për plazh', 29.99, 'banjo', 'https://images.unsplash.com/photo-1582735689369-4fe89db7114c?w=800', 60]
    ];
    
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($sample_products as $product) {
        $stmt->bind_param("ssdssi", $product[0], $product[1], $product[2], $product[3], $product[4], $product[5]);
        if ($stmt->execute()) {
            echo "<p class='info'>→ Added product: {$product[0]}</p>";
        }
    }
}

echo "<h3>Database Summary:</h3>";
echo "<table>";
echo "<tr><th>Table</th><th>Records</th></tr>";

$tables = ['users', 'products', 'orders', 'order_items', 'admins'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<tr><td>$table</td><td>$count</td></tr>";
    }
}
echo "</table>";

echo "<p style='margin-top: 30px;'><a href='index.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";

$conn->close();
?>