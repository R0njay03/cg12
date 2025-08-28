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
  <style>
    /* Enhanced User-Friendly Dashboard Styles */
    .dashboard-header {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 40px;
      border-radius: 20px;
      margin-bottom: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      position: relative;
      overflow: hidden;
    }
    
    /* User-friendly welcome message */
    .welcome-message {
      background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
      border: 1px solid #c3e6cb;
      color: #155724;
      padding: 20px 25px;
      border-radius: 15px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      animation: slideInDown 0.6s ease;
    }
    
    .welcome-message i {
      font-size: 2em;
      color: #28a745;
    }
    
    .welcome-text h3 {
      margin: 0 0 8px 0;
      font-size: 1.3em;
      font-weight: 600;
    }
    
    .welcome-text p {
      margin: 0;
      opacity: 0.9;
      font-size: 1em;
    }
    
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Breadcrumb navigation */
    .breadcrumb {
      background: rgba(255,255,255,0.9);
      padding: 15px 25px;
      border-radius: 10px;
      margin-bottom: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      backdrop-filter: blur(10px);
    }
    
    .breadcrumb-list {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 0;
      padding: 0;
    }
    
    .breadcrumb-item {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #666;
      font-weight: 500;
    }
    
    .breadcrumb-item:last-child {
      color: #002147;
      font-weight: 600;
    }
    
    .breadcrumb-item a {
      color: #002147;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    
    .breadcrumb-item a:hover {
      color: #c8102e;
    }
    
    .breadcrumb-separator {
      color: #ccc;
      margin: 0 5px;
    }
    
    /* Help tooltips */
    .tooltip {
      position: relative;
      display: inline-block;
      cursor: help;
    }
    
    .tooltip .tooltiptext {
      visibility: hidden;
      width: 250px;
      background-color: #002147;
      color: #fff;
      text-align: center;
      border-radius: 8px;
      padding: 12px;
      position: absolute;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      margin-left: -125px;
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 0.9em;
      line-height: 1.4;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .tooltip .tooltiptext::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #002147 transparent transparent transparent;
    }
    
    .tooltip:hover .tooltiptext {
      visibility: visible;
      opacity: 1;
    }
    
    /* Search Section Styles */
    .search-section {
      background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
      padding: 30px;
      border-radius: 20px;
      margin-bottom: 30px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      border-left: 5px solid #002147;
    }
    
    .search-container {
      max-width: 100%;
    }
    
    .search-header {
      margin-bottom: 25px;
      text-align: center;
    }
    
    .search-header h3 {
      color: #002147;
      margin: 0 0 10px 0;
      font-size: 1.5em;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    
    .search-header p {
      color: #666;
      margin: 0;
      font-size: 1em;
    }
    
    .search-bar-wrapper {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .search-input-group {
      display: flex;
      align-items: center;
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }
    
    .search-input-group:focus-within {
      border-color: #002147;
      box-shadow: 0 4px 20px rgba(0,33,71,0.15);
    }
    
    .search-input {
      flex: 1;
      padding: 15px 20px;
      border: none;
      outline: none;
      font-size: 1em;
      background: transparent;
      color: #333;
    }
    
    .search-input::placeholder {
      color: #999;
      font-style: italic;
    }
    
    .search-btn, .clear-btn {
      padding: 15px 20px;
      border: none;
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1em;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 50px;
    }
    
    .search-btn:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.05);
    }
    
    .clear-btn {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      border-left: 1px solid #dee2e6;
    }
    
    .clear-btn:hover {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    .search-filters {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .filter-select {
      flex: 1;
      min-width: 200px;
      padding: 12px 15px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      background: white;
      color: #333;
      font-size: 0.9em;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .filter-select:focus {
      outline: none;
      border-color: #002147;
      box-shadow: 0 0 0 3px rgba(0,33,71,0.1);
    }
    
    .search-results {
      margin-top: 25px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      overflow: hidden;
      animation: slideDown 0.3s ease;
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
    
    .results-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #dee2e6;
    }
    
    .results-count {
      color: #002147;
      font-weight: 600;
      font-size: 0.9em;
    }
    
    .close-results {
      background: none;
      border: none;
      color: #666;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-size: 0.9em;
    }
    
    .close-results:hover {
      background: #dc3545;
      color: white;
    }
    
    .results-content {
      max-height: 400px;
      overflow-y: auto;
      padding: 20px;
    }
    
    .search-result-item {
      padding: 15px;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .search-result-item:hover {
      background: #f8f9fa;
      border-color: #002147;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .search-result-item:last-child {
      margin-bottom: 0;
    }
    
    .result-title {
      color: #002147;
      font-weight: 600;
      font-size: 1.1em;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .result-description {
      color: #666;
      font-size: 0.9em;
      line-height: 1.5;
      margin-bottom: 10px;
    }
    
    .result-meta {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .result-badge {
      background: #e9ecef;
      color: #495057;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
      font-weight: 500;
    }
    
    .result-badge.type-local { background: #d4edda; color: #155724; }
    .result-badge.type-foreign { background: #d1ecf1; color: #0c5460; }
    .result-badge.type-unit { background: #fff3cd; color: #856404; }
    
    .no-results {
      text-align: center;
      padding: 40px 20px;
      color: #666;
    }
    
    .no-results i {
      font-size: 3em;
      color: #ddd;
      margin-bottom: 15px;
    }
    
    .no-results h4 {
      color: #002147;
      margin-bottom: 10px;
      font-size: 1.2em;
    }
    
    .no-results p {
      margin: 0;
      font-size: 0.9em;
    }

    /* Quick access toolbar */
    .quick-toolbar {
      background: rgba(255,255,255,0.95);
      padding: 20px 25px;
      border-radius: 15px;
      margin-bottom: 30px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .toolbar-title {
      color: #002147;
      font-size: 1.2em;
      font-weight: 600;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .toolbar-actions {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .toolbar-btn {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      color: #002147;
      padding: 12px 20px;
      border: 2px solid #dee2e6;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9em;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      min-width: 140px;
      justify-content: center;
    }
    
    .toolbar-btn:hover {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      border-color: #002147;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,33,71,0.3);
    }
    
    .toolbar-btn.primary {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      border-color: #002147;
    }
    
    .toolbar-btn.primary:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      border-color: #c8102e;
    }
    
    /* Status indicators */
    .status-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8em;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-active {
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      color: #155724;
    }
    
    .status-inactive {
      background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
      color: #721c24;
    }
    
    .status-pending {
      background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
      color: #856404;
    }
    
    /* Progress indicators */
    .progress-container {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 4px;
      margin: 10px 0;
    }
    
    .progress-bar {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      height: 8px;
      border-radius: 6px;
      transition: width 0.6s ease;
      position: relative;
      overflow: hidden;
    }
    
    .progress-bar::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
      );
      background-size: 50px 50px;
      animation: move 2s linear infinite;
    }
    
    @keyframes move {
      0% {
        background-position: 0 0;
      }
      100% {
        background-position: 50px 50px;
      }
    }
    
    /* Notification badge */
    .notification-badge {
      position: relative;
    }
    
    .notification-badge::after {
      content: attr(data-count);
      position: absolute;
      top: -8px;
      right: -8px;
      background: #c8102e;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      font-size: 0.7em;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(200, 16, 46, 0.4);
    }
    
    /* Accessibility improvements */
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    
    /* Focus styles for better accessibility */
    button:focus,
    .btn-action:focus,
    .action-btn:focus,
    .toolbar-btn:focus {
      outline: 3px solid #c8102e;
      outline-offset: 2px;
    }
    
    /* Loading states */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.9);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      backdrop-filter: blur(5px);
    }
    
    .loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #002147;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Enhanced keyboard navigation */
    .keyboard-nav {
      background: rgba(0,33,71,0.05);
      border: 2px solid #002147;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      font-size: 0.9em;
    }
    
    .keyboard-nav h4 {
      color: #002147;
      margin: 0 0 10px 0;
      font-size: 1em;
      font-weight: 600;
    }
    
    .keyboard-shortcuts {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 8px;
    }
    
    .shortcut {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 4px 0;
    }
    
    .shortcut-key {
      background: #002147;
      color: white;
      padding: 2px 8px;
      border-radius: 4px;
      font-family: monospace;
      font-size: 0.8em;
      font-weight: bold;
    }
    
    .dashboard-header::before {
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
    
    .dashboard-header-content {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      gap: 25px;
    }
    
    .header-logo {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.1);
      padding: 5px;
      transition: all 0.3s ease;
      flex-shrink: 0;
    }
    
    .header-logo:hover {
      transform: rotate(360deg) scale(1.1);
      border-color: #c8102e;
      box-shadow: 0 0 20px rgba(200, 16, 46, 0.5);
    }
    
    .header-text h1 {
      margin: 0 0 15px 0;
      font-size: 2.5em;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    
    .header-text p {
      margin: 0;
      opacity: 0.9;
      font-size: 1.2em;
      font-weight: 400;
    }
    
    /* Enhanced Statistics */
    .enhanced-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-bottom: 50px;
    }
    
    .enhanced-stat-card {
      background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
      padding: 35px 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      text-align: center;
      position: relative;
      overflow: hidden;
      transition: all 0.4s ease;
      cursor: pointer;
    }
    
    .enhanced-stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: var(--card-color, linear-gradient(90deg, #002147, #c8102e));
    }
    
    .enhanced-stat-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.6s;
    }
    
    .enhanced-stat-card:hover::after {
      left: 100%;
    }
    
    .enhanced-stat-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
      font-size: 3.5em;
      color: var(--icon-color, #002147);
      margin-bottom: 20px;
      transition: all 0.4s ease;
    }
    
    .enhanced-stat-card:hover .stat-icon {
      color: var(--icon-hover-color, #c8102e);
      transform: scale(1.1) rotate(360deg);
    }
    
    .stat-number {
      font-size: 3.2em;
      font-weight: bold;
      color: var(--number-color, #c8102e);
      margin-bottom: 12px;
      transition: all 0.3s ease;
    }
    
    .enhanced-stat-card:hover .stat-number {
      color: var(--number-hover-color, #002147);
      transform: scale(1.05);
    }
    
    .stat-label {
      color: #666;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 1em;
      letter-spacing: 1px;
    }
    
    /* Available Courses Section */
    .available-courses {
      background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
    }
    
    .available-courses::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, #002147, #c8102e, #f2b705);
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 35px;
      padding-bottom: 20px;
      border-bottom: 3px solid #f8f9fa;
    }
    
    .section-header h2 {
      color: #002147;
      margin: 0;
      font-size: 2.2em;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .view-all-link {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
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
    
    .view-all-link:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
    }
    
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 30px;
    }
    
    .course-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      border-left: 5px solid var(--course-color, #002147);
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
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.6s;
    }
    
    .course-card:hover::before {
      left: 100%;
    }
    
    .course-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .course-header {
      margin-bottom: 20px;
    }
    
    .course-title {
      color: #002147;
      font-size: 1.4em;
      font-weight: 600;
      margin-bottom: 10px;
      line-height: 1.3;
    }
    
    .course-code {
      background: #f8f9fa;
      color: #666;
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 0.85em;
      font-weight: 500;
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
    
    .badge-local { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; }
    .badge-foreign { background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); color: #0c5460; }
    .badge-unit { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #856404; }
    .badge-officer { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
    .badge-non-officer { background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%); color: #383d41; }
    .badge-both { background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%); color: #004085; }
    
    .course-meta {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
    }
    
    .course-meta span {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.9em;
      color: #666;
      font-weight: 500;
    }
    
    .course-description {
      color: #666;
      line-height: 1.6;
      margin-bottom: 25px;
      font-size: 0.95em;
    }
    
    .course-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    
    .btn-action {
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.9em;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
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
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .no-courses {
      text-align: center;
      padding: 80px 40px;
      color: #666;
    }
    
    .no-courses i {
      font-size: 6em;
      color: #ddd;
      margin-bottom: 30px;
    }
    
    .no-courses h3 {
      color: #002147;
      margin-bottom: 20px;
      font-size: 1.8em;
      font-weight: 600;
    }
    
    .no-courses p {
      font-size: 1.1em;
      margin-bottom: 30px;
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
      background-color: rgba(0,0,0,0.9);
      backdrop-filter: blur(5px);
      animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .pdf-modal-content {
      position: relative;
      margin: 1% auto;
      width: 95%;
      height: 95%;
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 80px rgba(0,0,0,0.4);
      animation: slideIn 0.4s ease;
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
    
    .pdf-modal-header {
      background: linear-gradient(135deg, #002147 0%, #004080 100%);
      color: white;
      padding: 25px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .pdf-modal-header h3 {
      margin: 0;
      font-size: 1.4em;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .pdf-close {
      color: white;
      font-size: 32px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      padding: 8px;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .pdf-close:hover {
      color: #c8102e;
      background: rgba(255,255,255,0.1);
      transform: scale(1.1) rotate(90deg);
    }
    
    .pdf-viewer {
      width: 100%;
      height: calc(100% - 80px);
      border: none;
      background: #f5f5f5;
    }
    
    .pdf-loading {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #002147;
      font-size: 1.2em;
      display: none;
    }
    
    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-bottom: 50px;
    }
    
    .action-card {
      background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
      padding: 35px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      text-align: center;
      transition: all 0.4s ease;
      border-top: 5px solid var(--action-color, #002147);
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
      transition: left 0.6s;
    }
    
    .action-card:hover::before {
      left: 100%;
    }
    
    .action-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .action-card i {
      font-size: 4em;
      color: var(--action-color, #002147);
      margin-bottom: 25px;
      transition: all 0.4s ease;
    }
    
    .action-card:hover i {
      color: var(--action-hover-color, #c8102e);
      transform: scale(1.1) rotate(360deg);
    }
    
    .action-card h3 {
      color: #002147;
      margin-bottom: 18px;
      font-size: 1.5em;
      font-weight: 600;
    }
    
    .action-card p {
      color: #666;
      margin-bottom: 30px;
      line-height: 1.6;
      font-size: 1em;
    }
    
    .action-btn {
      background: linear-gradient(135deg, var(--action-color, #002147) 0%, var(--action-dark, #004080) 100%);
      color: white;
      padding: 15px 30px;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 1em;
    }
    
    .action-btn:hover {
      background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
      transform: scale(1.05);
      box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .dashboard-header-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
      }
      
      .header-text h1 {
        font-size: 2em;
        flex-direction: column;
        gap: 10px;
      }
      
      .section-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      
      .course-grid {
        grid-template-columns: 1fr;
      }
      
      .pdf-modal-content {
        width: 98%;
        height: 98%;
        margin: 1% auto;
      }
      
      .header-logo {
        width: 40px;
        height: 40px;
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
      <li><a href="admin-dashboard-enhanced.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-courses.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
      <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
      <li><a href="#available-courses"><i class="fas fa-list-alt"></i> Available Courses</a></li>
      <li><a href="manage_personnel.php"><i class="fas fa-user-tie"></i> Manage Personnel Data</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <ol class="breadcrumb-list">
        <li class="breadcrumb-item">
          <a href="index-improved.html"><i class="fas fa-home"></i> Home</a>
        </li>
        <li class="breadcrumb-separator">/</li>
        <li class="breadcrumb-item">
          <i class="fas fa-tachometer-alt"></i> Admin Dashboard
        </li>
      </ol>
    </nav>

    <!-- Welcome Message -->
    <div class="welcome-message">
      <i class="fas fa-hand-wave"></i>
      <div class="welcome-text">
        <h3>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
        <p>Here's your training management overview. You have <?php echo $stats['total_courses']; ?> active courses and <?php echo $users_count; ?> system users.</p>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-header">
          <h3><i class="fas fa-search"></i> Search Dashboard</h3>
          <p>Search for courses, users, or any content across the system</p>
        </div>
        <div class="search-bar-wrapper">
          <div class="search-input-group">
            <input type="text" id="dashboardSearch" placeholder="Search courses, descriptions, categories..." class="search-input">
            <button type="button" onclick="performSearch()" class="search-btn">
              <i class="fas fa-search"></i>
            </button>
            <button type="button" onclick="clearSearch()" class="clear-btn" title="Clear search">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="search-filters">
            <select id="searchCategory" class="filter-select">
              <option value="all">All Categories</option>
              <option value="courses">Courses Only</option>
              <option value="local">Local Training</option>
              <option value="foreign">Foreign Training</option>
              <option value="unit">Unit/Interagency</option>
            </select>
            <select id="searchSort" class="filter-select">
              <option value="relevance">Sort by Relevance</option>
              <option value="name">Sort by Name</option>
              <option value="date">Sort by Date</option>
              <option value="type">Sort by Type</option>
            </select>
          </div>
        </div>
        <div class="search-results" id="searchResults" style="display: none;">
          <div class="results-header">
            <span class="results-count" id="resultsCount">0 results found</span>
            <button type="button" onclick="hideSearchResults()" class="close-results">
              <i class="fas fa-times"></i> Close
            </button>
          </div>
          <div class="results-content" id="resultsContent">
            <!-- Search results will be populated here -->
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Access Toolbar -->
    <div class="quick-toolbar">
      <div class="toolbar-title">
        <i class="fas fa-bolt"></i> Quick Actions
        <div class="tooltip">
          <i class="fas fa-question-circle" style="color: #666; font-size: 0.9em; margin-left: 8px;"></i>
          <span class="tooltiptext">Use these shortcuts to quickly access common administrative tasks</span>
        </div>
      </div>
      <div class="toolbar-actions">
        <a href="manage-courses-enhanced.php" class="toolbar-btn primary">
          <i class="fas fa-plus"></i> Add Course
        </a>
        <a href="manage-users.html" class="toolbar-btn">
          <i class="fas fa-user-plus"></i> Add User
        </a>
        <a href="course-materials.php" class="toolbar-btn">
          <i class="fas fa-upload"></i> Upload Material
        </a>
        <a href="manage_personnel.php" class="toolbar-btn">
          <i class="fas fa-user-tie"></i> Personnel
        </a>
      </div>
    </div>

    <!-- Enhanced Dashboard Header -->
    <div class="dashboard-header">
      <div class="dashboard-header-content">
        <div class="header-text">
          <h1><i class="fas fa-user-shield"></i> Administration Dashboard</h1>
          <p>PCG CG-12 Education & Training Management System</p>
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

    <!-- Keyboard Navigation Help -->
    <div class="keyboard-nav">
      <h4><i class="fas fa-keyboard"></i> Keyboard Shortcuts</h4>
      <div class="keyboard-shortcuts">
        <div class="shortcut">
          <span>Add New Course</span>
          <span class="shortcut-key">Alt + C</span>
        </div>
        <div class="shortcut">
          <span>Manage Users</span>
          <span class="shortcut-key">Alt + U</span>
        </div>
        <div class="shortcut">
          <span>Course Catalog</span>
          <span class="shortcut-key">Alt + B</span>
        </div>
        <div class="shortcut">
          <span>Manage Personnel</span>
          <span class="shortcut-key">Alt + P</span>
        </div>
      </div>
    </div>

    <!-- Enhanced Statistics -->
    <div class="enhanced-stats">
      <div class="enhanced-stat-card" style="--card-color: linear-gradient(90deg, #002147, #004080); --icon-color: #002147; --icon-hover-color: #c8102e; --number-color: #c8102e; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-number"><?php echo $stats['total_courses']; ?></div>
        <div class="stat-label">Total Courses</div>
      </div>
      <div class="enhanced-stat-card" style="--card-color: linear-gradient(90deg, #28a745, #20c997); --icon-color: #28a745; --icon-hover-color: #c8102e; --number-color: #28a745; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-home"></i></div>
        <div class="stat-number"><?php echo $stats['local_courses']; ?></div>
        <div class="stat-label">Local Training</div>
      </div>
      <div class="enhanced-stat-card" style="--card-color: linear-gradient(90deg, #17a2b8, #20c997); --icon-color: #17a2b8; --icon-hover-color: #c8102e; --number-color: #17a2b8; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-globe"></i></div>
        <div class="stat-number"><?php echo $stats['foreign_courses']; ?></div>
        <div class="stat-label">Foreign Training</div>
      </div>
      <div class="enhanced-stat-card" style="--card-color: linear-gradient(90deg, #ffc107, #fd7e14); --icon-color: #ffc107; --icon-hover-color: #c8102e; --number-color: #ffc107; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-handshake"></i></div>
        <div class="stat-number"><?php echo $stats['unit_courses']; ?></div>
        <div class="stat-label">Unit/Interagency</div>
      </div>
      <div class="enhanced-stat-card" style="--card-color: linear-gradient(90deg, #6f42c1, #e83e8c); --icon-color: #6f42c1; --icon-hover-color: #c8102e; --number-color: #6f42c1; --number-hover-color: #002147;">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo $users_count; ?></div>
        <div class="stat-label">System Users</div>
      </div>
    </div>

    <!-- Quick Actions -->
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
      <div class="action-card" style="--action-color: #6f42c1; --action-dark: #5a32a3; --action-hover-color: #c8102e;">
        <i class="fas fa-user-tie"></i>
        <h3>Manage Personnel</h3>
        <p>Manage personnel records, upload data via Excel/CSV, and track training assignments</p>
        <a href="manage_personnel.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Personnel</a>
      </div>
    </div>

    <!-- Available Courses Section -->
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

  <!-- Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
  </div>

  <script>
    // Enhanced User-Friendly JavaScript Functions
    
    function showLoading() {
      document.getElementById('loadingOverlay').style.display = 'flex';
    }
    
    function hideLoading() {
      document.getElementById('loadingOverlay').style.display = 'none';
    }

    function viewCourse(id) {
      showLoading();
      setTimeout(() => {
        window.open('course-details.php?id=' + id, '_blank');
        hideLoading();
      }, 500);
    }

    function editCourse(id) {
      showLoading();
      setTimeout(() => {
        window.location.href = 'edit-course-no-logo.php?id=' + id;
      }, 500);
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

    // Enhanced Keyboard Shortcuts
    document.addEventListener('keydown', function(event) {
      // Close modal with Escape key
      if (event.key === 'Escape') {
        closePDFModal();
        return;
      }
      
      // Keyboard shortcuts with Alt key
      if (event.altKey) {
        switch(event.key.toLowerCase()) {
          case 'c':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'manage-courses-enhanced.php';
            }, 300);
            break;
          case 'u':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'manage-users.html';
            }, 300);
            break;
          case 'r':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'reports.html';
            }, 300);
            break;
          case 'b':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'course-catalog.php';
            }, 300);
            break;
          case 'm':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'course-materials.php';
            }, 300);
            break;
          case 'p':
            event.preventDefault();
            showLoading();
            setTimeout(() => {
              window.location.href = 'manage_personnel.php';
            }, 300);
            break;
        }
      }
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('pdfModal');
      if (event.target == modal) {
        closePDFModal();
      }
    }

    // Enhanced smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          
          // Add visual feedback
          target.style.boxShadow = '0 0 20px rgba(200, 16, 46, 0.5)';
          setTimeout(() => {
            target.style.boxShadow = '';
          }, 2000);
        }
      });
    });

    // Enhanced statistics animation with progress bars
    document.querySelectorAll('.enhanced-stat-card').forEach((card, index) => {
      card.addEventListener('mouseenter', function() {
        const number = this.querySelector('.stat-number');
        const currentValue = parseInt(number.textContent);
        let counter = 0;
        const increment = currentValue / 30;
        
        // Add progress bar
        if (!this.querySelector('.progress-container')) {
          const progressContainer = document.createElement('div');
          progressContainer.className = 'progress-container';
          const progressBar = document.createElement('div');
          progressBar.className = 'progress-bar';
          progressBar.style.width = '0%';
          progressContainer.appendChild(progressBar);
          this.appendChild(progressContainer);
        }
        
        const progressBar = this.querySelector('.progress-bar');
        
        const timer = setInterval(() => {
          counter += increment;
          const percentage = Math.min((counter / currentValue) * 100, 100);
          progressBar.style.width = percentage + '%';
          
          if (counter >= currentValue) {
            number.textContent = currentValue;
            progressBar.style.width = '100%';
            clearInterval(timer);
            
            // Remove progress bar after animation
            setTimeout(() => {
              const container = this.querySelector('.progress-container');
              if (container) {
                container.remove();
              }
            }, 2000);
          } else {
            number.textContent = Math.floor(counter);
          }
        }, 50);
      });
    });

    // Add notification system
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
        font-weight: 600;
      `;
      notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }

    // Add CSS for notification animations
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
      @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
      }
    `;
    document.head.appendChild(style);

    // Enhanced toolbar button interactions
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        showNotification('Navigating to ' + this.textContent.trim() + '...', 'info');
        showLoading();
      });
    });

    // Add tooltips to action buttons
    document.querySelectorAll('.btn-action').forEach(btn => {
      btn.addEventListener('mouseenter', function() {
        const tooltip = document.createElement('div');
        tooltip.className = 'action-tooltip';
        tooltip.textContent = this.textContent.trim();
        tooltip.style.cssText = `
          position: absolute;
          background: #002147;
          color: white;
          padding: 8px 12px;
          border-radius: 6px;
          font-size: 0.8em;
          z-index: 1000;
          pointer-events: none;
          white-space: nowrap;
          transform: translateX(-50%);
          bottom: 100%;
          left: 50%;
          margin-bottom: 5px;
        `;
        
        this.style.position = 'relative';
        this.appendChild(tooltip);
      });
      
      btn.addEventListener('mouseleave', function() {
        const tooltip = this.querySelector('.action-tooltip');
        if (tooltip) {
          tooltip.remove();
        }
      });
    });

    // Auto-hide loading overlay on page load
    window.addEventListener('load', function() {
      hideLoading();
      showNotification('Dashboard loaded successfully!', 'success');
    });

    // Add focus management for accessibility
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Tab') {
        const focusableElements = document.querySelectorAll(
          'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (event.shiftKey && document.activeElement === firstElement) {
          event.preventDefault();
          lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
          event.preventDefault();
          firstElement.focus();
        }
      }
    });

    // Enhanced Search Functionality
    function performSearch() {
      const searchInput = document.getElementById('dashboardSearch');
      const searchCategory = document.getElementById('searchCategory');
      const searchSort = document.getElementById('searchSort');
      const searchResults = document.getElementById('searchResults');
      const resultsContent = document.getElementById('resultsContent');
      const resultsCount = document.getElementById('resultsCount');
      
      const query = searchInput.value.trim();
      const category = searchCategory.value;
      const sortBy = searchSort.value;
      
      if (query.length < 2) {
        showNotification('Please enter at least 2 characters to search', 'error');
        return;
      }
      
      showLoading();
      showNotification('Searching...', 'info');
      
      // Simulate search delay
      setTimeout(() => {
        const results = performDashboardSearch(query, category, sortBy);
        displaySearchResults(results, query);
        hideLoading();
        
        if (results.length > 0) {
          showNotification(`Found ${results.length} result(s)`, 'success');
        } else {
          showNotification('No results found', 'error');
        }
      }, 800);
    }
    
    function performDashboardSearch(query, category, sortBy) {
      const searchTerm = query.toLowerCase();
      let results = [];
      
      // Search through course cards
      const courseCards = document.querySelectorAll('.course-card');
      courseCards.forEach((card, index) => {
        const title = card.querySelector('.course-title')?.textContent || '';
        const description = card.querySelector('.course-description')?.textContent || '';
        const badges = Array.from(card.querySelectorAll('.badge')).map(badge => badge.textContent).join(' ');
        const meta = Array.from(card.querySelectorAll('.course-meta span')).map(span => span.textContent).join(' ');
        
        const searchableText = `${title} ${description} ${badges} ${meta}`.toLowerCase();
        
        if (searchableText.includes(searchTerm)) {
          // Check category filter
          let matchesCategory = true;
          if (category !== 'all') {
            const trainingType = badges.toLowerCase();
            switch(category) {
              case 'local':
                matchesCategory = trainingType.includes('local');
                break;
              case 'foreign':
                matchesCategory = trainingType.includes('foreign');
                break;
              case 'unit':
                matchesCategory = trainingType.includes('unit') || trainingType.includes('interagency');
                break;
              case 'courses':
                matchesCategory = true; // All course cards are courses
                break;
            }
          }
          
          if (matchesCategory) {
            results.push({
              type: 'course',
              title: title,
              description: description.substring(0, 200) + (description.length > 200 ? '...' : ''),
              badges: badges,
              meta: meta,
              element: card,
              relevance: calculateRelevance(searchableText, searchTerm)
            });
          }
        }
      });
      
      // Search through action cards
      const actionCards = document.querySelectorAll('.action-card');
      actionCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent || '';
        const description = card.querySelector('p')?.textContent || '';
        const searchableText = `${title} ${description}`.toLowerCase();
        
        if (searchableText.includes(searchTerm)) {
          let matchesCategory = true;
          if (category === 'courses') {
            matchesCategory = title.toLowerCase().includes('course') || description.toLowerCase().includes('course');
          }
          
          if (matchesCategory) {
            results.push({
              type: 'action',
              title: title,
              description: description,
              badges: '',
              meta: 'Quick Action',
              element: card,
              relevance: calculateRelevance(searchableText, searchTerm)
            });
          }
        }
      });
      
      // Search through statistics
      const statCards = document.querySelectorAll('.enhanced-stat-card');
      statCards.forEach(card => {
        const label = card.querySelector('.stat-label')?.textContent || '';
        const number = card.querySelector('.stat-number')?.textContent || '';
        const searchableText = `${label} ${number}`.toLowerCase();
        
        if (searchableText.includes(searchTerm)) {
          results.push({
            type: 'statistic',
            title: label,
            description: `Current count: ${number}`,
            badges: 'Statistics',
            meta: 'Dashboard Metric',
            element: card,
            relevance: calculateRelevance(searchableText, searchTerm)
          });
        }
      });
      
      // Sort results
      results = sortSearchResults(results, sortBy);
      
      return results;
    }
    
    function calculateRelevance(text, searchTerm) {
      const exactMatch = text.includes(searchTerm) ? 100 : 0;
      const wordMatch = searchTerm.split(' ').reduce((score, word) => {
        return score + (text.includes(word) ? 10 : 0);
      }, 0);
      return exactMatch + wordMatch;
    }
    
    function sortSearchResults(results, sortBy) {
      switch(sortBy) {
        case 'name':
          return results.sort((a, b) => a.title.localeCompare(b.title));
        case 'type':
          return results.sort((a, b) => a.type.localeCompare(b.type));
        case 'date':
          // For now, maintain original order as we don't have dates
          return results;
        case 'relevance':
        default:
          return results.sort((a, b) => b.relevance - a.relevance);
      }
    }
    
    function displaySearchResults(results, query) {
      const searchResults = document.getElementById('searchResults');
      const resultsContent = document.getElementById('resultsContent');
      const resultsCount = document.getElementById('resultsCount');
      
      resultsCount.textContent = `${results.length} result(s) found for "${query}"`;
      
      if (results.length === 0) {
        resultsContent.innerHTML = `
          <div class="no-results">
            <i class="fas fa-search"></i>
            <h4>No Results Found</h4>
            <p>Try adjusting your search terms or filters</p>
          </div>
        `;
      } else {
        resultsContent.innerHTML = results.map(result => `
          <div class="search-result-item" onclick="scrollToResult(this)" data-type="${result.type}">
            <div class="result-title">
              <i class="fas fa-${getResultIcon(result.type)}"></i>
              ${highlightSearchTerm(result.title, query)}
            </div>
            <div class="result-description">
              ${highlightSearchTerm(result.description, query)}
            </div>
            <div class="result-meta">
              <span class="result-badge type-${result.type.toLowerCase()}">${result.type}</span>
              ${result.badges ? `<span class="result-badge">${result.badges}</span>` : ''}
              ${result.meta ? `<span class="result-badge">${result.meta}</span>` : ''}
            </div>
          </div>
        `).join('');
      }
      
      searchResults.style.display = 'block';
      searchResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function getResultIcon(type) {
      switch(type) {
        case 'course': return 'graduation-cap';
        case 'action': return 'bolt';
        case 'statistic': return 'chart-bar';
        default: return 'file';
      }
    }
    
    function highlightSearchTerm(text, searchTerm) {
      if (!searchTerm) return text;
      const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\// Add search functionality for courses
    function searchCourses(query) {
      const courseCards = document.querySelectorAll('.course-card');
      const searchTerm = query.toLowerCase();
      
      courseCards.forEach(card => {
        const title = card.querySelector('.course-title').textContent.toLowerCase();
        const description = card.querySelector('.course-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
          card.style.display = 'block';
          card.style.animation = 'fadeIn 0.3s ease';
        } else {
          card.style.display = 'none';
        }
      });
    }')})`, 'gi');
      return text.replace(regex, '<mark style="background: #fff3cd; color: #856404; padding: 2px 4px; border-radius: 3px;">$1</mark>');
    }
    
    function scrollToResult(resultElement) {
      const type = resultElement.getAttribute('data-type');
      hideSearchResults();
      
      // Find and scroll to the corresponding element
      let targetElement = null;
      
      if (type === 'course') {
        const title = resultElement.querySelector('.result-title').textContent.trim();
        const courseCards = document.querySelectorAll('.course-card');
        courseCards.forEach(card => {
          const cardTitle = card.querySelector('.course-title').textContent.trim();
          if (cardTitle.includes(title.replace(/[^\w\s]/gi, ''))) {
            targetElement = card;
          }
        });
      } else if (type === 'action') {
        const title = resultElement.querySelector('.result-title').textContent.trim();
        const actionCards = document.querySelectorAll('.action-card');
        actionCards.forEach(card => {
          const cardTitle = card.querySelector('h3').textContent.trim();
          if (cardTitle.includes(title.replace(/[^\w\s]/gi, ''))) {
            targetElement = card;
          }
        });
      } else if (type === 'statistic') {
        const title = resultElement.querySelector('.result-title').textContent.trim();
        const statCards = document.querySelectorAll('.enhanced-stat-card');
        statCards.forEach(card => {
          const cardTitle = card.querySelector('.stat-label').textContent.trim();
          if (cardTitle.includes(title.replace(/[^\w\s]/gi, ''))) {
            targetElement = card;
          }
        });
      }
      
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Add highlight effect
        targetElement.style.boxShadow = '0 0 20px rgba(200, 16, 46, 0.5)';
        targetElement.style.transform = 'scale(1.02)';
        
        setTimeout(() => {
          targetElement.style.boxShadow = '';
          targetElement.style.transform = '';
        }, 3000);
        
        showNotification('Scrolled to result', 'success');
      }
    }
    
    function clearSearch() {
      const searchInput = document.getElementById('dashboardSearch');
      const searchCategory = document.getElementById('searchCategory');
      const searchSort = document.getElementById('searchSort');
      
      searchInput.value = '';
      searchCategory.value = 'all';
      searchSort.value = 'relevance';
      
      hideSearchResults();
      showNotification('Search cleared', 'info');
    }
    
    function hideSearchResults() {
      const searchResults = document.getElementById('searchResults');
      searchResults.style.display = 'none';
    }
    
    // Add real-time search as user types
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('dashboardSearch');
      let searchTimeout;
      
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
          searchTimeout = setTimeout(() => {
            performSearch();
          }, 500); // Debounce search
        } else if (query.length === 0) {
          hideSearchResults();
        }
      });
      
      // Handle Enter key
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          performSearch();
        }
      });
      
      // Handle Escape key to clear search
      searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          clearSearch();
        }
      });
    });

    // Legacy search function for backward compatibility
    function searchCourses(query) {
      const courseCards = document.querySelectorAll('.course-card');
      const searchTerm = query.toLowerCase();
      
      courseCards.forEach(card => {
        const title = card.querySelector('.course-title').textContent.toLowerCase();
        const description = card.querySelector('.course-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
          card.style.display = 'block';
          card.style.animation = 'fadeIn 0.3s ease';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Add real-time clock
    function updateClock() {
      const now = new Date();
      const timeString = now.toLocaleTimeString();
      const dateString = now.toLocaleDateString();
      
      // Add clock to header if it doesn't exist
      if (!document.querySelector('.dashboard-clock')) {
        const clock = document.createElement('div');
        clock.className = 'dashboard-clock';
        clock.style.cssText = `
          position: absolute;
          top: 20px;
          right: 20px;
          background: rgba(255,255,255,0.1);
          color: white;
          padding: 10px 15px;
          border-radius: 8px;
          font-size: 0.9em;
          backdrop-filter: blur(10px);
        `;
        document.querySelector('.dashboard-header').appendChild(clock);
      }
      
      const clock = document.querySelector('.dashboard-clock');
      clock.innerHTML = `
        <div style="font-weight: 600;">${timeString}</div>
        <div style="font-size: 0.8em; opacity: 0.8;">${dateString}</div>
      `;
    }

    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call

    console.log('Enhanced Admin Dashboard loaded successfully!');
  </script>
  
</body>
</html>