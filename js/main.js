document.addEventListener("DOMContentLoaded", function () {
    const fechaActual = new Date();
    const mesActual = fechaActual.getMonth() + 1; // getMonth() devuelve 0 para enero, sumamos 1
    const anioActual = fechaActual.getFullYear();

    generarCalendario(mesActual, anioActual);

    // ✅ Asegurar que la función está definida antes de llamarla
    if (typeof configurarFiltrosBusqueda === "function") {
        configurarFiltrosBusqueda();
    } else {
        console.error("❌ Error: 'configurarFiltrosBusqueda' no está definida.");
    }
});

    // ✅ Función para generar el calendario con el mes y año dados
    function generarCalendario(mes, anio) {
        const contenedorCalendario = document.getElementById("calendario-container");

        // 🔥 SOLUCIÓN: Asegurar que el contenedor esté completamente vacío antes de agregar un nuevo calendario
        contenedorCalendario.innerHTML = "";

        let diasDelMes = new Date(anio, mes, 0).getDate(); // Obtener la cantidad de días del mes

        // Crear la tabla del calendario
        let tabla = document.createElement("table");
        tabla.classList.add("calendario-mes");

        // Generar la cabecera con los días del mes
        let thead = document.createElement("thead");
        let filaEncabezado = document.createElement("tr");
        filaEncabezado.innerHTML = `<th>Habitación</th>` + 
            Array.from({ length: diasDelMes }, (_, i) => `<th class='dia_fecha'>${i + 1}</th>`).join('');
        thead.appendChild(filaEncabezado);
        tabla.appendChild(thead);

        // Crear el cuerpo de la tabla con las habitaciones
        let tbody = document.createElement("tbody");

        fetch("php/get_habitaciones.php")
            .then(response => response.json())
            .then(habitaciones => {
                // 🔥 SOLUCIÓN: Asegurar que el cuerpo de la tabla está vacío antes de llenarlo
                tbody.innerHTML = "";

                habitaciones.forEach(hab => {
                    let fila = document.createElement("tr");
                    fila.innerHTML = `<td class='hab-nombre'>Hab. ${hab}</td>` + 
                        Array.from({ length: diasDelMes }, (_, i) => `<td class='reserva-cell' data-hab='${hab}' data-dia='${i + 1}'></td>`).join('');
                    tbody.appendChild(fila);
                });

                tabla.appendChild(tbody);
                contenedorCalendario.appendChild(tabla);

                fetchReservas(mes, anio); // ✅ Obtener reservas del mes actual
            })
            .catch(error => console.error("❌ Error al obtener habitaciones:", error));
    }

