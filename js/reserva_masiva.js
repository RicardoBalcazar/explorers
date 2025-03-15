document.addEventListener("DOMContentLoaded", function () {
    initPage();
});

function initPage() {
    configurarEstadoReserva();
    cargarClientes();
    cargarExperiencias();  // 🚀 Llama a cargarExperiencias y asegura el evento change
    cargarTarifas();
    configurarCheckOutDinamico();
    generarCodigoReserva();
}

// 📌 1. Configuración del color del estado de la reserva
function configurarEstadoReserva() {
    const estadoReservaSelect = document.getElementById("estado_reserva");
    const estadoIndicador = document.querySelector(".estado-indicador");

    if (!estadoReservaSelect || !estadoIndicador) {
        console.error("❌ Elementos de estado de reserva no encontrados.");
        return;
    }

    const coloresEstado = {
        "Reconfirmación": "#39B54A",
        "Confirmación": "#312783",
        "Bloqueo": "#FBBA00",
        "Lista de Espera": "#009FE3",
        "Cancelación": "#E30613",
        "Anulación": "#000000"
    };

    function actualizarColorEstado() {
        const estadoSeleccionado = estadoReservaSelect.value;
        estadoIndicador.style.backgroundColor = coloresEstado[estadoSeleccionado] || "transparent";
    }

    actualizarColorEstado();
    estadoReservaSelect.addEventListener("change", actualizarColorEstado);
}

// 📌 2. Cargar clientes
function cargarClientes() {
    fetch("api/get_clientes.php")
        .then(response => response.json())
        .then(clientes => {
            const selectCliente = document.getElementById("id_cliente");
            if (!selectCliente) return;

            clientes.forEach(cliente => {
                let option = new Option(cliente.nombre_cliente, cliente.id_cliente);
                selectCliente.add(option);
            });
        })
        .catch(error => console.error("❌ Error al cargar clientes:", error));
}

// 📌 3. Cargar experiencias y enlazar con check-out dinámico
function cargarExperiencias() {
    fetch("api/get_experiencias.php")
        .then(response => response.json())
        .then(experiencias => {
            const idExperiencia = document.getElementById("id_experiencia");
            if (!idExperiencia) return;

            experiencias.forEach(exp => {
                let option = new Option(`${exp.nombre_experiencia} (${exp.noches} noches)`, exp.id_experiencia);
                option.setAttribute("data-noches", exp.noches);
                idExperiencia.add(option);
            });

            // ✅ Asegurar que el evento change se active correctamente
            idExperiencia.addEventListener("change", calcularCheckOut);

            // ✅ Si el usuario ya tiene un check-in seleccionado, recalcular check-out al cargar
            calcularCheckOut();
        })
        .catch(error => console.error("❌ Error al cargar experiencias:", error));
}

// 📌 4. Cargar tarifas
function cargarTarifas() {
    fetch("api/get_tarifas.php")
        .then(response => response.json())
        .then(tarifas => {
            const selectTarifa = document.getElementById("id_tarifa");
            if (!selectTarifa) return;

            tarifas.forEach(tarifa => {
                let option = new Option(tarifa.nombre_tarifa, tarifa.id_tarifa);
                selectTarifa.add(option);
            });
        })
        .catch(error => console.error("❌ Error al cargar tarifas:", error));
}

// 📌 5. Función GLOBAL para calcular Check-Out (Movida afuera)
function calcularCheckOut() {
    const checkIn = document.getElementById("check_in");
    const checkOut = document.getElementById("check_out");
    const idExperiencia = document.getElementById("id_experiencia");

    if (!checkIn || !checkOut || !idExperiencia) {
        console.error("❌ Elementos de fecha no encontrados.");
        return;
    }

    let noches = idExperiencia.options[idExperiencia.selectedIndex]?.getAttribute("data-noches");
    let fechaCheckIn = checkIn.value;

    if (fechaCheckIn && noches && !isNaN(noches) && noches > 0) {
        let fecha = new Date(fechaCheckIn);
        fecha.setDate(fecha.getDate() + parseInt(noches));
        checkOut.value = fecha.toISOString().split('T')[0]; // Formato YYYY-MM-DD
        cargarHabitacionesDisponibles(); // Llamar a cargarHabitacionesDisponibles después de calcular el check-out
    } else {
        checkOut.value = "";
    }
}

