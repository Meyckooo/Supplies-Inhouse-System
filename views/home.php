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
    <title>Home</title>
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
            <div class="header_title home_title">
                <h1 class="text-capitalize">
                    Atlantic <?php echo $timeContext; ?>, <?php echo htmlspecialchars($userName); ?>
                </h1>

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

                <div class="index_top">
                    <div class="top_index_info">
                        <h2>We Lead, <span>You Build.</span></h2>
                    </div>
                </div>

                <div class="index_bottom ">

                    <div class="index_video">
                        <video autoplay muted loop playsinline controls>
                            <source src="<?php echo $base_path ?>assets/video/atlantic-video.mp4" type="video/mp4">
                        </video>
                    </div>

                    <div class="index_right_content">
                        <h2>Integrity <span>Excellence</span> <cite>Teamwork</cite></h2>
                        <p>At Atlantic Hardware Cebu, we believe that strong relationships and quality service are built on integrity, driven by excellence, and strengthened through teamwork. We are committed to providing reliable hardware solutions, maintaining high standards, and working together to meet the needs of our customers and partners. Through trust, dedication, and collaboration, we continue to build stronger connections and contribute to a better future for the communities we serve.</p>
                        <p>Beyond providing quality products and dependable service, Atlantic Hardware Cebu values the importance of continuous growth and lasting partnerships. We strive to create a culture where every team member contributes to our shared goals, every customer is treated with respect, and every commitment reflects our dedication to excellence. Together, we aim to build a foundation of trust and reliability that supports our customers' projects and helps our community grow.</p>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <?php include $base_path . 'includes/footer.php'; ?>

    <script src="<?php echo $base_path ?>assets/js/swal.js"></script>
    <script src="<?php echo $base_path ?>assets/js/access.js"></script>
    <script src="<?php echo $base_path ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path ?>assets/js/ph_time.js"></script>

</body>

</html>