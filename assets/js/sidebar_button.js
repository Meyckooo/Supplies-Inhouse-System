document.addEventListener('DOMContentLoaded', function() {
    /*========================================================= //
    //                 Sidebar Toggle Logic                     //      
    //=========================================================*/
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        if (sidebar.dataset.sidebarInitialized === 'true') {
            return;
        }
        sidebar.dataset.sidebarInitialized = 'true';
        let toggleBtn = sidebar.querySelector('.sidebar-toggle');
        if (!toggleBtn) {
            toggleBtn = document.createElement('button');
            toggleBtn.className = 'sidebar-toggle';
            toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
            toggleBtn.innerHTML = '<span class="toggle-arrow">&#10094;</span>';
            sidebar.insertBefore(toggleBtn, sidebar.firstChild);
        }

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }

    /* ========================================================================== //
    //  Submenu Arrow Click Logic (Event Delegation for flexible open/close)      //
    // ========================================================================== */
    const menuList = document.querySelector('.menu-list');
    if (menuList) {
        menuList.addEventListener('click', function(e) {
            const arrow = e.target.closest('.submenu-arrow');
            if (!arrow) return;

            e.preventDefault();  
            e.stopPropagation(); 
            
            const parentLi = arrow.closest('.has-submenu');
            if (parentLi) {
                parentLi.classList.toggle('open');
            }
        });
    }

    /* ========================================================================== //
    //  Parent-Child Page Mapper                                                //
    // ========================================================================== */
    const pageRelations = {
        'user_accounts.php': [
            'add_user_account.php', 
            'edit_user_account.php', 
            'user_permission_account.php'
        ],
    };

    const currentPath = window.location.pathname;
    const rawFilename = currentPath.split('/').pop(); 
    const currentPage = rawFilename.split('?')[0].toLowerCase() || 'index.php';

    const menuItems = document.querySelectorAll('.menu-list li');
    
    /*========================================================================== //
    //  Function to Set Active Item and Open Parent Submenus                     //
    //==========================================================================*/
    function setActiveItem(item) {
        item.classList.add('current_page_item');
        
        let parentSubmenuLi = item.closest('.has-submenu');
        while (parentSubmenuLi) {
            parentSubmenuLi.classList.add('open');
            parentSubmenuLi = parentSubmenuLi.parentElement.closest('.has-submenu');
        }
    }

    // Clear all active states first
    menuItems.forEach(item => {
        item.classList.remove('current_page_item');
    });

    // Evaluate each menu item
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (!link) return;

        const href = link.getAttribute('href') || '';
        if (href === 'javascript:void(0);' || href === '#') return;

        const hrefFilename = href.split('/').pop();
        const hrefPage = hrefFilename.split('?')[0].toLowerCase();

        // Condition Exact Match
        const isExactMatch = (hrefPage === currentPage);

        // Condition Sub-page Match
        const subPages = pageRelations[hrefPage] || [];
        const isSubpageMatch = subPages.map(p => p.toLowerCase()).includes(currentPage);

        // Condition Root/Index Match
        const isIndexMatch = (currentPage === 'index.php' || currentPage === '') && hrefPage === 'index.php';

        if (isExactMatch || isSubpageMatch || isIndexMatch) {
            setActiveItem(item);
        }
    });
});