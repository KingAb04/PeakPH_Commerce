<?php
$host = "localhost";   // or 127.0.0.1
$user = "root";        // default XAMPP user
$pass = "";            // leave empty for XAMPP
$dbname = "peakph_db";

$db_connection_error = false;
$conn = null;

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for better character support
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $db_connection_error = true;
    $conn = null;
}

// Helper function to check if database is available
function isDatabaseConnected() {
    global $conn, $db_connection_error;
    return !$db_connection_error && $conn !== null;
}

// Helper function to execute safe queries with error handling
function executeQuery($query, $params = [], $types = null) {
    global $conn, $db_connection_error;
    
    if ($db_connection_error || $conn === null) {
        return false;
    }
    
    try {
        if (empty($params)) {
            $result = $conn->query($query);
        } else {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Use provided types or default to strings
            if ($types === null) {
                $types = str_repeat("s", count($params));
            }
            
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // For INSERT, UPDATE, DELETE queries, we don't need get_result()
            if (stripos($query, 'INSERT') === 0 || stripos($query, 'UPDATE') === 0 || stripos($query, 'DELETE') === 0) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                return $affected_rows > 0;
            } else {
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}
?>
