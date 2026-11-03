document.addEventListener('DOMContentLoaded', () => {
    // Receive Modal
    const receiveModal = document.getElementById('receiveItemModal');
    const receiveButtons = document.querySelectorAll('.receiving_btn_receive');
    const btnCloseReceive = document.getElementById('btnCloseReceiveModal');

    receiveButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (receiveModal) receiveModal.classList.add('show');
        });
    });

    if (btnCloseReceive && receiveModal) {
        btnCloseReceive.addEventListener('click', () => {
            receiveModal.classList.remove('show');
        });
    }

    // --- ADD LOCATOR SUB-MODAL LOGIC ---
    const addLocatorModal = document.getElementById('addLocatorSubModal');
    const openAddLocatorBtn = document.getElementById('openAddLocatorModalBtn');
    const closeAddLocatorBtn = document.getElementById('btnCloseAddLocatorModal');
    const submitNewLocatorBtn = document.getElementById('btnSubmitNewLocator');

    if (openAddLocatorBtn && addLocatorModal) {
        openAddLocatorBtn.addEventListener('click', () => {
            addLocatorModal.classList.add('show');
        });
    }

    if (closeAddLocatorBtn && addLocatorModal) {
        closeAddLocatorBtn.addEventListener('click', () => {
            addLocatorModal.classList.remove('show');
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === addLocatorModal) {
            addLocatorModal.classList.remove('show');
        }
        if (e.target === receiveModal) {
            receiveModal.classList.remove('show');
        }
    });

    // Handle adding new locator
    if (submitNewLocatorBtn) {
        submitNewLocatorBtn.addEventListener('click', () => {
            const locatorVal = document.getElementById('inputLocatorName').value.trim();
            const rackVal = document.getElementById('inputRackName').value.trim();
            const qtyVal = document.getElementById('inputInitialQty').value.trim();

            if (!locatorVal || !rackVal || !qtyVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Fields',
                    text: 'Please Fill All The Fields.',
                    confirmButtonColor: '#218c3c'
                });
                return;
            }

            const container = document.getElementById('locatorsListContainer');
            const countSpan = document.getElementById('locatorCount');

            // Create new locator row card HTML
            const newRow = document.createElement('div');
            newRow.className = 'locator_row_card';
            newRow.innerHTML = `
                <div class="loc_info">
                    <span class="receiving_label">LOCATOR</span>
                    <span class="loc_val">${locatorVal}</span>
                </div>
                <div class="loc_info">
                    <span class="receiving_label">RACK</span>
                    <span class="loc_val">${rackVal}</span>
                </div>
                <div class="loc_qty_badge">
                    <span class="receiving_label">QUANTITY</span>
                    <span class="receiving_badge_val">${parseFloat(qtyVal).toFixed(2)} <small>PCS</small></span>
                </div>
                <div class="loc_action_box">
                    <input type="text" class="global_input" placeholder="ENTER QUANTITY">
                    <button type="button" class="btn_put_locator">PUT</button>
                    <button type="button" class="btn_remove_locator">Remove</button>
                </div>
            `;

            container.appendChild(newRow);

            // Update locator count
            updateLocatorCount();

            // Clear inputs and close sub-modal
            document.getElementById('inputLocatorName').value = '';
            document.getElementById('inputRackName').value = '';
            document.getElementById('inputInitialQty').value = '';
            addLocatorModal.classList.remove('show');
        });
    }

    // Event Delegation for Remove Buttons
    document.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('btn_remove_locator')) {
            const rowCard = e.target.closest('.locator_row_card');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to remove this locator?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e82929',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    rowCard.remove();
                    updateLocatorCount();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Successfully Removed',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });

    function updateLocatorCount() {
        const container = document.getElementById('locatorsListContainer');
        const countSpan = document.getElementById('locatorCount');
        if (container && countSpan) {
            const currentCount = container.querySelectorAll('.locator_row_card').length;
            countSpan.textContent = currentCount;
        }
    }
});