<?php
/**
 * Punto de entrada temporal - Sistema básico funcionando
 */

// Configuración básica
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Obtener la ruta solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Remover el prefijo del directorio si existe
$basePath = '/artesanoDigital';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

if (empty($uri)) {
    $uri = '/';
}

// Router básico
switch ($uri) {
    case '/':
        // Página de inicio
        $titulo = 'Artesano Digital - Panamá Oeste';
        $descripcion = 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';
        include 'views/inicio.php';
        break;
        
    case '/productos':
        echo "<h1>Catálogo de Productos</h1><p>Próximamente...</p>";
        break;
        
    case '/login':
        echo "<h1>Iniciar Sesión</h1><p>Próximamente...</p>";
        break;
        
    case '/registro':
        echo "<h1>Registro</h1><p>Próximamente...</p>";
        break;
        
    default:
        // 404 - Página no encontrada
        http_response_code(404);
        include 'views/errors/404.php';
        break;
}
?>
