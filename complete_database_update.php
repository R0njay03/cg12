<?php
// Complete database update script for PCG CG-12 Course Management System
$host = "localhost";
$user = "root";
$pass = "";

// Connect to MySQL server
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Complete Database Update - PCG CG-12</title>";
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
echo "<h1>PCG CG-12 Complete Database Update</h1>";
echo "<p>Updating database structure and populating with comprehensive course data</p>";
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
    echo "<div class='info'>Courses table exists, checking structure...</div>";
    $table_exists = true;
    
    // Check for new columns
    $columns_to_check = ['training_type', 'category', 'subcategory', 'target_audience', 'course_level', 'prerequisites', 'learning_objectives', 'course_outline', 'file_attachment', 'status'];
    $missing_columns = [];
    
    foreach ($columns_to_check as $column) {
        $check_column = $conn->query("SHOW COLUMNS FROM courses LIKE '$column'");
        if ($check_column->num_rows == 0) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        echo "<div class='warning'>Missing columns: " . implode(', ', $missing_columns) . "</div>";
    } else {
        echo "<div class='success'>✓ All required columns exist</div>";
    }
}
echo "</div>";

// Step 2: Create or update table structure
echo "<div class='step'>";
echo "<h3>Step 2: Creating/Updating Table Structure</h3>";

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
    // Add missing columns to existing table
    $alter_queries = [
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS course_code VARCHAR(50) AFTER course_name",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS training_type ENUM('Local Training', 'Foreign Training', 'Unit / Interagency Training') NOT NULL DEFAULT 'Local Training' AFTER course_code",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS category VARCHAR(100) AFTER training_type",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS subcategory VARCHAR(100) AFTER category",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS target_audience ENUM('Officer', 'Non-Officer', 'Both') NOT NULL DEFAULT 'Both' AFTER subcategory",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS course_level ENUM('Basic', 'Intermediate', 'Advanced', 'Senior') DEFAULT 'Basic' AFTER target_audience",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS prerequisites TEXT AFTER capacity",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS learning_objectives TEXT AFTER prerequisites",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS course_outline TEXT AFTER learning_objectives",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS file_attachment VARCHAR(255) AFTER course_outline",
        "ALTER TABLE courses ADD COLUMN IF NOT EXISTS status ENUM('Active', 'Inactive', 'Pending') DEFAULT 'Active' AFTER file_attachment"
    ];
    
    foreach ($alter_queries as $query) {
        if ($conn->query($query) === TRUE) {
            echo "<div class='success'>✓ Updated table structure</div>";
            $success_count++;
        } else {
            // Check if error is because column already exists
            if (strpos($conn->error, 'Duplicate column name') !== false) {
                echo "<div class='warning'>⚠ Column already exists (skipped)</div>";
            } else {
                echo "<div class='error'>✗ Error updating table: " . $conn->error . "</div>";
                $error_count++;
            }
        }
    }
}
echo "</div>";

// Step 3: Clear existing data and insert comprehensive course data
echo "<div class='step'>";
echo "<h3>Step 3: Populating Course Data</h3>";

// Clear existing courses
$conn->query("DELETE FROM courses");
echo "<div class='info'>Cleared existing course data</div>";

