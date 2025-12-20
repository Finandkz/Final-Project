document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('streak-calendar');
    if (!root) return;

    const year = parseInt(root.dataset.year, 10) || new Date().getFullYear();
    let activeDates = [];
    try {
        activeDates = JSON.parse(root.dataset.active || '[]');
    } catch (e) {
        activeDates = [];
    }
    const activeSet = new Set(activeDates);

    const monthNames = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];
    const dayNames = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

    for (let m = 0; m < 12; m++) {
        const monthDiv = document.createElement('div');
        monthDiv.className = 'streak-month';

        const title = document.createElement('div');
        title.className = 'streak-month-title';
        title.textContent = monthNames[m];
        monthDiv.appendChild(title);

        const table = document.createElement('table');

        const thead = document.createElement('thead');
        const trHead = document.createElement('tr');
        dayNames.forEach(d => {
            const th = document.createElement('th');
            th.textContent = d;
            trHead.appendChild(th);
        });
        thead.appendChild(trHead);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        const firstDay = new Date(year, m, 1);
        let startIndex = firstDay.getDay();

        const daysInMonth = new Date(year, m + 1, 0).getDate();

        let currentRow = document.createElement('tr');

        for (let i = 0; i < startIndex; i++) {
            const td = document.createElement('td');
            td.className = 'streak-day-empty';
            currentRow.appendChild(td);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            if (currentRow.children.length === 7) {
                tbody.appendChild(currentRow);
                currentRow = document.createElement('tr');
            }

            const td = document.createElement('td');
            td.textContent = day;

            const dateStr =
                year + '-' +
                String(m + 1).padStart(2, '0') + '-' +
                String(day).padStart(2, '0');

            if (activeSet.has(dateStr)) {
                td.classList.add('streak-day-active');
            }

            currentRow.appendChild(td);
        }

        if (currentRow.children.length > 0) {
            while (currentRow.children.length < 7) {
                const td = document.createElement('td');
                td.className = 'streak-day-empty';
                currentRow.appendChild(td);
            }
            tbody.appendChild(currentRow);
        }

        table.appendChild(tbody);
        monthDiv.appendChild(table);
        root.appendChild(monthDiv);
    }
});
