// js/reservation.js
// ไฟล์จัดการลอจิกฝั่งผู้ใช้งานในหน้าจองโต๊ะ
// Frontend logic for the reservation page

// รอให้โครงสร้างเอกสารโหลดเสร็จก่อนจึงเริ่มทำงาน (Wait for the DOM to be fully loaded before running script)
document.addEventListener("DOMContentLoaded", () => {
    // อ้างอิงอิลิเมนต์ต่างๆ บนหน้าเว็บจาก ID (Get references to DOM elements by their IDs)
    const dateInput = document.getElementById('date'); // ช่องเลือกวันที่ (Date input field)
    const timeInput = document.getElementById('time'); // ช่องเลือกเวลา (Time input field)
    const guestsInput = document.getElementById('guests'); // ช่องระบุจำนวนคน (Guests input field)
    const checkBtn = document.getElementById('updateMapBtn'); // ปุ่มอัปเดตแผนผังโต๊ะ (Update map button)
    const tablesLayer = document.getElementById('tablesLayer'); // พื้นที่สำหรับแสดงโต๊ะทั้งหมด (Container to render tables)
    const tableIdInput = document.getElementById('table_id'); // ช่องซ่อนสำหรับเก็บไอดีโต๊ะที่เลือก (Hidden input for selected table ID)
    const selectedTableDisplay = document.getElementById('selectedTableDisplay'); // ข้อความแสดงชื่อโต๊ะที่กำลังเลือก (Text displaying selected table)
    const submitBtn = document.getElementById('submitBtn'); // ปุ่มดำเนินการชำระเงิน (Proceed to payment button)
    const selectionInfo = document.getElementById('selectionInfo'); // กล่องข้อมูลโต๊ะที่เลือก (Container showing selection info)

    // ตั้งค่าวันเริ่มต้นให้เป็นวันปัจจุบัน (Set default date input value to today's date)
    const today = new Date().toISOString().split('T')[0]; // แปลงวันปัจจุบันเป็นฟอร์แมต YYYY-MM-DD
    dateInput.value = today;
    
    // ตั้งค่าการดักจับเหตุการณ์การคลิกที่ปุ่มตรวจสอบ (Add click event listener to the check button)
    if(checkBtn){
        checkBtn.addEventListener('click', fetchTables);
    }
    
    // ฟังก์ชันดึงข้อมูลความจุโต๊ะจาก API (Async function to fetch table availability from API)
    async function fetchTables() {
        // ดึงค่าวันที่, เวลา, จำนวนคน ที่ผู้ใช้กรอก (Get user input values)
        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;

        // ตรวจสอบว่ากรอกวันและเวลาครบถ้วนหรือไม่ (Validate that date and time are provided)
        if (!date || !time) {
            alert("Please select both a Date and Time to view available tables."); // แจ้งเตือนให้เลือกข้อมูลให้ครบ
            return; // หยุดการทำงานถ้าข้อมูลไม่ครบ (Exit if inputs are empty)
        }

        // ดึงรหัสสาขาจากตัวแปรโกลบอล (Use the global constant CURRENT_PUB_ID defined in PHP)
        const pubId = (typeof CURRENT_PUB_ID !== 'undefined') ? CURRENT_PUB_ID : 1; // ใช้ 1 เป็นค่าพื้นฐานถ้าไม่มี (Fallback to 1)

        // รีเซ็ตการแสดงผลบนกระดานก่อนโหลดข้อมูลใหม่ (Reset map display before fetching new data)
        tablesLayer.innerHTML = '<p class="map-placeholder text-white text-center mt-10">Loading...</p>'; // แสดงข้อความกำลังโหลด (Show loading text)
        selectedTableDisplay.innerText = '--'; // ล้างข้อความแสดงโต๊ะที่เลือก (Clear selected table text)
        tableIdInput.value = ''; // ล้างข้อมูลไอดีโต๊ะในฟอร์ม (Clear hidden input)
        if(submitBtn) submitBtn.disabled = true; // ปิดการใช้งานปุ่มจ่ายเงินชั่วคราว (Disable submit button)
        if(selectionInfo) selectionInfo.classList.add('hidden'); // ซ่อนกล่องข้อมูล (Hide selection info container)

        try {
            // สร้าง URL ตรวจสอบผ่านตัวแปรแบบ GET (Construct API URL with query strings)
            const url = `api/get_tables.php?date=${date}&time=${time}&guests=${guests}&pub_id=${pubId}`;
            const response = await fetch(url); // ยิงคำขอไปยังเซิร์ฟเวอร์ (Fetch data from server)
            const data = await response.json(); // แปลงผลรับที่ได้เป็นตัวแปรแบบ JSON (Parse response to JSON)
            
            // ถ้าเซิร์ฟเวอร์ส่ง error กลับมา (Handle error response from API)
            if (data.error) {
                console.error(data.error); // บันทึกข้อผิดพลาดใน console
                tablesLayer.innerHTML = `<p class="map-error text-red-500 text-center mt-10">${data.error}</p>`; // แสดงข้อผิดพลาดบนหน้าจอ (Show error wrapper)
                return;
            }

            // นำข้อมูล array โต๊ะไปวาดเป็นแผนผังโต๊ะ (Pass the tables array to render function)
            renderMap(data.tables);
        } catch (error) {
            // กรณีการร้องขอ API ล้มเหลวเครือข่ายมีปัญหา (Catch network or fetching errors)
            console.error('Error fetching tables:', error);
            tablesLayer.innerHTML = '<p class="map-error text-red-500 text-center mt-10">Error loading tables</p>';
        }
    }

    // ฟังก์ชันในการวาดรายชื่อโต๊ะลงแผนผัง (Function to render table elements on the map)
    function renderMap(tables) {
        tablesLayer.innerHTML = ''; // ล้างข้อมูลเก่าบนแผนผังทิ้ง (Clear previous table elements)

        // วนลูปข้อมูลโต๊ะทีละรายการ (Iterate through each table object)
        tables.forEach(table => {
            const el = document.createElement('div'); // สร้าง element ใหม่แบบ <div> (Create a new <div> element)
            
            // Ensure status matches CSS classes: available, reserved (for booked), incompatible
            // สลับสถานะของสคริปต์ให้ตรงตาม CSS Classes (Adjust status to match predefined CSS classes)
            let statusClass = table.status;
            if (statusClass === 'booked') statusClass = 'reserved'; // แปลง 'booked' เป็น 'reserved' (Map 'booked' to 'reserved' class)
            
            // ตั้งค่าชื่อคลาสสำหรับการแสดงผล CSS (Set element classes for styling)
            el.className = `table-marker ${statusClass}`;
            if(table.type === 'vip') el.classList.add('vip'); // เพิ่มความสวยงามหากเป็นคลาส VIP (Add 'vip' class if applicable)
            
            // ตกแต่างตำแหน่งตำแหน่งด้วยสไตล์ Left และ Top แกน X, Y (Position the element based on X/Y coordinates)
            el.style.left = table.coord_x + '%';
            el.style.top = table.coord_y + '%';
            el.textContent = table.number; // แสดงหมายเลขโต๊ะแทนที่ข้อความข้างใน (Set text to table number)

            // จัดการกรณีสถานะโต๊ะแตกต่างกัน (Handle different table statuses)
            if (table.status === 'available') {
                // ถ้าโต๊ะว่าง อนุญาตให้ผู้ใช้คลิกได้ (If table is available, make it clickable)
                el.addEventListener('click', () => selectTable(table, el));
            } else if (table.status === 'incompatible') {
                // โต๊ะคนเกินหรือเล็กไปสำหรับกลุ่มนี้ (Show tooltip if table capacity does not match guests)
                el.title = "Not suitable for your group size";
            } else {
                // โต๊ะถูกจองไปแล้ว (Show tooltip if table is already reserved)
                el.title = "Reserved";
            }

            // นำ element ที่สร้าง นำเข้าไปแสดงในพื้นที่แสดงผลหลัก (Append the element to the tables layer)
            tablesLayer.appendChild(el);
        });
    }

    // ฟังก์ชันจัดการจังหวะที่ผู้ใช้งานเลือกโต๊ะ (Function executed when a user selects an available table)
    function selectTable(table, element) {
        // Clear previous selection
        // ล้างคลาส "selected" ออกจากโต๊ะที่เคยเลือกเอาไว้ก่อนหน้า (Remove "selected" class from previously chosen tables)
        document.querySelectorAll('.table-marker.selected').forEach(el => el.classList.remove('selected'));
        
        // Select new
        // เพิ่มคลาส "selected" เพื่อให้หน้าจอแสดงผลได้ถูกต้องว่าอันไหนได้รับเลือก (Add "selected" class to newly chosen table element)
        element.classList.add('selected');
        
        // อัปเดตข้อความบนจอแสดงผลเช่น "Table T1 (VIP)" (Update display text with table number and type)
        selectedTableDisplay.innerText = `Table ${table.number} (${table.type.toUpperCase()})`;
        // กำหนดรหัสโต๊ะให้กับช่องล่องหนเพื่อส่งต่อไปหน้าชำระเงิน (Store the table ID in the hidden input for form submission)
        tableIdInput.value = table.id;
        // อนุญาตให้คลิกปุ่มชำระเงินได้ (Enable the submit button)
        submitBtn.disabled = false;
        // แสดงกล่องข้อมูลรวมการชำระเงินที่ถูกซ่อนไว้ (Show the selection info container)
        if(selectionInfo) selectionInfo.classList.remove('hidden');
    }
});
