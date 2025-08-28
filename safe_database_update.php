<?php
// Safe database update script for PCG CG-12 Course Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Safe Database Update - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.warning { color: orange; padding: 8px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.summary { background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
.step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #002147; }
.step h3 { color: #002147; margin-top: 0; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>PCG CG-12 Safe Database Update</h1>";
echo "<p>Safely updating database structure while preserving data integrity</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// Step 1: Check current database structure
echo "<div class='step'>";
echo "<h3>Step 1: Analyzing Current Database Structure</h3>";

$check_db = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'pcg_training'");
if ($check_db->num_rows == 0) {
    echo "<div class='info'>Creating pcg_training database...</div>";
    if ($conn->query("CREATE DATABASE pcg_training") === TRUE) {
        echo "<div class='success'>✓ Database 'pcg_training' created successfully</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error creating database: " . $conn->error . "</div>";
        $error_count++;
    }
}

$conn->select_db("pcg_training");

// Check if courses table exists
$check_table = $conn->query("SHOW TABLES LIKE 'courses'");
if ($check_table->num_rows == 0) {
    echo "<div class='info'>Courses table doesn't exist, will create new structure</div>";
    $table_exists = false;
} else {
    echo "<div class='info'>Courses table exists, will update structure safely</div>";
    $table_exists = true;
    
    // Check for foreign key constraints
    $fk_query = "SELECT 
        CONSTRAINT_NAME, 
        TABLE_NAME, 
        COLUMN_NAME, 
        REFERENCED_TABLE_NAME, 
        REFERENCED_COLUMN_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'courses' 
        AND TABLE_SCHEMA = 'pcg_training'";
    
    $fk_result = $conn->query($fk_query);
    if ($fk_result && $fk_result->num_rows > 0) {
        echo "<div class='warning'>⚠ Found foreign key constraints referencing courses table</div>";
        while ($fk = $fk_result->fetch_assoc()) {
            echo "<div class='info'>Foreign key: {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} → courses.{$fk['REFERENCED_COLUMN_NAME']}</div>";
        }
    } else {
        echo "<div class='success'>✓ No foreign key constraints found</div>";
    }
}
echo "</div>";

// Step 2: Safely update table structure
echo "<div class='step'>";
echo "<h3>Step 2: Safely Updating Table Structure</h3>";

if (!$table_exists) {
    // Create new table with complete structure
    $create_table_sql = "
    CREATE TABLE courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(255) NOT NULL,
        course_code VARCHAR(50),
        training_type ENUM('Local Training', 'Foreign Training', 'Unit / Interagency Training') NOT NULL DEFAULT 'Local Training',
        category VARCHAR(100),
        subcategory VARCHAR(100),
        target_audience ENUM('Officer', 'Non-Officer', 'Both') NOT NULL DEFAULT 'Both',
        course_level ENUM('Basic', 'Intermediate', 'Advanced', 'Senior') DEFAULT 'Basic',
        description TEXT,
        duration VARCHAR(100),
        capacity INT DEFAULT 30,
        prerequisites TEXT,
        learning_objectives TEXT,
        course_outline TEXT,
        file_attachment VARCHAR(255),
        status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql) === TRUE) {
        echo "<div class='success'>✓ Created courses table with complete structure</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Error creating courses table: " . $conn->error . "</div>";
        $error_count++;
    }
} else {
    // Safely add missing columns to existing table
    echo "<div class='info'>Adding missing columns to existing table...</div>";
    
    // List of columns to check and add
    $columns_to_add = [
        ['name' => 'course_code', 'definition' => 'VARCHAR(50)', 'position' => 'AFTER course_name'],
        ['name' => 'training_type', 'definition' => "ENUM('Local Training', 'Foreign Training', 'Unit / Interagency Training') NOT NULL DEFAULT 'Local Training'", 'position' => 'AFTER course_code'],
        ['name' => 'category', 'definition' => 'VARCHAR(100)', 'position' => 'AFTER training_type'],
        ['name' => 'subcategory', 'definition' => 'VARCHAR(100)', 'position' => 'AFTER category'],
        ['name' => 'target_audience', 'definition' => "ENUM('Officer', 'Non-Officer', 'Both') NOT NULL DEFAULT 'Both'", 'position' => 'AFTER subcategory'],
        ['name' => 'course_level', 'definition' => "ENUM('Basic', 'Intermediate', 'Advanced', 'Senior') DEFAULT 'Basic'", 'position' => 'AFTER target_audience'],
        ['name' => 'prerequisites', 'definition' => 'TEXT', 'position' => 'AFTER capacity'],
        ['name' => 'learning_objectives', 'definition' => 'TEXT', 'position' => 'AFTER prerequisites'],
        ['name' => 'course_outline', 'definition' => 'TEXT', 'position' => 'AFTER learning_objectives'],
        ['name' => 'file_attachment', 'definition' => 'VARCHAR(255)', 'position' => 'AFTER course_outline'],
        ['name' => 'status', 'definition' => "ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active'", 'position' => 'AFTER file_attachment']
    ];
    
    foreach ($columns_to_add as $column) {
        // Check if column exists
        $check_column = $conn->query("SHOW COLUMNS FROM courses LIKE '{$column['name']}'");
        
        if ($check_column->num_rows == 0) {
            // Column doesn't exist, add it
            $alter_query = "ALTER TABLE courses ADD COLUMN {$column['name']} {$column['definition']} {$column['position']}";
            
            if ($conn->query($alter_query) === TRUE) {
                echo "<div class='success'>✓ Added column: {$column['name']}</div>";
                $success_count++;
            } else {
                echo "<div class='error'>✗ Error adding column {$column['name']}: " . $conn->error . "</div>";
                $error_count++;
            }
        } else {
            echo "<div class='warning'>⚠ Column {$column['name']} already exists (skipped)</div>";
        }
    }
    
    // Update existing records with default values
    echo "<div class='info'>Updating existing records with default values...</div>";
    
    $update_queries = [
        "UPDATE courses SET training_type = 'Local Training' WHERE training_type IS NULL OR training_type = ''",
        "UPDATE courses SET target_audience = 'Both' WHERE target_audience IS NULL OR target_audience = ''",
        "UPDATE courses SET course_level = 'Basic' WHERE course_level IS NULL OR course_level = ''",
        "UPDATE courses SET status = 'Active' WHERE status IS NULL OR status = ''"
    ];
    
    foreach ($update_queries as $query) {
        if ($conn->query($query) === TRUE) {
            $affected = $conn->affected_rows;
            if ($affected > 0) {
                echo "<div class='success'>✓ Updated $affected existing records</div>";
            }
            $success_count++;
        } else {
            echo "<div class='error'>✗ Error updating records: " . $conn->error . "</div>";
            $error_count++;
        }
    }
}

