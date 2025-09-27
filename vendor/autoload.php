<?php
/**
 * Autoloader for PHPMailer
 * Simple autoloader to load PHPMailer classes
 */

// Base path for PHPMailer
define('PHPMAILER_BASE_PATH', __DIR__ . '/phpmailer/phpmailer/src/');

// Autoload function
function phpmailer_autoload($className) {
    if (strpos($className, 'PHPMailer\\PHPMailer\\') === 0) {
        $className = str_replace('PHPMailer\\PHPMailer\\', '', $className);
        $file = PHPMAILER_BASE_PATH . $className . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
}

// Register the autoloader
spl_autoload_register('phpmailer_autoload');

// Alternative: Include the files directly
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    require_once PHPMAILER_BASE_PATH . 'PHPMailer.php';
    require_once PHPMAILER_BASE_PATH . 'SMTP.php';
    require_once PHPMAILER_BASE_PATH . 'Exception.php';
}
?>