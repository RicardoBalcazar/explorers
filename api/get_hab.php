<?php
require_once '../php/config.php';

header('Content-Type: application/json');

try {
    $query = $pdo->query("SELECT id_cant_hab FROM cant_hab ORDER BY id_cant_hab ASC");
    $habitaciones = $query->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($habitaciones);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener habitaciones: ' . $e->getMessage()]);
}
?>