// ✅ Función para obtener reservas filtradas por mes/año y pintar en el calendario
function fetchReservas(mes, anio) {
    fetch(`php/get_reservas.php?mes=${mes}&anio=${anio}`)
        .then(response => response.json())
        .then(data => {
            if (!data || Object.keys(data).length === 0) {
                console.warn(`⚠️ No hay reservas para ${mes}/${anio}.`);
                return;
            }

            // 🔹 Limpiar celdas antes de pintar nuevas reservas
            document.querySelectorAll(".reserva-cell").forEach(cell => {
                cell.style.backgroundColor = "";
                cell.innerHTML = "";
                cell.classList.remove("bloqueado");
                cell.style.pointerEvents = "auto"; // Reactivar celdas
            });

            // 🔥 Recorrer todas las reservas recibidas
            Object.entries(data).forEach(([idReserva, reservaInfo]) => {
                if (!reservaInfo.habitaciones || !Array.isArray(reservaInfo.habitaciones)) {
                    return;
                }

                reservaInfo.habitaciones.forEach(habitacion => {
                    let checkInDate = new Date(reservaInfo.check_in + "T00:00:00Z");
                    let checkOutDate = new Date(reservaInfo.check_out + "T00:00:00Z");

                    if (isNaN(checkInDate) || isNaN(checkOutDate)) {
                        console.warn(`⚠️ Fechas inválidas en reserva ${idReserva}: ${reservaInfo.check_in} - ${reservaInfo.check_out}`);
                        return;
                    }

                    for (let date = new Date(checkInDate); date <= checkOutDate; date.setUTCDate(date.getUTCDate() + 1)) {
                        let dia = date.getUTCDate();
                        let mesReserva = date.getUTCMonth() + 1;
                        let anioReserva = date.getUTCFullYear();

                        // 🔥 Filtrar SOLO reservas del mes/año seleccionado
                        if (mesReserva !== mes || anioReserva !== anio) {
                            continue;
                        }

                        let celda = document.querySelector(`.reserva-cell[data-hab='${habitacion}'][data-dia='${dia}']`);
                        if (!celda) {
                            continue;
                        }

                        let colorCliente = reservaInfo.color_cliente.startsWith("#") ? reservaInfo.color_cliente : "#" + reservaInfo.color_cliente;
                        let colorEstado = obtenerColorEstado(reservaInfo.estado_reserva); // 🔥 Obtener color del estado

                        // 🔒 Bloquear celda para evitar reservas manuales
                        celda.classList.add("bloqueado");
                        celda.style.pointerEvents = "none";
                        celda.style.backgroundColor = colorCliente;
                        celda.style.color = "white";
                        celda.style.fontWeight = "bold";
                        celda.style.fontSize = "10px";
                        celda.innerHTML = `
                            <span>${reservaInfo.codigo_reserva}</span>
                            <span class="estado-indicador" data-id-reserva="${idReserva}" 
                                  style="background-color: ${colorEstado}; border: 2px solid white; 
                                         width: 20px; height: 20px; display: inline-block; 
                                         border-radius: 50%; margin-left: 5px; pointer-events: auto;">
                            </span>
                        `;
                    }
                });
            });

            
// ✅ Función para activar tooltips en los círculos de estado y cargar detalles dinámicamente
function activarTooltips() {
    const tooltip = document.getElementById("tooltip-reserva");

    if (!tooltip) {
        console.error("❌ Error: 'tooltip-reserva' no encontrado en el DOM.");
        return;
    }

    // ✅ Eliminar eventos previos para evitar múltiples ejecuciones
    document.querySelectorAll(".estado-indicador").forEach(el => {
        el.removeEventListener("mouseover", mostrarTooltip);
        el.removeEventListener("mouseout", ocultarTooltip);
    });

    // ✅ Agregar eventos nuevamente
    document.querySelectorAll(".estado-indicador").forEach(el => {
        el.addEventListener("mouseover", mostrarTooltip);
        el.addEventListener("mouseout", ocultarTooltip);
    });

    function mostrarTooltip(event) {
        const idReserva = event.target.getAttribute("data-id-reserva");

        fetch(`php/get_reserva_detalle.php?id_reserva=${idReserva}`)
            .then(response => response.json())
            .then(reserva => {
                if (reserva.error) {
                    return;
                }

                // ✅ Insertar los datos en el tooltip
                document.getElementById("tooltip-codigo").innerText = reserva.codigo_reserva || "N/A";
                document.getElementById("tooltip-cliente").innerText = reserva.nombre_cliente || "Sin Cliente";
                document.getElementById("tooltip-estado").innerText = reserva.estado_reserva || "Sin Estado";
                document.getElementById("tooltip-checkin").innerText = reserva.check_in || "No definido";
                document.getElementById("tooltip-checkout").innerText = reserva.check_out || "No definido";
                document.getElementById("tooltip-usuario").innerText = reserva.usuario_creacion || "No asignado";
                
                // Evitar undefined en habitaciones
                let habitacionesHTML = reserva.habitaciones.map(hab => `
                    <div class="tooltip-habitacion">
                        <strong>${hab.tipo_habitacion || "Tipo desconocido"}</strong>
                        adulto ${hab.adultos || 0} | niño ${hab.ninos || 0} | infante ${hab.infantes || 0} | TC 0
                    </div>
                `).join('');
                document.getElementById("tooltip-habitaciones").innerHTML = habitacionesHTML;
                

                // ✅ Mostrar el tooltip y posicionarlo
                tooltip.classList.remove("tooltip-hidden");
                tooltip.classList.add("tooltip-visible");

                const rect = event.target.getBoundingClientRect();
                tooltip.style.top = `${rect.top + window.scrollY - tooltip.offsetHeight - 10}px`;
                tooltip.style.left = `${rect.left + window.scrollX + rect.width / 2 - tooltip.offsetWidth / 2}px`;
            })
            .catch(error => console.error("❌ Error al obtener detalles de la reserva:", error));
    }

    function ocultarTooltip() {
        setTimeout(() => {
            if (!tooltip.matches(":hover")) {
                tooltip.classList.remove("tooltip-visible");
                tooltip.classList.add("tooltip-hidden");
            }
        }, 300);
    }

    tooltip.addEventListener("mouseleave", function () {
        tooltip.classList.remove("tooltip-visible");
        tooltip.classList.add("tooltip-hidden");
    });
}


            // ✅ SOLO ACTIVAR TOOLTIP SI EXISTE LA FUNCIÓN Y HAY RESERVAS
            if (typeof activarTooltips === "function") {
                activarTooltips();
            }
        })
        .catch(error => console.error("❌ Error al obtener reservas:", error));
}

