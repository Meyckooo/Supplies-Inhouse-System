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


                <!-- RECEIVE ITEM MODAL -->
                <div class="received_modal_overlay" id="receiveItemModal">
                    <div class="receive_modal_content">
                        <div class="receive_modal_header">
                            <div class="receive_header_title_wrap">
                                <span class="receive_header_accent"></span>
                                <h3 id="modalItemTitle">AUTO CIRCUIT TESTER TS:URIAH 158-569 UA663170 6-24</h3>
                            </div>
                            <button type="button" class="receive_modal_close" id="btnCloseReceiveModal">&times;</button>
                        </div>

                        <div class="receive_modal_body">
                            <div class="receive_summary_grid">
                                <div class="receive_sum_col">
                                    <span class="receive_label">ITEM CODE</span>
                                    <span class="receive_value" id="modalItemCode">IO309-0190</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">ITEM NO</span>
                                    <span class="receive_value" id="modalItemNo">90333</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">BARCODE</span>
                                    <span class="receive_value" id="modalBarcode">805089663173</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">MONITORED BY</span>
                                    <span class="receive_value_bold" id="modalMonitored">DIY_INDUST</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">UNIT</span>
                                    <span class="receive_value" id="modalUnit">PCS</span>
                                </div>
                                <div class="receive_sum_col receive_badge_box">
                                    <span class="receive_label">RECEIVED QTY</span>
                                    <span class="receive_badge_val" id="modalReceivedQty">100.00 <small>PCS</small></span>
                                </div>
                                <div class="receive_sum_col receive_badge_box">
                                    <span class="receive_label">PLACED QTY</span>
                                    <span class="receive_badge_val" id="modalPlacedQty">104.00 <small>PCS</small></span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">REFERENCE NO.</span>
                                    <span class="receive_value" id="modalRefNo">115151</span>
                                </div>
                                <div class="receive_sum_col">
                                    <span class="receive_label">CREATED DATE</span>
                                    <span class="receive_date_val" id="modalCreatedDate">2026-09-17 15:06:17</span>
                                </div>
                            </div>

                            <div class="receive_balance_box">
                                <div class="balance_row">
                                    <span class="balance_label">SYSTEM BAL</span>
                                    <span class="balance_val">0.00 <small>PCS</small></span>
                                </div>
                                <div class="balance_row">
                                    <span class="balance_label">DEPOT BAL</span>
                                    <span class="balance_val">0.00 <small>PCS</small></span>
                                </div>
                            </div>

                            <div class="locators_section_header">
                                <h4>LOCATORS (<span id="locatorCount">3</span>)</h4>
                            </div>

                            <!-- Locators List Container -->
                            <div class="locators_list_container scrollable_locators_box" id="locatorsListContainer">
                                <!-- Locator Item Row 1 -->
                                <div class="locator_row_card">
                                    <div class="loc_info">
                                        <span class="receiving_label">LOCATOR</span>
                                        <span class="loc_val">3PS1A1</span>
                                    </div>
                                    <div class="loc_info">
                                        <span class="receiving_label">RACK</span>
                                        <span class="loc_val">DIY</span>
                                    </div>
                                    <div class="loc_qty_badge">
                                        <span class="receiving_label">QUANTITY</span>
                                        <span class="receiving_badge_val">65.00 <small>PCS</small></span>
                                    </div>
                                    <div class="loc_action_box">
                                        <input type="text" class="global_input" placeholder="ENTER QUANTITY">
                                        <button type="button" class="btn_put_locator">PUT</button>
                                        <button type="button" class="btn_remove_locator">Remove</button>
                                    </div>
                                </div>

                                <!-- Locator Item Row 2 -->
                                <div class="locator_row_card">
                                    <div class="loc_info">
                                        <span class="receiving_label">LOCATOR</span>
                                        <span class="loc_val">3PS1F2</span>
                                    </div>
                                    <div class="loc_info">
                                        <span class="receiving_label">RACK</span>
                                        <span class="loc_val">DIY</span>
                                    </div>
                                    <div class="loc_qty_badge">
                                        <span class="receiving_label">QUANTITY</span>
                                        <span class="receiving_badge_val">50.00 <small>PCS</small></span>
                                    </div>
                                    <div class="loc_action_box">
                                        <input type="text" class="global_input" placeholder="ENTER QUANTITY">
                                        <button type="button" class="btn_put_locator">PUT</button>
                                        <button type="button" class="btn_remove_locator">Remove</button>
                                    </div>
                                </div>

                                <!-- Locator Item Row 3 -->
                                <div class="locator_row_card">
                                    <div class="loc_info">
                                        <span class="receiving_label">LOCATOR</span>
                                        <span class="loc_val">3PS1J2</span>
                                    </div>
                                    <div class="loc_info">
                                        <span class="receiving_label">RACK</span>
                                        <span class="loc_val">DIY</span>
                                    </div>
                                    <div class="loc_qty_badge">
                                        <span class="receiving_label">QUANTITY</span>
                                        <span class="receiving_badge_val">20.00 <small>PCS</small></span>
                                    </div>
                                    <div class="loc_action_box">
                                        <input type="text" class="global_input" placeholder="ENTER QUANTITY">
                                        <button type="button" class="btn_put_locator">PUT</button>
                                        <button type="button" class="btn_remove_locator">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <!-- ADD LOCATOR BUTTON -->
                            <button type="button" class="floating_add_locator_btn" id="openAddLocatorModalBtn" title="Add Locator">
                                <span>+</span>
                            </button>
                            
                               <!-- ADD LOCATOR SUB-MODAL -->
                            <div class="received_modal_overlay" id="addLocatorSubModal">
                                <div class="add_locator_modal_content">
                                    <div class="add_locator_modal_header">
                                        <h3>ADD LOCATOR</h3>
                                        <button type="button" class="add_locator_cancel_text" id="btnCloseAddLocatorModal">Cancel</button>
                                    </div>

                                    <div class="add_locator_modal_body">
                                        <div class="form_group">
                                            <input type="text" class="global_input" id="inputLocatorName" placeholder="LOCATOR">
                                        </div>
                                        <div class="form_group">
                                            <input type="text" class="global_input" id="inputRackName" placeholder="RACK">
                                        </div>
                                        <div class="form_group">
                                            <input type="text" class="global_input" id="inputInitialQty" placeholder="INITIAL QUANTITY">
                                        </div>

                                        <button type="button" class="global_add_locator_submit_btn" id="btnSubmitNewLocator">ADD LOCATOR</button>
                                    </div>
                                </div>
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