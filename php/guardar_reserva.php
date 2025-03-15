<?php
error_log(print_r($_POST, true)); // 📌 Registrar datos recibidos para depuración

include 'config.php'; // 📌 Asegúrate de que 'config.php' tiene la conexión PDO

$response = ['success' => false, 'message' => ''];

try {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    });

    $pdo->beginTransaction();

    // 📌 Capturar datos principales de la reserva
    $codigo_reserva = $_POST['codigo_reserva'] ?? null;
    $id_cliente = $_POST['id_cliente'] ?? null;
    $id_experiencia = $_POST['id_experiencia'] ?? null;
    $estado_reserva = $_POST['estado_reserva'] ?? null;
    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;
    $id_tarifa = $_POST['id_tarifa'] ?? null;
    $id_cant_hab = $_POST['id_cant_hab'] ?? null;
    $id_tipo_habitacion = $_POST['id_tipo_habitacion'] ?? null;
    $id_variante_habitacion = $_POST['id_variante_habitacion'] ?? null;
    $adultos = $_POST['adultos'] ?? 0;
    $ninos = $_POST['ninos'] ?? 0;
    $infantes = $_POST['infantes'] ?? 0;
    $tc = $_POST['tc'] ?? 0;
    $usuario_creacion = 1; // 📌 Ajustar según el usuario en sesión
    $usuario_modificacion = 1;

    // 📌 Validaciones básicas
    if (!$codigo_reserva || !$id_cliente || !$id_experiencia || !$estado_reserva || !$check_in || !$check_out || !$id_tarifa) {
        throw new Exception("Faltan datos obligatorios en la reserva.");
    }

    if (!$id_cant_hab || !$id_tipo_habitacion || !$id_variante_habitacion) {
        throw new Exception("Faltan datos de habitación: id_cant_hab={$id_cant_hab}, id_tipo_habitacion={$id_tipo_habitacion}, id_variante_habitacion={$id_variante_habitacion}");
    }

    // 📌 Insertar en la tabla `reservas`
    $stmt = $pdo->prepare("
        INSERT INTO reservas (codigo_reserva, id_cliente, id_experiencia, estado_reserva, check_in, check_out, tarifas, usuario_creacion, fecha_creacion, usuario_modificacion, fecha_ultima_modificacion) 
        VALUES (:codigo_reserva, :id_cliente, :id_experiencia, :estado_reserva, :check_in, :check_out, :id_tarifa, :usuario_creacion, NOW(), :usuario_modificacion, NOW())
    ");
    $stmt->execute([
        ':codigo_reserva' => $codigo_reserva,
        ':id_cliente' => $id_cliente,
        ':id_experiencia' => $id_experiencia,
        ':estado_reserva' => $estado_reserva,
        ':check_in' => $check_in,
        ':check_out' => $check_out,
        ':id_tarifa' => $id_tarifa,
        ':usuario_creacion' => $usuario_creacion,
        ':usuario_modificacion' => $usuario_modificacion
    ]);

    $id_reserva = $pdo->lastInsertId();
    error_log("✅ Reserva insertada con ID: " . $id_reserva);

    // 📌 Insertar en `reservas_hab`
    $stmt_hab = $pdo->prepare("
        INSERT INTO reservas_hab (id_reserva, id_cant_hab, id_tipo_habitacion, id_variante, adultos, ninos, infantes, tc) 
        VALUES (:id_reserva, :id_cant_hab, :id_tipo_habitacion, :id_variante, :adultos, :ninos, :infantes, :tc)
    ");
    $stmt_hab->execute([
        ':id_reserva' => $id_reserva,
        ':id_cant_hab' => $id_cant_hab,
        ':id_tipo_habitacion' => $id_tipo_habitacion,
        ':id_variante' => $id_variante_habitacion,
        ':adultos' => $adultos,
        ':ninos' => $ninos,
        ':infantes' => $infantes,
        ':tc' => $tc
    ]);

    error_log("✅ Habitación insertada para la reserva: " . $id_reserva);

    // 📌 Insertar pasajeros en `huespedes`
// 📌 Insertar en `huespedes_extras`
if (!empty($_POST['pasajero'])) {
    foreach ($_POST['pasajero'] as $index => $pasajero) {
        $stmt_pasajero = $pdo->prepare("
            INSERT INTO huespedes (id_reserva, nombres, apellidos, pasaporte, fecha_nacimiento, id_pais, genero, alimentacion, numero_vuelo, fecha_hora_llegada, fecha_hora_salida, informacion_adicional, tipo) 
            VALUES (:id_reserva, :nombres, :apellidos, :pasaporte, :fecha_nacimiento, :id_pais, :genero, :alimentacion, :numero_vuelo, :fecha_hora_llegada, :fecha_hora_salida, :informacion_adicional, :tipo)
        ");
        $stmt_pasajero->execute([
            ':id_reserva' => $id_reserva,
            ':nombres' => $pasajero['nombre'] ?? '',
            ':apellidos' => $pasajero['apellido'] ?? '',
            ':pasaporte' => $pasajero['pasaporte'] ?? '',
            ':fecha_nacimiento' => $pasajero['fecha_nacimiento'] ?? null,
            ':id_pais' => $pasajero['id_pais'] ?? null,
            ':genero' => $pasajero['genero'] ?? '',
            ':alimentacion' => $pasajero['alimentacion'] ?? '',
            ':numero_vuelo' => $pasajero['numero_vuelo'] ?? '',
            ':fecha_hora_llegada' => $pasajero['fecha_hora_llegada'] ?? null,
            ':fecha_hora_salida' => $pasajero['fecha_hora_salida'] ?? null,
            ':informacion_adicional' => $pasajero['informacion_adicional'] ?? '',
            ':tipo' => $index
        ]);

        $id_pasajero = $pdo->lastInsertId(); // 🔥 Obtener el ID del pasajero recién insertado

        // 📌 Insertar experiencias adicionales
        if (!empty($pasajero['experiencias_adicionales'])) {
            foreach ($pasajero['experiencias_adicionales'] as $id_experiencia_ad) {
                $stmt_extra = $pdo->prepare("
                    INSERT INTO huespedes_extras (id_reserva, id_pasajero, id_experiencia_adicional) 
                    VALUES (:id_reserva, :id_pasajero, :id_experiencia_adicional)
                ");
                $stmt_extra->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_pasajero' => $id_pasajero,
                    ':id_experiencia_adicional' => $id_experiencia_ad
                ]);
            }
        }

        // 📌 Insertar costos adicionales
        if (!empty($pasajero['costos_adicionales'])) {
            foreach ($pasajero['costos_adicionales'] as $id_costo_ad) {
                $stmt_extra = $pdo->prepare("
                    INSERT INTO huespedes_extras (id_reserva, id_pasajero, id_adicional_otros) 
                    VALUES (:id_reserva, :id_pasajero, :id_adicional_otros)
                ");
                $stmt_extra->execute([
                    ':id_reserva' => $id_reserva,
                    ':id_pasajero' => $id_pasajero,
                    ':id_adicional_otros' => $id_costo_ad
                ]);
            }
        }

        // 📌 Insertar otro adicional (manual)
        if (!empty($pasajero['nombre_otro']) && !empty($pasajero['precio_otro'])) {
            $stmt_extra = $pdo->prepare("
                INSERT INTO huespedes_extras (id_reserva, id_pasajero, nombre_otro, precio_otro) 
                VALUES (:id_reserva, :id_pasajero, :nombre_otro, :precio_otro)
            ");
            $stmt_extra->execute([
                ':id_reserva' => $id_reserva,
                ':id_pasajero' => $id_pasajero,
                ':nombre_otro' => $pasajero['nombre_otro'],
                ':precio_otro' => $pasajero['precio_otro']
            ]);
        }
    }


    } else {
        error_log("⚠️ No hay pasajeros en la solicitud.");
    }

    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'Reserva guardada con éxito.';

} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("❌ Error en la transacción: " . $e->getMessage());
}

restore_error_handler();
header('Content-Type: application/json');
echo json_encode($response);
?>
