<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../php/config.php'; // Ajusta la ruta según tu estructura

if (!isset($_GET["id_tipo_habitacion"])) {
    echo json_encode(["error" => "Falta el parámetro id_tipo_habitacion"]);
    exit;
}

$id_tipo_habitacion = intval($_GET["id_tipo_habitacion"]);

try {
    $stmt = $pdo->prepare("SELECT id_variante, nombre_variante, capacidad FROM variantes_hab WHERE id_tipo_habitacion = ?");
    $stmt->execute([$id_tipo_habitacion]);
    $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($variantes);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener variantes: " . $e->getMessage()]);
}
?>
