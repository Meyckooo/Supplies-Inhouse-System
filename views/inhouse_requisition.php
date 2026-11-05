<?php
session_start();
$base_path = "../";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controller/inhouse_controller.php';

$moduleId = 2;

if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$dbConn = getDBConnection();
$inhouseController = new InhouseController($dbConn);
$headers = $inhouseController->getInhouseHeader();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In-House & Supplies Requisition</title>
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/media.css">
    <link rel="stylesheet" href="<?php echo $base_path ?>assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>/assets/images/favicon.png">
    <style>
        .global_table tbody tr {
            cursor: pointer;
        }

        .global_table tbody tr.selected_row {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }
    </style>
</head>

<body>

    <?php include $base_path . 'includes/sidebar.php'; ?>

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
                                    <option value="ctrl_no" selected>Control No</option>
                                    <option value="location">Location</option>
                                    <option value="department">Department</option>
                                    <option value="approver">Approver</option>
                                    <option value="section">Section</option>
                                </select>
                            </div>

                            <!-- <div class="date_picker_container">
                                <button type="button" class="date_picker_trigger" id="datePickerBtn">
                                    <figure><img src="<!?php echo $base_path ?>assets/images/icon/calendar.png" alt="calendar"></figure>
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

                                    <div class="calendar_grid" id="calendarGrid"> -->
                            <!-- Rendered Dynamically via JS -->
                            <!-- </div>

                                    <div class="calendar_footer">
                                        <button type="button" class="btn_clear" id="btnClearDate">Clear</button>
                                    </div>
                                </div>
                            </div> -->
                        </div>

                        <div class="filter_group right">
                            <a href="inhouse_requisition_add.php" class="global_add_btn">+ Add</a>
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
                                    <th>Status</th>
                                    <th class="text_center global-floating-action" style="width: 370px; min-width: 370px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($headers)): ?>
                                    <?php foreach ($headers as $row): ?>
                                        <?php
                                        $ostatus = (int)($row['ostatus'] ?? 0);
                                        $isCancelled = ($ostatus === 3);
                                        $rowClass = $isCancelled ? 'row_cancelled' : '';
                                        ?>
                                        <tr data-header-id="<?php echo htmlspecialchars($row['otableid']); ?>" class="<?php echo $rowClass; ?>">
                                            <td><?php echo str_pad($row['otableid'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($row['odate_need'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['odepartment'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['osection'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['olocation'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['opurpose'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['opostby'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['oapprover_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $ostatus = (int)($row['ostatus'] ?? 0);
                                                switch ($ostatus) {
                                                    case 1:
                                                        echo '<span class="global_status status_cancelled">To Approved</span>';
                                                        break;
                                                    case 2:
                                                        echo '<span class="global_status status_pending">Saved</span>';
                                                        break;
                                                    case 3:
                                                        echo '<span class="global_status status_deleted">Cancelled</span>';
                                                        break;
                                                    case 4:
                                                        echo '<span class="global_status status_save_approved">Saved Approved</span>';
                                                        break;
                                                    case 5:
                                                        echo '<span class="global_status status_approved">Approved</span>';
                                                        break;
                                                    case 0:
                                                    default:
                                                        echo '<span class="global_status">None</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td class="action_cell global-floating-action">
                                                <!-- Edit Button -->
                                                <a href="inhouse_requisition_edit.php?otableid=<?php echo htmlspecialchars($row['otableid']); ?>"
                                                    class="btn-tool-tip btn_edit <?php echo $isCancelled ? 'disabled_action' : ''; ?>"
                                                    data-ostatus="<?php echo htmlspecialchars($row['ostatus']); ?>">
                                                    <span>Edit</span>
                                                </a>

                                                <!-- View Button -->
                                                <a href="inhouse_requisition_view.php?otableid=<?php echo htmlspecialchars($row['otableid']); ?>" class="btn-tool-tip btn_view"><span>View</span></a>

                                                <!-- Print Button -->
                                                <a href="#" data-otableid="<?php echo htmlspecialchars($row['otableid']); ?>"
                                                    class="btn-tool-tip btn_print btn_direct_print <?php echo $isCancelled ? 'disabled_action' : ''; ?>">
                                                    <span>Print</span>
                                                </a>

                                                <!-- Cancel Form & Trigger Button -->
                                                <form action="../controller/inhouse_controller.php" method="POST" class="cancel-form" style="display:inline;">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="otableid" value="<?php echo htmlspecialchars($row['otableid']); ?>">
                                                    <button type="button"
                                                        class="btn-tool-tip btn_cancel btn_cancel_request <?php echo $isCancelled ? 'disabled_action' : ''; ?>"
                                                        data-ostatus="<?php echo htmlspecialchars($row['ostatus']); ?>"
                                                        <?php echo $isCancelled ? 'disabled' : ''; ?>>
                                                        <span>Cancel</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text_center">No requisition records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                </div>

                <!-- CHILD DETAILS TABLE AREA -->
                <div class="inhouse_requisition_child_tbl global_display_table_wrapper" style="margin-top: 20px;">
                    <div class="global_display_table_container">
                        <table class="global_display_table" id="detailsTable">
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
                                    <td colspan="8" class="text_center">Click a requisition row above to view items.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include $base_path . 'includes/footer.php'; ?>

    <script src="<?php echo $base_path ?>assets/js/swal.js"></script>
    <script src="<?php echo $base_path ?>assets/js/access.js"></script>
    <script src="<?php echo $base_path ?>assets/js/inhouse_requisition.js"></script>
    <script src="<?php echo $base_path ?>assets/js/sidebar_button.js"></script>
    <script src="<?php echo $base_path ?>assets/js/ph_time.js"></script>
    <script src="<?php echo $base_path ?>assets/js/print.js"></script>
    <script>
        window.currentUsername = "<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'ADMIN', ENT_QUOTES); ?>";
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".btn_edit").forEach(button => {
                button.addEventListener("click", function(e) {
                    const ostatus = parseInt(this.getAttribute("data-ostatus") || 0);

                    if (ostatus !== 2) {
                        e.preventDefault();
                        let message = 'Already Posted';
                        if (ostatus === 3) {
                            message = 'This requisition is cancelled and cannot be edited.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Action Denied',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            document.querySelectorAll(".btn_cancel_request").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    const ostatus = parseInt(this.getAttribute("data-ostatus") || 0);

                    if (ostatus !== 2) {
                        let message = 'Already Posted';
                        if (ostatus === 3) {
                            message = 'This requisition is already cancelled.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Action Denied',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    const form = this.closest("form");

                    Swal.fire({
                            title: 'Cancel Requisition',
                            text: "Please provide a reason for canceling this requisition.",
                            icon: 'warning',
                            showCancelButton: true,
                            input: 'textarea',
                            inputPlaceholder: 'Enter reason for cancellation',
                            inputValidator: (value) => {
                                if (!value) {
                                    return 'You need to enter a reason for cancellation';
                                }
                            },
                            reverseButtons: true,
                            cancelButtonColor: '#8b8e91',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Confirm',
                            cancelButtonText: 'Cancel'
                        })
                        .then((result) => {
                            if (result.isConfirmed) {
                                const cancelReason = result.value;

                                let reasonInput = form.querySelector('input[name="cancel_reason"]');
                                if (!reasonInput) {
                                    reasonInput = document.createElement('input');
                                    reasonInput.type = 'hidden';
                                    reasonInput.name = 'cancel_reason';
                                    form.appendChild(reasonInput);
                                }
                                reasonInput.value = cancelReason;

                                form.submit();
                            }
                        });
                });
            });

            // Handle session success/error alerts after redirect
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
                    title: 'Action Failed',
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