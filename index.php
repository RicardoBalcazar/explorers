<?php
// Conectar a la base de datos
require_once 'php/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Reservas</title>
    <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

<?php
// Obtener datos del usuario
$query = $pdo->query("SELECT nombres, rol, telefono, email FROM usuarios LIMIT 1");
$usuario = $query->fetch(PDO::FETCH_ASSOC);
?>

<!-- Header con logo, título y datos -->
<div class="header-container">
    <div class="logo">
        <img src="img\explorers-inn-logo-white.png" alt="Logo Empresa">
    </div>

    <div class="titulo">
        <h1>Calendario de Reservas</h1>
    </div>

    <div class="datos-usuario">
        <p><strong>📅 <span id="fecha-actual"></span></strong></p>
        <p><strong>🕒 <span id="hora-actual"></span></strong></p>
        <hr>
        <p><strong>👤 <?= $usuario['nombres'] ?></strong></p>
        <p><strong>🎓 <?= $usuario['rol'] ?></strong></p>
        <p><strong>📞 <?= $usuario['telefono'] ?></strong></p>
        <p><strong>📧 <?= $usuario['email'] ?></strong></p>
    </div>
</div>

<?php
// Obtener la fecha actual
$mes_actual = date('n'); // Número del mes (1-12)
$anio_actual = date('Y');

// Array de nombres de meses en español
$meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

// Obtener el nombre del mes actual
$nombre_mes = $meses[$mes_actual - 1];
?>

<!-- Contenedor del Mes y Año -->
<div id="mes-actual-container" class="mes-actual-container">
    <h2 id="mes-actual-texto"><?php echo "$nombre_mes $anio_actual"; ?></h2>
</div>

<!-- Botón para abrir filtros -->
<button id="btn-filtros" class="btn-filtros">Filtros de Búsqueda</button>

<!-- Modal de Filtros -->
<div id="modal-filtro" class="modal-filtro">
    <div class="modal-contenido">
        <span class="cerrar-modal" id="cerrar-filtro">&times;</span>
        <h3>Filtros de Búsqueda</h3>

        <label for="select-mes">Mes:</label>
        <select id="select-mes">
            <?php 
            $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            foreach ($meses as $key => $mes): ?>
                <option value="<?= $key + 1 ?>" <?= ($key + 1) == date('n') ? 'selected' : '' ?>><?= $mes ?></option>
            <?php endforeach; ?>
        </select>

        <label for="select-anio">Año:</label>
        <select id="select-anio">
            <?php for ($anio = date('Y'); $anio <= 2030; $anio++): ?>
                <option value="<?= $anio ?>" <?= $anio == date('Y') ? 'selected' : '' ?>><?= $anio ?></option>
            <?php endfor; ?>
        </select>

        <label for="select-cliente">Cliente:</label>
        <select id="select-cliente">
            <option value="">Seleccione Cliente</option>
            <!-- Los clientes se llenarán con JS -->
        </select>

        <label>Estado de Reserva:</label>
        <div class="estado-colores">
            <span class="estado-opcion" data-estado="Reconfirmación" style="background-color: #39B54A;"></span>
            <span class="estado-opcion" data-estado="Confirmación" style="background-color: #312783;"></span>
            <span class="estado-opcion" data-estado="Bloqueo" style="background-color: #FBBA00;"></span>
            <span class="estado-opcion" data-estado="Lista de Espera" style="background-color: #009FE3;"></span>
            <span class="estado-opcion" data-estado="Cancelación" style="background-color: #E30613;"></span>
            <span class="estado-opcion" data-estado="Anulación" style="background-color: #000000;"></span>
        </div>
        <button id="btn-aplicar-filtros" class="btn-aplicar">Aplicar Filtros</button>
    </div>
</div>

<!-- CONSUME CALENDARIO -->
<div id="calendario-container"></div>

<!-- Tooltip estático en el HTML -->
<div id="tooltip-reserva" class="tooltip-hidden">
    <div class="tooltip-header">
        <span class="tooltip-title">Datos de Reserva</span>
    </div>
    <div class="tooltip-content">
        <p><strong>Código:</strong> <span id="tooltip-codigo"></span></p>
        <p><strong>Cliente:</strong> <span id="tooltip-cliente"></span></p>
        <p><strong>Estado:</strong> <span id="tooltip-estado"></span></p>
        <hr>
        <p><strong>Check-in:</strong> <span id="tooltip-checkin"></span></p>
        <p><strong>Check-out:</strong> <span id="tooltip-checkout"></span></p>
        <hr>
        <div id="tooltip-habitaciones"></div>
        <hr>
        <p><strong>Usuario:</strong> <span id="tooltip-usuario"></span></p>

    </div>
