<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Handle user actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                
                if ($current_user['id'] == $user_id) {
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

// Fetch all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | PCG CG-12</title>
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #002147;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #002147;
        }
        
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #002147;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 150px 120px;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            align-items: center;
        }
        
        .user-row:hover {
            background-color: #f8f9fa;
        }
        
        .user-info h4 {
            margin: 0 0 5px 0;
            color: #002147;
            font-size: 1.1em;
        }
        
        .user-role {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .role-admin {
            background: #d4edda;
            color: #155724;
        }
        
        .role-user {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .user-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            transition: background 0.3s ease;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .toggle-form {
            background: #002147;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .toggle-form:hover {
            background: #c8102e;
            transform: scale(1.05);
        }
        
        .form-container {
            display: none;
            animation: slideDown 0.3s ease;
        }
        
        .form-container.active {
            display: block;
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
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #002147;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #c8102e;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-anchor"></i> CG-12 Admin</h2>
        <ul>
            <li><a href="admin-dashboard-enhanced.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage-users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-courses.php"><i class="fas fa-graduation-cap"></i> Manage Courses</a></li>
            <li><a href="course-materials.php"><i class="fas fa-file-pdf"></i> Course Materials</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h1><i class="fas fa-users"></i> User Management</h1>
            <p>Manage system users and access permissions</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number"><?php echo $users_result->num_rows; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
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
                <div class="stat-number"><?php echo $users_result->num_rows - $admin_count; ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>

        <!-- Add User Form -->
        <button class="toggle-form" onclick="toggleForm()">
            <i class="fas fa-plus"></i> Add New User
        </button>

        <div class="form-container" id="userForm">
            <div class="user-form">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role"><i class="fas fa-user-tag"></i> Role</label>
                            <select id="role" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="user">Regular User</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" style="background: #002147; color: white; padding: 12px 30px; border: none; border-radius: 25px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </form>
            </div>
        </div>

        <!-- Users List -->
        <div class="users-table">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> System Users</h2>
                <span><?php echo $users_result->num_rows; ?> users</span>
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
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
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
                                <span style="color: #666; font-size: 0.85em;">Protected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-users" style="font-size: 4em; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3>No Users Found</h3>
                    <p>Add some users to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('userForm');
            form.classList.toggle('active');
        }
    </script>
</body>
</html>