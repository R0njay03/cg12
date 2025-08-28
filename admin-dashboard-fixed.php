<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Check if courses table has the new structure
$check_columns = $conn->query("SHOW COLUMNS FROM courses LIKE 'training_type'");
$has_new_structure = $check_columns && $check_columns->num_rows > 0;

// Fetch available courses from database
if ($has_new_structure) {
    $courses_query = "SELECT * FROM courses WHERE status = 'Active' ORDER BY training_type, course_name LIMIT 6";
} else {
    $courses_query = "SELECT * FROM courses ORDER BY course_name LIMIT 6";
}
$courses_result = $conn->query($courses_query);

// Get course statistics
if ($has_new_structure) {
    $stats_query = "SELECT 
        COUNT(*) as total_courses,
        SUM(CASE WHEN training_type = 'Local Training' THEN 1 ELSE 0 END) as local_courses,
        SUM(CASE WHEN training_type = 'Foreign Training' THEN 1 ELSE 0 END) as foreign_courses,
        SUM(CASE WHEN training_type = 'Unit / Interagency Training' THEN 1 ELSE 0 END) as unit_courses
        FROM courses WHERE status = 'Active'";
} else {
    $stats_query = "SELECT COUNT(*) as total_courses, 0 as local_courses, 0 as foreign_courses, 0 as unit_courses FROM courses";
}
$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total_courses' => 0, 'local_courses' => 0, 'foreign_courses' => 0, 'unit_courses' => 0];

// Check if users table exists for user management
$users_check = $conn->query("SHOW TABLES LIKE 'users'");
$has_users_table = $users_check && $users_check->num_rows > 0;

