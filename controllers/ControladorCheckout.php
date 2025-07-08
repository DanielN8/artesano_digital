<?php
/**
 * Controlador de Checkout
 * Responsabilidad: Gestionar el proceso de compra
 */

namespace Controllers;

use Models\Carrito;
use Models\Pedido;
use Patrones\EstrategiaMetodoPago;
use Patrones\EstrategiaTarjeta;
use Patrones\EstrategiaYappy;
use Utils\GestorAutenticacion;
use Exception;


class ControladorCheckout 
{
    private GestorAutenticacion $gestorAuth;
    private Carrito $modeloCarrito;
    private Pedido $modeloPedido;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloCarrito = new Carrito();
        $this->modeloPedido = new Pedido();
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }

    // Paso 1: Dirección
    public function direccion() {
        // Debug - Guardar en un archivo de log que se recibió la solicitud
        file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - Método: ' . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
        
        // Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $this->gestorAuth->generarTokenCSRF();
            file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - Generado nuevo token CSRF' . "\n", FILE_APPEND);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug - Guardar datos recibidos
            file_put_contents(__DIR__ . '/../debug_checkout.log', date('Y-m-d H:i:s') . ' - POST: ' . print_r($_POST, true) . "\n", FILE_APPEND);
            
            $_SESSION['checkout_direccion'] = [
                'nombre' => $_POST['nombre'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'ciudad' => $_POST['ciudad'] ?? '',
                'telefono' => $_POST['telefono'] ?? ''
            ];
            header('Location: /artesanoDigital/checkout/pago');
            exit;
        }
        $this->cargarVista('checkout/direccion');
    }

    // Paso 2: Pago
    public function pago() {
        if (empty($_SESSION['checkout_direccion'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $metodo = $_POST['metodo_pago'] ?? '';
            $datos = [];
            if ($metodo === 'tarjeta') {
                $datos = [
                    'nombre_titular' => $_POST['nombre_tarjeta'] ?? '',
                    'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
                    'fecha_expiracion' => $_POST['expiracion'] ?? '',
                    'cvv' => $_POST['cvv'] ?? ''
                ];
            } elseif ($metodo === 'yappy') {
                $datos = [
                    'telefono' => $_POST['telefono_yappy'] ?? ''
                ];
            }
            $_SESSION['checkout_metodo_pago'] = $metodo;
            $_SESSION['checkout_datos_pago'] = $datos;
            header('Location: /artesanoDigital/checkout/factura');
            exit;
        }
        $this->cargarVista('checkout/pago');
    }

    // Paso 3: Factura
    public function factura() {
        if (empty($_SESSION['checkout_direccion']) || empty($_SESSION['checkout_metodo_pago'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->gestorAuth->verificarTokenCSRF($token)) {
                die('Token CSRF inválido');
            }
            header('Location: /artesanoDigital/checkout/completado');
            exit;
        }
        $this->cargarVista('checkout/factura');
    }

    // Paso 4: Completado
    public function completado() {
        if (empty($_SESSION['checkout_direccion']) || empty($_SESSION['checkout_metodo_pago']) || empty($_SESSION['checkout_datos_pago'])) {
            header('Location: /artesanoDigital/checkout/direccion');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->gestorAuth->verificarTokenCSRF($token)) {
                die('Token CSRF inválido');
            }
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $carrito = $_SESSION['carrito'] ?? [];
            $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carrito));
            $metodo = $_SESSION['checkout_metodo_pago'];
            $datosPago = $_SESSION['checkout_datos_pago'];
            $estrategia = $this->obtenerEstrategiaPago($metodo);
            $resultadoPago = $estrategia->procesarPago($total, $datosPago);
            if (!$resultadoPago['exitoso']) {
                $_SESSION['checkout_error_pago'] = $resultadoPago['mensaje'];
                header('Location: /artesanoDigital/checkout/pago');
                exit;
            }
            // Simular creación de pedido y limpiar carrito
            unset($_SESSION['carrito']);
            unset($_SESSION['checkout_direccion'], $_SESSION['checkout_metodo_pago'], $_SESSION['checkout_datos_pago']);
            $this->cargarVista('checkout/completado');
            exit;
        }
        $this->cargarVista('checkout/completado');
    }

    private function obtenerEstrategiaPago(string $metodo): EstrategiaMetodoPago {
        switch ($metodo) {
            case 'tarjeta':
                return new EstrategiaTarjeta();
            case 'yappy':
                return new EstrategiaYappy();
            default:
                return new EstrategiaTarjeta();
        }
    }

    private function cargarVista(string $vista, array $datos = []) {
        extract($datos);
        include __DIR__ . '/../views/' . $vista . '.php';
    }
    
    // Vista única del proceso de checkout (todo en uno)
    public function proceso() {
        if (!$this->gestorAuth->estaAutenticado()) {
            header('Location: /artesanoDigital/login?redirect=checkout');
            exit;
        }
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
        $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);
        
        $datos = [
            'productos' => $productos,
            'total' => $total,
            'usuario' => $usuario
        ];
        
        // Muestra la vista cart_process.php con su lógica de pasos
        $this->cargarVista('checkout/cart_process', $datos);
    }
}
