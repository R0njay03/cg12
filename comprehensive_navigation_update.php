<?php
// Comprehensive script to update all navigation links

echo "<!DOCTYPE html>";
echo "<html><head><title>Comprehensive Navigation Update - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.warning { color: orange; padding: 8px; background: #fff8f0; border-left: 3px solid orange; margin: 5px 0; border-radius: 3px; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
.file-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #002147; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Comprehensive Navigation Update</h1>";
echo "<p>Updating all files to use index-improved.html and proper navigation</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// Function to update file content
function updateFileContent($filename, $updates, &$success_count, &$error_count) {
    echo "<div class='file-section'>";
    echo "<h3>Updating: $filename</h3>";
    
    if (!file_exists($filename)) {
        echo "<div class='warning'>File not found: $filename</div>";
        return;
    }
    
    $content = file_get_contents($filename);
    $original_content = $content;
    $changes_made = 0;
    
    foreach ($updates as $search => $replace) {
        $new_content = str_replace($search, $replace, $content);
        if ($new_content !== $content) {
            $content = $new_content;
            $changes_made++;
            echo "<div class='info'>→ Replaced: " . htmlspecialchars(substr($search, 0, 50)) . "...</div>";
        }
    }
    
    if ($changes_made > 0) {
        if (file_put_contents($filename, $content)) {
            echo "<div class='success'>✓ Successfully updated $filename ($changes_made changes)</div>";
            $success_count++;
        } else {
            echo "<div class='error'>✗ Failed to write to $filename</div>";
            $error_count++;
        }
    } else {
        echo "<div class='info'>→ No changes needed in $filename</div>";
    }
    
    echo "</div>";
}

// Common navigation updates
$common_updates = [
    // Basic index.html replacements
    'href="index.html"' => 'href="index-improved.html"',
    "href='index.html'" => "href='index-improved.html'",
    'href="index.html#' => 'href="index-improved.html#',
    "href='index.html#" => "href='index-improved.html#",
    
    // JavaScript redirects
    'window.location.href = "index.html"' => 'window.location.href = "index-improved.html"',
    "window.location.href = 'index.html'" => "window.location.href = 'index-improved.html'",
    'location.href = "index.html"' => 'location.href = "index-improved.html"',
    "location.href = 'index.html'" => "location.href = 'index-improved.html'",
    
    // PHP redirects
    'header("Location: index.html")' => 'header("Location: index-improved.html")',
    "header('Location: index.html')" => "header('Location: index-improved.html')",
];

// Update admin-dashboard-with-logo.php
$admin_dashboard_updates = array_merge($common_updates, [
    // Add Home link to sidebar if not present
    '<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>' => 
    '<li><a href="index-improved.html"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>',
]);

updateFileContent('admin-dashboard-with-logo.php', $admin_dashboard_updates, $success_count, $error_count);

// Update manage-users.html
$manage_users_updates = array_merge($common_updates, [
    'href="admin-dashboard-fixed.php"' => 'href="admin-dashboard-with-logo.php"',
    'href="admin-dashboard.html"' => 'href="admin-dashboard-with-logo.php"',
]);

updateFileContent('manage-users.html', $manage_users_updates, $success_count, $error_count);

// Update manage-users-fixed.php
updateFileContent('manage-users-fixed.php', $manage_users_updates, $success_count, $error_count);

// Update course-materials.php
$course_materials_updates = array_merge($common_updates, [
    'href="admin-dashboard.html"' => 'href="admin-dashboard-with-logo.php"',
    'href="admin-dashboard-fixed.php"' => 'href="admin-dashboard-with-logo.php"',
]);

updateFileContent('course-materials.php', $course_materials_updates, $success_count, $error_count);

// Update course-catalog.php
$course_catalog_updates = array_merge($common_updates, [
    // Update navbar
    '<nav class="navbar">
      <a href="index.html">Home</a>
      <a href="course-catalog.php" class="active">Course Catalog</a>
      <a href="reports.html">Reports</a>
      <a href="index.html#contact">Contact</a>
    </nav>' => 
    '<nav class="navbar">
      <a href="index-improved.html"><i class="fas fa-home"></i> Home</a>
      <a href="course-catalog.php" class="active"><i class="fas fa-graduation-cap"></i> Course Catalog</a>
      <a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a>
      <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
    </nav>',
    
    // Update login button
    '<a href="login.html" class="login-btn">Admin Login</a>' => 
    '<a href="login.html" class="login-btn"><i class="fas fa-sign-in-alt"></i> Admin Login</a>',
]);

