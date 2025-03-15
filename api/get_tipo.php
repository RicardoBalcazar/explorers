<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../php/config.php'; // Ajusta la ruta según tu estructura

try {
    $stmt = $pdo->prepare("SELECT id_tipo_habitacion, nombre_tipo FROM tipo_hab");
    $stmt->execute();
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tipos);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener tipos de habitación: " . $e->getMessage()]);
}
?>
