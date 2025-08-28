<?php
// Database initialization script
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server (without selecting a database)
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute SQL file
$sql_file = file_get_contents('setup_database.sql');

// Split SQL file into individual queries
$queries = explode(';', $sql_file);

echo "<!DOCTYPE html>";
echo "<html><head><title>Database Setup</title></head><body>";
echo "<h2>PCG CG-12 Database Initialization</h2>";
echo "<div style='font-family: Arial, sans-serif; margin: 20px; max-width: 800px;'>";

$success_count = 0;
$error_count = 0;

foreach ($queries as $index => $query) {
    $query = trim($query);
    if (!empty($query) && !preg_match('/^--/', $query)) {
        // Extract the first few words for display
        $query_preview = substr($query, 0, 50) . (strlen($query) > 50 ? '...' : '');
        
        if ($conn->query($query) === TRUE) {
            echo "<p style='color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0;'>";
            echo "✓ <strong>Success:</strong> " . htmlspecialchars($query_preview) . "</p>";
            $success_count++;
        } else {
            // Check if it's a duplicate entry error (which we can ignore)
            if (strpos($conn->error, 'Duplicate entry') !== false) {
                echo "<p style='color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0;'>";
                echo "⚠ <strong>Skipped (already exists):</strong> " . htmlspecialchars($query_preview) . "</p>";
                $success_count++;
            } else {
                echo "<p style='color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0;'>";
                echo "✗ <strong>Error:</strong> " . htmlspecialchars($conn->error) . "<br>";
                echo "<small>Query: " . htmlspecialchars($query_preview) . "</small></p>";
                $error_count++;
            }
        }
    }
}

echo "<hr style='margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Database Setup Summary</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Database setup completed successfully!</strong><br>";
    echo "You can now use the admin dashboard with full database functionality.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Setup completed with some errors.</strong><br>";
    echo "Please check the errors above and resolve them if necessary.";
    echo "</div>";
}

echo "<p style='margin-top: 20px;'>";
echo "<a href='admin-dashboard.html' style='background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a> ";
echo "<a href='login.html' style='background: #c8102e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Go to Login</a>";
echo "</p>";

echo "</div></body></html>";

$conn->close();
?>