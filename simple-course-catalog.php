<?php
include 'db.php';

// Check what columns exist in the courses table
$columns_query = "SHOW COLUMNS FROM courses";
$columns_result = $conn->query($columns_query);
$available_columns = [];

if ($columns_result) {
    while ($row = $columns_result->fetch_assoc()) {
        $available_columns[] = $row['Field'];
    }
}

// Build query based on available columns
$select_fields = ['id', 'course_name'];
if (in_array('description', $available_columns)) $select_fields[] = 'description';
if (in_array('duration', $available_columns)) $select_fields[] = 'duration';
if (in_array('capacity', $available_columns)) $select_fields[] = 'capacity';
if (in_array('training_type', $available_columns)) $select_fields[] = 'training_type';
if (in_array('category', $available_columns)) $select_fields[] = 'category';
if (in_array('target_audience', $available_columns)) $select_fields[] = 'target_audience';
if (in_array('course_level', $available_columns)) $select_fields[] = 'course_level';

$query = "SELECT " . implode(', ', $select_fields) . " FROM courses";
$courses_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Catalog | PCG CG-12</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .catalog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .catalog-header {
            text-align: center;
            margin-bottom: 30px;
            color: #002147;
        }
        .update-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .update-notice h3 {
            margin-top: 0;
            color: #002147;
        }
        .update-btn {
            background: #002147;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin: 10px;
        }
        .update-btn:hover {
            background: #c8102e;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        .course-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .course-title {
            color: #002147;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .course-description {
            color: #495057;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .course-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .course-details span {
            color: #6c757d;
        }
        .course-details strong {
            color: #002147;
        }
        .no-courses {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            grid-column: 1 / -1;
        }
        .btn {
            background: #002147;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #c8102e;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <img src="pcg_logo.png" class="logo" />
            <div class="title-text">
                <h1>PCG CG-12 Course Catalog</h1>
                <h2>Education & Training Programs</h2>
            </div>
        </div>
        
        <nav class="navbar">
            <a href="index.html">Home</a>
            <a href="course-catalog.php" class="active">Course Catalog</a>
            <a href="reports.html">Reports</a>
            <a href="index.html#contact">Contact</a>
        </nav>
        
        <div class="header-right">
            <a href="login.html" class="login-btn">Admin Login</a>
        </div>
    </header>

    <main>
        <div class="catalog-container">
            <div class="catalog-header">
                <h1>PCG CG-12 Training Programs</h1>
                <p>Comprehensive education and training opportunities for Philippine Coast Guard personnel</p>
            </div>

            <?php if (!in_array('training_type', $available_columns)): ?>
                <div class="update-notice">
                    <h3>Database Update Required</h3>
                    <p>To access the full course catalog with all features including Local Training, Foreign Training, and Unit/Interagency Training categories, please update your database structure.</p>
                    <a href="complete_database_update.php" class="update-btn">Update Database Now</a>
                    <a href="update_database.php" class="update-btn">Alternative Update</a>
                </div>
            <?php endif; ?>

            <!-- Courses Display -->
            <div class="courses-grid">
                <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                        <div class="course-card">
                            <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                            
                            <?php if (isset($course['description']) && $course['description']): ?>
                                <div class="course-description">
                                    <?php echo htmlspecialchars(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-details">
                                <?php if (isset($course['duration']) && $course['duration']): ?>
                                    <span><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($course['capacity'])): ?>
                                    <span><strong>Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?> students</span>
                                <?php endif; ?>
                                
                                <?php if (isset($course['training_type']) && $course['training_type']): ?>
                                    <span><strong>Type:</strong> <?php echo htmlspecialchars($course['training_type']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($course['target_audience']) && $course['target_audience']): ?>
                                    <span><strong>Audience:</strong> <?php echo htmlspecialchars($course['target_audience']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($course['course_level']) && $course['course_level']): ?>
                                    <span><strong>Level:</strong> <?php echo htmlspecialchars($course['course_level']); ?></span>
                                <?php endif; ?>
                                
                                <?php if (isset($course['category']) && $course['category']): ?>
                                    <span><strong>Category:</strong> <?php echo htmlspecialchars($course['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-courses">
                        <h3>No courses found</h3>
                        <p>No courses are currently available in the database.</p>
                        <p><a href="complete_database_update.php" class="btn">Initialize Course Database</a></p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="color: #002147;">Need Help?</h3>
                <p>If you're experiencing issues with the course catalog, try updating the database structure:</p>
                <a href="complete_database_update.php" class="update-btn">Complete Database Update</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        &copy; 2025 Philippine Coast Guard – Deputy Chief of Coast Guard Staff for Education and Training, CG-12 | All rights reserved.
    </footer>
</body>
</html>