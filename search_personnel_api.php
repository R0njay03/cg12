<?php
session_start();
include 'db.php';

// Set JSON header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Build search query
    $where_conditions = [];
    $params = [];
    $param_types = '';

    // Name search (searches both firstname and lastname)
    if (!empty($_GET['name'])) {
        $name_search = '%' . $_GET['name'] . '%';
        $where_conditions[] = "(firstname LIKE ? OR lastname LIKE ? OR CONCAT(firstname, ' ', lastname) LIKE ?)";
        $params[] = $name_search;
        $params[] = $name_search;
        $params[] = $name_search;
        $param_types .= 'sss';
    }

    // Rank filter
    if (!empty($_GET['rank'])) {
        $where_conditions[] = "rank = ?";
        $params[] = $_GET['rank'];
        $param_types .= 's';
    }

    // Unit filter
    if (!empty($_GET['unit'])) {
        $where_conditions[] = "unit_code = ?";
        $params[] = $_GET['unit'];
        $param_types .= 's';
    }

    // Category filter
    if (!empty($_GET['category'])) {
        $where_conditions[] = "category = ?";
        $params[] = $_GET['category'];
        $param_types .= 's';
    }

    // Training search (searches multiple training fields)
    if (!empty($_GET['training'])) {
        $training_search = '%' . $_GET['training'] . '%';
        $where_conditions[] = "(cgoc_class LIKE ? OR cgmc_cgnoc_class LIKE ? OR cgscc_class LIKE ? OR cgsc_class LIKE ? OR cgec_class LIKE ? OR functional_course LIKE ?)";
        $params[] = $training_search;
        $params[] = $training_search;
        $params[] = $training_search;
        $params[] = $training_search;
        $params[] = $training_search;
        $params[] = $training_search;
        $param_types .= 'ssssss';
    }

    // Specialization search
    if (!empty($_GET['specialization'])) {
        $spec_search = '%' . $_GET['specialization'] . '%';
        $where_conditions[] = "specialization LIKE ?";
        $params[] = $spec_search;
        $param_types .= 's';
    }

    // Date range filters
    if (!empty($_GET['date_from'])) {
        $where_conditions[] = "(date_entered_service >= ? OR last_promotion_date >= ? OR original_enlistment >= ?)";
        $params[] = $_GET['date_from'];
        $params[] = $_GET['date_from'];
        $params[] = $_GET['date_from'];
        $param_types .= 'sss';
    }

    if (!empty($_GET['date_to'])) {
        $where_conditions[] = "(date_entered_service <= ? OR last_promotion_date <= ? OR original_enlistment <= ?)";
        $params[] = $_GET['date_to'];
        $params[] = $_GET['date_to'];
        $params[] = $_GET['date_to'];
        $param_types .= 'sss';
    }

    // Seminar/Workshop search
    if (!empty($_GET['seminar'])) {
        $seminar_search = '%' . $_GET['seminar'] . '%';
        $where_conditions[] = "seminars_workshops LIKE ?";
        $params[] = $seminar_search;
        $param_types .= 's';
    }

    // Serial number search
    if (!empty($_GET['serial'])) {
        $serial_search = '%' . $_GET['serial'] . '%';
        $where_conditions[] = "serial_number LIKE ?";
        $params[] = $serial_search;
        $param_types .= 's';
    }

    // Build the complete query
    $base_query = "SELECT * FROM personnel";
    
    if (!empty($where_conditions)) {
        $base_query .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $base_query .= " ORDER BY lastname, firstname";

    // Add limit to prevent excessive results
    $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 1000) : 100;
    $base_query .= " LIMIT " . $limit;

    // Prepare and execute query
    $stmt = $conn->prepare($base_query);
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all personnel records
    $personnel = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates for display
        if ($row['date_entered_service']) {
            $row['date_entered_service_formatted'] = date('M j, Y', strtotime($row['date_entered_service']));
        }
        if ($row['last_promotion_date']) {
            $row['last_promotion_date_formatted'] = date('M j, Y', strtotime($row['last_promotion_date']));
        }
        if ($row['original_enlistment']) {
            $row['original_enlistment_formatted'] = date('M j, Y', strtotime($row['original_enlistment']));
        }

        // Add computed fields
        $row['full_name'] = trim($row['firstname'] . ' ' . ($row['mi'] ? $row['mi'] . '. ' : '') . $row['lastname']);
        
        // Determine primary training
        $primary_training = '';
        if ($row['category'] === 'Officer') {
            $primary_training = $row['cgoc_class'] ?: ($row['cgscc_class'] ?: ($row['cgsc_class'] ?: ''));
        } else {
            $primary_training = $row['cgmc_cgnoc_class'] ?: ($row['functional_course'] ?: '');
        }
        $row['primary_training'] = $primary_training;

        $personnel[] = $row;
    }

    // Get total count for pagination info
    $count_query = "SELECT COUNT(*) as total FROM personnel";
    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_count = $count_result->fetch_assoc()['total'];

    // Return successful response
    echo json_encode([
        'success' => true,
        'personnel' => $personnel,
        'total_count' => $total_count,
        'returned_count' => count($personnel),
        'search_parameters' => $_GET
    ]);

} catch (Exception $e) {
    // Return error response
    error_log("Personnel search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching personnel records',
        'error_details' => $e->getMessage()
    ]);
}

$conn->close();
?>