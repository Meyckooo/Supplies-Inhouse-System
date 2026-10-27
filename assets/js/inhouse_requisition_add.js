document.addEventListener('DOMContentLoaded', () => {
    const purposeSelect = document.getElementById('purposeSelect');
    const subGroups = document.querySelectorAll('.sub_dropdown_group');

    if (purposeSelect) {
        purposeSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            subGroups.forEach(group => group.style.display = 'none');

            if (val === 'selling_marketing') {
                document.getElementById('sub_selling_marketing').style.display = 'flex';
            } else if (val === 'production') {
                document.getElementById('sub_production').style.display = 'flex';
            } else if (val === 'vehicle_fleet') {
                document.getElementById('sub_vehicle_fleet').style.display = 'flex';
            } else if (val === 'it_hardware') {
                document.getElementById('sub_it_hardware').style.display = 'flex';
            }
        });
    }

    // --- LOOKUP ITEM MODAL JS ---
    const lookupItemModal = document.getElementById('lookupItemModal');
    const btnOpenLookupModal = document.querySelector('.global_add_btn');
    const btnCloseLookupModal = document.getElementById('btnCloseLookupModal');
    const selectAllCheckbox = document.getElementById('selectAllLookupItems');

    // Open Modal when "+ Add Item" button is clicked
    if (btnOpenLookupModal && lookupItemModal) {
        btnOpenLookupModal.addEventListener('click', () => {
            lookupItemModal.classList.add('show');
        });
    }

    // Close Modal via Close (X) button
    if (btnCloseLookupModal && lookupItemModal) {
        btnCloseLookupModal.addEventListener('click', () => {
            lookupItemModal.classList.remove('show');
        });
    }

    // Close Modal when clicking outside the modal box
    window.addEventListener('click', (e) => {
        if (e.target === lookupItemModal) {
            lookupItemModal.classList.remove('show');
        }
    });

    // Toggle "Select All" Checkboxes inside Lookup Table
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (e) => {
            const itemCheckboxes = document.querySelectorAll('.lookup_item_checkbox');
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });
    }
});