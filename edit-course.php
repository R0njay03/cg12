<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage-courses-fixed.php");
    exit();
}

$course_id = $_GET['id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_course') {
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
    $status = $_POST['status'];
    
    $file_attachment = $_POST['existing_file']; // Keep existing file by default
    
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
                // Delete old file if exists
                if ($file_attachment && file_exists($upload_dir . $file_attachment)) {
                    unlink($upload_dir . $file_attachment);
                }
                $file_attachment = $file_name;
            }
        }
    }
    
    // Update course in database
    $stmt = $conn->prepare("UPDATE courses SET course_name=?, course_code=?, training_type=?, category=?, subcategory=?, target_audience=?, course_level=?, description=?, duration=?, capacity=?, prerequisites=?, learning_objectives=?, course_outline=?, file_attachment=?, status=? WHERE id=?");
    $stmt->bind_param("sssssssssisssssi", $course_name, $course_code, $training_type, $category, $subcategory, $target_audience, $course_level, $description, $duration, $capacity, $prerequisites, $learning_objectives, $course_outline, $file_attachment, $status, $course_id);
    
    if ($stmt->execute()) {
        $success_message = "Course updated successfully!";
    } else {
        $error_message = "Error updating course: " . $conn->error;
    }
}

// Fetch course data
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: manage-courses-fixed.php");
    exit();
}

