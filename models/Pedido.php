<?php
/**
 * Modelo Pedido - Gestión de pedidos de compra
 * Responsabilidad: CRUD de pedidos y operaciones relacionadas
 */

namespace Models;

use Config\Database;
use PDO;
use Exception;

class Pedido 
{
    private PDO $conexion;

    public function __construct() 
    {
        $db = Database::obtenerInstancia();
        $this->conexion = $db->obtenerConexion();
    }

    /**
     * Crea un nuevo pedido
     * @param array $datos
     * @return array
     */
    public function crear(array $datos): array 
    {
        try {
            $this->conexion->beginTransaction();

            // Crear el pedido principal
            $sql = "INSERT INTO pedidos (id_usuario, estado, metodo_pago, total, direccion_envio) 
                    VALUES (:id_usuario, 'pendiente', :metodo_pago, :total, :direccion_envio)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                'id_usuario' => $datos['id_usuario'],
                'metodo_pago' => $datos['metodo_pago'],
                'total' => $datos['total'],
                'direccion_envio' => $datos['direccion_envio']
            ]);

            $idPedido = $this->conexion->lastInsertId();

            // Agregar productos al pedido
            foreach ($datos['productos'] as $producto) {
                $sqlProducto = "INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario) 
                               VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";

                $stmtProducto = $this->conexion->prepare($sqlProducto);
                $stmtProducto->execute([
                    'id_pedido' => $idPedido,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio']
                ]);
            }

            $this->conexion->commit();

            return [
                'exitoso' => true,
                'mensaje' => 'Pedido creado exitosamente',
                'id_pedido' => (int)$idPedido
            ];

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al crear pedido: " . $e->getMessage());
            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar el pedido'
            ];
        }
    }

    /**
     * Obtiene pedidos de un usuario
     * @param int $idUsuario
     * @return array
     */
    public function obtenerPorUsuario(int $idUsuario): array 
    {
        try {
            $sql = "SELECT * FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha_pedido DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error al obtener pedidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de un pedido
     * @param int $idPedido
     * @param string $estado
     * @return bool
     */
    public function actualizarEstado(int $idPedido, string $estado): bool 
    {
        try {
            $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([
                'estado' => $estado,
                'id_pedido' => $idPedido
            ]);

        } catch (Exception $e) {
            error_log("Error al actualizar estado del pedido: " . $e->getMessage());
            return false;
        }
    }
}