// ✅ Función para obtener color del estado de la reserva
function obtenerColorEstado(estado) {
    const coloresEstados = {
        "Reconfirmación": "#39B54A",
        "Confirmación": "#312783",
        "Bloqueo": "#FBBA00",
        "Lista de Espera": "#009FE3",
        "Cancelación": "#E30613",
        "Anulación": "#000000"
    };
    return coloresEstados[estado] || "#000"; // 🔥 Retorna color específico o negro por defecto
}

// ✅ CONFIGURAR FILTROS DE BÚSQUEDA (MODAL)
function configurarFiltrosBusqueda() {
    const btnAbrirFiltro = document.getElementById("btn-filtros");
    const modalFiltro = document.getElementById("modal-filtro");
    const btnCerrarFiltro = document.getElementById("cerrar-filtro");
    const btnAplicarFiltros = document.getElementById("btn-aplicar-filtros");

    if (!btnAbrirFiltro || !modalFiltro || !btnCerrarFiltro || !btnAplicarFiltros) {
        console.error("❌ Error: No se encontraron elementos del modal.");
        return;
    }

    // ✅ ABRIR MODAL
    btnAbrirFiltro.addEventListener("click", function () {
        modalFiltro.classList.add("modal-activo");
    });

    // ✅ CERRAR MODAL
    btnCerrarFiltro.addEventListener("click", function () {
        modalFiltro.classList.remove("modal-activo");
    });

    // ✅ APLICAR FILTROS
    btnAplicarFiltros.addEventListener("click", function () {
        const mesSeleccionado = parseInt(document.getElementById("select-mes").value);
        const anioSeleccionado = parseInt(document.getElementById("select-anio").value);

        console.log(`🔍 Aplicando filtros: Mes ${mesSeleccionado}, Año ${anioSeleccionado}`);

        // 🔥 SOLUCIÓN: Asegurar que el calendario se borre antes de regenerarlo
        document.getElementById("calendario-container").innerHTML = "";

        generarCalendario(mesSeleccionado, anioSeleccionado);
        modalFiltro.classList.remove("modal-activo"); // Cierra el modal tras aplicar los filtros
    });
}

//fin consumo de reservas toolpip

//HEADER

// ✅ Mostrar fecha y hora en vivo
function actualizarFechaHora() {
    const fechaElement = document.getElementById("fecha-actual");
    const horaElement = document.getElementById("hora-actual");
    
    const ahora = new Date();
    const opcionesFecha = { year: 'numeric', month: 'long', day: 'numeric' };
    const opcionesHora = { hour: '2-digit', minute: '2-digit', second: '2-digit' };

    fechaElement.textContent = ahora.toLocaleDateString("es-ES", opcionesFecha);
    horaElement.textContent = ahora.toLocaleTimeString("es-ES", opcionesHora);
}

setInterval(actualizarFechaHora, 1000);
actualizarFechaHora(); // Cargar al inicio

//FIN HEADER

// ✅ Función para actualizar dinámicamente el mes y año en el contenedor
function actualizarMesEnPantalla(mes, anio) {
    const nombresMeses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    const nombreMes = nombresMeses[mes - 1];
    document.getElementById("mes-actual-texto").innerText = `${nombreMes} ${anio}`;
}

