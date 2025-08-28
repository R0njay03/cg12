<?php
// Script to add regular users to the PCG CG-12 Training System
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
echo "<html><head><title>Add Regular Users - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.warning { color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; }
.summary { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Add Regular Users</h1>";
echo "<p>PCG CG-12 Training Management System</p>";
echo "</div>";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $stmt->close();
    
    if (empty($errors)) {
        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Regular user role
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);
        
        if ($stmt->execute()) {
            echo "<div class='success'>";
            echo "✓ Regular user '$username' created successfully with 'user' role.</div>";
            echo "<div class='summary'>";
            echo "<h3>User Details:</h3>";
            echo "<p><strong>Username:</strong> $username</p>";
            echo "<p><strong>Role:</strong> $role</p>";
            echo "<p><strong>Access Level:</strong> View-only access to personnel data</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "✗ Error creating user: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='error'>";
        echo "<strong>Please fix the following errors:</strong><br>";
        foreach ($errors as $error) {
            echo "• $error<br>";
        }
        echo "</div>";
    }
}

// Display form
echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label for='username'>Username:</label>";
echo "<input type='text' id='username' name='username' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='password'>Password:</label>";
echo "<input type='password' id='password' name='password' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='confirm_password'>Confirm Password:</label>";
echo "<input type='password' id='confirm_password' name='confirm_password' required>";
echo "</div>";

echo "<button type='submit' class='btn'>Create Regular User</button>";
echo "</form>";

echo "<div class='summary'>";
echo "<h3>Role-Based Access Control</h3>";
echo "<p><strong>Admin Role:</strong> Full access - can view, edit, delete, and upload personnel data</p>";
echo "<p><strong>User Role:</strong> View-only access - can only view personnel data, no editing capabilities</p>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='admin-dashboard.html' class='btn'>Go to Admin Dashboard</a>";
echo "<a href='manage_personnel.php' class='btn'>Manage Personnel</a>";
echo "<a href='index.html' class='btn'>Go to Homepage</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 