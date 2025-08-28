<?php
include 'db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header("Location: course-catalog.php");
    exit();
}

// Check if status column exists
$check_status_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'courses' 
                      AND COLUMN_NAME = 'status'";
$status_exists = $conn->query($check_status_query)->num_rows > 0;

if ($status_exists) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND status = 'Active'");
} else {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
}

$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: course-catalog.php");
    exit();
}

$course = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> | PCG CG-12</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .course-detail-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .course-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #002147;
        }
        .course-title {
            color: #002147;
            font-size: 2.5em;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .course-code {
            color: #6c757d;
            font-size: 1.2em;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .course-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge-local { background: #d4edda; color: #155724; }
        .badge-foreign { background: #d1ecf1; color: #0c5460; }
        .badge-unit { background: #fff3cd; color: #856404; }
        .badge-officer { background: #f8d7da; color: #721c24; }
        .badge-non-officer { background: #e2e3e5; color: #383d41; }
        .badge-both { background: #d1ecf1; color: #0c5460; }
        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #002147;
        }
        .info-card h3 {
            color: #002147;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .info-card p {
            color: #495057;
            margin: 0;
            font-size: 1.1em;
            font-weight: 500;
        }
        .course-section {
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #002147;
            font-size: 1.5em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f2b705;
        }
        .section-content {
            color: #495057;
            line-height: 1.6;
            font-size: 1.1em;
        }
        .file-download {
            background: #002147;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        .file-download:hover {
            background: #c8102e;
        }
        .back-button {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
            transition: background 0.3s ease;
        }
        .back-button:hover {
            background: #5a6268;
        }
        .contact-info {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin-top: 30px;
        }
        .contact-info h3 {
            margin-bottom: 15px;
            color: #f2b705;
        }
        .prerequisites-list {
            list-style: none;
            padding: 0;
        }
        .prerequisites-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .prerequisites-list li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 8px;
        }
        .objectives-list {
            list-style: none;
            padding: 0;
        }
        .objectives-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .objectives-list li:before {
            content: "→ ";
            color: #002147;
            font-weight: bold;
            margin-right: 8px;
        }
        .db-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="pcg_logo.png" class="logo" />
            <div class="title-text">
                <h1>PCG CG-12 Course Details</h1>
                <h2>Education & Training Programs</h2>
            </div>
        </div>
        
        <nav class="navbar">
            <a href="index-improved.html">Home</a>
            <a href="course-catalog.php">Course Catalog</a>
            <a href="reports.html">Reports</a>
            <a href="index-improved.html#contact">Contact</a>
        </nav>
        
        <div class="header-right">
            <a href="login.html" class="login-btn"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
        </div>
    </header>

    <main>
        <div class="course-detail-container">
            <?php if (!$status_exists): ?>
                <div class="db-warning">
                    <strong>Database Update Required:</strong> 
                    <a href="fix_database.php" style="color: #002147; font-weight: bold;">Click here to update the database structure</a>
                </div>
            <?php endif; ?>

            <a href="course-catalog.php" class="back-button">← Back to Course Catalog</a>
            
            <div class="course-header">
                <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
                
                <?php if ($course['course_code']): ?>
                    <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                <?php endif; ?>
                
                <div class="course-badges">
                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $course['training_type'])); ?>">
                        <?php echo htmlspecialchars($course['training_type']); ?>
                    </span>
                    <?php if ($course['category']): ?>
                        <span class="badge" style="background: #e9ecef; color: #495057;">
                            <?php echo htmlspecialchars($course['category']); ?>
                        </span>
                    <?php endif; ?>
                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $course['target_audience'])); ?>">
                        <?php echo htmlspecialchars($course['target_audience']); ?>
                    </span>
                    <span class="badge" style="background: #fff3cd; color: #856404;">
                        <?php echo htmlspecialchars($course['course_level']); ?> Level
                    </span>
                </div>
            </div>

            <!-- Course Information Grid -->
            <div class="course-info-grid">
                <?php if ($course['duration']): ?>
                    <div class="info-card">
                        <h3>Duration</h3>
                        <p><?php echo htmlspecialchars($course['duration']); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="info-card">
                    <h3>Capacity</h3>
                    <p><?php echo htmlspecialchars($course['capacity']); ?> students</p>
                </div>
                
                <div class="info-card">
                    <h3>Target Audience</h3>
                    <p><?php echo htmlspecialchars($course['target_audience']); ?></p>
                </div>
                
                <div class="info-card">
                    <h3>Course Level</h3>
                    <p><?php echo htmlspecialchars($course['course_level']); ?></p>
                </div>
                
                <?php if ($course['subcategory']): ?>
                    <div class="info-card">
                        <h3>Subcategory</h3>
                        <p><?php echo htmlspecialchars($course['subcategory']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Course Description -->
            <?php if ($course['description']): ?>
                <div class="course-section">
                    <h2 class="section-title">Course Description</h2>
                    <div class="section-content">
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Prerequisites -->
            <?php if ($course['prerequisites']): ?>
                <div class="course-section">
                    <h2 class="section-title">Prerequisites</h2>
                    <div class="section-content">
                        <?php 
                        $prerequisites = explode(',', $course['prerequisites']);
                        if (count($prerequisites) > 1): ?>
                            <ul class="prerequisites-list">
                                <?php foreach ($prerequisites as $prereq): ?>
                                    <li><?php echo htmlspecialchars(trim($prereq)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php echo nl2br(htmlspecialchars($course['prerequisites'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Learning Objectives -->
            <?php if ($course['learning_objectives']): ?>
                <div class="course-section">
                    <h2 class="section-title">Learning Objectives</h2>
                    <div class="section-content">
                        <?php 
                        $objectives = explode(',', $course['learning_objectives']);
                        if (count($objectives) > 1): ?>
                            <ul class="objectives-list">
                                <?php foreach ($objectives as $objective): ?>
                                    <li><?php echo htmlspecialchars(trim($objective)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php echo nl2br(htmlspecialchars($course['learning_objectives'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Course Outline -->
            <?php if ($course['course_outline']): ?>
                <div class="course-section">
                    <h2 class="section-title">Course Outline</h2>
                    <div class="section-content">
                        <p><?php echo nl2br(htmlspecialchars($course['course_outline'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- File Attachment -->
            <?php if ($course['file_attachment']): ?>
                <div class="course-section">
                    <h2 class="section-title">Course Materials</h2>
                    <div class="section-content">
                        <p>Additional course materials and documentation are available for download:</p>
                        <br>
                        <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                           target="_blank" class="file-download">
                            📎 Download Course Materials
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Contact Information -->
            <div class="contact-info">
                <h3>For More Information</h3>
                <p>Contact the PCG CG-12 Education and Training Department</p>
                <p><strong>Email:</strong> cg12@coastguard.gov.ph</p>
                <p><strong>Phone:</strong> +63915 2413 583</p>
                <p><strong>Address:</strong> Headquarters, Philippine Coast Guard, Port Area, Manila</p>
            </div>
        </div>
    </main>

    <footer class="footer">
        &copy; 2025 Philippine Coast Guard – Deputy Chief of Coast Guard Staff for Education and Training, CG-12 | All rights reserved.
    </footer>
</body>
</html>