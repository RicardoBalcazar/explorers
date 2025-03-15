<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../php/config.php'; // Ajusta la ruta según tu estructura

try {
    $stmt = $pdo->prepare("SELECT id_experiencia, nombre_experiencia, noches FROM experiencias WHERE estado = 'activo'");
    $stmt->execute();
    $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($experiencias);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener experiencias: " . $e->getMessage()]);
}
?>
