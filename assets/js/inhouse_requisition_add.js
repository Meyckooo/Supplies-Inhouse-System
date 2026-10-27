document.addEventListener("DOMContentLoaded", () => {
  const purposeSelect = document.getElementById("purposeSelect");
  const subGroups = document.querySelectorAll(".sub_dropdown_group");

  if (purposeSelect) {
    purposeSelect.addEventListener("change", (e) => {
      const val = e.target.value;
      subGroups.forEach((group) => (group.style.display = "none"));

      if (val === "selling_marketing") {
        document.getElementById("sub_selling_marketing").style.display = "flex";
      } else if (val === "production") {
        document.getElementById("sub_production").style.display = "flex";
      } else if (val === "vehicle_fleet") {
        document.getElementById("sub_vehicle_fleet").style.display = "flex";
      } else if (val === "it_hardware") {
        document.getElementById("sub_it_hardware").style.display = "flex";
      }
    });
  }

  // --- LOOKUP ITEM MODAL JS ---
  const lookupItemModal = document.getElementById("lookupItemModal");
  const btnOpenLookupModal = document.querySelector(".global_add_btn");
  const btnCloseLookupModal = document.getElementById("btnCloseLookupModal");
  const selectAllCheckbox = document.getElementById("selectAllLookupItems");
  const btnSearchLookup = document.querySelector(".lookup_btn_search");
  const lookupSearchInput = document.querySelector(".lookup_search_input");
  const lookupSearchSelect = document.querySelector(".lookup_search_select");
  const lookupTableBody = document.querySelector(".lookup_table tbody");
  const btnAddSelected = document.getElementById("btnAddSelected");
  const mainTableBody = document.querySelector(".ir_add_display_tbl tbody");

  // Auto-convert lookup search input text to UPPERCASE as the user types
  if (lookupSearchInput) {
    lookupSearchInput.addEventListener("input", (e) => {
      const start = e.target.selectionStart;
      const end = e.target.selectionEnd;
      e.target.value = e.target.value.toUpperCase();
      e.target.setSelectionRange(start, end); // Keeps cursor position stable
    });
  }

  // Open Modal when "+ Add Item" button is clicked
  if (btnOpenLookupModal && lookupItemModal) {
    btnOpenLookupModal.addEventListener("click", () => {
      lookupItemModal.classList.add("show");
      fetchLookupItems("", "all"); // Load initial items on open
    });
  }

  // Close Modal via Close (X) button
  if (btnCloseLookupModal && lookupItemModal) {
    btnCloseLookupModal.addEventListener("click", () => {
      lookupItemModal.classList.remove("show");
    });
  }

  // Close Modal when clicking outside the modal box
  window.addEventListener("click", (e) => {
    if (e.target === lookupItemModal) {
      lookupItemModal.classList.remove("show");
    }
  });

  // Toggle "Select All" Checkboxes inside Lookup Table
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", (e) => {
      const itemCheckboxes = document.querySelectorAll(".lookup_item_checkbox");
      itemCheckboxes.forEach((checkbox) => {
        checkbox.checked = e.target.checked;
      });
    });
  }

  // Fetch Lookup Items via AJAX pointing to the controller
  function fetchLookupItems(query = "", type = "all") {
    fetch(
      `../controller/inhouse_item_controller.php?search=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}`,
    )
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          populateLookupTable(result.data);
        }
      })
      .catch((error) => console.error("Error fetching lookup items:", error));
  }

  // Trigger Search Button Click
  if (btnSearchLookup) {
    btnSearchLookup.addEventListener("click", () => {
      const query = lookupSearchInput.value.trim();
      const searchType = lookupSearchSelect ? lookupSearchSelect.value : "all";
      fetchLookupItems(query, searchType);
    });
  }

  // Trigger Search on Enter Key in Input
  if (lookupSearchInput) {
    lookupSearchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        const query = lookupSearchInput.value.trim();
        const searchType = lookupSearchSelect
          ? lookupSearchSelect.value
          : "all";
        fetchLookupItems(query, searchType);
      }
    });
  }

  // Populate Lookup Modal Table
  function populateLookupTable(items) {
    lookupTableBody.innerHTML = "";
    if (items.length === 0) {
      lookupTableBody.innerHTML = `<tr><td colspan="7" class="text_center">No items found</td></tr>`;
      return;
    }

    items.forEach((item) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td class="text_center">
                    <input type="checkbox" class="lookup_item_checkbox" 
                           data-code="${item.oitemcode || ""}" 
                           data-barcode="${item.obarcode || ""}" 
                           data-desc="${item.oitemdesc || ""}" 
                           data-itemno="${item.oitemno || ""}" 
                           data-unit="${item.ounit || ""}" 
                           data-consignid="${item.oconsignid || 0}" 
                           data-price="${item.oprice || 0}">
                </td>
                <td class="item_code_cell">${item.oitemcode || ""}</td>
                <td>${item.obarcode || ""}</td>
                <td>${item.oitemdesc || ""}</td>
                <td>${item.oitemno || ""}</td>
                <td>${item.ounit || ""}</td>
                <td class="text_right font_bold">${parseFloat(item.ostore || 0).toFixed(2)}</td>
            `;
      lookupTableBody.appendChild(row);
    });
  }

  // Toggle Checkbox when Clicking Anywhere on the Table Row
  if (lookupTableBody) {
    lookupTableBody.addEventListener("click", (e) => {
      const row = e.target.closest("tr");
      if (!row) return;

      if (e.target.tagName === "INPUT" && e.target.type !== "checkbox") return;

      const checkbox = row.querySelector(".lookup_item_checkbox");
      if (checkbox && e.target !== checkbox) {
        checkbox.checked = !checkbox.checked;
      }
    });
  }

  // Add Selected Items to Main Requisition Table
  if (btnAddSelected) {
    btnAddSelected.addEventListener("click", () => {
      const checkedBoxes = document.querySelectorAll(
        ".lookup_item_checkbox:checked",
      );

      if (checkedBoxes.length === 0) {
        alert("Please select at least one item.");
        return;
      }

      // Clear dummy row if it exists
      const firstCellText = mainTableBody.rows[0]?.cells[1]?.textContent;
      if (
        firstCellText === "X1007-049" ||
        mainTableBody.querySelector("td[colspan]")
      ) {
        mainTableBody.innerHTML = "";
      }

      checkedBoxes.forEach((chk) => {
        const rowCount = mainTableBody.rows.length + 1;
        const code = chk.dataset.code;
        const barcode = chk.dataset.barcode;
        const desc = chk.dataset.desc;
        const itemno = chk.dataset.itemno;
        const unit = chk.dataset.unit;
        const consignid = chk.dataset.consignid;
        const price = chk.dataset.price;

        const newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td class="text_center row_number">${rowCount}</td>
            <td>${code}<input type="hidden" name="items[][item_code]" value="${code}"></td>
            <td>${barcode}<input type="hidden" name="items[][bar_code]" value="${barcode}"></td>
            <td>${desc}<input type="hidden" name="items[][item_desc]" value="${desc}"></td>
            <td>${itemno}<input type="hidden" name="items[][item_no]" value="${itemno}"></td>
            <td>${consignid}<input type="hidden" name="items[][consign_id]" value="${consignid}"></td>
            <td>${price}<input type="hidden" name="items[][unit_price]" value="${price}"></td>
            <td>${unit}<input type="hidden" name="items[][oum]" value="${unit}"></td>
            <td><select name="items[][type]" class="global_select">
                <option value="inhouse">SUPPLIES</option>
                <option value="external">INHOUSE</option>
                <option value="external">PRNT</option>
            </select></td>
            <td>SUPPLIES</td>
            <td><input type="number" name="items[][qty]" class="global_input" value="1" min="1" required></td>
            <td class="text_center">
                <button type="button" class="btn btn-danger btn-sm remove_item_btn" style="padding: 2px 8px; font-size: 12px;">&times;</button>
            </td>
        `;
        mainTableBody.appendChild(newRow);
      });

      // Close modal after adding
      lookupItemModal.classList.remove("show");
    });
  }

  // --- NEW: Remove Item Row & Re-index Row Numbers ---
  if (mainTableBody) {
    mainTableBody.addEventListener("click", (e) => {
      if (e.target.classList.contains("remove_item_btn")) {
        const row = e.target.closest("tr");
        if (row) {
          row.remove();
          updateRowNumbers();
        }
      }
    });
  }

  function updateRowNumbers() {
    const rows = mainTableBody.querySelectorAll("tr");
    rows.forEach((row, index) => {
      const numCell = row.querySelector(".row_number");
      if (numCell) {
        numCell.textContent = index + 1;
      }
    });
  }
});
