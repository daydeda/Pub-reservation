// js/reservation.js

document.addEventListener("DOMContentLoaded", () => {
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');
    const guestsInput = document.getElementById('guests');
    const checkBtn = document.getElementById('updateMapBtn');
    const tablesLayer = document.getElementById('tablesLayer');
    const tableIdInput = document.getElementById('table_id');
    const selectedTableDisplay = document.getElementById('selectedTableDisplay');
    const submitBtn = document.getElementById('submitBtn');

    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.value = today;
    
    // Add event listener
    if(checkBtn){
        checkBtn.addEventListener('click', fetchTables);
    }
    
    async function fetchTables() {
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;

        if (!date || !time) {
            alert("Please select both a Date and Time to view available tables.");
            return;
        }

        // Use the global constant CURRENT_PUB_ID defined in PHP
        const pubId = (typeof CURRENT_PUB_ID !== 'undefined') ? CURRENT_PUB_ID : 1;

        // Reset display
        tablesLayer.innerHTML = '<p class="map-placeholder text-white text-center mt-10">Loading...</p>';
        selectedTableDisplay.innerText = '--';
        tableIdInput.value = '';
        if(submitBtn) submitBtn.disabled = true;

        try {
            const url = `api/get_tables.php?date=${date}&time=${time}&guests=${guests}&pub_id=${pubId}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.error) {
                console.error(data.error);
                tablesLayer.innerHTML = `<p class="map-error text-red-500 text-center mt-10">${data.error}</p>`;
                return;
            }

            renderMap(data.tables);
        } catch (error) {
            console.error('Error fetching tables:', error);
            tablesLayer.innerHTML = '<p class="map-error text-red-500 text-center mt-10">Error loading tables</p>';
        }
    }

    function renderMap(tables) {
        tablesLayer.innerHTML = ''; // Clear

        tables.forEach(table => {
            const el = document.createElement('div');
            // Ensure status matches CSS classes: available, reserved (for booked), incompatible
            let statusClass = table.status;
            if (statusClass === 'booked') statusClass = 'reserved'; // Map 'booked' to 'reserved' class
            
            el.className = `table-marker ${statusClass}`;
            if(table.type === 'vip') el.classList.add('vip');
            
            el.style.left = table.coord_x + '%';
            el.style.top = table.coord_y + '%';
            el.textContent = table.number;

            if (table.status === 'available') {
                el.addEventListener('click', () => selectTable(table, el));
            } else if (table.status === 'incompatible') {
                el.title = "Not suitable for your group size";
            } else {
                el.title = "Reserved";
            }

            tablesLayer.appendChild(el);
        });
    }

    function selectTable(table, element) {
        // Clear previous selection
        document.querySelectorAll('.table-marker.selected').forEach(el => el.classList.remove('selected'));
        
        // Select new
        element.classList.add('selected');
        
        selectedTableDisplay.innerText = `Table ${table.number} (${table.type.toUpperCase()})`;
        tableIdInput.value = table.id;
        submitBtn.disabled = false;
    }
});
