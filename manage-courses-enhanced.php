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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Enhanced PCG Logo and Sidebar Styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
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
        
        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 25px;
        }
        
        .sidebar-logo {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            border: 5px solid #c8102e;
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            padding: 10px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 10px 30px rgba(0,0,0,0.3),
                0 0 0 2px rgba(255,255,255,0.1) inset,
                0 0 25px rgba(200, 16, 46, 0.4),
                0 0 50px rgba(200, 16, 46, 0.2);
            position: relative;
            z-index: 3;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }
        
        .sidebar-logo:hover {
            transform: scale(1.2) rotate(360deg);
            border-color: #f2b705;
            box-shadow: 
                0 20px 50px rgba(0,0,0,0.4),
                0 0 0 3px rgba(255,255,255,0.2) inset,
                0 0 40px rgba(242, 183, 5, 0.8),
                0 0 80px rgba(200, 16, 46, 0.6),
                0 0 120px rgba(242, 183, 5, 0.4);
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.3)) brightness(1.2);
        }
        
        .logo-glow {
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200, 16, 46, 0.3) 0%, rgba(242, 183, 5, 0.2) 50%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
            z-index: 1;
        }
        
        .logo-glow::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, rgba(200, 16, 46, 0.1), rgba(242, 183, 5, 0.1), rgba(200, 16, 46, 0.1));
            animation: rotate 8s linear infinite;
            transform: translate(-50%, -50%);
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.7;
            }
            50% {
                transform: scale(1.15);
                opacity: 1;
            }
        }
        
        @keyframes rotate {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
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
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
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
        
        .sidebar ul li a::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            width: 0;
            height: 0;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            transform: translateY(-50%);
        }
        
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: linear-gradient(135deg, rgba(200, 16, 46, 0.9) 0%, rgba(242, 183, 5, 0.7) 100%);
            transform: translateX(8px);
            box-shadow: 
                0 6px 20px rgba(200, 16, 46, 0.4),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }
        
        .sidebar ul li a:hover::after,
        .sidebar ul li a.active::after {
            border-left-color: rgba(255,255,255,0.8);
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
        
        /* Enhanced Form Styles */
        .course-form {
            background: linear-gradient(135deg, white 0%, #f8fafc 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 
                0 15px 35px rgba(0,0,0,0.1),
                0 0 0 1px rgba(255,255,255,0.5) inset;
            margin-bottom: 35px;
            border-top: 6px solid #002147;
            position: relative;
            overflow: hidden;
        }
        
        .course-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.8s ease;
        }
        
        .course-form:hover::before {
            left: 100%;
        }
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 30px;
            color: #002147;
            position: relative;
            z-index: 2;
        }
        
        .form-header i {
            font-size: 1.8em;
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 33, 71, 0.3);
        }
        
        .form-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #002147;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1em;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: white;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 4px rgba(0, 33, 71, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            height: 130px;
            resize: vertical;
        }
        
        /* Enhanced Course Cards */
        .course-card {
            background: linear-gradient(135deg, white 0%, #f8fafc 100%);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 
                0 15px 35px rgba(0,0,0,0.08),
                0 0 0 1px rgba(255,255,255,0.5) inset;
            margin-bottom: 30px;
            border-left: 6px solid #002147;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            transition: left 0.8s ease;
        }
        
        .course-card:hover::before {
            left: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.15),
                0 0 0 1px rgba(255,255,255,0.8) inset;
        }
        
        .course-title {
            color: #1e293b;
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
        }
        
        .course-badges {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.8em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .badge-local-training { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; }
        .badge-foreign-training { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); color: #0c5460; }
        .badge-unit-interagency-training { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404; }
        .badge-officer { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
        .badge-non-officer { background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%); color: #383d41; }
        .badge-both { background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%); color: #004085; }
        
        /* Enhanced Buttons */
        .btn-small {
            padding: 10px 18px;
            font-size: 0.9em;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
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
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }
        
        .toggle-form {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 18px 35px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 1.1em;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 25px rgba(0, 33, 71, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .toggle-form:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 15px 35px rgba(200, 16, 46, 0.4);
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 25px rgba(0, 33, 71, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 15px 35px rgba(200, 16, 46, 0.4);
        }
        
        /* Enhanced Messages */
        .message {
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar-logo {
                width: 70px;
                height: 70px;
            }
            
            .sidebar-header h2 {
                font-size: 1em;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .course-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="pcg_logo.png" alt="PCG Logo" class="sidebar-logo" />
                <div class="logo-glow"></div>
            </div>
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
                    
                    <div style="display: flex; align-items: center; margin-top: 40px; gap: 20px;">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-<?php echo $edit_course ? 'save' : 'plus'; ?>"></i>
                            <?php echo $edit_course ? 'Update Course' : 'Add Course'; ?>
                        </button>
                        
                        <?php if ($edit_course): ?>
                            <a href="manage-courses-enhanced.php" class="cancel-btn" style="background: #6c757d; color: white; padding: 18px 40px; border-radius: 30px; text-decoration: none; font-weight: 700; transition: all 0.3s ease;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="filters" style="background: linear-gradient(135deg, white 0%, #f8fafc 100%); padding: 30px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); margin-bottom: 30px; border-top: 6px solid #c8102e;">
            <h3 style="color: #002147; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 1.5em; font-weight: 700;">
                <i class="fas fa-filter"></i> Filter Courses
            </h3>
            <div class="filters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; align-items: end;">
                <div class="filter-group">
                    <label for="filter_type" style="font-weight: 600; color: #002147; font-size: 1em; margin-bottom: 8px; display: block;">Training Type</label>
                    <select id="filter_type" style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">All Types</option>
                        <option value="Local Training" <?php echo $filter_type == 'Local Training' ? 'selected' : ''; ?>>Local Training</option>
                        <option value="Foreign Training" <?php echo $filter_type == 'Foreign Training' ? 'selected' : ''; ?>>Foreign Training</option>
                        <option value="Unit / Interagency Training" <?php echo $filter_type == 'Unit / Interagency Training' ? 'selected' : ''; ?>>Unit / Interagency Training</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_category" style="font-weight: 600; color: #002147; font-size: 1em; margin-bottom: 8px; display: block;">Category</label>
                    <select id="filter_category" style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">All Categories</option>
                        <option value="Career Course" <?php echo $filter_category == 'Career Course' ? 'selected' : ''; ?>>Career Course</option>
                        <option value="Functional Courses" <?php echo $filter_category == 'Functional Courses' ? 'selected' : ''; ?>>Functional Courses</option>
                        <option value="Senior Level Courses" <?php echo $filter_category == 'Senior Level Courses' ? 'selected' : ''; ?>>Senior Level Courses</option>
                        <option value="Officer Courses" <?php echo $filter_category == 'Officer Courses' ? 'selected' : ''; ?>>Officer Courses</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_audience" style="font-weight: 600; color: #002147; font-size: 1em; margin-bottom: 8px; display: block;">Target Audience</label>
                    <select id="filter_audience" style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px;">
                        <option value="">All Audiences</option>
                        <option value="Officer" <?php echo $filter_audience == 'Officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="Non-Officer" <?php echo $filter_audience == 'Non-Officer' ? 'selected' : ''; ?>>Non-Officer</option>
                        <option value="Both" <?php echo $filter_audience == 'Both' ? 'selected' : ''; ?>>Both</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button onclick="applyFilters()" style="background: linear-gradient(135deg, #002147 0%, #004080 100%); color: white; padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
                
                <div class="filter-group">
                    <button onclick="clearFilters()" style="background: #6c757d; color: white; padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Courses List -->
        <div class="courses-list">
            <h2 style="color: #002147; font-size: 1.8em; font-weight: 700; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-list"></i> Course Catalog (<?php echo $courses_result->num_rows; ?> courses)
            </h2>
            
            <?php if ($courses_result->num_rows > 0): ?>
                <?php while($course = $courses_result->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="course-header">
                            <div>
                                <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <?php if ($course['course_code']): ?>
                                    <div class="course-code" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #64748b; padding: 6px 15px; border-radius: 20px; font-size: 0.85em; font-weight: 600; display: inline-block; margin-top: 8px;"><?php echo htmlspecialchars($course['course_code']); ?></div>
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
                                <p style="margin-bottom: 15px; line-height: 1.6; color: #64748b;"><strong style="color: #1e293b;"><i class="fas fa-align-left"></i> Description:</strong> <?php echo htmlspecialchars(substr($course['description'], 0, 200)) . (strlen($course['description']) > 200 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            
                            <div class="details-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; padding: 20px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px;">
                                <?php if ($course['duration']): ?>
                                    <p style="margin: 0; color: #64748b; font-weight: 500;"><strong style="color: #1e293b;"><i class="fas fa-clock"></i> Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                                <?php endif; ?>
                                <p style="margin: 0; color: #64748b; font-weight: 500;"><strong style="color: #1e293b;"><i class="fas fa-user-friends"></i> Capacity:</strong> <?php echo htmlspecialchars($course['capacity']); ?> students</p>
                                <?php if ($course['subcategory']): ?>
                                    <p style="margin: 0; color: #64748b; font-weight: 500;"><strong style="color: #1e293b;"><i class="fas fa-folder-open"></i> Subcategory:</strong> <?php echo htmlspecialchars($course['subcategory']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($course['file_attachment']): ?>
                                <p style="margin-top: 20px;">
                                    <strong style="color: #1e293b;"><i class="fas fa-paperclip"></i> Attachment:</strong> 
                                    <a href="uploads/courses/<?php echo htmlspecialchars($course['file_attachment']); ?>" 
                                       target="_blank" style="color: #002147; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 8px 15px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 8px; transition: all 0.3s ease; margin-left: 10px;">
                                        <i class="fas fa-file"></i> <?php echo htmlspecialchars($course['file_attachment']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-actions" style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 25px;">
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
                <div class="no-data" style="text-align: center; padding: 80px 40px; background: linear-gradient(135deg, white 0%, #f8fafc 100%); border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08);">
                    <i class="fas fa-graduation-cap" style="font-size: 5em; color: #e2e8f0; margin-bottom: 25px;"></i>
                    <h3 style="color: #002147; margin-bottom: 15px; font-size: 1.5em; font-weight: 700;">No Courses Found</h3>
                    <p style="color: #64748b; font-size: 1.1em;">No courses match the current filters. Try adjusting your search criteria or add new courses.</p>
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
            
            let url = 'manage-courses-enhanced.php?';
            const params = [];
            
            if (type) params.push('type=' + encodeURIComponent(type));
            if (category) params.push('category=' + encodeURIComponent(category));
            if (audience) params.push('audience=' + encodeURIComponent(audience));
            
            url += params.join('&');
            window.location.href = url;
        }

        function clearFilters() {
            window.location.href = 'manage-courses-enhanced.php';
        }

        function viewCourse(id) {
            window.open('course-details.php?id=' + id, '_blank');
        }

        function editCourse(id) {
            window.location.href = 'manage-courses-enhanced.php?edit=' + id;
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

        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.course-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>