<?php
// apply_users_sql.php
// Safe helper to create/repair the `users` table using the app DB user.
// Upload to backend/ and open in a browser (e.g. https://your-site.com/backend/apply_users_sql.php).
// Delete the file after a successful run.

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php'; // uses $conn

function column_exists($conn, $col) {
    $res = $conn->query("SHOW COLUMNS FROM `users` LIKE '{$conn->real_escape_string($col)}'");
    return $res && $res->num_rows > 0;
}

function unique_index_on_column($conn, $col) {
    $col = $conn->real_escape_string($col);
    $res = $conn->query("SHOW INDEX FROM `users` WHERE Column_name = '{$col}' AND Non_unique = 0");
    return $res && $res->num_rows > 0;
}

echo "<h2>apply_users_sql.php — running</h2>";
try {
    // 1) Create table if missing
    $create_sql = "
    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `unique_id` VARCHAR(20) UNIQUE,
      `name` VARCHAR(100) NOT NULL,
      `email` VARCHAR(100) UNIQUE NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `phone` VARCHAR(20),
      `address` TEXT,
      `city` VARCHAR(50),
      `country` VARCHAR(50),
      `postal_code` VARCHAR(20),
      `role` VARCHAR(50) DEFAULT 'customer',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if ($conn->query($create_sql) === TRUE) {
        echo "<p style='color:green;'>✓ users table created/verified</p>";
    } else {
        throw new Exception('Create table failed: ' . $conn->error);
    }

    // 2) Ensure columns exist (ALTER if needed)
    $columns = [
        'unique_id' => "VARCHAR(20) UNIQUE",
        'name' => "VARCHAR(100) NOT NULL",
        'email' => "VARCHAR(100) UNIQUE NOT NULL",
        'password' => "VARCHAR(255) NOT NULL",
        'phone' => "VARCHAR(20)",
        'address' => "TEXT",
        'city' => "VARCHAR(50)",
        'country' => "VARCHAR(50)",
        'postal_code' => "VARCHAR(20)",
        'role' => "VARCHAR(50) DEFAULT 'customer'",
        'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];

    foreach ($columns as $col => $definition) {
        if (!column_exists($conn, $col)) {
            $sql = "ALTER TABLE `users` ADD COLUMN `{$col}` {$definition}";
            if ($conn->query($sql) === TRUE) {
                echo "<p>→ Added column `{$col}`</p>";
            } else {
                echo "<p style='color:orange;'>⚠ Could not add column `{$col}`: {$conn->error}</p>";
            }
        } else {
            echo "<p>→ Column `{$col}` exists</p>";
        }
    }

    // 3) Ensure unique indexes on email and unique_id
    if (!unique_index_on_column($conn, 'email')) {
        $sql = "ALTER TABLE `users` ADD UNIQUE INDEX `ux_users_email` (`email`)";
        if ($conn->query($sql) === TRUE) {
            echo "<p>→ Added unique index on `email`</p>";
        } else {
            echo "<p style='color:orange;'>⚠ Could not add unique index on `email`: {$conn->error}</p>";
        }
    } else {
        echo "<p>→ Unique index on `email` exists</p>";
    }

    if (!unique_index_on_column($conn, 'unique_id')) {
        $sql = "ALTER TABLE `users` ADD UNIQUE INDEX `ux_users_unique_id` (`unique_id`)";
        if ($conn->query($sql) === TRUE) {
            echo "<p>→ Added unique index on `unique_id`</p>";
        } else {
            echo "<p style='color:orange;'>⚠ Could not add unique index on `unique_id`: {$conn->error}</p>";
        }
    } else {
        echo "<p>→ Unique index on `unique_id` exists</p>";
    }

    // 4) Populate unique_id for rows missing it (stable per id)
    $update_sql = "UPDATE `users` SET `unique_id` = LPAD(`id`, 6, '0') WHERE `unique_id` IS NULL OR `unique_id` = ''";
    if ($conn->query($update_sql) === TRUE) {
        echo "<p style='color:green;'>✓ Populated unique_id for existing rows</p>";
    } else {
        echo "<p style='color:orange;'>⚠ Could not populate unique_id: {$conn->error}</p>";
    }

    // 5) Summary
    $res = $conn->query("SELECT COUNT(*) as c FROM `users`");
    $count = $res ? $res->fetch_assoc()['c'] : 'N/A';
    echo "<p>Users rows: {$count}</p>";

    echo "<p style='color:blue;'>Done. Please DELETE this file from the server after verifying.</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$conn->close();