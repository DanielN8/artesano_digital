<?php
/**
 * Controlador de Artesano
 * Responsabilidad: Gestionar el panel de artesanos
 */

namespace Controllers;

use Models\Usuario;
use Models\Pedido;
use Utils\GestorAutenticacion;
use Exception;

// Incluir el modelo Producto (que no tiene namespace)
require_once dirname(__FILE__) . '/../models/Producto.php';

class ControladorArtesano 
{
    private GestorAutenticacion $gestorAuth;
    private Usuario $modeloUsuario;
    private \Producto $modeloProducto;
    private Pedido $modeloPedido;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloUsuario = new Usuario();
        $this->modeloProducto = new \Producto();
        $this->modeloPedido = new Pedido();
        
        // Verificar que el usuario esté autenticado y sea artesano
        if (!$this->gestorAuth->estaAutenticado() || 
            $this->gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'artesano') {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }

    /**
     * Muestra el dashboard del artesano
     */
    public function mostrarDashboard(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        
        $datos = [
            'titulo' => 'Panel de Artesano',
            'usuario' => $usuario,
            'estadisticas' => $this->obtenerEstadisticas($usuario['id_usuario'] ?? 0),
            'pedidos_recientes' => [] // Placeholder para pedidos
        ];

        $this->cargarVista('artesano/dashboard', $datos);
    }

    /**
     * Gestiona los productos del artesano
     */
    public function gestionarProductos(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $productos = []; // Implementar obtención de productos del artesano
        
        $datos = [
            'titulo' => 'Mis Productos',
            'productos' => $productos
        ];

        $this->cargarVista('artesano/productos', $datos);
    }

    /**
     * Muestra formulario para crear producto
     */
    public function mostrarCrearProducto(): void 
    {
        $datos = [
            'titulo' => 'Crear Producto'
        ];

        $this->cargarVista('artesano/crear-producto', $datos);
    }

    /**
     * Procesa la creación de un producto
     */
    public function crearProducto(): void 
    {
        try {
            // Implementar creación de producto
            $this->responderJSON([
                'exitoso' => true,
                'mensaje' => 'Producto creado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error al crear producto: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error al crear producto'
            ]);
        }
    }

    /**
     * Actualiza un producto
     */
    public function actualizarProducto(): void 
    {
        try {
            // Implementar actualización de producto
            $this->responderJSON([
                'exitoso' => true,
                'mensaje' => 'Producto actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error al actualizar producto'
            ]);
        }
    }

    /**
     * Gestiona los pedidos del artesano
     */
    public function gestionarPedidos(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $pedidos = []; // Implementar obtención de pedidos
        
        $datos = [
            'titulo' => 'Gestión de Pedidos',
            'pedidos' => $pedidos
        ];

        $this->cargarVista('artesano/pedidos', $datos);
    }

    /**
     * Actualiza el estado de un pedido
     */
    public function actualizarEstadoPedido(): void 
    {
        try {
            $idPedido = (int)($_POST['id_pedido'] ?? 0);
            $estado = $_POST['estado'] ?? '';

            if ($idPedido <= 0 || empty($estado)) {
                $this->responderJSON([
                    'exitoso' => false,
                    'mensaje' => 'Datos inválidos'
                ]);
                return;
            }

            $resultado = $this->modeloPedido->actualizarEstado($idPedido, $estado);

            $this->responderJSON([
                'exitoso' => $resultado,
                'mensaje' => $resultado ? 'Estado actualizado' : 'Error al actualizar estado'
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar estado de pedido: " . $e->getMessage());
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtiene estadísticas del artesano
     * @param int $idUsuario
     * @return array
     */
    private function obtenerEstadisticas(int $idUsuario): array 
    {
        // Por ahora retornamos datos de prueba
        return [
            'productos_activos' => 5,
            'ventas_totales' => 12,
            'ingresos_totales' => 450.00,
            'valoracion_promedio' => '4.5',
            'pedidos_pendientes' => 3
        ];
    }

    /**
     * Carga una vista
     * @param string $vista
     * @param array $datos
     */
    private function cargarVista(string $vista, array $datos = []): void 
    {
        extract($datos);
        include "views/{$vista}.php";
    }

    /**
     * Responde con JSON
     * @param array $datos
     */
    private function responderJSON(array $datos): void 
    {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}
