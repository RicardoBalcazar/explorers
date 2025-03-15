<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once 'config.php';

// ** Archivo de logs para capturar errores **
$errorLogFile = 'errorlog.txt';

// 📌 Función para escribir en el log
function logError($message) {
    global $errorLogFile;
    file_put_contents($errorLogFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// 📌 Iniciar log
logError("=== INICIO DE EJECUCIÓN ===");

// 📌 Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("❌ ERROR: Método no permitido.");
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

try {
    // ** Iniciar transacción **
    $pdo->beginTransaction();

    // ** Validar si `id_reserva` existe y tiene valor **
    if (!isset($_POST['id_reserva']) || empty($_POST['id_reserva'])) {
        throw new Exception("❌ ERROR: ID de reserva no proporcionado.");
    }

    $id_reserva = $_POST['id_reserva'];
    logError("🔍 ID Reserva recibido: " . $id_reserva);

    // ** Guardar en el log los datos recibidos **
    logError("📌 Datos recibidos:\n" . print_r($_POST, true));

    // ** Validar si la reserva existe en la BD **
    $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id_reserva = ?");
    $stmt->execute([$id_reserva]);
    $reserva_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva_actual) {
        throw new Exception("❌ ERROR: No existe la reserva en la base de datos.");
    }
    logError("✅ Reserva encontrada en BD.");

    // 📌 Obtener los datos y asegurarse de que los valores vacíos se mantengan correctamente
    $codigo_reserva = $_POST['codigo_reserva'] ?? $reserva_actual['codigo_reserva'];
    $estado_reserva = $_POST['estado_reserva'] ?? $reserva_actual['estado_reserva'];
    $id_cliente = !empty($_POST['id_cliente']) ? $_POST['id_cliente'] : $reserva_actual['id_cliente'];
    $id_experiencia = !empty($_POST['id_experiencia']) ? $_POST['id_experiencia'] : $reserva_actual['id_experiencia'];
    $check_in = $_POST['check_in'] ?? $reserva_actual['check_in'];
    $check_out = $_POST['check_out'] ?? $reserva_actual['check_out'];
    $tarifas = $_POST['id_tarifa'] ?? $reserva_actual['tarifas'];
    $usuario_modificacion = $_POST['usuario_modificacion'] ?? 1;
    $fecha_modificacion = date('Y-m-d H:i:s');

    // 📌 Actualizar tabla `reservas`
    $stmt = $pdo->prepare("UPDATE reservas SET 
        codigo_reserva = ?, estado_reserva = ?, id_cliente = ?, 
        id_experiencia = ?, check_in = ?, check_out = ?, tarifas = ?, 
        usuario_modificacion = ?, fecha_ultima_modificacion = ? 
        WHERE id_reserva = ?");
    
    $stmt->execute([
        $codigo_reserva, $estado_reserva, $id_cliente, 
        $id_experiencia, $check_in, $check_out, $tarifas, 
        $usuario_modificacion, $fecha_modificacion, $id_reserva
    ]);

    logError("✅ Reserva actualizada correctamente.");

    // 📌 Obtener valores actuales de la habitación
    $stmt = $pdo->prepare("SELECT id_tipo_habitacion, id_variante, adultos, ninos, infantes, tc FROM reservas_hab WHERE id_reserva = ?");
    $stmt->execute([$id_reserva]);
    $habitacion_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$habitacion_actual) {
        throw new Exception("❌ ERROR: No se encontró la habitación asociada a la reserva.");
    }

    $id_tipo_habitacion = isset($_POST['id_tipo_habitacion']) && $_POST['id_tipo_habitacion'] !== "" ? $_POST['id_tipo_habitacion'] : $habitacion_actual['id_tipo_habitacion'];
    $id_variante = isset($_POST['variante_habitacion']) && $_POST['variante_habitacion'] !== "" ? $_POST['variante_habitacion'] : $habitacion_actual['id_variante'];
    $adultos = isset($_POST['adultos']) && $_POST['adultos'] !== "" ? $_POST['adultos'] : $habitacion_actual['adultos'];
    $ninos = isset($_POST['ninos']) && $_POST['ninos'] !== "" ? $_POST['ninos'] : $habitacion_actual['ninos'];
    $infantes = isset($_POST['infantes']) && $_POST['infantes'] !== "" ? $_POST['infantes'] : $habitacion_actual['infantes'];
    $tc = isset($_POST['tc']) && $_POST['tc'] !== "" ? $_POST['tc'] : $habitacion_actual['tc'];

    // 📌 Actualizar información de habitaciones
    $stmt = $pdo->prepare("UPDATE reservas_hab SET id_tipo_habitacion = ?, id_variante = ?, adultos = ?, ninos = ?, infantes = ?, tc = ? WHERE id_reserva = ?");
    $stmt->execute([
        $id_tipo_habitacion, $id_variante, $adultos, $ninos, $infantes, $tc, $id_reserva
    ]);

    logError("✅ Habitación actualizada correctamente: Tipo: $id_tipo_habitacion, Variante: $id_variante, Adultos: $adultos, Niños: $ninos, Infantes: $infantes, TC: $tc");

    // 📌 Actualizar pasajeros
if (isset($_POST['pasajero']) && is_array($_POST['pasajero'])) {
    foreach ($_POST['pasajero'] as $id_pasajero => $pasajero) {
        if (!empty($pasajero['nombres'])) {
            $stmt = $pdo->prepare("UPDATE huespedes SET nombres = ?, apellidos = ?, pasaporte = ?, fecha_nacimiento = ?, id_pais = ?, genero = ?, alimentacion = ?, numero_vuelo = ?, fecha_hora_llegada = ?, fecha_hora_salida = ?, informacion_adicional = ? WHERE id_pasajero = ?");
            $stmt->execute([
                $pasajero['nombres'], $pasajero['apellidos'], $pasajero['pasaporte'],
                $pasajero['fecha_nacimiento'], $pasajero['id_pais'], $pasajero['genero'],
                $pasajero['alimentacion'], $pasajero['numero_vuelo'],
                $pasajero['fecha_hora_llegada'], $pasajero['fecha_hora_salida'], 
                $pasajero['informacion_adicional'], $id_pasajero
            ]);
            logError("✅ Pasajero {$id_pasajero} actualizado correctamente.");

            // Eliminar registros existentes en `huéspedes_extras` para este pasajero
            $stmt = $pdo->prepare("DELETE FROM huespedes_extras WHERE id_reserva = ? AND id_pasajero = ?");
            $stmt->execute([$id_reserva, $id_pasajero]);
            logError("🗑️ Registros existentes eliminados para pasajero {$id_pasajero}.");

            // Insertar nuevas experiencias adicionales
            if (isset($pasajero['experiencias']) && is_array($pasajero['experiencias'])) {
                foreach ($pasajero['experiencias'] as $id_experiencia) {
                    $stmt = $pdo->prepare("INSERT INTO huespedes_extras (id_reserva, id_pasajero, id_experiencia_adicional) VALUES (?, ?, ?)");
                    $stmt->execute([$id_reserva, $id_pasajero, $id_experiencia]);
                    logError("✅ Experiencia adicional {$id_experiencia} guardada para pasajero {$id_pasajero}.");
                }
            }

            // Insertar nuevos costos adicionales
            if (isset($pasajero['costos']) && is_array($pasajero['costos'])) {
                foreach ($pasajero['costos'] as $id_costo) {
                    $stmt = $pdo->prepare("INSERT INTO huespedes_extras (id_reserva, id_pasajero, id_adicional_otros) VALUES (?, ?, ?)");
                    $stmt->execute([$id_reserva, $id_pasajero, $id_costo]);
                    logError("✅ Costo adicional {$id_costo} guardado para pasajero {$id_pasajero}.");
                }
            }

            // Insertar otro adicional
            $otro_adicional_nombre = $pasajero['otro_adicional']['nombre'] ?? null;
            $otro_adicional_precio = $pasajero['otro_adicional']['precio'] ?? null;
            if ($otro_adicional_nombre !== null || $otro_adicional_precio !== null) {
                $stmt = $pdo->prepare("INSERT INTO huespedes_extras (id_reserva, id_pasajero, nombre_otro, precio_otro) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_reserva, $id_pasajero, $otro_adicional_nombre, $otro_adicional_precio]);
                logError("✅ Otro adicional guardado para pasajero {$id_pasajero}: Nombre: {$otro_adicional_nombre}, Precio: {$otro_adicional_precio}.");
            }
        }
    }


} else {
    logError("⚠️ No se proporcionaron datos para otro adicional, se mantiene el existente.");
}

    // 📌 Confirmar transacción
    $pdo->commit();

    // 📌 Guardar mensaje de éxito en el log
    logError("✅ Transacción confirmada con éxito.");

    // 📌 Retornar respuesta
    echo json_encode(['success' => 'Reserva actualizada correctamente.']);
    
} catch (Exception $e) {
    // 📌 Registrar error en el archivo log
    logError("❌ ERROR: " . $e->getMessage());

    // 📌 Revertir transacción si hay error
    $pdo->rollBack();

    // 📌 Respuesta de error
    echo json_encode(['error' => 'Error al actualizar la reserva: ' . $e->getMessage()]);
}
