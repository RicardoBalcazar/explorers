<?php
require_once '../php/config.php';

header('Content-Type: application/json');

$prefijo = "EXPLO-";

try {
    // Obtener el último código de reserva registrado
    $stmt = $pdo->query("SELECT codigo_reserva FROM reservas WHERE codigo_reserva LIKE 'EXPLO-%' ORDER BY id_reserva DESC LIMIT 1");
    $ultimaReserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ultimaReserva && preg_match('/(\d+)$/', $ultimaReserva['codigo_reserva'], $matches)) {
        $ultimoNumero = (int)$matches[1]; // Extraer el número final (Ejemplo: "EXPLO-00015" → 15)
    } else {
        $ultimoNumero = 0; // Si no hay reservas, empezamos en 0
    }

    // Generar el nuevo número con 5 dígitos (Ejemplo: "00016")
    $nuevoNumero = str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);
    $nuevoCodigo = "{$prefijo}{$nuevoNumero}";

    echo json_encode(["codigo_reserva" => $nuevoCodigo]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al generar código de reserva: " . $e->getMessage()]);
}
?>
