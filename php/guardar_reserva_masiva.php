<?php
require_once 'config.php';

$response = ['success' => false, 'message' => ''];
$logFile = 'error_log.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the incoming POST data
    error_log("Datos recibidos:\n" . print_r($_POST, true), 3, $logFile);

    $codigo_reserva = $_POST['codigo_reserva'];
    $id_cliente = $_POST['id_cliente'];
    $id_experiencia = $_POST['id_experiencia'];
    $estado_reserva = $_POST['estado_reserva'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $id_tarifa = $_POST['id_tarifa'];
    
    // Verificar la conexión antes de proceder
    if ($pdo === false) {
        $response['message'] = 'Error en la conexión a la base de datos';
        error_log($response['message'], 3, $logFile);
        echo json_encode($response);
        exit;
    }

    // Insertar en la tabla reservas
    $query_reserva = "INSERT INTO reservas (codigo_reserva, id_cliente, id_experiencia, estado_reserva, check_in, check_out, tarifas, total_habitaciones, total_adicionales, id_descuento, monto_descuento, id_forma_pago, monto_comision, total_reserva, usuario_creacion, fecha_creacion, usuario_modificacion, fecha_ultima_modificacion) VALUES (?, ?, ?, ?, ?, ?, ?, 0.00, 0.00, 1, 0.00, 1, 0.00, 0.00, 1, NOW(), 1, NOW())";
    $stmt_reserva = $pdo->prepare($query_reserva);
    if ($stmt_reserva === false) {
        $response['message'] = 'Error preparing statement: ' . $pdo->errorInfo()[2];
        error_log($response['message'], 3, $logFile);
        echo json_encode($response);
        exit;
    }
    
    $stmt_reserva->execute([$codigo_reserva, $id_cliente, $id_experiencia, $estado_reserva, $check_in, $check_out, $id_tarifa]);
    
    if ($stmt_reserva) {
        $id_reserva = $pdo->lastInsertId();

        // Insertar en la tabla reservas_hab
        foreach ($_POST['habitaciones'] as $index => $id_cant_hab) {
            $id_tipo_habitacion = $_POST['tipo_habitacion'][$index];
            $id_variante = $_POST['variante_habitacion'][$index];
            $adultos = $_POST['adultos'][$index];
            $ninos = $_POST['ninos'][$index];
            $infantes = $_POST['infantes'][$index];
            $tc = $_POST['tc'][$index];
            $precio_asignado = 0.00;

            $query_habitacion = "INSERT INTO reservas_hab (id_reserva, id_cant_hab, id_tipo_habitacion, id_variante, adultos, ninos, infantes, tc, precio_asignado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_habitacion = $pdo->prepare($query_habitacion);
            if ($stmt_habitacion === false) {
                $response['message'] = 'Error preparing statement: ' . $pdo->errorInfo()[2];
                error_log($response['message'], 3, $logFile);
                echo json_encode($response);
                exit;
            }
            
            $stmt_habitacion->execute([$id_reserva, $id_cant_hab, $id_tipo_habitacion, $id_variante, $adultos, $ninos, $infantes, $tc, $precio_asignado]);
            if (!$stmt_habitacion) {
                $response['message'] = 'Error executing statement: ' . $stmt_habitacion->errorInfo()[2];
                error_log($response['message'], 3, $logFile);
                echo json_encode($response);
                exit;
            }
        }

        $response['success'] = true;
        $response['message'] = 'Reserva creada exitosamente';
    } else {
        $response['message'] = 'Error al crear la reserva: ' . $stmt_reserva->errorInfo()[2];
        error_log($response['message'], 3, $logFile);
    }

    $stmt_reserva = null;
} else {
    $response['message'] = 'Método no permitido';
    error_log($response['message'], 3, $logFile);
}

$pdo = null;
header('Content-Type: application/json');
echo json_encode($response);
?>