if ($has_users_table) {
    $users_count_result = $conn->query("SELECT COUNT(*) as total FROM users");
    $users_count = $users_count_result ? $users_count_result->fetch_assoc()['total'] : 0;
} else {
    $users_count = 1; // At least the current admin user
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | PCG CG-12</title>
  <link rel="stylesheet" href="admin-style-enhanced.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Additional Dashboard Styles */
    .dashboard-header {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .dashboard-header h1 {
      margin: 0;
      font-size: 2.2em;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .dashboard-header p {
      margin: 15px 0 0 0;
      opacity: 0.9;
      font-size: 1.1em;
    }
    
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .action-card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      text-align: center;
      transition: all 0.3s ease;
      border-top: 4px solid #002147;
      position: relative;
      overflow: hidden;
    }
    
    .action-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.5s;
    }
    
    .action-card:hover::before {
      left: 100%;
    }
    
    .action-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .action-card i {
      font-size: 3.5em;
      color: #002147;
      margin-bottom: 20px;
    }
    
    .action-card h3 {
      color: #002147;
      margin-bottom: 15px;
      font-size: 1.4em;
      font-weight: 600;
    }
    
    .action-card p {
      color: #666;
      margin-bottom: 25px;
      line-height: 1.6;
    }
    
    .action-btn {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 12px 30px;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .action-btn:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
    }
    
    .courses-preview {
      background: white;
      padding: 35px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      margin-bottom: 30px;
    }
    
    .courses-preview h2 {
      color: #002147;
      margin-bottom: 25px;
      font-size: 1.8em;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .course-preview-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 25px;
      margin-bottom: 25px;
    }
    
    .course-preview-card {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 25px;
      transition: all 0.3s ease;
      background: #f8f9fa;
    }
    
    .course-preview-card:hover {
      border-color: #002147;
      box-shadow: 0 8px 20px rgba(0,0,0,0.12);
      transform: translateY(-3px);
    }
    
    .course-title {
      color: #002147;
      font-size: 1.2em;
      font-weight: 600;
      margin-bottom: 12px;
      line-height: 1.3;
    }
    
    .course-meta {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
      font-size: 0.9em;
      color: #666;
      flex-wrap: wrap;
    }
    
    .course-meta span {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .course-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .btn-small {
      padding: 8px 15px;
      font-size: 0.85em;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .btn-view {
      background: #17a2b8;
      color: white;
    }
    
    .btn-view:hover {
      background: #138496;
      transform: translateY(-2px);
    }
    
    .btn-edit {
      background: #ffc107;
      color: #212529;
    }
    
    .btn-edit:hover {
      background: #e0a800;
      transform: translateY(-2px);
    }
    
    .btn-pdf {
      background: #dc3545;
      color: white;
    }
    
    .btn-pdf:hover {
      background: #c82333;
      transform: translateY(-2px);
    }
    
    .view-all-btn {
      text-align: center;
      margin-top: 25px;
    }
    
    .view-all-btn a {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 15px 35px;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    
    .view-all-btn a:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
    }
    
    .no-courses {
      text-align: center;
      padding: 60px 40px;
      color: #666;
    }
    
    .no-courses i {
      font-size: 5em;
      color: #ddd;
      margin-bottom: 25px;
    }
    
    .no-courses h3 {
      color: #002147;
      margin-bottom: 15px;
      font-size: 1.5em;
    }
    
    .database-notice {
      background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
      border: 1px solid #ffeaa7;
      color: #856404;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .database-notice strong {
      color: #002147;
    }
    
    .database-notice a {
      color: #002147;
      font-weight: bold;
      text-decoration: none;
      padding: 8px 16px;
      background: rgba(0, 33, 71, 0.1);
      border-radius: 5px;
      margin-left: 10px;
      transition: all 0.3s ease;
    }
    
    .database-notice a:hover {
      background: #002147;
      color: white;
      transform: scale(1.05);
    }
    
    /* PDF Modal Styles */
    .pdf-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.8);
      backdrop-filter: blur(5px);
    }
    
    .pdf-modal-content {
      position: relative;
      margin: 2% auto;
      width: 90%;
      height: 90%;
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .pdf-modal-header {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 20px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .pdf-modal-header h3 {
      margin: 0;
      font-size: 1.3em;
      font-weight: 600;
    }
    
    .pdf-close {
      color: white;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      padding: 5px;
      border-radius: 50%;
    }
    
    .pdf-close:hover {
      color: #c8102e;
      background: rgba(255,255,255,0.1);
      transform: scale(1.1);
    }
    
    .pdf-viewer {
      width: 100%;
      height: calc(100% - 70px);
      border: none;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
    <ul>
      <li><a href="admin-dashboard-fixed.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-courses.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
      <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
      <li><a href="course-catalog.php"><i class="fas fa-book"></i> Course Catalog</a></li>
      <li><a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <h1><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
      <p>PCG CG-12 Education & Training Administration Dashboard</p>
    </div>

    <?php if (!$has_new_structure): ?>
      <div class="database-notice">
        <strong><i class="fas fa-exclamation-triangle"></i> Database Update Required:</strong> 
        <a href="safe_database_update.php"><i class="fas fa-database"></i> Update Database</a>
        for full course management features.
      </div>
    <?php endif; ?>

    <!-- Enhanced Statistics -->
    <div class="enhanced-stats">
      <div class="enhanced-stat-card">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-number"><?php echo $stats['total_courses']; ?></div>
        <div class="stat-label">Total Courses</div>
      </div>
      <div class="enhanced-stat-card">
        <div class="stat-icon"><i class="fas fa-home"></i></div>
        <div class="stat-number"><?php echo $stats['local_courses']; ?></div>
        <div class="stat-label">Local Training</div>
      </div>
      <div class="enhanced-stat-card">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-number"><?php echo $stats['foreign_courses']; ?></div>
        <div class="stat-label">Foreign Training</div>
      </div>
      <div class="enhanced-stat-card">
        <div class="stat-icon"><i class="fas fa-handshake"></i></div>
        <div class="stat-number"><?php echo $stats['unit_courses']; ?></div>
        <div class="stat-label">Unit/Interagency</div>
      </div>
      <div class="enhanced-stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo $users_count; ?></div>
        <div class="stat-label">System Users</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <div class="action-card">
        <i class="fas fa-users"></i>
        <h3>Manage Users</h3>
        <p>Add, edit, or remove system users and manage access permissions for the training system</p>
        <a href="manage-users.html" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Users</a>
      </div>
      <div class="action-card">
        <i class="fas fa-graduation-cap"></i>
        <h3>Manage Courses</h3>
        <p>Create, update, and organize training courses and programs for all personnel categories</p>
        <a href="manage-courses.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Courses</a>
      </div>
      <div class="action-card">
        <i class="fas fa-file-pdf"></i>
        <h3>Course Materials</h3>
        <p>Upload and manage PDF files, documents, and course materials for training programs</p>
        <a href="course-materials.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Materials</a>
      </div>
      <div class="action-card">
        <i class="fas fa-book"></i>
        <h3>Course Catalog</h3>
        <p>Browse and view all available training programs, materials, and course information</p>
        <a href="course-catalog.php" class="action-btn"><i class="fas fa-arrow-right"></i> View Catalog</a>
      </div>
    </div>

    <!-- Available Courses Preview -->
    <div class="courses-preview">
      <h2><i class="fas fa-book-open"></i> Recent Courses</h2>
      
      <?php if ($courses_result && $courses_result->num_rows > 0): ?>
        <div class="course-preview-grid">
          <?php while($course = $courses_result->fetch_assoc()): ?>
            <div class="course-preview-card">
              <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
              
              <div class="course-meta">
                <?php if (isset($course['training_type'])): ?>
                  <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($course['training_type']); ?></span>
                <?php endif; ?>
                <?php if (isset($course['duration']) && $course['duration']): ?>
                  <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration']); ?></span>
                <?php endif; ?>
                <?php if (isset($course['capacity'])): ?>
                  <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($course['capacity']); ?> students</span>
                <?php endif; ?>
              </div>
              
              <?php if (isset($course['description']) && $course['description']): ?>
                <p style="color: #666; font-size: 0.9em; margin-bottom: 15px; line-height: 1.5;">
                  <?php echo htmlspecialchars(substr($course['description'], 0, 120)) . (strlen($course['description']) > 120 ? '...' : ''); ?>
                </p>
              <?php endif; ?>
              
              <div class="course-actions">
                <button class="btn-small btn-view" onclick="viewCourse(<?php echo $course['id']; ?>)">
                  <i class="fas fa-eye"></i> View
                </button>
                <button class="btn-small btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <?php if (isset($course['file_attachment']) && $course['file_attachment']): ?>
                  <button class="btn-small btn-pdf" onclick="viewPDF('<?php echo htmlspecialchars($course['file_attachment']); ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')">
                    <i class="fas fa-file-pdf"></i> PDF
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        
        <div class="view-all-btn">
          <a href="manage-courses.php"><i class="fas fa-list"></i> View All Courses</a>
        </div>
      <?php else: ?>
        <div class="no-courses">
          <i class="fas fa-graduation-cap"></i>
          <h3>No Courses Available</h3>
          <p>Start by adding some courses to the system or update your database structure.</p>
          <a href="manage-courses.php" class="action-btn"><i class="fas fa-plus"></i> Add Courses</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- PDF Modal -->
  <div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
      <div class="pdf-modal-header">
        <h3 id="pdfTitle"><i class="fas fa-file-pdf"></i> Course Material</h3>
        <span class="pdf-close" onclick="closePDFModal()">&times;</span>
      </div>
      <iframe id="pdfViewer" class="pdf-viewer" src=""></iframe>
    </div>
  </div>

  <script>
    function viewCourse(id) {
      window.open('course-details.php?id=' + id, '_blank');
    }

    function editCourse(id) {
      window.location.href = 'manage-courses.php?edit=' + id;
    }

    function viewPDF(filename, courseName) {
      const modal = document.getElementById('pdfModal');
      const viewer = document.getElementById('pdfViewer');
      const title = document.getElementById('pdfTitle');
      
      title.innerHTML = '<i class="fas fa-file-pdf"></i> ' + courseName + ' - Course Material';
      viewer.src = 'uploads/courses/' + filename;
      modal.style.display = 'block';
      
      // Add loading indicator
      viewer.onload = function() {
        console.log('PDF loaded successfully');
      };
      
      viewer.onerror = function() {
        alert('Error loading PDF file. Please check if the file exists.');
        closePDFModal();
      };
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

    // Add smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  </script>
</body>
</html>