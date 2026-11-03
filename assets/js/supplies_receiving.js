document.addEventListener('DOMContentLoaded', () => {
    const cardContainer = document.getElementById('receivingCardContainer');
    const itemCount = document.querySelector('#receivingItemsCount span');
    const searchInput = document.getElementById('SearchInput');
    const monitoredFilter = document.getElementById('filterMonitoredCategory');
    const descriptionFilter = document.getElementById('filterDescriptionCategory');
    const receiveModal = document.getElementById('receiveItemModal');
    const btnCloseReceive = document.getElementById('btnCloseReceiveModal');
    const receiveQuantityInput = document.getElementById('receiveQuantityInput');
    const postReceiveButton = document.getElementById('btnPostReceive');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let receivingRecords = [];
    let appliedSearch = '';
    let appliedSearchField = descriptionFilter.value;
    let activeReceivingId = null;

    function createInfoColumn(label, value, options = {}) {
        const column = document.createElement('div');
        column.className = options.badge
            ? 'receiving_info_col receiving_badge_box'
            : 'receiving_info_col';

        const labelElement = document.createElement('span');
        labelElement.className = 'receiving_label';
        labelElement.textContent = label;

        const valueElement = document.createElement('span');
        valueElement.className = options.badge
            ? 'receiving_badge_val'
            : options.bold ? 'receiving_value_bold' : 'receiving_value';

        if (options.badge) {
            valueElement.append(document.createTextNode(value ?? '-'));
            const unit = document.createElement('small');
            unit.textContent = ` ${options.unit || ''}`;
            valueElement.appendChild(unit);
        } else {
            valueElement.textContent = value ?? '-';
        }

        column.append(labelElement, valueElement);
        return column;
    }

    function createReceivingCard(record) {
        const card = document.createElement('section');
        card.className = 'receiving_card';

        const header = document.createElement('div');
        header.className = 'receiving_card_header';
        const title = document.createElement('h3');
        title.className = 'receiving_item_title';
        title.textContent = record.oitemdesc;
        const receiveButton = document.createElement('button');
        receiveButton.type = 'button';
        receiveButton.className = 'receiving_btn_receive';
        receiveButton.dataset.recordId = record.otableid;
        receiveButton.textContent = 'RECEIVE';
        header.append(title, receiveButton);

        const body = document.createElement('div');
        body.className = 'receiving_card_body';
        const grid = document.createElement('div');
        grid.className = 'receiving_info_grid';
        grid.append(
            createInfoColumn('ITEM CODE', record.oitemcode),
            createInfoColumn('ITEM NO', record.oitemno),
            createInfoColumn('BARCODE', record.obarcode),
            createInfoColumn('MONITORED BY', record.omonitor_by, { bold: true }),
            createInfoColumn('UNIT', record.ounit),
            createInfoColumn('RECEIVED QTY', record.orecv_qty, { badge: true, unit: record.ounit }),
            createInfoColumn('PLACED QTY', record.oput_qty, { badge: true, unit: record.ounit }),
            createInfoColumn('REFERENCE NO.', record.orefno),
        );

        const footer = document.createElement('div');
        footer.className = 'receiving_card_footer';
        const dateLabel = document.createElement('span');
        dateLabel.className = 'receiving_label';
        dateLabel.textContent = 'CREATED DATE';
        const dateValue = document.createElement('span');
        dateValue.className = 'receiving_date_val';
        dateValue.textContent = record.ocreatedate || '-';
        footer.append(dateLabel, dateValue);
        body.append(grid, footer);
        card.append(header, body);
        return card;
    }

    function renderReceivingRecords() {
        const query = appliedSearch.toLowerCase();
        const monitoredBy = monitoredFilter.value;
        const filteredRecords = receivingRecords.filter(record => {
            const fieldValue = String(record[appliedSearchField] ?? '').toLowerCase();
            return fieldValue.includes(query)
                && (!monitoredBy || record.omonitor_by === monitoredBy);
        });

        if (itemCount) {
            itemCount.textContent = filteredRecords.length;
        }
        if (filteredRecords.length === 0) {
            cardContainer.textContent = receivingRecords.length === 0
                ? 'No receiving records found.'
                : 'No records match the selected filters.';
            return;
        }

        cardContainer.replaceChildren(...filteredRecords.map(createReceivingCard));
    }

    async function loadReceivingRecords() {
        try {
            const response = await fetch(cardContainer.dataset.endpoint, {
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.error || `Request failed (${response.status}).`);
            }
            if (!Array.isArray(result.data)) {
                throw new Error('The server returned invalid receiving records.');
            }

            receivingRecords = result.data;
            const monitoredByValues = [...new Set(receivingRecords.map(record => record.omonitor_by).filter(Boolean))].sort();
            monitoredFilter.replaceChildren(new Option('All Monitored By', ''));
            monitoredByValues.forEach(value => monitoredFilter.add(new Option(value, value)));
            renderReceivingRecords();
        } catch (error) {
            console.error('Error loading receiving records:', error);
            cardContainer.textContent = error.message;
            if (itemCount) {
                itemCount.textContent = '0';
            }
        }
    }

    function setQuantityValue(element, quantity, unit) {
        const unitElement = document.createElement('small');
        unitElement.textContent = ` ${unit || ''}`;
        element.replaceChildren(document.createTextNode(quantity ?? '-'), unitElement);
    }

    function showReceivePostMessage(message, isSuccess) {
        if (!message) {
            return;
        }

        return Swal.fire({
            icon: isSuccess ? 'success' : 'error',
            title: isSuccess ? 'Success' : 'Unable to post',
            text: message,
            confirmButtonColor: isSuccess ? '#218c3c' : '#d33',
        });
    }

    function displayReceivingRecord(record) {
        document.getElementById('modalItemTitle').textContent = record.oitemdesc;
        document.getElementById('modalItemCode').textContent = record.oitemcode;
        document.getElementById('modalItemNo').textContent = record.oitemno;
        document.getElementById('modalBarcode').textContent = record.obarcode;
        document.getElementById('modalMonitored').textContent = record.omonitor_by;
        document.getElementById('modalUnit').textContent = record.ounit;
        setQuantityValue(document.getElementById('modalReceivedQty'), record.orecv_qty, record.ounit);
        setQuantityValue(document.getElementById('modalPlacedQty'), record.oput_qty, record.ounit);
        document.getElementById('modalRefNo').textContent = record.orefno;
        document.getElementById('modalCreatedDate').textContent = record.ocreatedate || '-';
    }

    async function postReceivedQuantity() {
        const quantity = receiveQuantityInput.value.trim();
        if (!activeReceivingId) {
            showReceivePostMessage('Select a receiving record before posting a quantity.', false);
            return;
        }
        if (!/^[0-9]{1,8}$/.test(quantity) || Number(quantity) <= 0) {
            showReceivePostMessage('Enter a quantity greater than zero using digits only.', false);
            receiveQuantityInput.focus();
            return;
        }
        if (!csrfToken) {
            showReceivePostMessage('Unable to verify your session. Refresh the page and try again.', false);
            return;
        }

        postReceiveButton.disabled = true;
        receiveQuantityInput.disabled = true;
        showReceivePostMessage('', true);
        try {
            const response = await fetch(cardContainer.dataset.endpoint, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'post',
                    csrf_token: csrfToken,
                    otableid: String(activeReceivingId),
                    quantity,
                }),
            });
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('The server returned an invalid response.');
            }
            if (!response.ok || !result.success) {
                throw new Error(result.error || `Request failed (${response.status}).`);
            }

            await loadReceivingRecords();
            const updatedRecord = receivingRecords.find(
                record => String(record.otableid) === String(activeReceivingId),
            );
            if (updatedRecord) {
                displayReceivingRecord(updatedRecord);
            }
            receiveQuantityInput.value = '';
            await showReceivePostMessage(result.message, true);
            receiveModal.classList.remove('show');
        } catch (error) {
            console.error('Error posting received quantity:', error);
            showReceivePostMessage(error.message, false);
        } finally {
            postReceiveButton.disabled = false;
            receiveQuantityInput.disabled = false;
        }
    }

    if (cardContainer && searchInput && monitoredFilter && descriptionFilter) {
        searchInput.addEventListener('keydown', event => {
            if (event.key !== 'Enter') {
                return;
            }
            event.preventDefault();
            appliedSearch = searchInput.value.trim();
            appliedSearchField = descriptionFilter.value;
            renderReceivingRecords();
        });
        monitoredFilter.addEventListener('change', renderReceivingRecords);
        cardContainer.addEventListener('click', event => {
            const button = event.target.closest('.receiving_btn_receive');
            if (!button || !cardContainer.contains(button)) {
                return;
            }

            const record = receivingRecords.find(item => String(item.otableid) === button.dataset.recordId);
            if (!record) {
                return;
            }

            activeReceivingId = record.otableid;
            receiveQuantityInput.value = '';
            showReceivePostMessage('', true);
            displayReceivingRecord(record);
            receiveModal.classList.add('show');
        });
        loadReceivingRecords();
    }

    if (postReceiveButton && receiveQuantityInput) {
        postReceiveButton.addEventListener('click', postReceivedQuantity);
    }

    if (btnCloseReceive && receiveModal) {
        btnCloseReceive.addEventListener('click', () => {
            receiveModal.classList.remove('show');
        });
    }

    if (receiveModal) {
        receiveModal.addEventListener('input', event => {
            if (event.target instanceof HTMLInputElement && event.target.matches('[data-numeric-only]')) {
                event.target.value = event.target.value.replace(/\D/g, '');
            }
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === receiveModal) {
            receiveModal.classList.remove('show');
        }
    });

});