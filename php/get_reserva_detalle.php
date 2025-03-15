<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id_reserva'])) {
    echo json_encode(['error' => 'Falta el ID de la reserva']);
    exit;
}

$id_reserva = intval($_GET['id_reserva']);

try {
    $stmt = $pdo->prepare(
        "SELECT r.codigo_reserva, r.estado_reserva, 
                DATE_FORMAT(r.check_in, '%d/%m/%Y') AS check_in, 
                DATE_FORMAT(r.check_out, '%d/%m/%Y') AS check_out, 
                u.nombres AS usuario_creacion,
                c.nombre_cliente, c.color_cliente,
                h.id_cant_hab AS habitacion, CONCAT(th.nombre_tipo, ' - ', v.nombre_variante) AS tipo_habitacion,
                h.adultos, h.ninos, h.infantes
        FROM reservas r
        LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
        LEFT JOIN reservas_hab h ON r.id_reserva = h.id_reserva
        LEFT JOIN tipo_hab th ON h.id_tipo_habitacion = th.id_tipo_habitacion
        LEFT JOIN variantes_hab v ON h.id_variante = v.id_variante
        LEFT JOIN usuarios u ON r.usuario_creacion = u.id_usuario
        WHERE r.id_reserva = :id_reserva"
    );
    
    $stmt->execute(['id_reserva' => $id_reserva]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$reservas) {
        echo json_encode(['error' => 'No se encontró la reserva']);
        exit;
    }

    $reservaDetalle = [
        'codigo_reserva' => $reservas[0]['codigo_reserva'],
        'estado_reserva' => $reservas[0]['estado_reserva'],
        'check_in' => $reservas[0]['check_in'],
        'check_out' => $reservas[0]['check_out'],
        'usuario_creacion' => $reservas[0]['usuario_creacion'],
        'nombre_cliente' => $reservas[0]['nombre_cliente'],
        'color_cliente' => $reservas[0]['color_cliente'],
        'habitaciones' => []
    ];
    
    foreach ($reservas as $reserva) {
        $reservaDetalle['habitaciones'][] = [
            'habitacion' => $reserva['habitacion'],
            'tipo_habitacion' => $reserva['tipo_habitacion'],
            'adultos' => $reserva['adultos'],
            'ninos' => $reserva['ninos'],
            'infantes' => $reserva['infantes']
        ];
    }
    
    echo json_encode($reservaDetalle);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener detalle de la reserva: ' . $e->getMessage()]);
    exit;
}
?>
