<?php
// Quick database fix script for PCG CG-12 Course Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Quick Database Fix - PCG CG-12</title>";
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
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>PCG CG-12 Quick Database Fix</h1>";
echo "<p>Adding missing columns to existing courses table</p>";
echo "</div>";

// Select the database
$conn->select_db("pcg_training");

$success_count = 0;
$error_count = 0;

// Check if courses table exists
$check_table = $conn->query("SHOW TABLES LIKE 'courses'");
if ($check_table->num_rows == 0) {
    echo "<div class='error'>✗ Courses table doesn't exist. Please run the complete database update instead.</div>";
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='complete_database_update_fixed.php' class='btn'>Run Complete Database Update</a>";
    echo "</div>";
    echo "</div></body></html>";
    exit();
}

echo "<div class='info'>Courses table found. Adding missing columns...</div>";

// List of columns to add with their definitions
$columns_to_add = [
    "course_code VARCHAR(50) AFTER course_name",
    "training_type ENUM('Local Training', 'Foreign Training', 'Unit / Interagency Training') NOT NULL DEFAULT 'Local Training' AFTER course_code",
    "category VARCHAR(100) AFTER training_type",
    "subcategory VARCHAR(100) AFTER category",
    "target_audience ENUM('Officer', 'Non-Officer', 'Both') NOT NULL DEFAULT 'Both' AFTER subcategory",
    "course_level ENUM('Basic', 'Intermediate', 'Advanced', 'Senior') DEFAULT 'Basic' AFTER target_audience",
    "prerequisites TEXT AFTER capacity",
    "learning_objectives TEXT AFTER prerequisites",
    "course_outline TEXT AFTER learning_objectives",
    "file_attachment VARCHAR(255) AFTER course_outline",
    "status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active' AFTER file_attachment"
];

foreach ($columns_to_add as $column_def) {
    $column_name = explode(' ', $column_def)[0];
    
    // Check if column already exists
    $check_column = $conn->query("SHOW COLUMNS FROM courses LIKE '$column_name'");
    
    if ($check_column->num_rows == 0) {
        // Column doesn't exist, add it
        $alter_query = "ALTER TABLE courses ADD COLUMN $column_def";
        
        if ($conn->query($alter_query) === TRUE) {
            echo "<div class='success'>✓ Added column: $column_name</div>";
            $success_count++;
        } else {
            echo "<div class='error'>✗ Error adding column $column_name: " . $conn->error . "</div>";
            $error_count++;
        }
    } else {
        echo "<div class='warning'>⚠ Column $column_name already exists (skipped)</div>";
    }
}

// Update existing courses to have default values
echo "<div class='info'>Updating existing courses with default values...</div>";

$update_queries = [
    "UPDATE courses SET training_type = 'Local Training' WHERE training_type IS NULL OR training_type = ''",
    "UPDATE courses SET target_audience = 'Both' WHERE target_audience IS NULL OR target_audience = ''",
    "UPDATE courses SET course_level = 'Basic' WHERE course_level IS NULL OR course_level = ''",
    "UPDATE courses SET status = 'Active' WHERE status IS NULL OR status = ''"
];

foreach ($update_queries as $query) {
    if ($conn->query($query) === TRUE) {
        $affected = $conn->affected_rows;
        echo "<div class='success'>✓ Updated $affected courses with default values</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error updating courses: " . $conn->error . "</div>";
        $error_count++;
    }
}

// Add a few sample courses if the table is empty or has very few courses
$count_result = $conn->query("SELECT COUNT(*) as total FROM courses");
$count_row = $count_result->fetch_assoc();
$total_courses = $count_row['total'];

if ($total_courses < 5) {
    echo "<div class='info'>Adding sample courses...</div>";
    
    $sample_courses = [
        [
            'Advanced Navigation Course',
            'ANC-001',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Both',
            'Intermediate',
            'Advanced maritime navigation techniques and GPS systems training for Coast Guard personnel.',
            '4 weeks',
            25,
            'Basic navigation knowledge, Maritime experience',
            'Master advanced navigation techniques, Understand GPS systems, Develop route planning skills',
            'Week 1: Navigation Fundamentals, Week 2: GPS Systems, Week 3: Route Planning, Week 4: Practical Exercises',
            '',
            'Active'
        ],
        [
            'Maritime Safety Administration Course',
            'MARSAD-001',
            'Local Training',
            'Functional Courses',
            'Maritime Safety',
            'Both',
            'Intermediate',
            'Comprehensive course covering maritime safety regulations and vessel inspection procedures.',
            '6 weeks',
            30,
            'Basic maritime knowledge, Safety certification',
            'Master safety regulations, Develop inspection skills, Understand safety management',
            'Week 1-2: Safety Regulations, Week 3-4: Inspection Procedures, Week 5-6: Safety Management',
            '',
            'Active'
        ],
        [
            'International Maritime Security Course',
            'IMSC-001',
            'Foreign Training',
            'International Cooperation',
            'Maritime Security',
            'Officer',
            'Advanced',
            'International course focusing on maritime security cooperation with allied coast guards.',
            '3 weeks',
            15,
            'Security clearance, International assignment eligibility',
            'Develop international cooperation skills, Master joint operations, Understand security protocols',
            'Week 1: International Law, Week 2: Joint Operations, Week 3: Security Protocols',
            'international_maritime_security.pdf',
            'Active'
        ]
    ];
    
    $insert_sql = "INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives, course_outline, file_attachment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    
    foreach ($sample_courses as $course) {
        $stmt->bind_param("sssssssssisssss", ...$course);
        if ($stmt->execute()) {
            echo "<div class='success'>✓ Added sample course: " . htmlspecialchars($course[0]) . "</div>";
            $success_count++;
        } else {
            echo "<div class='error'>✗ Error adding sample course: " . $conn->error . "</div>";
            $error_count++;
        }
    }
}

// Create uploads directory
$upload_dir = 'uploads/courses/';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "<div class='success'>✓ Created uploads directory: $upload_dir</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to create uploads directory</div>";
        $error_count++;
    }
} else {
    echo "<div class='success'>✓ Uploads directory already exists</div>";
}

// Final verification
$final_count = $conn->query("SELECT COUNT(*) as total FROM courses");
$final_row = $final_count->fetch_assoc();

echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Quick Database Fix Complete!</h3>";
echo "<p><strong>Successful operations:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";
echo "<p><strong>Total courses in database:</strong> " . $final_row['total'] . "</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Database fix completed successfully!</strong><br>";
    echo "Your courses table now has all required columns and the course catalog should work properly.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Fix completed with some errors.</strong><br>";
    echo "Please review the errors above. The system should still be functional.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='course-catalog.php' class='btn'>View Course Catalog</a>";
echo "<a href='manage-courses.php' class='btn'>Manage Courses</a>";
echo "<a href='admin-dashboard.html' class='btn'>Admin Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

$conn->close();
?>