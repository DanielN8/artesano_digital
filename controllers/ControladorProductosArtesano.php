<?php
/**
 * Controlador de productos para artesanos
 * Responsabilidad: Gestionar operaciones CRUD para productos de artesanos
 */

namespace Controllers;

use Models\Producto;
use Patrones\FactoriaProducto;
use Patrones\ComponenteProducto;
use Utils\GestorUploads;
use Utils\GestorAutenticacion;
use Exception;

class ControladorProductosArtesano 
{
    private GestorAutenticacion $gestorAuth;
    private Producto $modeloProducto;
    private GestorUploads $gestorUploads;

    public function __construct() 
    {
        $this->gestorAuth = GestorAutenticacion::obtenerInstancia();
        $this->modeloProducto = new Producto();
        $this->gestorUploads = new GestorUploads();
        
        // Verificar que el usuario esté autenticado y sea artesano
        if (!$this->gestorAuth->estaAutenticado() || !$this->gestorAuth->esArtesano()) {
            header('Location: /artesanoDigital/auth/login?redirect=artesano/dashboard');
            exit;
        }
    }

    /**
     * Crea un nuevo producto con posibilidad de descuento
     */
    public function crear(array $datos, array $archivo = null): array 
    {
        try {
            // Validar datos básicos
            if (empty($datos['nombre']) || empty($datos['precio'])) {
                return [
                    'error' => true,
                    'mensaje' => 'Nombre y precio son obligatorios'
                ];
            }
            
            // Obtener ID de tienda del artesano
            $usuario = $this->gestorAuth->obtenerUsuarioActual();
            $idTienda = $this->modeloProducto->obtenerTiendaPorUsuario($usuario['id_usuario']);
            
            if (!$idTienda) {
                return [
                    'error' => true,
                    'mensaje' => 'No tienes una tienda registrada'
                ];
            }
            
            // Procesar imagen si existe
            $rutaImagen = '';
            if ($archivo && isset($archivo['imagen']) && !empty($archivo['imagen']['name'])) {
                $resultadoUpload = $this->gestorUploads->subirImagen($archivo['imagen'], 'productos');
                if (!$resultadoUpload['error']) {
                    $rutaImagen = $resultadoUpload['ruta'];
                }
            }
            
            // Preparar datos del producto
            $datosProducto = [
                'id_tienda' => $idTienda,
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? '',
                'precio' => floatval($datos['precio']),
                'imagen' => $rutaImagen,
                'stock' => intval($datos['stock'] ?? 0),
                'activo' => isset($datos['activo']) ? true : false
            ];
            
            // Agregar datos de descuento si existen
            if (isset($datos['tipo_descuento']) && !empty($datos['tipo_descuento'])) {
                if ($datos['tipo_descuento'] === 'porcentaje' && isset($datos['descuento_porcentaje'])) {
                    $datosProducto['descuento_porcentaje'] = floatval($datos['descuento_porcentaje']);
                    $datosProducto['razon_descuento'] = $datos['razon_descuento'] ?? 'Oferta especial';
                } elseif ($datos['tipo_descuento'] === 'monto' && isset($datos['descuento_monto'])) {
                    $datosProducto['descuento_monto'] = floatval($datos['descuento_monto']);
                    $datosProducto['razon_descuento'] = $datos['razon_descuento'] ?? 'Oferta especial';
                }
            }
            
            // Guardar producto en la base de datos
            $resultado = $this->modeloProducto->crear($datosProducto);
            
            if (!$resultado['error']) {
                // Guardar descuentos en tabla separada si se implementa
                if (isset($datosProducto['descuento_porcentaje']) || isset($datosProducto['descuento_monto'])) {
                    $tipoDescuento = isset($datosProducto['descuento_porcentaje']) ? 'porcentaje' : 'monto';
                    $valorDescuento = isset($datosProducto['descuento_porcentaje']) 
                                    ? $datosProducto['descuento_porcentaje']
                                    : $datosProducto['descuento_monto'];
                    
                    $this->modeloProducto->guardarDescuento(
                        $resultado['id_producto'],
                        $tipoDescuento,
                        $valorDescuento,
                        $datosProducto['razon_descuento']
                    );
                }
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            return [
                'error' => true,
                'mensaje' => 'Error al crear el producto: ' . $e->getMessage()
            ];
        }
    }
}
