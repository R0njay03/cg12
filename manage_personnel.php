<?php
session_start();
include 'db.php';

// Check user role and authentication
$is_authenticated = isset($_SESSION['username']) && isset($_SESSION['role']);
$is_admin = $is_authenticated && $_SESSION['role'] === 'admin';
$is_user = $is_authenticated && $_SESSION['role'] === 'user';

// Redirect if not authenticated
if (!$is_authenticated) {
    header("Location: login.html");
    exit();
}

// Handle File Upload
if ($is_admin && isset($_POST['upload_file'])) {
    $upload_success = false;
    $upload_message = '';
    
    if (isset($_FILES['personnel_file']) && $_FILES['personnel_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['personnel_file']['name'], PATHINFO_EXTENSION));
        
        // Check if it's a valid file
        if (in_array($file_extension, ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])) {
            $upload_dir = 'uploads/personnel/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . $_FILES['personnel_file']['name'];
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['personnel_file']['tmp_name'], $file_path)) {
                $personnel_id = (int)$_POST['personnel_id'];
                $stmt = $conn->prepare("UPDATE personnel SET upload_file = ? WHERE id = ?");
                $stmt->bind_param("si", $file_path, $personnel_id);
                
                if ($stmt->execute()) {
                    $upload_success = true;
                    $upload_message = "File uploaded successfully.";
                } else {
                    $upload_message = "Error updating database.";
                }
            } else {
                $upload_message = "Error uploading file.";
            }
        } else {
            $upload_message = "Invalid file format. Please upload PDF, DOC, DOCX, JPG, JPEG, or PNG files only.";
        }
    } else {
        $upload_message = "Please select a valid file to upload.";
    }
}

