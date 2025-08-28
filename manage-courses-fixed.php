<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$edit_course = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_course' || $_POST['action'] == 'edit_course') {
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
        
        if ($_POST['action'] == 'add_course') {
            // Add new course
            $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, training_type, category, subcategory, target_audience, course_level, description, duration, capacity, prerequisites, learning_objectives, course_outline, file_attachment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->bind_param("sssssssssissss", $course_name, $course_code, $training_type, $category, $subcategory, $target_audience, $course_level, $description, $duration, $capacity, $prerequisites, $learning_objectives, $course_outline, $file_attachment);
            
            if ($stmt->execute()) {
                $success_message = "Course added successfully!";
            } else {
                $error_message = "Error adding course: " . $conn->error;
            }
        } else {
            // Edit existing course
            $course_id = $_POST['course_id'];
            
            // If no new file uploaded, keep existing file
            if (!$file_attachment && isset($_POST['existing_file'])) {
                $file_attachment = $_POST['existing_file'];
            }
            
            $stmt = $conn->prepare("UPDATE courses SET course_name=?, course_code=?, training_type=?, category=?, subcategory=?, target_audience=?, course_level=?, description=?, duration=?, capacity=?, prerequisites=?, learning_objectives=?, course_outline=?, file_attachment=? WHERE id=?");
            $stmt->bind_param("sssssssssissssi", $course_name, $course_code, $training_type, $category, $subcategory, $target_audience, $course_level, $description, $duration, $capacity, $prerequisites, $learning_objectives, $course_outline, $file_attachment, $course_id);
            
            if ($stmt->execute()) {
                $success_message = "Course updated successfully!";
            } else {
                $error_message = "Error updating course: " . $conn->error;
            }
        }
    } elseif ($_POST['action'] == 'delete_course') {
        $course_id = $_POST['course_id'];
        
        // Get file attachment to delete
        $file_query = $conn->prepare("SELECT file_attachment FROM courses WHERE id = ?");
        $file_query->bind_param("i", $course_id);
        $file_query->execute();
        $file_result = $file_query->get_result();
        $file_data = $file_result->fetch_assoc();
        
        // Delete the course
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $course_id);
        
        if ($stmt->execute()) {
            // Delete associated file if exists
            if ($file_data['file_attachment'] && file_exists('uploads/courses/' . $file_data['file_attachment'])) {
                unlink('uploads/courses/' . $file_data['file_attachment']);
            }
            $success_message = "Course deleted successfully!";
        } else {
            $error_message = "Error deleting course: " . $conn->error;
        }
    }
}

