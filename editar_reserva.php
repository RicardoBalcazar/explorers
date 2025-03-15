<?php
require_once 'php/config.php';

if (!isset($_GET['id_reserva']) || empty($_GET['id_reserva'])) {
    die("ID de reserva no proporcionado.");
}

$id_reserva = intval($_GET['id_reserva']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="css/edit_styles.css">
</head>
<body>

<h2>Editar Reserva</h2>

<form id="form-editar-reserva" class="contenedor-editar">
<input type="hidden" id="id_reserva" name="id_reserva" value="<?php echo $id_reserva; ?>">

    

    <!-- 🔹 Primera Sección: Detalles de la Reserva -->
    <h3>Detalles</h3>
    <div class="detalle-reserva">
        <div class="form-group">
            <label>Código Reserva:</label>
            <input type="text" id="codigo_reserva" readonly>
        </div>

        <div class="form-group">
                <!-- Estado de Reserva -->
                <div class="form-group">
                    <label>Estado:</label>
                    <div class="estado-container">
                        <span class="estado-indicador"></span>
                        <select name="estado_reserva" id="estado_reserva">
                        <option value="">Seleccione estado</option>
                            <option value="Reconfirmación">Reconfirmación</option>
                            <option value="Confirmación">Confirmación</option>
                            <option value="Bloqueo">Bloqueo</option>
                            <option value="Lista de Espera">Lista de Espera</option>
                            <option value="Cancelación">Cancelación</option>
                            <option value="Anulación">Anulación</option>
                        </select>
                    </div>
                </div>
        </div>

        <div class="form-group">
            <label>Cliente:</label>
            <select id="id_cliente">
                <option value="">Cargando...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Experiencia:</label>
            <select id="id_experiencia">
                <option value="">Cargando...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Check-in:</label>
            <input type="date" id="check_in">
        </div>

        <div class="form-group">
            <label>Check-out:</label>
            <input type="date" id="check_out">
        </div>

        <div class="form-group">
            <label>Tarifas:</label>
            <select id="id_tarifa">
                <option value="">Cargando...</option>
            </select>
        </div>
    </div>

    <!-- 🔹 Segunda Sección: Habitaciones -->
    <h3>Habitación</h3>
    <div class="habitacion-reserva">


        <div class="form-group">
            <label>Tipo de Habitación:</label>
            <select id="id_tipo_habitacion">
                <option value="">Cargando...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Variante de Habitación:</label>
            <select id="variante_habitacion">
                <option value="">Cargando...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Adultos:</label>
            <select id="adultos">
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>

        <div class="form-group">
            <label>Niños:</label>
            <select id="ninos">
                <option value="0">0</option>
                <option value="1">1</option>
            </select>
        </div>

        <div class="form-group">
            <label>Infantes:</label>
            <select id="infantes">
                <option value="0">0</option>
                <option value="1">1</option>
            </select>
        </div>

        <div class="form-group">
            <label>TC:</label>
            <select id="tc">
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
            </select>
        </div>
    </div>

    <h3>Pasajeros</h3>
    <!-- 🔹 Tercera Sección: Pasajeros -->
    <div class="pasajeros-reserva">

        <div id="contenedor-pasajeros">
            <!-- Aquí se agregarán los pasajeros dinámicamente -->
        </div>

        <button type="button" id="agregar-pasajero" class="btn-agregar">Agregar Pasajero</button>
    </div>

    <button type="submit" class="btn-guardar">Guardar Cambios</button>
</form>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const idReserva = document.getElementById("id_reserva").value;

    console.log("📌 Cargando datos para la reserva ID:", idReserva);

    fetch(`api/get_editar_reserva.php?id_reserva=${idReserva}`)
        .then(response => response.json())
        .then(reserva => {
            console.log("✅ Datos de la reserva recibidos:", reserva);

            if (!reserva || reserva.error) {
                console.error("❌ Error en la API:", reserva?.error || "Respuesta vacía.");
                alert("No se pudo cargar la reserva.");
                return;
            }

            // ✅ Cargar Pasajeros
            reserva.pasajeros.forEach(pasajero => agregarPasajero(pasajero));

            // ✅ Asignar valores a los inputs normales
            document.getElementById("codigo_reserva").value = reserva.codigo_reserva || "";
            document.getElementById("estado_reserva").value = reserva.estado_reserva || "";
            document.getElementById("check_in").value = reserva.check_in || "";
            document.getElementById("check_out").value = reserva.check_out || "";
            document.getElementById("id_tarifa").value = reserva.tarifas || "";
            document.getElementById("adultos").value = reserva.habitaciones?.adultos || 0;
            document.getElementById("ninos").value = reserva.habitaciones?.ninos || 0;
            document.getElementById("infantes").value = reserva.habitaciones?.infantes || 0;
            document.getElementById("tc").value = reserva.habitaciones?.tc || 0;

            // ✅ Cargar opciones antes de asignar valores en selects
            cargarOpciones("id_cliente", "api/get_clientes.php", reserva.cliente?.id_cliente);
            cargarOpciones("id_experiencia", "api/get_experiencias.php", reserva.experiencia?.id_experiencia);
            cargarOpciones("id_tipo_habitacion", "api/get_tipo.php", reserva.habitaciones?.id_tipo_habitacion);
            cargarOpciones("id_tarifa", "api/get_tarifas.php", reserva.habitaciones?.id_tipo_habitacion);

            // ✅ CORREGIDO: Obtener la variante de la habitación correctamente
            if (reserva.habitaciones?.id_tipo_habitacion) {
    cargarOpciones(
        "variante_habitacion",
        `api/get_edit_variante.php?id_tipo_hab=${reserva.habitaciones.id_tipo_habitacion}&id_variante=${reserva.habitaciones.id_variante}`,
        reserva.habitaciones.id_variante
    );
}
        })
        .catch(error => {
            console.error("❌ Error en la solicitud:", error);
            alert("Error al obtener los datos de la reserva.");
        });
});

