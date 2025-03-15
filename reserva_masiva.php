<?php
// Configurar el manejo de errores
ini_set('log_errors', 'On');
ini_set('error_log', 'error_log.txt');
ini_set('display_errors', 'Off'); // Opcional: para no mostrar errores en el navegador

// Crear un log local en el mismo directorio del script
$file = 'log_reservas.txt';
$logData = date("Y-m-d H:i:s") . " - " . print_r($_POST, true) . "\n";
file_put_contents($file, $logData, FILE_APPEND | LOCK_EX);

error_log(print_r($_POST, true)); // También lo dejamos en el log de PHP para depuración
require_once 'php/config.php';

$response = ['success' => false, 'message' => ''];
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

<h2>Reserva Masiva</h2>
<h4>Fecha seleccionada: <span id="fecha-seleccionada">Ninguna</span></h4>


<form id="form-editar-reserva" class="contenedor-editar">
    <!-- 🔹 Primera Sección: Detalles de la Reserva -->
    <h3>Detalles</h3>
    <div class="detalle-reserva">
        <div class="form-group">
            <label>Código de Reserva:</label>
            <input type="text" name="codigo_reserva" id="codigo_reserva" readonly>
        </div>

        <div class="form-group">
            <!-- Estado de Reserva -->
            <div class="form-group">
                <label>Estado:</label>
                <div class="estado-container">
                    <span class="estado-indicador"></span>
                    <select name="estado_reserva" id="estado_reserva">
                        <option value="">seleccione estado</option>
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
            <select name="id_cliente" id="id_cliente">
                <option value="">seleccione cliente...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Experiencia:</label>
            <select name="id_experiencia" id="id_experiencia">
                <option value="">seleccione experiencia...</option>
            </select>
        </div>

        <div class="form-group">
            <label>Check-in:</label>
            <input type="date" name="check_in" id="check_in">
        </div>

        <div class="form-group">
            <label>Check-out:</label>
            <input type="date" name="check_out" id="check_out">
        </div>

        <div class="form-group">
            <label>Tarifas:</label>
            <select name="id_tarifa" id="id_tarifa">
                <option value="">seleccione tarifa...</option>
            </select>
        </div>
    </div> 


<!-- 📌 Contenedor de Selección de Habitaciones -->
<div class="habitaciones-container">
    <h3>Selecciona las Habitaciones</h3>
    
    <div class="habitaciones-header">
        <span>Habitación</span>
        <span>Tipo</span>
        <span>Variante</span>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adultos</span>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Niños</span>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Infantes</span>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TC</span>
        <span>Pasajeros</span>
    </div>

    <div id="contenedor-habitaciones">
        <!-- 🔹 Las filas de habitaciones se generarán dinámicamente aquí -->
    </div>
</div>
    <button type="submit" class="btn-guardar-masiva">Crear Reserva</button>
</form>

<!-- Modal -->
<div id="modalPasajeros" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="formPasajeros">
            <div id="pasajerosContainer"></div>
            <button type="button" id="btnGuardarPasajeros">Guardar</button>
        </form>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/reserva_masiva.js?v=<?php echo time(); ?>"></script>
</body>
</html>
