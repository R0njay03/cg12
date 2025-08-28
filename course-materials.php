<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Handle file upload
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'upload_material') {
        $course_id = $_POST['course_id'];
        
        if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] == 0) {
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
                    // Update course with file attachment
                    $stmt = $conn->prepare("UPDATE courses SET file_attachment = ? WHERE id = ?");
                    $stmt->bind_param("si", $file_name, $course_id);
                    
                    if ($stmt->execute()) {
                        $message = "Course material uploaded successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error updating course: " . $conn->error;
                        $message_type = "error";
                    }
                } else {
                    $message = "Error uploading file!";
                    $message_type = "error";
                }
            } else {
                $message = "Invalid file type. Only PDF, images, and documents are allowed.";
                $message_type = "error";
            }
        } else {
            $message = "Please select a file to upload.";
            $message_type = "error";
        }
    }
}

// Fetch courses
$courses_query = "SELECT * FROM courses ORDER BY course_name";
$courses_result = $conn->query($courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials | PCG CG-12</title>
    <link rel="stylesheet" href="admin-style-enhanced.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .material-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-top: 4px solid #002147;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .material-title {
            color: #002147;
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .material-info {
            margin-bottom: 20px;
            color: #666;
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .upload-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px dashed #dee2e6;
        }
        
        .upload-form.active {
            border-color: #002147;
            background: #f0f8ff;
        }
        
        .file-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .btn-upload {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .btn-upload:hover {
            background: #218838;
        }
        
        .btn-view-pdf {
            background: #dc3545;
            color: white;
        }
        
        .btn-view-pdf:hover {
            background: #c82333;
        }
        
        .btn-toggle-upload {
            background: #17a2b8;
            color: white;
        }
        
        .btn-toggle-upload:hover {
            background: #138496;
        }
        
        .file-status {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .status-has-file {
            background: #28a745;
            color: white;
        }
        
        .status-no-file {
            background: #ffc107;
            color: #212529;
        }
        
        .pdf-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .pdf-modal-content {
            position: relative;
            margin: 2% auto;
            width: 90%;
            height: 90%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .pdf-modal-header {
            background: #002147;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .pdf-close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .pdf-close:hover {
            color: #c8102e;
        }
        
        .pdf-viewer {
            width: 100%;
            height: calc(100% - 60px);
            border: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        <ul>
            <li><a href="admin-dashboard-enhanced.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php" class="active"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h1><i class="fas fa-file-pdf"></i> Course Materials Management</h1>
            <p>Upload and manage PDF files and course materials</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="materials-grid">
            <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                <?php while($course = $courses_result->fetch_assoc()): ?>
                    <div class="material-card">
                        <div class="material-title">
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </div>
                        
                        <div class="file-status">
                            <?php if (isset($course['file_attachment']) && $course['file_attachment']): ?>
                                <div class="status-icon status-has-file">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span style="color: #28a745; font-weight: 600;">Material Available</span>
                            <?php else: ?>
                                <div class="status-icon status-no-file">
                                    <i class="fas fa-exclamation"></i>
                                </div>
                                <span style="color: #ffc107; font-weight: 600;">No Material</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="material-info">
                            <?php if (isset($course['course_code']) && $course['course_code']): ?>
                                <p><strong>Code:</strong> <?php echo htmlspecialchars($course['course_code']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($course['training_type'])): ?>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($course['training_type']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($course['duration']) && $course['duration']): ?>
                                <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($course['file_attachment']) && $course['file_attachment']): ?>
                                <p><strong>File:</strong> <?php echo htmlspecialchars($course['file_attachment']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="material-actions">
                            <?php if (isset($course['file_attachment']) && $course['file_attachment']): ?>
                                <button class="btn-view-pdf" onclick="viewPDF('<?php echo htmlspecialchars($course['file_attachment']); ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')">
                                    <i class="fas fa-eye"></i> View PDF
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn-toggle-upload" onclick="toggleUpload(<?php echo $course['id']; ?>)">
                                <i class="fas fa-upload"></i> 
                                <?php echo (isset($course['file_attachment']) && $course['file_attachment']) ? 'Replace' : 'Upload'; ?> Material
                            </button>
                        </div>
                        
                        <div class="upload-form" id="upload-<?php echo $course['id']; ?>" style="display: none;">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_material">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                
                                <label for="file-<?php echo $course['id']; ?>">
                                    <i class="fas fa-file"></i> Select Course Material (PDF, Images, Documents)
                                </label>
                                <input type="file" id="file-<?php echo $course['id']; ?>" name="course_file" class="file-input" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                
                                <button type="submit" class="btn-upload">
                                    <i class="fas fa-upload"></i> Upload Material
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>No Courses Available</h3>
                    <p>Add some courses first to manage their materials.</p>
                    <a href="manage-courses.php" style="color: #002147; font-weight: bold;">Manage Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PDF Modal -->
    <div id="pdfModal" class="pdf-modal">
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3 id="pdfTitle">Course Material</h3>
                <span class="pdf-close" onclick="closePDFModal()">&times;</span>
            </div>
            <iframe id="pdfViewer" class="pdf-viewer" src=""></iframe>
        </div>
    </div>

    <script>
        function toggleUpload(courseId) {
            const uploadForm = document.getElementById('upload-' + courseId);
            if (uploadForm.style.display === 'none') {
                uploadForm.style.display = 'block';
                uploadForm.classList.add('active');
            } else {
                uploadForm.style.display = 'none';
                uploadForm.classList.remove('active');
            }
        }

        function viewPDF(filename, courseName) {
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            const title = document.getElementById('pdfTitle');
            
            title.textContent = courseName + ' - Course Material';
            viewer.src = 'uploads/courses/' + filename;
            modal.style.display = 'block';
        }

        function closePDFModal() {
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            
            modal.style.display = 'none';
            viewer.src = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('pdfModal');
            if (event.target == modal) {
                closePDFModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePDFModal();
            }
        });
    </script>
</body>
</html>