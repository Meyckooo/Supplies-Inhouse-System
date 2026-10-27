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
    <title>In-House Requisition</title>
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
                    <h1>In-House Requisition</h1>
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
                        <h3>Add Inhouse Requisition</h3>
                    </div>

                    <div class="form_body_content">
                        <form class="global_form_grid_sections">

                            <!-- Section 1: General Details -->
                            <div class="form_section_card">
                                <div class="section_title">General Details</div>

                                <div class="form_group">
                                    <label>Company <span class="required">*</span></label>
                                    <input type="text" class="global_input" placeholder="Enter company name" required>
                                </div>

                                <div class="form_group">
                                    <label>Location <span class="required">*</span></label>
                                    <select class="global_select" required>
                                        <option value="" disabled selected>Select Location</option>
                                        <option value="cebu">Cebu City</option>
                                        <option value="mandaue">Mandaue Hub</option>
                                        <option value="banawa">Banawa Branch</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Department <span class="required">*</span></label>
                                    <select class="global_select" required>
                                        <option value="" disabled selected>Select Department</option>
                                        <option value="it">IT Department</option>
                                        <option value="accounting">Accounting</option>
                                        <option value="operations">Operations</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Section <span class="required">*</span></label>
                                    <select class="global_select" required>
                                        <option value="" disabled selected>Select Section</option>
                                        <option value="software">Software Dev</option>
                                        <option value="finance">Finance</option>
                                        <option value="logistics">Logistics</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Section 2: Purpose & Operations -->
                            <div class="form_section_card">
                                <div class="section_title">Purpose & Operations</div>

                                <div class="form_group">
                                    <label>Mode <span class="required">*</span></label>
                                    <input type="text" class="global_input has-value" value="Inhouse Standard" readonly required>
                                </div>

                                <div class="form_group">
                                    <label>Purpose <span class="required">*</span></label>
                                    <select class="global_select" id="purposeSelect" required>
                                        <option value="" disabled selected>Select Purpose</option>
                                        <option value="office_admin">Office & Admin</option>
                                        <option value="handling_storage">Handling & Storage Supplies</option>
                                        <option value="other_operational">Other Operational Supplies</option>
                                        <option value="medical">Medical Supplies</option>
                                        <option value="selling_marketing">Selling & Marketing</option>
                                        <option value="facility_sanitation">Facility Sanitation/ Upkeep & Janitorial</option>
                                        <option value="production">Production</option>
                                        <option value="facilities_building">Facilities & Building</option>
                                        <option value="equipment_machinery">Equipment & Machinery</option>
                                        <option value="vehicle_fleet">Vehicle & Fleet</option>
                                        <option value="it_hardware">IT & Hardware</option>
                                        <option value="safety_ppe">Safety & PPE (Personal Protective Equipment)</option>
                                    </select>
                                </div>

                                <!-- Subdropdowns -->
                                <div class="form_group sub_dropdown_group" id="sub_selling_marketing" style="display:none;">
                                    <label>Selling & Marketing <span class="required">*</span></label>
                                    <select class="global_select">
                                        <option value="" disabled selected>Select</option>
                                        <option value="cashiering">Cashiering / POS</option>
                                        <option value="selling_fulfillment">Selling & Fulfillment</option>
                                        <option value="promotional">Promotional & Display</option>
                                        <option value="diy">DIY</option>
                                        <option value="counter">Counter</option>
                                    </select>
                                </div>

                                <div class="form_group sub_dropdown_group" id="sub_production" style="display:none;">
                                    <label>Production <span class="required">*</span></label>
                                    <select class="global_select">
                                        <option value="" disabled selected>Select</option>
                                        <option value="stickering">Stickering/ Labeling</option>
                                        <option value="packaging">Production Packaging</option>
                                        <option value="others">Others</option>
                                    </select>
                                </div>

                                <div class="form_group sub_dropdown_group" id="sub_vehicle_fleet" style="display:none;">
                                    <label>Vehicle & Fleet <span class="required">*</span></label>
                                    <select class="global_select">
                                        <option value="" disabled selected>Select</option>
                                        <option value="repairs">Repairs</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="project">Project</option>
                                    </select>
                                </div>

                                <div class="form_group sub_dropdown_group" id="sub_it_hardware" style="display:none;">
                                    <label>IT & Hardware <span class="required">*</span></label>
                                    <select class="global_select">
                                        <option value="" disabled selected>Select</option>
                                        <option value="repairs">Repairs</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="project">Project</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Approver <span class="required">*</span></label>
                                    <select class="global_select" required>
                                        <option value="" disabled selected>Select Approver</option>
                                        <option value="manager_a">Manager A</option>
                                        <option value="director_b">Director B</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Posted Date <span class="required">*</span></label>
                                    <input type="date" class="global_input" required>
                                </div>
                            </div>

                            <!-- Section 3: Additional Details -->
                            <div class="form_section_card">
                                <div class="section_title">Additional Details</div>

                                <div class="form_group">
                                    <label>Requested By <span class="required">*</span></label>
                                    <input type="text" class="global_input has-value" value="Current User (Admin)" readonly required>
                                </div>

                                <div class="form_group">
                                    <label>Priority Levels <span class="required">*</span></label>
                                    <select class="global_select priority_select" required>
                                        <option value="" disabled selected>Select Priority</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="high">High</option>
                                        <option value="standard">Standard</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Plate Number / # <span class="required">*</span></label>
                                    <select class="global_select" required>
                                        <option value="" disabled selected>Select Plate No.</option>
                                        <option value="plate_1">ABC-1234</option>
                                        <option value="plate_2">XYZ-5678</option>
                                    </select>
                                </div>

                                <div class="form_group">
                                    <label>Odometer <span class="required">*</span></label>
                                    <input type="text" class="global_input has-value" value="45,200 km" readonly required>
                                </div>

                                <div class="form_group">
                                    <label>Total Amount <span class="required">*</span></label>
                                    <input type="text" class="global_input has-value" value="₱ 0.00" readonly required>
                                </div>
                            </div>

                            <!-- Section 4: Instructions -->
                            <div class="form_section_card full_width_card">
                                <div class="section_title">Instructions</div>
                                <div class="form_group delivery_instruct full_width">
                                    <label>Delivery Instructions <span class="required">*</span></label>
                                    <textarea class="global_textarea" rows="3" placeholder="Enter delivery instructions..." required></textarea>
                                </div>
                            </div>

                            <!-- Form Footer Actions -->
                            <div class="form_footer_actions full_width_card">
                                <button type="submit" class="global_btn_save">Save</button>
                                <button type="submit" class="global_btn_submit">Post</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <?php include $base_path . '../includes/footer.php'; ?>


    <script src="../assets/js/swal.js"></script>
    <script src="../assets/js/access.js"></script>
    <script src="../assets/js/inhouse_requisition_add.js"></script>
    <script src="../assets/js/sidebar_button.js"></script>
</body>

</html>