// ✅ Función Mejorada para Cargar Opciones en los Selects
function cargarOpciones(elementId, apiUrl, valorSeleccionado) {
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error(`❌ Error: ${apiUrl} no devolvió un array válido.`, data);
                return;
            }

            console.log(`🔍 Datos recibidos de ${apiUrl}:`, data);

            const select = document.getElementById(elementId);
            select.innerHTML = '<option value="">Seleccione una opción</option>';

            data.forEach(item => {
                const idKey = Object.keys(item).find(k => k.includes("id_"));
                const nameKey = Object.keys(item).find(k => k.includes("nombre"));

                if (!idKey || !nameKey) {
                    console.error(`❌ Error en estructura de ${apiUrl}:`, item);
                    return;
                }

                let option = document.createElement("option");
                option.value = item[idKey]; 
                option.textContent = item[nameKey]; 
                select.appendChild(option);
            });

            // ✅ Asignar el valor correcto después de que se cargan las opciones
            setTimeout(() => {
                if (select.querySelector(`option[value="${valorSeleccionado}"]`)) {
                    select.value = valorSeleccionado;
                    console.log(`✅ ${elementId} asignado correctamente: ${valorSeleccionado}`);
                } else {
                    console.warn(`⚠️ No se encontró el valor ${valorSeleccionado} en ${elementId}`);
                }
            }, 300);
        })
        .catch(error => console.error(`❌ Error al cargar opciones de ${elementId}:`, error));
}

</script>

<script>
    document.getElementById("agregar-pasajero").addEventListener("click", function () {
    agregarPasajero();
});

