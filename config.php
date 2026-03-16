<?php
/**
 * Configuration file for base URLs
 * This file automatically detects the environment and sets appropriate URLs
 */

// Detect if running on localhost or production server
$isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);

// Get the protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Optional manual URL overrides for containerized deployments
$envBaseUrl = getenv('BASE_URL');
$envAdminBaseUrl = getenv('ADMIN_BASE_URL');

if (!empty($envBaseUrl) && !empty($envAdminBaseUrl)) {
    $Base_Url = rtrim($envBaseUrl, '/');
    $Admin_Base_Url = rtrim($envAdminBaseUrl, '/');
} elseif ($isLocalhost) {
    // Local development environment
    $Base_Url = $protocol . $_SERVER['SERVER_NAME'] . '/E-commerce-Project-FullStack';
    $Admin_Base_Url = $protocol . $_SERVER['SERVER_NAME'] . '/E-commerce-Project-FullStack/admin';
} else {
    // Production / Docker environment
    // Construct base URL including port when non-standard (e.g. Docker on :8080)
    $port = $_SERVER['SERVER_PORT'];
    $includePort = ($protocol === 'https://' && $port != 443) || ($protocol === 'http://' && $port != 80);
    $hostWithPort = $_SERVER['SERVER_NAME'] . ($includePort ? ':' . $port : '');
    $Base_Url = $protocol . $hostWithPort;
    $Admin_Base_Url = $protocol . $hostWithPort . '/admin';
    
    // Option 3: Custom domain/path - Manually specify your production URLs (DISABLED)
    // Uncomment and modify these lines if you need specific URLs
    // $Base_Url = 'https://your-domain.com';
    // $Admin_Base_Url = 'https://your-domain.com/admin';
}

// Database configuration
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
$dbname = getenv('DB_NAME') ?: "kunj_ecommerce";

// For production, use different database credentials
// IMPORTANT: Update these with your actual production database credentials!
if (!$isLocalhost && getenv('DB_HOST') === false) {
    // TODO: Replace these with your actual production database credentials
    $servername = "localhost"; // Usually 'localhost' but check with your hosting provider
    $username = "kunj"; // Your hosting database username
    $password = "Kunj@localhost#@!"; // Your hosting database password
    $dbname = "kunj_ecommerce"; // Your hosting database name
}

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>