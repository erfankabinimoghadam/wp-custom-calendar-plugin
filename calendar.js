<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    // Create modal for event details
    const modal = document.createElement('div');
    modal.id = 'calendar-modal';
    modal.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;opacity:0;transform:scale(0.95);transition:opacity 0.3s ease, transform 0.3s ease;';
    modal.innerHTML = `
        <div id="calendar-modal-inner">
            <button id="calendar-modal-close">X</button>
            <div id="calendar-modal-content"></div>
        </div>
    `;
    document.body.appendChild(modal);

    const modalContent = document.getElementById('calendar-modal-content');
    const modalClose = document.getElementById('calendar-modal-close');
    const modalInner = document.getElementById('calendar-modal-inner');

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: PLUGIN_CALENDAR_EVENTS,
        dayHeaderFormat: window.matchMedia("(min-width: 1200px)").matches ? { weekday: 'long' } : { weekday: 'short' },

        // Event rendering
        eventDidMount: function(info) {
            // Force two lines if title overflows
            const titleEl = info.el.querySelector('.fc-event-title');
            if (titleEl && titleEl.scrollWidth > titleEl.clientWidth) info.el.classList.add('fc-force-two-lines');

            // Mark auto events
            if (info.event.extendedProps.auto && info.event.extendedProps.from_parent_SPECIFIC) {
                info.el.setAttribute('data-auto', 'true');
                info.el.classList.add('fc-auto-event');
            }
        },

        // Event click behavior
        eventClick: function(info) {
            const description = info.event.extendedProps.description || 'No description available.';
            const link = info.event.extendedProps.subscription_link;
            const isAuto = info.event.extendedProps.auto;
            const permalink = info.event.extendedProps.permalink;

            // Auto event opens external link directly
            if (isAuto && permalink) {
                window.open(permalink, '_blank');
                return;
            }

            info.jsEvent.preventDefault();

            // Add subscribe button if link exists
            const button = link
                ? `<a href="${link}" target="_blank">Subscribe</a>`
                : '';

            modalContent.innerHTML = description + button;
            modal.style.display = 'flex';
            setTimeout(() => { modal.style.opacity = '1'; modal.style.transform = 'scale(1)'; }, 10);
            document.body.style.overflow = 'hidden';
        }
    });

    calendar.render();

    // Close modal
    function closeModal() {
        modal.style.opacity = '0';
        modal.style.transform = 'scale(0.95)';
        document.body.style.overflow = 'auto';
        setTimeout(() => modal.style.display = 'none', 300);
    }
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    modalInner.addEventListener('click', e => e.stopPropagation());

    // Filter events via legend
    function filterEventsByType(type) {
        let filteredEvents;
        if (type === 'Events') filteredEvents = PLUGIN_CALENDAR_EVENTS.filter(e => e.from_parent_SPECIFIC);
        else if (type === 'Courses') filteredEvents = PLUGIN_CALENDAR_EVENTS.filter(e => !e.from_parent_SPECIFIC);
        else filteredEvents = PLUGIN_CALENDAR_EVENTS;

        calendar.removeAllEvents();
        calendar.addEventSource(filteredEvents);
        calendar.render();
    }

    // Add event listeners to legend filters
    document.querySelectorAll('.legend-filter').forEach(item => {
        item.addEventListener('click', () => {
            const selectedType = item.getAttribute('data-event-type');
            filterEventsByType(selectedType);
            // Reset styles (pseudo)
        });
    });

    // Reset button
    const resetBtn = document.getElementById('reset-button');
    if (resetBtn) resetBtn.addEventListener('click', () => {
        calendar.removeAllEvents();
        calendar.addEventSource(PLUGIN_CALENDAR_EVENTS);
        calendar.render();
    });
});
</script>
