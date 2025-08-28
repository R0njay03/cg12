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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .edit-course-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .course-header {
            background: linear-gradient(135deg, #002147 0%, #1e3a8a 25%, #3b82f6 50%, #0ea5e9 75%, #06b6d4 100%);
            color: white;
            padding: 40px;
            border-radius: 25px;
            margin-bottom: 40px;
            box-shadow: 
                0 15px 40px rgba(0,0,0,0.15),
                0 0 0 1px rgba(255,255,255,0.1) inset;
            position: relative;
            overflow: hidden;
        }
        
        .course-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(200,16,46,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(242,183,5,0.1) 0%, transparent 50%);
            animation: float 30s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(0) translateY(0) rotate(0deg); }
            100% { transform: translateX(-150px) translateY(-150px) rotate(360deg); }
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
            font-size: 2.8em;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 25px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }
        
        .course-header h1 i {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 18px;
            border-radius: 20px;
            transition: all 0.4s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .course-header h1:hover i {
            background: rgba(200, 16, 46, 0.3);
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 12px 30px rgba(200, 16, 46, 0.2);
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }
        
        .edit-form {
            background: linear-gradient(135deg, white 0%, #f8fafc 100%);
            padding: 50px;
            border-radius: 25px;
            box-shadow: 
                0 20px 40px rgba(0,0,0,0.1),
                0 0 0 1px rgba(255,255,255,0.5) inset;
            border-top: 6px solid #002147;
            position: relative;
            overflow: hidden;
        }
        
        .edit-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.8s ease;
        }
        
        .edit-form:hover::before {
            left: 100%;
        }
        
        .form-section {
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }
        
        .form-section h3 {
            color: #1e293b;
            font-size: 1.6em;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f1f5f9;
        }
        
        .form-section h3 i {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.8em;
            box-shadow: 0 4px 15px rgba(0, 33, 71, 0.3);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1em;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 18px 22px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 4px rgba(0, 33, 71, 0.1);
            transform: translateY(-3px);
        }
        
        .form-group textarea {
            height: 140px;
            resize: vertical;
        }
        
        .file-upload-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px;
            border-radius: 20px;
            border: 3px dashed #cbd5e1;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .file-upload-section:hover {
            border-color: #002147;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
        }
        
        .current-file {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .file-icon {
            font-size: 2.5em;
            color: #dc3545;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            padding: 15px;
            border-radius: 15px;
        }
        
        .file-info h4 {
            margin: 0 0 8px 0;
            color: #1e293b;
            font-weight: 700;
            font-size: 1.1em;
        }
        
        .file-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.95em;
        }
        
        .file-actions {
            margin-left: auto;
            display: flex;
            gap: 12px;
        }
        
        .btn-file {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .btn-remove {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-file:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .form-actions {
            display: flex;
            gap: 25px;
            justify-content: center;
            margin-top: 50px;
            padding-top: 40px;
            border-top: 3px solid #f1f5f9;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 18px 45px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 25px rgba(0, 33, 71, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 18px 45px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 15px 35px rgba(200, 16, 46, 0.4);
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 15px 35px rgba(90, 98, 104, 0.4);
        }
        
        .message {
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 35px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .message::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.8s ease;
        }
        
        .message:hover::before {
            left: 100%;
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
            gap: 20px;
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .status-toggle label {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1em;
        }
        
        .toggle-switch {
            position: relative;
            width: 70px;
            height: 35px;
            background: #cbd5e1;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) inset;
        }
        
        .toggle-switch.active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .toggle-slider {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 27px;
            height: 27px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .toggle-switch.active .toggle-slider {
            transform: translateX(35px);
        }
        
        #status-text {
            font-weight: 700;
            font-size: 1.1em;
            color: #1e293b;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(180deg, #002147 0%, #001a3a 50%, #001122 100%);
            box-shadow: 
                4px 0 25px rgba(0,0,0,0.3),
                inset -1px 0 0 rgba(255,255,255,0.1);
            position: relative;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.02)"/><circle cx="80" cy="40" r="0.5" fill="rgba(255,255,255,0.01)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.015)"/></svg>') repeat;
            animation: sparkle 40s infinite linear;
            pointer-events: none;
        }
        
        @keyframes sparkle {
            0% { opacity: 0.3; transform: translateX(0) translateY(0); }
            50% { opacity: 0.6; transform: translateX(-50px) translateY(-50px); }
            100% { opacity: 0.3; transform: translateX(-100px) translateY(-100px); }
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 35px;
            padding: 30px 20px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.2em;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #fff;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
            transition: all 0.4s ease;
            position: relative;
        }
        
        .sidebar-header h2::before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #c8102e, #f2b705);
            transition: all 0.4s ease;
            transform: translateX(-50%);
        }
        
        .sidebar-header:hover h2::before {
            width: 100%;
        }
        
        .sidebar-header h2 i {
            margin-right: 10px;
            color: #c8102e;
            transition: all 0.4s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        .sidebar-header:hover h2 {
            color: #f2b705;
            transform: scale(1.05);
            text-shadow: 2px 2px 12px rgba(242, 183, 5, 0.5);
        }
        
        .sidebar-header:hover h2 i {
            color: #f2b705;
            transform: rotate(15deg) scale(1.1);
        }
        
        /* Enhanced Sidebar Navigation */
        .sidebar ul {
            position: relative;
            z-index: 2;
        }
        
        .sidebar ul li a {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border-radius: 0 25px 25px 0;
            margin: 2px 0;
        }
        
        .sidebar ul li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.6s ease;
        }
        
        .sidebar ul li a:hover::before {
            left: 100%;
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: linear-gradient(135deg, rgba(200, 16, 46, 0.9) 0%, rgba(242, 183, 5, 0.7) 100%);
            transform: translateX(8px);
            box-shadow: 
                0 6px 20px rgba(200, 16, 46, 0.4),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        .sidebar ul li a i {
            transition: all 0.4s ease;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar ul li a:hover i {
            transform: scale(1.3) rotate(5deg);
            color: #f2b705;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .course-header-content {
                flex-direction: column;
                gap: 25px;
                text-align: center;
            }
            
            .course-header h1 {
                font-size: 2.2em;
                flex-direction: column;
                gap: 15px;
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
            
            .edit-form {
                padding: 30px 25px;
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
            <li><a href="admin-dashboard-enhanced-v2.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses-enhanced.php" class="active"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
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
                    <a href="manage-courses-enhanced.php" class="back-btn">
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
                                <small style="color: #64748b; margin-top: 15px; display: block; font-size: 0.9em; line-height: 1.5;">
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
                        <a href="manage-courses-enhanced.php" class="btn-secondary">
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