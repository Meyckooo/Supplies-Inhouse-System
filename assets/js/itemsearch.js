document.addEventListener("DOMContentLoaded", function () {
    // --- Elements ---
    const searchQuery = document.getElementById("searchQuery");
    const searchButton = document.getElementById("searchButton");
    const searchOption = document.getElementById("searchOption");
    const searchResults = document.getElementById("searchResults");
    const selectAllCheckbox = document.getElementById("selectAll");
    const addSelectedItemsButton = document.getElementById("addSelectedItems");
    const itemsTableBody = document.getElementById("itemsTableBody");

    const searchQueryAdd = document.getElementById("searchQueryAdd");
    const searchButtonAdd = document.getElementById("searchButtonAdd");
    const searchResultsAdd = document.getElementById("searchResultsAdd");

    let selectedItems = new Map();

    function sanitizeDesc(desc) {
        return desc.replace(/['"]/g, "#")
                   .replace(/ "/g, "#")
                   .replace(/" /g, "#")
                   .replace(/' /g, "#")
                   .replace(/ '/g, "#");
    }

    searchQuery.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchButton.click();
        }
    });

    searchButton.addEventListener("click", function () {
        const query = searchQuery.value.trim().toLowerCase();
        const option = searchOption.value;
        const url = `lookup?searchOption=${option}&searchQuery=${query}`;

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                searchResults.innerHTML = "";

                if (data.length === 0) {
                    searchResults.innerHTML = '<tr><td colspan="9">No results found.</td></tr>';
                    return;
                }

                data.forEach((item) => {
                    item.oitemdesc = sanitizeDesc(item.oitemdesc);
                    const isChecked = selectedItems.has(item.oitemcode) ? "checked" : "";
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td><input type="checkbox" class="itemCheckbox" data-item='${JSON.stringify(item)}' ${isChecked}></td>
                        <td>${item.oitemcode}</td>
                        <td>${item.obarcode}</td>
                        <td>${item.oitemdesc}</td>
                        <td>${item.oitemno}</td>
                        <td>${item.ounit}</td>
                        <td>${item.ostore}</td>
                    `;
                    searchResults.appendChild(row);
                });

                attachCheckboxListeners();
            })
            .catch((error) => {
                console.error("Error fetching lookup ", error);
                alert("An error occurred during the search. Please try again.");
            });
    });

    function attachCheckboxListeners() {
        document.querySelectorAll(".itemCheckbox").forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                const itemData = JSON.parse(this.dataset.item);
                if (this.checked) {
                    selectedItems.set(itemData.oitemcode, itemData);
                } else {
                    selectedItems.delete(itemData.oitemcode);
                }
            });
        });
    }

    selectAllCheckbox.addEventListener("change", function () {
        const checked = this.checked;
        document.querySelectorAll(".itemCheckbox").forEach((checkbox) => {
            checkbox.checked = checked;
            const itemData = JSON.parse(checkbox.dataset.item);
            if (checked) {
                selectedItems.set(itemData.oitemcode, itemData);
            } else {
                selectedItems.delete(itemData.oitemcode);
            }
        });
    });

addSelectedItemsButton.addEventListener("click", function () {
    const existingItems = new Set();

    // Check already added items
    document.querySelectorAll('#itemsTableBody input[name="items[]"]').forEach((input) => {
        const item = JSON.parse(input.value);
        existingItems.add(item.oitemcode);
    });

    selectedItems.forEach((item, itemCode) => {
        if (!existingItems.has(itemCode)) {
            existingItems.add(itemCode);
            item.oitemdesc = sanitizeDesc(item.oitemdesc);
            const rowCount = document.querySelectorAll("#itemsTableBody tr").length + 1;
            const row = itemsTableBody.insertRow();
            row.innerHTML = `
                <td class="item-count">${rowCount}</td>
                <input type="hidden" name="items[]" value='${JSON.stringify(item)}'>
                <td>${item.oitemcode}</td>
                <td>${item.obarcode}</td>
                <td>${item.oitemdesc}</td>
                <td>${item.oitemno}</td>
                <td>${item.ounit}</td>
                <td>${item.ostore}</td>
                <td><input type="text" name="req_qty[]" maxlength="6" oninput="validateNumberInput(this)" class="form-control" required></td>
                <td><input type="text" class="form-control" readonly disabled></td>
                <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
            `;
        }
    });

    selectedItems.clear();
    $("#lookup").modal("hide");
    updateItemNumbers();
});

    itemsTableBody.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-item")) {
            e.target.closest("tr").remove();
            updateItemNumbers();
        }
    });

    function updateItemNumbers() {
        document.querySelectorAll("#itemsTableBody tr").forEach((row, index) => {
            row.querySelector(".item-count").innerText = index + 1;
        });
    }

    searchQueryAdd.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchButtonAdd.click();
        }
    });

    searchButtonAdd.addEventListener("click", function () {
        const query = searchQueryAdd.value;
        const url = `search_lookup?searchOptionAdd=d_desc&searchInputAdd=${query}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                searchResultsAdd.innerHTML = "";
                if (data.length === 0) {
                    searchResultsAdd.innerHTML = '<tr><td colspan="8">No results found.</td></tr>';
                    return;
                }

                data.forEach(item => {
                    item.oitemdesc = sanitizeDesc(item.oitemdesc);
                    const row = searchResultsAdd.insertRow();
                    row.innerHTML = `
                        <td>${item.oitemcode}</td>
                        <td>${item.obarcode}</td>
                        <td>${item.oitemdesc}</td> 
                        <td>${item.oitemno}</td>
                        <td>${item.ounit}</td>
                        <td>${item.ostore}</td>
                        <td><button type="button" class="btn-perm add-item-btn" data-item='${JSON.stringify(item)}'>Add Item</button></td>
                    `;
                });
            })
            .catch(error => {
                console.error("Error fetching manual add ", error);
                alert("An error occurred during the manual add search. Please try again.");
            });
    });

    searchResultsAdd.addEventListener("click", function (event) {
        if (event.target.classList.contains("add-item-btn")) {
            const itemData = JSON.parse(event.target.dataset.item);
            itemData.oitemdesc = sanitizeDesc(itemData.oitemdesc);
            const rowCount = document.querySelectorAll("#itemsTableBody tr").length + 1;

            const row = itemsTableBody.insertRow();
            row.innerHTML = `
                <td class="item-count">${rowCount}</td>
                <input type="hidden" name="items[]" value='${JSON.stringify(itemData)}'>
                <td>${itemData.oitemcode}</td>
                <td>${itemData.obarcode}</td>
                <td>${itemData.oitemdesc}</td>
                <td>${itemData.oitemno}</td>
                <td>${itemData.ounit}</td>
                <td>${itemData.ostore}</td>
                <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
            `;

            $("#manualadd").modal("hide");
            updateItemNumbers();
        }
    });
});

function validateNumberInput(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}
