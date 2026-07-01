// public/js/main.js
// Client-side interactions for Hotel Luna Azul MVC

document.addEventListener('DOMContentLoaded', function() {
    initLiveClock();
    
    // Auto-fetch rooms if dates are pre-filled on load (like in Edit mode)
    const checkIn = document.getElementById('fecha_ingreso');
    const checkOut = document.getElementById('fecha_salida');
    if (checkIn && checkOut && checkIn.value && checkOut.value) {
        // Only run if the dropdown is empty or in edit mode
        const roomSelect = document.getElementById('numero_habitacion');
        if (roomSelect && roomSelect.options.length <= 1) {
            fetchAvailableRooms();
        }
    }
});

// 1. Live Clock in Header
function initLiveClock() {
    const clockEl = document.getElementById('live-clock');
    if (!clockEl) return;

    setInterval(function() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        clockEl.textContent = `${day}/${month}/${year} ${hours}:${minutes}`;
    }, 10000); // Update every 10 seconds
}

// 2. Client-side Guest Form Validation
function validateGuestForm() {
    let isValid = true;
    clearFormErrors();

    const nombre = document.getElementById('nombre_completo');
    const edad = document.getElementById('edad');
    const tipoDoc = document.getElementById('tipo_documento');
    const numDoc = document.getElementById('numero_documento');
    const direccion = document.getElementById('direccion');
    const celular = document.getElementById('celular');
    const email = document.getElementById('email');
    const contacto = document.getElementById('contacto_emergencia');
    const parentesco = document.getElementById('parentesco_contacto');

    if (!nombre.value.trim()) {
        showInputError(nombre, 'El nombre completo es requerido.');
        isValid = false;
    }

    const edadVal = parseInt(edad.value, 10);
    if (isNaN(edadVal) || edadVal <= 0 || edadVal > 120) {
        showInputError(edad, 'Ingrese una edad válida (1 - 120).');
        isValid = false;
    }

    if (!tipoDoc.value) {
        showInputError(tipoDoc, 'Seleccione un tipo de documento.');
        isValid = false;
    }

    if (!numDoc.value.trim()) {
        showInputError(numDoc, 'El número de documento es requerido.');
        isValid = false;
    }

    if (!direccion.value.trim()) {
        showInputError(direccion, 'La dirección es requerida.');
        isValid = false;
    }

    const celRegex = /^[0-9+ \-]{7,15}$/;
    if (!celRegex.test(celular.value.trim())) {
        showInputError(celular, 'Ingrese un número de celular válido (mín. 7 dígitos).');
        isValid = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
        showInputError(email, 'Ingrese una dirección de correo válida.');
        isValid = false;
    }

    if (!contacto.value.trim()) {
        showInputError(contacto, 'El contacto de emergencia es requerido.');
        isValid = false;
    }

    if (!parentesco.value) {
        showInputError(parentesco, 'Seleccione el parentesco.');
        isValid = false;
    }

    return isValid;
}

// 3. Client-side Reservation Form Validation
function validateReservationForm() {
    let isValid = true;
    clearFormErrors();

    const checkIn = document.getElementById('fecha_ingreso');
    const checkOut = document.getElementById('fecha_salida');
    const guest = document.getElementById('guest_uuid');
    const room = document.getElementById('numero_habitacion');
    const numGuests = document.getElementById('numero_huespedes');

    if (!checkIn.value) {
        showInputError(checkIn, 'La fecha de check-in es requerida.');
        isValid = false;
    }

    if (!checkOut.value) {
        showInputError(checkOut, 'La fecha de check-out es requerida.');
        isValid = false;
    }

    if (checkIn.value && checkOut.value) {
        const d1 = new Date(checkIn.value + 'T00:00:00');
        const d2 = new Date(checkOut.value + 'T00:00:00');
        const today = new Date();
        today.setHours(0,0,0,0);

        if (d1 >= d2) {
            showInputError(checkOut, 'La fecha de salida debe ser posterior a la fecha de ingreso.');
            isValid = false;
        }

        // Only enforce today check in new reservation
        const isEdit = document.getElementById('reservation_uuid') !== null;
        if (!isEdit && d1 < today) {
            showInputError(checkIn, 'La fecha de ingreso no puede ser anterior a la fecha de hoy.');
            isValid = false;
        }
    }

    if (!guest.value) {
        showInputError(guest, 'Debe seleccionar un huésped.');
        isValid = false;
    }

    if (!room.value) {
        showInputError(room, 'Debe asignar una habitación disponible.');
        isValid = false;
    }

    const guestsCount = parseInt(numGuests.value, 10);
    if (isNaN(guestsCount) || guestsCount <= 0) {
        showInputError(numGuests, 'El número de huéspedes debe ser mayor a 0.');
        isValid = false;
    } else if (room.value) {
        const selectedOption = room.options[room.selectedIndex];
        if (selectedOption) {
            const maxCapacidad = parseInt(selectedOption.getAttribute('data-capacidad'), 10);
            if (maxCapacidad && guestsCount > maxCapacidad) {
                showInputError(numGuests, `Esta habitación solo permite alojar hasta ${maxCapacidad} persona(s).`);
                isValid = false;
            }
        }
    }

    return isValid;
}

// Helper to show inline input errors
function showInputError(inputEl, message) {
    inputEl.classList.add('input-error');
    const parent = inputEl.parentElement;
    
    let errorSpan = parent.querySelector('.error-text');
    if (!errorSpan) {
        errorSpan = document.createElement('span');
        errorSpan.className = 'error-text';
        parent.appendChild(errorSpan);
    }
    errorSpan.textContent = message;
}

function clearFormErrors() {
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    document.querySelectorAll('.error-text').forEach(el => el.remove());
}

