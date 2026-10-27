<?php
session_start();
$base_path = "./";

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
    <title>In-House & Supplies Requisition</title>
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
                    <h1>Requisition</h1>
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
                                <input type="text" placeholder="Search..." class="global_input" id="tableSearchInput">
                            </div>

                            <div class="filter_dropdown">
                                <select class="global_select" id="filterCategory">
                                    <option value="" disabled selected>Filter By</option>
                                    <option value="ctrl_no">Control No</option>
                                    <option value="location">Location</option>
                                    <option value="department">Department</option>
                                    <option value="approver">Approver</option>
                                    <option value="section">Section</option>
                                </select>
                            </div>

                            <div class="date_picker_container">
                                <button type="button" class="date_picker_trigger" id="datePickerBtn">
                                    <figure><img src="../assets/images/icon/calendar.png" alt="calendar"></figure>
                                    <span id="selectedDateRangeText">Select Date</span>
                                </button>

                                <div class="calendar_dropdown" id="calendarDropdown">
                                    <div class="calendar_header">
                                        <button type="button" class="nav_btn" id="prevMonthBtn">&lt;</button>
                                        <div class="month_year_selector">
                                            <select id="monthSelect" class="header_select"></select>
                                            <select id="yearSelect" class="header_select"></select>
                                        </div>
                                        <button type="button" class="nav_btn" id="nextMonthBtn">&gt;</button>
                                    </div>

                                    <div class="calendar_grid" id="calendarGrid">
                                        <!-- Rendered Dynamically via JS -->
                                    </div>

                                    <div class="calendar_footer">
                                        <button type="button" class="btn_clear" id="btnClearDate">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter_group right">
                            <a href="inhouse_requisition_add.php" class="global_add_btn">&plus; Add</a>
                        </div>
                    </section>

                    <!-- TABLE AREA -->
                    <section class="global_table_container">
                        <table class="global_table">
                            <thead>
                                <tr>
                                    <th>Ctrl No</th>
                                    <th>Date Requested</th>
                                    <th>Department</th>
                                    <th>Section</th>
                                    <th>Location</th>
                                    <th>Purpose</th>
                                    <th>Requested By</th>
                                    <th>Approver</th>
                                    <th>Approval Date</th>
                                    <th>Status</th>
                                    <th class="text_center global-floating-action" style="width: 370px; min-width: 370px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>REQ-2026-001</td>
                                    <td>2026-10-26</td>
                                    <td>IT Department</td>
                                    <td>Software Dev</td>
                                    <td>Cebu City</td>
                                    <td>System Upgrade Hardware</td>
                                    <td>Ivan</td>
                                    <td>Robert Balalilhe</td>
                                    <td>2026-10-26 13:32:35</td>
                                    <td><span class="global_status status_pending">Pending</span></td>
                                    <td class="action_cell global-floating-action">
                                        <button class="btn-tool-tip btn_edit"><span>Edit</span></button>
                                        <button class="btn-tool-tip btn_view"><span>View</span></button>
                                        <button class="btn-tool-tip btn_print"><span>Print</span></button>
                                        <button class="btn-tool-tip btn_cancel"><span>Cancel</span></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>REQ-2026-002</td>
                                    <td>2026-10-28</td>
                                    <td>Accounting</td>
                                    <td>Finance</td>
                                    <td>Banawa Branch</td>
                                    <td>Office Supplies</td>
                                    <td>Argie</td>
                                    <td>Robert Balalilhe</td>
                                    <td>2026-10-26 13:32:35</td>
                                    <td><span class="global_status status_approved">Approved</span></td>
                                    <td class="action_cell global-floating-action">
                                        <button class="btn-tool-tip btn_edit"><span>Edit</span></button>
                                        <button class="btn-tool-tip btn_view"><span>View</span></button>
                                        <button class="btn-tool-tip btn_print"><span>Print</span></button>
                                        <button class="btn-tool-tip btn_cancel"><span>Cancel</span></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>REQ-2026-003</td>
                                    <td>2026-10-30</td>
                                    <td>Operations</td>
                                    <td>Logistics</td>
                                    <td>Mandaue Hub</td>
                                    <td>Vehicle Maintenance</td>
                                    <td>Meycko</td>
                                    <td>Robert Balalilhe</td>
                                    <td>2026-10-26 13:32:35</td>
                                    <td><span class="global_status status_cancelled">Cancelled</span></td>
                                    <td class="action_cell global-floating-action">
                                        <button class="btn-tool-tip btn_edit"><span>Edit</span></button>
                                        <button class="btn-tool-tip btn_view"><span>View</span></button>
                                        <button class="btn-tool-tip btn_print"><span>Print</span></button>
                                        <button class="btn-tool-tip btn_cancel"><span>Cancel</span></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>

                <div class="inhouse_requisition_child_tbl global_display_table_wrapper">
                    <div class="global_display_table_container">
                        <table class="global_display_table">
                            <thead>
                                <tr>
                                    <th class="text_center" style="width: 60px;">#</th>
                                    <th>Item Code</th>
                                    <th>Item Description</th>
                                    <th>UOM</th>
                                    <th class="text_right">Req Qty</th>
                                    <th class="text_right">Approve Qty</th>
                                    <th class="text_right">Released Qty</th>
                                    <th>Item No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text_center">1</td>
                                    <td>X1007-049</td>
                                    <td>ZZ. ALBATROSS</td>
                                    <td>PCS</td>
                                    <td class="text_right">10</td>
                                    <td class="text_right">10</td>
                                    <td class="text_right">0</td>
                                    <td>137297</td>
                                </tr>
                                <tr>
                                    <td class="text_center index_col">2</td>
                                    <td>X4-464</td>
                                    <td>ZZ. ALBATROSS</td>
                                    <td>PCS</td>
                                    <td class="text_right">1</td>
                                    <td class="text_right">1</td>
                                    <td class="text_right">0</td>
                                    <td>160721</td>
                                </tr>
                                <tr>
                                    <td class="text_center index_col">3</td>
                                    <td>X4-378</td>
                                    <td>ZZ. TOILET BOWL CLEANER</td>
                                    <td>GAL</td>
                                    <td class="text_right">1</td>
                                    <td class="text_right">1</td>
                                    <td class="text_right">0</td>
                                    <td>160510</td>
                                </tr>
                                <tr>
                                    <td class="text_center index_col">4</td>
                                    <td>X4-466</td>
                                    <td>ZZ. DETERGENT POWDER</td>
                                    <td>PCS</td>
                                    <td class="text_right">2</td>
                                    <td class="text_right">2</td>
                                    <td class="text_right">0</td>
                                    <td>160724</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <?php include $base_path . '../includes/footer.php'; ?>

    <script src="../assets/js/swal.js"></script>
    <script src="../assets/js/access.js"></script>
    <script src="../assets/js/inhouse_requisition.js"></script>
    <script src="../assets/js/sidebar_button.js"></script>
    <script src="../assets/js/ph_time.js"></script>
</body>

</html>