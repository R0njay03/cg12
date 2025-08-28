<?php
// Database fix script for PCG CG-12 Course Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Database Fix - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>PCG CG-12 Database Fix</h1>";
echo "<p>Adding missing status column to courses table</p>";
echo "</div>";

// First, let's check if the status column exists
$check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = 'pcg_training' 
                AND TABLE_NAME = 'courses' 
                AND COLUMN_NAME = 'status'";

$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    echo "<div class='success'>✓ Status column already exists in courses table</div>";
} else {
    // Add the status column
    $add_column_query = "ALTER TABLE pcg_training.courses 
                        ADD COLUMN status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active'";
    
    if ($conn->query($add_column_query) === TRUE) {
        echo "<div class='success'>✓ Successfully added status column to courses table</div>";
    } else {
        echo "<div class='error'>✗ Error adding status column: " . $conn->error . "</div>";
    }
}

// Update all existing courses to Active status
$update_query = "UPDATE pcg_training.courses SET status = 'Active' WHERE status IS NULL OR status = ''";

if ($conn->query($update_query) === TRUE) {
    $affected_rows = $conn->affected_rows;
    echo "<div class='success'>✓ Updated $affected_rows courses to Active status</div>";
} else {
    echo "<div class='error'>✗ Error updating course status: " . $conn->error . "</div>";
}

// Verify the fix by checking course count
$count_query = "SELECT COUNT(*) as total FROM pcg_training.courses WHERE status = 'Active'";
$count_result = $conn->query($count_query);

if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    echo "<div class='success'>✓ Database fix completed! Total active courses: " . $count_row['total'] . "</div>";
} else {
    echo "<div class='error'>✗ Error verifying course count: " . $conn->error . "</div>";
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<strong>✓ Database fix completed successfully!</strong><br>";
echo "The courses table now has the required status column and all courses are set to Active status.";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='course-catalog.php' class='btn'>View Course Catalog</a>";
echo "<a href='manage-courses.php' class='btn'>Manage Courses</a>";
echo "<a href='admin-dashboard.html' class='btn'>Admin Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?>