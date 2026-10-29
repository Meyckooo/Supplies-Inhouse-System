<?php
session_start();
$base_path = "../";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$moduleId = 2;

/*========================================================== //
//            Enforce Main Module Access                    //
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
    <title>Receiving</title>
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
                    <h1>Receiving</h1>
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
                <div class="header_buttons">
                    <ul>
                        <li><a class="global_back_btn" href="inhouse_main.php">← Back</a></li>
                    </ul>
                </div>

                <!-- INHOUSE REQUISITION TABLE SECTION -->
                <div class="inhouse_requisition global_table_wrapper">
                    <section class="global_filter_bar">
                        <div class="filter_group left">
                            <div class="search_box">
                                <input type="text" placeholder="Search..." class="global_input" id="SearchInput">
                            </div>

                            <div class="filter_dropdown">
                                <select class="global_select" id="filterMonitoredCategory">
                                    <option value="" disabled selected>All Monitored By</option>
                                    <option value="diy_indust">Diy_Indust</option>
                                </select>
                            </div>

                            <div class="filter_dropdown">
                                <select class="global_select" id="filterDescriptionCategory">
                                    <option value="" disabled selected>Description</option>
                                    <option value="desc">Sample Description</option>
                                </select>
                            </div>
                        </div>

                        <div class="receiving_items_count" id="receivingItemsCount"><span>2</span> ITEMS FOUND</div>
                    </section>

                    <!-- RECEIVING CARDS SECTION -->
                    <div class="receiving_container">
                        
                        <div id="receivingCardContainer" class="receiving_card_list">

                            <section class="receiving_card">
                                <div class="receiving_card_header">
                                    <h3 class="receiving_item_title">AUTO CIRCUIT TESTER TS:URIAH 158-569 UA663170 6-24</h3>
                                    <button type="button" class="receiving_btn_receive">RECEIVE</button>
                                </div>
                                <div class="receiving_card_body">
                                    <div class="receiving_info_grid">
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">ITEM CODE</span>
                                            <span class="receiving_value">IO309-0190</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">ITEM NO</span>
                                            <span class="receiving_value">90333</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">BARCODE</span>
                                            <span class="receiving_value">805089663173</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">MONITORED BY</span>
                                            <span class="receiving_value_bold">DIY_INDUST</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">UNIT</span>
                                            <span class="receiving_value">PCS</span>
                                        </div>
                                        <div class="receiving_info_col receiving_badge_box">
                                            <span class="receiving_label">RECEIVED QTY</span>
                                            <span class="receiving_badge_val">100.00 <small>PCS</small></span>
                                        </div>
                                        <div class="receiving_info_col receiving_badge_box">
                                            <span class="receiving_label">PLACED QTY</span>
                                            <span class="receiving_badge_val">104.00 <small>PCS</small></span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">REFERENCE NO.</span>
                                            <span class="receiving_value">115151</span>
                                        </div>
                                    </div>
                                    <div class="receiving_card_footer">
                                        <span class="receiving_label">CREATED DATE</span>
                                        <span class="receiving_date_val">2026-09-17 15:06:17</span>
                                    </div>
                                </div>
                            </section>
                            <section class="receiving_card">
                                <div class="receiving_card_header">
                                    <h3 class="receiving_item_title">AUTO CIRCUIT TESTER TS:URIAH 158-569 UA663170 6-24</h3>
                                    <button type="button" class="receiving_btn_receive">RECEIVE</button>
                                </div>
                                <div class="receiving_card_body">
                                    <div class="receiving_info_grid">
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">ITEM CODE</span>
                                            <span class="receiving_value">IO309-0190</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">ITEM NO</span>
                                            <span class="receiving_value">90333</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">BARCODE</span>
                                            <span class="receiving_value">805089663173</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">MONITORED BY</span>
                                            <span class="receiving_value_bold">DIY_INDUST</span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">UNIT</span>
                                            <span class="receiving_value">PCS</span>
                                        </div>
                                        <div class="receiving_info_col receiving_badge_box">
                                            <span class="receiving_label">RECEIVED QTY</span>
                                            <span class="receiving_badge_val">100.00 <small>PCS</small></span>
                                        </div>
                                        <div class="receiving_info_col receiving_badge_box">
                                            <span class="receiving_label">PLACED QTY</span>
                                            <span class="receiving_badge_val">104.00 <small>PCS</small></span>
                                        </div>
                                        <div class="receiving_info_col">
                                            <span class="receiving_label">REFERENCE NO.</span>
                                            <span class="receiving_value">115151</span>
                                        </div>
                                    </div>
                                    <div class="receiving_card_footer">
                                        <span class="receiving_label">CREATED DATE</span>
                                        <span class="receiving_date_val">2026-09-17 15:06:17</span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <?php include $base_path . 'includes/footer.php'; ?>

    <script src="<?php echo $base_path ?>assets/js/swal.js"></script>
    <script src="<?php echo $base_path ?>assets/js/access.js"></script>
    <script src="<?php echo $base_path ?>assets/js/supplies_receiving.js"></script>
    <script src="<?php echo $base_path ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path ?>assets/js/ph_time.js"></script>
</body>

</html>