<?php

/** @var string $base_path */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;

/*=========================================================
// Force Login Check (Redirect to views/login.php)       //
==========================================================*/
if (!$isLoggedIn) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . 'views/login.php'); 
    exit(); 
}

$userName      = $_SESSION['user_name'] ?? 'User';
$userId        = $_SESSION['user_id'] ?? '';
$isSuperAdmin  = isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
$currentDate   = date("F d, Y");

$conn = getDBConnection();

/*=========================================================
// Fetch current user's allowed module IDs and permissions //
==========================================================*/
$allowedModules = [];
if ($isLoggedIn && !$isSuperAdmin) {
    $queryAllowed = "SELECT oModuleid 
                    FROM tbl_access 
                    WHERE oUserid = ? 
                    AND oMain = 1";
    if ($stmt = $conn->prepare($queryAllowed)) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $resultAllowed = $stmt->get_result();
        while ($row = $resultAllowed->fetch_assoc()) {
            $allowedModules[] = (int)$row['oModuleid'];
        }
        $stmt->close();
    }
}
/*=========================================================
//         Helper function to verify permissions         //
=========================================================*/
if (!function_exists('hasAccess')) {
    function hasAccess(int $moduleId, bool $isSuperAdmin, array $allowedModules): bool {
        return $isSuperAdmin || in_array($moduleId, $allowedModules);
    }
}

if (!function_exists('hasActionAccess')) {
    function hasActionAccess(int $moduleId, string $action, bool $isSuperAdmin, string $userId, mysqli $conn): bool
    {
        if ($isSuperAdmin) {
            return true;
        }

        // Standardize column name ('View' -> 'oView', 'Print' -> 'oPrint')
        $dbColumn = (strpos($action, 'o') === 0) ? $action : 'o' . ucfirst($action);

        $allowedColumns = ['oMain', 'oAdd', 'oEdit', 'oView', 'oSave', 'oPost', 'oCancel', 'oPrint', 'oDiscount', 'oSend', 'oSA', 'oSupervisor', 'oManager', 'oAudit'];
        if (!in_array($dbColumn, $allowedColumns)) {
            return false;
        }

        $query = "SELECT {$dbColumn} FROM tbl_access WHERE oUserid = ? AND oModuleid = ? LIMIT 1";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("si", $userId, $moduleId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return isset($result[$dbColumn]) && (int)$result[$dbColumn] === 1;
        }

        return false;
    }
}


if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

    $project_folder = explode('/', trim($script_name, '/'))[0] ?? '';

    $base_url = $project_folder ? '/' . $project_folder . '/' : '/';
    define('BASE_URL', $base_url);
}