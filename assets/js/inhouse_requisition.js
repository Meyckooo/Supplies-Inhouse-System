document.addEventListener('DOMContentLoaded', () => {
    //* DOM Elements *//
    const datePickerBtn = document.getElementById('datePickerBtn');
    const calendarDropdown = document.getElementById('calendarDropdown');
    const selectedDateRangeText = document.getElementById('selectedDateRangeText');
    const calendarGrid = document.getElementById('calendarGrid');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const prevMonthBtn = document.getElementById('prevMonthBtn');
    const nextMonthBtn = document.getElementById('nextMonthBtn');
    const btnClearDate = document.getElementById('btnClearDate');

    //* Storage Key para sa Persistence *//
    const STORAGE_KEY_START = 'inhouse_req_date_start';
    const STORAGE_KEY_END = 'inhouse_req_date_end';

    //* State Variables *//
    let currentDate = new Date(2026, 9, 1); //* Default View *//
    let startDate = null;
    let endDate = null;
    let hoveredDate = null;

    function loadSavedDates() {
        const savedStart = localStorage.getItem(STORAGE_KEY_START);
        const savedEnd = localStorage.getItem(STORAGE_KEY_END);

        if (savedStart && savedEnd) {
            startDate = new Date(savedStart);
            endDate = new Date(savedEnd);
            currentDate = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        } else {
          
            startDate = null;
            endDate = null;
        }
    }

    const monthNames = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];

    function initDropdowns() {
        monthSelect.innerHTML = '';
        monthNames.forEach((month, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = month;
            monthSelect.appendChild(option);
        });

        yearSelect.innerHTML = '';
        const currentYr = new Date().getFullYear();
        for (let y = currentYr - 5; y <= currentYr + 5; y++) {
            const option = document.createElement('option');
            option.value = y;
            option.textContent = y;
            yearSelect.appendChild(option);
        }
    }

    function formatDateDisplay(date) {
        if (!date) return '';
        const m = monthNames[date.getMonth()].substring(0, 3);
        const d = date.getDate();
        const y = date.getFullYear();
        return `${m} ${d}, ${y}`;
    }

    function updateTriggerText() {
        if (startDate && endDate) {
            if (startDate.getTime() === endDate.getTime()) {
                selectedDateRangeText.textContent = formatDateDisplay(startDate);
            } else {
                selectedDateRangeText.textContent = `${formatDateDisplay(startDate)} to ${formatDateDisplay(endDate)}`;
            }
        } else if (startDate) {
            selectedDateRangeText.textContent = `${formatDateDisplay(startDate)} to ...`;
        } else {
            selectedDateRangeText.textContent = 'Select Date';
        }
    }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthSelect.value = month;
        yearSelect.value = year;

        calendarGrid.innerHTML = '';

        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            const dayHead = document.createElement('div');
            dayHead.className = 'day_name';
            dayHead.textContent = day;
            calendarGrid.appendChild(dayHead);
        });

        const firstDayIndex = new Date(year, month, 1).getDay();
        const totalDaysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();

        //*  Previous Month Days *//
        for (let i = firstDayIndex; i > 0; i--) {
            const dayNum = prevMonthDays - i + 1;
            const thisDate = new Date(year, month - 1, dayNum);
            const dayDiv = createDayCell(dayNum, thisDate, true);
            calendarGrid.appendChild(dayDiv);
        }

        //*  Current Month Days *//
        for (let day = 1; day <= totalDaysInMonth; day++) {
            const thisDate = new Date(year, month, day);
            const dayDiv = createDayCell(day, thisDate, false);
            calendarGrid.appendChild(dayDiv);
        }

        //* Next Month Days Padding *//
        const totalCells = firstDayIndex + totalDaysInMonth;
        const remainingCells = 42 - totalCells;
        for (let j = 1; j <= remainingCells; j++) {
            const thisDate = new Date(year, month + 1, j);
            const dayDiv = createDayCell(j, thisDate, true);
            calendarGrid.appendChild(dayDiv);
        }
    }

    function createDayCell(dayNum, thisDate, isDim) {
        const dayDiv = document.createElement('div');
        dayDiv.className = isDim ? 'day_num dim' : 'day_num';
        dayDiv.textContent = dayNum;

        const time = thisDate.getTime();
        const startTime = startDate ? startDate.getTime() : null;
        const endTime = endDate ? endDate.getTime() : null;
        const hoverTime = hoveredDate ? hoveredDate.getTime() : null;

        // Class Highlight Engine
        if (startTime && !endTime && time === startTime) {
            dayDiv.classList.add('range_single');
        } else if (startTime && endTime) {
            if (startTime === endTime && time === startTime) {
                dayDiv.classList.add('range_single');
            } else if (time === startTime) {
                dayDiv.classList.add('range_start');
            } else if (time === endTime) {
                dayDiv.classList.add('range_end');
            } else if (time > startTime && time < endTime) {
                dayDiv.classList.add('in_range');
            }
        } else if (startTime && !endTime && hoverTime && time > startTime && time <= hoverTime) {
            if (time === hoverTime) {
                dayDiv.classList.add('range_end');
            } else {
                dayDiv.classList.add('hover_in_range');
            }
        }

        dayDiv.addEventListener('click', (e) => {
            e.stopPropagation();
            handleDateClick(thisDate);
        });

        dayDiv.addEventListener('dblclick', (e) => {
            e.stopPropagation();
            handleDoubleClick(thisDate);
        });

        dayDiv.addEventListener('mouseenter', () => {
            if (startDate && !endDate) {
                hoveredDate = thisDate;
                renderCalendar();
            }
        });

        return dayDiv;
    }

    function handleDateClick(clickedDate) {
        const normalized = new Date(clickedDate.getFullYear(), clickedDate.getMonth(), clickedDate.getDate());

        if (!startDate || (startDate && endDate)) {

            startDate = normalized;
            endDate = null;
            hoveredDate = null;
            updateTriggerText();
            renderCalendar();
        } else if (startDate && !endDate) {
       
            if (normalized < startDate) {
                endDate = startDate;
                startDate = normalized;
            } else {
                endDate = normalized;
            }
            hoveredDate = null;
            
            localStorage.setItem(STORAGE_KEY_START, startDate.toISOString());
            localStorage.setItem(STORAGE_KEY_END, endDate.toISOString());

            updateTriggerText();
            renderCalendar();

            setTimeout(() => {
                calendarDropdown.classList.remove('show');
            }, 200);
        }
    }

    function handleDoubleClick(clickedDate) {
        const normalized = new Date(clickedDate.getFullYear(), clickedDate.getMonth(), clickedDate.getDate());
        startDate = normalized;
        endDate = normalized;
        hoveredDate = null;

        localStorage.setItem(STORAGE_KEY_START, startDate.toISOString());
        localStorage.setItem(STORAGE_KEY_END, endDate.toISOString());

        updateTriggerText();
        renderCalendar();

        setTimeout(() => {
            calendarDropdown.classList.remove('show');
        }, 150);
    }

    //* Dropdown Toggle *//
    datePickerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        calendarDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!calendarDropdown.contains(e.target) && !datePickerBtn.contains(e.target)) {
            calendarDropdown.classList.remove('show');
        }
    });

    //* Month & Year Navigation *//
    prevMonthBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    monthSelect.addEventListener('change', (e) => {
        currentDate.setMonth(parseInt(e.target.value));
        renderCalendar();
    });

    yearSelect.addEventListener('change', (e) => {
        currentDate.setFullYear(parseInt(e.target.value));
        renderCalendar();
    });

    btnClearDate.addEventListener('click', (e) => {
        e.stopPropagation();
        startDate = null;
        endDate = null;
        hoveredDate = null;

        localStorage.removeItem(STORAGE_KEY_START);
        localStorage.removeItem(STORAGE_KEY_END);

        updateTriggerText();
        renderCalendar();
    });
    

    // Initialization Sequence
    initDropdowns();
    loadSavedDates();
    updateTriggerText();
    renderCalendar();

    //Initiate Function syncGlobalFloatingAction
    syncGlobalFloatingAction();

    //Initiate Function syncGlobalFloatongActionHeights
    syncGlobalFloatongActionHeights();

    setTimeout(() => {
      const container = document.querySelector(".global_table_container");
      if (container) {
        container.scrollLeft = 0;

        if (typeof syncGlobalFloatingAction === "function") {
          syncGlobalFloatingAction();
        }
      }
    }, 150);
});


