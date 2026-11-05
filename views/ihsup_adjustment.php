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
//         Enforce Main Module Access                      //
//==========================================================*/
if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$currentUser = $_SESSION['user_name'] ?? '';
$dbConn = getDBConnection();
$inhouseController = new InhouseController($dbConn);

// Get otableid from URL safely
$otableId = $_GET['otableid'] ?? $_GET['id'] ?? null;
$requisition = null;
$requisitionDetails = [];

if ($otableId) {
    $requisition = $inhouseController->getHeaderById($otableId);
    $requisitionDetails = $inhouseController->getInhouseDetails($otableId);
}

$locations = $inhouseController->getCompanyLocation();
$defaultCompany = !empty($locations) ? ($locations[0]['ocompany'] ?? $locations[0]['company'] ?? '') : '';
$defaultLocation = !empty($locations) ? ($locations[0]['olocation'] ?? $locations[0]['location'] ?? '') : '';

$deptRecords = $inhouseController->getDepartments();
$departments = array_unique(array_column($deptRecords, 'odepartment_name'));
sort($departments);

$approvers = $inhouseController->getApprovers();
$plateNumbers = $inhouseController->getPlateNumbers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-House & Supplies Requisition</title>
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
                    <h1>Adjustments</h1>
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
                    <div class="form_body_content">
                        <form id="inhouseForm" action="../controller/inhouse_controller.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="otableid" value="<?php echo htmlspecialchars($otableId ?? ''); ?>">
                            <!-- Added Item Table -->
                            <div class="inhouse_requisition_child_tbl global_display_table_wrapper" style="margin-top: 20px;">
                                <div class="table_action">
                                    <h2 class="h3_repeat">Edit Added Item</h2>
                                    <!-- <button type="button" class="global_add_btn">+ Add Item</button> -->
                                </div>

                                <div class="global_display_table_container">
                                    <table class="ir_add_display_tbl global_display_table">
                                        <thead>
                                            <tr>
                                                <th class="text_center" style="width: 60px;">#</th>
                                                <th>created date</th>
                                                <th>ctrl No</th>
                                                <th>type</th>
                                                <th>ref no</th>
                                                <th>remarks</th>
                                                <th>created by</th>
                                                <th>modified date</th>
                                                <th>reason</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($requisitionDetails)): ?>
                                                <?php foreach ($requisitionDetails as $index => $item): ?>
                                                    <tr>
                                                        <td class="text_center row_number"><?php echo $index + 1; ?></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['oitemcode'] ?? ''); ?>
                                                            <!-- CRITICAL: Pass the existing row's primary key (otableid) as detail_id -->
                                                            <input type="hidden" name="items[<?php echo $index; ?>][detail_id]" value="<?php echo htmlspecialchars($item['otableid'] ?? ''); ?>">
                                                            <input type="hidden" name="items[<?php echo $index; ?>][item_code]" value="<?php echo htmlspecialchars($item['oitemcode'] ?? ''); ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['obarcode'] ?? ''); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][bar_code]" value="<?php echo htmlspecialchars($item['obarcode'] ?? ''); ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['oitemdesc'] ?? ''); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][item_desc]" value="<?php echo htmlspecialchars($item['oitemdesc'] ?? ''); ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['oitemno'] ?? ''); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][item_no]" value="<?php echo htmlspecialchars($item['oitemno'] ?? ''); ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['ocon_id'] ?? ''); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][consign_id]" value="<?php echo htmlspecialchars($item['ocon_id'] ?? ''); ?>">
                                                        </td>
                                                        <td class="text_right item_price">
                                                            <?php
                                                            $unitPrice = $item['ounitprice'] ?? ($item['ounit_price'] ?? '0.00');
                                                            echo htmlspecialchars($unitPrice);
                                                            ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][unit_price]" value="<?php echo htmlspecialchars($unitPrice); ?>">
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($item['ounit'] ?? ''); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][oum]" value="<?php echo htmlspecialchars($item['ounit'] ?? ''); ?>">
                                                        </td>
                                                        <td>
                                                            <select name="items[<?php echo $index; ?>][type]" class="global_select">
                                                                <option value="SUPPLIES" <?php echo (($item['otype'] ?? '') == 1 || ($item['otype'] ?? '') === 'SUPPLIES') ? 'selected' : ''; ?>>SUPPLIES</option>
                                                                <option value="INHOUSE" <?php echo (($item['otype'] ?? '') == 2 || ($item['otype'] ?? '') === 'INHOUSE') ? 'selected' : ''; ?>>INHOUSE</option>
                                                                <option value="PRNT" <?php echo (($item['otype'] ?? '') == 3 || ($item['otype'] ?? '') === 'PRNT') ? 'selected' : ''; ?>>PRNT</option>
                                                            </select>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['ostatus'] ?? 'SUPPLIES'); ?></td>
                                                        <td class="text_right">
                                                            <input type="number" name="items[<?php echo $index; ?>][qty]" class="global_input item_qty" value="<?php echo htmlspecialchars($item['oreq_qty'] ?? 1); ?>" min="1" style="width: 80px;" required>
                                                        </td>
                                                        <td class="text_center">
                                                            <button type="button" class="btn btn-danger btn-sm remove_item_btn" style="padding: 2px 8px; font-size: 12px;">×</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php if (empty($requisitionDetails)): ?>
                                                <tr class="no_items_row">
                                                    <td colspan="12" class="text_center">No items added yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
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
                            <!-- Dynamically populated via JS -->
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
    <script src="<?php echo $base_path; ?>assets/js/inhouse_requisition_edit.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/ph_time.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inhouseForm = document.getElementById('inhouseForm');

            if (inhouseForm) {
                inhouseForm.addEventListener('submit', function(e) {
                    const itemRows = document.querySelectorAll('.ir_add_display_tbl tbody tr:not(.no_items_row)');

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

            // --- AUTO-TRIGGER SWEETALERT FOR CONTROLLER SESSION MESSAGES ---
            <?php if (isset($_SESSION['success_message'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES); ?>',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: '<?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES); ?>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>