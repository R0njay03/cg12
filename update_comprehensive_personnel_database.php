<?php
// Comprehensive database update script for PCG CG-12 Personnel Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read and execute SQL file
$sql_file = file_get_contents('update_comprehensive_personnel_database.sql');

// Split SQL file into individual queries
$queries = explode(';', $sql_file);

echo "<!DOCTYPE html>";
echo "<html><head><title>Comprehensive Personnel Database Update - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.warning { color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; }
.summary { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
.feature-list { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
.feature-list h4 { color: #002147; margin-bottom: 10px; }
.feature-list ul { margin: 0; padding-left: 20px; }
.feature-list li { margin: 5px 0; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Comprehensive Personnel Database Update</h1>";
echo "<p>PCG CG-12 Training Management System - Enhanced Personnel Structure</p>";
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
echo "<h3>Comprehensive Personnel Database Update Summary</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";
echo "<p><strong>Total personnel added/updated:</strong> $total_personnel_added</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Comprehensive personnel database update completed successfully!</strong><br>";
    echo "The personnel database has been updated with the enhanced structure including:<br>";
    echo "• Comprehensive fields for both Officers and Non-Officers<br>";
    echo "• Detailed training and career progression tracking<br>";
    echo "• File upload capabilities<br>";
    echo "• Enhanced role-based access control";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Update completed with some errors.</strong><br>";
    echo "Please check the errors above and resolve them if necessary.";
    echo "</div>";
}

echo "</div>";

// Display new features
echo "<div class='feature-list'>";
echo "<h4>🎯 New Personnel Management Features:</h4>";
echo "<ul>";
echo "<li><strong>Comprehensive Data Fields:</strong> RANK, Last Name, First Name, MI, Serial Number, Unit Code, Sub-Unit</li>";
echo "<li><strong>Non-Officer Specific:</strong> CGMC/CGNOC Class, Specialization, Functional Courses, BLMC/ALMC/CGNOAC, CGNOSEC, Career Dates</li>";
echo "<li><strong>Officer Specific:</strong> CGOC Class, CGSCC Class, CGSC Class, CGEC Class, Third Level Career Course</li>";
echo "<li><strong>Common Features:</strong> Seminars/Workshops, File Upload, Remarks</li>";
echo "<li><strong>Role-Based Access:</strong> Admins can edit all fields, Regular users can only edit Unit Code and Rank</li>";
echo "</ul>";
echo "</div>";

echo "<div class='feature-list'>";
echo "<h4>🔐 Access Control:</h4>";
echo "<ul>";
echo "<li><strong>Administrators:</strong> Full access to view, edit, delete, and upload all personnel data</li>";
echo "<li><strong>Regular Users:</strong> View-only access with ability to edit only Unit Code and Rank</li>";
echo "<li><strong>File Management:</strong> Upload and manage personnel documents and certificates</li>";
echo "<li><strong>Data Validation:</strong> Comprehensive validation for all personnel fields</li>";
echo "</ul>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='admin-dashboard-enhanced.php' class='btn'>Go to Admin Dashboard</a>";
echo "<a href='manage_personnel.php' class='btn'>Manage Personnel</a>";
echo "<a href='create_test_user.php' class='btn'>Create Test User</a>";
echo "<a href='index.html' class='btn'>Go to Homepage</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 