<?php
require_once 'config.php';

header('Content-Type: application/json');

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m'); 
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

try {
    $stmt = $pdo->prepare("
        SELECT r.id_reserva, r.codigo_reserva, r.estado_reserva, r.check_in, r.check_out, 
               c.nombre_cliente, c.color_cliente, h.id_cant_hab AS habitacion
        FROM reservas r
        JOIN clientes c ON r.id_cliente = c.id_cliente
        JOIN reservas_hab h ON r.id_reserva = h.id_reserva
        WHERE MONTH(r.check_in) = :mes AND YEAR(r.check_in) = :anio
        OR MONTH(r.check_out) = :mes AND YEAR(r.check_out) = :anio
    ");
    
    $stmt->execute(['mes' => $mes, 'anio' => $anio]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$reservas) {
        echo json_encode([]);
        exit;
    }

    $reservasAgrupadas = [];
    foreach ($reservas as $reserva) {
        if (!isset($reservasAgrupadas[$reserva['id_reserva']])) {
            $reservasAgrupadas[$reserva['id_reserva']] = [
                'codigo_reserva' => $reserva['codigo_reserva'],
                'estado_reserva' => $reserva['estado_reserva'],
                'check_in' => $reserva['check_in'],
                'check_out' => $reserva['check_out'],
                'nombre_cliente' => $reserva['nombre_cliente'],
                'color_cliente' => $reserva['color_cliente'],
                'habitaciones' => []
            ];
        }
        $reservasAgrupadas[$reserva['id_reserva']]['habitaciones'][] = $reserva['habitacion'];
    }

    echo json_encode($reservasAgrupadas);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener reservas: ' . $e->getMessage()]);
    exit;
}
?>
