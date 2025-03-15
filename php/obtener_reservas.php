<?php
require_once 'config.php';
header('Content-Type: application/json');

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

try {
    $stmt = $pdo->prepare("SELECT check_in, check_out FROM reservas WHERE MONTH(check_in) = :mes AND YEAR(check_in) = :anio OR MONTH(check_out) = :mes AND YEAR(check_out) = :anio");
    $stmt->execute(['mes' => $mes, 'anio' => $anio]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $diasOcupados = [];

    foreach ($reservas as $reserva) {
        $fechaInicio = new DateTime($reserva['check_in']);
        $fechaFin = new DateTime($reserva['check_out']);
        
        while ($fechaInicio <= $fechaFin) {
            $diasOcupados[] = $fechaInicio->format('Y-m-d');
            $fechaInicio->modify('+1 day');
        }
    }

    echo json_encode(array_values(array_unique($diasOcupados)));
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener reservas: ' . $e->getMessage()]);
    exit;
}