// Insert comprehensive course data
$courses_data = [
    // NON-OFFICER SPECIALIZATION RATING COURSES
    ['Operations Specialization Rating Course', 'OSRC-001', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Comprehensive training program designed to develop operational expertise in Coast Guard personnel, covering maritime operations, search and rescue procedures, and operational planning.', '8 weeks', 25, 'Minimum 2 years service, Basic seamanship certification', 'Develop advanced operational skills, Master SAR procedures, Understand operational planning and execution', '', '', 'Active'],
    
    ['Boatswains Mate Rating Course', 'BMRC-002', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Specialized training for boatswains mates covering seamanship, boat handling, deck operations, and crew supervision responsibilities.', '6 weeks', 20, 'Basic seamanship course completion, Physical fitness standards', 'Master boat handling techniques, Develop leadership skills, Understand deck operations and maintenance', '', '', 'Active'],
    
    ['Machinery Technician Rating Course', 'MTRC-003', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Technical training program for machinery technicians covering marine engines, mechanical systems, troubleshooting, and maintenance procedures.', '10 weeks', 15, 'Basic mechanical aptitude, Technical school background preferred', 'Master marine engine operations, Develop troubleshooting skills, Understand preventive maintenance procedures', '', '', 'Active'],
    
    ['Electricians Mate Rating Course', 'EMRC-004', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Electrical systems training covering marine electrical systems, electronics, power distribution, and electrical safety procedures.', '8 weeks', 18, 'Basic electrical knowledge, Safety certification', 'Master electrical system operations, Develop diagnostic skills, Understand electrical safety protocols', '', '', 'Active'],
    
    ['Damage Control Rating Course', 'DCRC-005', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Critical training in damage control procedures, firefighting, flooding control, and emergency response aboard vessels.', '4 weeks', 30, 'Basic firefighting certification, Physical fitness requirements', 'Master damage control procedures, Develop emergency response skills, Understand vessel safety systems', '', '', 'Active'],
    
    ['Commissary Specialization Rating Course', 'CSRC-006', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Training in food service operations, inventory management, nutrition, and galley operations for Coast Guard facilities.', '6 weeks', 20, 'Food safety certification, Basic math skills', 'Master food service operations, Develop inventory management skills, Understand nutrition and meal planning', '', '', 'Active'],
    
    ['Aids to Navigation Specialization Course', 'ATONSC-007', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Specialized training in maintenance and operation of navigational aids, buoy systems, and lighthouse operations.', '8 weeks', 15, 'Basic navigation knowledge, Technical aptitude', 'Master ATON maintenance procedures, Develop technical skills, Understand navigation systems', '', '', 'Active'],
    
    ['Radio Operations and Maintenance Specialization Course', 'ROMSC-008', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Comprehensive training in radio communications, equipment maintenance, and emergency communication procedures.', '6 weeks', 25, 'Basic electronics knowledge, Communication skills', 'Master radio operations, Develop maintenance skills, Understand emergency communication protocols', '', '', 'Active'],
    
    ['Gunners Mate Specialization Course', 'GMSC-009', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Advanced training in weapons systems, ammunition handling, ballistics, and weapons maintenance procedures.', '10 weeks', 12, 'Security clearance, Physical fitness standards, Weapons safety certification', 'Master weapons systems operations, Develop maintenance expertise, Understand ballistics and safety procedures', '', '', 'Active'],
    
    ['Information System Technician Specialization Course', 'ISTSC-010', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Technical training in computer systems, network administration, cybersecurity, and information technology support.', '12 weeks', 20, 'Basic computer skills, Technical aptitude test', 'Master IT system operations, Develop cybersecurity skills, Understand network administration', '', '', 'Active'],
    
    ['Coast Guard Security Border Protection Specialization Course', 'CGSBPSC-011', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Specialized training in border security, immigration enforcement, customs procedures, and security protocols.', '8 weeks', 25, 'Security clearance, Law enforcement background check', 'Master border security procedures, Develop enforcement skills, Understand legal frameworks', '', '', 'Active'],
    
    ['Coast Guard Intelligence Course', 'CGIC-012', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Intelligence analysis training covering threat assessment, information gathering, analysis techniques, and intelligence reporting.', '10 weeks', 15, 'Top secret clearance, Analytical skills assessment', 'Master intelligence analysis, Develop threat assessment skills, Understand reporting procedures', '', '', 'Active'],
    
    ['CGIG-IAS Investigation and Inspection Specialization Course', 'CGIG-IAS-013', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Specialized training in investigation techniques, inspection procedures, evidence handling, and report writing.', '8 weeks', 18, 'Law enforcement background, Investigation experience preferred', 'Master investigation techniques, Develop inspection skills, Understand legal procedures', '', '', 'Active'],
    
    ['Internal Auditor Specialization Course', 'IASC-014', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Training in audit procedures, financial analysis, compliance checking, and audit reporting for internal operations.', '6 weeks', 20, 'Accounting background, Analytical skills', 'Master audit procedures, Develop analytical skills, Understand compliance requirements', '', '', 'Active'],
    
    ['Marine Environmental Protection Specialization Course', 'MEPSC-015', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Environmental protection training covering pollution response, environmental law, and marine ecosystem protection.', '8 weeks', 25, 'Environmental science background preferred', 'Master environmental protection procedures, Develop response skills, Understand environmental regulations', '', '', 'Active'],
    
    ['Yeoman Specialization Course', 'YSC-016', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Administrative training covering office procedures, records management, correspondence, and administrative support functions.', '4 weeks', 30, 'Basic computer skills, Communication skills', 'Master administrative procedures, Develop organizational skills, Understand records management', '', '', 'Active'],
    
    ['Paralegal Specialization Course', 'PSC-017', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Legal support training covering legal research, document preparation, case management, and court procedures.', '10 weeks', 15, 'College education preferred, Legal interest', 'Master legal research skills, Develop document preparation abilities, Understand legal procedures', '', '', 'Active'],
    
    ['Veterinary Aide Specialization Course', 'VASC-018', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Basic', 'Training in animal care, veterinary assistance, health monitoring, and basic medical procedures for service animals.', '6 weeks', 12, 'Animal handling experience, Medical interest', 'Master animal care procedures, Develop medical assistance skills, Understand health monitoring', '', '', 'Active'],
    
    ['Drill Instructor Specialization Course', 'DISC-019', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Advanced', 'Leadership training for drill instructors covering training methods, leadership techniques, and recruit development.', '8 weeks', 15, 'Leadership experience, Physical fitness standards, Minimum 5 years service', 'Master training techniques, Develop leadership skills, Understand recruit development', '', '', 'Active'],
    
    ['Healthcare Technician Specialization Course', 'HTSC-020', 'Local Training', 'Career Course', 'Specialization Courses', 'Non-Officer', 'Intermediate', 'Medical training covering basic healthcare, first aid, medical procedures, and health maintenance for Coast Guard personnel.', '12 weeks', 20, 'Medical background preferred, First aid certification', 'Master healthcare procedures, Develop medical skills, Understand health maintenance', '', '', 'Active'],
    
    // FUNCTIONAL COURSES
    ['Maritime Safety Administration Course (MARSAD)', 'MARSAD-021', 'Local Training', 'Functional Courses', 'Maritime Safety', 'Both', 'Intermediate', 'Comprehensive course covering maritime safety regulations, vessel inspection procedures, safety management systems, and accident investigation.', '6 weeks', 25, 'Basic maritime knowledge, Safety certification', 'Master safety regulations, Develop inspection skills, Understand safety management systems', '', '', 'Active'],
    
    ['Marine Environment Protection Course (MAREP)', 'MAREP-022', 'Local Training', 'Functional Courses', 'Environmental Protection', 'Both', 'Intermediate', 'Environmental protection course covering marine pollution prevention, response procedures, environmental law, and ecosystem protection.', '4 weeks', 30, 'Environmental awareness, Basic science knowledge', 'Master pollution prevention, Develop response capabilities, Understand environmental law', '', '', 'Active'],
    
    ['Maritime Security Law Enforcement Course (MARSEC)', 'MARSEC-023', 'Local Training', 'Functional Courses', 'Security and Law Enforcement', 'Both', 'Advanced', 'Law enforcement training covering maritime security, boarding procedures, law enforcement techniques, and legal authorities.', '8 weeks', 20, 'Law enforcement background, Security clearance', 'Master security procedures, Develop enforcement skills, Understand legal authorities', '', '', 'Active'],
    
    // SENIOR LEVEL COURSES (Non-Officer)
    ['Coast Guard Non-Officer Advanced Course (CGNOAC)', 'CGNOAC-024', 'Local Training', 'Senior Level Courses', 'Advanced Leadership', 'Non-Officer', 'Advanced', 'Advanced leadership course for senior non-commissioned officers covering strategic thinking, advanced leadership, and organizational management.', '10 weeks', 15, 'Minimum 10 years service, Leadership experience, Supervisory role', 'Develop strategic thinking, Master advanced leadership, Understand organizational management', '', '', 'Active'],
    
    ['Coast Guard Senior Executive Course (CGNOSEC)', 'CGNOSEC-025', 'Local Training', 'Senior Level Courses', 'Executive Leadership', 'Non-Officer', 'Senior', 'Executive-level course for senior non-commissioned officers covering executive leadership, policy development, and strategic management.', '8 weeks', 12, 'CGNOAC completion, Minimum 15 years service, Executive potential', 'Master executive leadership, Develop policy skills, Understand strategic management', '', '', 'Active'],
    
    // OFFICER COURSES
    ['Coast Guard Officer Course (Basic Course)', 'CGOC-026', 'Local Training', 'Officer Courses', 'Basic Officer Training', 'Officer', 'Basic', 'Foundational course for new Coast Guard officers covering leadership fundamentals, maritime law, navigation, and basic command responsibilities.', '16 weeks', 30, 'Officer commission, College degree, Physical fitness standards', 'Develop basic leadership skills, Master maritime fundamentals, Understand command responsibilities', '', '', 'Active'],
    
    ['Coast Guard Station Commanders Course', 'CGSCC-027', 'Local Training', 'Officer Courses', 'Command Training', 'Officer', 'Advanced', 'Command training for station commanders covering operational command, resource management, personnel leadership, and community relations.', '8 weeks', 20, 'Minimum 5 years officer service, Command selection', 'Master command skills, Develop management abilities, Understand operational leadership', '', '', 'Active'],
    
    ['Coast Guard Staff Course', 'CGSC-028', 'Local Training', 'Officer Courses', 'Staff Training', 'Officer', 'Intermediate', 'Staff officer training covering staff procedures, planning processes, coordination techniques, and analytical skills for headquarters assignments.', '12 weeks', 25, 'Minimum 3 years officer service, Staff assignment', 'Master staff procedures, Develop analytical skills, Understand planning processes', '', '', 'Active'],
    
    ['Coast Guard Executive Course', 'CGEC-029', 'Local Training', 'Officer Courses', 'Executive Training', 'Officer', 'Senior', 'Executive leadership course for senior officers covering strategic leadership, policy development, interagency coordination, and executive decision-making.', '10 weeks', 15, 'Minimum 12 years officer service, Senior leadership potential', 'Master executive leadership, Develop strategic thinking, Understand policy development', '', '', 'Active'],
    
    // FOREIGN TRAINING SAMPLES
    ['International Maritime Security Course', 'IMSC-030', 'Foreign Training', 'International Cooperation', 'Maritime Security', 'Both', 'Advanced', 'International course conducted in partnership with allied coast guards focusing on maritime security cooperation and joint operations.', '4 weeks', 10, 'Security clearance, International assignment eligibility', 'Develop international cooperation skills, Master joint operations, Understand maritime security protocols', '', 'foreign_training_maritime_security.pdf', 'Active'],
    
    ['ASEAN Coast Guard Cooperation Course', 'ACGCC-032', 'Foreign Training', 'Regional Cooperation', 'Regional Security', 'Officer', 'Advanced', 'Regional cooperation course with ASEAN member coast guards focusing on maritime domain awareness and regional security.', '3 weeks', 15, 'Officer rank, Regional assignment eligibility', 'Master regional cooperation, Develop diplomatic skills, Understand ASEAN protocols', '', 'asean_cooperation_course.pdf', 'Active'],
    
    // UNIT/INTERAGENCY TRAINING SAMPLES
    ['Joint Interagency Task Force Training', 'JIATF-031', 'Unit / Interagency Training', 'Joint Operations', 'Multi-Agency Training', 'Both', 'Advanced', 'Multi-agency training exercise focusing on coordination between Coast Guard, Navy, and other agencies in joint operations.', '2 weeks', 50, 'Multi-agency clearance, Joint operations experience', 'Master interagency coordination, Develop joint operations skills, Understand multi-agency protocols', '', 'interagency_joint_ops.pdf', 'Active']
];

$insert_sql = "INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives, course_outline, file_attachment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_sql);
$courses_added = 0;

foreach ($courses_data as $course) {
    $stmt->bind_param("sssssssssississs", ...$course);
    if ($stmt->execute()) {
        $courses_added++;
    } else {
        echo "<div class='error'>✗ Error inserting course: " . $course[0] . " - " . $conn->error . "</div>";
        $error_count++;
    }
}

echo "<div class='success'>✓ Successfully added $courses_added courses to the database</div>";
$success_count += $courses_added;

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

// Summary
echo "<div class='summary'>";
echo "<h3 style='color: #002147;'>Database Update Complete!</h3>";
echo "<p><strong>Total successful operations:</strong> $success_count</p>";
echo "<p><strong>Total errors:</strong> $error_count</p>";
echo "<p><strong>Courses in database:</strong> $courses_added</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ Database update completed successfully!</strong><br>";
    echo "The PCG CG-12 course management system is now ready with:<br>";
    echo "• Complete course database structure<br>";
    echo "• All Local Training courses (Career, Functional, Senior Level, Officer)<br>";
    echo "• Sample Foreign Training and Unit/Interagency Training courses<br>";
    echo "• File upload system for course materials<br>";
    echo "• Full course management capabilities";
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