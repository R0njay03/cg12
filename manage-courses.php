<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Handle file upload for Foreign Training and Unit/Interagency Training
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_course') {
        $course_name = $_POST['course_name'];
        $course_code = $_POST['course_code'];
        $training_type = $_POST['training_type'];
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $target_audience = $_POST['target_audience'];
        $course_level = $_POST['course_level'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $capacity = $_POST['capacity'];
        $prerequisites = $_POST['prerequisites'];
        $learning_objectives = $_POST['learning_objectives'];
        $course_outline = $_POST['course_outline'];
        
        $file_attachment = null;
        
        // Handle file upload for Foreign Training and Unit/Interagency Training
        if (($training_type == 'Foreign Training' || $training_type == 'Unit / Interagency Training') && 
            isset($_FILES['course_file']) && $_FILES['course_file']['error'] == 0) {
            
            $upload_dir = 'uploads/courses/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['course_file']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $file_name = uniqid() . '_' . $_FILES['course_file']['name'];
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['course_file']['tmp_name'], $file_path)) {
                    $file_attachment = $file_name;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives, course_outline, file_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssissss", $course_name, $course_code, $training_type, $category, $subcategory, $target_audience, $course_level, $description, $duration, $capacity, $prerequisites, $learning_objectives, $course_outline, $file_attachment);
        
        if ($stmt->execute()) {
            $success_message = "Course added successfully!";
        } else {
            $error_message = "Error adding course: " . $conn->error;
        }
    }
}

// Fetch courses with filtering
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_audience = isset($_GET['audience']) ? $_GET['audience'] : '';

