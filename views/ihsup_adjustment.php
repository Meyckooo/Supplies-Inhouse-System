<?php
session_start();
$base_path = "../";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$moduleId = 2;

/*========================================================== //
//             Enforce Main Module Access                   //
//==========================================================*/
if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$dbConn = getDBConnection();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjustments</title>
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/media.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>/assets/images/favicon.png">

</head>

<body>

    <?php include $base_path . 'includes/sidebar.php'; ?>


    <div class="main_content">
        <div class="wrapper">
            <div class="header_title">
                <div class="header_info">
                    <h1>Adjustments</h1>
                </div>
            </div>
        </div>
    </div>

      <div class="main_content_body">
        <div class="wrapper">
            <div class="main_content_body_con">
                
            
            </div>
        </div>
    </div>



    <?php include $base_path . 'includes/footer.php'; ?>


    <script src="<?php echo $base_path ?>assets/js/swal.js"></script>
    <script src="<?php echo $base_path ?>assets/js/access.js"></script>
    <script src="<?php echo $base_path ?>assets/js/pos_viewing.js"></script>
    <script src="<?php echo $base_path ?>assets/js/sidebar_button.js"></script>
</body>

</html>