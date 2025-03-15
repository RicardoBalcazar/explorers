<?php
include '../php/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id_adicional_otros, nombre_adicional, precio FROM costos_ad");
    $stmt->execute();
    $costos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($costos);
} catch (Exception $e) {
    echo json_encode(["error" => "❌ Error al obtener los costos adicionales: " . $e->getMessage()]);
}
?>
