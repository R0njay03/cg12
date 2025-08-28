<?php
// Script to update navigation links in admin dashboard

$file_path = 'admin-dashboard-fixed.php';
$content = file_get_contents($file_path);

// Update sidebar navigation link
$content = str_replace(
    '<li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>',
    '<li><a href="manage-users.html"><i class="fas fa-users"></i> Manage Users</a></li>',
    $content
);

// Update quick action link
$content = str_replace(
    '<a href="manage-users.php" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Users</a>',
    '<a href="manage-users.html" class="action-btn"><i class="fas fa-arrow-right"></i> Manage Users</a>',
    $content
);

// Save the updated content
file_put_contents($file_path, $content);

echo "Links updated successfully!<br>";
echo "<a href='admin-dashboard-fixed.php'>Go to Admin Dashboard</a><br>";
echo "<a href='manage-users.html'>Go to Manage Users</a>";
?>