// ✅ Modificar la función de aplicar filtros para actualizar el mes en pantalla
document.getElementById("btn-aplicar-filtros").addEventListener("click", function () {
    const mesSeleccionado = parseInt(document.getElementById("select-mes").value);
    const anioSeleccionado = parseInt(document.getElementById("select-anio").value);

    console.log(`🔍 Aplicando filtros: Mes ${mesSeleccionado}, Año ${anioSeleccionado}`);

    generarCalendario(mesSeleccionado, anioSeleccionado);
    actualizarMesEnPantalla(mesSeleccionado, anioSeleccionado); // 🔥 Actualizar el mes en el HTML
    document.getElementById("modal-filtro").classList.remove("modal-activo"); // Cierra el modal tras aplicar los filtros
});
// FIN Modificar la función de aplicar filtros para actualizar el mes en pantalla

// ✅ Función para abrir/cerrar el modal de detalles de reserva
document.addEventListener("DOMContentLoaded", function () {
    const modalReserva = document.querySelector(".modal_indiv");
    const cerrarModal = document.querySelector(".cerrar-modal_indiv");
    const codigoReservaInput = document.getElementById("codigo_reserva");
    const idCantHabInput = document.getElementById("id_cant_hab"); // Capturar el input oculto

    if (!modalReserva || !cerrarModal || !codigoReservaInput || !idCantHabInput) {
        console.error("❌ No se encontró algún elemento necesario en el DOM.");
        return;
    }

    function generarCodigoReserva() {
        fetch("api/generar_codigo_reserva.php")
            .then(response => response.json())
            .then(data => {
                if (data.codigo_reserva) {
                    codigoReservaInput.value = data.codigo_reserva;
                } else {
                    console.error("❌ Error: No se recibió un código de reserva válido.");
                }
            })
            .catch(error => console.error("❌ Error al generar código de reserva:", error));
    }

    // 🔥 Event delegation para detectar clics en las celdas del calendario
    document.addEventListener("click", function (event) {
        const celda = event.target.closest(".reserva-cell"); // Verifica si hizo clic en una celda válida
        if (celda && !celda.classList.contains("bloqueado")) {
            console.log("✅ Clic detectado en una celda, abriendo modal...");

            // ✅ Capturar id_cant_hab desde el atributo de la celda y asignarlo al input oculto
            const idCantHab = celda.getAttribute("data-hab"); // Asumiendo que "data-hab" tiene el id correcto
            if (idCantHab) {
                idCantHabInput.value = idCantHab;
                console.log("📌 ID Cantidad de Habitación capturado:", idCantHab);
            } else {
                console.warn("⚠️ No se encontró id_cant_hab en la celda seleccionada.");
            }

            // ✅ Abrir el modal y generar código de reserva
            modalReserva.classList.remove("oculto");
            modalReserva.style.display = "block";
            generarCodigoReserva();
        }
    });

    // 🔥 Cerrar modal al hacer clic en "×"
    cerrarModal.addEventListener("click", function () {
        console.log("✅ Clic en cerrar modal...");
        modalReserva.classList.add("oculto");
        modalReserva.style.display = "none";
    });

    // 🔥 Cerrar modal si se hace clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target.classList.contains("modal_indiv")) {
            console.log("✅ Clic fuera del modal, cerrando...");
            modalReserva.classList.add("oculto");
            modalReserva.style.display = "none";
        }
    });

    // 🔥 Escuchar evento cuando el calendario se regenera
    document.addEventListener("calendarioGenerado", function () {
        console.log("🔄 Calendario regenerado, eventos activos.");
    });
});


//tabs
document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".tab-link");
    const tabContents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            const targetTab = this.getAttribute("data-tab");
            console.log(`✅ Tab clickeado: ${targetTab}`); // DEBUGGING

            // Remover "active" de todos los tabs
            tabs.forEach(t => t.classList.remove("active"));
            tabContents.forEach(c => c.classList.remove("active"));

            // Activar el tab seleccionado
            this.classList.add("active");
            document.getElementById(`tab-${targetTab}`).classList.add("active");
            console.log(`✅ Tab activado: ${targetTab}`); // DEBUGGING
        });
    });
});

//fin tabs