</div>

<!-- Modal Nueva Reserva (Siempre en el DOM) -->
<div class="modal_indiv oculto">
    <div class="modal_indiv-contenido">
        <span class="cerrar-modal_indiv">&times;</span>
        <h3>Nueva Reserva</h3>

        <!-- FORMULARIO -->
        <form method="post" action="php/guardar_reserva.php" id="form-reserva">

        <!-- Tabs -->
        <div class="tabs">
            <button type="button" class="tab-link active" data-tab="detalles">DETALLES</button>
            <button type="button" class="tab-link" data-tab="habitaciones">HABITACIONES</button>
            <button type="button" class="tab-link" data-tab="pasajeros">PASAJEROS</button>
            <!--<button type="button" class="tab-link" data-tab="liquidacion">LIQUIDACIÓN</button>-->
        </div>

        <!-- Contenido de los Tabs -->
        <div class="tab-content active" id="tab-detalles">
            
        <h4>Detalles</h4>

        <div class="form-container">
                <!-- Código de Reserva (Autogenerado) -->
                 <div class="form-group">
                    <label>Código de Reserva:</label>
                    <input type="text" name="codigo_reserva" id="codigo_reserva" readonly>
                </div>

                <!-- Cliente (Obtenido de get_clientes.php) -->
                <div class="form-group">
                    <label>Cliente:</label>
                    <select name="id_cliente" id="id_cliente">
                        <option value="">Seleccione un Cliente</option>
                    </select>
                </div>

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

                <!-- Experiencia (Obtenida de get_experiencias.php) -->
                <div class="form-group">
                    <label>Experiencia:</label>
                    <select name="id_experiencia" id="id_experiencia">
                        <option value="">Seleccione una Experiencia</option>
                    </select>
                </div>

                <!-- Check-in y Check-out -->
                <div class="form-group">
                <label>Check-in:</label>
                <input type="date" name="check_in" id="check_in">
            </div>

                <label>Check-out:</label>
                <input type="date" name="check_out" id="check_out" readonly>

                <!-- Tarifas (Obtenidas de get_tarifas.php) -->
                <label>Tarifas:</label>
                <select name="id_tarifa" id="id_tarifa">
                    <option value="">Seleccione una Tarifa</option>
                </select>
            </div>


        </div>

        <div class="tab-content" id="tab-habitaciones">
            <h4>Habitaciones</h4>
            <div class="form-container">
                <!-- Tipo de Habitación -->
                <div class="form-group">
                    <label for="tipo_habitacion">Tipo de Habitación:</label>
                    <select name="id_tipo_habitacion" id="id_tipo_habitacion" class="select-hab">
                        <option value="">Seleccione Tipo de Habitación</option>
                    </select>
                </div>

                <!-- Variante de Habitación -->
                <div class="form-group">
                    <label for="variante_habitacion">Variante:</label>
                    <select name="id_variante_habitacion" id="variante_habitacion" class="select-variante">
                        <option value="">Seleccione Variante</option>
                    </select>
                </div>

                <!-- Adultos -->
                <div class="form-group">
                    <label for="adultos">Adultos:</label>
                    <select name="adultos" id="adultos" class="select-pasajeros">
                        <option value="1">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>

                <!-- Niños -->
                <div class="form-group">
                    <label for="ninos">Niños:</label>
                    <select name="ninos" id="ninos" class="select-pasajeros">
                        <option value="0">0</option>
                        <option value="1">1</option>
                    </select>
                </div>

                <!-- Infantes -->
                <div class="form-group">
                    <label for="infantes">Infantes:</label>
                    <select name="infantes" id="infantes" class="select-pasajeros">
                        <option value="0">0</option>
                        <option value="1">1</option>
                    </select>
                </div>

                <!-- TC -->
                <div class="form-group">
                    <label for="tc">TC:</label>
                    <select name="tc" id="tc" class="select-pasajeros">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-pasajeros">
            <h4>Pasajeros</h4>
                <!-- Contenedor donde se insertarán dinámicamente los formularios de pasajeros -->
                <div id="contenedor-pasajeros"></div>
        </div>

        <!-- <div class="tab-content" id="tab-liquidacion">
            <h4>Liquidación</h4>
        </div>-->
        <input type="hidden" id="id_cant_hab" name="id_cant_hab">
        <button type="submit" class="btn-guardar">Guardar Reserva</button>
        </form>
    </div>
</div>

    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/editar_reserva.js?v=<?php echo time(); ?>"></script>
    <script src="js/disponiblidad.js?v=<?php echo time(); ?>"></script>

</body>
</html>