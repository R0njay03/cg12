<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Check if users table exists and has required columns
$table_exists = false;
$has_created_at = false;

$check_table = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_table && $check_table->num_rows > 0) {
    $table_exists = true;
    
    // Check if created_at column exists
    $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    $has_created_at = $check_column && $check_column->num_rows > 0;
}

// Handle user actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $table_exists) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $role = $_POST['role'];
                
                // Check if username already exists
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check_stmt->bind_param("s", $username);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $message = "Username already exists!";
                    $message_type = "error";
                } else {
                    // Hash password and insert user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $hashed_password, $role);
                    
                    if ($stmt->execute()) {
                        $message = "User added successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error adding user: " . $conn->error;
                        $message_type = "error";
                    }
                }
                break;
                
            case 'delete_user':
                $user_id = $_POST['user_id'];
                
                // Don't allow deletion of current user
                $current_user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $current_user_stmt->bind_param("s", $_SESSION['username']);
                $current_user_stmt->execute();
                $current_user_result = $current_user_stmt->get_result();
                $current_user = $current_user_result->fetch_assoc();
                
                if ($current_user && $current_user['id'] == $user_id) {
                    $message = "Cannot delete your own account!";
                    $message_type = "error";
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    
                    if ($stmt->execute()) {
                        $message = "User deleted successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Error deleting user: " . $conn->error;
                        $message_type = "error";
                    }
                }
                break;
        }
    }
}

// Fetch all users with appropriate ORDER BY clause
$users_result = null;
if ($table_exists) {
    if ($has_created_at) {
        $users_query = "SELECT * FROM users ORDER BY created_at DESC";
    } else {
        $users_query = "SELECT * FROM users ORDER BY id DESC";
    }
    $users_result = $conn->query($users_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | PCG CG-12</title>
    <link rel="stylesheet" href="admin-style-enhanced.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #002147;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 3px rgba(0, 33, 71, 0.1);
        }
        
        .users-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5em;
        }
        
        .user-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 120px;
            gap: 20px;
            padding: 25px;
            border-bottom: 1px solid #e9ecef;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .user-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .user-info h4 {
            margin: 0 0 8px 0;
            color: #002147;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-role {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .role-admin {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .role-user {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        .user-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        
        .toggle-form {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1em;
        }
        
        .toggle-form:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }
        
        .form-container {
            display: none;
            animation: slideDown 0.4s ease;
        }
        
        .form-container.active {
            display: block;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message {
            padding: 18px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            text-align: center;
            border-top: 4px solid #002147;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 3em;
            color: #002147;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.8em;
            font-weight: bold;
            color: #c8102e;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 1px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3);
        }
        
        .no-users {
            text-align: center;
            padding: 60px 40px;
            color: #666;
        }
        
        .no-users i {
            font-size: 5em;
            color: #ddd;
            margin-bottom: 25px;
        }
        
        .no-users h3 {
            color: #002147;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        
        .protected-user {
            color: #28a745;
            font-weight: 600;
            font-size: 0.85em;
            display: flex;
            align-items: center;
            gap: 5px;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        <ul>
            <li><a href="admin-dashboard-with-logo.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users-fixed.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="course-catalog.php"><i class="fas fa-book"></i> Course Catalog</a></li>
            <li><a href="reports.html"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h1><i class="fas fa-users"></i> User Management</h1>
            <p>Manage system users and access permissions for the PCG CG-12 training system</p>
        </header>

        <?php if (!$table_exists): ?>
            <div class="database-notice">
                <strong><i class="fas fa-exclamation-triangle"></i> Users Table Not Found:</strong> 
                <a href="fix_users_table.php"><i class="fas fa-database"></i> Fix Users Table</a>
                to enable user management functionality.
            </div>
        <?php elseif (!$has_created_at): ?>
            <div class="database-notice">
                <strong><i class="fas fa-exclamation-triangle"></i> Table Structure Update Required:</strong> 
                <a href="fix_users_table.php"><i class="fas fa-database"></i> Update Table Structure</a>
                for full functionality.
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($table_exists && $users_result): ?>
            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $users_result->num_rows; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="stat-number">
                        <?php 
                        $admin_count = 0;
                        $users_result->data_seek(0);
                        while ($user = $users_result->fetch_assoc()) {
                            if ($user['role'] == 'admin') $admin_count++;
                        }
                        echo $admin_count;
                        ?>
                    </div>
                    <div class="stat-label">Administrators</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user"></i></div>
                    <div class="stat-number"><?php echo $users_result->num_rows - $admin_count; ?></div>
                    <div class="stat-label">Regular Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number">24</div>
                    <div class="stat-label">Hours Active</div>
                </div>
            </div>

            <!-- Add User Form -->
            <button class="toggle-form" onclick="toggleForm()">
                <i class="fas fa-plus"></i> Add New User
            </button>

            <div class="form-container" id="userForm">
                <div class="user-form">
                    <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                    <p style="color: #666; margin-bottom: 25px;">Create a new user account for the PCG CG-12 training system</p>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Username</label>
                                <input type="text" id="username" name="username" required placeholder="Enter username">
                            </div>
                            
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" id="password" name="password" required placeholder="Enter password">
                            </div>
                            
                            <div class="form-group">
                                <label for="role"><i class="fas fa-user-tag"></i> Role</label>
                                <select id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Administrator</option>
                                    <option value="user">Regular User</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="users-table">
                <div class="table-header">
                    <h2><i class="fas fa-list"></i> System Users</h2>
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; font-weight: 600;"><?php echo $users_result->num_rows; ?> users</span>
                </div>
                
                <?php if ($users_result->num_rows > 0): ?>
                    <?php 
                    $users_result->data_seek(0);
                    while($user = $users_result->fetch_assoc()): 
                    ?>
                        <div class="user-row">
                            <div class="user-info">
                                <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></h4>
                                <?php if ($user['username'] == $_SESSION['username']): ?>
                                    <small style="color: #28a745; font-weight: 600;">(Current User)</small>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <span class="user-role role-<?php echo $user['role']; ?>">
                                    <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user'; ?>"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </div>
                            
                            <div style="color: #666; font-size: 0.9em;">
                                <i class="fas fa-calendar"></i>
                                <?php 
                                if ($has_created_at && isset($user['created_at'])) {
                                    echo date('M j, Y', strtotime($user['created_at']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                            
                            <div style="color: #666; font-size: 0.9em;">
                                ID: <?php echo $user['id']; ?>
                            </div>
                            
                            <div class="user-actions">
                                <?php if ($user['username'] != $_SESSION['username']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="protected-user">
                                        <i class="fas fa-shield-alt"></i> Protected
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-users">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>Add some users to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-users">
                <i class="fas fa-database"></i>
                <h3>Database Setup Required</h3>
                <p>The users table needs to be created or fixed before you can manage users.</p>
                <a href="fix_users_table.php" class="submit-btn" style="text-decoration: none; margin-top: 20px;">
                    <i class="fas fa-database"></i> Fix Database
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('userForm');
            form.classList.toggle('active');
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.6s ease forwards';
            });
        });

        // Add CSS for fade in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>