//TAB DETALLES
document.addEventListener("DOMContentLoaded", function () {
    // Definir colores de estado
    const coloresEstados = {
        "Reconfirmación": "#39B54A",
        "Confirmación": "#312783",
        "Bloqueo": "#FBBA00",
        "Lista de Espera": "#009FE3",
        "Cancelación": "#E30613",
        "Anulación": "#000000"
    };

    const estadoReserva = document.getElementById("estado_reserva");
    const estadoIndicador = document.querySelector(".estado-indicador");
    const checkIn = document.getElementById("check_in");
    const checkOut = document.getElementById("check_out");
    const idExperiencia = document.getElementById("id_experiencia");

    // ✅ Cambiar color del indicador según el estado
    estadoReserva.addEventListener("change", function () {
        estadoIndicador.style.backgroundColor = coloresEstados[this.value] || "#ccc";
    });

    // ✅ Cargar datos de clientes
    fetch("api/get_clientes.php")
        .then(response => response.json())
        .then(clientes => {
            const selectCliente = document.getElementById("id_cliente");
            clientes.forEach(cliente => {
                let option = new Option(cliente.nombre_cliente, cliente.id_cliente);
                selectCliente.add(option);
            });
        });

    // ✅ Cargar datos de experiencias
    fetch("api/get_experiencias.php")
        .then(response => response.json())
        .then(experiencias => {
            experiencias.forEach(exp => {
                let option = new Option(`${exp.nombre_experiencia} (${exp.noches} noches)`, exp.id_experiencia);
                option.setAttribute("data-noches", exp.noches);
                idExperiencia.add(option);
            });
        });

    // ✅ Cargar datos de tarifas
    fetch("api/get_tarifas.php")
        .then(response => response.json())
        .then(tarifas => {
            const selectTarifa = document.getElementById("id_tarifa");
            tarifas.forEach(tarifa => {
                let option = new Option(tarifa.nombre_tarifa, tarifa.id_tarifa);
                selectTarifa.add(option);
            });
        });

    // ✅ Función para calcular Check-out basado en Check-in + Noches
    function calcularCheckOut() {
        let noches = idExperiencia.options[idExperiencia.selectedIndex]?.getAttribute("data-noches");
        let fechaCheckIn = checkIn.value;

        if (fechaCheckIn && noches && !isNaN(noches) && noches > 0) {
            let fecha = new Date(fechaCheckIn);
            fecha.setDate(fecha.getDate() + parseInt(noches));
            checkOut.value = fecha.toISOString().split('T')[0]; // Formato YYYY-MM-DD
        } else {
            checkOut.value = "";
        }
    }

    // ✅ Activar eventos solo cuando el modal se abre
    const modalReserva = document.querySelector(".modal_indiv");
    modalReserva.addEventListener("click", function () {
        idExperiencia.addEventListener("change", calcularCheckOut);
        checkIn.addEventListener("change", calcularCheckOut);
    });

    // ✅ También calcular si la reserva se edita
    document.querySelectorAll(".btn-editar-reserva").forEach(btn => {
        btn.addEventListener("click", function () {
            setTimeout(calcularCheckOut, 500); // Esperar datos de la API
        });
    });
});
//FIN TAB DETALLES

//TAB HABITACIONES


//get tipo
document.addEventListener("DOMContentLoaded", function () {
    const selectTipoHab = document.getElementById("id_tipo_habitacion");

    // ✅ Función para cargar los tipos de habitación
    function cargarTiposHabitacion() {
        fetch("api/get_tipo.php")
            .then(response => response.json())
            .then(tipos => {
                console.log("✅ Datos recibidos de get_tipo.php:", tipos); // Debugging

                // Limpiar el select antes de agregar nuevas opciones
                selectTipoHab.innerHTML = "";

                // Agregar opción por defecto
                let defaultOption = new Option("Seleccione Tipo de Habitación", "");
                selectTipoHab.appendChild(defaultOption);

                // Agregar cada tipo de habitación como opción
                tipos.forEach(tipo => {
                    let option = new Option(tipo.nombre_tipo, tipo.id_tipo_habitacion);
                    selectTipoHab.appendChild(option);
                });
            })
            .catch(error => console.error("❌ Error al obtener tipos de habitación:", error));
    }

    // ✅ Evento para cargar los tipos cuando se abre el modal
    document.addEventListener("click", function (event) {
        if (event.target.closest(".reserva-cell")) {
            cargarTiposHabitacion(); // Cargar tipos de habitación al abrir el modal
        }
    });
});
//fin get tipo