// Handle edit request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_course = $edit_result->fetch_assoc();
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
    <link rel="stylesheet" href="admin-style-enhanced.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .course-form {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-top: 4px solid #002147;
        }
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            color: #002147;
        }
        
        .form-header h2 {
            margin: 0;
            font-size: 1.5em;
            font-weight: 600;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #002147;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 3px rgba(0, 33, 71, 0.1);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .filters {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-top: 4px solid #c8102e;
        }
        
        .filters h3 {
            color: #002147;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #002147;
            font-size: 0.9em;
        }
        
        .course-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border-left: 5px solid #002147;
            transition: all 0.3s ease;
        }
        
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .course-title {
            color: #002147;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .course-code {
            color: #666;
            font-size: 0.9em;
            font-weight: 500;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .course-badges {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-local-training { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; }
        .badge-foreign-training { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); color: #0c5460; }
        .badge-unit-interagency-training { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404; }
        .badge-officer { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
        .badge-non-officer { background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%); color: #383d41; }
        .badge-both { background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%); color: #004085; }
        
        .course-details {
            margin-bottom: 20px;
        }
        
        .course-details p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .course-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 0.85em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .file-link {
            color: #002147;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .file-link:hover {
            background: #002147;
            color: white;
            transform: scale(1.05);
        }
        
        .message {
            padding: 18px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .toggle-form {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toggle-form:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }
        
        .form-container {
            display: none;
            animation: slideDown 0.4s ease;
        }
        
        .form-container.active {
            display: block;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }
        
        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 15px;
        }
        
        .cancel-btn:hover {
            background: #5a6268;
            transform: scale(1.05);
        }
        
        .no-data {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .no-data i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-data h3 {
            color: #002147;
            margin-bottom: 10px;
        }
        
        .filter-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
        }
        
        .clear-btn {
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .clear-btn:hover {
            background: #5a6268;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="pcg_logo.png" alt="PCG Logo" class="sidebar-logo" />
            <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        </div>
        <ul>
            <li><a href="admin-dashboard-with-logo.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses-fixed.php" class="active"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="course-catalog.php"><i class="fas fa-book"></i> Course Catalog</a></li>
            <li><a href="index-improved.html"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> Course Management System</h1>
            <p>Manage and organize PCG CG-12 training courses and programs</p>
        </header>

        <?php if ($success_message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <button class="toggle-form" onclick="toggleForm()">
            <i class="fas fa-plus"></i> 
            <?php echo $edit_course ? 'Cancel Edit' : 'Add New Course'; ?>
        </button>

        <div class="form-container <?php echo $edit_course ? 'active' : ''; ?>" id="courseForm">
            <div class="course-form">
                <div class="form-header">
                    <i class="fas fa-<?php echo $edit_course ? 'edit' : 'plus'; ?>"></i>
                    <h2><?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?></h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_course ? 'edit_course' : 'add_course'; ?>">
                    <?php if ($edit_course): ?>
                        <input type="hidden" name="course_id" value="<?php echo $edit_course['id']; ?>">
                        <input type="hidden" name="existing_file" value="<?php echo $edit_course['file_attachment']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="course_name"><i class="fas fa-graduation-cap"></i> Course Name *</label>
                            <input type="text" id="course_name" name="course_name" required 
                                   value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="course_code"><i class="fas fa-code"></i> Course Code</label>
                            <input type="text" id="course_code" name="course_code" 
                                   value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_code']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="training_type"><i class="fas fa-tag"></i> Training Type *</label>
                            <select id="training_type" name="training_type" required onchange="toggleFileUpload()">
                                <option value="">Select Training Type</option>
                                <option value="Local Training" <?php echo ($edit_course && $edit_course['training_type'] == 'Local Training') ? 'selected' : ''; ?>>Local Training</option>
                                <option value="Foreign Training" <?php echo ($edit_course && $edit_course['training_type'] == 'Foreign Training') ? 'selected' : ''; ?>>Foreign Training</option>
                                <option value="Unit / Interagency Training" <?php echo ($edit_course && $edit_course['training_type'] == 'Unit / Interagency Training') ? 'selected' : ''; ?>>Unit / Interagency Training</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="category"><i class="fas fa-folder"></i> Category</label>
                            <select id="category" name="category" onchange="updateSubcategories()">
                                <option value="">Select Category</option>
                                <option value="Career Course" <?php echo ($edit_course && $edit_course['category'] == 'Career Course') ? 'selected' : ''; ?>>Career Course</option>
                                <option value="Functional Courses" <?php echo ($edit_course && $edit_course['category'] == 'Functional Courses') ? 'selected' : ''; ?>>Functional Courses</option>
                                <option value="Senior Level Courses" <?php echo ($edit_course && $edit_course['category'] == 'Senior Level Courses') ? 'selected' : ''; ?>>Senior Level Courses</option>
                                <option value="Officer Courses" <?php echo ($edit_course && $edit_course['category'] == 'Officer Courses') ? 'selected' : ''; ?>>Officer Courses</option>
                                <option value="International Cooperation" <?php echo ($edit_course && $edit_course['category'] == 'International Cooperation') ? 'selected' : ''; ?>>International Cooperation</option>
                                <option value="Joint Operations" <?php echo ($edit_course && $edit_course['category'] == 'Joint Operations') ? 'selected' : ''; ?>>Joint Operations</option>
                                <option value="Regional Cooperation" <?php echo ($edit_course && $edit_course['category'] == 'Regional Cooperation') ? 'selected' : ''; ?>>Regional Cooperation</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="subcategory"><i class="fas fa-folder-open"></i> Subcategory</label>
                            <select id="subcategory" name="subcategory">
                                <option value="">Select Subcategory</option>
                                <?php if ($edit_course && $edit_course['subcategory']): ?>
                                    <option value="<?php echo htmlspecialchars($edit_course['subcategory']); ?>" selected>
                                        <?php echo htmlspecialchars($edit_course['subcategory']); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="target_audience"><i class="fas fa-users"></i> Target Audience *</label>
                            <select id="target_audience" name="target_audience" required>
                                <option value="">Select Target Audience</option>
                                <option value="Officer" <?php echo ($edit_course && $edit_course['target_audience'] == 'Officer') ? 'selected' : ''; ?>>Officer</option>
                                <option value="Non-Officer" <?php echo ($edit_course && $edit_course['target_audience'] == 'Non-Officer') ? 'selected' : ''; ?>>Non-Officer</option>
                                <option value="Both" <?php echo ($edit_course && $edit_course['target_audience'] == 'Both') ? 'selected' : ''; ?>>Both</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_level"><i class="fas fa-layer-group"></i> Course Level</label>
                            <select id="course_level" name="course_level">
                                <option value="Basic" <?php echo ($edit_course && $edit_course['course_level'] == 'Basic') ? 'selected' : ''; ?>>Basic</option>
                                <option value="Intermediate" <?php echo ($edit_course && $edit_course['course_level'] == 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo ($edit_course && $edit_course['course_level'] == 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                                <option value="Senior" <?php echo ($edit_course && $edit_course['course_level'] == 'Senior') ? 'selected' : ''; ?>>Senior</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration"><i class="fas fa-clock"></i> Duration</label>
                            <input type="text" id="duration" name="duration" placeholder="e.g., 8 weeks" 
                                   value="<?php echo $edit_course ? htmlspecialchars($edit_course['duration']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="capacity"><i class="fas fa-user-friends"></i> Capacity</label>
                            <input type="number" id="capacity" name="capacity" 
                                   value="<?php echo $edit_course ? $edit_course['capacity'] : '30'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Course Description</label>
                        <textarea id="description" name="description" placeholder="Detailed course description..."><?php echo $edit_course ? htmlspecialchars($edit_course['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="prerequisites"><i class="fas fa-list-check"></i> Prerequisites</label>
                        <textarea id="prerequisites" name="prerequisites" placeholder="Course prerequisites and requirements..."><?php echo $edit_course ? htmlspecialchars($edit_course['prerequisites']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="learning_objectives"><i class="fas fa-bullseye"></i> Learning Objectives</label>
                        <textarea id="learning_objectives" name="learning_objectives" placeholder="Learning objectives and outcomes..."><?php echo $edit_course ? htmlspecialchars($edit_course['learning_objectives']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_outline"><i class="fas fa-list-ol"></i> Course Outline</label>
                        <textarea id="course_outline" name="course_outline" placeholder="Detailed course outline and curriculum..."><?php echo $edit_course ? htmlspecialchars($edit_course['course_outline']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group" id="file_upload_group" style="display: <?php echo ($edit_course && ($edit_course['training_type'] == 'Foreign Training' || $edit_course['training_type'] == 'Unit / Interagency Training')) ? 'block' : 'none'; ?>;">
                        <label for="course_file"><i class="fas fa-file-upload"></i> Course File (PDF, Images, Documents)</label>
                        <input type="file" id="course_file" name="course_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small>Upload course materials, brochures, or related documents for Foreign Training and Unit/Interagency Training courses.</small>
                        <?php if ($edit_course && $edit_course['file_attachment']): ?>
                            <p style="margin-top: 10px;">
                                <strong>Current file:</strong> 
                                <a href="uploads/courses/<?php echo htmlspecialchars($edit_course['file_attachment']); ?>" 
                                   target="_blank" class="file-link">
                                    <i class="fas fa-file"></i> <?php echo htmlspecialchars($edit_course['file_attachment']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; align-items: center; margin-top: 30px;">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-<?php echo $edit_course ? 'save' : 'plus'; ?>"></i>
                            <?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>
                        </button>
                        
                        <?php if ($edit_course): ?>
                            <a href="manage-courses-fixed.php" class="cancel-btn">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <h3><i class="fas fa-filter"></i> Filter Courses</h3>
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="filter_type">Training Type</label>
                    <select id="filter_type">
                        <option value="">All Types</option>
                        <option value="Local Training" <?php echo $filter_type == 'Local Training' ? 'selected' : ''; ?>>Local Training</option>
                        <option value="Foreign Training" <?php echo $filter_type == 'Foreign Training' ? 'selected' : ''; ?>>Foreign Training</option>
                        <option value="Unit / Interagency Training" <?php echo $filter_type == 'Unit / Interagency Training' ? 'selected' : ''; ?>>Unit / Interagency Training</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_category">Category</label>
                    <select id="filter_category">
                        <option value="">All Categories</option>
                        <option value="Career Course" <?php echo $filter_category == 'Career Course' ? 'selected' : ''; ?>>Career Course</option>
                        <option value="Functional Courses" <?php echo $filter_category == 'Functional Courses' ? 'selected' : ''; ?>>Functional Courses</option>
                        <option value="Senior Level Courses" <?php echo $filter_category == 'Senior Level Courses' ? 'selected' : ''; ?>>Senior Level Courses</option>
                        <option value="Officer Courses" <?php echo $filter_category == 'Officer Courses' ? 'selected' : ''; ?>>Officer Courses</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_audience">Target Audience</label>
                    <select id="filter_audience">
                        <option value="">All Audiences</option>
                        <option value="Officer" <?php echo $filter_audience == 'Officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="Non-Officer" <?php echo $filter_audience == 'Non-Officer' ? 'selected' : ''; ?>>Non-Officer</option>
                        <option value="Both" <?php echo $filter_audience == 'Both' ? 'selected' : ''; ?>>Both</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button onclick="applyFilters()" class="filter-btn">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
                
                <div class="filter-group">
                    <button onclick="clearFilters()" class="clear-btn">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Courses List -->
        <div class="courses-list">
            <h2><i class="fas fa-list"></i> Course Catalog (<?php echo $courses_result->num_rows; ?> courses)</h2>
            
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
                            <span class="badge badge-<?php echo strtolower(str_replace([' ', '/'], ['-', '-'], $course['training_type'])); ?>">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($course['training_type']); ?>
                            </span>
                            <?php if ($course['category']): ?>
                                <span class="badge" style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); color: #495057;">
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($course['category']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge badge-<?php echo strtolower(str_replace([' ', '-'], ['-', '-'], $course['target_audience'])); ?>">
                                <i class="fas fa-users"></i> <?php echo htmlspecialchars($course['target_audience']); ?>
                            </span>
                            <span class="badge" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404;">
                                <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($course['course_level']); ?>
                            </span>
                        </div>
                        
                        <div class="course-details">
                            <?php if ($course['description']): ?>
                                <p><strong><i class="fas fa-align-left"></i> Description:</strong> <?php echo htmlspecialchars(substr($course['description'], 0, 200)) . (strlen($course['description']) > 200 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            
                            <div class="details-grid">
                                <?php if ($course['duration']): ?>
                                    <p><strong><i class="fas fa-clock"></i> Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                                <?php endif; ?>
                                <p><strong><i class="fas fa-user-friends"></i> Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?> students</p>
                                <?php if ($course['subcategory']): ?>
                                    <p><strong><i class="fas fa-folder-open"></i> Subcategory:</strong> <?php echo htmlspecialchars($course['subcategory']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($course['file_attachment']): ?>
                                <p style="margin-top: 15px;">
                                    <strong><i class="fas fa-paperclip"></i> Attachment:</strong> 
                                    <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                                       target="_blank" class="file-link">
                                        <i class="fas fa-file"></i> <?php echo htmlspecialchars($course['file_attachment']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-actions">
                            <button class="btn-small btn-view" onclick="viewCourse(<?php echo $course['id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn-small btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn-small btn-delete" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>No Courses Found</h3>
                    <p>No courses match the current filters. Try adjusting your search criteria or add new courses.</p>
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
            
            // Clear existing options except the first one
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
            
            let url = 'manage-courses-fixed.php?';
            const params = [];
            
            if (type) params.push('type=' + encodeURIComponent(type));
            if (category) params.push('category=' + encodeURIComponent(category));
            if (audience) params.push('audience=' + encodeURIComponent(audience));
            
            url += params.join('&');
            window.location.href = url;
        }

        function clearFilters() {
            window.location.href = 'manage-courses-fixed.php';
        }

        function viewCourse(id) {
            window.open('course-details.php?id=' + id, '_blank');
        }

        function editCourse(id) {
            window.location.href = 'manage-courses-fixed.php?edit=' + id;
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="course_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize subcategories on page load if editing
        <?php if ($edit_course && $edit_course['category']): ?>
            document.addEventListener('DOMContentLoaded', function() {
                updateSubcategories();
                // Set the selected subcategory
                setTimeout(function() {
                    const subcategorySelect = document.getElementById('subcategory');
                    subcategorySelect.value = '<?php echo htmlspecialchars($edit_course['subcategory']); ?>';
                }, 100);
            });
        <?php endif; ?>

        // Initialize file upload visibility on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFileUpload();
        });
    </script>
</body>
</html>