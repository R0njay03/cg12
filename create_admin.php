<?php
// Simple script to create admin user
include 'db.php';

// Create users table if it doesn't exist
$create_table_sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($create_table_sql);

// Check if admin user exists
$check_admin = $conn->query("SELECT * FROM users WHERE username = 'admin'");

if ($check_admin->num_rows > 0) {
    // Update existing admin user
    $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
    $update_stmt->bind_param("s", $hashed_password);
    $update_stmt->execute();
    echo "Admin user password updated successfully!<br>";
} else {
    // Create new admin user
    $username = 'admin';
    $password = 'admin';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';
    
    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($insert_stmt->execute()) {
        echo "Admin user created successfully!<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
}

echo "<br>Login Credentials:<br>";
echo "Username: admin<br>";
echo "Password: admin<br>";
echo "<br><a href='login.html'>Go to Login Page</a>";

$conn->close();
?>