<?php
require_once 'config.php';

// Verificar que las fechas fueron enviadas
if (isset($_POST['check_in']) && isset($_POST['check_out'])) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Consulta para obtener los IDs de las reservas en el rango de fechas
    $query_reservas = "
        SELECT id_reserva
        FROM reservas
        WHERE (check_in BETWEEN :check_in AND :check_out)
           OR (check_out BETWEEN :check_in AND :check_out)
           OR (check_in <= :check_in AND check_out >= :check_out)
    ";

    $stmt_reservas = $pdo->prepare($query_reservas);
    $stmt_reservas->execute(['check_in' => $check_in, 'check_out' => $check_out]);
    $reservas = $stmt_reservas->fetchAll(PDO::FETCH_COLUMN);

    $habitaciones_reservadas = [];

    if (!empty($reservas)) {
        // Consulta para obtener las habitaciones reservadas
        $query_habitaciones = "
            SELECT id_cant_hab
            FROM reservas_hab
            WHERE id_reserva IN (" . implode(',', array_fill(0, count($reservas), '?')) . ")
        ";

        $stmt_habitaciones = $pdo->prepare($query_habitaciones);
        $stmt_habitaciones->execute($reservas);
        $habitaciones_reservadas = $stmt_habitaciones->fetchAll(PDO::FETCH_COLUMN);
    }

    // Devolver las habitaciones reservadas en formato JSON
    echo json_encode($habitaciones_reservadas);
} else {
    echo json_encode([]);
}
?>