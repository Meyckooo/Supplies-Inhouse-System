document.addEventListener("DOMContentLoaded", () => {
  const plateSection = document.getElementById("plateNumberSection");
  const plateTableBody = document.getElementById("plateNumberTableBody");
  const plateButton = document.getElementById("togglePlateConfig");
  const plateForm = document.getElementById("registerPlateForm");
  const plateModalElement = document.getElementById("registerPlateModal");
  const plateInput = document.getElementById("plateNumberInput");
  const plateMessage = document.getElementById("registerPlateMessage");
  const plateSubmitButton = document.getElementById("registerPlateSubmit");

  const approverSection = document.getElementById("approverSection");
  const approverTableBody = document.getElementById("approverTableBody");
  const approverButton = document.getElementById("toggleApproverConfig");
  const approverForm = document.getElementById("registerApproverForm");
  const approverModalElement = document.getElementById("registerApproverModal");
  const approverSearchInput = document.getElementById("approverSearchInput");
  const approverUserId = document.getElementById("approverUserId");
  const approverSearchResults = document.getElementById("approverSearchResults");
  const departmentSelect = document.getElementById("approverDepartmentSelect");
  const locationSelect = document.getElementById("approverLocationSelect");
  const approverMessage = document.getElementById("registerApproverMessage");
  const approverSubmitButton = document.getElementById("registerApproverSubmit");
  let approverSearchTimer;
  let approverSearchController;

  function showTableMessage(tableBody, columnCount, message) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = columnCount;
    cell.textContent = message;
    row.appendChild(cell);
    tableBody.replaceChildren(row);
  }

  async function loadPlateNumbers() {
    try {
      const response = await fetch(plateTableBody.dataset.endpoint, {
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      if (!Array.isArray(result.data)) {
        throw new Error("The server returned invalid plate data.");
      }

      if (result.data.length === 0) {
        showTableMessage(plateTableBody, 3, "No plate numbers found.");
        return;
      }

      const rows = result.data.map((plate) => {
        const row = document.createElement("tr");
        const idCell = document.createElement("td");
        const plateCell = document.createElement("td");
        const actionCell = document.createElement("td");

        idCell.textContent = plate.otableid;
        plateCell.textContent = plate.oplateno;
        actionCell.className = "action_cell global-floating-action";
        actionCell.textContent = "-";

        row.append(idCell, plateCell, actionCell);
        return row;
      });

      plateTableBody.replaceChildren(...rows);
    } catch (error) {
      console.error("Error loading plate numbers:", error);
      showTableMessage(plateTableBody, 3, error.message);
    }
  }

  async function loadApprovers() {
    try {
      const response = await fetch(approverTableBody.dataset.endpoint, {
        headers: { Accept: "application/json" },
      });
      const result = await readResponse(response);
      if (!Array.isArray(result.data)) {
        throw new Error("The server returned invalid approver data.");
      }

      if (result.data.length === 0) {
        showTableMessage(approverTableBody, 5, "No approvers found.");
        return;
      }

      const rows = result.data.map((approver) => {
        const row = document.createElement("tr");
        const values = [
          approver.oapprover_name,
          approver.oapprover_id,
          approver.olocation_id,
          approver.odepartment_name,
          "-",
        ];

        values.forEach((value, index) => {
          const cell = document.createElement("td");
          cell.textContent = value ?? "";
          if (index === values.length - 1) {
            cell.className = "action_cell global-floating-action";
          }
          row.appendChild(cell);
        });

        return row;
      });

      approverTableBody.replaceChildren(...rows);
    } catch (error) {
      console.error("Error loading approvers:", error);
      showTableMessage(approverTableBody, 5, error.message);
    }
  }

  async function loadApproverOptions() {
    const url = `${approverForm.dataset.endpoint}?action=options`;
    const response = await fetch(url, {
      headers: { Accept: "application/json" },
    });
    const result = await readResponse(response);
    const options = result.data;
    if (!options || typeof options !== "object" || Array.isArray(options)) {
      throw new Error("The server returned approver options in an invalid format.");
    }

    if (!Array.isArray(options.departments)) {
      throw new Error("The server did not return a valid department list.");
    }
    if (!Array.isArray(options.locations)) {
      throw new Error("The server did not return a valid location list.");
    }

    populateSelect(departmentSelect, options.departments, "otableid", "odepartment_name", "Select a department");
    populateSelect(
      locationSelect,
      options.locations.map((locationId) => ({ id: locationId, name: `Location #${locationId}` })),
      "id",
      "name",
      "Select a location",
    );
  }

  function showApproverSearchMessage(message) {
    const item = document.createElement("div");
    item.className = "list-group-item text-muted";
    item.textContent = message;
    approverSearchResults.replaceChildren(item);
    approverSearchResults.hidden = false;
    approverSearchInput.setAttribute("aria-expanded", "true");
  }

  function renderApproverSearchResults(users) {
    const items = users.map((user) => {
      const item = document.createElement("button");
      item.type = "button";
      item.className = "list-group-item list-group-item-action";
      item.setAttribute("role", "option");
      item.dataset.userId = user.oUserid;
      item.textContent = user.oFullname;
      return item;
    });

    approverSearchResults.replaceChildren(...items);
    approverSearchResults.hidden = items.length === 0;
    approverSearchInput.setAttribute("aria-expanded", String(items.length > 0));
  }

  async function searchApprovers(query) {
    approverSearchController?.abort();
    const controller = new AbortController();
    approverSearchController = controller;
    const url = new URL(approverForm.dataset.endpoint, window.location.href);
    url.searchParams.set("action", "search");
    url.searchParams.set("q", query);

    try {
      const response = await fetch(url, {
        headers: { Accept: "application/json" },
        signal: controller.signal,
      });
      const result = await readResponse(response);
      if (approverSearchInput.value.trim() !== query) {
        return;
      }
      if (!Array.isArray(result.data)) {
        throw new Error("The server returned invalid approver search results.");
      }

      if (result.data.length === 0) {
        showApproverSearchMessage("No matching approvers found.");
        return;
      }

      renderApproverSearchResults(result.data);
    } catch (error) {
      if (error.name === "AbortError") {
        return;
      }
      if (approverSearchInput.value.trim() === query) {
        showApproverSearchMessage(error.message);
      }
    } finally {
      if (approverSearchController === controller) {
        approverSearchController = null;
      }
    }
  }

  function populateSelect(select, options, valueKey, labelKey, placeholder) {
    const placeholderOption = document.createElement("option");
    placeholderOption.value = "";
    placeholderOption.textContent = placeholder;
    select.replaceChildren(placeholderOption);

    options.forEach((item) => {
      const option = document.createElement("option");
      option.value = item[valueKey];
      option.textContent = item[labelKey];
      select.appendChild(option);
    });
  }

  async function readResponse(response) {
    let result;
    try {
      result = await response.json();
    } catch {
      throw new Error("The server returned an invalid response.");
    }

    if (!response.ok || !result.success) {
      throw new Error(result.error || `Request failed (${response.status}).`);
    }

    return result;
  }

  function setStatus(element, text, isSuccess) {
    element.textContent = text;
    element.className = isSuccess ? "mt-2 text-success" : "mt-2 text-danger";
  }

  if (plateButton && plateSection && plateTableBody) {
    let clicks = 0;
    plateButton.addEventListener("click", async () => {
      clicks += 1;
      if (clicks % 2 === 0) {
        window.bootstrap.Modal.getOrCreateInstance(plateModalElement).show();
        return;
      }

      approverSection.hidden = true;
      plateSection.hidden = false;
      showTableMessage(plateTableBody, 3, "Loading plate numbers...");
      await loadPlateNumbers();
    });
  }

  if (approverButton && approverSection && approverTableBody) {
    let clicks = 0;
    approverButton.addEventListener("click", async () => {
      clicks += 1;
      if (clicks % 2 === 0) {
        window.bootstrap.Modal.getOrCreateInstance(approverModalElement).show();
        return;
      }

      plateSection.hidden = true;
      approverSection.hidden = false;
      showTableMessage(approverTableBody, 5, "Loading approvers...");
      await loadApprovers();
    });
  }

  if (plateForm && plateModalElement && plateInput && plateMessage && plateSubmitButton) {
    plateModalElement.addEventListener("show.bs.modal", () => {
      plateMessage.textContent = "";
      plateMessage.className = "mt-2";
      plateForm.reset();
    });

    plateForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      plateMessage.textContent = "";
      plateMessage.className = "mt-2";

      const plateNumber = plateInput.value.trim().toUpperCase();
      if (!plateNumber || plateNumber.length > 10) {
        setStatus(plateMessage, "Enter a plate number between 1 and 10 characters.", false);
        plateInput.focus();
        return;
      }

      plateInput.value = plateNumber;
      plateSubmitButton.disabled = true;

      try {
        const response = await fetch(plateForm.dataset.endpoint, {
          method: "POST",
          headers: { Accept: "application/json" },
          body: new FormData(plateForm),
        });
        const result = await readResponse(response);

        setStatus(plateMessage, result.message, true);
        await loadPlateNumbers();
      } catch (error) {
        setStatus(plateMessage, error.message, false);
      } finally {
        plateSubmitButton.disabled = false;
      }
    });
  }

  if (approverForm && approverModalElement && approverMessage && approverSubmitButton) {
    approverModalElement.addEventListener("show.bs.modal", async () => {
      clearTimeout(approverSearchTimer);
      approverSearchController?.abort();
      approverSearchResults.replaceChildren();
      approverSearchResults.hidden = true;
      approverSearchInput.setAttribute("aria-expanded", "false");
      approverMessage.textContent = "";
      approverMessage.className = "mt-2";
      approverForm.reset();

      try {
        await loadApproverOptions();
      } catch (error) {
        console.error("Error loading approver registration options:", error);
        setStatus(approverMessage, error.message, false);
      }
    });

    approverModalElement.addEventListener("hide.bs.modal", () => {
      clearTimeout(approverSearchTimer);
      approverSearchController?.abort();
    });

    approverSearchInput.addEventListener("input", () => {
      approverUserId.value = "";
      clearTimeout(approverSearchTimer);
      approverSearchController?.abort();

      const query = approverSearchInput.value.trim();
      if (!query) {
        approverSearchResults.replaceChildren();
        approverSearchResults.hidden = true;
        approverSearchInput.setAttribute("aria-expanded", "false");
        return;
      }

      showApproverSearchMessage("Searching...");
      approverSearchTimer = setTimeout(() => searchApprovers(query), 250);
    });

    approverSearchResults.addEventListener("click", (event) => {
      const resultButton = event.target.closest("button[data-user-id]");
      if (!resultButton || !approverSearchResults.contains(resultButton)) {
        return;
      }

      approverUserId.value = resultButton.dataset.userId;
      approverSearchInput.value = resultButton.textContent;
      approverSearchResults.replaceChildren();
      approverSearchResults.hidden = true;
      approverSearchInput.setAttribute("aria-expanded", "false");
      approverSearchInput.focus();
    });

    approverForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      approverMessage.textContent = "";
      approverMessage.className = "mt-2";

      if (!approverUserId.value || !departmentSelect.value || !locationSelect.value) {
        setStatus(approverMessage, "Select an approver, department, and location.", false);
        return;
      }

      approverSubmitButton.disabled = true;
      try {
        const response = await fetch(approverForm.dataset.endpoint, {
          method: "POST",
          headers: { Accept: "application/json" },
          body: new FormData(approverForm),
        });
        const result = await readResponse(response);

        setStatus(approverMessage, result.message, true);
        await loadApprovers();
      } catch (error) {
        setStatus(approverMessage, error.message, false);
      } finally {
        approverSubmitButton.disabled = false;
      }
    });
  }
});
