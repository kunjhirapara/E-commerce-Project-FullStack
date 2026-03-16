<?php
require_once __DIR__ . '/../includes/security.php';
Security::init_secure_session('ADMIN_SESSION');

unset($_SESSION['admin_email'], $_SESSION['admin_logged_in'], $_SESSION['admin_role'], $_SESSION['last_activity']);
Security::destroy_session();

header("Location: signin.php");
exit();
?>