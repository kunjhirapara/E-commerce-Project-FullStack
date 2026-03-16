<?php
/**
 * Configuration file for base URLs
 * This file automatically detects the environment and sets appropriate URLs
 */

// Detect if running on localhost or production server
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocalhost = in_array($serverName, ['localhost', '127.0.0.1', '::1']);

// Get the protocol (http or https) — also check X-Forwarded-Proto for reverse proxy setups
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'))
    ? "https://" : "http://";

// Build host (include non-standard ports only)
$port = $_SERVER['SERVER_PORT'] ?? null;
$includePort = $port !== null && (($protocol === 'https://' && (int) $port !== 443) || ($protocol === 'http://' && (int) $port !== 80));
$hostWithPort = $serverName . ($includePort ? ':' . $port : '');

// Determine project web path (e.g. /projects/Ecommerce) using DOCUMENT_ROOT + __DIR__.
// Fallback to SCRIPT_NAME when document root mapping is unavailable.
$projectPath = '';
$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
if (!empty($documentRoot)) {
    $docRootReal = realpath($documentRoot);
    $projectReal = realpath(__DIR__);

    if ($docRootReal !== false && $projectReal !== false) {
        $docRootNorm = str_replace('\\', '/', $docRootReal);
        $projectNorm = str_replace('\\', '/', $projectReal);

        if (strpos($projectNorm, $docRootNorm) === 0) {
            $relative = substr($projectNorm, strlen($docRootNorm));
            $projectPath = '/' . ltrim(trim(str_replace('\\', '/', $relative)), '/');
            if ($projectPath === '/') {
                $projectPath = '';
            }
        }
    }
}

if ($projectPath === '' && isset($_SERVER['SCRIPT_NAME'])) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptDir = rtrim($scriptDir, '/');
    if ($scriptDir === '.') {
        $scriptDir = '';
    }

    // If running from /.../admin, base path should be its parent.
    if (preg_match('#/admin$#', $scriptDir)) {
        $scriptDir = preg_replace('#/admin$#', '', $scriptDir);
    }

    $projectPath = $scriptDir;
}

// Optional manual URL overrides for containerized deployments
$envBaseUrl = getenv('BASE_URL');
$envAdminBaseUrl = getenv('ADMIN_BASE_URL');

if (!empty($envBaseUrl) || !empty($envAdminBaseUrl)) {
    if (!empty($envBaseUrl)) {
        $Base_Url = rtrim($envBaseUrl, '/');
        $Admin_Base_Url = !empty($envAdminBaseUrl)
            ? rtrim($envAdminBaseUrl, '/')
            : $Base_Url . '/admin';
    } else {
        $Admin_Base_Url = rtrim($envAdminBaseUrl, '/');
        $Base_Url = preg_replace('#/admin$#', '', $Admin_Base_Url);
    }
} elseif ($isLocalhost) {
    // Local development environment
    $Base_Url = $protocol . $serverName . '/E-commerce-Project-FullStack';
    $Admin_Base_Url = $protocol . $serverName . '/E-commerce-Project-FullStack/admin';
} else {
    // Production / Docker environment
    // Keep the deployment subdirectory if present (e.g. /projects/Ecommerce).
    $Base_Url = $protocol . $hostWithPort . $projectPath;
    $Admin_Base_Url = $Base_Url . '/admin';
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