//get variante
document.addEventListener("DOMContentLoaded", function () {
    const selectTipoHab = document.getElementById("id_tipo_habitacion");
    const selectVarianteHab = document.getElementById("variante_habitacion");

    // ✅ Función para cargar variantes de habitación según el tipo seleccionado
    function cargarVariantesHabitacion() {
        let idTipo = selectTipoHab.value; // Obtener el ID del tipo de habitación seleccionado

        if (!idTipo) {
            // Si no se selecciona un tipo de habitación, reiniciar el select de variantes
            selectVarianteHab.innerHTML = '<option value="">Seleccione Variante</option>';
            return;
        }

        fetch(`api/get_variante.php?id_tipo_habitacion=${idTipo}`)
            .then(response => response.json())
            .then(variantes => {
                console.log("✅ Datos recibidos de get_variante.php:", variantes); // Debugging

                // Limpiar el select antes de agregar nuevas opciones
                selectVarianteHab.innerHTML = '<option value="">Seleccione Variante</option>';

                // Agregar cada variante como opción
                variantes.forEach(variante => {
                    let option = new Option(variante.nombre_variante, variante.id_variante);
                    selectVarianteHab.appendChild(option);
                });
            })
            .catch(error => console.error("❌ Error al obtener variantes de habitación:", error));
    }

    // ✅ Evento para cargar las variantes cuando se cambia el tipo de habitación
    selectTipoHab.addEventListener("change", cargarVariantesHabitacion);
});


//fin get variante

//FIN TAB HABITACIONES


// ✅ TAB PASAJEROS

// ✅ TAB PASAJEROS

