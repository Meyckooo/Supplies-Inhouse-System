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

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
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
                                <input type="text" placeholder="Enter search value and press Enter" class="global_input" id="SearchInput">
                            </div>

                            <div class="filter_dropdown">
                                <select class="global_select" id="filterMonitoredCategory">
                                    <option value="">All Monitored By</option>
                                </select>
                            </div>

                            <div class="filter_dropdown">
                                <select class="global_select" id="filterDescriptionCategory">
                                    <option value="oitemcode">ITEM CODE</option>
                                    <option value="oitemno">ITEM NO</option>
                                    <option value="obarcode">BARCODE</option>
                                    <option value="orefno">REFERENCE NO.</option>
                                    <option value="oitemdesc">DESCRIPTION</option>
                                </select>
                            </div>
                        </div>

                        <div class="receiving_items_count" id="receivingItemsCount"><span>0</span> ITEMS FOUND</div>
                    </section>

                    <!-- RECEIVING CARDS SECTION -->
                    <div class="receiving_container">

                        <div
                            id="receivingCardContainer"
                            class="receiving_card_list"
                            data-endpoint="../controller/receiving_controller.php"
                            role="status"
                            aria-live="polite">Loading receiving records...</div>
                    </div>

                </div>


                <!-- RECEIVE ITEM MODAL -->
                <div class="received_modal_overlay" id="receiveItemModal">
                    <div class="receive_modal_content">
                        <div class="receive_modal_header">
                            <div class="receive_header_title_wrap">
                                <span class="receive_header_accent"></span>
                                <h3 id="modalItemTitle">Receiving Item</h3>
                            </div>
                            <button type="button" class="receive_modal_close" id="btnCloseReceiveModal">&times;</button>
                        </div>

                        <div class="receive_modal_body">
                            <div class="post_buttons">
                                <ul>
                                    <li><button type="button" class="global_post_btn" id="btnPostReceive">POST</button></li>
                                </ul>
                            </div>
                            
                            <div class="receive_summary_grid">
                                <div class="receive_sum_col">
                                    <span class="receive_label">ITEM CODE</span>
                                    <span class="receive_value" id="modalItemCode">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">ITEM NO</span>
                                    <span class="receive_value" id="modalItemNo">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">BARCODE</span>
                                    <span class="receive_value" id="modalBarcode">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">MONITORED BY</span>
                                    <span class="receive_value_bold" id="modalMonitored">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">UNIT</span>
                                    <span class="receive_value" id="modalUnit">-</span>
                                </div>
                                <div class="receive_sum_col receive_badge_box">
                                    <span class="receive_label">RECEIVED QTY</span>
                                    <span class="receive_badge_val" id="modalReceivedQty">-</span>
                                </div>
                                <div class="receive_sum_col receive_badge_box">
                                    <span class="receive_label">PLACED QTY</span>
                                    <span class="receive_badge_val" id="modalPlacedQty">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">REFERENCE NO.</span>
                                    <span class="receive_value" id="modalRefNo">-</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">CREATED DATE</span>
                                    <span class="receive_date_val" id="modalCreatedDate">-</span>
                                </div>
                                <div class="loc_action_box">
                                    <input type="text" class="global_input" id="receiveQuantityInput" placeholder="ENTER QUANTITY" inputmode="numeric" pattern="[0-9]*" data-numeric-only maxlength="5">
                                </div>
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