echo "</div>";

// Step 3: Add sample courses if needed
echo "<div class='step'>";
echo "<h3>Step 3: Adding Sample Course Data</h3>";

// Check current course count
$count_result = $conn->query("SELECT COUNT(*) as total FROM courses");
$count_row = $count_result->fetch_assoc();
$current_courses = $count_row['total'];

echo "<div class='info'>Current courses in database: $current_courses</div>";

if ($current_courses < 10) {
    echo "<div class='info'>Adding comprehensive sample courses...</div>";
    
    // Prepare the insert statement
    $insert_sql = "INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives, course_outline, file_attachment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    $courses_added = 0;
    
    // Comprehensive course data
    $courses = [
        // LOCAL TRAINING - CAREER COURSES
        [
            'Operations Specialization Rating Course',
            'OSRC-001',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Non-Officer',
            'Intermediate',
            'Comprehensive training program designed to develop operational expertise in Coast Guard personnel, covering maritime operations, search and rescue procedures, and operational planning.',
            '8 weeks',
            25,
            'Minimum 2 years service, Basic seamanship certification',
            'Develop advanced operational skills, Master SAR procedures, Understand operational planning and execution',
            'Week 1-2: Basic Operations, Week 3-4: SAR Procedures, Week 5-6: Operational Planning, Week 7-8: Practical Exercises',
            '',
            'Active'
        ],
        [
            'Boatswains Mate Rating Course',
            'BMRC-002',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Non-Officer',
            'Intermediate',
            'Specialized training for boatswains mates covering seamanship, boat handling, deck operations, and crew supervision responsibilities.',
            '6 weeks',
            20,
            'Basic seamanship course completion, Physical fitness standards',
            'Master boat handling techniques, Develop leadership skills, Understand deck operations and maintenance',
            'Week 1-2: Seamanship Fundamentals, Week 3-4: Boat Handling, Week 5-6: Leadership and Supervision',
            '',
            'Active'
        ],
        [
            'Machinery Technician Rating Course',
            'MTRC-003',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Non-Officer',
            'Intermediate',
            'Technical training program for machinery technicians covering marine engines, mechanical systems, troubleshooting, and maintenance procedures.',
            '10 weeks',
            15,
            'Basic mechanical aptitude, Technical school background preferred',
            'Master marine engine operations, Develop troubleshooting skills, Understand preventive maintenance procedures',
            'Week 1-3: Engine Fundamentals, Week 4-6: Troubleshooting, Week 7-8: Maintenance, Week 9-10: Practical Applications',
            '',
            'Active'
        ],
        [
            'Electricians Mate Rating Course',
            'EMRC-004',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Non-Officer',
            'Intermediate',
            'Electrical systems training covering marine electrical systems, electronics, power distribution, and electrical safety procedures.',
            '8 weeks',
            18,
            'Basic electrical knowledge, Safety certification',
            'Master electrical system operations, Develop diagnostic skills, Understand electrical safety protocols',
            'Week 1-2: Electrical Fundamentals, Week 3-4: Marine Systems, Week 5-6: Diagnostics, Week 7-8: Safety Procedures',
            '',
            'Active'
        ],
        [
            'Damage Control Rating Course',
            'DCRC-005',
            'Local Training',
            'Career Course',
            'Specialization Courses',
            'Non-Officer',
            'Intermediate',
            'Critical training in damage control procedures, firefighting, flooding control, and emergency response aboard vessels.',
            '4 weeks',
            30,
            'Basic firefighting certification, Physical fitness requirements',
            'Master damage control procedures, Develop emergency response skills, Understand vessel safety systems',
            'Week 1: Firefighting, Week 2: Flooding Control, Week 3: Emergency Response, Week 4: Practical Exercises',
            '',
            'Active'
        ],
        // LOCAL TRAINING - FUNCTIONAL COURSES
        [
            'Maritime Safety Administration Course (MARSAD)',
            'MARSAD-021',
            'Local Training',
            'Functional Courses',
            'Maritime Safety',
            'Both',
            'Intermediate',
            'Comprehensive course covering maritime safety regulations, vessel inspection procedures, safety management systems, and accident investigation.',
            '6 weeks',
            25,
            'Basic maritime knowledge, Safety certification',
            'Master safety regulations, Develop inspection skills, Understand safety management systems',
            'Week 1-2: Safety Regulations, Week 3-4: Inspection Procedures, Week 5-6: Accident Investigation',
            '',
            'Active'
        ],
        [
            'Marine Environment Protection Course (MAREP)',
            'MAREP-022',
            'Local Training',
            'Functional Courses',
            'Environmental Protection',
            'Both',
            'Intermediate',
            'Environmental protection course covering marine pollution prevention, response procedures, environmental law, and ecosystem protection.',
            '4 weeks',
            30,
            'Environmental awareness, Basic science knowledge',
            'Master pollution prevention, Develop response capabilities, Understand environmental law',
            'Week 1: Environmental Law, Week 2: Pollution Prevention, Week 3-4: Response Procedures',
            '',
            'Active'
        ],
        [
            'Maritime Security Law Enforcement Course (MARSEC)',
            'MARSEC-023',
            'Local Training',
            'Functional Courses',
            'Security and Law Enforcement',
            'Both',
            'Advanced',
            'Law enforcement training covering maritime security, boarding procedures, law enforcement techniques, and legal authorities.',
            '8 weeks',
            20,
            'Law enforcement background, Security clearance',
            'Master security procedures, Develop enforcement skills, Understand legal authorities',
            'Week 1-2: Legal Framework, Week 3-4: Boarding Procedures, Week 5-6: Security Operations, Week 7-8: Practical Training',
            '',
            'Active'
        ],
        // LOCAL TRAINING - SENIOR LEVEL COURSES
        [
            'Coast Guard Non-Officer Advanced Course (CGNOAC)',
            'CGNOAC-024',
            'Local Training',
            'Senior Level Courses',
            'Advanced Leadership',
            'Non-Officer',
            'Advanced',
            'Advanced leadership course for senior non-commissioned officers covering strategic thinking, advanced leadership, and organizational management.',
            '10 weeks',
            15,
            'Minimum 10 years service, Leadership experience, Supervisory role',
            'Develop strategic thinking, Master advanced leadership, Understand organizational management',
            'Week 1-3: Strategic Thinking, Week 4-6: Advanced Leadership, Week 7-8: Management, Week 9-10: Capstone Project',
            '',
            'Active'
        ],
        // LOCAL TRAINING - OFFICER COURSES
        [
            'Coast Guard Officer Course (Basic Course)',
            'CGOC-026',
            'Local Training',
            'Officer Courses',
            'Basic Officer Training',
            'Officer',
            'Basic',
            'Foundational course for new Coast Guard officers covering leadership fundamentals, maritime law, navigation, and basic command responsibilities.',
            '16 weeks',
            30,
            'Officer commission, College degree, Physical fitness standards',
            'Develop basic leadership skills, Master maritime fundamentals, Understand command responsibilities',
            'Week 1-4: Leadership Fundamentals, Week 5-8: Maritime Law, Week 9-12: Navigation, Week 13-16: Command Training',
            '',
            'Active'
        ],
        // FOREIGN TRAINING
        [
            'International Maritime Security Course',
            'IMSC-030',
            'Foreign Training',
            'International Cooperation',
            'Maritime Security',
            'Both',
            'Advanced',
            'International course conducted in partnership with allied coast guards focusing on maritime security cooperation and joint operations.',
            '4 weeks',
            10,
            'Security clearance, International assignment eligibility',
            'Develop international cooperation skills, Master joint operations, Understand maritime security protocols',
            'Week 1: International Law, Week 2: Joint Operations, Week 3: Security Protocols, Week 4: Practical Exercises',
            'foreign_training_maritime_security.pdf',
            'Active'
        ],
        // UNIT / INTERAGENCY TRAINING
        [
            'Joint Interagency Task Force Training',
            'JIATF-031',
            'Unit / Interagency Training',
            'Joint Operations',
            'Multi-Agency Training',
            'Both',
            'Advanced',
            'Multi-agency training exercise focusing on coordination between Coast Guard, Navy, and other agencies in joint operations.',
            '2 weeks',
            50,
            'Multi-agency clearance, Joint operations experience',
            'Master interagency coordination, Develop joint operations skills, Understand multi-agency protocols',
            'Week 1: Coordination Procedures, Week 2: Joint Exercise',
            'interagency_joint_ops.pdf',
            'Active'
        ]
    ];
    
    foreach ($courses as $course) {
        $stmt->bind_param("sssssssssisssss", 
            $course[0],  // course_name
            $course[1],  // course_code
            $course[2],  // training_type
            $course[3],  // category
            $course[4],  // subcategory
            $course[5],  // target_audience
            $course[6],  // course_level
            $course[7],  // description
            $course[8],  // duration
            $course[9],  // capacity
            $course[10], // prerequisites
            $course[11], // learning_objectives
            $course[12], // course_outline
            $course[13], // file_attachment
            $course[14]  // status
        );
        
        if ($stmt->execute()) {
            $courses_added++;
            echo "<div class='success'>✓ Added: " . htmlspecialchars($course[0]) . "</div>";
        } else {
            echo "<div class='error'>✗ Error inserting course: " . htmlspecialchars($course[0]) . " - " . $conn->error . "</div>";
            $error_count++;
        }
    }
    
    $success_count += $courses_added;
} else {
    echo "<div class='success'>✓ Sufficient courses already exist in database</div>";
}

echo "</div>";

// Step 4: Create uploads directory
echo "<div class='step'>";
echo "<h3>Step 4: Setting Up File Upload Directory</h3>";

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

echo "</div>";

// Final verification
$final_count = $conn->query("SELECT COUNT(*) as total FROM courses");
$final_row = $final_count->fetch_assoc();

// Summary
echo "<div class='summary'>";
echo "<h3 style='color: #002147;'>Safe Database Update Complete!</h3>";
echo "<p><strong>Total successful operations:</strong> $success_count</p>";
echo "<p><strong>Total errors:</strong> $error_count</p>";
echo "<p><strong>Total courses in database:</strong> " . $final_row['total'] . "</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Database update completed successfully!</strong><br>";
    echo "The PCG CG-12 course management system is now ready with:<br>";
    echo "• Complete course database structure<br>";
    echo "• Comprehensive course catalog with all training types<br>";
    echo "• File upload system for course materials<br>";
    echo "• Full course management capabilities<br>";
    echo "• Data integrity preserved throughout the update";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Update completed with some errors.</strong><br>";
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