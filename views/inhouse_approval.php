<?php
session_start();
$base_path = "../";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$moduleId = 2;

// Enforce Main Module Access
if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

// Time & greeting context
date_default_timezone_set('Asia/Manila');
$hour = (int)date('G');
if ($hour >= 5 && $hour < 12) {
    $timeContext = "Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $timeContext = "Afternoon";
} else {
    $timeContext = "Evening";
}

$dbConn = getDBConnection();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INHOUSE APPROVAL</title>

    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/media.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>/assets/images/favicon.png">

    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed-preload');
        }
    </script>
    <script src="assets/js/sidebar_button.js"></script>

    <script>
        function guardModuleAccess(isLoggedIn, hasAccess, moduleName) {
            if (!isLoggedIn) {
                alert("Authentication Required: You must log into an active account to access the " + moduleName);
                window.location.href = "views/login.php";
                return false;
            }
            if (!hasAccess) {
                alert("Access Denied: You are not permitted to access the " + moduleName);
                return false;
            }
            return true;
        }
    </script>
</head>

<body>

    <?php include $base_path . 'includes/sidebar.php'; ?>

    <div class="main_content">
        <div class="wrapper">
            <div class="header_title">
                <div class="header_info">
                    <h1>Approval</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="main_content_body">
        <div class="wrapper">
            <div class="main_content_body_con">
                <p>Parent Table</p>
                <!-- MAIN PARENT TABLE -->
            <div style="position: relative; z-index: 1; margin-top: 20px;">
                <table class="table" style="width: 100%; border: 1px solid #010000; position: relative; z-index: 1; height: 400px; max-height: 400px; overflow-y: auto;">
                    <thead>
                        <tr style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 2; border: 1px solid #010000;">
                            <th>CTRL_NO</th>
                            <th>DATE REQUESTED</th>
                            <th>DEPARTMENT</th>
                            <th>SECTION</th>
                            <th>LOCATION</th>
                            <th>STATUS</th>
                            <th>PURPOSE</th>
                            <th>REQUESTED BY</th>
                            <th>APPROVER</th>
                            <th>APPROVAL DATE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>2024-06-01</td>
                            <td>IT</td>
                            <td>Development</td>
                            <td>HQ</td>
                            <td>Pending</td>
                            <td>Development</td>
                            <td>John Doe</td>
                            <td>Jane Smith</td>
                            <td>2024-06-02</td>
                            <td><button>VIEW</button></td>
                            <td><button>APPROVE</button></td>
                            <td> <button>PRINT</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MAIN PARENT TABLE -->
             <table style="width: 100%; border: 1px solid #010000; position: relative; z-index: 1; max-height: 400px; overflow-y: auto; margin-top: 20px;">
                <thead>
                    <tr style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 2; border: 1px solid #010000;">
                        <th>ITEM CODE</th>
                        <th>ITEM DESCRIPTION</th>
                        <th>OUM</th>
                        <th>REQ QTY</th>
                        <th>APPROVE QTY</th>
                        <th>RELEASE QTY</th>
                        <th>ITEM NO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ITM001</td>
                        <td>Item Description 1</td>
                        <td>PCS</td>
                        <td>10</td>
                        <td>8</td>
                        <td>5</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>ITM002</td>
                        <td>Item Description 2</td>
                        <td>PCS</td>
                        <td>20</td>
                        <td>15</td>
                        <td>10</td>
                        <td>2</td>
                    </tr>
             </table>
            </div>

            
        </div>
    </div>

    <?php include $base_path . 'includes/footer.php'; ?>

</body>

</html>