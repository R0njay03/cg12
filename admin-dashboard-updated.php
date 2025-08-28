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
    $courses_query = "SELECT * FROM courses WHERE status = 'Active' ORDER BY training_type, course_name LIMIT 8";
} else {
    $courses_query = "SELECT * FROM courses ORDER BY course_name LIMIT 8";
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* Enhanced Dashboard Styles with Modern Design */
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      min-height: 100vh;
    }
    
    .main-content {
      background: transparent;
    }
    
    /* Ultra Modern Dashboard Header */
    .dashboard-header {
      background: linear-gradient(135deg, #002147 0%, #1e3a8a 25%, #3b82f6 50%, #0ea5e9 75%, #06b6d4 100%);
      color: white;
      padding: 60px 50px;
      border-radius: 30px;
      margin-bottom: 60px;
      box-shadow: 
        0 25px 50px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.1) inset,
        0 1px 0 rgba(255,255,255,0.2) inset;
      position: relative;
      overflow: hidden;
    }
    
    .dashboard-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(200,16,46,0.12) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(242,183,5,0.1) 0%, transparent 50%),
        conic-gradient(from 0deg at 50% 50%, rgba(255,255,255,0.05) 0deg, transparent 60deg, rgba(255,255,255,0.05) 120deg, transparent 180deg);
      animation: float 40s infinite linear;
    }
    
    .dashboard-header::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.06)"/><circle cx="80" cy="40" r="0.5" fill="rgba(255,255,255,0.04)"/><circle cx="40" cy="80" r="1.5" fill="rgba(255,255,255,0.05)"/><circle cx="60" cy="10" r="0.8" fill="rgba(255,255,255,0.03)"/><circle cx="10" cy="70" r="1.2" fill="rgba(255,255,255,0.04)"/></svg>') repeat;
      animation: sparkle 35s infinite linear;
    }
    
    @keyframes float {
      0% { transform: translateX(0) translateY(0) rotate(0deg); }
      100% { transform: translateX(-150px) translateY(-150px) rotate(360deg); }
    }
    
    @keyframes sparkle {
      0% { opacity: 0.4; transform: translateX(0) translateY(0); }
      50% { opacity: 0.8; transform: translateX(-75px) translateY(-75px); }
      100% { opacity: 0.4; transform: translateX(-150px) translateY(-150px); }
    }
    
    .dashboard-header-content {
      position: relative;
      z-index: 3;
      display: flex;
      align-items: center;
      gap: 40px;
    }
    
    .header-logo {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      border: 6px solid rgba(255,255,255,0.25);
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      padding: 15px;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 
        0 15px 35px rgba(0,0,0,0.2),
        0 0 0 1px rgba(255,255,255,0.1) inset;
    }
    
    .header-logo:hover {
      transform: rotate(360deg) scale(1.15);
      border-color: #c8102e;
      box-shadow: 
        0 0 50px rgba(200, 16, 46, 0.7), 
        0 20px 40px rgba(0,0,0,0.3),
        0 0 0 1px rgba(255,255,255,0.2) inset;
      background: rgba(255,255,255,0.25);
    }
    
    .header-text h1 {
      margin: 0 0 20px 0;
      font-size: 3.2em;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 30px;
      text-shadow: 2px 2px 12px rgba(0,0,0,0.3);
      letter-spacing: -1px;
      line-height: 1.1;
    }
    
    .header-text h1 i {
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      padding: 18px;
      border-radius: 20px;
      transition: all 0.4s ease;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .header-text h1:hover i {
      background: rgba(200, 16, 46, 0.3);
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 12px 30px rgba(200, 16, 46, 0.2);
    }
    
    .header-text p {
      margin: 0;
      opacity: 0.95;
      font-size: 1.4em;
      font-weight: 500;
      text-shadow: 1px 1px 6px rgba(0,0,0,0.2);
      letter-spacing: 0.5px;
    }
    
    /* Ultra Modern Statistics Cards */
    .enhanced-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 35px;
      margin-bottom: 60px;
    }
    
    .enhanced-stat-card {
      background: linear-gradient(135deg, white 0%, #f8fafc 100%);
      padding: 40px 35px;
      border-radius: 25px;
      box-shadow: 
        0 20px 40px rgba(0,0,0,0.08),
        0 0 0 1px rgba(255,255,255,0.5) inset;
      text-align: center;
      position: relative;
      overflow: hidden;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      backdrop-filter: blur(10px);
    }
    
    .enhanced-stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: var(--card-gradient, linear-gradient(90deg, #002147, #c8102e));
      border-radius: 25px 25px 0 0;
    }
    
    .enhanced-stat-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
      transition: left 0.8s ease;
    }
    
    .enhanced-stat-card:hover::after {
      left: 100%;
    }
    
    .enhanced-stat-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 
        0 30px 60px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.8) inset;
    }
    
    .stat-icon {
      font-size: 4em;
      color: var(--icon-color, #002147);
      margin-bottom: 25px;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    
    .enhanced-stat-card:hover .stat-icon {
      color: var(--icon-hover-color, #c8102e);
      transform: scale(1.2) rotate(360deg);
      filter: drop-shadow(0 8px 16px rgba(200, 16, 46, 0.3));
    }
    
    .stat-number {
      font-size: 3.8em;
      font-weight: 800;
      color: var(--number-color, #c8102e);
      margin-bottom: 15px;
      transition: all 0.4s ease;
      line-height: 1;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .enhanced-stat-card:hover .stat-number {
      color: var(--number-hover-color, #002147);
      transform: scale(1.1);
      text-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .stat-label {
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 1em;
      letter-spacing: 1.5px;
      line-height: 1.4;
    }
    
    /* Modern Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 35px;
      margin-bottom: 60px;
    }
    
    .action-card {
      background: linear-gradient(135deg, white 0%, #f8fafc 100%);
      padding: 40px;
      border-radius: 25px;
      box-shadow: 
        0 20px 40px rgba(0,0,0,0.08),
        0 0 0 1px rgba(255,255,255,0.5) inset;
      text-align: center;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border-top: 6px solid var(--action-color, #002147);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .action-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
      transition: left 0.8s ease;
    }
    
    .action-card:hover::before {
      left: 100%;
    }
    
    .action-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 
        0 30px 60px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.8) inset;
    }
    
    .action-card i {
      font-size: 4.5em;
      color: var(--action-color, #002147);
      margin-bottom: 30px;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    
    .action-card:hover i {
      color: var(--action-hover-color, #c8102e);
      transform: scale(1.2) rotate(360deg);
      filter: drop-shadow(0 8px 16px rgba(200, 16, 46, 0.3));
    }
    
    .action-card h3 {
      color: #1e293b;
      margin-bottom: 20px;
      font-size: 1.6em;
      font-weight: 700;
      line-height: 1.3;
    }
    
    .action-card p {
      color: #64748b;
      margin-bottom: 35px;
      line-height: 1.7;
      font-size: 1.05em;
    }
    
    .action-btn {
      background: linear-gradient(135deg, var(--action-color, #002147) 0%, var(--action-dark, #004080) 100%);
      color: white;
      padding: 18px 35px;
      text-decoration: none;
      border-radius: 30px;
      font-weight: 700;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: inline-flex;
      align-items: center;
      gap: 12px;
      font-size: 1.05em;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .action-btn:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.08) translateY(-2px);
      box-shadow: 0 15px 35px rgba(200, 16, 46, 0.4);
    }
    
    /* Ultra Modern Available Courses */
    .available-courses {
      background: linear-gradient(135deg, white 0%, #f8fafc 100%);
      padding: 50px;
      border-radius: 30px;
      box-shadow: 
        0 25px 50px rgba(0,0,0,0.1),
        0 0 0 1px rgba(255,255,255,0.5) inset;
      margin-bottom: 50px;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .available-courses::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #002147, #c8102e, #f2b705, #28a745);
      border-radius: 30px 30px 0 0;
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 45px;
      padding-bottom: 25px;
      border-bottom: 3px solid #f1f5f9;
    }
    
    .section-header h2 {
      color: #1e293b;
      margin: 0;
      font-size: 2.5em;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 20px;
      letter-spacing: -0.5px;
    }
    
    .view-all-link {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 15px 30px;
      text-decoration: none;
      border-radius: 30px;
      font-weight: 700;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .view-all-link:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.08) translateY(-2px);
      box-shadow: 0 15px 35px rgba(200, 16, 46, 0.4);
    }
    
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
      gap: 35px;
    }
    
    .course-card {
      background: white;
      border-radius: 20px;
      padding: 35px;
      box-shadow: 
        0 15px 35px rgba(0,0,0,0.08),
        0 0 0 1px rgba(255,255,255,0.5) inset;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border-left: 6px solid var(--course-color, #002147);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
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
      transform: translateY(-12px) scale(1.02);
      box-shadow: 
        0 25px 50px rgba(0,0,0,0.15),
        0 0 0 1px rgba(255,255,255,0.8) inset;
    }
    
    .course-title {
      color: #1e293b;
      font-size: 1.5em;
      font-weight: 700;
      margin-bottom: 12px;
      line-height: 1.3;
    }
    
    .course-code {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      color: #64748b;
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85em;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
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
    
    .badge-local { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; }
    .badge-foreign { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); color: #0c5460; }
    .badge-unit { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404; }
    .badge-officer { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
    .badge-non-officer { background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%); color: #383d41; }
    .badge-both { background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%); color: #004085; }
    
    .course-meta {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
      gap: 18px;
      margin-bottom: 25px;
      padding: 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05) inset;
    }
    
    .course-meta span {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.95em;
      color: #64748b;
      font-weight: 600;
    }
    
    .course-description {
      color: #64748b;
      line-height: 1.7;
      margin-bottom: 30px;
      font-size: 1em;
    }
    
    .course-actions {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .btn-action {
      padding: 12px 22px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.9em;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
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
    
    .btn-pdf {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }
    
    .btn-action:hover {
      transform: translateY(-4px) scale(1.05);
      box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }
    
    .no-courses {
      text-align: center;
      padding: 80px 40px;
      color: #666;
    }
    
    .no-courses i {
      font-size: 5em;
      color: #e2e8f0;
      margin-bottom: 25px;
    }
    
    .no-courses h3 {
      color: #1e293b;
      margin-bottom: 15px;
      font-size: 1.5em;
      font-weight: 700;
    }
    
    .no-courses p {
      color: #64748b;
      font-size: 1.1em;
      margin-bottom: 30px;
    }
    
    /* Enhanced PDF Modal */
    .pdf-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.95);
      backdrop-filter: blur(10px);
      animation: fadeIn 0.4s ease;
    }
    
    .pdf-modal-content {
      position: relative;
      margin: 1% auto;
      width: 96%;
      height: 96%;
      background: white;
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 
        0 30px 80px rgba(0,0,0,0.5),
        0 0 0 1px rgba(255,255,255,0.1) inset;
      animation: slideIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .pdf-modal-header {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 30px 35px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .pdf-modal-header h3 {
      margin: 0;
      font-size: 1.5em;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .pdf-close {
      color: white;
      font-size: 36px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.4s ease;
      padding: 12px;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.1);
    }
    
    .pdf-close:hover {
      color: #c8102e;
      background: rgba(255,255,255,0.2);
      transform: scale(1.1) rotate(90deg);
    }
    
    .pdf-viewer {
      width: 100%;
      height: calc(100% - 80px);
      border: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .dashboard-header {
        padding: 40px 30px;
      }
      
      .dashboard-header-content {
        flex-direction: column;
        text-align: center;
        gap: 25px;
      }
      
      .header-text h1 {
        font-size: 2.5em;
        flex-direction: column;
        gap: 15px;
      }
      
      .section-header {
        flex-direction: column;
        gap: 25px;
        text-align: center;
      }
      
      .course-grid {
        grid-template-columns: 1fr;
      }
      
      .enhanced-stats {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }
      
      .quick-actions {
        grid-template-columns: 1fr;
      }
    }
    
    /* Loading Animation */
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    
    .loading {
      animation: pulse 2s infinite;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideIn {
      from { 
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
      }
      to { 
        opacity: 1;
        transform: translateY(0) scale(1);
      }
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
      <li><a href="admin-dashboard-updated.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-courses-enhanced.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
      <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
      <li><a href="course-catalog.php"><i class="fas fa-book"></i> Course Catalog</a></li>
      <li><a href="#available-courses"><i class="fas fa-list-alt"></i> Available Courses</a></li>
      <li><a href="index-improved.html"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <!-- Ultra Modern Dashboard Header -->
    <div class="dashboard-header">
      <div class="dashboard-header-content">
        <img src="pcg_logo.png" alt="PCG Logo" class="header-logo" />
        <div class="header-text">
          <h1><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
          <p>PCG CG-12 Education & Training Administration Dashboard</p>
        </div>
      </div>
    </div>

    <?php if (!$has_new_structure): ?>
      <div class="database-notice">
        <strong><i class="fas fa-exclamation-triangle"></i> Database Update Required:</strong> 
        <a href="safe_database_update.php"><i class="fas fa-database"></i> Update Database</a>
        for full course management features.
      </div>
    <?php endif; ?>

    <!-- Ultra Modern Statistics -->
    <div class="enhanced-stats">
      <div class="enhanced-stat-card" style="--card-gradient: linear-gradient(90deg, #002147, #004080); --icon-color: #002147; --icon-hover-color: #c8102e; --number-color: #c8102e; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-number"><?php echo $stats['total_courses']; ?></div>
        <div class="stat-label">Total Courses</div>
      </div>
      <div class="enhanced-stat-card" style="--card-gradient: linear-gradient(90deg, #28a745, #20c997); --icon-color: #28a745; --icon-hover-color: #c8102e; --number-color: #28a745; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-home"></i></div>
        <div class="stat-number"><?php echo $stats['local_courses']; ?></div>
        <div class="stat-label">Local Training</div>
      </div>
      <div class="enhanced-stat-card" style="--card-gradient: linear-gradient(90deg, #17a2b8, #20c997); --icon-color: #17a2b8; --icon-hover-color: #c8102e; --number-color: #17a2b8; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-number"><?php echo $stats['foreign_courses']; ?></div>
        <div class="stat-label">Foreign Training</div>
      </div>
      <div class="enhanced-stat-card" style="--card-gradient: linear-gradient(90deg, #ffc107, #fd7e14); --icon-color: #ffc107; --icon-hover-color: #c8102e; --number-color: #ffc107; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-handshake"></i></div>
        <div class="stat-number"><?php echo $stats['unit_courses']; ?></div>
        <div class="stat-label">Unit/Interagency</div>
      </div>
      <div class="enhanced-stat-card" style="--card-gradient: linear-gradient(90deg, #6f42c1, #e83e8c); --icon-color: #6f42c1; --icon-hover-color: #c8102e; --number-color: #6f42c1; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo $users_count; ?></div>
        <div class="stat-label">System Users</div>
      </div>
    </div>

    <!-- Modern Quick Actions -->
    <div class="quick-actions">
      <div class="action-card" style="--action-color: #002147; --action-dark: #004080; --action-hover-color: #c8102e;">
        <i class="fas fa-users"></i>
        <h3>Manage Users</h3>
        <p>Add, edit, or remove system users and manage access permissions for the training system</p>
        <a href="manage-users.html" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Users</a>
      </div>
      <div class="action-card" style="--action-color: #28a745; --action-dark: #20c997; --action-hover-color: #c8102e;">
        <i class="fas fa-graduation-cap"></i>
        <h3>Manage Courses</h3>
        <p>Create, update, and organize training courses and programs for all personnel categories</p>
        <a href="manage-courses-enhanced.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Courses</a>
      </div>
      <div class="action-card" style="--action-color: #dc3545; --action-dark: #c82333; --action-hover-color: #c8102e;">
        <i class="fas fa-file-pdf"></i>
        <h3>Course Materials</h3>
        <p>Upload and manage PDF files, documents, and course materials for training programs</p>
        <a href="course-materials.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Materials</a>
      </div>
      <div class="action-card" style="--action-color: #17a2b8; --action-dark: #138496; --action-hover-color: #c8102e;">
        <i class="fas fa-book"></i>
        <h3>Course Catalog</h3>
        <p>Browse and view all available training programs, materials, and course information</p>
        <a href="course-catalog.php" class="action-btn"><i class="fas fa-arrow-right"></i> View Catalog</a>
      </div>
    </div>

    <!-- Ultra Modern Available Courses Section -->
    <div id="available-courses" class="available-courses">
      <div class="section-header">
        <h2><i class="fas fa-list-alt"></i> Available Courses</h2>
        <a href="manage-courses-enhanced.php" class="view-all-link">
          <i class="fas fa-list"></i> View All Courses
        </a>
      </div>
      
      <?php if ($courses_result && $courses_result->num_rows > 0): ?>
        <div class="course-grid">
          <?php while($course = $courses_result->fetch_assoc()): ?>
            <div class="course-card" style="--course-color: <?php 
              if (isset($course['training_type'])) {
                switch($course['training_type']) {
                  case 'Local Training': echo '#28a745'; break;
                  case 'Foreign Training': echo '#17a2b8'; break;
                  case 'Unit / Interagency Training': echo '#ffc107'; break;
                  default: echo '#002147';
                }
              } else {
                echo '#002147';
              }
            ?>;">
              <div class="course-header">
                <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                <?php if (isset($course['course_code']) && $course['course_code']): ?>
                  <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                <?php endif; ?>
              </div>
              
              <div class="course-badges">
                <?php if (isset($course['training_type'])): ?>
                  <span class="badge badge-<?php echo strtolower(str_replace([' ', '/'], ['-', '-'], $course['training_type'])); ?>">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($course['training_type']); ?>
                  </span>
                <?php endif; ?>
                <?php if (isset($course['target_audience'])): ?>
                  <span class="badge badge-<?php echo strtolower(str_replace([' ', '-'], ['-', '-'], $course['target_audience'])); ?>">
                    <i class="fas fa-users"></i> <?php echo htmlspecialchars($course['target_audience']); ?>
                  </span>
                <?php endif; ?>
                <?php if (isset($course['course_level'])): ?>
                  <span class="badge" style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); color: #495057;">
                    <i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($course['course_level']); ?>
                  </span>
                <?php endif; ?>
              </div>
              
              <div class="course-meta">
                <?php if (isset($course['duration']) && $course['duration']): ?>
                  <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($course['duration']); ?></span>
                <?php endif; ?>
                <?php if (isset($course['capacity'])): ?>
                  <span><i class="fas fa-user-friends"></i> <?php echo htmlspecialchars($course['capacity']); ?> students</span>
                <?php endif; ?>
                <?php if (isset($course['category']) && $course['category']): ?>
                  <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($course['category']); ?></span>
                <?php endif; ?>
              </div>
              
              <?php if (isset($course['description']) && $course['description']): ?>
                <div class="course-description">
                  <?php echo htmlspecialchars(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?>
                </div>
              <?php endif; ?>
              
              <div class="course-actions">
                <button class="btn-action btn-view" onclick="viewCourse(<?php echo $course['id']; ?>)">
                  <i class="fas fa-eye"></i> View Details
                </button>
                <button class="btn-action btn-edit" onclick="editCourse(<?php echo $course['id']; ?>)">
                  <i class="fas fa-edit"></i> Edit Course
                </button>
                <?php if (isset($course['file_attachment']) && $course['file_attachment']): ?>
                  <button class="btn-action btn-pdf" onclick="viewPDF('<?php echo htmlspecialchars($course['file_attachment']); ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')">
                    <i class="fas fa-file-pdf"></i> View PDF
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="no-courses">
          <i class="fas fa-graduation-cap"></i>
          <h3>No Courses Available</h3>
          <p>Start by adding some courses to the system or update your database structure for enhanced course management.</p>
          <a href="manage-courses-enhanced.php" class="action-btn" style="--action-color: #002147; --action-dark: #004080;">
            <i class="fas fa-plus"></i> Add New Course
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Enhanced PDF Modal -->
  <div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
      <div class="pdf-modal-header">
        <h3 id="pdfTitle"><i class="fas fa-file-pdf"></i> Course Material</h3>
        <span class="pdf-close" onclick="closePDFModal()">&times;</span>
      </div>
      <div class="pdf-loading" id="pdfLoading">
        <i class="fas fa-spinner fa-spin"></i> Loading PDF...
      </div>
      <iframe id="pdfViewer" class="pdf-viewer" src=""></iframe>
    </div>
  </div>

  <script>
    function viewCourse(id) {
      window.open('course-details.php?id=' + id, '_blank');
    }

    function editCourse(id) {
      window.location.href = 'edit-course-no-logo.php?id=' + id;
    }

    function viewPDF(filename, courseName) {
      const modal = document.getElementById('pdfModal');
      const viewer = document.getElementById('pdfViewer');
      const title = document.getElementById('pdfTitle');
      const loading = document.getElementById('pdfLoading');
      
      title.innerHTML = '<i class="fas fa-file-pdf"></i> ' + courseName + ' - Course Material';
      loading.style.display = 'block';
      viewer.style.display = 'none';
      modal.style.display = 'block';
      
      // Set PDF source
      viewer.src = 'uploads/courses/' + filename;
      
      // Handle loading
      viewer.onload = function() {
        loading.style.display = 'none';
        viewer.style.display = 'block';
        console.log('PDF loaded successfully');
      };
      
      viewer.onerror = function() {
        loading.style.display = 'none';
        alert('Error loading PDF file. Please check if the file exists.');
        closePDFModal();
      };
    }

    function closePDFModal() {
      const modal = document.getElementById('pdfModal');
      const viewer = document.getElementById('pdfViewer');
      const loading = document.getElementById('pdfLoading');
      
      modal.style.display = 'none';
      viewer.src = '';
      loading.style.display = 'none';
      viewer.style.display = 'block';
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

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Enhanced loading animation for statistics cards
    document.querySelectorAll('.enhanced-stat-card').forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`;
      
      card.addEventListener('mouseenter', function() {
        const number = this.querySelector('.stat-number');
        const currentValue = parseInt(number.textContent);
        let counter = 0;
        const increment = currentValue / 30;
        
        const timer = setInterval(() => {
          counter += increment;
          if (counter >= currentValue) {
            number.textContent = currentValue;
            clearInterval(timer);
          } else {
            number.textContent = Math.floor(counter);
          }
        }, 30);
      });
    });

    // Add entrance animations
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.enhanced-stat-card, .action-card, .course-card');
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

    // Add parallax effect to header
    window.addEventListener('scroll', function() {
      const header = document.querySelector('.dashboard-header');
      const scrolled = window.pageYOffset;
      const rate = scrolled * -0.5;
      
      if (header) {
        header.style.transform = `translateY(${rate}px)`;
      }
    });
  </script>
</body>
</html>