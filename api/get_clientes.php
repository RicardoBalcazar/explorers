<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../php/config.php'; // Ajusta la ruta según tu estructura

try {
    // Obtener todos los clientes activos
    $stmt = $pdo->prepare("SELECT id_cliente, nombre_cliente FROM clientes WHERE estado_cliente = 'Activo'");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clientes);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener clientes: " . $e->getMessage()]);
}
?>
