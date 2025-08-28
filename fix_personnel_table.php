<?php
// Script to fix personnel table structure
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
echo "<html><head><title>Fix Personnel Table - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 5px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; }
.error { color: red; padding: 5px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; }
.warning { color: orange; padding: 5px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; }
.btn { background: #002147; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Fix Personnel Table Structure</h1>";
echo "<p>PCG CG-12 Training Management System</p>";
echo "</div>";

// Check current table structure
echo "<h3>Step 1: Checking current table structure...</h3>";
$result = $conn->query("SHOW TABLES LIKE 'personnel'");
if ($result->num_rows > 0) {
    echo "<div class='success'>✓ Personnel table exists</div>";
    
    // Get current structure
    $result = $conn->query("DESCRIBE personnel");
    $current_columns = [];
    while ($row = $result->fetch_assoc()) {
        $current_columns[] = $row['Field'];
    }
    
    echo "<div class='info'>Current columns: " . implode(', ', $current_columns) . "</div>";
    
    // Check if we need to update
    $required_columns = ['id', 'rank', 'lastname', 'firstname', 'unit_code', 'category', 'remarks'];
    $missing_columns = array_diff($required_columns, $current_columns);
    
    if (empty($missing_columns)) {
        echo "<div class='success'>✓ All required columns exist. Table structure is correct.</div>";
    } else {
        echo "<div class='warning'>⚠ Missing columns: " . implode(', ', $missing_columns) . "</div>";
        
        echo "<h3>Step 2: Updating table structure...</h3>";
        
        // Drop and recreate the table
        if ($conn->query("DROP TABLE IF EXISTS personnel")) {
            echo "<div class='success'>✓ Old personnel table dropped</div>";
            
            // Create new table with correct structure
            $create_table_sql = "
            CREATE TABLE personnel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rank VARCHAR(100) NOT NULL,
                lastname VARCHAR(100) NOT NULL,
                firstname VARCHAR(100) NOT NULL,
                unit_code VARCHAR(50) NOT NULL,
                category ENUM('Officer', 'Non-Officer') NOT NULL,
                remarks TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_personnel (lastname, firstname, unit_code)
            )";
            
            if ($conn->query($create_table_sql)) {
                echo "<div class='success'>✓ New personnel table created with correct structure</div>";
                
                // Insert sample data
                echo "<h3>Step 3: Inserting sample data...</h3>";
                $sample_data_sql = "
                INSERT INTO personnel (rank, lastname, firstname, unit_code, category, remarks) VALUES
                ('Commander', 'Fernandez', 'Roberto', 'CG-HQ', 'Officer', 'Headquarters Staff'),
                ('Lieutenant', 'Rodriguez', 'Carlos', 'CG-DV', 'Officer', 'District Visayas'),
                ('Lieutenant Junior Grade', 'Dela Cruz', 'Juan', 'CG-NCR', 'Officer', 'District NCR'),
                ('Lieutenant Junior Grade', 'Villanueva', 'Patricia', 'CG-DM', 'Officer', 'District Mindanao'),
                ('Chief Petty Officer', 'Torres', 'Miguel', 'CG-SD', 'Non-Officer', 'Station Davao'),
                ('Petty Officer 3', 'Santos', 'Maria', 'CG-SB', 'Non-Officer', 'Station Batangas'),
                ('Petty Officer 2', 'Reyes', 'Ana', 'CG-SC', 'Non-Officer', 'Station Cebu'),
                ('Seaman', 'Garcia', 'Jose', 'CG-SM', 'Non-Officer', 'Station Manila'),
                ('Petty Officer 1', 'Lopez', 'Antonio', 'CG-SP', 'Non-Officer', 'Station Palawan'),
                ('Seaman Apprentice', 'Martinez', 'Carmen', 'CG-SZ', 'Non-Officer', 'Station Zamboanga')
                ";
                
                if ($conn->query($sample_data_sql)) {
                    echo "<div class='success'>✓ Sample data inserted successfully</div>";
                    
                    // Verify the fix
                    $result = $conn->query("SELECT COUNT(*) as count FROM personnel");
                    $row = $result->fetch_assoc();
                    echo "<div class='success'>✓ Personnel table now contains " . $row['count'] . " records</div>";
                    
                    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<strong>✓ Personnel table structure has been fixed successfully!</strong><br>";
                    echo "The table now has the correct structure with all required columns:<br>";
                    echo "• id, rank, lastname, firstname, unit_code, category, remarks<br>";
                    echo "• Sample data has been inserted for testing<br>";
                    echo "• The manage_personnel.php page should now work correctly";
                    echo "</div>";
                    
                } else {
                    echo "<div class='error'>✗ Error inserting sample data: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='error'>✗ Error creating new table: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='error'>✗ Error dropping old table: " . $conn->error . "</div>";
        }
    }
} else {
    echo "<div class='error'>✗ Personnel table does not exist. Creating it...</div>";
    
    // Create the table
    $create_table_sql = "
    CREATE TABLE personnel (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rank VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        firstname VARCHAR(100) NOT NULL,
        unit_code VARCHAR(50) NOT NULL,
        category ENUM('Officer', 'Non-Officer') NOT NULL,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_personnel (lastname, firstname, unit_code)
    )";
    
    if ($conn->query($create_table_sql)) {
        echo "<div class='success'>✓ Personnel table created successfully</div>";
        
        // Insert sample data
        $sample_data_sql = "
        INSERT INTO personnel (rank, lastname, firstname, unit_code, category, remarks) VALUES
        ('Commander', 'Fernandez', 'Roberto', 'CG-HQ', 'Officer', 'Headquarters Staff'),
        ('Lieutenant', 'Rodriguez', 'Carlos', 'CG-DV', 'Officer', 'District Visayas'),
        ('Lieutenant Junior Grade', 'Dela Cruz', 'Juan', 'CG-NCR', 'Officer', 'District NCR'),
        ('Lieutenant Junior Grade', 'Villanueva', 'Patricia', 'CG-DM', 'Officer', 'District Mindanao'),
        ('Chief Petty Officer', 'Torres', 'Miguel', 'CG-SD', 'Non-Officer', 'Station Davao'),
        ('Petty Officer 3', 'Santos', 'Maria', 'CG-SB', 'Non-Officer', 'Station Batangas'),
        ('Petty Officer 2', 'Reyes', 'Ana', 'CG-SC', 'Non-Officer', 'Station Cebu'),
        ('Seaman', 'Garcia', 'Jose', 'CG-SM', 'Non-Officer', 'Station Manila'),
        ('Petty Officer 1', 'Lopez', 'Antonio', 'CG-SP', 'Non-Officer', 'Station Palawan'),
        ('Seaman Apprentice', 'Martinez', 'Carmen', 'CG-SZ', 'Non-Officer', 'Station Zamboanga')
        ";
        
        if ($conn->query($sample_data_sql)) {
            echo "<div class='success'>✓ Sample data inserted successfully</div>";
        } else {
            echo "<div class='error'>✗ Error inserting sample data: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='error'>✗ Error creating table: " . $conn->error . "</div>";
    }
}

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='manage_personnel.php' class='btn'>👥 Test Manage Personnel</a>";
echo "<a href='create_test_user.php' class='btn'>👤 Create Test User</a>";
echo "<a href='admin-dashboard.html' class='btn'>🏠 Admin Dashboard</a>";
echo "<a href='login.html' class='btn'>🔐 Login</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?> 