// Handle Excel/CSV Upload
if ($is_admin && isset($_POST['upload_excel'])) {
    $upload_success = false;
    $upload_message = '';
    
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        
        // Check if it's a valid Excel/CSV file
        if (in_array($file_extension, ['xlsx', 'xls', 'csv'])) {
            $filePath = $_FILES['excel_file']['tmp_name'];
            
            try {
                // For CSV files, use simple parsing
                if ($file_extension === 'csv') {
                    $handle = fopen($filePath, 'r');
                    if ($handle !== FALSE) {
                        $row = 0;
                        $success_count = 0;
                        $error_count = 0;
                        
                        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            $row++;
                            if ($row == 1) continue; // Skip header row
                            
                            if (count($data) >= 8) {
                                $rank = trim($data[0]);
                                $lastname = trim($data[1]);
                                $firstname = trim($data[2]);
                                $mi = trim($data[3]);
                                $serial_number = trim($data[4]);
                                $unit_code = trim($data[5]);
                                $sub_unit = trim($data[6]);
                                $category = trim($data[7]);
                                
                                // Validate category
                                if (!in_array($category, ['Officer', 'Non-Officer'])) {
                                    $category = 'Non-Officer'; // Default
                                }
                                
                                // Additional fields for comprehensive data
                                $cgmc_cgnoc_class = isset($data[8]) ? trim($data[8]) : '';
                                $specialization = isset($data[9]) ? trim($data[9]) : '';
                                $functional_course = isset($data[10]) ? trim($data[10]) : '';
                                $blmc_almc_cgnoac = isset($data[11]) ? trim($data[11]) : '';
                                $cgnosec = isset($data[12]) ? trim($data[12]) : '';
                                $original_enlistment = isset($data[13]) ? trim($data[13]) : '';
                                $date_entered_service = isset($data[14]) ? trim($data[14]) : '';
                                $comp_ret = isset($data[15]) ? trim($data[15]) : '';
                                $last_promotion_date = isset($data[16]) ? trim($data[16]) : '';
                                $cgoc_class = isset($data[17]) ? trim($data[17]) : '';
                                $cgscc_class = isset($data[18]) ? trim($data[18]) : '';
                                $cgsc_class = isset($data[19]) ? trim($data[19]) : '';
                                $cgec_class = isset($data[20]) ? trim($data[20]) : '';
                                $third_level_career = isset($data[21]) ? trim($data[21]) : '';
                                $seminars_workshops = isset($data[22]) ? trim($data[22]) : '';
                                $remarks = isset($data[23]) ? trim($data[23]) : '';
                                
                                // Insert or Update using comprehensive fields
                                $stmt = $conn->prepare("INSERT INTO personnel (
                                    rank, lastname, firstname, mi, serial_number, unit_code, sub_unit, category,
                                    cgmc_cgnoc_class, specialization, functional_course, blmc_almc_cgnoac, cgnosec,
                                    original_enlistment, date_entered_service, comp_ret, last_promotion_date,
                                    cgoc_class, cgscc_class, cgsc_class, cgec_class, third_level_career,
                                    seminars_workshops, remarks
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE 
                                    rank=VALUES(rank), lastname=VALUES(lastname), firstname=VALUES(firstname), 
                                    mi=VALUES(mi), unit_code=VALUES(unit_code), sub_unit=VALUES(sub_unit), 
                                    category=VALUES(category), cgmc_cgnoc_class=VALUES(cgmc_cgnoc_class),
                                    specialization=VALUES(specialization), functional_course=VALUES(functional_course),
                                    blmc_almc_cgnoac=VALUES(blmc_almc_cgnoac), cgnosec=VALUES(cgnosec),
                                    original_enlistment=VALUES(original_enlistment), date_entered_service=VALUES(date_entered_service),
                                    comp_ret=VALUES(comp_ret), last_promotion_date=VALUES(last_promotion_date),
                                    cgoc_class=VALUES(cgoc_class), cgscc_class=VALUES(cgscc_class),
                                    cgsc_class=VALUES(cgsc_class), cgec_class=VALUES(cgec_class),
                                    third_level_career=VALUES(third_level_career), seminars_workshops=VALUES(seminars_workshops),
                                    remarks=VALUES(remarks)");
                                
                                $stmt->bind_param("ssssssssssssssssssssssss", 
                                    $rank, $lastname, $firstname, $mi, $serial_number, $unit_code, $sub_unit, $category,
                                    $cgmc_cgnoc_class, $specialization, $functional_course, $blmc_almc_cgnoac, $cgnosec,
                                    $original_enlistment, $date_entered_service, $comp_ret, $last_promotion_date,
                                    $cgoc_class, $cgscc_class, $cgsc_class, $cgec_class, $third_level_career,
                                    $seminars_workshops, $remarks);
                                
                                if ($stmt->execute()) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                }
                            }
                        }
                        fclose($handle);
                        
                        $upload_success = true;
                        $upload_message = "CSV data successfully uploaded. $success_count records processed successfully, $error_count errors.";
                    }
                } else {
                    // For Excel files, try to use PhpSpreadsheet if available
                    if (file_exists('vendor/autoload.php')) {
                        try {
                            require 'vendor/autoload.php';
                            
                            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                            $sheet = $spreadsheet->getActiveSheet();
                            $rows = $sheet->toArray();
                            
                            $success_count = 0;
                            $error_count = 0;
                            
                            // Skip header row
                            foreach ($rows as $index => $row) {
                                if ($index == 0) continue;
                                
                                if (count($row) >= 8) {
                                    $rank = trim($row[0]);
                                    $lastname = trim($row[1]);
                                    $firstname = trim($row[2]);
                                    $mi = trim($row[3]);
                                    $serial_number = trim($row[4]);
                                    $unit_code = trim($row[5]);
                                    $sub_unit = trim($row[6]);
                                    $category = trim($row[7]);
                                    
                                    // Validate category
                                    if (!in_array($category, ['Officer', 'Non-Officer'])) {
                                        $category = 'Non-Officer'; // Default
                                    }
                                    
                                    // Additional fields for comprehensive data
                                    $cgmc_cgnoc_class = isset($row[8]) ? trim($row[8]) : '';
                                    $specialization = isset($row[9]) ? trim($row[9]) : '';
                                    $functional_course = isset($row[10]) ? trim($row[10]) : '';
                                    $blmc_almc_cgnoac = isset($row[11]) ? trim($row[11]) : '';
                                    $cgnosec = isset($row[12]) ? trim($row[12]) : '';
                                    $original_enlistment = isset($row[13]) ? trim($row[13]) : '';
                                    $date_entered_service = isset($row[14]) ? trim($row[14]) : '';
                                    $comp_ret = isset($row[15]) ? trim($row[15]) : '';
                                    $last_promotion_date = isset($row[16]) ? trim($row[16]) : '';
                                    $cgoc_class = isset($row[17]) ? trim($row[17]) : '';
                                    $cgscc_class = isset($row[18]) ? trim($row[18]) : '';
                                    $cgsc_class = isset($row[19]) ? trim($row[19]) : '';
                                    $cgec_class = isset($row[20]) ? trim($row[20]) : '';
                                    $third_level_career = isset($row[21]) ? trim($row[21]) : '';
                                    $seminars_workshops = isset($row[22]) ? trim($row[22]) : '';
                                    $remarks = isset($row[23]) ? trim($row[23]) : '';
                                    
                                    // Insert or Update using comprehensive fields
                                    $stmt = $conn->prepare("INSERT INTO personnel (
                                        rank, lastname, firstname, mi, serial_number, unit_code, sub_unit, category,
                                        cgmc_cgnoc_class, specialization, functional_course, blmc_almc_cgnoac, cgnosec,
                                        original_enlistment, date_entered_service, comp_ret, last_promotion_date,
                                        cgoc_class, cgscc_class, cgsc_class, cgec_class, third_level_career,
                                        seminars_workshops, remarks
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE 
                                        rank=VALUES(rank), lastname=VALUES(lastname), firstname=VALUES(firstname), 
                                        mi=VALUES(mi), unit_code=VALUES(unit_code), sub_unit=VALUES(sub_unit), 
                                        category=VALUES(category), cgmc_cgnoc_class=VALUES(cgmc_cgnoc_class),
                                        specialization=VALUES(specialization), functional_course=VALUES(functional_course),
                                        blmc_almc_cgnoac=VALUES(blmc_almc_cgnoac), cgnosec=VALUES(cgnosec),
                                        original_enlistment=VALUES(original_enlistment), date_entered_service=VALUES(date_entered_service),
                                        comp_ret=VALUES(comp_ret), last_promotion_date=VALUES(last_promotion_date),
                                        cgoc_class=VALUES(cgoc_class), cgscc_class=VALUES(cgscc_class),
                                        cgsc_class=VALUES(cgsc_class), cgec_class=VALUES(cgec_class),
                                        third_level_career=VALUES(third_level_career), seminars_workshops=VALUES(seminars_workshops),
                                        remarks=VALUES(remarks)");
                                    
                                    $stmt->bind_param("ssssssssssssssssssssssss", 
                                        $rank, $lastname, $firstname, $mi, $serial_number, $unit_code, $sub_unit, $category,
                                        $cgmc_cgnoc_class, $specialization, $functional_course, $blmc_almc_cgnoac, $cgnosec,
                                        $original_enlistment, $date_entered_service, $comp_ret, $last_promotion_date,
                                        $cgoc_class, $cgscc_class, $cgsc_class, $cgec_class, $third_level_career,
                                        $seminars_workshops, $remarks);
                                    
                                    if ($stmt->execute()) {
                                        $success_count++;
                                    } else {
                                        $error_count++;
                                    }
                                }
                            }
                            
                            $upload_success = true;
                            $upload_message = "Excel data successfully uploaded. $success_count records processed successfully, $error_count errors.";
                        } catch (Exception $e) {
                            $upload_message = "Error processing Excel file: " . $e->getMessage() . ". Please use CSV format instead.";
                        }
                    } else {
                        $upload_message = "PhpSpreadsheet library not found. Please install it via Composer or use CSV format.";
                    }
                }
            } catch (Exception $e) {
                $upload_message = "Error processing file: " . $e->getMessage();
            }
        } else {
            $upload_message = "Invalid file format. Please upload .xlsx, .xls, or .csv files only.";
        }
    } else {
        $upload_message = "Please select a valid file to upload.";
    }
}

