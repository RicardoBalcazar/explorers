<?php
header('Content-Type: application/json');
include '../php/config.php'; // Conexión a la base de datos

try {
    $stmt = $pdo->query("SELECT id_experiencia_adicional, nombre_experiencia, descripcion, precio FROM experiencias_ad");
    $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($experiencias);
} catch (Exception $e) {
    echo json_encode(["error" => "❌ Error al obtener experiencias adicionales: " . $e->getMessage()]);
}
?>
