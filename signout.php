<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');

unset($_SESSION['email'], $_SESSION['userLogId'], $_SESSION['last_activity']);
Security::destroy_session();
header("Location: index.php");
exit();
?>