$where_conditions = [];
$params = [];
$types = "";

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | PCG CG-12</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .course-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #002147;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        .course-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #002147;
        }
        .course-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 15px;
        }
        .course-title {
            color: #002147;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .course-code {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        .course-badges {
            display: flex;
            gap: 10px;
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
        .course-details {
            margin-bottom: 15px;
        }
        .course-actions {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        .file-link {
            color: #002147;
            text-decoration: none;
            font-weight: 500;
        }
        .file-link:hover {
            text-decoration: underline;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .toggle-form {
            background: #002147;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .toggle-form:hover {
            background: #c8102e;
        }
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        <ul>
            <li><a href="admin-dashboard-enhanced.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses.php" class="active"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h1>Course Management System</h1>
        </header>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <button class="toggle-form" onclick="toggleForm()">Add New Course</button>

        <div class="form-container" id="courseForm">
            <div class="course-form">
                <h2>Add New Course</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_course">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name">Course Name *</label>
                            <input type="text" id="course_name" name="course_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_code">Course Code</label>
                            <input type="text" id="course_code" name="course_code">
                        </div>
                        
                        <div class="form-group">
                            <label for="training_type">Training Type *</label>
                            <select id="training_type" name="training_type" required onchange="toggleFileUpload()">
                                <option value="">Select Training Type</option>
                                <option value="Local Training">Local Training</option>
                                <option value="Foreign Training">Foreign Training</option>
                                <option value="Unit / Interagency Training">Unit / Interagency Training</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" onchange="updateSubcategories()">
                                <option value="">Select Category</option>
                                <option value="Career Course">Career Course</option>
                                <option value="Functional Courses">Functional Courses</option>
                                <option value="Senior Level Courses">Senior Level Courses</option>
                                <option value="Officer Courses">Officer Courses</option>
                                <option value="International Cooperation">International Cooperation</option>
                                <option value="Joint Operations">Joint Operations</option>
                                <option value="Regional Cooperation">Regional Cooperation</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="subcategory">Subcategory</label>
                            <select id="subcategory" name="subcategory">
                                <option value="">Select Subcategory</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="target_audience">Target Audience *</label>
                            <select id="target_audience" name="target_audience" required>
                                <option value="">Select Target Audience</option>
                                <option value="Officer">Officer</option>
                                <option value="Non-Officer">Non-Officer</option>
                                <option value="Both">Both</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_level">Course Level</label>
                            <select id="course_level" name="course_level">
                                <option value="Basic">Basic</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                                <option value="Senior">Senior</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration</label>
                            <input type="text" id="duration" name="duration" placeholder="e.g., 8 weeks">
                        </div>
                        
                        <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" id="capacity" name="capacity" value="30">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Course Description</label>
                        <textarea id="description" name="description" placeholder="Detailed course description..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="prerequisites">Prerequisites</label>
                        <textarea id="prerequisites" name="prerequisites" placeholder="Course prerequisites and requirements..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="learning_objectives">Learning Objectives</label>
                        <textarea id="learning_objectives" name="learning_objectives" placeholder="Learning objectives and outcomes..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_outline">Course Outline</label>
                        <textarea id="course_outline" name="course_outline" placeholder="Detailed course outline and curriculum..."></textarea>
                    </div>
                    
                    <div class="form-group" id="file_upload_group" style="display: none;">
                        <label for="course_file">Course File (PDF, Images, Documents)</label>
                        <input type="file" id="course_file" name="course_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small>Upload course materials, brochures, or related documents for Foreign Training and Unit/Interagency Training courses.</small>
                    </div>
                    
                    <button type="submit" style="background: #002147; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">Add Course</button>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label for="filter_type">Training Type</label>
                <select id="filter_type" onchange="applyFilters()">
                    <option value="">All Types</option>
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
                    <option value="">All Audiences</option>
                    <option value="Officer" <?php echo $filter_audience == 'Officer' ? 'selected' : ''; ?>>Officer</option>
                    <option value="Non-Officer" <?php echo $filter_audience == 'Non-Officer' ? 'selected' : ''; ?>>Non-Officer</option>
                    <option value="Both" <?php echo $filter_audience == 'Both' ? 'selected' : ''; ?>>Both</option>
                </select>
            </div>
            
            <button onclick="clearFilters()" style="background: #6c757d; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Clear Filters</button>
        </div>

        <!-- Courses List -->
        <div class="courses-list">
            <h2>Course Catalog (<?php echo $courses_result->num_rows; ?> courses)</h2>
            
            <?php if ($courses_result->num_rows > 0): ?>
                <?php while($course = $courses_result->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <div>
                                <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <?php if ($course['course_code']): ?>
                                    <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
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
                        
                        <div class="course-details">
                            <?php if ($course['description']): ?>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($course['description'], 0, 200)) . (strlen($course['description']) > 200 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
                                <?php if ($course['duration']): ?>
                                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                                <?php endif; ?>
                                <p><strong>Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?> students</p>
                                <?php if ($course['subcategory']): ?>
                                    <p><strong>Subcategory:</strong> <?php echo htmlspecialchars($course['subcategory']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($course['file_attachment']): ?>
                                <p style="margin-top: 10px;">
                                    <strong>Attachment:</strong> 
                                    <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                                       target="_blank" class="file-link">
                                        📎 <?php echo htmlspecialchars($course['file_attachment']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-actions">
                            <button class="btn-small" onclick="viewCourse(<?php echo $course['id']; ?>)">View Details</button>
                            <button class="btn-small" onclick="editCourse(<?php echo $course['id']; ?>)">Edit</button>
                            <button class="btn-small" style="background: #dc3545;" onclick="deleteCourse(<?php echo $course['id']; ?>)">Delete</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No courses found matching the current filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('courseForm');
            form.classList.toggle('active');
        }

        function toggleFileUpload() {
            const trainingType = document.getElementById('training_type').value;
            const fileUploadGroup = document.getElementById('file_upload_group');
            
            if (trainingType === 'Foreign Training' || trainingType === 'Unit / Interagency Training') {
                fileUploadGroup.style.display = 'block';
            } else {
                fileUploadGroup.style.display = 'none';
            }
        }

        function updateSubcategories() {
            const category = document.getElementById('category').value;
            const subcategory = document.getElementById('subcategory');
            
            // Clear existing options
            subcategory.innerHTML = '<option value="">Select Subcategory</option>';
            
            const subcategories = {
                'Career Course': ['Specialization Courses', 'Advanced Leadership', 'Executive Leadership'],
                'Functional Courses': ['Maritime Safety', 'Environmental Protection', 'Security and Law Enforcement'],
                'Senior Level Courses': ['Advanced Leadership', 'Executive Leadership'],
                'Officer Courses': ['Basic Officer Training', 'Command Training', 'Staff Training', 'Executive Training'],
                'International Cooperation': ['Bilateral Training', 'Multilateral Training', 'Exchange Programs'],
                'Joint Operations': ['Multi-Agency Training', 'Joint Exercises', 'Coordination Training'],
                'Regional Cooperation': ['ASEAN Programs', 'Regional Security', 'Maritime Cooperation']
            };
            
            if (subcategories[category]) {
                subcategories[category].forEach(function(sub) {
                    const option = document.createElement('option');
                    option.value = sub;
                    option.textContent = sub;
                    subcategory.appendChild(option);
                });
            }
        }

        function applyFilters() {
            const type = document.getElementById('filter_type').value;
            const category = document.getElementById('filter_category').value;
            const audience = document.getElementById('filter_audience').value;
            
            let url = 'manage-courses.php?';
            const params = [];
            
            if (type) params.push('type=' + encodeURIComponent(type));
            if (category) params.push('category=' + encodeURIComponent(category));
            if (audience) params.push('audience=' + encodeURIComponent(audience));
            
            url += params.join('&');
            window.location.href = url;
        }

        function clearFilters() {
            window.location.href = 'manage-courses.php';
        }

        function viewCourse(id) {
            window.open('course-details.php?id=' + id, '_blank');
        }

        function editCourse(id) {
            window.location.href = 'edit-course.php?id=' + id;
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
                window.location.href = 'delete-course.php?id=' + id;
            }
        }
    </script>
</body>
</html>