function agregarPasajero(pasajero = {}) {
    const contenedor = document.getElementById("contenedor-pasajeros");
    const index = contenedor.children.length + 1;
    const idPasajero = pasajero.id_pasajero || `nuevo_${index}`;

    const div = document.createElement("div");
    div.classList.add("pasajero-form");
    div.setAttribute("data-id", idPasajero);

    div.innerHTML = `
     <div class="pasajero-form">
            <div class="pasajero-titulo">
                <h4>${pasajero.tipo ? `${pasajero.tipo.charAt(0).toUpperCase() + pasajero.tipo.slice(1)} ${index}` : `Pasajero ${index}`}</h4>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="pasajero[${idPasajero}][nombres]" value="${pasajero.nombres || ''}" required>
                </div>
                <div class="form-group">
                    <label>Apellido:</label>
                    <input type="text" name="pasajero[${idPasajero}][apellidos]" value="${pasajero.apellidos || ''}" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Pasaporte:</label>
                    <input type="text" name="pasajero[${idPasajero}][pasaporte]" value="${pasajero.pasaporte || ''}">
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento:</label>
                    <input type="date" name="pasajero[${idPasajero}][fecha_nacimiento]" value="${pasajero.fecha_nacimiento || ''}" required>
                </div>
            </div>
            <div class="form-row">
                <!-- 📌 País -->
                <div class="form-group">
                    <label>País:</label>
                    <select name="pasajero[${idPasajero}][id_pais]" id="pais_${idPasajero}"></select>
                </div>

                <!-- 📌 Género -->
                <div class="form-group">
                    <label>Género:</label>
                    <select name="pasajero[${idPasajero}][genero]">
                        <option value="Masculino" ${pasajero.genero === "Masculino" ? "selected" : ""}>Masculino</option>
                        <option value="Femenino" ${pasajero.genero === "Femenino" ? "selected" : ""}>Femenino</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <!-- 📌 Alimentación -->
                <div class="form-group">
                    <label>Alimentación:</label>
                    <select name="pasajero[${idPasajero}][alimentacion]">
                        <option value="Normal" ${pasajero.alimentacion === "Normal" ? "selected" : ""}>Normal</option>
                        <option value="Vegano" ${pasajero.alimentacion === "Vegano" ? "selected" : ""}>Vegano</option>
                    </select>
                </div>


                <!-- 📌 Número de vuelo -->
                <div class="form-group">
                    <label>Número de Vuelo:</label>
                    <input type="text" name="pasajero[${idPasajero}][numero_vuelo]" value="${pasajero.numero_vuelo || ''}">
                </div>
            </div>
            <div class="form-row">
                <!-- 📌 Fechas y Horas -->
                <div class="form-group">
                    <label>Fecha y Hora de Llegada:</label>
                    <input type="datetime-local" name="pasajero[${idPasajero}][fecha_hora_llegada]" value="${pasajero.fecha_hora_llegada || ''}">
                </div>

                <div class="form-group">
                    <label>Fecha y Hora de Salida:</label>
                    <input type="datetime-local" name="pasajero[${idPasajero}][fecha_hora_salida]" value="${pasajero.fecha_hora_salida || ''}">
                </div>
            </div>

            <!-- 📌 Información Adicional -->
            <div class="form-group">
                <label>Información Adicional:</label>
                <textarea name="pasajero[${idPasajero}][informacion_adicional]">${pasajero.informacion_adicional || ''}</textarea>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-danger eliminar-pasajero">Eliminar Pasajero</button>
            </div>

            <h5>Adicionales</h5>
            <div class="adicionales-experiencias"></div>
            <div class="adicionales-costos"></div>

            <h5>Otro Adicional:</h5>
            <div class="form-group">
                <label>Nombre del Adicional:</label>
                <input type="text" class="otro-adicional-nombre" name="pasajero[${idPasajero}][otro_adicional][nombre]" placeholder="Nombre del Adicional">
            </div>
            <div class="form-group">
                <label>Precio:</label>
                <input type="number" class="otro-adicional-precio" name="pasajero[${idPasajero}][otro_adicional][precio]" placeholder="Precio">
            </div>
        </div>

    `;

    contenedor.appendChild(div);

        // 📌 AQUÍ VA EL CÓDIGO PARA LLENAR EL "OTRO ADICIONAL"
        if (pasajero.adicionales) {
        let otroAdicional = pasajero.adicionales.find(ad => ad.nombre_otro);
        if (otroAdicional) {
            div.querySelector(".otro-adicional-nombre").value = otroAdicional.nombre_otro;
            div.querySelector(".otro-adicional-precio").value = otroAdicional.precio_otro || 0.00;
        }
    }

    // 📌 Cargar países
    cargarOpciones("pais_" + idPasajero, "api/get_paises.php", pasajero.id_pais);

    // 📌 Cargar experiencias y costos adicionales con valores existentes
    cargarOpcionesCheckbox("api/get_experiencias_ad.php", div.querySelector(".adicionales-experiencias"), `pasajero[${idPasajero}][experiencias]`, pasajero.adicionales);
    cargarOpcionesCheckbox("api/get_costos_ad.php", div.querySelector(".adicionales-costos"), `pasajero[${idPasajero}][costos]`, pasajero.adicionales);

    // 📌 Evento para eliminar pasajero
    div.querySelector(".eliminar-pasajero").addEventListener("click", function () {
        contenedor.removeChild(div);
    });
}

