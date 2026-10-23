<?php
$base_path = '';
require_once __DIR__ . '/includes/auth_check.php';

if (hasAccess(2, $isSuperAdmin, $allowedModules)) {
    header('Location: views/home.php');
    exit;
}

if (hasAccess(3, $isSuperAdmin, $allowedModules)) {
    header('Location: views/home.php');
    exit;
}

header('HTTP/1.1 403 Forbidden');
header('Location: 403.php');
exit;
?>