document.addEventListener("DOMContentLoaded", function () {
    const selectAdultos = document.getElementById("adultos");
    const selectNinos = document.getElementById("ninos");
    const selectInfantes = document.getElementById("infantes");
    const selectTC = document.getElementById("tc");
    const contenedorPasajeros = document.getElementById("contenedor-pasajeros");

    let listaPaises = [];
    let listaExperienciasAd = [];
    let listaCostosAd = [];

    // ✅ Cargar países desde get_paises.php al inicio
    function cargarPaises() {
        fetch("api/get_paises.php")
            .then(response => response.json())
            .then(paises => {
                listaPaises = paises;
                console.log("✅ Países cargados:", listaPaises);
            })
            .catch(error => console.error("❌ Error al obtener países:", error));
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

    // 🔥 Cargar todos los datos necesarios al iniciar la página
    cargarPaises();
    cargarExperienciasAd();
    cargarCostosAd();

    // ✅ Función para generar pasajeros dinámicamente
    function generarPasajeros() {
        let totalAdultos = parseInt(selectAdultos.value) || 0;
        let totalNinos = parseInt(selectNinos.value) || 0;
        let totalInfantes = parseInt(selectInfantes.value) || 0;
        let totalTC = parseInt(selectTC.value) || 0;
        let totalPasajeros = totalAdultos + totalNinos + totalInfantes + totalTC;

        // Limpiar contenedor de pasajeros antes de agregar nuevos
        contenedorPasajeros.innerHTML = "";

        // Generar formularios según la cantidad seleccionada
        let index = 0;
        for (let i = 0; i < totalAdultos; i++) {
            contenedorPasajeros.appendChild(crearFormularioPasajero(index++, "Adulto"));
        }
        for (let i = 0; i < totalNinos; i++) {
            contenedorPasajeros.appendChild(crearFormularioPasajero(index++, "Niño"));
        }
        for (let i = 0; i < totalInfantes; i++) {
            contenedorPasajeros.appendChild(crearFormularioPasajero(index++, "Infante"));
        }
        for (let i = 0; i < totalTC; i++) {
            contenedorPasajeros.appendChild(crearFormularioPasajero(index++, "TC (Guía)"));
        }

        console.log(`✅ Se generaron ${totalPasajeros} formularios de pasajeros.`);
    }

    // ✅ Función para crear un formulario de pasajero dinámico
    function crearFormularioPasajero(index, tipo) {
        let div = document.createElement("div");
        div.classList.add("pasajero-form");
        div.style.backgroundColor = "#f7f7f7"; // ✅ Fondo gris claro para mejor separación
        div.style.padding = "15px";
        div.style.borderRadius = "8px";
        div.style.marginBottom = "10px";

        div.innerHTML = `
        <h4>${tipo} ${index + 1} (${tipo.toLowerCase()})</h4>
    
        <div class="pasajero-container">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="pasajero[${index}][nombre]" required>
            </div>
    
            <div class="form-group">
                <label>Apellido:</label>
                <input type="text" name="pasajero[${index}][apellido]" required>
            </div>
    
            <div class="form-group">
                <label>Pasaporte:</label>
                <input type="text" name="pasajero[${index}][pasaporte]">
            </div>
    
            <div class="form-group">
                <label>Fecha de Nacimiento:</label>
                <input type="date" name="pasajero[${index}][fecha_nacimiento]" required>
            </div>
    
            <div class="form-group">
                <label>País:</label>
                <select name="pasajero[${index}][id_pais]" required>
                    <option value="">Seleccione un País</option>
                    ${listaPaises.map(pais => `<option value="${pais.id_pais}">${pais.nombre_pais}</option>`).join("")}
                </select>
            </div>

            <div class="form-group">
                <label>Género:</label>
                <select name="pasajero[${index}][genero]" required>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
            </div>
    
            <div class="form-group">
                <label>Alimentación:</label>
                <select name="pasajero[${index}][alimentacion]" required>
                    <option value="Normal">Normal</option>
                    <option value="Vegano">Vegetariano</option>
                </select>
            </div>
    
            <div class="form-group">
                <label>Número de Vuelo:</label>
                <input type="text" name="pasajero[${index}][numero_vuelo]">
            </div>
    
            <div class="form-group">
                <label>Fecha y Hora de Llegada:</label>
                <input type="datetime-local" name="pasajero[${index}][fecha_hora_llegada]">
            </div>
    
            <div class="form-group">
                <label>Fecha y Hora de Salida:</label>
                <input type="datetime-local" name="pasajero[${index}][fecha_hora_salida]">
            </div>

                        <div class="form-group info-adicional">
                <label>Información Adicional:</label>
                <textarea name="pasajero[${index}][informacion_adicional]"></textarea>
            </div>

            <!-- 🔹 Sección de Adicionales -->
            <div class="adicionales">
                <h4>Adicionales</h4>

                <!-- 🔹 Experiencias Adicionales (Checkbox) -->
                <div class="adicionales-container">
                    <label><strong>Experiencias Adicionales:</strong></label>
                    <div id="experiencias_adicionales_${index}" class="experiencias-adicionales">
                        ${listaExperienciasAd.map(exp => `
                            <label class="check_pas">
                                <input type="checkbox" name="pasajero[${index}][experiencias_adicionales][]" value="${exp.id_experiencia_adicional}">
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
                                <input type="checkbox" name="pasajero[${index}][costos_adicionales][]" value="${costo.id_adicional_otros}">
                                ${costo.nombre_adicional} - $${costo.precio}
                            </label>
                        `).join("")}
                    </div>
                </div>

                <!-- 🔹 Otro Adicional (Nombre y Precio) -->
                <div class="form-group otro-adicional">
                    <label><strong>Otro Adicional:</strong></label>
                    <input type="text" name="pasajero[${index}][nombre_otro]" placeholder="Nombre del Adicional">
                    <input type="number" name="pasajero[${index}][precio_otro]" placeholder="Precio" step="0.01">
                </div>
            </div>
        </div>
    `;

        return div;
    }

    // ✅ Escuchar cambios en los selects para generar pasajeros dinámicamente
    selectAdultos.addEventListener("change", generarPasajeros);
    selectNinos.addEventListener("change", generarPasajeros);
    selectInfantes.addEventListener("change", generarPasajeros);
    selectTC.addEventListener("change", generarPasajeros);
});

// ✅ FIN TAB PASAJEROS


// ✅ FIN TAB PASAJEROS


//FIN TAB PASAJEROS

//MENSAJES EXITO Y ERROR DE RESERVAS
document.addEventListener("DOMContentLoaded", function () {
    const formReserva = document.getElementById("form-reserva");

    if (!formReserva) {
        console.error("❌ No se encontró el formulario en el DOM.");
        return;
    }

    formReserva.addEventListener("submit", function (event) {
        event.preventDefault(); // Evita el envío tradicional del formulario

        const formData = new FormData(formReserva);

        fetch("php/guardar_reserva.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("✅ Reserva guardada con éxito.");
                window.location.href = "index.php"; // 🔥 Redirigir después de éxito
            } else {
                alert("❌ Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("❌ Error en la solicitud:", error);
            alert("❌ Error en la conexión con el servidor.");
        });
    });
});

// FIN MENSAJES EXITO Y ERROR DE RESERVAS

//click en la fecha
document.addEventListener("DOMContentLoaded", function () {
    function inicializarCalendario(mes, anio) {
        const calendarioContainer = document.getElementById("calendario-container");
        const calendarioMes = document.querySelector(".calendario-mes thead tr");

        if (!calendarioContainer || !calendarioMes) {
            console.warn("⚠️ El calendario aún no está disponible. Reintentando en 500ms...");
            setTimeout(() => inicializarCalendario(mes, anio), 500); // Reintenta después de 500ms
            return;
        }

        console.log("✅ Elementos del calendario encontrados. Asignando eventos...");

        const diasEnMes = new Date(anio, mes, 0).getDate(); // Último día del mes

        // Asignar dinámicamente `data-fecha` a cada <th> con la clase "dia_fecha"
        calendarioMes.querySelectorAll("th:not(:first-child)").forEach((th, index) => {
            const dia = index + 1;
            const fecha = `${anio}-${mes.toString().padStart(2, "0")}-${dia.toString().padStart(2, "0")}`;
            th.setAttribute("data-fecha", fecha);
            th.classList.add("dia_fecha"); // ✅ Añadir la clase para identificación
            console.log(`📅 Fecha asignada: ${fecha}`);
        });

        // Delegación de eventos para capturar clics en las fechas con clase "dia_fecha"
        calendarioContainer.addEventListener("click", function (event) {
            const target = event.target.closest(".dia_fecha"); // ✅ Detecta clics en cualquier th con esta clase

            if (target) {
                const fechaSeleccionada = target.getAttribute("data-fecha");
                if (fechaSeleccionada) {
                    console.log(`🔗 Redirigiendo a reserva_masiva.php con fecha: ${fechaSeleccionada}`);
                    window.location.href = `reserva_masiva.php?fecha=${fechaSeleccionada}`;
                } else {
                    console.warn("⚠️ La fecha seleccionada no tiene un atributo data-fecha.");
                }
            }
        });

        console.log("📌 Fechas generadas dinámicamente con clase 'dia_fecha'.");
    }

    // Función para regenerar el calendario y reasignar eventos
    function regenerarCalendario(mes, anio) {
        generarCalendario(mes, anio); // Regenera el calendario
        inicializarCalendario(mes, anio); // Reasigna los eventos y atributos
    }

    const fechaActual = new Date();
    const mesActual = fechaActual.getMonth() + 1; // getMonth es 0-indexed
    const anioActual = fechaActual.getFullYear();

    inicializarCalendario(mesActual, anioActual); // Ejecutar la función al cargar la página

    // Ejemplo de cómo podrías llamar a regenerarCalendario después de aplicar filtros
    document.getElementById("btn-aplicar-filtros").addEventListener("click", function () {
        const mesSeleccionado = parseInt(document.getElementById("select-mes").value);
        const anioSeleccionado = parseInt(document.getElementById("select-anio").value);

        console.log(`🔍 Aplicando filtros: Mes ${mesSeleccionado}, Año ${anioSeleccionado}`);

        regenerarCalendario(mesSeleccionado, anioSeleccionado);
        document.getElementById("modal-filtro").classList.remove("modal-activo"); // Cierra el modal tras aplicar los filtros
    });
});
//fin click en la fecha