// Cargar experiencias y costos adicionales
function cargarOpcionesCheckbox(apiUrl, contenedor, name, seleccionados = []) {
    fetch(apiUrl)
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error(`❌ Error: ${apiUrl} no devolvió un array válido.`, data);
                return;
            }

            console.log(`🔍 Datos recibidos de ${apiUrl}:`, data);

            contenedor.innerHTML = "";
            data.forEach(item => {
                const idKey = Object.keys(item).find(k => k.includes("id_")); // Buscar clave de ID
                const nameKey = Object.keys(item).find(k => k.includes("nombre")); // Buscar clave de nombre
                const priceKey = Object.keys(item).find(k => k.includes("precio")); // Buscar clave de precio
                
                if (!idKey || !nameKey) {
                    console.error(`❌ Error en estructura de ${apiUrl}:`, item);
                    return;
                }

                let checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.name = `${name}[]`;
                checkbox.value = item[idKey];
                checkbox.checked = seleccionados.some(sel => sel[idKey] == item[idKey]); // Marcar si estaba seleccionado

                let label = document.createElement("label");
                label.textContent = `${item[nameKey]} - $${item[priceKey] || 0.00}`;

                let div = document.createElement("div");
                div.appendChild(checkbox);
                div.appendChild(label);

                contenedor.appendChild(div);
            });

            
        })
        .catch(error => console.error(`❌ Error al cargar opciones de ${apiUrl}:`, error));
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const checkInInput = document.getElementById("check_in");
    const checkOutInput = document.getElementById("check_out");
    const experienciaSelect = document.getElementById("id_experiencia");

    // 🎯 Almacenar las noches de cada experiencia
    let nochesExperiencias = {};

    // ✅ Obtener experiencias con sus noches
    fetch("api/get_experiencias.php")
        .then(response => response.json())
        .then(data => {
            data.forEach(exp => {
                nochesExperiencias[exp.id_experiencia] = parseInt(exp.noches) || 0;
            });
        })
        .catch(error => console.error("❌ Error al obtener las experiencias:", error));

    // ✅ Función para actualizar el Check-out
    function actualizarCheckOut() {
        const checkInValue = checkInInput.value;
        const idExperienciaSeleccionada = experienciaSelect.value;
        const noches = nochesExperiencias[idExperienciaSeleccionada] || 0;

        if (checkInValue && noches > 0) {
            let checkInDate = new Date(checkInValue);
            checkInDate.setDate(checkInDate.getDate() + noches); // Sumar noches a la fecha de check-in

            let checkOutDate = checkInDate.toISOString().split("T")[0]; // Convertir a formato YYYY-MM-DD
            checkOutInput.value = checkOutDate;
        } else {
            checkOutInput.value = ""; // Limpiar el campo si no hay noches definidas
        }
    }

    // 🔹 Evento: Cambio en la experiencia
    experienciaSelect.addEventListener("change", actualizarCheckOut);

    // 🔹 Evento: Cambio en el Check-in
    checkInInput.addEventListener("change", actualizarCheckOut);
});

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const tipoHabitacionSelect = document.getElementById("id_tipo_habitacion");
    const varianteHabitacionSelect = document.getElementById("variante_habitacion");

    // ✅ Función para cargar variantes según el tipo de habitación
    function cargarVariantes() {
        const idTipoHabitacion = tipoHabitacionSelect.value;

        if (!idTipoHabitacion) {
            varianteHabitacionSelect.innerHTML = '<option value="">Seleccione una variante</option>';
            return;
        }

        fetch(`api/get_edit_variante.php?id_tipo_hab=${idTipoHabitacion}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) {
                    console.error("❌ Error: La respuesta no es un array válido.", data);
                    return;
                }

                // 🔹 Guardamos la variante actualmente seleccionada
                const varianteSeleccionada = varianteHabitacionSelect.value;

                // 🔹 Limpiamos y llenamos el select con las nuevas opciones
                varianteHabitacionSelect.innerHTML = '<option value="">Seleccione una variante</option>';
                data.forEach(variante => {
                    let option = document.createElement("option");
                    option.value = variante.id_variante;
                    option.textContent = variante.nombre_variante;
                    varianteHabitacionSelect.appendChild(option);
                });

                // 🔹 Restauramos la variante si sigue disponible
                if (data.some(v => v.id_variante == varianteSeleccionada)) {
                    varianteHabitacionSelect.value = varianteSeleccionada;
                }
            })
            .catch(error => console.error("❌ Error al cargar variantes:", error));
    }

    // 🔹 Evento: Cambio en el Tipo de Habitación
    tipoHabitacionSelect.addEventListener("change", cargarVariantes);

    // 🔹 Cargar variantes iniciales si ya hay un tipo de habitación seleccionado
    if (tipoHabitacionSelect.value) {
        cargarVariantes();
    }
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const estadoReservaSelect = document.getElementById("estado_reserva");
    const estadoIndicador = document.querySelector(".estado-indicador");

    // 📌 Definir los colores de cada estado
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

    // ✅ Actualizar color al cargar la página
    actualizarColorEstado();

    // ✅ Cambiar color cuando se seleccione otro estado
    estadoReservaSelect.addEventListener("change", actualizarColorEstado);
});


</script>

<script>
document.querySelector(".btn-guardar").addEventListener("click", function (event) {
    event.preventDefault();

    let formData = new FormData();

    // Capturar datos generales de la reserva
    formData.append("id_reserva", document.getElementById("id_reserva").value);
    formData.append("codigo_reserva", document.getElementById("codigo_reserva").value);
    formData.append("estado_reserva", document.getElementById("estado_reserva").value);
    formData.append("id_cliente", document.getElementById("id_cliente").value);
    formData.append("id_experiencia", document.getElementById("id_experiencia").value);
    formData.append("check_in", document.getElementById("check_in").value);
    formData.append("check_out", document.getElementById("check_out").value);
    formData.append("id_tarifa", document.getElementById("id_tarifa").value);
    formData.append("id_tipo_habitacion", document.getElementById("id_tipo_habitacion").value);
    formData.append("variante_habitacion", document.getElementById("variante_habitacion").value);
    formData.append("adultos", document.getElementById("adultos").value);
    formData.append("ninos", document.getElementById("ninos").value);
    formData.append("infantes", document.getElementById("infantes").value);
    formData.append("tc", document.getElementById("tc").value);

    // 📌 Capturar datos de pasajeros correctamente
// 📌 Capturar datos de pasajeros dinámicamente
document.querySelectorAll(".pasajero-form").forEach((form) => {
    let id_pasajero = form.getAttribute("data-id");

    if (!id_pasajero) {
        console.warn("⚠️ Pasajero sin ID encontrado, omitiendo...");
        return;
    }

    console.log(`📌 Capturando datos de pasajero ${id_pasajero}`);

    formData.append(`pasajero[${id_pasajero}][nombres]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][nombres]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][apellidos]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][apellidos]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][pasaporte]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][pasaporte]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][fecha_nacimiento]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][fecha_nacimiento]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][id_pais]`, form.querySelector('select[name="pasajero[' + id_pasajero + '][id_pais]"]').value || ""); 
    formData.append(`pasajero[${id_pasajero}][genero]`, form.querySelector('select[name="pasajero[' + id_pasajero + '][genero]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][alimentacion]`, form.querySelector('select[name="pasajero[' + id_pasajero + '][alimentacion]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][numero_vuelo]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][numero_vuelo]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][fecha_hora_llegada]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][fecha_hora_llegada]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][fecha_hora_salida]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][fecha_hora_salida]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][informacion_adicional]`, form.querySelector('textarea[name="pasajero[' + id_pasajero + '][informacion_adicional]"]').value || "");

    // 📌 Capturar experiencias adicionales
    form.querySelectorAll(`input[name="pasajero[${id_pasajero}][experiencias][]"]:checked`).forEach((checkbox) => {
        formData.append(`pasajero[${id_pasajero}][experiencias][]`, checkbox.value);
    });

    // 📌 Capturar costos adicionales
    form.querySelectorAll(`input[name="pasajero[${id_pasajero}][costos][]"]:checked`).forEach((checkbox) => {
        formData.append(`pasajero[${id_pasajero}][costos][]`, checkbox.value);
    });

    // 📌 Capturar otro adicional
    formData.append(`pasajero[${id_pasajero}][otro_adicional][nombre]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][otro_adicional][nombre]"]').value || "");
    formData.append(`pasajero[${id_pasajero}][otro_adicional][precio]`, form.querySelector('input[name="pasajero[' + id_pasajero + '][otro_adicional][precio]"]').value || 0);
});
    fetch("php/guardar_cambios_reserva.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: "success",
                title: "¡Reserva actualizada!",
                text: "Los cambios se guardaron correctamente.",
                confirmButtonText: "Aceptar"
            }).then(() => {
                window.location.href = "index.php";
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error al actualizar",
                text: data.error || "Hubo un problema al guardar los cambios.",
                confirmButtonText: "Aceptar"
            });
        }
    })
    .catch(error => {
        console.error("❌ Error en la solicitud:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudo procesar la solicitud.",
            confirmButtonText: "Aceptar"
        });
    });
});



</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/disponiblidad.js?v=<?php echo time(); ?>"></script>
</body>
</html>
