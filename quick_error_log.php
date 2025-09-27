<?php
/**
 * Quick Error Log Viewer
 */

echo "<h1>🚨 Recent Error Logs</h1>";
echo "<p><em>Shows last 20 lines of PHP error log</em></p>";

// Try to find the error log file
$possibleLogs = [
    ini_get('error_log'),
    'C:\\xampp\\php\\logs\\php_error_log',
    'C:\\xampp\\apache\\logs\\error.log',
    __DIR__ . '/error.log'
];

$errorLogFile = null;
foreach ($possibleLogs as $logFile) {
    if (!empty($logFile) && file_exists($logFile)) {
        $errorLogFile = $logFile;
        break;
    }
}

echo "<p><strong>Log file:</strong> " . ($errorLogFile ?: 'Not found') . "</p>";

if ($errorLogFile && file_exists($errorLogFile)) {
    $lines = file($errorLogFile);
    $lastLines = array_slice($lines, -20);
    
    echo "<div style='background: #f4f4f4; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; white-space: pre-wrap;'>";
    
    foreach ($lastLines as $line) {
        $line = htmlspecialchars($line);
        
        // Highlight OTP-related errors
        if (stripos($line, 'otp') !== false || stripos($line, 'verification') !== false) {
            echo "<div style='background: #ffeb3b; padding: 2px;'>$line</div>";
        } else {
            echo $line;
        }
    }
    echo "</div>";
    
    echo "<br><button onclick='window.location.reload()'>🔄 Refresh Logs</button>";
} else {
    echo "<p style='color: orange;'>Error log file not found. Errors may be logged elsewhere or logging may be disabled.</p>";
}

// Also check if there are any recent OTP records that might give us clues
require_once __DIR__ . '/includes/db.php';

echo "<h3>Recent OTP Activity</h3>";
$recentOTPs = $conn->query("SELECT email, otp_code, attempts, created_at, is_verified FROM otp_verifications ORDER BY created_at DESC LIMIT 5");

if ($recentOTPs && $recentOTPs->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Email</th><th>OTP</th><th>Attempts</th><th>Created</th><th>Verified</th></tr>";
    while ($otp = $recentOTPs->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$otp['email']}</td>";
        echo "<td>{$otp['otp_code']}</td>";
        echo "<td>{$otp['attempts']}</td>";
        echo "<td>{$otp['created_at']}</td>";
        echo "<td>" . ($otp['is_verified'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>