<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            
            // Redirect to enhanced admin dashboard
            header("Location: admin-dashboard-enhanced-v2.php");
            exit();
        } else {
            // Invalid password
            echo "<script>
                alert('Invalid password. Please try again.');
                window.location='login.html';
            </script>";
        }
    } else {
        // User not found
        echo "<script>
            alert('User not found. Please check your username.');
            window.location='login.html';
        </script>";
    }
    
    $stmt->close();
} else {
    // If not POST request, redirect to login page
    header("Location: login.html");
    exit();
}

$conn->close();
?>