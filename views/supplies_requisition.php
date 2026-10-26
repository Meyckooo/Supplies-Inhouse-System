<?php
session_start();
$base_path = "./";

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$moduleId = 2;

if (!hasAccess($moduleId, $isSuperAdmin, $allowedModules)) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ' . $base_path . '403.php');
    exit();
}

$dbConn = getDBConnection();

// Get search filters (using $dbConn matching your database setup)
$searchOption = isset($_GET['searchOption']) ? $_GET['searchOption'] : '';
$searchInput = isset($_GET['searchInput']) ? $_GET['searchInput'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

$whereSQL = "WHERE 1";

// Search condition
if (!empty($searchInput) && !empty($searchOption)) {
    $searchInput = mysqli_real_escape_string($dbConn, $searchInput);
    if (is_numeric($searchInput)) {
        $whereSQL .= " AND $searchOption = '$searchInput'";
    } else {
        $whereSQL .= " AND $searchOption LIKE '%$searchInput%'";
    }
}

// Date range condition
if (!empty($startDate) && !empty($endDate)) {
    $startDateFormatted = date('Y-m-d', strtotime($startDate)) . ' 00:00:00';
    $endDateFormatted = date('Y-m-d', strtotime($endDate)) . ' 23:59:59';

    $startDate = mysqli_real_escape_string($dbConn, $startDateFormatted);
    $endDate = mysqli_real_escape_string($dbConn, $endDateFormatted);

    $whereSQL .= " AND opostdate BETWEEN '$startDate' AND '$endDate'";
}
$query = "SELECT * FROM tbl_supplies_header $whereSQL ORDER BY opostdate DESC";
$result = mysqli_query($dbConn, $query);

$userid = $_SESSION['oUserid'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/media.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="icon" href="<?php echo $base_path; ?>../assets/images/favicon.png">

    <style>
        .background-wrapper {
            position: absolute;
            top: 0;
            right: 10%;
            left: 16%;
            height: 100%;
            width: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .background-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1;
        }

        .table-container {
            position: relative;
            z-index: 1;
            padding: 10px 0;
        }
        
        .col-form-label {
            color: #333;
        }

        .scrollable-table {
            max-height: 320px; 
            overflow-y: auto;
            overflow-x: auto;
        }
        
        .scrollable-table thead th {
            position: sticky;
            top: 0;
            background-color: #212529; 
            color: white;
            z-index: 2;
            white-space: pre;
        }

        /* Status Colors */
        .cancel { background-color: rgb(179, 179, 179) !important; }
        .save { background-color: #ebf89e !important; }
        .approve { background-color: rgb(248, 158, 170) !important; }
        .approved { background-color: #fff !important; }
        .selected-row { background-color: rgb(66, 156, 253) !important; color: #fff !important; }
        
        .crod { white-space: pre; }

        .header1 {
            background-color: #63a6fd;
            color: #fff;
            border-bottom: 2px solid #ffc107;
        }

        /* --- BUTTON STYLES --- */
        .btn-perm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background-color: #408bfc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-perm:hover {
            background-color: #2b70e8;
            color: white;
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-perm img {
            width: 16px;
            height: 16px;
            filter: brightness(0) invert(1);
        }

        .btn-perm.btn-secondary { background-color: #6c757d; }
        .btn-perm.btn-secondary:hover { background-color: #5a6268; }
        .btn-perm.btn-danger { background-color: #dc3545; }
        .btn-perm.btn-danger:hover { background-color: #c82333; }

        .btn-log {
            color: #fff;
            text-decoration: underline;
            margin-left: 5px;
            font-weight: bold;
        }
        .btn-log:hover {
            color: #d1e2ff;
        }
        .marg {
            margin-top: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include $base_path . '../includes/sidebar.php'; ?>
    <div class="main_content">
        <div class="wrapper">
            <div class="header_title">
                <div class="header_info">
                    <h1>Requisition</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="main_content_body">
        <div class="wrapper">
            <div class="main_content_body_con">

                <div class="table-container">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-5 text-start">
                            <button class="btn-perm" data-bs-toggle="modal" data-bs-target="#otherfunct">OTHER FUNCTION</button>
                        </div>
                        <div class="col-md-7 text-end">
                            <a href="requisition.php" class="btn-perm"><img src="images/icons/refresh.svg"/> REFRESH</a>
                            <a href="add" class="btn-perm"><img src="images/icons/add.svg"/> ADD</a>
                            <button id="editButton" class="btn-perm"><img src="images/icons/pen.svg"/> EDIT</button>
                            <button id="viewButton" class="btn-perm"><img src="images/icons/pen.svg"/> VIEW</button>
                            <button id="printButton" class="btn-perm"><img src="images/icons/print.svg"/> PRINT</button>
                            <button class="btn-perm btn-danger" id="cancelButton"><img src="images/icons/cancel.svg"/> CANCEL</button>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form class="marg" id="searchForm" method="GET" action="requisition">
                        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
                        
                        <div class="row g-2 mb-2">
                            <div class="col-sm-8">
                                <input type="search" id="searchInput" value="<?php echo htmlspecialchars($searchInput); ?>" name="searchInput" style="text-transform:uppercase" class="form-control" autocomplete="off" placeholder="SEARCH...">
                            </div>
                            <div class="col-sm-4">
                                <select id="searchOption" name="searchOption" class="form-select">
                                    <option value="otableid" <?php echo ($searchOption == 'otableid') ? 'selected' : ''; ?>>CONTROL NO</option>
                                    <option value="olocation" <?php echo ($searchOption == 'olocation') ? 'selected' : ''; ?>>LOCATION</option>
                                    <option value="odepartment" <?php echo ($searchOption == 'odepartment') ? 'selected' : ''; ?>>DEPARTMENT</option>
                                    <option value="oapprover" <?php echo ($searchOption == 'oapprover') ? 'selected' : ''; ?>>APPROVER</option>
                                    <option value="osection" <?php echo ($searchOption == 'osection') ? 'selected' : ''; ?>>SECTION</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label class="col-form-label fw-bold">FROM:</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo !empty($startDate) ? htmlspecialchars(date('Y-m-d', strtotime($startDate))) : ''; ?>"> 
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label fw-bold">TO:</label>
                            </div>
                            <div class="col-sm-3">
                                <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo !empty($endDate) ? htmlspecialchars(date('Y-m-d', strtotime($endDate))) : ''; ?>"> 
                            </div>
                            <div class="col-sm-2"></div>
                        </div>
                    </form>

                    <!-- Top Table -->
                    <div class="scrollable-table mb-4">
                        <table class="table table-bordered table-hover" style="cursor: default;">
                            <thead class="table-dark">
                                <tr>
                                    <th>CTRL_NO</th>
                                    <th>DATE REQUESTED</th>
                                    <th>DEPARTMENT</th>
                                    <th>SECTION</th>
                                    <th>LOCATION</th>
                                    <th>PURPOSE</th>
                                    <th>REMARKS</th>
                                    <th>DATE NEEDED</th>
                                    <th>REQUESTED BY</th>
                                    <th>APPROVER</th>
                                    <th>APPROVAL DATE</th>
                                    <th>ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    if($result && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            if($row['ostatus'] == 1){
                                                $colorrow = "approve";
                                            } elseif($row['ostatus'] == 2) {
                                                $colorrow = "save";
                                            } elseif ($row['ostatus'] == 3){
                                                $colorrow = "cancel";
                                            } else {
                                                $colorrow = "approved";
                                            }

                                            echo '<tr class="' . $colorrow . '">';
                                            echo '<td class="crod">' . htmlspecialchars($row['otableid'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['opostdate'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['odepartment'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['osection'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['olocation'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['opurpose'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['oremarks'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['odate_need'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['opostby'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['oapprover'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['oapprovedate'] ?? '') . '</td>';
                                            echo '<td class="crod">' . htmlspecialchars($row['opostby_id'] ?? '') . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="12" class="text-center bg-white">No records found</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Table -->
                    <div class="scrollable-table">
                        <table class="table table-bordered table-striped bg-white">
                            <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th>ITEM CODE</th>
                                    <th>ITEM DESCRIPTION</th>
                                    <th>UOM</th>
                                    <th>REQ_QTY</th>
                                    <th>APPROVE_QTY</th>
                                    <th>RELEASED QTY</th>
                                    <th>ITEM NO</th>
                                </tr>
                            </thead>
                            <tbody style="cursor: default;">
                                <?php
                                for ($i = 0; $i < 7; $i++) {
                                    echo '<tr>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '<td class="crod">&nbsp;</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Other Function Modal -->
    <div class="modal fade" id="otherfunct" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header header1 text-white">
                    <h5 class="modal-title">OTHER FUNCTION</h5>
                </div>
                <div class="modal-body text-center p-4">
                    <button class="btn-perm w-100 justify-content-center py-2" data-bs-toggle="modal" data-bs-target="#account" data-bs-dismiss="modal">
                        <img src="images/icons/user.svg"/> MY ACCOUNT
                    </button>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Modal -->
    <div class="modal fade" id="account" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header header1 text-white">
                    <h5 class="modal-title">EDIT MY ACCOUNT</h5>
                </div>
                <div class="modal-body p-4 bg-light">
                    <?php
                        if (isset($_POST['update'])) {
                            $user = $_POST["user"];
                            $fname = $_POST["fname"];
                            $pass = $_POST["pass"];
                            $post = $_POST["post"];

                            $sql_p = "SELECT * FROM tbl_user WHERE oPostcode='$post' AND oUserid != $userid";
                            $res_p = mysqli_query($dbConn, $sql_p);

                            if (mysqli_num_rows($res_p) != 0) {
                                $post_error = "<p class='text-danger mb-0'>Postcode already taken</p>";
                            } else {
                                $query = "UPDATE tbl_user SET oPassword = '{$pass}' WHERE oUserid = $userid";
                                mysqli_query($dbConn, $query);
                                echo "<script> alert('User successfully updated'); window.location='logout.php'; </script>";
                            }
                        }
                        
                        $query = "SELECT * FROM tbl_user WHERE oUserid = $userid ";
                        $view_users = mysqli_query($dbConn, $query);
                        $user = $fname = $post = $pass = $status = "";

                        if($row = mysqli_fetch_assoc($view_users)) {
                            $user = $row['oUsername'];
                            $fname = $row['oFullname'];
                            $post = $row['oPostcode'];
                            $pass = $row['oPassword'];
                            $status = ($row['oActive'] == 1) ? "ACTIVE" : "INACTIVE";
                        }
                    ?>
                    
                    <form action="" method="post" autocomplete="off">
                        <?php if (isset($post_error)) echo $post_error; ?>
                        
                        <div class="mb-3">
                            <label for="user" class="form-label fw-bold">Username</label>
                            <input type="text" name="user" class="form-control" required value="<?php echo htmlspecialchars($user); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="fname" class="form-label fw-bold">Fullname</label>
                            <input type="text" name="fname" class="form-control" required value="<?php echo htmlspecialchars($fname); ?>" readonly>
                        </div>
                    
                        <div class="mb-3">
                            <label for="post" class="form-label fw-bold">Postcode</label>
                            <input type="text" id="randomnum" name="post" class="form-control" readonly value="<?php echo htmlspecialchars($post); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="pass" class="form-label fw-bold">Password</label>
                            <input type="password" name="pass" id="pass" class="form-control" required value="<?php echo htmlspecialchars($pass); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_pass" class="form-label fw-bold">Confirm Password</label>
                            <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <input type="text" name="status" class="form-control" value="<?php echo $status; ?>" readonly>
                        </div>

                        <div class="modal-footer px-0 pb-0 border-0">
                            <button type="submit" name="update" class="btn-perm">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>    
                </div>
            </div>
        </div>
    </div>

    <?php include $base_path . '../includes/footer.php'; ?>

    <script src="../assets/js/sweetalert.js"></script>
    <script src="../assets/js/access.js"></script>
    <script src="../assets/js/pos_viewing.js"></script>
    <script src="../assets/js/sidebar_button.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const topTableRows = document.querySelectorAll('.table-container table:first-of-type tbody tr');
        const bottomTableBody = document.querySelectorAll('.table-container table')[1].querySelector('tbody');
        const EMPTY_ROWS = 7;
        const editButton = document.getElementById('editButton');
        const viewButton = document.getElementById('viewButton');
        const cancelButton = document.getElementById('cancelButton');
        const printButton = document.getElementById('printButton');
        let selectedRow = null;

        topTableRows.forEach(row => {
            row.addEventListener('click', () => {
                topTableRows.forEach(r => r.classList.remove('selected-row'));
                row.classList.add('selected-row');
                selectedRow = row;

                const otableid = row.children[0].textContent.trim();

                fetch(`get_details?parent_id=${otableid}`)
                    .then(res => res.json())
                    .then(data => {
                        bottomTableBody.innerHTML = '';
                       
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach((item, idx) => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td class="item-count">${idx + 1}</td>
                                    <td class="crod">${item.oitemcode}</td>
                                    <td class="crod">${item.oitemdesc}</td>
                                    <td class="crod">${item.ounit}</td>
                                    <td class="crod">${item.oreq_qty}</td>
                                    <td class="crod">${item.oapprove_qty || 0}</td>
                                    <td class="crod">${item.orelease_qty}</td>
                                    <td class="crod">${item.oitemno}</td>
                                `;
                                bottomTableBody.appendChild(tr);
                            });
                            for (let i = data.length; i < EMPTY_ROWS; i++) {
                                bottomTableBody.appendChild(getEmptyRow());
                            }
                        } else {
                            for (let i = 0; i < EMPTY_ROWS; i++) {
                                bottomTableBody.appendChild(getEmptyRow());
                            }
                        }
                    });
            });
        });

        editButton.addEventListener('click', () => {
            if (!selectedRow) {
                Swal.fire({
                    icon: 'warning',
                    title: "No Selection",
                    text: "Please choose a requisition to continue.",
                });
                return;
            }
            if (!selectedRow.classList.contains('save')) {
                Swal.fire({
                    icon: 'error',
                    title: "Can't Edit",
                    text: "Can't Edit. Invalid status.",
                });
                return;
            }
            const otableid = selectedRow.children[0].textContent.trim();
            window.location.href = `edit?id=${otableid}`;
        });

        viewButton.addEventListener('click', () => {
            if (!selectedRow) {
                Swal.fire({
                    icon: 'warning',
                    title: "No Selection",
                    text: "Please choose a requisition to continue.",
                });
                return;
            }
            
            const otableid = selectedRow.children[0].textContent.trim();
            window.location.href = `view?id=${otableid}`;
        });

        cancelButton.addEventListener('click', () => {
            if (!selectedRow) {
                Swal.fire({
                    icon: 'warning',
                    title: "No Selection",
                    text: "Please choose a requisition to continue.",
                });
                return;
            }
            const postby_id = selectedRow.children[11].textContent.trim(); 
            const loggedInUserId = "<?php echo $_SESSION['oUserid'] ?? ''; ?>";
            if (loggedInUserId !== postby_id) {
                Swal.fire({
                    icon: 'warning',
                    title: "Cant Cancel!",
                    text: "Cancellation is not permitted, as you are not the requester.",
                });
                return;
            }

            const otableid = selectedRow.children[0].textContent.trim();

            if (selectedRow.classList.contains('approved')) {
                Swal.fire({
                    icon: 'info',
                    title: "Invalid Action!",
                    text: "Unable to cancel; the item has already been approved.",
                });
                return;
            }
            if (selectedRow.classList.contains('cancel')) {
                Swal.fire({
                    icon: 'info',
                    title: "Previously Cancelled",
                    text: "The requisition has previously been cancelled."
                });
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "Proceeding will permanently cancel the requisition.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('cancel_requisition', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `otableid=${encodeURIComponent(otableid)}`
                    })
                    .then(res => res.text())
                    .then(response => {
                        if (response.trim() === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                text: 'Requisition has been cancelled.'
                            }).then(() => {
                                window.location.href = 'requisition';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: err.toString()
                        });
                    });
                }
            });
        });

        printButton.addEventListener('click', () => {
            if (!selectedRow) {
                Swal.fire({
                    icon: 'warning',
                    title: "No Selection",
                    text: "Please choose a requisition to continue.",
                });
                return;
            }
            
            const otableid = selectedRow.children[0].textContent.trim();
            window.location.href = `print?id=${otableid}`;
        });

        function getEmptyRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
                <td class="crod">&nbsp;</td>
            `;
            return tr;
        }
    });
    </script>

    <script>
    const currentDate = new Date();
    const formattedDate = currentDate.toLocaleString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
    const updatedDateElem = document.getElementById('updated-date');
    if(updatedDateElem) {
        updatedDateElem.innerText = `${formattedDate}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action=""]'); 
        if (!form) return;

        form.addEventListener('submit', function (e) {
            const pass = document.getElementById('pass');
            const confirmPass = document.getElementById('confirm_pass');
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Not Match',
                    text: 'Password and Confirm Password do not match. Please try again.'
                }).then(() => {
                    pass.value = '';
                    confirmPass.value = '';
                    pass.focus();
                });
            }
        });
    });
    </script>
</body>
</html>