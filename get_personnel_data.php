<?php
session_start();
include 'db.php';

// Check authentication
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Personnel ID required']);
    exit();
}

$personnel_id = (int)$_GET['id'];

// Fetch personnel data
$stmt = $conn->prepare("SELECT * FROM personnel WHERE id = ?");
$stmt->bind_param("i", $personnel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $personnel = $result->fetch_assoc();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($personnel);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Personnel not found']);
}

$stmt->close();
$conn->close();
?> 