function updatePhilippineDateTime() {
    const clockContainer = document.getElementById('phRealtimeClock');
    if (!clockContainer) return;

    const now = new Date();

    // Configuration para sa Philippine Time (Asia/Manila)
    const dateOptions = { 
        timeZone: 'Asia/Manila', 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    };
    
    const timeOptions = { 
        timeZone: 'Asia/Manila', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: true 
    };

    const dateFormatter = new Intl.DateTimeFormat('en-US', dateOptions);
    const timeFormatter = new Intl.DateTimeFormat('en-US', timeOptions);

    const formattedDate = dateFormatter.format(now);
    const formattedTime = timeFormatter.format(now);

    // I-inject sa HTML elements
    clockContainer.innerHTML = `
        <span class="ph_clock_date">${formattedDate}</span>
        <span class="ph_clock_time">${formattedTime}</span>
    `;
}

// Padaganon dayon pag-load ug mag-update kada 1 segundo (1000ms)
setInterval(updatePhilippineDateTime, 1000);
document.addEventListener('DOMContentLoaded', updatePhilippineDateTime);