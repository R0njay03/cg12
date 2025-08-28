<?php
// Script to add admin user to the database
include 'db.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Add Admin User - PCG CG-12</title>";
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
.user-info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 15px 0; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Add Admin User</h1>";
echo "<p>Creating admin user for PCG CG-12 system</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// Step 1: Check if users table exists
echo "<div class='info'>Step 1: Checking if users table exists...</div>";

$check_table = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows == 0) {
    echo "<div class='warning'>Users table doesn't exist. Creating users table...</div>";
    
    // Create users table
    $create_table_sql = "
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "<div class='success'>✓ Users table created successfully</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error creating users table: " . $conn->error . "</div>";
        $error_count++;
    }
} else {
    echo "<div class='success'>✓ Users table already exists</div>";
}

// Step 2: Check if admin user already exists
echo "<div class='info'>Step 2: Checking if admin user already exists...</div>";

$check_admin = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($check_admin->num_rows > 0) {
    echo "<div class='warning'>Admin user already exists. Updating password...</div>";
    
    // Update existing admin user password
    $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
    $update_stmt->bind_param("s", $hashed_password);
    
    if ($update_stmt->execute()) {
        echo "<div class='success'>✓ Admin user password updated successfully</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error updating admin user: " . $conn->error . "</div>";
        $error_count++;
    }
} else {
    echo "<div class='info'>Admin user doesn't exist. Creating new admin user...</div>";
    
    // Create new admin user
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
}

// Step 3: Verify admin user
echo "<div class='info'>Step 3: Verifying admin user...</div>";

$verify_admin = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($verify_admin->num_rows > 0) {
    $admin_user = $verify_admin->fetch_assoc();
    echo "<div class='success'>✓ Admin user verified successfully</div>";
    
    echo "<div class='user-info'>";
    echo "<h3>Admin User Details:</h3>";
    echo "<p><strong>ID:</strong> " . $admin_user['id'] . "</p>";
    echo "<p><strong>Username:</strong> " . $admin_user['username'] . "</p>";
    echo "<p><strong>Role:</strong> " . $admin_user['role'] . "</p>";
    echo "<p><strong>Created:</strong> " . $admin_user['created_at'] . "</p>";
    echo "<p><strong>Password:</strong> admin (hashed in database)</p>";
    echo "</div>";
    
    $success_count++;
} else {
    echo "<div class='error'>✗ Failed to verify admin user</div>";
    $error_count++;
}

// Step 4: Test login credentials
echo "<div class='info'>Step 4: Testing login credentials...</div>";

$test_password = 'admin';
if ($verify_admin->num_rows > 0) {
    $admin_user = $verify_admin->fetch_assoc();
    if (password_verify($test_password, $admin_user['password'])) {
        echo "<div class='success'>✓ Login credentials test passed</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Login credentials test failed</div>";
        $error_count++;
    }
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Admin User Setup Complete!</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Admin user setup completed successfully!</strong><br>";
    echo "You can now login with the following credentials:<br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin<br>";
    echo "<strong>Role:</strong> Administrator";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Setup completed with some errors.</strong><br>";
    echo "Please review the errors above and try again if necessary.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='login.html' class='btn'>Go to Login Page</a>";
echo "<a href='admin-dashboard-fixed.php' class='btn'>Admin Dashboard</a>";
echo "<a href='manage-users.php' class='btn'>Manage Users</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?>