// 📌 6. Configurar eventos para el cálculo de Check-Out
function configurarCheckOutDinamico() {
    const checkIn = document.getElementById("check_in");
    const idExperiencia = document.getElementById("id_experiencia");

    if (!checkIn || !idExperiencia) {
        console.error("❌ Elementos de fecha no encontrados.");
        return;
    }

    checkIn.addEventListener("change", calcularCheckOut);
    idExperiencia.addEventListener("change", calcularCheckOut);
}

// 📌 7. Generar Código de Reserva automáticamente
function generarCodigoReserva() {
    const codigoReservaInput = document.getElementById("codigo_reserva");
    if (!codigoReservaInput) {
        console.error("❌ Error: Campo de código de reserva no encontrado.");
        return;
    }

    // Evita regenerar si ya tiene un código (ej. en una edición)
    if (codigoReservaInput.value.trim() !== "") {
        console.log("🔹 Código de reserva ya presente, no se regenerará.");
        return;
    }

    fetch("api/generar_codigo_reserva.php")
        .then(response => response.json())
        .then(data => {
            if (data.codigo_reserva) {
                codigoReservaInput.value = data.codigo_reserva;
                console.log(`✅ Código de reserva generado: ${data.codigo_reserva}`);
            } else {
                console.error("❌ Error: No se recibió un código de reserva válido.");
            }
        })
        .catch(error => console.error("❌ Error al generar código de reserva:", error));
}

//CAPTURA LA FECHA PARA ACOLOCARLA
document.addEventListener("DOMContentLoaded", function () {
    function obtenerParametroURL(nombre) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(nombre);
    }

    function formatearFechaAmigable(fechaISO) {
        const meses = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        const partes = fechaISO.split("-");
        if (partes.length === 3) {
            const anio = partes[0];
            const mes = meses[parseInt(partes[1], 10) - 1];
            const dia = parseInt(partes[2], 10);
            return `${dia} de ${mes} del ${anio}`;
        }
        return "Fecha inválida";
    }

    function cargarFechaSeleccionada() {
        const fechaParametro = obtenerParametroURL("fecha");
        const spanFecha = document.getElementById("fecha-seleccionada");
        const inputCheckIn = document.getElementById("check_in");

        if (fechaParametro) {
            // Formatear y mostrar en el span
            spanFecha.textContent = formatearFechaAmigable(fechaParametro);

            // Asignar la fecha al input de tipo date
            inputCheckIn.value = fechaParametro;
        } else {
            spanFecha.textContent = "Ninguna";
        }
    }

    cargarFechaSeleccionada();
});
//fin captura de fecha

//CREAR HABITACIONES
function cargarHabitacionesDisponibles() {
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;

    if (!checkIn || !checkOut) {
        console.log('Check-in o check-out no seleccionados.');
        return;
    }

    console.log('Enviando solicitud AJAX para obtener habitaciones reservadas...');
    fetch('php/obtener_hab_reservadas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `check_in=${checkIn}&check_out=${checkOut}`
    })
    .then(response => response.json())
    .then(habitacionesReservadas => {
        console.log('Habitaciones reservadas recibidas:', habitacionesReservadas);

        fetch("api/get_hab.php")
            .then(response => response.json())
            .then(habitaciones => {
                const contenedorHabitaciones = document.getElementById("contenedor-habitaciones");
                contenedorHabitaciones.innerHTML = ""; // Limpiar antes de agregar

                habitaciones.forEach((cantidad_disponible, index) => {
                    let fila = document.createElement("div");
                    fila.classList.add("habitacion-row");
                    fila.setAttribute("data-id", index + 1);
                    fila.innerHTML = `
                        <input type="checkbox" name="habitaciones[]" class="habitacion-checkbox">
                        <span>Hab ${cantidad_disponible}</span>
                        <select name="tipo_habitacion[]" class="tipo-hab" data-id="${index + 1}"></select>
                        <select name="variante_habitacion[]" class="variante-hab" data-id="${index + 1}"></select>
                        <select name="adultos[]" class="adultos">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                        <select name="ninos[]" class="ninos">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                        <select name="infantes[]" class="infantes">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                        <select name="tc[]" class="tc">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    `;

                    const checkbox = fila.querySelector('.habitacion-checkbox');
                    if (habitacionesReservadas.includes(index + 1)) {
                        checkbox.disabled = true;
                        fila.classList.add('reservada');
                    }

                    contenedorHabitaciones.appendChild(fila);
                });
                cargarTipos();
            })
            .catch(error => console.error("❌ Error al obtener habitaciones:", error));
    })
    .catch(error => {
        console.error('Error al obtener habitaciones reservadas:', error);
    });
}