// Handle Add Personnel
if ($is_admin && isset($_POST['add_personnel'])) {
    $rank = trim($_POST['rank']);
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $mi = trim($_POST['mi']);
    $serial_number = trim($_POST['serial_number']);
    $unit_code = trim($_POST['unit_code']);
    $sub_unit = trim($_POST['sub_unit']);
    $category = trim($_POST['category']);
    
    // Validate required fields
    if (empty($rank) || empty($lastname) || empty($firstname) || empty($serial_number) || empty($unit_code) || empty($category)) {
        $add_message = "Please fill in all required fields.";
        $add_success = false;
    } else {
        // Prepare optional fields based on category
        $cgmc_cgnoc_class = trim($_POST['cgmc_cgnoc_class'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $functional_course = trim($_POST['functional_course'] ?? '');
        $blmc_almc_cgnoac = trim($_POST['blmc_almc_cgnoac'] ?? '');
        $cgnosec = trim($_POST['cgnosec'] ?? '');
        $original_enlistment = trim($_POST['original_enlistment'] ?? '');
        $date_entered_service = trim($_POST['date_entered_service'] ?? '');
        $comp_ret = trim($_POST['comp_ret'] ?? '');
        $last_promotion_date = trim($_POST['last_promotion_date'] ?? '');
        
        // Officer specific fields
        $cgoc_class = trim($_POST['cgoc_class'] ?? '');
        $cgscc_class = trim($_POST['cgscc_class'] ?? '');
        $cgsc_class = trim($_POST['cgsc_class'] ?? '');
        $cgec_class = trim($_POST['cgec_class'] ?? '');
        $third_level_career = trim($_POST['third_level_career'] ?? '');
        
        // Common fields
        $seminars_workshops = trim($_POST['seminars_workshops'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        
        // Convert empty dates to NULL
        $original_enlistment = $original_enlistment ?: null;
        $date_entered_service = $date_entered_service ?: null;
        $last_promotion_date = $last_promotion_date ?: null;
        
        try {
            $stmt = $conn->prepare("INSERT INTO personnel (
                rank, lastname, firstname, mi, serial_number, unit_code, sub_unit, category,
                cgmc_cgnoc_class, specialization, functional_course, blmc_almc_cgnoac, cgnosec,
                original_enlistment, date_entered_service, comp_ret, last_promotion_date,
                cgoc_class, cgscc_class, cgsc_class, cgec_class, third_level_career,
                seminars_workshops, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssssssssssssssssssssssss", 
                $rank, $lastname, $firstname, $mi, $serial_number, $unit_code, $sub_unit, $category,
                $cgmc_cgnoc_class, $specialization, $functional_course, $blmc_almc_cgnoac, $cgnosec,
                $original_enlistment, $date_entered_service, $comp_ret, $last_promotion_date,
                $cgoc_class, $cgscc_class, $cgsc_class, $cgec_class, $third_level_career,
                $seminars_workshops, $remarks
            );
            
            if ($stmt->execute()) {
                $add_message = "Personnel record added successfully for {$firstname} {$lastname}.";
                $add_success = true;
                
                // Redirect to refresh the page and show the new record
                header("Location: manage_personnel.php?added=1");
                exit();
            } else {
                $add_message = "Error adding personnel record: " . $stmt->error;
                $add_success = false;
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $add_message = "Error: Personnel with serial number {$serial_number} already exists.";
            } else {
                $add_message = "Error adding personnel record: " . $e->getMessage();
            }
            $add_success = false;
        }
    }
}

// Handle Personnel Update
if (isset($_POST['update_personnel'])) {
    $personnel_id = (int)$_POST['personnel_id'];
    $rank = trim($_POST['rank']);
    $unit_code = trim($_POST['unit_code']);
    
    // Regular users can only update rank and unit_code
    if ($is_admin) {
        // Admin can update all fields
        $lastname = trim($_POST['lastname']);
        $firstname = trim($_POST['firstname']);
        $mi = trim($_POST['mi']);
        $serial_number = trim($_POST['serial_number']);
        $sub_unit = trim($_POST['sub_unit']);
        $category = trim($_POST['category']);
        
        // Non-Officer specific fields
        $cgmc_cgnoc_class = trim($_POST['cgmc_cgnoc_class']);
        $specialization = trim($_POST['specialization']);
        $functional_course = trim($_POST['functional_course']);
        $blmc_almc_cgnoac = trim($_POST['blmc_almc_cgnoac']);
        $cgnosec = trim($_POST['cgnosec']);
        $original_enlistment = trim($_POST['original_enlistment']);
        $date_entered_service = trim($_POST['date_entered_service']);
        $comp_ret = trim($_POST['comp_ret']);
        $last_promotion_date = trim($_POST['last_promotion_date']);
        
        // Officer specific fields
        $cgoc_class = trim($_POST['cgoc_class']);
        $cgscc_class = trim($_POST['cgscc_class']);
        $cgsc_class = trim($_POST['cgsc_class']);
        $cgec_class = trim($_POST['cgec_class']);
        $third_level_career = trim($_POST['third_level_career']);
        
        // Common fields
        $seminars_workshops = trim($_POST['seminars_workshops']);
        $remarks = trim($_POST['remarks']);
        
        $stmt = $conn->prepare("UPDATE personnel SET 
            rank=?, lastname=?, firstname=?, mi=?, serial_number=?, unit_code=?, sub_unit=?, category=?,
            cgmc_cgnoc_class=?, specialization=?, functional_course=?, blmc_almc_cgnoac=?, cgnosec=?,
            original_enlistment=?, date_entered_service=?, comp_ret=?, last_promotion_date=?,
            cgoc_class=?, cgscc_class=?, cgsc_class=?, cgec_class=?, third_level_career=?,
            seminars_workshops=?, remarks=?
            WHERE id=?");
        $stmt->bind_param("ssssssssssssssssssssssssi", 
            $rank, $lastname, $firstname, $mi, $serial_number, $unit_code, $sub_unit, $category,
            $cgmc_cgnoc_class, $specialization, $functional_course, $blmc_almc_cgnoac, $cgnosec,
            $original_enlistment, $date_entered_service, $comp_ret, $last_promotion_date,
            $cgoc_class, $cgscc_class, $cgsc_class, $cgec_class, $third_level_career,
            $seminars_workshops, $remarks, $personnel_id);
    } else {
        // Regular user can only update rank and unit_code
        $stmt = $conn->prepare("UPDATE personnel SET rank=?, unit_code=? WHERE id=?");
        $stmt->bind_param("ssi", $rank, $unit_code, $personnel_id);
    }
    
    if ($stmt->execute()) {
        $update_message = "Personnel record updated successfully.";
    } else {
        $update_message = "Error updating personnel record.";
    }
}

// Handle Delete Personnel
if ($is_admin && isset($_POST['delete_personnel'])) {
    $personnel_id = (int)$_POST['personnel_id'];
    $stmt = $conn->prepare("DELETE FROM personnel WHERE id = ?");
    $stmt->bind_param("i", $personnel_id);
    
    if ($stmt->execute()) {
        $delete_message = "Personnel record deleted successfully.";
    } else {
        $delete_message = "Error deleting personnel record.";
    }
}

// Fetch Officers (General Line and Technical)
$general_line_officers = $conn->query("SELECT * FROM personnel WHERE category='General Line Officer' ORDER BY lastname, firstname");
$technical_officers = $conn->query("SELECT * FROM personnel WHERE category='Technical Officer' ORDER BY lastname, firstname");
$officers = $conn->query("SELECT * FROM personnel WHERE category IN ('General Line Officer', 'Technical Officer') ORDER BY lastname, firstname");

// Fetch Non-Officers
$non_officers = $conn->query("SELECT * FROM personnel WHERE category='Non-Officer' ORDER BY lastname, firstname");

// Get counts
$general_line_count = $conn->query("SELECT COUNT(*) as count FROM personnel WHERE category='General Line Officer'")->fetch_assoc()['count'];
$technical_count = $conn->query("SELECT COUNT(*) as count FROM personnel WHERE category='Technical Officer'")->fetch_assoc()['count'];
$officer_count = $general_line_count + $technical_count;
$non_officer_count = $conn->query("SELECT COUNT(*) as count FROM personnel WHERE category='Non-Officer'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive Personnel Management - PCG CG-12</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #002147 0%, #1e3a8a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .user-info {
            background: <?= $is_admin ? '#d4edda' : '#fff3cd' ?>;
            color: <?= $is_admin ? '#155724' : '#856404' ?>;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid <?= $is_admin ? '#28a745' : '#ffc107' ?>;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .section-title {
            background: linear-gradient(135deg, #002147 0%, #1e3a8a 100%);
            color: white;
            padding: 15px 20px;
            font-size: 1.3em;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        
        .table-container {
            border: 1px solid #dee2e6;
            border-radius: 0 0 8px 8px;
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 1200px;
        }
        
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .file-upload {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .file-upload:hover {
            border-color: #007bff;
        }

        /* Search and Upload Section */
        .search-upload-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .search-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #002147;
            box-shadow: 0 0 0 3px rgba(0, 33, 71, 0.1);
        }

        .search-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn:hover {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: translateY(-2px);
        }

        .search-filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-filters select {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            min-width: 150px;
        }

        .upload-container {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .upload-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .upload-box:hover {
            border-color: #002147;
            background: #f8f9fa;
        }

        .file-input-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .file-select-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-select-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-1px);
        }

        .file-name {
            color: #666;
            font-size: 14px;
            flex: 1;
        }

        .upload-btn {
            background: linear-gradient(135deg, #002147 0%, #004080 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
        }

        .upload-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
            transform: translateY(-2px);
        }

        .upload-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .upload-info {
            margin-top: 15px;
            text-align: center;
        }

        .upload-info small {
            color: #666;
        }

        .upload-info a {
            color: #002147;
            text-decoration: none;
            font-weight: 600;
        }

        .upload-info a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Comprehensive Personnel Management</h1>
        <p>PCG CG-12 Training Management System</p>
    </div>
    
    <div class="content">
        <a href="<?= $is_admin ? 'admin-dashboard-enhanced.php' : 'user-dashboard.html' ?>" class="btn back-btn">← Back to Dashboard</a>
        
        <div class="user-info">
            <strong>👤 Logged in as:</strong> <?= htmlspecialchars($_SESSION['username']) ?> 
            <span style="float: right; font-weight: bold;">
                <?= $is_admin ? '🔧 Administrator' : '👁️ Regular User' ?>
            </span>
            <br>
            <small>
                <?= $is_admin ? 'Full access to edit all personnel data' : 'Can only edit Unit Code and Rank. All other fields are view-only.' ?>
            </small>
        </div>

        <!-- Search and Upload Section -->
        <div class="search-upload-section">
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search personnel by name, rank, unit code, or serial number..." onkeyup="filterPersonnel()">
                    <button class="search-btn" onclick="filterPersonnel()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="search-filters">
                    <select id="categoryFilter" onchange="filterPersonnel()">
                        <option value="">All Categories</option>
                        <option value="General Line Officer">General Line Officers</option>
                        <option value="Technical Officer">Technical Officers</option>
                        <option value="Non-Officer">Non-Officers</option>
                    </select>
                    <select id="regionFilter" onchange="filterPersonnel()">
                        <option value="">All Regions</option>
                        <option value="NCR">NCR</option>
                        <option value="Luzon">Luzon</option>
                        <option value="Visayas">Visayas</option>
                        <option value="Mindanao">Mindanao</option>
                    </select>
                </div>
            </div>

            <?php if ($is_admin): ?>
            <div class="upload-container">
                <div class="upload-box">
                    <form method="POST" enctype="multipart/form-data" id="excelUploadForm">
                        <div class="file-input-wrapper">
                            <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv" style="display: none;" onchange="updateFileName()">
                            <button type="button" class="file-select-btn" onclick="document.getElementById('excelFile').click()">
                                <i class="fas fa-file-upload"></i> Choose File
                            </button>
                            <span id="fileName" class="file-name">No file selected</span>
                        </div>
                        <button type="submit" name="upload_excel" class="upload-btn" disabled id="uploadBtn">
                            <i class="fas fa-upload"></i> Upload & Update
                        </button>
                    </form>
                </div>
                <div class="upload-info">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Supported formats: Excel (.xlsx, .xls) or CSV. 
                        <a href="personnel_upload_instructions_enhanced.html" target="_blank">View Instructions</a>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($upload_message)): ?>
            <div class="message <?= $upload_success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($upload_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($update_message)): ?>
            <div class="message success">
                <?= htmlspecialchars($update_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($delete_message)): ?>
            <div class="message success">
                <?= htmlspecialchars($delete_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($add_message)): ?>
            <div class="message <?= $add_success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($add_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
            <div class="message success">
                Personnel record has been successfully added to the database.
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $general_line_count ?></div>
                <div class="stat-label">General Line Officers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $technical_count ?></div>
                <div class="stat-label">Technical Officers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $non_officer_count ?></div>
                <div class="stat-label">Non-Officers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $officer_count + $non_officer_count ?></div>
                <div class="stat-label">Total Personnel</div>
            </div>
        </div>
        
        <?php if ($is_admin): ?>
        <!-- Add Personnel Section -->
        <div style="text-align: center; margin: 30px 0;">
            <button class="btn btn-success" onclick="openAddModal()" style="padding: 12px 24px; font-size: 16px;">
                <i class="fas fa-plus"></i> Add New Personnel
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Officers Section -->
        <h3 class="section-title">👔 Officers (<?= $officer_count ?>) - General Line & Technical</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>MI</th>
                        <th>Serial #</th>
                        <th>Unit Code</th>
                        <th>Sub-Unit</th>
                        <th>CGOC Class</th>
                        <th>CGSCC Class</th>
                        <th>CGSC Class</th>
                        <th>CGEC Class</th>
                        <th>3rd Level Career</th>
                        <th>Seminars/Workshops</th>
                        <th>File</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($officers->num_rows > 0): ?>
                        <?php while ($row = $officers->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['rank']) ?></strong></td>
                            <td><?= htmlspecialchars($row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['firstname']) ?></td>
                            <td><?= htmlspecialchars($row['mi'] ?? '') ?></td>
                            <td><code><?= htmlspecialchars($row['serial_number'] ?? '') ?></code></td>
                            <td><?= htmlspecialchars($row['unit_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['sub_unit'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgoc_class'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgscc_class'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgsc_class'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgec_class'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['third_level_career'] ?? '') ?></td>
                            <td><?= htmlspecialchars(substr($row['seminars_workshops'] ?? '', 0, 50)) ?>...</td>
                            <td>
                                <?php if (!empty($row['upload_file'])): ?>
                                    <a href="<?= htmlspecialchars($row['upload_file']) ?>" target="_blank" class="btn btn-success">📄</a>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($row['remarks'] ?? '', 0, 30)) ?>...</td>
                            <td>
                                <button onclick="editPersonnel(<?= $row['id'] ?>)" class="btn btn-primary">Edit</button>
                                <?php if ($is_admin): ?>
                                    <button onclick="uploadFile(<?= $row['id'] ?>)" class="btn btn-warning">📁</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this personnel record?')">
                                        <input type="hidden" name="personnel_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete_personnel" class="btn btn-danger">Del</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="16" style="text-align: center; padding: 40px; color: #6c757d; font-style: italic;">
                                No officers found in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Non-Officers Section -->
        <h3 class="section-title">👷 Non-Officers (<?= $non_officer_count ?>)</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>MI</th>
                        <th>Serial #</th>
                        <th>Unit Code</th>
                        <th>Sub-Unit</th>
                        <th>CGMC/CGNOC</th>
                        <th>Specialization</th>
                        <th>Functional</th>
                        <th>BLMC/ALMC/CGNOAC</th>
                        <th>CGNOSEC</th>
                        <th>Enlistment</th>
                        <th>Service Date</th>
                        <th>Last Promotion</th>
                        <th>Seminars/Workshops</th>
                        <th>File</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($non_officers->num_rows > 0): ?>
                        <?php while ($row = $non_officers->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['rank']) ?></strong></td>
                            <td><?= htmlspecialchars($row['lastname']) ?></td>
                            <td><?= htmlspecialchars($row['firstname']) ?></td>
                            <td><?= htmlspecialchars($row['mi'] ?? '') ?></td>
                            <td><code><?= htmlspecialchars($row['serial_number'] ?? '') ?></code></td>
                            <td><?= htmlspecialchars($row['unit_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['sub_unit'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgmc_cgnoc_class'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['specialization'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['functional_course'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['blmc_almc_cgnoac'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['cgnosec'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['original_enlistment'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['date_entered_service'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['last_promotion_date'] ?? '') ?></td>
                            <td><?= htmlspecialchars(substr($row['seminars_workshops'] ?? '', 0, 50)) ?>...</td>
                            <td>
                                <?php if (!empty($row['upload_file'])): ?>
                                    <a href="<?= htmlspecialchars($row['upload_file']) ?>" target="_blank" class="btn btn-success">📄</a>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(substr($row['remarks'] ?? '', 0, 30)) ?>...</td>
                            <td>
                                <button onclick="editPersonnel(<?= $row['id'] ?>)" class="btn btn-primary">Edit</button>
                                <?php if ($is_admin): ?>
                                    <button onclick="uploadFile(<?= $row['id'] ?>)" class="btn btn-warning">📁</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this personnel record?')">
                                        <input type="hidden" name="personnel_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete_personnel" class="btn btn-danger">Del</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="19" style="text-align: center; padding: 40px; color: #6c757d; font-style: italic;">
                                No non-officers found in the database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Personnel Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Personnel Record</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="personnel_id" id="edit_personnel_id">
            <input type="hidden" name="update_personnel" value="1">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_rank">Rank *</label>
                    <input type="text" id="edit_rank" name="rank" required>
                </div>
                <div class="form-group">
                    <label for="edit_unit_code">Unit Code *</label>
                    <input type="text" id="edit_unit_code" name="unit_code" required>
                </div>
            </div>
            
            <?php if ($is_admin): ?>
            <!-- Admin-only fields -->
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_lastname">Last Name *</label>
                    <input type="text" id="edit_lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="edit_firstname">First Name *</label>
                    <input type="text" id="edit_firstname" name="firstname" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_mi">Middle Initial</label>
                    <input type="text" id="edit_mi" name="mi" maxlength="10">
                </div>
                <div class="form-group">
                    <label for="edit_serial_number">Serial Number *</label>
                    <input type="text" id="edit_serial_number" name="serial_number" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="edit_sub_unit">Sub-Unit</label>
                    <input type="text" id="edit_sub_unit" name="sub_unit">
                </div>
                <div class="form-group">
                    <label for="edit_category">Category *</label>
                    <select id="edit_category" name="category" required>
                        <option value="General Line Officer">General Line Officer</option>
                        <option value="Technical Officer">Technical Officer</option>
                        <option value="Non-Officer">Non-Officer</option>
                    </select>
                </div>
            </div>
            
            <!-- Non-Officer specific fields -->
            <div id="non_officer_fields" style="display: none;">
                <h4>Non-Officer Specific Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cgmc_cgnoc_class">CGMC/CGNOC Class</label>
                        <input type="text" id="edit_cgmc_cgnoc_class" name="cgmc_cgnoc_class">
                    </div>
                    <div class="form-group">
                        <label for="edit_specialization">Specialization</label>
                        <input type="text" id="edit_specialization" name="specialization">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_functional_course">Functional Course</label>
                        <input type="text" id="edit_functional_course" name="functional_course">
                    </div>
                    <div class="form-group">
                        <label for="edit_blmc_almc_cgnoac">BLMC/ALMC/CGNOAC</label>
                        <input type="text" id="edit_blmc_almc_cgnoac" name="blmc_almc_cgnoac">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cgnosec">CGNOSEC</label>
                        <input type="text" id="edit_cgnosec" name="cgnosec">
                    </div>
                    <div class="form-group">
                        <label for="edit_original_enlistment">Original Enlistment</label>
                        <input type="date" id="edit_original_enlistment" name="original_enlistment">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_date_entered_service">Date Entered Service</label>
                        <input type="date" id="edit_date_entered_service" name="date_entered_service">
                    </div>
                    <div class="form-group">
                        <label for="edit_comp_ret">CompRet</label>
                        <input type="text" id="edit_comp_ret" name="comp_ret">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_last_promotion_date">Last Promotion Date</label>
                    <input type="date" id="edit_last_promotion_date" name="last_promotion_date">
                </div>
            </div>
            
            <!-- Officer specific fields -->
            <div id="officer_fields" style="display: none;">
                <h4>Officer Specific Information</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cgoc_class">CGOC Class</label>
                        <input type="text" id="edit_cgoc_class" name="cgoc_class">
                    </div>
                    <div class="form-group">
                        <label for="edit_cgscc_class">CGSCC Class</label>
                        <input type="text" id="edit_cgscc_class" name="cgscc_class">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_cgsc_class">CGSC Class</label>
                        <input type="text" id="edit_cgsc_class" name="cgsc_class">
                    </div>
                    <div class="form-group">
                        <label for="edit_cgec_class">CGEC Class</label>
                        <input type="text" id="edit_cgec_class" name="cgec_class">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_third_level_career">Third Level Career Course</label>
                    <input type="text" id="edit_third_level_career" name="third_level_career">
                </div>
            </div>
            
            <!-- Common fields -->
            <div class="form-group">
                <label for="edit_seminars_workshops">Seminars/Workshops Attended</label>
                <textarea id="edit_seminars_workshops" name="seminars_workshops" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_remarks">Remarks</label>
                <textarea id="edit_remarks" name="remarks" rows="3"></textarea>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-success">Update Record</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- File Upload Modal -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeUploadModal()">&times;</span>
        <h2>Upload Personnel File</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="personnel_id" id="upload_personnel_id">
            <input type="hidden" name="upload_file" value="1">
            
            <div class="file-upload">
                <input type="file" name="personnel_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                <p>Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG</p>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-success">Upload File</button>
                <button type="button" onclick="closeUploadModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Personnel Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <h2>Add New Personnel</h2>
        
        <form method="POST" style="max-width: 100%;">
            <input type="hidden" name="add_personnel" value="1">
            
            <!-- Basic Information -->
            <h4 style="margin: 20px 0 10px 0; color: #002147;">Basic Information</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="add_rank">Rank *</label>
                    <select id="add_rank" name="rank" required>
                        <option value="">Select Rank</option>
                        <option value="Commander">Commander</option>
                        <option value="Lieutenant">Lieutenant</option>
                        <option value="Lieutenant Junior Grade">Lieutenant Junior Grade</option>
                        <option value="Chief Petty Officer">Chief Petty Officer</option>
                        <option value="Petty Officer 1">Petty Officer 1</option>
                        <option value="Petty Officer 2">Petty Officer 2</option>
                        <option value="Petty Officer 3">Petty Officer 3</option>
                        <option value="Seaman">Seaman</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_category">Category *</label>
                    <select id="add_category" name="category" required onchange="toggleAddFields()">
                        <option value="">Select Category</option>
                        <option value="Officer">Officer</option>
                        <option value="Non-Officer">Non-Officer</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="add_lastname">Last Name *</label>
                    <input type="text" id="add_lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="add_firstname">First Name *</label>
                    <input type="text" id="add_firstname" name="firstname" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="add_mi">Middle Initial</label>
                    <input type="text" id="add_mi" name="mi" maxlength="10">
                </div>
                <div class="form-group">
                    <label for="add_serial_number">Serial Number *</label>
                    <input type="text" id="add_serial_number" name="serial_number" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="add_unit_code">Unit Code *</label>
                    <select id="add_unit_code" name="unit_code" required>
                        <option value="">Select Unit</option>
                        <option value="CG-HQ">CG Headquarters</option>
                        <option value="CG-NCR">CG District NCR</option>
                        <option value="CG-DV">CG District Visayas</option>
                        <option value="CG-DM">CG District Mindanao</option>
                        <option value="CG-SD">CG Station Davao</option>
                        <option value="CG-SB">CG Station Batangas</option>
                        <option value="CG-SC">CG Station Cebu</option>
                        <option value="CG-SM">CG Station Manila</option>
                        <option value="CG-SP">CG Station Palawan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_sub_unit">Sub-Unit</label>
                    <input type="text" id="add_sub_unit" name="sub_unit">
                </div>
            </div>

            <!-- Officer Fields -->
            <div id="add_officer_fields" style="display: none;">
                <h4 style="margin: 20px 0 10px 0; color: #002147;">Officer Training & Career</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_cgoc_class">CGOC Class</label>
                        <input type="text" id="add_cgoc_class" name="cgoc_class">
                    </div>
                    <div class="form-group">
                        <label for="add_cgscc_class">CGSCC Class</label>
                        <input type="text" id="add_cgscc_class" name="cgscc_class">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_cgsc_class">CGSC Class</label>
                        <input type="text" id="add_cgsc_class" name="cgsc_class">
                    </div>
                    <div class="form-group">
                        <label for="add_cgec_class">CGEC Class</label>
                        <input type="text" id="add_cgec_class" name="cgec_class">
                    </div>
                </div>
                <div class="form-group">
                    <label for="add_third_level_career">Third Level Career Course</label>
                    <input type="text" id="add_third_level_career" name="third_level_career">
                </div>
            </div>

            <!-- Non-Officer Fields -->
            <div id="add_non_officer_fields" style="display: none;">
                <h4 style="margin: 20px 0 10px 0; color: #002147;">Non-Officer Training & Career</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_cgmc_cgnoc_class">CGMC/CGNOC Class</label>
                        <input type="text" id="add_cgmc_cgnoc_class" name="cgmc_cgnoc_class">
                    </div>
                    <div class="form-group">
                        <label for="add_specialization">Specialization</label>
                        <input type="text" id="add_specialization" name="specialization">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_functional_course">Functional Course</label>
                        <input type="text" id="add_functional_course" name="functional_course">
                    </div>
                    <div class="form-group">
                        <label for="add_blmc_almc_cgnoac">BLMC/ALMC/CGNOAC</label>
                        <input type="text" id="add_blmc_almc_cgnoac" name="blmc_almc_cgnoac">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_cgnosec">CGNOSEC</label>
                        <input type="text" id="add_cgnosec" name="cgnosec">
                    </div>
                    <div class="form-group">
                        <label for="add_comp_ret">CompRet</label>
                        <input type="text" id="add_comp_ret" name="comp_ret">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_original_enlistment">Original Enlistment</label>
                        <input type="date" id="add_original_enlistment" name="original_enlistment">
                    </div>
                    <div class="form-group">
                        <label for="add_date_entered_service">Date Entered Service</label>
                        <input type="date" id="add_date_entered_service" name="date_entered_service">
                    </div>
                </div>
                <div class="form-group">
                    <label for="add_last_promotion_date">Last Promotion Date</label>
                    <input type="date" id="add_last_promotion_date" name="last_promotion_date">
                </div>
            </div>

            <!-- Common Fields -->
            <h4 style="margin: 20px 0 10px 0; color: #002147;">Additional Information</h4>
            <div class="form-group">
                <label for="add_seminars_workshops">Seminars/Workshops Attended</label>
                <textarea id="add_seminars_workshops" name="seminars_workshops" rows="3" placeholder="List seminars and workshops attended..."></textarea>
            </div>
            <div class="form-group">
                <label for="add_remarks">Remarks</label>
                <textarea id="add_remarks" name="remarks" rows="3" placeholder="Additional notes or remarks..."></textarea>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn btn-success">Add Personnel</button>
                <button type="button" onclick="closeAddModal()" class="btn btn-danger">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPersonnel(id) {
    // Fetch personnel data via AJAX and populate the form
    fetch('get_personnel_data.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_personnel_id').value = data.id;
            document.getElementById('edit_rank').value = data.rank;
            document.getElementById('edit_unit_code').value = data.unit_code;
            
            <?php if ($is_admin): ?>
            document.getElementById('edit_lastname').value = data.lastname || '';
            document.getElementById('edit_firstname').value = data.firstname || '';
            document.getElementById('edit_mi').value = data.mi || '';
            document.getElementById('edit_serial_number').value = data.serial_number || '';
            document.getElementById('edit_sub_unit').value = data.sub_unit || '';
            document.getElementById('edit_category').value = data.category || '';
            
            // Show/hide category-specific fields
            if (data.category === 'Non-Officer') {
                document.getElementById('non_officer_fields').style.display = 'block';
                document.getElementById('officer_fields').style.display = 'none';
            } else if (data.category === 'General Line Officer' || data.category === 'Technical Officer') {
                document.getElementById('officer_fields').style.display = 'block';
                document.getElementById('non_officer_fields').style.display = 'none';
                
                document.getElementById('edit_cgmc_cgnoc_class').value = data.cgmc_cgnoc_class || '';
                document.getElementById('edit_specialization').value = data.specialization || '';
                document.getElementById('edit_functional_course').value = data.functional_course || '';
                document.getElementById('edit_blmc_almc_cgnoac').value = data.blmc_almc_cgnoac || '';
                document.getElementById('edit_cgnosec').value = data.cgnosec || '';
                document.getElementById('edit_original_enlistment').value = data.original_enlistment || '';
                document.getElementById('edit_date_entered_service').value = data.date_entered_service || '';
                document.getElementById('edit_comp_ret').value = data.comp_ret || '';
                document.getElementById('edit_last_promotion_date').value = data.last_promotion_date || '';
            } else {
                document.getElementById('officer_fields').style.display = 'block';
                document.getElementById('non_officer_fields').style.display = 'none';
                
                document.getElementById('edit_cgoc_class').value = data.cgoc_class || '';
                document.getElementById('edit_cgscc_class').value = data.cgscc_class || '';
                document.getElementById('edit_cgsc_class').value = data.cgsc_class || '';
                document.getElementById('edit_cgec_class').value = data.cgec_class || '';
                document.getElementById('edit_third_level_career').value = data.third_level_career || '';
            }
            
            document.getElementById('edit_seminars_workshops').value = data.seminars_workshops || '';
            document.getElementById('edit_remarks').value = data.remarks || '';
            <?php endif; ?>
            
            document.getElementById('editModal').style.display = 'block';
        });
}

function uploadFile(id) {
    document.getElementById('upload_personnel_id').value = id;
    document.getElementById('uploadModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

// Category change handler for admin
<?php if ($is_admin): ?>
document.getElementById('edit_category').addEventListener('change', function() {
    if (this.value === 'Non-Officer') {
        document.getElementById('non_officer_fields').style.display = 'block';
        document.getElementById('officer_fields').style.display = 'none';
    } else if (this.value === 'General Line Officer' || this.value === 'Technical Officer') {
        document.getElementById('officer_fields').style.display = 'block';
        document.getElementById('non_officer_fields').style.display = 'none';
    }
});
<?php endif; ?>

// Search and Filter Functions
function filterPersonnel() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const regionFilter = document.getElementById('regionFilter').value;
    
    const officerRows = document.querySelectorAll('table:first-of-type tbody tr');
    const nonOfficerRows = document.querySelectorAll('table:last-of-type tbody tr');
    
    // Filter Officers
    officerRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const category = row.querySelector('td:nth-child(8)')?.textContent || 'General Line Officer'; // Get category from table
        const unitCode = row.querySelector('td:nth-child(6)')?.textContent || '';
        
        const matchesSearch = text.includes(searchTerm);
        const matchesCategory = !categoryFilter || category.includes(categoryFilter);
        const matchesRegion = !regionFilter || getRegionFromUnitCode(unitCode) === regionFilter;
        
        row.style.display = (matchesSearch && matchesCategory && matchesRegion) ? '' : 'none';
    });
    
    // Filter Non-Officers
    nonOfficerRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const category = 'Non-Officer'; // Non-Officers table
        const unitCode = row.querySelector('td:nth-child(6)')?.textContent || '';
        
        const matchesSearch = text.includes(searchTerm);
        const matchesCategory = !categoryFilter || category.includes(categoryFilter);
        const matchesRegion = !regionFilter || getRegionFromUnitCode(unitCode) === regionFilter;
        
        row.style.display = (matchesSearch && matchesCategory && matchesRegion) ? '' : 'none';
    });
    
    updateTableCounts();
}

function getRegionFromUnitCode(unitCode) {
    if (unitCode.includes('HQ') || unitCode.includes('NCR')) return 'NCR';
    if (unitCode.includes('CL') || unitCode.includes('CV')) return 'Luzon';
    if (unitCode.includes('CE') || unitCode.includes('WV')) return 'Visayas';
    if (unitCode.includes('DV') || unitCode.includes('DM') || unitCode.includes('DS')) return 'Mindanao';
    return '';
}

function updateTableCounts() {
    const visibleOfficers = document.querySelectorAll('table:first-of-type tbody tr:not([style*="display: none"])').length;
    const visibleNonOfficers = document.querySelectorAll('table:last-of-type tbody tr:not([style*="display: none"])').length;
    
    // Update section titles with counts
    const officerTitle = document.querySelector('.section-title:contains("Officers")');
    const nonOfficerTitle = document.querySelector('.section-title:contains("Non-Officers")');
    
    if (officerTitle) {
        officerTitle.textContent = `👔 Officers (${visibleOfficers})`;
    }
    if (nonOfficerTitle) {
        nonOfficerTitle.textContent = `👷 Non-Officers (${visibleNonOfficers})`;
    }
}

// File Upload Functions
function updateFileName() {
    const fileInput = document.getElementById('excelFile');
    const fileName = document.getElementById('fileName');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileName.textContent = file.name;
        uploadBtn.disabled = false;
        
        // Validate file type
        const allowedTypes = ['.xlsx', '.xls', '.csv'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
            alert('Please select a valid file type: Excel (.xlsx, .xls) or CSV (.csv)');
            fileInput.value = '';
            fileName.textContent = 'No file selected';
            uploadBtn.disabled = true;
        }
    } else {
        fileName.textContent = 'No file selected';
        uploadBtn.disabled = true;
    }
}

// Clear search and filters
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('regionFilter').value = '';
    filterPersonnel();
}

// Add Personnel Modal Functions
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    // Reset form
    document.querySelector('#addModal form').reset();
    document.getElementById('add_officer_fields').style.display = 'none';
    document.getElementById('add_non_officer_fields').style.display = 'none';
}

function toggleAddFields() {
    const category = document.getElementById('add_category').value;
    const officerFields = document.getElementById('add_officer_fields');
    const nonOfficerFields = document.getElementById('add_non_officer_fields');
    
    if (category === 'Officer') {
        officerFields.style.display = 'block';
        nonOfficerFields.style.display = 'none';
    } else if (category === 'Non-Officer') {
        nonOfficerFields.style.display = 'block';
        officerFields.style.display = 'none';
    } else {
        officerFields.style.display = 'none';
        nonOfficerFields.style.display = 'none';
    }
}
</script>

</body>
</html>
