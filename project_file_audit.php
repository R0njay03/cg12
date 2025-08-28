<?php
// Script to audit all project files and identify unused ones

echo "<!DOCTYPE html>";
echo "<html><head><title>Project File Audit - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.active { color: green; font-weight: bold; }
.unused { color: red; font-weight: bold; }
.utility { color: blue; font-weight: bold; }
.asset { color: orange; font-weight: bold; }
.file-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
.file-category { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #002147; }
.file-list { list-style: none; padding: 0; }
.file-list li { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.file-size { font-size: 0.8em; color: #666; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
.btn.danger { background: #dc3545; }
.btn.danger:hover { background: #c82333; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>PCG CG-12 Project File Audit</h1>";
echo "<p>Complete analysis of all project files and their usage status</p>";
echo "</div>";

// Get all files
$all_files = glob('*');
$total_size = 0;

// Categorize files
$active_files = [];
$unused_files = [];
$utility_files = [];
$asset_files = [];

// Define current active/essential files
$essential_files = [
    // Main pages
    'index-improved.html' => 'Enhanced home page',
    'contact.html' => 'Contact page with Facebook integration',
    'reports.html' => 'Enhanced reports and analytics',
    'login.html' => 'Admin login page',
    'logout.php' => 'Logout functionality',
    
    // Admin system
    'admin-dashboard-with-logo.php' => 'Main admin dashboard with PCG logo',
    'manage-users.html' => 'User management interface',
    'manage-users-fixed.php' => 'User management with database',
    'manage-courses.php' => 'Course management system',
    'course-materials.php' => 'Course materials management',
    
    // Public pages
    'course-catalog.php' => 'Public course catalog',
    'course-details.php' => 'Individual course pages',
    
    // Styles
    'style-enhanced.css' => 'Enhanced main stylesheet',
    'admin-style-enhanced.css' => 'Enhanced admin stylesheet',
    
    // Backend
    'db.php' => 'Database connection',
    
    // Assets
    'pcg_logo.png' => 'PCG official logo'
];

// Define utility/maintenance files
$utility_patterns = [
    'cleanup', 'update', 'fix', 'add_', 'comprehensive', 'safe_', 'audit', 'navigation'
];

foreach ($all_files as $file) {
    if (!is_file($file)) continue;
    
    $file_size = filesize($file);
    $total_size += $file_size;
    
    if (array_key_exists($file, $essential_files)) {
        $active_files[$file] = [
            'description' => $essential_files[$file],
            'size' => $file_size,
            'status' => 'active'
        ];
    } else {
        $is_utility = false;
        foreach ($utility_patterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                $utility_files[$file] = [
                    'description' => 'Utility/maintenance script',
                    'size' => $file_size,
                    'status' => 'utility'
                ];
                $is_utility = true;
                break;
            }
        }
        
        if (!$is_utility) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'ico', 'pdf'])) {
                $asset_files[$file] = [
                    'description' => 'Asset file',
                    'size' => $file_size,
                    'status' => 'asset'
                ];
            } else {
                $unused_files[$file] = [
                    'description' => 'Potentially unused file',
                    'size' => $file_size,
                    'status' => 'unused'
                ];
            }
        }
    }
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

// Display summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Project Summary</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;'>";
echo "<div><strong>Total Files:</strong> " . count($all_files) . "</div>";
echo "<div><strong>Active Files:</strong> <span class='active'>" . count($active_files) . "</span></div>";
echo "<div><strong>Unused Files:</strong> <span class='unused'>" . count($unused_files) . "</span></div>";
echo "<div><strong>Utility Files:</strong> <span class='utility'>" . count($utility_files) . "</span></div>";
echo "<div><strong>Asset Files:</strong> <span class='asset'>" . count($asset_files) . "</span></div>";
echo "<div><strong>Total Size:</strong> " . formatBytes($total_size) . "</div>";
echo "</div>";
echo "</div>";

// Display file categories
echo "<div class='file-grid'>";

// Active Files
echo "<div class='file-category'>";
echo "<h3 style='color: green;'><i class='fas fa-check-circle'></i> Active Files (" . count($active_files) . ")</h3>";
echo "<p>Essential files currently in use</p>";
echo "<ul class='file-list'>";
foreach ($active_files as $file => $info) {
    echo "<li>";
    echo "<div>";
    echo "<strong>$file</strong><br>";
    echo "<small>{$info['description']}</small>";
    echo "</div>";
    echo "<span class='file-size'>" . formatBytes($info['size']) . "</span>";
    echo "</li>";
}
echo "</ul>";
echo "</div>";

// Unused Files
if (!empty($unused_files)) {
    echo "<div class='file-category'>";
    echo "<h3 style='color: red;'><i class='fas fa-exclamation-triangle'></i> Unused Files (" . count($unused_files) . ")</h3>";
    echo "<p>Files that may be safe to remove</p>";
    echo "<ul class='file-list'>";
    foreach ($unused_files as $file => $info) {
        echo "<li>";
        echo "<div>";
        echo "<strong>$file</strong><br>";
        echo "<small>{$info['description']}</small>";
        echo "</div>";
        echo "<span class='file-size'>" . formatBytes($info['size']) . "</span>";
        echo "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Utility Files
if (!empty($utility_files)) {
    echo "<div class='file-category'>";
    echo "<h3 style='color: blue;'><i class='fas fa-tools'></i> Utility Files (" . count($utility_files) . ")</h3>";
    echo "<p>Maintenance and setup scripts</p>";
    echo "<ul class='file-list'>";
    foreach ($utility_files as $file => $info) {
        echo "<li>";
        echo "<div>";
        echo "<strong>$file</strong><br>";
        echo "<small>{$info['description']}</small>";
        echo "</div>";
        echo "<span class='file-size'>" . formatBytes($info['size']) . "</span>";
        echo "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Asset Files
if (!empty($asset_files)) {
    echo "<div class='file-category'>";
    echo "<h3 style='color: orange;'><i class='fas fa-image'></i> Asset Files (" . count($asset_files) . ")</h3>";
    echo "<p>Images, documents, and media files</p>";
    echo "<ul class='file-list'>";
    foreach ($asset_files as $file => $info) {
        echo "<li>";
        echo "<div>";
        echo "<strong>$file</strong><br>";
        echo "<small>{$info['description']}</small>";
        echo "</div>";
        echo "<span class='file-size'>" . formatBytes($info['size']) . "</span>";
        echo "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

// Recommendations
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
echo "<h3 style='color: #856404;'><i class='fas fa-lightbulb'></i> Cleanup Recommendations</h3>";

if (!empty($unused_files)) {
    echo "<h4>Files Safe to Remove:</h4>";
    echo "<ul>";
    foreach ($unused_files as $file => $info) {
        echo "<li><code>$file</code> - {$info['description']}</li>";
    }
    echo "</ul>";
    echo "<p><strong>Potential space savings:</strong> " . formatBytes(array_sum(array_column($unused_files, 'size'))) . "</p>";
} else {
    echo "<p><strong>✓ No unused files detected!</strong> Your project is clean.</p>";
}

echo "<h4>Current Active Navigation Structure:</h4>";
echo "<ul>";
echo "<li><strong>Home:</strong> index-improved.html</li>";
echo "<li><strong>Admin Dashboard:</strong> admin-dashboard-with-logo.php</li>";
echo "<li><strong>Course Catalog:</strong> course-catalog.php</li>";
echo "<li><strong>Contact:</strong> contact.html</li>";
echo "<li><strong>Reports:</strong> reports.html</li>";
echo "<li><strong>User Management:</strong> manage-users.html / manage-users-fixed.php</li>";
echo "</ul>";
echo "</div>";

// Action buttons
echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='cleanup_unused_links.php' class='btn'><i class='fas fa-broom'></i> Clean Up Links</a>";
if (!empty($unused_files)) {
    echo "<a href='#' class='btn danger' onclick='confirmCleanup()'><i class='fas fa-trash'></i> Remove Unused Files</a>";
}
echo "<a href='index-improved.html' class='btn'><i class='fas fa-home'></i> Go to Home</a>";
echo "</div>";

echo "<script>";
echo "function confirmCleanup() {";
echo "  if (confirm('Are you sure you want to remove all unused files? This action cannot be undone.')) {";
echo "    alert('File removal feature would be implemented here. For safety, please remove files manually after verification.');";
echo "  }";
echo "}";
echo "</script>";

echo "</div></body></html>";
?>