function cargarTipos() {
    fetch("api/get_tipo.php")
        .then(response => response.json())
        .then(tipos => {
            document.querySelectorAll(".tipo-hab").forEach(select => {
                select.innerHTML = "<option value=''>Seleccionar</option>";
                tipos.forEach(tipo => {
                    let option = new Option(tipo.nombre_tipo, tipo.id_tipo_habitacion);
                    select.add(option);
                });
                
                select.addEventListener("change", function () {
                    cargarVariantes(this); // Pasa el select actual a la función cargarVariantes
                });
            });
        })
        .catch(error => console.error("❌ Error al obtener tipos de habitación:", error));
}

function cargarVariantes(selectTipo) {
    let idTipo = selectTipo.value;
    let fila = selectTipo.closest('.habitacion-row');
    let selectVariante = fila.querySelector('.variante-hab');

    if (!idTipo) {
        selectVariante.innerHTML = "<option value=''>Seleccione un tipo primero...</option>";
        return;
    }

    fetch(`api/get_variante.php?id_tipo_habitacion=${idTipo}`)
        .then(response => response.json())
        .then(variantes => {
            selectVariante.innerHTML = "";
            if (variantes.length === 0) {
                selectVariante.innerHTML = "<option value=''>❌ No hay variantes disponibles</option>";
            } else {
                variantes.forEach(variante => {
                    let option = new Option(variante.nombre_variante, variante.id_variante);
                    selectVariante.add(option);
                });
            }
        })
        .catch(error => console.error("❌ Error al obtener variantes de habitación:", error));
}

// Llama a la función cargarHabitaciones para inicializar el proceso
cargarHabitacionesDisponibles();
//fin crear habitaciones

//guardar resreva
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.btn-guardar-masiva').addEventListener('click', function(event) {
        event.preventDefault();
        guardarReservaMasiva();
    });
});

function guardarReservaMasiva() {
    const form = document.getElementById('form-editar-reserva');
    const formData = new FormData(form);

    // Obtener el mes y el año del check-in para la redirección
    const checkInDate = new Date(formData.get('check_in'));
    const month = checkInDate.getMonth() + 1; // getMonth() devuelve 0-11
    const year = checkInDate.getFullYear();

    // Clear previous habitacion data from formData
    formData.delete('habitaciones[]');
    formData.delete('tipo_habitacion[]');
    formData.delete('variante_habitacion[]');
    formData.delete('adultos[]');
    formData.delete('ninos[]');
    formData.delete('infantes[]');
    formData.delete('tc[]');

    // Add only selected habitaciones data
    document.querySelectorAll('.habitacion-row').forEach(row => {
        const checkbox = row.querySelector('.habitacion-checkbox');
        if (checkbox.checked) {
            const idCantHab = row.getAttribute('data-id');
            const tipoHab = row.querySelector('.tipo-hab').value;
            const varianteHab = row.querySelector('.variante-hab').value;
            const adultos = row.querySelector('.adultos').value;
            const ninos = row.querySelector('.ninos').value;
            const infantes = row.querySelector('.infantes').value;
            const tc = row.querySelector('.tc').value;

            formData.append('habitaciones[]', idCantHab);
            formData.append('tipo_habitacion[]', tipoHab);
            formData.append('variante_habitacion[]', varianteHab);
            formData.append('adultos[]', adultos);
            formData.append('ninos[]', ninos);
            formData.append('infantes[]', infantes);
            formData.append('tc[]', tc);
        }
    });

    // Log the formData contents
    console.log('Datos del formulario a enviar:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    fetch('php/guardar_reserva_masiva.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text()) // Change to response.text() to inspect the raw response
    .then(data => {
        console.log('Raw response:', data); // Log the raw response
        try {
            const jsonData = JSON.parse(data);
            if (jsonData.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: jsonData.message,
                    icon: 'success'
                }).then(() => {
                    window.location.href = `index.php?month=${month}&year=${year}`;
                });
            } else {
                console.error('Error en la respuesta del servidor:', jsonData.message);
                Swal.fire('Error', jsonData.message, 'error');
            }
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            Swal.fire('Error', 'Respuesta inesperada del servidor', 'error');
        }
    })
    .catch(error => {
        console.error('Error al enviar la solicitud:', error);
        Swal.fire('Error', 'Error al enviar la solicitud', 'error');
    });
}