// 4. AJAX: Fetch Available Rooms dynamically based on Dates
function fetchAvailableRooms() {
    const checkIn = document.getElementById('fecha_ingreso').value;
    const checkOut = document.getElementById('fecha_salida').value;
    const roomSelect = document.getElementById('numero_habitacion');
    const loadingMsg = document.getElementById('rooms-loading-msg');
    
    if (!roomSelect) return;
    if (!checkIn || !checkOut) return;

    // Simple client validation before request
    const d1 = new Date(checkIn);
    const d2 = new Date(checkOut);
    if (d1 >= d2) {
        roomSelect.innerHTML = '<option value="">La salida debe ser posterior al ingreso...</option>';
        return;
    }

    // Check if edit mode (has exclude UUID)
    const uuidEl = document.getElementById('reservation_uuid');
    const excludeUuid = uuidEl ? uuidEl.value : '';

    if (loadingMsg) loadingMsg.style.display = 'inline-block';
    
    const url = `index.php?controller=reservations&action=getAvailableRoomsAjax&check_in=${checkIn}&check_out=${checkOut}&exclude_uuid=${excludeUuid}`;

    fetch(url)
        .then(response => response.json())
        .then(rooms => {
            if (loadingMsg) loadingMsg.style.display = 'none';
            
            roomSelect.innerHTML = '';
            
            if (rooms.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '❌ No hay habitaciones libres en este periodo';
                roomSelect.appendChild(opt);
            } else {
                const optPlaceholder = document.createElement('option');
                optPlaceholder.value = '';
                optPlaceholder.textContent = 'Seleccione una habitación...';
                roomSelect.appendChild(optPlaceholder);

                rooms.forEach(room => {
                    const opt = document.createElement('option');
                    opt.value = room.numero_habitacion;
                    
                    let capacidadMax = 1;
                    if (room.tipo_habitacion === 'Sencilla') capacidadMax = 1;
                    else if (room.tipo_habitacion === 'Doble') capacidadMax = 2;
                    else if (room.tipo_habitacion === 'Suite') capacidadMax = 4;
                    else if (room.tipo_habitacion === 'Familiar') capacidadMax = 6;
                    
                    opt.setAttribute('data-capacidad', capacidadMax);
                    opt.textContent = `Habitación ${room.numero_habitacion} - ${room.tipo_habitacion} (Máx. ${capacidadMax} pers.)`;
                    roomSelect.appendChild(opt);
                });
            }
        })
        .catch(err => {
            if (loadingMsg) loadingMsg.style.display = 'none';
            console.error('Error fetching available rooms:', err);
            roomSelect.innerHTML = '<option value="">Error al cargar habitaciones...</option>';
        });
}

// 5. Native HTML Dialog + AJAX Quick Guest Registration
function openNewGuestDialog() {
    const dialog = document.getElementById('quick-guest-dialog');
    if (dialog) {
        dialog.showModal();
    }
}

function closeNewGuestDialog() {
    const dialog = document.getElementById('quick-guest-dialog');
    if (dialog) {
        dialog.close();
        document.getElementById('quick-guest-form').reset();
        document.getElementById('dialog-error').style.display = 'none';
    }
}

function submitQuickGuestForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('quick-guest-form');
    const formData = new FormData(form);
    const errorBox = document.getElementById('dialog-error');
    
    errorBox.style.display = 'none';
    errorBox.textContent = '';
    
    const url = 'index.php?controller=guests&action=create&ajax=1';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            // Guest created successfully! Add to select dropdown and select it
            const guestSelect = document.getElementById('guest_uuid');
            const newGuest = res.guest;
            
            const option = document.createElement('option');
            option.value = newGuest.uuid;
            option.textContent = `${newGuest.nombre_completo} (${newGuest.tipo_documento} ${newGuest.numero_documento})`;
            
            guestSelect.appendChild(option);
            guestSelect.value = newGuest.uuid; // Select the newly created guest
            
            // Close dialog
            closeNewGuestDialog();
        } else {
            // Show errors in dialog
            errorBox.style.display = 'block';
            let errorMsg = 'Errores de validación:\n';
            for (const key in res.errors) {
                errorMsg += `- ${res.errors[key]}\n`;
            }
            errorBox.innerText = errorMsg;
        }
    })
    .catch(err => {
        errorBox.style.display = 'block';
        errorBox.textContent = 'Error de conexión con el servidor al registrar el huésped.';
        console.error(err);
    });
}

// 6. Interactive Search Filters (Table Row Toggle)
function filterGuestsTable() {
    const searchVal = document.getElementById('guest-search').value.toLowerCase();
    const table = document.getElementById('guests-table');
    if (!table) return;

    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const nameCell = rows[i].querySelector('.guest-name-cell');
        const docCell = rows[i].querySelector('.guest-doc-cell');
        const emailCell = rows[i].querySelector('.guest-email-cell');
        
        let textMatch = false;
        
        if (nameCell && nameCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        if (docCell && docCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        if (emailCell && emailCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        
        if (textMatch) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

function filterReservationsTable() {
    const searchVal = document.getElementById('reservation-search').value.toLowerCase();
    const table = document.getElementById('reservations-table');
    if (!table) return;

    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const guestCell = rows[i].querySelector('.reservation-guest-cell');
        const roomCell = rows[i].querySelector('.badge-outline');
        const statusCell = rows[i].querySelector('.reservation-status-cell');
        
        let textMatch = false;
        
        if (guestCell && guestCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        if (roomCell && roomCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        if (statusCell && statusCell.textContent.toLowerCase().includes(searchVal)) textMatch = true;
        
        if (textMatch) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}
