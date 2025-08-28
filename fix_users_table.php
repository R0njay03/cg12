<?php
// Script to fix users table structure
include 'db.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix Users Table - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.warning { color: orange; padding: 8px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Fix Users Table Structure</h1>";
echo "<p>Adding missing columns to users table</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// Step 1: Check if users table exists
echo "<div class='info'>Step 1: Checking users table structure...</div>";

$check_table = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows == 0) {
    echo "<div class='error'>Users table doesn't exist. Creating users table...</div>";
    
    // Create users table with proper structure
    $create_table_sql = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "<div class='success'>✓ Users table created successfully</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error creating users table: " . $conn->error . "</div>";
        $error_count++;
    }
} else {
    echo "<div class='success'>✓ Users table exists</div>";
    
    // Check for missing columns
    $columns_to_check = ['created_at', 'updated_at'];
    $missing_columns = [];
    
    foreach ($columns_to_check as $column) {
        $check_column = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($check_column->num_rows == 0) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        echo "<div class='warning'>Missing columns: " . implode(', ', $missing_columns) . "</div>";
        
        // Add missing columns
        foreach ($missing_columns as $column) {
            if ($column == 'created_at') {
                $alter_query = "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            } elseif ($column == 'updated_at') {
                $alter_query = "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            }
            
            if ($conn->query($alter_query) === TRUE) {
                echo "<div class='success'>✓ Added column: $column</div>";
                $success_count++;
            } else {
                echo "<div class='error'>✗ Error adding column $column: " . $conn->error . "</div>";
                $error_count++;
            }
        }
    } else {
        echo "<div class='success'>✓ All required columns exist</div>";
    }
}

// Step 2: Ensure admin user exists
echo "<div class='info'>Step 2: Checking admin user...</div>";

$check_admin = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    echo "<div class='warning'>Admin user doesn't exist. Creating admin user...</div>";
    
    $username = 'admin';
    $password = 'admin';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';
    
    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($insert_stmt->execute()) {
        echo "<div class='success'>✓ Admin user created successfully</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error creating admin user: " . $conn->error . "</div>";
        $error_count++;
    }
} else {
    echo "<div class='success'>✓ Admin user exists</div>";
}

// Step 3: Verify table structure
echo "<div class='info'>Step 3: Verifying final table structure...</div>";

$describe_result = $conn->query("DESCRIBE users");
if ($describe_result) {
    echo "<div class='success'>✓ Users table structure:</div>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px; border: 1px solid #ddd;'>Field</th><th style='padding: 8px; border: 1px solid #ddd;'>Type</th><th style='padding: 8px; border: 1px solid #ddd;'>Null</th><th style='padding: 8px; border: 1px solid #ddd;'>Key</th><th style='padding: 8px; border: 1px solid #ddd;'>Default</th></tr>";
    
    while ($row = $describe_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['Key'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    $success_count++;
} else {
    echo "<div class='error'>✗ Error describing table: " . $conn->error . "</div>";
    $error_count++;
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Users Table Fix Complete!</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Users table fix completed successfully!</strong><br>";
    echo "The users table now has the proper structure and the manage-users.php should work correctly.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Fix completed with some errors.</strong><br>";
    echo "Please review the errors above and try again if necessary.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='manage-users.php' class='btn'>Test Manage Users (PHP)</a>";
echo "<a href='manage-users.html' class='btn'>Manage Users (HTML)</a>";
echo "<a href='admin-dashboard-fixed.php' class='btn'>Admin Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?>