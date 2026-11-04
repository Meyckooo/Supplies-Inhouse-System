<?php
session_start();
$base_path = "../";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controller/inhouse_controller.php';

$moduleId = 2;

$phTimezone = new DateTimeZone('Asia/Manila');
$currentPhDate = (new DateTime('now', $phTimezone))->format('Y-m-d');

/*========================================================== //
//           Enforce Main Module Access                  //
//==========================================================*/
if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$currentUser = $_SESSION['user_name'] ?? '';

$dbConn = getDBConnection();

$inhouseController = new InhouseController($dbConn);
$locations = $inhouseController->getCurrentCompany();

$defaultCompany = !empty($locations) ? $locations[0]['ocompany'] : '';
$defaultLocation = !empty($locations) ? $locations[0]['olocation'] : '';

$inhouseController = new InhouseController($dbConn);
$deptRecords = $inhouseController->getDepartments();

$departments = array_unique(array_column($deptRecords, 'odepartment_name'));
sort($departments);

$companyLocation = (new InhouseController($dbConn))->getCompanyLocation();

$approvers = $inhouseController->getApprovers();

$plateNumbers = $inhouseController->getPlateNumbers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-House &amp; Supplies Requisition</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/media.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>assets/images/favicon.png">
</head>

<body>

    <?php include $base_path . 'includes/sidebar.php'; ?>

    <div class="main_content">
        <div class="wrapper">
            <div class="header_title">
                <div class="header_info">
                    <h1>Add Requisition</h1>
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
                        <li><a class="global_back_btn" href="inhouse_requisition.php">← Back</a></li>
                    </ul>
                </div>

                <div class="global_form_wrapper">
                    <div class="form_page_header">
                        <h3 class="h3_repeat">Add Inhouse Requisition</h3>
                    </div>

                    <div class="form_body_content">
                        <!-- Added enctype="multipart/form-data" to allow file uploads and wrapped both form inputs and details table inside -->
                        <form id="inhouseForm" action="../controller/inhouse_controller.php" method="POST" enctype="multipart/form-data">

                            <div class="global_form_grid_sections">
                                <!-- Section 1: General Details -->
                                <div class="form_section_card">
                                    <div class="section_title">General Details</div>

                                    <div class="form_group">
                                        <label>Company</label>
                                        <input type="text" name="company" class="global_input has-value" value="<?php echo htmlspecialchars($defaultCompany); ?>" disabled placeholder="Auto" required>
                                    </div>

                                    <div class="form_group">
                                        <label>Location</label>
                                        <input type="text" name="location" class="global_input has-value" value="<?php echo htmlspecialchars($defaultLocation); ?>" disabled placeholder="Auto" required>
                                    </div>

                                    <div class="form_group">
                                        <label>Department <span class="required">*</span></label>
                                        <select class="global_select" id="departmentSelect" name="department" required>
                                            <option value="" disabled selected>Select Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <?php if (!empty($dept)): ?>
                                                    <option value="<?php echo htmlspecialchars($dept); ?>">
                                                        <?php echo htmlspecialchars($dept); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form_group">
                                        <label>Section <span class="required">*</span></label>
                                        <select class="global_select" id="sectionSelect" name="section" required>
                                            <option value="" disabled selected>Select Section</option>
                                        </select>
                                    </div>

                                    <!-- Pass the database mapping to JavaScript -->
                                    <script>
                                        const departmentSectionMap = <?php echo json_encode($deptRecords); ?>;
                                    </script>

                                    <!-- <div class="form_group">
                                        <label>Attachement</label>
                                        <input type="file" name="attachment" class="global_input">
                                    </div> -->
                                </div>

                                <!-- Section 2: Purpose &amp; Operations -->
                                <div class="form_section_card">
                                    <div class="section_title">Purpose &amp; Operations</div>

                                    <div class="form_group">
                                        <label>Mode <span class="required">*</span></label>
                                        <select class="global_select" name="mode" required>
                                            <option value="" disabled selected>Select Mode</option>
                                            <option value="pickup">Pick-up</option>
                                            <option value="pick&amp;delivery">Pick-up &amp; Delivery</option>
                                        </select>
                                    </div>

                                    <div class="form_group">
                                        <label>Purpose <span class="required">*</span></label>
                                        <select class="global_select" id="purposeSelect" name="purpose" required>
                                            <option value="" disabled selected>Select Purpose</option>
                                            <option value="office_admin">Office &amp; Admin</option>
                                            <option value="handling_storage">Handling &amp; Storage Supplies</option>
                                            <option value="other_operational">Other Operational Supplies</option>
                                            <option value="medical">Medical Supplies</option>
                                            <option value="selling_marketing">Selling &amp; Marketing</option>
                                            <option value="facility_sanitation">Facility Sanitation/ Upkeep &amp; Janitorial</option>
                                            <option value="production">Production</option>
                                            <option value="facilities_building">Facilities &amp; Building</option>
                                            <option value="equipment_machinery">Equipment &amp; Machinery</option>
                                            <option value="vehicle_fleet">Vehicle &amp; Fleet</option>
                                            <option value="it_hardware">IT &amp; Hardware</option>
                                            <option value="safety_ppe">Safety &amp; PPE (Personal Protective Equipment)</option>
                                        </select>
                                    </div>

                                    <!-- Subdropdowns -->
                                    <div class="form_group sub_dropdown_group" id="sub_selling_marketing" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="cashiering">Cashiering / POS</option>
                                            <option value="selling_fulfillment">Selling &amp; Fulfillment</option>
                                            <option value="promotional">Promotional &amp; Display</option>
                                            <option value="diy">DIY</option>
                                            <option value="counter">Counter</option>
                                        </select>
                                    </div>

                                    <div class="form_group sub_dropdown_group" id="sub_production" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="stickering">Stickering/ Labeling</option>
                                            <option value="packaging">Production Packaging</option>
                                            <option value="others">Others</option>
                                        </select>
                                    </div>

                                    <div class="form_group sub_dropdown_group" id="sub_facilities_and_building" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="repairs">Repairs</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="project">Project</option>
                                        </select>
                                    </div>

                                    <div class="form_group sub_dropdown_group" id="sub_it_and_hardware" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="repairs">Repairs</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="project">Project</option>
                                        </select>
                                    </div>

                                    <div class="form_group sub_dropdown_group" id="sub_equipment_and_machinery" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="repairs">Repairs</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="project">Project</option>
                                        </select>
                                    </div>

                                    <div class="form_group sub_dropdown_group" id="sub_vehicle_fleet" style="display:none;">
                                        <label>Subpurpose<span class="required">*</span></label>
                                        <select name="sub_purpose" class="global_select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="repairs">Repairs</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="project">Project</option>
                                        </select>
                                    </div>


                                    <div class="form_group">
                                        <label>Approver <span class="required">*</span></label>
                                        <select class="global_select" name="approverId" required>
                                            <option value="" disabled selected>Select Approver</option>
                                            <?php foreach ($approvers as $app): ?>
                                                <option value="<?php echo htmlspecialchars($app['oapprover_id']); ?>">
                                                    <?php echo htmlspecialchars($app['oapprover_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form_group">
                                        <label>Post Date</label>
                                        <input type="date" name="postDate" class="global_input has-value" value="<?php echo $currentPhDate; ?>" required>
                                    </div>
                                </div>

                                <!-- Section 3: Additional Details -->
                                <div class="form_section_card">
                                    <div class="section_title">Additional Details</div>

                                    <div class="form_group">
                                        <label>Requested By</label>
                                        <input type="text" name="postBy" class="global_input has-value" value="<?php echo htmlspecialchars($currentUser); ?>" disabled required>
                                    </div>

                                    <div class="form_group">
                                        <label>Priority Levels <span class="required">*</span></label>
                                        <select class="global_select priority_select" name="prioLvl" required>
                                            <option value="" disabled selected>Select Priority</option>
                                            <option value="4">Urgent</option>
                                            <option value="3">High</option>
                                            <option value="2">Standard</option>
                                            <option value="1">Low</option>
                                        </select>
                                    </div>

                                    <div class="form_group">
                                        <label>Plate Number / # <span class="required">*</span></label>
                                        <select class="global_select" name="plate_no" required>
                                            <option value="" disabled selected>Select Plate No.</option>
                                            <?php foreach ($plateNumbers as $plate): ?>
                                                <option value="<?php echo htmlspecialchars($plate['oplateno']); ?>">
                                                    <?php echo htmlspecialchars($plate['oplateno']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form_group">
                                        <label>Odometer</label>
                                        <input type="number" name="odometer" class="global_input" value="">
                                    </div>

                                    <div class="form_group">
                                        <label>Total Amount</label>
                                        <input type="text" class="global_input has-value" value="₱ 0.00" disabled required>
                                    </div>
                                </div>

                                <!-- Section 4: Instructions -->
                                <div class="form_section_card full_width_card">
                                    <div class="section_title">Instructions</div>
                                    <div class="form_group delivery_instruct full_width">
                                        <label>Delivery Instructions</label>
                                        <textarea name="remarks" class="global_textarea" rows="3" placeholder="Enter delivery instructions..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Added Item Table now nested inside the form wrapper -->
                            <div class="inhouse_requisition_child_tbl global_display_table_wrapper" style="margin-top: 20px;">
                                <div class="table_action">
                                    <h2 class="h3_repeat">Added Item</h2>
                                    <button type="button" class="global_add_btn">+ Add Item</button>
                                </div>

                                <div class="global_display_table_container">
                                    <table class="ir_add_display_tbl global_display_table">
                                        <thead>
                                            <tr>
                                                <th class="text_center" style="width: 60px;">#</th>
                                                <th>Item Code</th>
                                                <th>Barcode</th>
                                                <th>Item Description</th>
                                                <th>Item No</th>
                                                <th>Concession ID</th>
                                                <th>Unit Price</th>
                                                <th>UOM</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Req Qty</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Form Footer Actions -->
                <div class="form_footer_actions">
                    <button type="submit" form="inhouseForm" name="action" value="save" class="global_btn_save btn-tool-tip ir_btn_save"><span>Save</span></button>
                    <button type="submit" form="inhouseForm" name="action" value="post" class="global_btn_submit btn-tool-tip ir_btn_submit"><span>Post</span></button>
                </div>

            </div>
        </div>
    </div>

    <!-- LOOKUP ITEM MODAL -->
    <div class="global_modal_overlay" id="lookupItemModal">
        <div class="lookup_modal_content">
            <div class="lookup_modal_header">
                <h3>LOOKUP ITEM</h3>
                <button type="button" class="lookup_modal_close" id="btnCloseLookupModal">×</button>
            </div>

            <div class="lookup_modal_body">
                <div class="lookup_search_bar">
                    <input type="text" class="lookup_search_input" placeholder="Search Item Here">

                    <select class="lookup_search_select">
                        <option value="item_description">ITEM DESCRIPTION</option>
                        <option value="item_code">ITEM CODE</option>
                        <option value="barcode">BARCODE</option>
                        <option value="uom">UOM</option>
                    </select>

                    <button type="button" class="lookup_btn_search">SEARCH</button>
                </div>

                <div class="lookup_table_wrapper">
                    <table class="lookup_table">
                        <thead>
                            <tr>
                                <th class="text_center" style="width: 50px;">
                                    <input type="checkbox" id="selectAllLookupItems"> All
                                </th>
                                <th>ITEM CODE</th>
                                <th>BARCODE</th>
                                <th>ITEM DESCRIPTION</th>
                                <th>ITEM NO</th>
                                <th>UOM</th>
                                <th class="text_right">STOCK ONHAND</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

                <div class="lookup_modal_footer_actions">
                    <button type="button" class="global_add_selected_btn" id="btnAddSelected">Add Selected</button>
                </div>

            </div>
        </div>
    </div>

    <?php include $base_path . 'includes/footer.php'; ?>

    <script src="<?php echo $base_path; ?>assets/js/swal.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/access.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/inhouse_requisition_add.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/ph_time.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inhouseForm = document.getElementById('inhouseForm');

            if (inhouseForm) {
                inhouseForm.addEventListener('submit', function(e) {
                    const itemRows = document.querySelectorAll('.ir_add_display_tbl tbody tr');

                    if (itemRows.length === 0) {
                        e.preventDefault(); 

                        Swal.fire({
                            icon: 'warning',
                            title: 'No Items Added',
                            text: 'Add Items First.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    </script>


</body>

</html>