function syncGlobalFloatingAction() {
  const container = document.querySelector(".global_table_container");
  if (!container) return;

  function updatePosition() {
    const actionHeader = document.querySelector(
      ".global_table thead th.global-floating-action",
    );
    const actionCells = document.querySelectorAll(
      ".global_table tbody td.global-floating-action",
    );

    if (!actionHeader) return;

    const maxScrollLeft = container.scrollWidth - container.clientWidth;

    if (maxScrollLeft <= 0) {
      actionHeader.style.transform = "none";
      actionHeader.style.boxShadow = "none";
      actionCells.forEach((cell) => {
        cell.style.transform = "none";
        cell.style.boxShadow = "none";
      });
      return;
    }

    const currentScrollLeft = container.scrollLeft;
    const remainingScroll = maxScrollLeft - currentScrollLeft;
    const shiftX = Math.max(0, remainingScroll);

    actionHeader.style.transform = `translate3d(-${shiftX}px, 0px, 0px)`;
    actionCells.forEach((cell) => {
      cell.style.transform = `translate3d(-${shiftX}px, 0px, 0px)`;
    });

    if (shiftX > 2) {
      actionHeader.style.boxShadow = "-4px 2px 8px rgba(0,0,0,0.15)";
      actionCells.forEach((cell) => {
        cell.style.boxShadow = "-4px 0 8px rgba(0,0,0,0.1)";
      });
    } else {
      actionHeader.style.boxShadow = "none";
      actionCells.forEach((cell) => {
        cell.style.boxShadow = "none";
      });
    }
  }

  container.removeEventListener("scroll", updatePosition);
  container.addEventListener("scroll", updatePosition);

  window.removeEventListener("resize", updatePosition);
  window.addEventListener("resize", updatePosition);

  updatePosition();
}


function syncGlobalFloatongActionHeights() {
    const rows = document.querySelectorAll(".global_table tbody tr");
    
    rows.forEach(row => {
        const actionCell = row.querySelector("td.global-floating-action");
        if (!actionCell) return;

        actionCell.style.height = 'auto';

        const rowHeight = row.offsetHeight;

        actionCell.style.height = `${rowHeight}px`;
    });
}

window.addEventListener('load', syncGlobalFloatongActionHeights);
window.addEventListener('resize', syncGlobalFloatongActionHeights);