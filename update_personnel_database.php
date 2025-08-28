<?php
// Database update script for PCG CG-12 Personnel Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute SQL file
$sql_file = file_get_contents('update_personnel_database.sql');

// Split SQL file into individual queries
$queries = explode(';', $sql_file);

echo "<!DOCTYPE html>";
echo "<html><head><title>Personnel Database Update - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.warning { color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; }
.summary { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>PCG CG-12 Personnel Database Update</h1>";
echo "<p>Updating personnel structure with Officers and Non-Officers categories</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;
$total_personnel_added = 0;

foreach ($queries as $index => $query) {
    $query = trim($query);
    if (!empty($query) && !preg_match('/^--/', $query)) {
        // Extract the first few words for display
        $query_preview = substr($query, 0, 80) . (strlen($query) > 80 ? '...' : '');
        
        if ($conn->query($query) === TRUE) {
            echo "<div class='success'>";
            echo "✓ <strong>Success:</strong> " . htmlspecialchars($query_preview) . "</div>";
            $success_count++;
            
            // Count INSERT statements for personnel
            if (stripos($query, 'INSERT INTO personnel') !== false) {
                $total_personnel_added++;
            }
        } else {
            // Check if it's a duplicate entry error (which we can ignore)
            if (strpos($conn->error, 'Duplicate entry') !== false) {
                echo "<div class='warning'>";
                echo "⚠ <strong>Skipped (already exists):</strong> " . htmlspecialchars($query_preview) . "</div>";
                $success_count++;
            } else {
                echo "<div class='error'>";
                echo "✗ <strong>Error:</strong> " . htmlspecialchars($conn->error) . "<br>";
                echo "<small>Query: " . htmlspecialchars($query_preview) . "</small></div>";
                $error_count++;
            }
        }
    }
}

echo "<div class='summary'>";
echo "<h3>Personnel Database Update Summary</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";
echo "<p><strong>Total personnel added/updated:</strong> $total_personnel_added</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Personnel database update completed successfully!</strong><br>";
    echo "The personnel database has been updated with the new structure including:<br>";
    echo "• Officers and Non-Officers categories<br>";
    echo "• Enhanced personnel details (rank, lastname, firstname, unit_code, remarks)<br>";
    echo "• Sample personnel data for testing";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Update completed with some errors.</strong><br>";
    echo "Please check the errors above and resolve them if necessary.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='admin-dashboard.html' class='btn'>Go to Admin Dashboard</a>";
echo "<a href='manage_personnel.php' class='btn'>Manage Personnel</a>";
echo "<a href='index.html' class='btn'>Go to Homepage</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 