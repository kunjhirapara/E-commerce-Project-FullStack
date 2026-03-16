<?php
/**
 * Security Helper Class
 * 
 * Provides modern security features:
 * - Secure session management with HTTP-only cookies
 * - CSRF token generation and validation
 * - Password hashing with bcrypt/Argon2
 * - Security headers
 * - Rate limiting helpers
 * 
 * @version 2.0
 * @author Kunj Developer
 */

class Security {

    /**
     * Build a safe cookie domain value.
     * Returns empty string for localhost/IP hosts so PHP uses host-only cookies.
     *
     * @return string
     */
    private static function get_cookie_domain() {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        if ($host === '') {
            return '';
        }

        // Remove port if present (e.g. localhost:8080).
        $host = preg_replace('/:\\d+$/', '', $host);

        // Host-only cookie for localhost and IPs.
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return '';
        }

        return $host;
    }
    
    /**
     * Initialize secure session configuration
     * Uses modern PHP session best practices
     * 
     * @param string $session_name Custom session name
     * @param bool $regenerate_id Whether to regenerate session ID
     */
    public static function init_secure_session($session_name = 'SECURE_SESS', $regenerate_id = false) {
        // Session configuration before session_start()
        if (session_status() === PHP_SESSION_NONE) {
            $cookieDomain = self::get_cookie_domain();
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);

            // Secure session cookie parameters
            session_set_cookie_params([
                'lifetime' => 0,                    // Session cookie (until browser close)
                'path' => '/',
                'domain' => $cookieDomain,
                'secure' => $isHttps,              // Only send over HTTPS
                'httponly' => true,                 // Prevent JavaScript access
                'samesite' => 'Strict'              // CSRF protection
            ]);
            
            // Use custom session name (not default PHPSESSID)
            session_name($session_name);
            
            // Only use cookies for sessions (no URL parameters)
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_trans_sid', 0);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Start the session
            session_start();
            
            // Regenerate session ID if requested (prevents session fixation)
            if ($regenerate_id && isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
            }
            
            // Mark session as initiated
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
                $_SESSION['created'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['remote_addr'] = self::get_client_ip();
            }
            
            // Validate session
            self::validate_session();
        }
    }
    
    /**
     * Validate current session for security
     * Checks for session hijacking attempts
     */
    private static function validate_session() {
        // Check if session has expired (30 minutes of inactivity)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            self::destroy_session();
            return false;
        }
        $_SESSION['last_activity'] = time();
        
        // Validate user agent (basic fingerprinting)
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
                self::destroy_session();
                return false;
            }
        }
        
        // Validate IP address (can be disabled if using mobile networks)
        // Uncomment to enable IP validation
        // if (isset($_SESSION['remote_addr'])) {
        //     if ($_SESSION['remote_addr'] !== self::get_client_ip()) {
        //         self::destroy_session();
        //         return false;
        //     }
        // }
        
        // Regenerate session ID periodically (every 30 minutes)
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } else if (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        return true;
    }
    
    /**
     * Get real client IP address
     * Handles proxies and load balancers
     */
    private static function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                    'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Securely destroy session
     */
    public static function destroy_session() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = array();
            
            // Delete session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
        }
    }
    
    /**
     * Hash password using modern algorithm
     * Uses Argon2 if available, falls back to bcrypt
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hash_password($password) {
        // Use Argon2 if available (PHP 7.2+), otherwise use bcrypt
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,  // 64 MB
                'time_cost' => 4,        // 4 iterations
                'threads' => 3           // 3 parallel threads
            ]);
        } else if (defined('PASSWORD_ARGON2I')) {
            return password_hash($password, PASSWORD_ARGON2I, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        } else {
            // Bcrypt fallback (most compatible)
            return password_hash($password, PASSWORD_BCRYPT, [
                'cost' => 12  // Cost factor (4-31, higher = more secure but slower)
            ]);
        }
    }
    
    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password matches
     */
    public static function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing
     * Returns true if algorithm has been updated
     * 
     * @param string $hash Current password hash
     * @return bool True if needs rehashing
     */
    public static function needs_rehash($hash) {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        } else if (defined('PASSWORD_ARGON2I')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2I, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
        } else {
            return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
        }
    }
    
    /**
     * Generate CSRF token
     * Creates a unique token for form/AJAX protection
     * 
     * @return string CSRF token
     */
    public static function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validate_csrf_token($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token input field HTML
     * 
     * @return string HTML input field
     */
    public static function csrf_token_input() {
        $token = self::generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Set security headers
     * Adds modern security headers to responses
     */
    public static function set_security_headers() {
        if (headers_sent()) {
            return;
        }

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection (for older browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (adjust as needed)
        // header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
        
        // Permissions Policy (formerly Feature-Policy)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    /**
     * Sanitize input data
     * Basic input sanitization
     * 
     * @param string $data Input data
     * @return string Sanitized data
     */
    public static function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Validate email
     * 
     * @param string $email Email address
     * @return bool True if valid
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate an uploaded image using server-side MIME detection.
     *
     * @param array $file Uploaded file entry from $_FILES
     * @param int $maxSize Maximum allowed file size in bytes
     * @return array Validation result
     */
    public static function validate_uploaded_image($file, $maxSize = 2097152) {
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name'])) {
            return ['valid' => false, 'error' => 'No file uploaded or selected.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload failed. Please try again.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid upload source.'];
        }

        if ($file['size'] <= 0 || $file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large. File must be less than 2 megabytes.'];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Uploaded file is not a valid image.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if ($mimeType === false || !isset($allowedMimeTypes[$mimeType])) {
            return ['valid' => false, 'error' => 'Invalid file type. Only JPG, GIF and PNG types are accepted.'];
        }

        return [
            'valid' => true,
            'mime' => $mimeType,
            'extension' => $allowedMimeTypes[$mimeType]
        ];
    }

    /**
     * Move a validated uploaded image into the destination directory.
     *
     * @param array $file Uploaded file entry from $_FILES
     * @param string $uploadDir Destination directory
     * @param string $prefix Filename prefix
     * @return array Storage result
     */
    public static function store_uploaded_image($file, $uploadDir, $prefix = 'upload_') {
        $validation = self::validate_uploaded_image($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'error' => 'Failed to create upload directory.'];
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
        $safeBaseName = trim($safeBaseName, '_');
        if ($safeBaseName === '') {
            $safeBaseName = 'image';
        }

        $fileName = uniqid($prefix, true) . '_' . $safeBaseName . '.' . $validation['extension'];
        $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file. Check folder permissions.'];
        }

        return ['success' => true, 'filename' => $fileName];
    }
    
    /**
     * Rate limiting helper
     * Simple rate limiting based on session
     * 
     * @param string $action Action identifier
     * @param int $limit Maximum attempts
     * @param int $window Time window in seconds
     * @return bool True if within limit
     */
    public static function check_rate_limit($action, $limit = 5, $window = 300) {
        $key = 'rate_limit_' . $action;
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'reset_time' => time() + $window
            ];
            return true;
        }
        
        // Check if window has expired
        if (time() > $_SESSION[$key]['reset_time']) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'reset_time' => time() + $window
            ];
            return true;
        }
        
        // Increment attempts
        $_SESSION[$key]['attempts']++;
        
        // Check if limit exceeded
        return $_SESSION[$key]['attempts'] <= $limit;
    }
    
    /**
     * Generate random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generate_token($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

// Auto-set security headers on include
Security::set_security_headers();
