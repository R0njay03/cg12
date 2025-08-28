<?php
/**
 * Validation Helper Functions for Personnel Management System
 * Philippine Coast Guard CG-12
 */

class PersonnelValidator {
    
    // Valid rank options
    private static $valid_ranks = [
        'Commander',
        'Lieutenant',
        'Lieutenant Junior Grade',
        'Chief Petty Officer',
        'Petty Officer 1',
        'Petty Officer 2',
        'Petty Officer 3',
        'Seaman'
    ];
    
    // Valid unit codes
    private static $valid_units = [
        'CG-HQ',    // CG Headquarters
        'CG-NCR',   // CG District NCR
        'CG-DV',    // CG District Visayas
        'CG-DM',    // CG District Mindanao
        'CG-SD',    // CG Station Davao
        'CG-SB',    // CG Station Batangas
        'CG-SC',    // CG Station Cebu
        'CG-SM',    // CG Station Manila
        'CG-SP'     // CG Station Palawan
    ];
    
    // Valid categories
    private static $valid_categories = ['Officer', 'Non-Officer'];
    
    /**
     * Validate personnel data
     */
    public static function validatePersonnelData($data) {
        $errors = [];
        
        // Required field validation
        $required_fields = ['rank', 'lastname', 'firstname', 'serial_number', 'unit_code', 'category'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        // Rank validation
        if (!empty($data['rank']) && !in_array($data['rank'], self::$valid_ranks)) {
            $errors[] = "Invalid rank: {$data['rank']}";
        }
        
        // Unit code validation
        if (!empty($data['unit_code']) && !in_array($data['unit_code'], self::$valid_units)) {
            $errors[] = "Invalid unit code: {$data['unit_code']}";
        }
        
        // Category validation
        if (!empty($data['category']) && !in_array($data['category'], self::$valid_categories)) {
            $errors[] = "Invalid category: {$data['category']}. Must be 'Officer' or 'Non-Officer'";
        }
        
        // Serial number format validation
        if (!empty($data['serial_number'])) {
            if (!preg_match('/^CG-\d{4}-\d{3}$/', $data['serial_number'])) {
                $errors[] = "Invalid serial number format. Expected format: CG-YYYY-###";
            }
        }
        
        // Name validation (no special characters)
        if (!empty($data['lastname']) && !preg_match('/^[a-zA-Z\s\-\.\']+$/', $data['lastname'])) {
            $errors[] = "Last name contains invalid characters";
        }
        
        if (!empty($data['firstname']) && !preg_match('/^[a-zA-Z\s\-\.\']+$/', $data['firstname'])) {
            $errors[] = "First name contains invalid characters";
        }
        
        // Date validation
        $date_fields = ['original_enlistment', 'date_entered_service', 'last_promotion_date'];
        foreach ($date_fields as $field) {
            if (!empty($data[$field]) && !self::validateDate($data[$field])) {
                $errors[] = "Invalid date format for {$field}. Expected: YYYY-MM-DD";
            }
        }
        
        // Middle initial validation
        if (!empty($data['mi']) && strlen($data['mi']) > 10) {
            $errors[] = "Middle initial is too long (maximum 10 characters)";
        }
        
        return $errors;
    }
    
    /**
     * Validate date format
     */
    public static function validateDate($date) {
        if (empty($date)) return true;
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizePersonnelData($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Trim whitespace
                $value = trim($value);
                
                // Convert empty strings to null for optional fields
                if ($value === '') {
                    $value = null;
                }
                
                // Special handling for specific fields
                switch ($key) {
                    case 'rank':
                    case 'unit_code':
                    case 'category':
                        // These should match exact values
                        break;
                    case 'lastname':
                    case 'firstname':
                        // Names should be title case
                        $value = ucwords(strtolower($value));
                        break;
                    case 'serial_number':
                        // Convert to uppercase
                        $value = strtoupper($value);
                        break;
                    case 'mi':
                        // Middle initial should be uppercase
                        $value = strtoupper($value);
                        break;
                    default:
                        // For other text fields, just clean up
                        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        break;
                }
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate CSV file structure
     */
    public static function validateCSVFile($file_path) {
        $errors = [];
        
        if (!file_exists($file_path)) {
            return ['File does not exist'];
        }
        
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return ['Cannot read file'];
        }
        
        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['File appears to be empty'];
        }
        
        // Expected headers (minimum required)
        $required_headers = ['RANK', 'LASTNAME', 'FIRSTNAME', 'SERIAL_NUMBER', 'UNIT_CODE', 'CATEGORY'];
        
        foreach ($required_headers as $req_header) {
            if (!in_array($req_header, $header)) {
                $errors[] = "Missing required column: {$req_header}";
            }
        }
        
        // Check for data rows
        $row_count = 0;
        while (($row = fgetcsv($handle)) !== FALSE) {
            $row_count++;
            if ($row_count > 1000) {
                $errors[] = "File contains too many rows (maximum 1000 per upload)";
                break;
            }
        }
        
        if ($row_count === 0) {
            $errors[] = "File contains no data rows";
        }
        
        fclose($handle);
        return $errors;
    }
    
    /**
     * Generate validation report for bulk upload
     */
    public static function generateUploadReport($successful_records, $failed_records, $errors) {
        $report = [
            'total_processed' => $successful_records + $failed_records,
            'successful' => $successful_records,
            'failed' => $failed_records,
            'success_rate' => $successful_records + $failed_records > 0 ? 
                round(($successful_records / ($successful_records + $failed_records)) * 100, 2) : 0,
            'errors' => $errors
        ];
        
        return $report;
    }
    
    /**
     * Get validation rules for frontend
     */
    public static function getValidationRules() {
        return [
            'ranks' => self::$valid_ranks,
            'units' => self::$valid_units,
            'categories' => self::$valid_categories,
            'serial_format' => 'CG-YYYY-###',
            'date_format' => 'YYYY-MM-DD',
            'required_fields' => ['rank', 'lastname', 'firstname', 'serial_number', 'unit_code', 'category']
        ];
    }
    
    /**
     * Log validation errors
     */
    public static function logValidationError($error, $data = null) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error,
            'data' => $data,
            'user' => $_SESSION['username'] ?? 'unknown'
        ];
        
