<?php
include 'db.php';

// Fetch courses with filtering
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_audience = isset($_GET['audience']) ? $_GET['audience'] : '';

// Check if status column exists
$check_status_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = DATABASE() 
                      AND TABLE_NAME = 'courses' 
                      AND COLUMN_NAME = 'status'";
$status_exists = $conn->query($check_status_query)->num_rows > 0;

$where_conditions = [];
$params = [];
$types = "";

// Only add status condition if column exists
if ($status_exists) {
    $where_conditions[] = "status = 'Active'";
}

if ($filter_type) {
    $where_conditions[] = "training_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}
if ($filter_category) {
    $where_conditions[] = "category = ?";
    $params[] = $filter_category;
    $types .= "s";
}
if ($filter_audience) {
    $where_conditions[] = "target_audience = ?";
    $params[] = $filter_audience;
    $types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
$query = "SELECT * FROM courses $where_clause ORDER BY training_type, category, course_name";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $courses_result = $stmt->get_result();
} else {
    $courses_result = $conn->query($query);
}

// Get statistics
$stats_where = $status_exists ? "WHERE status = 'Active'" : "";
$stats_query = "SELECT 
    training_type,
    COUNT(*) as count
    FROM courses 
    $stats_where
    GROUP BY training_type";
$stats_result = $conn->query($stats_query);
$stats = [];
while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['training_type']] = $row['count'];
}
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #f2b705;
        }
        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #002147;
        }
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .course-code {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        .course-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-local { background: #d4edda; color: #155724; }
        .badge-foreign { background: #d1ecf1; color: #0c5460; }
        .badge-unit { background: #fff3cd; color: #856404; }
        .badge-officer { background: #f8d7da; color: #721c24; }
        .badge-non-officer { background: #e2e3e5; color: #383d41; }
        .badge-both { background: #d1ecf1; color: #0c5460; }
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
        .file-link {
            color: #002147;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .file-link:hover {
            text-decoration: underline;
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
        .section-header {
            background: #002147;
            color: white;
            padding: 15px 20px;
            margin: 30px 0 20px 0;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 600;
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
                <h1>PCG CG-12 Course Catalog</h1>
                <h2>Education & Training Programs</h2>
            </div>
        </div>
        
        <nav class="navbar">
            <a href="index-improved.html">Home</a>
            <a href="course-catalog.php" class="active">Course Catalog</a>
            <a href="reports.html">Reports</a>
            <a href="index-improved.html#contact">Contact</a>
        </nav>
        
        <div class="header-right">
            <a href="login.html" class="login-btn"><i class="fas fa-sign-in-alt"></i> Admin Login</a>
        </div>
    </header>

    <main>
        <div class="catalog-container">
            <?php if (!$status_exists): ?>
                <div class="db-warning">
                    <strong>Database Update Required:</strong> 
                    <a href="fix_database.php" style="color: #002147; font-weight: bold;">Click here to update the database structure</a>
                </div>
            <?php endif; ?>

            <div class="catalog-header">
                <h1>PCG CG-12 Training Programs</h1>
                <p>Comprehensive education and training opportunities for Philippine Coast Guard personnel</p>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo isset($stats['Local Training']) ? $stats['Local Training'] : 0; ?></div>
                    <div>Local Training Programs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo isset($stats['Foreign Training']) ? $stats['Foreign Training'] : 0; ?></div>
                    <div>Foreign Training Programs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo isset($stats['Unit / Interagency Training']) ? $stats['Unit / Interagency Training'] : 0; ?></div>
                    <div>Unit/Interagency Programs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $courses_result->num_rows; ?></div>
                    <div>Total Available Courses</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label for="filter_type">Training Type</label>
                    <select id="filter_type" onchange="applyFilters()">
                        <option value="">All Training Types</option>
                        <option value="Local Training" <?php echo $filter_type == 'Local Training' ? 'selected' : ''; ?>>Local Training</option>
                        <option value="Foreign Training" <?php echo $filter_type == 'Foreign Training' ? 'selected' : ''; ?>>Foreign Training</option>
                        <option value="Unit / Interagency Training" <?php echo $filter_type == 'Unit / Interagency Training' ? 'selected' : ''; ?>>Unit / Interagency Training</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_category">Category</label>
                    <select id="filter_category" onchange="applyFilters()">
                        <option value="">All Categories</option>
                        <option value="Career Course" <?php echo $filter_category == 'Career Course' ? 'selected' : ''; ?>>Career Course</option>
                        <option value="Functional Courses" <?php echo $filter_category == 'Functional Courses' ? 'selected' : ''; ?>>Functional Courses</option>
                        <option value="Senior Level Courses" <?php echo $filter_category == 'Senior Level Courses' ? 'selected' : ''; ?>>Senior Level Courses</option>
                        <option value="Officer Courses" <?php echo $filter_category == 'Officer Courses' ? 'selected' : ''; ?>>Officer Courses</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_audience">Target Audience</label>
                    <select id="filter_audience" onchange="applyFilters()">
                        <option value="">All Personnel</option>
                        <option value="Officer" <?php echo $filter_audience == 'Officer' ? 'selected' : ''; ?>>Officers</option>
                        <option value="Non-Officer" <?php echo $filter_audience == 'Non-Officer' ? 'selected' : ''; ?>>Non-Officers</option>
                        <option value="Both" <?php echo $filter_audience == 'Both' ? 'selected' : ''; ?>>Both</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button onclick="clearFilters()" class="btn">Clear Filters</button>
                </div>
            </div>

            <!-- Courses Display -->
            <?php
            $current_type = '';
            $courses_result->data_seek(0);
            $courses_by_type = [];
            
            while ($course = $courses_result->fetch_assoc()) {
                $courses_by_type[$course['training_type']][] = $course;
            }
            ?>

            <?php foreach ($courses_by_type as $type => $courses): ?>
                <div class="section-header"><?php echo htmlspecialchars($type); ?></div>
                
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                            
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
                                    <?php echo htmlspecialchars($course['course_level']); ?>
                                </span>
                            </div>
                            
                            <?php if ($course['description']): ?>
                                <div class="course-description">
                                    <?php echo htmlspecialchars(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-details">
                                <?php if ($course['duration']): ?>
                                    <span><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></span>
                                <?php endif; ?>
                                <span><strong>Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?> students</span>
                                <?php if ($course['subcategory']): ?>
                                    <span><strong>Category:</strong> <?php echo htmlspecialchars($course['subcategory']); ?></span>
                                <?php endif; ?>
                                <span><strong>Level:</strong> <?php echo htmlspecialchars($course['course_level']); ?></span>
                            </div>
                            
                            <?php if ($course['file_attachment']): ?>
                                <div style="margin-top: 15px;">
                                    <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                                       target="_blank" class="file-link">
                                        📎 View Course Materials
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 15px;">
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($courses_by_type)): ?>
                <div class="no-courses">
                    <h3>No courses found</h3>
                    <p>No courses match your current filter criteria. Please try adjusting your filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        &copy; 2025 Philippine Coast Guard – Deputy Chief of Coast Guard Staff for Education and Training, CG-12 | All rights reserved.
    </footer>

    <script>
        function applyFilters() {
            const type = document.getElementById('filter_type').value;
            const category = document.getElementById('filter_category').value;
            const audience = document.getElementById('filter_audience').value;
            
            let url = 'course-catalog.php?';
            const params = [];
            
            if (type) params.push('type=' + encodeURIComponent(type));
            if (category) params.push('category=' + encodeURIComponent(category));
            if (audience) params.push('audience=' + encodeURIComponent(audience));
            
            url += params.join('&');
            window.location.href = url;
        }

        function clearFilters() {
            window.location.href = 'course-catalog.php';
        }
    </script>
</body>
</html>