<?php
/**
 * Controlador API
 * Responsabilidad: Gestionar endpoints API para AJAX
 */

namespace Controllers;

use Models\Usuario;
use Patrones\DecoradorNotificacion;
use Utils\GestorAutenticacion;
use Exception;

class ControladorAPI 
{
    private GestorAutenticacion $gestorAuth;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        
        // Configurar headers para API
        header('Content-Type: application/json');
        
        // Endpoints públicos que no requieren autenticación
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $publicEndpoints = [
            '/artesanoDigital/api/notificaciones'
        ];
        
        // Solo verificar autenticación para endpoints protegidos
        if (!in_array($requestUri, $publicEndpoints) && !$this->gestorAuth->estaAutenticado()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }

    /**
     * Obtiene notificaciones del usuario actual
     */
    public function obtenerNotificaciones(): void 
    {
        try {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            
            // Por ahora retornamos datos de prueba
            $notificaciones = [
                [
                    'id' => 1,
                    'tipo' => 'nuevo_pedido',
                    'mensaje' => 'Nuevo pedido recibido por $85.00',
                    'leida' => false,
                    'fecha' => '2024-12-01 10:30:00'
                ],
                [
                    'id' => 2,
                    'tipo' => 'stock_bajo',
                    'mensaje' => 'Stock bajo en Mola Tradicional (2 unidades)',
                    'leida' => false,
                    'fecha' => '2024-12-01 09:15:00'
                ]
            ];

            echo json_encode([
                'exitoso' => true,
                'notificaciones' => $notificaciones
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Marca una notificación como leída
     */
    public function marcarNotificacionLeida(): void 
    {
        try {
            $idNotificacion = (int)($_POST['id'] ?? 0);
            
            if ($idNotificacion <= 0) {
                http_response_code(400);
                echo json_encode([
                    'exitoso' => false,
                    'mensaje' => 'ID de notificación inválido'
                ]);
                return;
            }

            // Implementar marcado como leída
            // Por ahora simulamos éxito
            echo json_encode([
                'exitoso' => true,
                'mensaje' => 'Notificación marcada como leída'
            ]);

        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene información del carrito (contador de productos)
     */
    public function obtenerInfoCarrito(): void 
    {
        try {
            header('Content-Type: application/json');
            $cantidad = 0;
            $total = 0.00;
            
            // Verificamos autenticación
            if ($this->gestorAuth->estaAutenticado()) {
                $usuario = $this->gestorAuth->obtenerUsuarioActual();
                
                // Creamos instancia del modelo carrito
                require_once dirname(__FILE__) . '/../models/Carrito.php';
                $modeloCarrito = new \Models\Carrito();
                
                // Obtenemos datos reales
                $cantidad = $modeloCarrito->contarProductos($usuario['id_usuario']);
                $total = $modeloCarrito->calcularTotal($usuario['id_usuario']);
                
                // Actualizamos el contador en sesión para el header
                $_SESSION['carrito_total'] = $cantidad;
            } else {
                // Carrito de sesión para usuarios no autenticados
                if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
                    foreach ($_SESSION['carrito'] as $item) {
                        $cantidad += (int)$item['cantidad'];
                        $total += (float)$item['precio'] * (int)$item['cantidad'];
                    }
                }
                $_SESSION['carrito_total'] = $cantidad;
            }
            
            echo json_encode([
                'exitoso' => true,
                'cantidad_productos' => $cantidad,
                'total' => $total
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener info del carrito: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error al obtener información del carrito'
            ]);
        }
    }

    /**
     * Búsqueda de productos para autocompletado
     */
    public function buscarProductos(): void 
    {
        try {
            $termino = $_GET['q'] ?? '';
            
            if (strlen($termino) < 2) {
                echo json_encode([
                    'exitoso' => true,
                    'productos' => []
                ]);
                return;
            }

            // Por ahora retornamos datos de prueba
            $productos = [
                ['id' => 1, 'nombre' => 'Mola Tradicional', 'precio' => 85.00],
                ['id' => 2, 'nombre' => 'Vasija de Cerámica', 'precio' => 45.00]
            ];

            echo json_encode([
                'exitoso' => true,
                'productos' => $productos
            ]);

        } catch (Exception $e) {
            error_log("Error en búsqueda de productos: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'exitoso' => false,
                'mensaje' => 'Error en búsqueda'
            ]);
        }
    }
}