        error_log("VALIDATION_ERROR: " . json_encode($log_entry));
    }
}

/**
 * Security Helper Functions
 */
class SecurityHelper {
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Rate limiting for uploads
     */
    public static function checkUploadRateLimit($user_id, $limit_per_hour = 10) {
        $cache_key = "upload_rate_limit_{$user_id}";
        $uploads = $_SESSION[$cache_key] ?? [];
        
        // Remove uploads older than 1 hour
        $one_hour_ago = time() - 3600;
        $uploads = array_filter($uploads, function($timestamp) use ($one_hour_ago) {
            return $timestamp > $one_hour_ago;
        });
        
        if (count($uploads) >= $limit_per_hour) {
            return false;
        }
        
        // Add current upload
        $uploads[] = time();
        $_SESSION[$cache_key] = $uploads;
        
        return true;
    }
    
    /**
     * Validate file upload security
     */
    public static function validateFileUpload($file) {
        $errors = [];
        
        // Check file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = "File was not properly uploaded";
            return $errors;
        }
        
        // Check file size (50MB limit)
        $max_size = 50 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $errors[] = "File size exceeds 50MB limit";
        }
        
        // Check file extension
        $allowed_extensions = ['csv', 'xls', 'xlsx'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Invalid file type. Only CSV, XLS, and XLSX files are allowed";
        }
        
        // Check MIME type
        $allowed_mimes = [
            'text/csv',
            'application/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = "Invalid file format detected";
        }
        
        return $errors;
    }
}

/**
 * Usage Examples:
 * 
 * // Validate personnel data before insertion
 * $errors = PersonnelValidator::validatePersonnelData($_POST);
 * if (!empty($errors)) {
 *     // Handle validation errors
 * }
 * 
 * // Sanitize data before database operations
 * $clean_data = PersonnelValidator::sanitizePersonnelData($_POST);
 * 
 * // Validate CSV file before processing
 * $csv_errors = PersonnelValidator::validateCSVFile($file_path);
 * if (!empty($csv_errors)) {
 *     // Handle CSV validation errors
 * }
 * 
 * // Check upload rate limit
 * if (!SecurityHelper::checkUploadRateLimit($_SESSION['user_id'])) {
 *     // Rate limit exceeded
 * }
 */
?>