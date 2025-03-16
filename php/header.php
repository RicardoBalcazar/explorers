<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css?v=<?= time(); ?>">
    <script src="js/script_header.js" defer></script>
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Explorers Inn'; ?></title>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <!-- Logo principal -->
            <div class="logo">
                <a href="index.php"><img src="img\explorers-inn-logo-white.png" alt="Explorers Logo"></a>
            </div>
            <!-- Botón hamburguesa -->
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Menú flotante -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!-- Logo en el menú flotante -->
            <img src="img\explorers-inn-logo-white.png" alt="Explorers Logo">
        </div>
        <ul class="menu">
            <li class="menu-title">CLIENTES</li>
            <li><a href="nuevo_cliente.php">Nuevo Cliente</a></li>
            <li><a href="listado_clientes.php">Listado de Clientes</a></li>
            <li class="menu-title">LIQUIDACIONES</li>
            <li><a href="nueva_liquidacion.php">Nueva Liquidación</a></li>
            <li><a href="listado_liquidacion.php">Listado de Liquidación</a></li>
            <li><a href="listado_eliminadas.php">Liquidaciones Eliminadas</a></li>
        </ul>
    </nav>
</body>
</html>
