<?php
require_once '../php/config.php';

header('Content-Type: application/json');

$id_tipo_habitacion = isset($_GET['id_tipo_hab']) ? intval($_GET['id_tipo_hab']) : null;
$id_variante = isset($_GET['id_variante']) ? intval($_GET['id_variante']) : null;

if (!$id_tipo_habitacion) {
    echo json_encode(['error' => 'Falta el parámetro id_tipo_habitacion']);
    exit;
}

try {
    // ✅ Obtener todas las variantes del tipo de habitación
    $stmt = $pdo->prepare("SELECT id_variante, nombre_variante FROM variantes_hab WHERE id_tipo_habitacion = :id_tipo_habitacion");
    $stmt->execute(['id_tipo_habitacion' => $id_tipo_habitacion]);

    $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Marcar la variante seleccionada en la reserva
    foreach ($variantes as &$variante) {
        $variante['seleccionado'] = ($variante['id_variante'] == $id_variante);
    }

    echo json_encode($variantes);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener variantes: ' . $e->getMessage()]);
}
?>