$course = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course | PCG CG-12</title>
    <link rel="stylesheet" href="admin-style-enhanced.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-course-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .course-header {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        .course-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-100px) translateY(-100px); }
        }
        
        .course-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .course-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .edit-form {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 5px solid #002147;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .form-section h3 {
            color: #002147;
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #002147;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }
        
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 3px rgba(0, 33, 71, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .file-upload-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border: 2px dashed #dee2e6;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .file-upload-section:hover {
            border-color: #002147;
            background: #f0f8ff;
        }
        
        .current-file {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .file-icon {
            font-size: 2em;
            color: #dc3545;
        }
        
        .file-info h4 {
            margin: 0 0 5px 0;
            color: #002147;
            font-weight: 600;
        }
        
        .file-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .file-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }
        
        .btn-file {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-remove {
            background: #dc3545;
            color: white;
        }
        
        .btn-file:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .form-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f8f9fa;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px 40px;
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: scale(1.05);
        }
        
        .message {
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
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
        
        .status-toggle {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .status-toggle label {
            margin: 0;
            font-weight: 600;
            color: #002147;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .toggle-switch.active {
            background: #28a745;
        }
        
        .toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .toggle-switch.active .toggle-slider {
            transform: translateX(30px);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .course-header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .course-header h1 {
                font-size: 2em;
                flex-direction: column;
                gap: 10px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .current-file {
                flex-direction: column;
                text-align: center;
            }
            
            .file-actions {
                margin-left: 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        </div>
        <ul>
            <li><a href="admin-dashboard-enhanced.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses-fixed.php" class="active"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="course-catalog.php"><i class="fas fa-book"></i> Course Catalog</a></li>
            <li><a href="#available-courses"><i class="fas fa-list-alt"></i> Available Courses</a></li>
            <li><a href="index-improved.html"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="edit-course-container">
            <!-- Course Header -->
            <div class="course-header">
                <div class="course-header-content">
                    <h1><i class="fas fa-edit"></i> Edit Course</h1>
                    <a href="manage-courses-fixed.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Courses
                    </a>
                </div>
            </div>

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

            <!-- Edit Form -->
            <div class="edit-form">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_course">
                    <input type="hidden" name="existing_file" value="<?php echo htmlspecialchars($course['file_attachment']); ?>">
                    
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="course_name"><i class="fas fa-graduation-cap"></i> Course Name *</label>
                                <input type="text" id="course_name" name="course_name" required 
                                       value="<?php echo htmlspecialchars($course['course_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="course_code"><i class="fas fa-code"></i> Course Code</label>
                                <input type="text" id="course_code" name="course_code" 
                                       value="<?php echo htmlspecialchars($course['course_code']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="training_type"><i class="fas fa-tag"></i> Training Type *</label>
                                <select id="training_type" name="training_type" required onchange="toggleFileUpload()">
                                    <option value="">Select Training Type</option>
                                    <option value="Local Training" <?php echo ($course['training_type'] == 'Local Training') ? 'selected' : ''; ?>>Local Training</option>
                                    <option value="Foreign Training" <?php echo ($course['training_type'] == 'Foreign Training') ? 'selected' : ''; ?>>Foreign Training</option>
                                    <option value="Unit / Interagency Training" <?php echo ($course['training_type'] == 'Unit / Interagency Training') ? 'selected' : ''; ?>>Unit / Interagency Training</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="category"><i class="fas fa-folder"></i> Category</label>
                                <select id="category" name="category" onchange="updateSubcategories()">
                                    <option value="">Select Category</option>
                                    <option value="Career Course" <?php echo ($course['category'] == 'Career Course') ? 'selected' : ''; ?>>Career Course</option>
                                    <option value="Functional Courses" <?php echo ($course['category'] == 'Functional Courses') ? 'selected' : ''; ?>>Functional Courses</option>
                                    <option value="Senior Level Courses" <?php echo ($course['category'] == 'Senior Level Courses') ? 'selected' : ''; ?>>Senior Level Courses</option>
                                    <option value="Officer Courses" <?php echo ($course['category'] == 'Officer Courses') ? 'selected' : ''; ?>>Officer Courses</option>
                                    <option value="International Cooperation" <?php echo ($course['category'] == 'International Cooperation') ? 'selected' : ''; ?>>International Cooperation</option>
                                    <option value="Joint Operations" <?php echo ($course['category'] == 'Joint Operations') ? 'selected' : ''; ?>>Joint Operations</option>
                                    <option value="Regional Cooperation" <?php echo ($course['category'] == 'Regional Cooperation') ? 'selected' : ''; ?>>Regional Cooperation</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="subcategory"><i class="fas fa-folder-open"></i> Subcategory</label>
                                <select id="subcategory" name="subcategory">
                                    <option value="">Select Subcategory</option>
                                    <?php if ($course['subcategory']): ?>
                                        <option value="<?php echo htmlspecialchars($course['subcategory']); ?>" selected>
                                            <?php echo htmlspecialchars($course['subcategory']); ?>
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="target_audience"><i class="fas fa-users"></i> Target Audience *</label>
                                <select id="target_audience" name="target_audience" required>
                                    <option value="">Select Target Audience</option>
                                    <option value="Officer" <?php echo ($course['target_audience'] == 'Officer') ? 'selected' : ''; ?>>Officer</option>
                                    <option value="Non-Officer" <?php echo ($course['target_audience'] == 'Non-Officer') ? 'selected' : ''; ?>>Non-Officer</option>
                                    <option value="Both" <?php echo ($course['target_audience'] == 'Both') ? 'selected' : ''; ?>>Both</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="course_level"><i class="fas fa-layer-group"></i> Course Level</label>
                                <select id="course_level" name="course_level">
                                    <option value="Basic" <?php echo ($course['course_level'] == 'Basic') ? 'selected' : ''; ?>>Basic</option>
                                    <option value="Intermediate" <?php echo ($course['course_level'] == 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="Advanced" <?php echo ($course['course_level'] == 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                                    <option value="Senior" <?php echo ($course['course_level'] == 'Senior') ? 'selected' : ''; ?>>Senior</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration"><i class="fas fa-clock"></i> Duration</label>
                                <input type="text" id="duration" name="duration" placeholder="e.g., 8 weeks" 
                                       value="<?php echo htmlspecialchars($course['duration']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="capacity"><i class="fas fa-user-friends"></i> Capacity</label>
                                <input type="number" id="capacity" name="capacity" 
                                       value="<?php echo $course['capacity']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Details -->
                    <div class="form-section">
                        <h3><i class="fas fa-align-left"></i> Course Details</h3>
                        
                        <div class="form-group">
                            <label for="description"><i class="fas fa-align-left"></i> Course Description</label>
                            <textarea id="description" name="description" placeholder="Detailed course description..."><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="prerequisites"><i class="fas fa-list-check"></i> Prerequisites</label>
                            <textarea id="prerequisites" name="prerequisites" placeholder="Course prerequisites and requirements..."><?php echo htmlspecialchars($course['prerequisites']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="learning_objectives"><i class="fas fa-bullseye"></i> Learning Objectives</label>
                            <textarea id="learning_objectives" name="learning_objectives" placeholder="Learning objectives and outcomes..."><?php echo htmlspecialchars($course['learning_objectives']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="course_outline"><i class="fas fa-list-ol"></i> Course Outline</label>
                            <textarea id="course_outline" name="course_outline" placeholder="Detailed course outline and curriculum..."><?php echo htmlspecialchars($course['course_outline']); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- File Management -->
                    <div class="form-section">
                        <h3><i class="fas fa-file-upload"></i> Course Materials</h3>
                        
                        <?php if ($course['file_attachment']): ?>
                            <div class="current-file">
                                <div class="file-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="file-info">
                                    <h4>Current File</h4>
                                    <p><?php echo htmlspecialchars($course['file_attachment']); ?></p>
                                </div>
                                <div class="file-actions">
                                    <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                                       target="_blank" class="btn-file btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="btn-file btn-remove" onclick="removeFile()">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-upload-section" id="file_upload_group" style="display: <?php echo ($course['training_type'] == 'Foreign Training' || $course['training_type'] == 'Unit / Interagency Training') ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label for="course_file"><i class="fas fa-file-upload"></i> Upload New Course File</label>
                                <input type="file" id="course_file" name="course_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small style="color: #666; margin-top: 10px; display: block;">
                                    Upload course materials, brochures, or related documents (PDF, Images, Documents).<br>
                                    Only required for Foreign Training and Unit/Interagency Training courses.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Status -->
                    <div class="form-section">
                        <h3><i class="fas fa-toggle-on"></i> Course Status</h3>
                        <div class="status-toggle">
                            <label for="status">Course Status:</label>
                            <div class="toggle-switch <?php echo ($course['status'] == 'Active') ? 'active' : ''; ?>" onclick="toggleStatus()">
                                <div class="toggle-slider"></div>
                            </div>
                            <span id="status-text"><?php echo $course['status']; ?></span>
                            <input type="hidden" id="status" name="status" value="<?php echo $course['status']; ?>">
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Update Course
                        </button>
                        <a href="manage-courses-fixed.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        function toggleStatus() {
            const toggle = document.querySelector('.toggle-switch');
            const statusInput = document.getElementById('status');
            const statusText = document.getElementById('status-text');
            
            toggle.classList.toggle('active');
            
            if (toggle.classList.contains('active')) {
                statusInput.value = 'Active';
                statusText.textContent = 'Active';
            } else {
                statusInput.value = 'Inactive';
                statusText.textContent = 'Inactive';
            }
        }

        function removeFile() {
            if (confirm('Are you sure you want to remove the current file?')) {
                document.querySelector('input[name="existing_file"]').value = '';
                document.querySelector('.current-file').style.display = 'none';
            }
        }

        // Initialize subcategories on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSubcategories();
            // Set the selected subcategory
            setTimeout(function() {
                const subcategorySelect = document.getElementById('subcategory');
                subcategorySelect.value = '<?php echo htmlspecialchars($course['subcategory']); ?>';
            }, 100);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const courseName = document.getElementById('course_name').value.trim();
            const trainingType = document.getElementById('training_type').value;
            const targetAudience = document.getElementById('target_audience').value;
            
            if (!courseName || !trainingType || !targetAudience) {
                e.preventDefault();
                alert('Please fill in all required fields (Course Name, Training Type, Target Audience).');
                return false;
            }
        });
    </script>
</body>
</html>