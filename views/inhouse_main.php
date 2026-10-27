<?php
session_start();
$base_path = "./";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$moduleId = 2;
/*==========================================================
//             Enforce Main Module Access                   //
//==========================================================*/
if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$dbConn = getDBConnection();

// Check specific action permissions for frontend buttons
// $canAdd   = hasActionAccess($moduleId, 'Add', $isSuperAdmin, $userId, $dbConn);
// $canEdit  = hasActionAccess($moduleId, 'Edit', $isSuperAdmin, $userId, $dbConn);
// $canView  = hasActionAccess($moduleId, 'View', $isSuperAdmin, $userId, $dbConn);
// $canSave  = hasActionAccess($moduleId, 'Save', $isSuperAdmin, $userId, $dbConn);
// $canPost  = hasActionAccess($moduleId, 'Post', $isSuperAdmin, $userId, $dbConn);
// $canCancel = hasActionAccess($moduleId, 'Cancel', $isSuperAdmin, $userId, $dbConn);
// $canPrint = hasActionAccess($moduleId, 'Print', $isSuperAdmin, $userId, $dbConn);
// $canDiscount = hasActionAccess($moduleId, 'Discount', $isSuperAdmin, $userId, $dbConn);
// $canSend = hasActionAccess($moduleId, 'Send', $isSuperAdmin, $userId, $dbConn);
// $canSA = hasActionAccess($moduleId, 'SA', $isSuperAdmin, $userId, $dbConn);
// $canSupervisor = hasActionAccess($moduleId, 'Supervisor', $isSuperAdmin, $userId, $dbConn);
// $canManager = hasActionAccess($moduleId, 'Manager', $isSuperAdmin, $userId, $dbConn);
// $canAudit = hasActionAccess($moduleId, 'Audit', $isSuperAdmin, $userId, $dbConn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/media.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>../assets/images/favicon.png">

</head>

<body>

    <?php include $base_path . '../includes/sidebar.php'; ?>


    <div class="main_content">
        <div class="wrapper">
            <div class="header_title">
                <div class="header_info">
                    <h1>In-House</h1>
                </div>
                
                <div class="ph_realtime_clock_wrapper" id="phRealtimeClock">
                    <span class="ph_clock_date">Loading date...</span>
                    <span class="ph_clock_time">Loading time...</span>
                </div>
            </div>
        </div>
    </div>

      <div class="main_content_body">
        <div class="wrapper">
            <div class="main_content_body_con">
                <p>This is a simple In-House.</p>
            </div>
        </div>
    </div>



    <?php include $base_path . '../includes/footer.php'; ?>


    <script src="../assets/js/swal.js"></script>
    <script src="../assets/js/access.js"></script>
    <script src="../assets/js/sidebar_button.js"></script>
    <script src="../assets/js/ph_time.js"></script>
</body>

</html>