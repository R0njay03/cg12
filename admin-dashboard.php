<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<div class="sidebar">
  <h2>CG-12 Admin</h2>
  <ul>
    <li><a href="admin-dashboard.php" class="active">Dashboard</a></li>
    <li><a href="manage-users.php">Manage Users</a></li>
    <li><a href="manage-courses.php">Manage Courses</a></li>
    <li><a href="reports.html">Reports</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</div>
<div class="main-content">
  <header>
    <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
  </header>
  <section class="cards">
    <div class="card"><h3>Total Users</h3><p>120</p></div>
    <div class="card"><h3>Courses Offered</h3><p>25</p></div>
    <div class="card"><h3>Reports Generated</h3><p>15</p></div>
  </section>
</div>
</body>
</html>
