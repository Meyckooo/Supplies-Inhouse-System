<?php

/** @var string $base_path */
/** @var bool $isSuperAdmin */
/** @var array $allowedModules */


require_once __DIR__ . '/auth_check.php';


if (!defined('BASE_URL')) {
    define('BASE_URL', '/landing_page/');
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/media.css">
<script src="<?php echo BASE_URL; ?>assets/js/sidebar_button.js"></script>

<div class="sidebar bg-dark-slate text-white flex-shrink-0 p-3 shadow">
    <a href="<?php echo BASE_URL; ?>index.php">
        <div class="pb-3 mb-4 text-center main_logo">
            <img src="<?php echo BASE_URL; ?>assets/images/atlantichardware_logo_with_since1963.png" alt="Logo" class="sidebar-logo mb-2">
        </div>
    </a>

    <ul class="menu-list list-unstyled px-1">

        <!-- Module 1: MY ACCOUNT (Normal Link) -->
        <?php if (hasAccess(1, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2">
                <a href="<?php echo BASE_URL; ?>views/my_account.php" class="my_acc menu-link d-flex align-items-center justify-content-between">
                    <span>My Account</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Module 3: USER ACCOUNTS -->
        <?php if (hasAccess(3, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2">
                <a class="user_acc menu-link d-flex align-items-center justify-content-between" href="<?php echo BASE_URL; ?>views/user_accounts.php"
                 data-subpages="add_user_account.php, edit_user_account.php, user_permission_account.php">
                    User Accounts
                </a>
            </li>
        <?php endif; ?>

        <!-- Module 2: HOME PAGE  -->
        <?php if (hasAccess(2, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2 has-submenu">
                <a href="<?php echo BASE_URL; ?>views/home.php" class="home menu-link d-flex align-items-center justify-content-between">
                    <span>Home</span>
                </a>
                
            </li>
        <?php endif; ?>


        <!-- Module 4: Supplies PAGE  -->
        <?php if (hasAccess(2, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2 has-submenu">
                <a href="javascript:void(0);" class="supplies menu-link d-flex align-items-center justify-content-between">
                    <span>Supplies</span>
                    <span class="submenu-arrow">&#9662;</span>
                </a>

                <ul class="sub-menu list-unstyled ps-3 mt-2">
                    <li class="menu-item mb-1">
                        <a href="<?php echo BASE_URL; ?>views/requisition.php" class="menu-link">Requisition</a>
                    </li>
                    <li class="menu-item mb-1">
                        <a href="<?php echo BASE_URL; ?>views/approval.php" class="menu-link">Approval</a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>


          <!-- Module 5: PURCHASE NON-TRADE PAGE  -->
        <?php if (hasAccess(2, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2 has-submenu">
                <a href="javascript:void(0);" class="my_acc menu-link d-flex align-items-center justify-content-between">
                    <span>Purchase NON-TRADE</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Module 6: INHOUSE PAGE  -->
        <?php if (hasAccess(2, $isSuperAdmin, $allowedModules)): ?>
            <li class="menu-item mb-2 has-submenu">
                <a href="<?php echo BASE_URL; ?>views/inhouse_main.php" class="inhouse menu-link d-flex align-items-center justify-content-between">
                    <span>Inhouse</span>
                    <span class="submenu-arrow">&#9662;</span>
                </a>

                <ul class="sub-menu list-unstyled ps-3 mt-2">
                    <li class="menu-item mb-1">
                        <a href="<?php echo BASE_URL; ?>views/inhouse_requisition.php" class="menu-link">Requisition</a>
                    </li>
                    <li class="menu-item mb-1">
                        <a href="<?php echo BASE_URL; ?>views/inhouse_approval.php" class="menu-link">Approval</a>
                    </li>
                    <!-- Nested Submenu  -->
                    <li class="menu-item mb-1 has-submenu">
                        <a href="<?php echo BASE_URL; ?>views/supplies_rel_rec.php" class="menu-link d-flex align-items-center justify-content-between">
                            <span>Supplies REL. &amp; REC.</span>
                            <span class="submenu-arrow">&#9662;</span>
                        </a>
                        <ul class="sub-menu list-unstyled ps-3 mt-2">
                            <li class="menu-item mb-1">
                                <a href="<?php echo BASE_URL; ?>views/releasing.php" class="menu-link">Releasing</a>
                            </li>
                            <li class="menu-item mb-1">
                                <a href="<?php echo BASE_URL; ?>views/receiving.php" class="menu-link">Receiving</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

        <li class="menu-item mb-2">
            <a href="<?php echo BASE_URL; ?>logout.php" class="sidebar_logout menu-link d-flex align-items-center justify-content-between">
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>