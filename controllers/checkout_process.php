<?php
/**
 * Controller: checkout_process.php
 * Carga inicial para el proceso de checkout en una sola página
 */

// Asegurarnos de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la configuración de la base de datos y modelos necesarios
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Carrito.php';
require_once __DIR__ . '/../models/Producto.php';

use Models\Carrito;
use Config\Database;

// Verificar si el usuario está autenticado
$usuarioAutenticado = isset($_SESSION['usuario_id']);
$idUsuario = $usuarioAutenticado ? $_SESSION['usuario_id'] : null;

// Si no está autenticado y requiere login, redireccionar
$requireLogin = true; // Cambiar a false para permitir checkout a usuarios no registrados

if ($requireLogin && !$usuarioAutenticado) {
    header('Location: /artesanoDigital/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Instanciar modelo del carrito
$modeloCarrito = new Carrito();

// Obtener productos del carrito
if ($usuarioAutenticado) {
    $productos = $modeloCarrito->obtenerProductos($idUsuario);
    $total = $modeloCarrito->calcularTotal($idUsuario);
} else {
    // Para usuarios no autenticados, usar el carrito de sesión
    $productos = $_SESSION['carrito'] ?? [];
    
    // Calcular total manualmente
    $total = 0;
    foreach ($productos as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }
}

// Si hay un carrito en localStorage y no en sesión, sincronizar
if (isset($_COOKIE['has_cart']) && !isset($_SESSION['carrito_sincronizado'])) {
    // Marcar como sincronizado para no volver a intentarlo
    $_SESSION['carrito_sincronizado'] = true;
    
    // Incluir un script para sincronizar en la primera carga
    $scriptSincronizar = true;
} else {
    $scriptSincronizar = false;
}

// Datos para la vista
$usuario = [];
if ($usuarioAutenticado) {
    // Obtener datos del usuario actual
    $db = Database::obtenerInstancia()->obtenerConexion();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
    $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
}

// Definir título y descripción para el SEO
$titulo = "Finalizar compra | Artesano Digital";
$descripcion = "Complete su pedido de productos artesanales panameños.";

// Incluir la vista
include_once __DIR__ . '/../views/checkout/cart_process.php';
