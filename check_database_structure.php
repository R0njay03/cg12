<?php
// Script to check database structure
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
echo "<html><head><title>Database Structure Check - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.warning { color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; }
.info { color: #0c5460; padding: 5px; background: #d1ecf1; border-left: 3px solid #bee5eb; margin: 5px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f2f2f2; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Database Structure Check</h1>";
echo "<p>PCG CG-12 Training Management System</p>";
echo "</div>";

// Check if personnel table exists
$result = $conn->query("SHOW TABLES LIKE 'personnel'");
if ($result->num_rows > 0) {
    echo "<div class='success'>✓ Personnel table exists</div>";
    
    // Get table structure
    $result = $conn->query("DESCRIBE personnel");
    echo "<h3>Current Personnel Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required columns
    $required_columns = ['id', 'rank', 'lastname', 'firstname', 'unit_code', 'category', 'remarks'];
    $existing_columns = [];
    
    $result = $conn->query("DESCRIBE personnel");
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<h3>Column Check:</h3>";
    foreach ($required_columns as $column) {
        if (in_array($column, $existing_columns)) {
            echo "<div class='success'>✓ Column '$column' exists</div>";
        } else {
            echo "<div class='error'>✗ Column '$column' is missing</div>";
        }
    }
    
    // Check if table has data
    $result = $conn->query("SELECT COUNT(*) as count FROM personnel");
    $row = $result->fetch_assoc();
    echo "<div class='info'>📊 Personnel table contains " . $row['count'] . " records</div>";
    
} else {
    echo "<div class='error'>✗ Personnel table does not exist</div>";
}

// Check users table
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "<div class='success'>✓ Users table exists</div>";
    
    // Get users table structure
    $result = $conn->query("DESCRIBE users");
    echo "<h3>Users Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check users count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<div class='info'>👥 Users table contains " . $row['count'] . " users</div>";
    
    // Show user roles
    $result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    echo "<h3>User Roles:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "<div class='info'>Role '" . $row['role'] . "': " . $row['count'] . " users</div>";
    }
    
} else {
    echo "<div class='error'>✗ Users table does not exist</div>";
}

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='update_personnel_database.php' class='btn'>🔄 Update Personnel Database</a>";
echo "<a href='create_test_user.php' class='btn'>👤 Create Test User</a>";
echo "<a href='manage_personnel.php' class='btn'>👥 Manage Personnel</a>";
echo "<a href='admin-dashboard.html' class='btn'>🏠 Admin Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 