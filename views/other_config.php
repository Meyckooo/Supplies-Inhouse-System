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
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Config</title>
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
                    <h1>Other Functions</h1>
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
                   
                </div>

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
                                <!-- <button type="button" class="date_picker_trigger" id="datePickerBtn">
                                    <figure><img src="<?php echo $base_path ?>assets/images/icon/calendar.png" alt="calendar"></figure>
                                    <span id="selectedDateRangeText">Select Date</span>
                                </button> -->

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
                                    </div>

                                    <div class="calendar_footer">
                                        <button type="button" class="btn_clear" id="btnClearDate">Clear</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter_group right">
                            <button type="button" class="global_add_btn" id="togglePlateConfig">&plus; Plate Number</button>
                            <button type="button" class="global_add_btn" id="toggleApproverConfig">&plus; Approver List</button>
                        </div>
                    </section>

                    <!-- TABLE AREA 1 for Register plate # only-->
                    <section class="global_table_container" id="plateNumberSection" hidden>
                        <table class="global_table">
                            <thead>
                                <tr>
                                    <th>PLATE NO ID</th>
                                    <th>PLATE NO</th>
                                    <th class="text_center global-floating-action" style="width: 370px; min-width: 370px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="plateNumberTableBody" data-endpoint="../controller/plate_number_controller.php">
                                <tr><td colspan="3">Click Register Plate # to load plate numbers.</td></tr>
                            </tbody>
                        </table>
                    </section>

                    <!-- TABLE AREA 2 for Register Approver only-->
                    <section class="global_table_container" id="approverSection" hidden>
                        <table class="global_table">
                            <thead>
                                <tr>
                                    <th>Approver Name</th>
                                    <th>Approver ID</th>
                                    <th>Location ID</th>
                                    <th>Department to Approve</th>
                                    <th class="text_center global-floating-action" style="width: 370px; min-width: 370px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="approverTableBody" data-endpoint="../controller/approver_controller.php">
                                <tr><td colspan="5">Click Approver Config to load registered approvers.</td></tr>
                            </tbody>
                        </table>
                    </section>
                
                    <div class="modal fade" id="registerPlateModal" tabindex="-1" aria-labelledby="registerPlateModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="registerPlateForm" method="post" data-endpoint="../controller/plate_number_controller.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="registerPlateModalLabel">Register Plate #</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label for="plateNumberInput" class="form-label">Plate Number</label>
                                        <input type="text" class="form-control" id="plateNumberInput" name="oplateno" maxlength="10" required autocomplete="off">
                                        <div id="registerPlateMessage" class="mt-2" role="status" aria-live="polite"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" id="registerPlateSubmit">POST</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="registerApproverModal" tabindex="-1" aria-labelledby="registerApproverModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="registerApproverForm" method="post" data-endpoint="../controller/approver_controller.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="registerApproverModalLabel">Approver Config</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="approverSearchInput" class="form-label">Approver</label>
                                            <input type="text" class="form-control" id="approverSearchInput" autocomplete="off"
                                                placeholder="Search approver by name" aria-autocomplete="list"
                                                aria-controls="approverSearchResults" aria-expanded="false">
                                            <input type="hidden" id="approverUserId" name="approver_id">
                                            <div id="approverSearchResults" class="list-group mt-1" role="listbox" hidden></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="approverDepartmentSelect" class="form-label">Department</label>
                                            <select class="form-select" id="approverDepartmentSelect" name="department_id" required>
                                                <option value="">Loading departments...</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="approverLocationSelect" class="form-label">Location</label>
                                            <select class="form-select" id="approverLocationSelect" name="location_id" required>
                                                <option value="">Loading locations...</option>
                                            </select>
                                        </div>
                                        <div id="registerApproverMessage" class="mt-2" role="status" aria-live="polite"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary" id="registerApproverSubmit">POST</button>
                                    </div>
                                </form>
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
    <script src="<?php echo $base_path ?>assets/js/inhouse_requisition.js"></script>
    <script src="<?php echo $base_path ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_path ?>assets/js/other_config.js"></script>
    <script src="<?php echo $base_path ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path ?>assets/js/ph_time.js"></script>
</body>

</html>