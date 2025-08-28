<?php
// Script to update all navigation links to point to index-improved.html

echo "<!DOCTYPE html>";
echo "<html><head><title>Update Navigation Links - PCG CG-12</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.header { background: #002147; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
.success { color: green; padding: 8px; background: #f0f8f0; border-left: 3px solid green; margin: 5px 0; border-radius: 3px; }
.error { color: red; padding: 8px; background: #f8f0f0; border-left: 3px solid red; margin: 5px 0; border-radius: 3px; }
.info { color: blue; padding: 8px; background: #f0f8ff; border-left: 3px solid blue; margin: 5px 0; border-radius: 3px; }
.btn { background: #002147; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block; font-weight: bold; }
.btn:hover { background: #c8102e; }
</style></head><body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>Update Navigation Links</h1>";
echo "<p>Updating all files to link to index-improved.html</p>";
echo "</div>";

$success_count = 0;
$error_count = 0;

// List of files to update
$files_to_update = [
    'admin-dashboard-with-logo.php',
    'manage-users.html',
    'manage-users-fixed.php',
    'course-materials.php',
    'course-catalog.php',
    'course-details.php',
    'manage-courses.php',
    'reports.html',
    'login.html'
];

// Patterns to replace
$replacements = [
    // Replace index.html with index-improved.html
    'href="index.html"' => 'href="index-improved.html"',
    "href='index.html'" => "href='index-improved.html'",
    'href="index.html#' => 'href="index-improved.html#',
    "href='index.html#" => "href='index-improved.html#",
    
    // Update navigation text to include Home icon
    '<a href="index-improved.html">Home</a>' => '<a href="index-improved.html"><i class="fas fa-home"></i> Home</a>',
    
    // Update any remaining old references
    'window.location.href = "index.html"' => 'window.location.href = "index-improved.html"',
];

foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        echo "<div class='info'>Processing: $file</div>";
        
        $content = file_get_contents($file);
        $original_content = $content;
        
        // Apply all replacements
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Check if content was modified
        if ($content !== $original_content) {
            if (file_put_contents($file, $content)) {
                echo "<div class='success'>✓ Updated navigation links in $file</div>";
                $success_count++;
            } else {
                echo "<div class='error'>✗ Failed to update $file</div>";
                $error_count++;
            }
        } else {
            echo "<div class='info'>→ No changes needed in $file</div>";
        }
    } else {
        echo "<div class='error'>✗ File not found: $file</div>";
        $error_count++;
    }
}

// Update course-catalog.php navbar specifically
$course_catalog_file = 'course-catalog.php';
if (file_exists($course_catalog_file)) {
    echo "<div class='info'>Updating course-catalog.php navbar...</div>";
    
    $content = file_get_contents($course_catalog_file);
    
    // Update navbar in course-catalog.php
    $old_navbar = '<nav class="navbar">
      <a href="index.html">Home</a>
      <a href="course-catalog.php" class="active">Course Catalog</a>
      <a href="reports.html">Reports</a>
      <a href="index.html#contact">Contact</a>
    </nav>';
    
    $new_navbar = '<nav class="navbar">
      <a href="index-improved.html"><i class="fas fa-home"></i> Home</a>
      <a href="course-catalog.php" class="active"><i class="fas fa-graduation-cap"></i> Course Catalog</a>
      <a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a>
      <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
    </nav>';
    
    $content = str_replace($old_navbar, $new_navbar, $content);
    
    if (file_put_contents($course_catalog_file, $content)) {
        echo "<div class='success'>✓ Updated course-catalog.php navbar</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to update course-catalog.php navbar</div>";
        $error_count++;
    }
}

// Update course-details.php navbar
$course_details_file = 'course-details.php';
if (file_exists($course_details_file)) {
    echo "<div class='info'>Updating course-details.php navbar...</div>";
    
    $content = file_get_contents($course_details_file);
    
    // Update navbar in course-details.php
    $old_navbar = '<nav class="navbar">
            <a href="index.html">Home</a>
            <a href="course-catalog.php">Course Catalog</a>
            <a href="reports.html">Reports</a>
            <a href="index.html#contact">Contact</a>
        </nav>';
    
    $new_navbar = '<nav class="navbar">
            <a href="index-improved.html"><i class="fas fa-home"></i> Home</a>
            <a href="course-catalog.php"><i class="fas fa-graduation-cap"></i> Course Catalog</a>
            <a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
        </nav>';
    
    $content = str_replace($old_navbar, $new_navbar, $content);
    
    if (file_put_contents($course_details_file, $content)) {
        echo "<div class='success'>✓ Updated course-details.php navbar</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to update course-details.php navbar</div>";
        $error_count++;
    }
}

// Update login.html redirect
$login_file = 'login.html';
if (file_exists($login_file)) {
    echo "<div class='info'>Updating login.html...</div>";
    
    $content = file_get_contents($login_file);
    
    // Update any references to index.html in login
    $content = str_replace('href="index.html"', 'href="index-improved.html"', $content);
    $content = str_replace("href='index.html'", "href='index-improved.html'", $content);
    
    if (file_put_contents($login_file, $content)) {
        echo "<div class='success'>✓ Updated login.html</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to update login.html</div>";
        $error_count++;
    }
}

// Update logout.php redirect
$logout_file = 'logout.php';
if (file_exists($logout_file)) {
    echo "<div class='info'>Updating logout.php...</div>";
    
    $content = file_get_contents($logout_file);
    
    // Update logout redirect
    $content = str_replace('header("Location: index.html")', 'header("Location: index-improved.html")', $content);
    $content = str_replace("header('Location: index.html')", "header('Location: index-improved.html')", $content);
    
    if (file_put_contents($logout_file, $content)) {
        echo "<div class='success'>✓ Updated logout.php</div>";
        $success_count++;
    } else {
        echo "<div class='error'>✗ Failed to update logout.php</div>";
        $error_count++;
    }
}

// Summary
echo "<div style='background: #e8f4f8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3 style='color: #002147;'>Navigation Links Update Complete!</h3>";
echo "<p><strong>Successful updates:</strong> $success_count</p>";
echo "<p><strong>Errors:</strong> $error_count</p>";

if ($error_count == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>✓ All navigation links updated successfully!</strong><br>";
    echo "All files now link to index-improved.html as the home page.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<strong>⚠ Update completed with some errors.</strong><br>";
    echo "Please review the errors above.";
    echo "</div>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index-improved.html' class='btn'>Go to Home Page</a>";
echo "<a href='admin-dashboard-with-logo.php' class='btn'>Admin Dashboard</a>";
echo "<a href='course-catalog.php' class='btn'>Course Catalog</a>";
echo "</div>";

echo "</div></body></html>";
?>