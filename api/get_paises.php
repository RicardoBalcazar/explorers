<?php
include_once '../php/config.php';

// Consulta para obtener los países
$query = $pdo->prepare("SELECT id_pais, nombre_pais FROM paises");
$query->execute();
$paises = $query->fetchAll(PDO::FETCH_ASSOC);

// Generar las opciones del select en formato JSON
header('Content-Type: application/json');
echo json_encode($paises);
?>