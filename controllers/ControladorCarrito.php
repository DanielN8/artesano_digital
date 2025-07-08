<?php
/**
 * Controlador de Carrito
 * Responsabilidad: Gestionar el carrito de compras
 */

namespace Controllers;

use Models\Carrito;
use Utils\GestorAutenticacion;

// Incluir el modelo Producto (que no tiene namespace)
require_once dirname(__FILE__) . '/../models/Producto.php';

class ControladorCarrito 
{
    private GestorAutenticacion $gestorAuth;
    private Carrito $modeloCarrito;
    private \Producto $modeloProducto;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloCarrito = new Carrito();
        $this->modeloProducto = new \Producto();
    }

    /**
     * Muestra el carrito de compras
     */
    public function mostrarCarrito(): void 
    {
        $productos = [];
        $total = 0;

        if ($this->gestorAuth->estaAutenticado()) {
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $productos = $this->modeloCarrito->obtenerProductos($usuario['id_usuario']);
            $total = $this->modeloCarrito->calcularTotal($usuario['id_usuario']);
        }

        $datos = [
            'titulo' => 'Carrito de Compras',
            'productos' => $productos,
            'total' => $total
        ];

        $this->cargarVista('carrito/mostrar', $datos);
    }

    /**
     * Agrega un producto al carrito
     */
    public function agregarProducto(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Debes iniciar sesión para agregar productos al carrito'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        if ($idProducto <= 0 || $cantidad <= 0) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'Datos inválidos'
            ]);
            return;
        }

        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->agregarProducto(
            $usuario['id_usuario'], 
            $idProducto, 
            $cantidad
        );

        $this->responderJSON($resultado);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     */
    public function actualizarCantidad(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'No autorizado'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->actualizarCantidad(
            $usuario['id_usuario'], 
            $idProducto, 
            $cantidad
        );

        $this->responderJSON($resultado);
    }

    /**
     * Elimina un producto del carrito
     */
    public function eliminarProducto(): void 
    {
        if (!$this->gestorAuth->estaAutenticado()) {
            $this->responderJSON([
                'exitoso' => false,
                'mensaje' => 'No autorizado'
            ]);
            return;
        }

        $idProducto = (int)($_POST['id_producto'] ?? 0);
        
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        $resultado = $this->modeloCarrito->eliminarProducto(
            $usuario['id_usuario'], 
            $idProducto
        );

        $this->responderJSON($resultado);
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