//OBTENER RESERVAS Y PINTARLAS EN LAS HABITACIONES
function cargarHabitacionesDisponibles() {
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;

    if (!checkIn || !checkOut) {
        console.log('Check-in o check-out no seleccionados.');
        return;
    }

    console.log('Enviando solicitud AJAX para obtener habitaciones reservadas...');
    fetch('php/obtener_hab_reservadas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `check_in=${checkIn}&check_out=${checkOut}`
    })
    .then(response => response.json())
    .then(habitacionesReservadas => {
        console.log('Habitaciones reservadas recibidas:', habitacionesReservadas);

        fetch("api/get_hab.php")
            .then(response => response.json())
            .then(habitaciones => {
                const contenedorHabitaciones = document.getElementById("contenedor-habitaciones");
                contenedorHabitaciones.innerHTML = ""; // Limpiar antes de agregar

                habitaciones.forEach((cantidad_disponible, index) => {
                    let fila = document.createElement("div");
                    fila.classList.add("habitacion-row");
                    fila.setAttribute("data-id", index + 1);
                    fila.innerHTML = `
                        <input type="checkbox" name="habitaciones[]" class="habitacion-checkbox">
                        <span>Hab ${cantidad_disponible}</span>
                        <select name="tipo_habitacion[]" class="tipo-hab" data-id="${index + 1}"></select>
                        <select name="variante_habitacion[]" class="variante-hab" data-id="${index + 1}"></select>
                        <select name="adultos[]" class="adultos">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                        <select name="ninos[]" class="ninos">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                        <select name="infantes[]" class="infantes">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                        <select name="tc[]" class="tc">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                        <button type="button" class="btn-abrir-modal" data-id="${index + 1}">Agregar Pasajeros</button>
                        
                    `;

                    const checkbox = fila.querySelector('.habitacion-checkbox');
                    if (habitacionesReservadas.includes(index + 1)) {
                        checkbox.disabled = true;
                        fila.classList.add('reservada');
                    }

                    contenedorHabitaciones.appendChild(fila);
                });
                cargarTipos();
            })
            .catch(error => console.error("❌ Error al obtener habitaciones:", error));
    })
    .catch(error => {
        console.error('Error al obtener habitaciones reservadas:', error);
    });
}
//FIN OBTENER RESERVAS Y PINTARLAS EN LAS HABITACIONES

