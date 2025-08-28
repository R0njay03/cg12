<?php
// Script to create a test regular user for PCG CG-12 Training System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use the database
$conn->select_db("pcg_training");

echo "<!DOCTYPE html>";
echo "<html><head><title>Create Test User - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 15px 0; }
.error { color: red; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 15px 0; }
.info { color: #0c5460; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 15px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Create Test User</h1>";
echo "<p>PCG CG-12 Training Management System</p>";
echo "</div>";

// Check if test user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='info'>";
    echo "<h3>Test User Already Exists</h3>";
    echo "<p>A test user with username 'testuser' already exists in the system.</p>";
    echo "</div>";
} else {
    // Create test user
    $username = 'testuser';
    $password = 'test123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<div class='success'>";
        echo "<h3>✓ Test User Created Successfully!</h3>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Role:</strong> $role</p>";
        echo "<p><strong>Access Level:</strong> View-only access to personnel data</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>✗ Error Creating Test User</h3>";
        echo "<p>Error: " . $conn->error . "</p>";
        echo "</div>";
    }
    $stmt->close();
}

echo "<div class='info'>";
echo "<h3>Role-Based Access Control</h3>";
echo "<p><strong>Admin Role:</strong> Full access - can view, edit, delete, and upload personnel data</p>";
echo "<p><strong>User Role:</strong> View-only access - can only view personnel data, no editing capabilities</p>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='login.html' class='btn'>🔐 Go to Login</a>";
echo "<a href='add_regular_user.php' class='btn'>👤 Add More Users</a>";
echo "<a href='manage_personnel.php' class='btn'>👥 Manage Personnel</a>";
echo "<a href='admin-dashboard.html' class='btn'>🏠 Admin Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 