const purposeSelect = document.getElementById('purposeSelect');
    const subGroups = document.querySelectorAll('.sub_dropdown_group');

    if (purposeSelect) {
        purposeSelect.addEventListener('change', (e) => {
            const val = e.target.value;
            
            // Hide all sub-dropdowns first
            subGroups.forEach(group => group.style.display = 'none');

            // Show specific sub-dropdown based on selection
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