<?php
// 🔹 Detectar si estamos en LOCALHOST o en PRODUCCIÓN
$servidor_local = ['127.0.0.1', '::1', 'localhost'];
$es_local = in_array($_SERVER['HTTP_HOST'], $servidor_local);

// 🔹 Configuración de la base de datos
if ($es_local) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rblweb_reservas');
    define('DB_USER', 'root');
    define('DB_PASS', '0502Jrpm2707*');
    define('BASE_URL', 'http://localhost/reservas/');
} else {
    define('DB_HOST', 'tu_host_remoto');
    define('DB_NAME', 'rblweb_reservas');
    define('DB_USER', 'tu_usuario_remoto');
    define('DB_PASS', 'tu_contraseña_remota');
    define('BASE_URL', 'https://reservas.explorersinn.com/');
}

// 🔹 Conexión a la base de datos con PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 🔹 Obtener configuración del hotel desde la base de datos
$query = $pdo->prepare("SELECT * FROM config_hotel WHERE subdominio = :subdominio LIMIT 1");
$subdominio = $es_local ? 'localhost' : $_SERVER['HTTP_HOST'];
$query->bindParam(':subdominio', $subdominio, PDO::PARAM_STR);
$query->execute();
$config_hotel = $query->fetch(PDO::FETCH_ASSOC);

// 🔹 Definir constantes para el hotel
if ($config_hotel) {
    define('HOTEL_NOMBRE', $config_hotel['nombre_hotel']);
    define('HOTEL_LOGO', BASE_URL . $config_hotel['logo_original']);
    define('HOTEL_LOGO_WHITE', BASE_URL . $config_hotel['logo_white']);
    define('HOTEL_COLOR_PRIMARIO', $config_hotel['color_primario']);
    define('HOTEL_COLOR_SECUNDARIO', $config_hotel['color_secundario']);
    define('HOTEL_COLOR_TERCIARIO', $config_hotel['color_terciario']);
} else {
    // 🔹 Valores por defecto en caso de error
    define('HOTEL_NOMBRE', 'Hotel Default');
    define('HOTEL_LOGO', BASE_URL . 'img/default_logo.png');
    define('HOTEL_LOGO_WHITE', BASE_URL . 'img/default_logo_white.png');
    define('HOTEL_COLOR_PRIMARIO', '#000000');
    define('HOTEL_COLOR_SECUNDARIO', '#FFFFFF');
    define('HOTEL_COLOR_TERCIARIO', '#CCCCCC');
}

?>
