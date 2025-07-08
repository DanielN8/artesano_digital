<?php
/**
 * Punto de entrada principal del sistema
 * Responsabilidad: Enrutamiento y configuración inicial
 */

// Cargar autoloader de Composer si existe, sino usar autoloader personalizado
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($clase) {
        // Convertir namespace a ruta de archivo
        $archivo = __DIR__ . '/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $clase) . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
        }
    });
}

// Incluir archivos necesarios
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Producto.php';
require_once __DIR__ . '/controllers/ControladorAuth.php';
require_once __DIR__ . '/controllers/ControladorInicio.php';
require_once __DIR__ . '/controllers/ControladorProductos.php';

// Cargar variables de entorno
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} else {
    // Cargar .env manualmente si no hay Composer
    if (file_exists(__DIR__ . '/.env')) {
        $lineas = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            if (strpos($linea, '=') !== false && strpos($linea, '#') !== 0) {
                list($clave, $valor) = explode('=', $linea, 2);
                $_ENV[trim($clave)] = trim($valor);
            }
        }
    }
}

// Configuración de errores según entorno
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de zona horaria
date_default_timezone_set('America/Panama');

// Configuración de sesión segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);

/**
 * Router simple para manejar rutas
 */
class Router 
{
    private array $rutas = [];

    public function agregar(string $metodo, string $ruta, callable $controlador): void 
    {
        $this->rutas[] = [
            'metodo' => strtoupper($metodo),
            'ruta' => $ruta,
            'controlador' => $controlador
        ];
    }

    public function ejecutar(): void 
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover slash final si existe
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->rutas as $ruta) {
            if ($ruta['metodo'] === $metodo && $this->coincideRuta($ruta['ruta'], $uri)) {
                call_user_func($ruta['controlador']);
                return;
            }
        }

        // Ruta no encontrada
        http_response_code(404);
        include 'views/errors/404.php';
    }

    private function coincideRuta(string $patron, string $uri): bool 
    {
        // Convertir patrón a regex
        $patron = preg_replace('/\{[^}]+\}/', '([^/]+)', $patron);
        $patron = '#^' . $patron . '$#';
        
        return preg_match($patron, $uri);
    }
}

// Crear router
$router = new Router();

// Definir rutas principales
$router->agregar('GET', '/', function() {
    $controlador = new Controllers\ControladorInicio();
    $controlador->mostrarInicio();
});

// Rutas de autenticación
$router->agregar('GET', '/login', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->mostrarLogin();
});

$router->agregar('POST', '/login', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->procesarLogin();
});

$router->agregar('GET', '/registro', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->mostrarRegistro();
});

$router->agregar('POST', '/registro', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->procesarRegistro();
});

$router->agregar('GET', '/logout', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->logout();
});

$router->agregar('GET', '/recuperar-contrasena', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->mostrarRecuperacion();
});

$router->agregar('POST', '/recuperar-contrasena', function() {
    $controlador = new Controllers\ControladorAuth();
    $controlador->procesarRecuperacion();
});

// Rutas de productos (públicas)
$router->agregar('GET', '/productos', function() {
    $controlador = new Controllers\ControladorProductos();
    $controlador->mostrarCatalogo();
});

$router->agregar('GET', '/producto/{id}', function() {
    $id = (int)basename($_SERVER['REQUEST_URI']);
    $controlador = new Controllers\ControladorProductos();
    $controlador->mostrarDetalle($id);
});

// Rutas de cliente (requieren autenticación)
$router->agregar('GET', '/cliente/dashboard', function() {
    $controlador = new Controllers\ControladorCliente();
    $controlador->mostrarDashboard();
});

$router->agregar('GET', '/carrito', function() {
    $controlador = new Controllers\ControladorCarrito();
    $controlador->mostrarCarrito();
});

$router->agregar('POST', '/carrito/agregar', function() {
    $controlador = new Controllers\ControladorCarrito();
    $controlador->agregarProducto();
});

$router->agregar('POST', '/carrito/actualizar', function() {
    $controlador = new Controllers\ControladorCarrito();
    $controlador->actualizarCantidad();
});

$router->agregar('POST', '/carrito/eliminar', function() {
    $controlador = new Controllers\ControladorCarrito();
    $controlador->eliminarProducto();
});

$router->agregar('GET', '/checkout', function() {
    $controlador = new Controllers\ControladorCheckout();
    $controlador->mostrarCheckout();
});

$router->agregar('POST', '/checkout/procesar', function() {
    $controlador = new Controllers\ControladorCheckout();
    $controlador->procesarPago();
});

// Rutas de artesano (requieren autenticación)
$router->agregar('GET', '/artesano/dashboard', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->mostrarDashboard();
});

$router->agregar('GET', '/artesano/productos', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->gestionarProductos();
});

$router->agregar('POST', '/artesano/productos/crear', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->crearProducto();
});

$router->agregar('POST', '/artesano/productos/actualizar', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->actualizarProducto();
});

$router->agregar('GET', '/artesano/pedidos', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->gestionarPedidos();
});

$router->agregar('POST', '/artesano/pedidos/actualizar-estado', function() {
    $controlador = new Controllers\ControladorArtesano();
    $controlador->actualizarEstadoPedido();
});

// Rutas API para AJAX
$router->agregar('GET', '/api/notificaciones', function() {
    $controlador = new Controllers\ControladorAPI();
    $controlador->obtenerNotificaciones();
});

$router->agregar('POST', '/api/notificaciones/marcar-leida', function() {
    $controlador = new Controllers\ControladorAPI();
    $controlador->marcarNotificacionLeida();
});

// Ejecutar router
try {
    $router->ejecutar();
} catch (Exception $e) {
    error_log("Error en router: " . $e->getMessage());
    http_response_code(500);
    include 'views/errors/500.php';
}
