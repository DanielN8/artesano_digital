<?php
/**
 * Controlador de Cliente
 * Responsabilidad: Gestionar el panel de clientes
 */

namespace Controllers;

use Utils\GestorAutenticacion;

class ControladorCliente 
{
    private GestorAutenticacion $gestorAuth;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        
        // Verificar que el usuario esté autenticado y sea cliente
        if (!$this->gestorAuth->estaAutenticado() || 
            $this->gestorAuth->obtenerUsuarioActual()['tipo_usuario'] !== 'cliente') {
            header('Location: /artesanoDigital/login');
            exit;
        }
    }

    /**
     * Muestra el dashboard del cliente
     */
    public function mostrarDashboard(): void 
    {
        $usuario = $this->gestorAuth->obtenerUsuarioActual();
        
        $datos = [
            'titulo' => 'Panel de Cliente',
            'usuario' => $usuario,
            'pedidos_recientes' => $this->obtenerPedidosRecientes($usuario['id_usuario'] ?? 0),
            'estadisticas' => [
                'pedidos_totales' => 0,
                'productos_favoritos' => 0,
                'artesanos_seguidos' => 0,
                'total_compras' => 0
            ],
            'favoritos_recientes' => []
        ];

        $this->cargarVista('cliente/dashboard', $datos);
    }

    /**
     * Obtiene pedidos recientes del cliente
     * @param int $idUsuario
     * @return array
     */
    private function obtenerPedidosRecientes(int $idUsuario): array 
    {
        // Por ahora retornamos datos de prueba
        return [];
        return [
            [
                'id' => 1,
                'fecha' => '2024-12-01',
                'total' => 85.00,
                'estado' => 'enviado'
            ],
            [
                'id' => 2,
                'fecha' => '2024-11-28',
                'total' => 45.00,
                'estado' => 'entregado'
            ]
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
}
