<?php
require_once '../php/config.php'; // Conexión a la base de datos

header('Content-Type: application/json');

// ✅ Validar que el ID de la reserva fue proporcionado
if (!isset($_GET['id_reserva']) || empty($_GET['id_reserva'])) {
    echo json_encode(['error' => 'ID de reserva no proporcionado.']);
    exit;
}

$id_reserva = intval($_GET['id_reserva']);
error_log("🔍 ID de reserva recibido: " . $id_reserva);

try {
    // ✅ Obtener datos de la reserva
    $stmt = $pdo->prepare("
        SELECT 
            r.id_reserva, r.codigo_reserva, r.estado_reserva, 
            DATE_FORMAT(r.check_in, '%Y-%m-%d') AS check_in, 
            DATE_FORMAT(r.check_out, '%Y-%m-%d') AS check_out, 
            r.tarifas, 
            c.id_cliente, c.nombre_cliente, 
            e.id_experiencia, e.nombre_experiencia, 
            h.id_tipo_habitacion, th.nombre_tipo, h.id_variante, vh.nombre_variante, 
            h.adultos, h.ninos, h.infantes, h.tc, 
            u.nombres AS usuario_creacion
        FROM reservas r
        LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
        LEFT JOIN experiencias e ON r.id_experiencia = e.id_experiencia
        LEFT JOIN reservas_hab h ON r.id_reserva = h.id_reserva
        LEFT JOIN tipo_hab th ON h.id_tipo_habitacion = th.id_tipo_habitacion
        LEFT JOIN variantes_hab vh ON h.id_variante = vh.id_variante
        LEFT JOIN usuarios u ON r.usuario_creacion = u.id_usuario
        WHERE r.id_reserva = :id_reserva
    ");
    
    $stmt->execute(['id_reserva' => $id_reserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        echo json_encode(['error' => 'No se encontró la reserva.']);
        exit;
    }

    // ✅ Obtener los pasajeros de la reserva
    $stmt_pasajeros = $pdo->prepare("
        SELECT p.id_pasajero, p.nombres, p.apellidos, p.pasaporte, 
               DATE_FORMAT(p.fecha_nacimiento, '%Y-%m-%d') AS fecha_nacimiento, 
               pa.nombre_pais AS pais, p.genero
        FROM huespedes p
        LEFT JOIN paises pa ON p.id_pais = pa.id_pais
        WHERE p.id_reserva = :id_reserva
    ");
    $stmt_pasajeros->execute(['id_reserva' => $id_reserva]);
    $pasajeros = $stmt_pasajeros->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Obtener adicionales de cada pasajero
    foreach ($pasajeros as &$pasajero) {
        $stmt_extras = $pdo->prepare("
            SELECT id_experiencia_adicional, nombre_otro, precio_otro
            FROM huespedes_extras
            WHERE id_pasajero = :id_pasajero
        ");
        $stmt_extras->execute(['id_pasajero' => $pasajero['id_pasajero']]);
        $pasajero['extras'] = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Estructurar datos finales
    $reservaDetalle = [
        'id_reserva' => $reserva['id_reserva'],
        'codigo_reserva' => $reserva['codigo_reserva'],
        'estado_reserva' => $reserva['estado_reserva'],
        'check_in' => $reserva['check_in'],
        'check_out' => $reserva['check_out'],
        'tarifas' => $reserva['tarifas'],
        'cliente' => [
            'id_cliente' => $reserva['id_cliente'],
            'nombre_cliente' => $reserva['nombre_cliente'],
        ],
        'experiencia' => [
            'id_experiencia' => $reserva['id_experiencia'],
            'nombre_experiencia' => $reserva['nombre_experiencia'],
        ],
        'habitaciones' => [
            'id_tipo_habitacion' => $reserva['id_tipo_habitacion'],
            'nombre_tipo' => $reserva['nombre_tipo'],
            'id_variante' => $reserva['id_variante'],
            'nombre_variante' => $reserva['nombre_variante'],
            'adultos' => $reserva['adultos'],
            'ninos' => $reserva['ninos'],
            'infantes' => $reserva['infantes'],
            'tc' => $reserva['tc'],
        ],
        'pasajeros' => $pasajeros,
        'usuario_creacion' => $reserva['usuario_creacion']
    ];

    $stmt_pasajeros = $pdo->prepare("
    SELECT h.id_pasajero, h.nombres, h.apellidos, h.pasaporte, h.fecha_nacimiento, h.id_pais,
           h.genero, h.alimentacion, h.informacion_adicional, h.numero_vuelo,
           h.fecha_hora_llegada, h.fecha_hora_salida, h.tipo
    FROM huespedes h
    WHERE h.id_reserva = :id_reserva
    ");
    $stmt_pasajeros->execute(['id_reserva' => $id_reserva]);
    $pasajeros = $stmt_pasajeros->fetchAll(PDO::FETCH_ASSOC);

    // 📌 Obtener adicionales de cada pasajero
    foreach ($pasajeros as &$pasajero) {
        $stmt_extras = $pdo->prepare("
            SELECT id_experiencia_adicional, id_adicional_otros, nombre_otro, precio_otro
            FROM huespedes_extras
            WHERE id_pasajero = :id_pasajero
        ");
        $stmt_extras->execute(['id_pasajero' => $pasajero['id_pasajero']]);
        $pasajero['adicionales'] = $stmt_extras->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📌 Agregar los pasajeros al JSON de la reserva
    $reservaDetalle['pasajeros'] = $pasajeros;

    
    echo json_encode($reservaDetalle);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener detalle de la reserva: ' . $e->getMessage()]);
    exit;
}
?>
