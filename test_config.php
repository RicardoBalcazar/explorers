<?php
require_once 'php/config.php'; // Importar la configuración

echo "<h2>Prueba de Configuración</h2>";
echo "<p><strong>Nombre del Hotel:</strong> " . HOTEL_NOMBRE . "</p>";
echo "<p><strong>Logo del Hotel:</strong> <img src='" . HOTEL_LOGO . "' width='150'></p>";
echo "<p><strong>Logo White:</strong> <img src='" . HOTEL_LOGO_WHITE . "' width='150'></p>";
echo "<p><strong>Color Primario:</strong> <span style='color:" . HOTEL_COLOR_PRIMARIO . ";'>" . HOTEL_COLOR_PRIMARIO . "</span></p>";
echo "<p><strong>Color Secundario:</strong> <span style='color:" . HOTEL_COLOR_SECUNDARIO . ";'>" . HOTEL_COLOR_SECUNDARIO . "</span></p>";
echo "<p><strong>Color Terciario:</strong> <span style='color:" . HOTEL_COLOR_TERCIARIO . ";'>" . HOTEL_COLOR_TERCIARIO . "</span></p>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
?>
