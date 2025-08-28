<?php
// Script to identify and remove unused links

echo "<!DOCTYPE html>";
echo "<html><head><title>Cleanup Unused Links - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.warning { color: orange; padding: 8px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
.file-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #002147; }
.unused-link { background: #fff3cd; padding: 5px; margin: 2px 0; border-radius: 3px; font-family: monospace; }
.removed-link { background: #f8d7da; padding: 5px; margin: 2px 0; border-radius: 3px; font-family: monospace; text-decoration: line-through; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Cleanup Unused Links</h1>";
echo "<p>Identifying and removing unused links from PCG CG-12 project</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;
$removed_links = 0;

// Get all files in the directory
$files = glob('*.{html,php,css,js}', GLOB_BRACE);

// Define current active files (files that should exist and be linked)
$active_files = [
    'index-improved.html',
    'admin-dashboard-with-logo.php',
    'course-catalog.php',
    'course-details.php',
    'contact.html',
    'reports.html',
    'login.html',
    'logout.php',
    'manage-users.html',
    'manage-users-fixed.php',
    'manage-courses.php',
    'course-materials.php',
    'style-enhanced.css',
    'admin-style-enhanced.css',
    'db.php'
];

// Define unused/deprecated files and links to remove
$unused_links = [
    // Old file references
    'href="index.html"',
    'href="admin-dashboard.html"',
    'href="admin-dashboard.php"',
    'href="admin-dashboard-fixed.php"',
    'href="courses.html"',
    'href="manage-users.php"',
    'href="reports-enhanced.html"',
    'href="style.css"',
    'href="admin-style.css"',
    
    // JavaScript redirects to old files
    'window.location.href = "index.html"',
    'location.href = "index.html"',
    'window.location.href = "admin-dashboard.html"',
    'location.href = "admin-dashboard.html"',
    
    // PHP redirects to old files
    'header("Location: index.html")',
    'header("Location: admin-dashboard.html")',
    
    // Broken or placeholder links
    'href="#"',
    'src="bg.jpg"',
    'src="course1.jpg"',
    'src="course2.jpg"',
    'src="course3.jpg"',
    
    // Old navigation patterns
    'href="index.html#contact"',
    'href="index.html#home"',
    'href="courses.html"',
];

// Replacement mappings for fixing links
$link_replacements = [
    // Update to current active files
    'href="index.html"' => 'href="index-improved.html"',
    'href="admin-dashboard.html"' => 'href="admin-dashboard-with-logo.php"',
    'href="admin-dashboard.php"' => 'href="admin-dashboard-with-logo.php"',
    'href="admin-dashboard-fixed.php"' => 'href="admin-dashboard-with-logo.php"',
    'href="courses.html"' => 'href="course-catalog.php"',
    'href="manage-users.php"' => 'href="manage-users.html"',
    'href="reports-enhanced.html"' => 'href="reports.html"',
    'href="style.css"' => 'href="style-enhanced.css"',
    'href="admin-style.css"' => 'href="admin-style-enhanced.css"',
    
    // Fix JavaScript redirects
    'window.location.href = "index.html"' => 'window.location.href = "index-improved.html"',
    'location.href = "index.html"' => 'location.href = "index-improved.html"',
    'window.location.href = "admin-dashboard.html"' => 'window.location.href = "admin-dashboard-with-logo.php"',
    'location.href = "admin-dashboard.html"' => 'location.href = "admin-dashboard-with-logo.php"',
    
    // Fix PHP redirects
    'header("Location: index.html")' => 'header("Location: index-improved.html")',
    'header("Location: admin-dashboard.html")' => 'header("Location: admin-dashboard-with-logo.php")',
    
    // Fix navigation links
    'href="index.html#contact"' => 'href="contact.html"',
    'href="index.html#home"' => 'href="index-improved.html"',
    
    // Remove placeholder links
    'href="#"' => 'href="javascript:void(0)"',
];

// Links to completely remove (empty them)
$remove_completely = [
    'src="bg.jpg"',
    'src="course1.jpg"',
    'src="course2.jpg"',
    'src="course3.jpg"',
];

echo "<div class='info'>Scanning " . count($files) . " files for unused links...</div>";

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    echo "<div class='file-section'>";
    echo "<h3>Processing: $file</h3>";
    
    $content = file_get_contents($file);
    $original_content = $content;
    $file_changes = 0;
    
    // Apply link replacements
    foreach ($link_replacements as $old_link => $new_link) {
        if (strpos($content, $old_link) !== false) {
            $content = str_replace($old_link, $new_link, $content);
            echo "<div class='unused-link'>Fixed: " . htmlspecialchars($old_link) . " → " . htmlspecialchars($new_link) . "</div>";
            $file_changes++;
            $removed_links++;
        }
    }
    
    // Remove links completely
    foreach ($remove_completely as $remove_link) {
        if (strpos($content, $remove_link) !== false) {
            // For image sources, replace with placeholder or remove the attribute
            if (strpos($remove_link, 'src=') !== false) {
                $content = str_replace($remove_link, '', $content);
                echo "<div class='removed-link'>Removed: " . htmlspecialchars($remove_link) . "</div>";
            }
            $file_changes++;
            $removed_links++;
        }
    }
    
    // Remove broken iframe references
    if (strpos($content, 'CG12-0825-019.pdf') !== false) {
        $content = preg_replace('/<iframe[^>]*CG12-0825-019\.pdf[^>]*><\/iframe>/', '', $content);
        echo "<div class='removed-link'>Removed: Broken PDF iframe</div>";
        $file_changes++;
        $removed_links++;
    }
    
    // Clean up multiple spaces and empty attributes
    $content = preg_replace('/\s+/', ' ', $content);
    $content = str_replace('src=""', '', $content);
    $content = str_replace('href=""', '', $content);
    
    // Save changes if any were made
    if ($content !== $original_content) {
        if (file_put_contents($file, $content)) {
            echo "<div class='success'>✓ Updated $file ($file_changes changes)</div>";
            $success_count++;
        } else {
            echo "<div class='error'>✗ Failed to update $file</div>";
            $error_count++;
        }
    } else {
        echo "<div class='info'>→ No changes needed in $file</div>";
    }
    
    echo "</div>";
}

// Identify and suggest removal of unused files
echo "<div class='file-section'>";
echo "<h3>Unused Files Analysis</h3>";

$all_files = glob('*');
$unused_files = [];

foreach ($all_files as $file) {
    if (is_file($file) && !in_array($file, $active_files)) {
        // Check if it's a system file or utility
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $filename = pathinfo($file, PATHINFO_FILENAME);
        
        // Skip system files and utilities
        if (in_array($extension, ['txt', 'md', 'gitignore']) || 
            strpos($filename, 'cleanup') !== false ||
            strpos($filename, 'update') !== false ||
            strpos($filename, 'fix') !== false ||
            strpos($filename, 'add_') !== false ||
            strpos($filename, 'comprehensive') !== false ||
            strpos($filename, 'safe_') !== false ||
            $file === 'pcg_logo.png') {
            continue;
        }
        
        $unused_files[] = $file;
    }
}

if (!empty($unused_files)) {
    echo "<div class='warning'>Found " . count($unused_files) . " potentially unused files:</div>";
    foreach ($unused_files as $unused_file) {
        echo "<div class='unused-link'>$unused_file</div>";
    }
    echo "<div class='info'>Note: These files may be safe to remove, but verify they're not needed before deletion.</div>";
} else {
    echo "<div class='success'>✓ No unused files detected</div>";
}

echo "</div>";

// Create a redirect for old index.html
$redirect_content = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=index-improved.html">
    <title>Redirecting...</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .redirect-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 400px; margin: 0 auto; }
        .logo { width: 60px; height: 60px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="redirect-box">
        <img src="pcg_logo.png" alt="PCG Logo" class="logo">
        <h2>PCG CG-12</h2>
        <p>Redirecting to the new home page...</p>
        <p>If you are not redirected automatically, <a href="index-improved.html">click here</a>.</p>
    </div>
</body>
</html>';

if (file_put_contents('index.html', $redirect_content)) {
    echo "<div class='file-section'>";
    echo "<h3>Created Redirect</h3>";
    echo "<div class='success'>✓ Created index.html redirect to index-improved.html</div>";
    echo "</div>";
    $success_count++;
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Cleanup Complete!</h3>";
echo "<p><strong>Files processed:</strong> " . count($files) . "</p>";
echo "<p><strong>Successful updates:</strong> $success_count</p>";
echo "<p><strong>Links fixed/removed:</strong> $removed_links</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ All unused links have been cleaned up!</strong><br>";
    echo "• Fixed broken file references<br>";
    echo "• Updated navigation links to current files<br>";
    echo "• Removed placeholder and broken links<br>";
    echo "• Created redirect for old index.html<br>";
    echo "• Identified potentially unused files";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Cleanup completed with some errors.</strong><br>";
    echo "Please review the errors above and fix manually if needed.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index-improved.html' class='btn'><i class='fas fa-home'></i> Go to Home</a>";
echo "<a href='admin-dashboard-with-logo.php' class='btn'><i class='fas fa-tachometer-alt'></i> Admin Dashboard</a>";
echo "<a href='course-catalog.php' class='btn'><i class='fas fa-graduation-cap'></i> Course Catalog</a>";
echo "</div>";

echo "</div></body></html>";
?>