updateFileContent('course-catalog.php', $course_catalog_updates, $success_count, $error_count);

// Update course-details.php
$course_details_updates = array_merge($common_updates, [
    // Update navbar
    '<nav class="navbar">
            <a href="index.html">Home</a>
            <a href="course-catalog.php">Course Catalog</a>
            <a href="reports.html">Reports</a>
            <a href="index.html#contact">Contact</a>
        </nav>' => 
    '<nav class="navbar">
            <a href="index-improved.html"><i class="fas fa-home"></i> Home</a>
            <a href="course-catalog.php"><i class="fas fa-graduation-cap"></i> Course Catalog</a>
            <a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
        </nav>',
        
    // Update login button
    '<a href="login.html" class="login-btn">Admin Login</a>' => 
    '<a href="login.html" class="login-btn"><i class="fas fa-sign-in-alt"></i> Admin Login</a>',
]);

updateFileContent('course-details.php', $course_details_updates, $success_count, $error_count);

// Update manage-courses.php
$manage_courses_updates = array_merge($common_updates, [
    'href="admin-dashboard.html"' => 'href="admin-dashboard-with-logo.php"',
    'href="admin-dashboard-fixed.php"' => 'href="admin-dashboard-with-logo.php"',
]);

updateFileContent('manage-courses.php', $manage_courses_updates, $success_count, $error_count);

// Update login.html
$login_updates = array_merge($common_updates, [
    // Update any back to home links
    '>Home<' => '><i class="fas fa-home"></i> Home<',
]);

updateFileContent('login.html', $login_updates, $success_count, $error_count);

// Update logout.php
updateFileContent('logout.php', $common_updates, $success_count, $error_count);

// Update reports.html
$reports_updates = array_merge($common_updates, [
    // Update navbar if it exists
    '<a href="index.html">Home</a>' => '<a href="index-improved.html"><i class="fas fa-home"></i> Home</a>',
    '<a href="index.html#contact">Contact</a>' => '<a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>',
]);

updateFileContent('reports.html', $reports_updates, $success_count, $error_count);

// Create a simple redirect from old index.html to new one
$redirect_content = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=index-improved.html">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting to the new home page...</p>
    <p>If you are not redirected automatically, <a href="index-improved.html">click here</a>.</p>
</body>
</html>';

if (file_put_contents('index.html', $redirect_content)) {
    echo "<div class='file-section'>";
    echo "<h3>Created redirect: index.html</h3>";
    echo "<div class='success'>✓ Created redirect from index.html to index-improved.html</div>";
    echo "</div>";
    $success_count++;
} else {
    echo "<div class='file-section'>";
    echo "<h3>Failed to create redirect: index.html</h3>";
    echo "<div class='error'>✗ Could not create redirect file</div>";
    echo "</div>";
    $error_count++;
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Comprehensive Navigation Update Complete!</h3>";
echo "<p><strong>Successful updates:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ All navigation links updated successfully!</strong><br>";
    echo "• All files now link to index-improved.html as the home page<br>";
    echo "• Admin dashboard links updated to admin-dashboard-with-logo.php<br>";
    echo "• Contact links updated to contact.html<br>";
    echo "• Navigation icons added for better user experience<br>";
    echo "• Redirect created from old index.html to new version";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Update completed with some errors.</strong><br>";
    echo "Please review the errors above and manually fix any remaining issues.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index-improved.html' class='btn'><i class='fas fa-home'></i> Go to Home Page</a>";
echo "<a href='admin-dashboard-with-logo.php' class='btn'><i class='fas fa-tachometer-alt'></i> Admin Dashboard</a>";
echo "<a href='course-catalog.php' class='btn'><i class='fas fa-graduation-cap'></i> Course Catalog</a>";
echo "<a href='contact.html' class='btn'><i class='fas fa-envelope'></i> Contact</a>";
echo "</div>";

echo "</div></body></html>";
?>