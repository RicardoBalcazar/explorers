<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../php/config.php'; // Ajusta la ruta según tu estructura

try {
    $stmt = $pdo->prepare("SELECT id_tarifa, nombre_tarifa FROM tarifas");
    $stmt->execute();
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tarifas);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener tarifas: " . $e->getMessage()]);
}
?>