//MODAL PASAJEROS

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById("modalPasajeros");
    const span = document.getElementsByClassName("close")[0];
    const pasajerosContainer = document.getElementById("pasajerosContainer");
    let pasajerosData = {}; // Variable temporal para guardar los datos de los pasajeros
    let listaPaises = []; // Variable para almacenar los países

    // Función para rellenar el formulario de pasajeros con datos guardados
    function rellenarFormularioPasajeros(tipo, index, data) {
        return `
            <div class="pasajero-form">
                <h3>${tipo.charAt(0).toUpperCase() + tipo.slice(1)} ${index}</h3>
                <div class="form-row">
                    <div class="form-group"><label>Nombres: <input type="text" name="${tipo}_nombres[]" value="${data[`${tipo}_nombres[]`] || ''}" required></label></div>
                    <div class="form-group"><label>Apellidos: <input type="text" name="${tipo}_apellidos[]" value="${data[`${tipo}_apellidos[]`] || ''}" required></label></div>
                    <div class="form-group"><label>Pasaporte: <input type="text" name="${tipo}_pasaporte[]" value="${data[`${tipo}_pasaporte[]`] || ''}" required></label></div>
                    <div class="form-group"><label>Fecha de Nacimiento: <input type="date" name="${tipo}_fecha_nacimiento[]" value="${data[`${tipo}_fecha_nacimiento[]`] || ''}" required></label></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>País: <select name="${tipo}_pais[]" required>${getPaisesOptions(data[`${tipo}_pais[]`])}</select></label></div>
                    <div class="form-group"><label>Género: 
                        <select name="${tipo}_genero[]" required>
                            <option value="Masculino" ${data[`${tipo}_genero[]`] === 'Masculino' ? 'selected' : ''}>Masculino</option>
                            <option value="Femenino" ${data[`${tipo}_genero[]`] === 'Femenino' ? 'selected' : ''}>Femenino</option>
                        </select>
                    </label></div>
                    <div class="form-group"><label>Alimentación: 
                        <select name="${tipo}_alimentacion[]" required>
                            <option value="Normal" ${data[`${tipo}_alimentacion[]`] === 'Normal' ? 'selected' : ''}>Normal</option>
                            <option value="Vegano" ${data[`${tipo}_alimentacion[]`] === 'Vegano' ? 'selected' : ''}>Vegano</option>
                        </select>
                    </label></div>
                    <div class="form-group"><label>Número de Vuelo: <input type="text" name="${tipo}_numero_vuelo[]" value="${data[`${tipo}_numero_vuelo[]`] || ''}" required></label></div>
                    <div class="form-group"><label>Fecha y Hora de Llegada: <input type="datetime-local" name="${tipo}_fecha_hora_llegada[]" value="${data[`${tipo}_fecha_hora_llegada[]`] || ''}" required></label></div>
                    <div class="form-group"><label>Fecha y Hora de Salida: <input type="datetime-local" name="${tipo}_fecha_hora_salida[]" value="${data[`${tipo}_fecha_hora_salida[]`] || ''}" required></label></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Información adicional: <textarea name="${tipo}_informacion_adicional[]">${data[`${tipo}_informacion_adicional[]`] || ''}</textarea></label></div>
                
                    <!-- 🔹 Experiencias Adicionales (Checkbox) -->
                    <div class="adicionales-container">
                        <label><strong>Experiencias Adicionales:</strong></label>
                        <div id="experiencias_adicionales_${index}" class="experiencias-adicionales">
                            ${listaExperienciasAd.map(exp => `
                                <label class="check_pas">
                                    <input type="checkbox" name="pasajero[${index}][experiencias_adicionales][]" value="${exp.id_experiencia_adicional}" ${data[`pasajero[${index}][experiencias_adicionales][]`] && data[`pasajero[${index}][experiencias_adicionales][]`].includes(exp.id_experiencia_adicional) ? 'checked' : ''}>
                                    ${exp.nombre_experiencia} - $${exp.precio}
                                </label>
                            `).join("")}
                        </div>
                    </div>

                    <!-- 🔹 Costos Adicionales (Checkbox) -->
                    <div class="adicionales-container">
                        <label><strong>Costos Adicionales:</strong></label>
                        <div id="costos_adicionales_${index}" class="costos-adicionales">
                            ${listaCostosAd.map(costo => `
                                <label>
                                    <input type="checkbox" name="pasajero[${index}][costos_adicionales][]" value="${costo.id_adicional_otros}" ${data[`pasajero[${index}][costos_adicionales][]`] && data[`pasajero[${index}][costos_adicionales][]`].includes(costo.id_adicional_otros) ? 'checked' : ''}>
                                    ${costo.nombre_adicional} - $${costo.precio}
                                </label>
                            `).join("")}
                        </div>
                    </div>

                    <div class="otro-adicional">
                        <h4>Otro Adicional</h4>
                        <div class="form-group"><label>Nombre: <input type="text" name="${tipo}_otro_nombre[]" value="${data[`${tipo}_otro_nombre[]`] || ''}"></label></div>
                        <div class="form-group"><label>Precio: <input type="number" name="${tipo}_otro_precio[]" value="${data[`${tipo}_otro_precio[]`] || ''}"></label></div>
                    </div>
                </div>
            </div>
        `;
    }

    // Función para cargar el formulario de pasajeros
    function cargarFormularioPasajeros(habitacionId) {
        pasajerosContainer.innerHTML = ''; // Limpiar el contenedor

        // Verificar si ya existen datos guardados para la habitación
        if (pasajerosData[habitacionId]) {
            const pasajeros = pasajerosData[habitacionId];
            Object.keys(pasajeros).forEach((key) => {
                pasajeros[key].forEach((value, i) => {
                    pasajerosContainer.innerHTML += rellenarFormularioPasajeros(key, i + 1, value);
                });
            });
        } else {
            // Obtener los valores seleccionados
            const fila = document.querySelector(`.habitacion-row[data-id="${habitacionId}"]`);
            const adultos = fila.querySelector('.adultos').value;
            const ninos = fila.querySelector('.ninos').value;
            const infantes = fila.querySelector('.infantes').value;
            const tc = fila.querySelector('.tc').value;

            // Crear formularios dinámicamente
            for (let i = 0; i < adultos; i++) {
                pasajerosContainer.innerHTML += crearFormularioPasajero('adulto', i + 1);
            }
            for (let i = 0; i < ninos; i++) {
                pasajerosContainer.innerHTML += crearFormularioPasajero('niño', i + 1);
            }
            for (let i = 0; i < infantes; i++) {
                pasajerosContainer.innerHTML += crearFormularioPasajero('infante', i + 1);
            }
            for (let i = 0; i < tc; i++) {
                pasajerosContainer.innerHTML += crearFormularioPasajero('tc', i + 1);
            }
        }
    }

    // Función para crear el formulario de pasajeros
    function crearFormularioPasajero(tipo, index, data = {}) {
        return `
            <div class="pasajero-form">
                <h3>${tipo.charAt(0).toUpperCase() + tipo.slice(1)} ${index}</h3>
                <div class="form-row">
                <div class="form-group"><label>Nombres: <input type="text" name="${tipo}_nombres[]" value="${data[`${tipo}_nombres[]`] || ''}" required></label></div>
                <div class="form-group"><label>Apellidos: <input type="text" name="${tipo}_apellidos[]" value="${data[`${tipo}_apellidos[]`] || ''}" required></label></div>
                <div class="form-group"><label>Pasaporte: <input type="text" name="${tipo}_pasaporte[]" value="${data[`${tipo}_pasaporte[]`] || ''}" required></label></div>
                <div class="form-group"><label>Fecha de Nacimiento: <input type="date" name="${tipo}_fecha_nacimiento[]" value="${data[`${tipo}_fecha_nacimiento[]`] || ''}" required></label></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>País: <select name="${tipo}_pais[]" required>${getPaisesOptions(data[`${tipo}_pais[]`])}</select></label></div>
                <div class="form-group"><label>Género: 
                    <select name="${tipo}_genero[]" required>
                        <option value="Masculino" ${data[`${tipo}_genero[]`] === 'Masculino' ? 'selected' : ''}>Masculino</option>
                        <option value="Femenino" ${data[`${tipo}_genero[]`] === 'Femenino' ? 'selected' : ''}>Femenino</option>
                    </select>
                </label></div>
                <div class="form-group"><label>Alimentación: 
                    <select name="${tipo}_alimentacion[]" required>
                        <option value="Normal" ${data[`${tipo}_alimentacion[]`] === 'Normal' ? 'selected' : ''}>Normal</option>
                        <option value="Vegano" ${data[`${tipo}_alimentacion[]`] === 'Vegano' ? 'selected' : ''}>Vegano</option>
                    </select>
                </label></div>
                <div class="form-group"><label>Número de Vuelo: <input type="text" name="${tipo}_numero_vuelo[]" value="${data[`${tipo}_numero_vuelo[]`] || ''}" required></label></div>
                <div class="form-group"><label>Fecha y Hora de Llegada: <input type="datetime-local" name="${tipo}_fecha_hora_llegada[]" value="${data[`${tipo}_fecha_hora_llegada[]`] || ''}" required></label></div>
                <div class="form-group"><label>Fecha y Hora de Salida: <input type="datetime-local" name="${tipo}_fecha_hora_salida[]" value="${data[`${tipo}_fecha_hora_salida[]`] || ''}" required></label></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Información adicional: <textarea name="${tipo}_informacion_adicional[]">${data[`${tipo}_informacion_adicional[]`] || ''}</textarea></label></div>
            
                <!-- 🔹 Experiencias Adicionales (Checkbox) -->
                <div class="adicionales-container">
                    <label><strong>Experiencias Adicionales:</strong></label>
                    <div id="experiencias_adicionales_${index}" class="experiencias-adicionales">
                        ${listaExperienciasAd.map(exp => `
                            <label class="check_pas">
                                <input type="checkbox" name="pasajero[${index}][experiencias_adicionales][]" value="${exp.id_experiencia_adicional}" ${data[`pasajero[${index}][experiencias_adicionales][]`] && data[`pasajero[${index}][experiencias_adicionales][]`].includes(exp.id_experiencia_adicional) ? 'checked' : ''}>
                                ${exp.nombre_experiencia} - $${exp.precio}
                            </label>
                        `).join("")}
                    </div>
                </div>

                <!-- 🔹 Costos Adicionales (Checkbox) -->
                <div class="adicionales-container">
                    <label><strong>Costos Adicionales:</strong></label>
                    <div id="costos_adicionales_${index}" class="costos-adicionales">
                        ${listaCostosAd.map(costo => `
                            <label>
                                <input type="checkbox" name="pasajero[${index}][costos_adicionales][]" value="${costo.id_adicional_otros}" ${data[`pasajero[${index}][costos_adicionales][]`] && data[`pasajero[${index}][costos_adicionales][]`].includes(costo.id_adicional_otros) ? 'checked' : ''}>
                                ${costo.nombre_adicional} - $${costo.precio}
                            </label>
                        `).join("")}
                    </div>
                </div>

                <div class="otro-adicional">
                    <h4>Otro Adicional</h4>
                    <div class="form-group"><label>Nombre: <input type="text" name="${tipo}_otro_nombre[]" value="${data[`${tipo}_otro_nombre[]`] || ''}"></label></div>
                    <div class="form-group"><label>Precio: <input type="number" name="${tipo}_otro_precio[]" value="${data[`${tipo}_otro_precio[]`] || ''}"></label></div>
                </div>
            </div>
            </div>
        `;
    }

    // Función para obtener las opciones de países
    function getPaisesOptions(selectedPais) {
        return listaPaises.map(pais => `<option value="${pais.id_pais}" ${selectedPais === pais.id_pais ? 'selected' : ''}>${pais.nombre_pais}</option>`).join("");
    }

    // ✅ Cargar experiencias adicionales desde get_experiencias_ad.php
    function cargarExperienciasAd() {
        fetch("api/get_experiencias_ad.php")
            .then(response => response.json())
            .then(experiencias => {
                listaExperienciasAd = experiencias;
                console.log("✅ Experiencias Adicionales cargadas:", listaExperienciasAd);
            })
            .catch(error => console.error("❌ Error al obtener experiencias adicionales:", error));
    }

    // ✅ Cargar costos adicionales desde get_costos_ad.php
    function cargarCostosAd() {
        fetch("api/get_costos_ad.php")
            .then(response => response.json())
            .then(costos => {
                listaCostosAd = costos;
                console.log("✅ Costos Adicionales cargados:", listaCostosAd);
            })
            .catch(error => console.error("❌ Error al obtener costos adicionales:", error));
    }

    // ✅ Cargar países desde get_paises.php
    function cargarPaises() {
        fetch("api/get_paises.php")
            .then(response => response.json())
            .then(paises => {
                listaPaises = paises;
                console.log("✅ Países cargados:", listaPaises);
            })
            .catch(error => console.error("❌ Error al obtener países:", error));
    }

    // Llama a las funciones para cargar experiencias adicionales, costos privados y países
    cargarExperienciasAd();
    cargarCostosAd();
    cargarPaises();

    // Escuchar clics en los botones para abrir el modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-abrir-modal')) {
            const habitacionId = event.target.getAttribute('data-id');
            modal.style.display = "block";
            cargarFormularioPasajeros(habitacionId);
        }
    });

    // Guardar los datos de los pasajeros al hacer clic en "Guardar" en el modal
    document.getElementById('btnGuardarPasajeros').addEventListener('click', function() {
        const habitacionId = document.querySelector('.habitacion-row[data-id]').getAttribute('data-id');
        const formData = new FormData(document.getElementById('formPasajeros'));
        const pasajeros = {};
        formData.forEach((value, key) => {
            if (!pasajeros[key]) {
                pasajeros[key] = [];
            }
            pasajeros[key].push(value);
        });
        pasajerosData[habitacionId] = pasajeros;
        console.log('Datos de los pasajeros guardados:', pasajerosData); // Console log para verificar los datos
        modal.style.display = "none";
    });

    // Cerrar el modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

//FIN MODAL PASAJEROS