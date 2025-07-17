<?php 
// Variables para el layout
$titulo = $titulo ?? 'Dashboard de Artesano - Artesano Digital';
$descripcion = $descripcion ?? 'Panel de administración para artesanos';

// Iniciar captura de contenido
ob_start(); 
?>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div class="dashboard-container dashboard-bg-white">
    <div class="dashboard-header">
        <h1>Panel de Artesano</h1>
        <p>Bienvenido, <?= htmlspecialchars($usuario['nombre'] ?? 'Artesano') ?></p>
    </div>
    <!-- Cards de Resumen -->
    <div class="resumen-cards-horizontal">
        <div class="resumen-card resumen-blue">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">inventory_2</span></div>
            <div>
                <div class="resumen-label">Productos Activos</div>
                <div class="resumen-value"><?= $estadisticas['productos_activos'] ?? 0 ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-green">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">shopping_cart</span></div>
            <div>
                <div class="resumen-label">Ventas</div>
                <div class="resumen-value"><?= $estadisticas['ventas_totales'] ?? 0 ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-teal">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">attach_money</span></div>
            <div>
                <div class="resumen-label">Ingresos Totales</div>
                <div class="resumen-value">B/. <?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-yellow">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">local_shipping</span></div>
            <div>
                <div class="resumen-label">Pedidos Pendientes</div>
                <div class="resumen-value"><?= $estadisticas['pedidos_pendientes'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <!-- Botones de acción -->
    <div class="dashboard-actions-bar">
        <button id="btnNuevoProducto" class="dashboard-btn dashboard-btn-blue"><i class="fas fa-plus-circle"></i> Nuevo Producto</button>
        <a href="/artesanoDigital/artesano/tienda" class="dashboard-btn dashboard-btn-green"><i class="fas fa-store"></i> Mi Tienda</a>
        <a href="/artesanoDigital/artesano/ventas" class="dashboard-btn dashboard-btn-indigo"><i class="fas fa-chart-line"></i> Análisis de Ventas</a>
    </div>
    <!-- Pedidos Recientes -->
    <div class="dashboard-main">
        <div class="card">
            <div class="card-header">
                <h3>Pedidos Recientes</h3>
            </div>
            <div class="card-body">
                <?php if (empty($pedidos_recientes ?? [])): ?>
                <p class="empty-state">No hay pedidos recientes</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table pedidos-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos_recientes as $pedido): ?>
                            <tr>
                                <td>#<?= $pedido['id_pedido'] ?></td>
                                <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                                <td>$<?= number_format($pedido['total'], 2) ?></td>
                                <td>
                                    <span class="badge status-<?= $pedido['estado'] ?>"><?= $pedido['estado'] ?></span>
                                </td>
                                <td>
                                    <a href="/artesanoDigital/artesano/pedidos/<?= $pedido['id_pedido'] ?>" class="btn btn-sm">Ver</a>code($pedido) ?>'>Ver detalles</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Productos -->
    <div class="dashboard-main">
        <div class="card">
            <div class="card-header">
                <h3>Mis Productos</h3>
            </div>
            <div class="card-body">
                <?php if (empty($productos ?? [])): ?>
                <p class="empty-state">No tienes productos. ¡Agrega tu primer producto!</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table productos-table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): 
                                // Check if we have a decorated product with discount
                                $tieneDescuento = false;
                                $precioOriginal = $producto['precio'];
                                $precioFinal = $precioOriginal;
                                $textoDescuento = '';
                                
                                if (isset($producto['descuento_porcentaje']) && $producto['descuento_porcentaje'] > 0) {
                                    $tieneDescuento = true;
                                    $precioFinal = $precioOriginal * (1 - $producto['descuento_porcentaje']/100);
                                    $textoDescuento = $producto['descuento_porcentaje'] . '% OFF';
                                } elseif (isset($producto['descuento_monto']) && $producto['descuento_monto'] > 0) {
                                    $tieneDescuento = true;
                                    $precioFinal = max(0, $precioOriginal - $producto['descuento_monto']);
                                    $textoDescuento = 'B/. ' . number_format($producto['descuento_monto'], 2) . ' OFF';
                                }
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= !empty($producto['imagen']) ? '/artesanoDigital/uploads/' . $producto['imagen'] : '/artesanoDigital/public/placeholder.jpg' ?>" 
                                         class="producto-imagen-miniatura" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                                </td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td>
                                    <?php if ($tieneDescuento): ?>
                                        <span class="precio-original">B/. <?= number_format($precioOriginal, 2) ?></span>
                                        <span class="precio-descuento">B/. <?= number_format($precioFinal, 2) ?></span>
                                        <span class="badge-descuento"><?= $textoDescuento ?></span>
                                    <?php else: ?>
                                        <span>B/. <?= number_format($producto['precio'], 2) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $producto['stock'] ?>
                                    <?php if ($producto['stock'] < 5): ?>
                                        <span class="badge-warning">¡Bajo!</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge status-<?= $producto['activo'] ? 'active' : 'inactive' ?>">
                                        <?= $producto['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/artesanoDigital/artesano/productos/editar/<?= $producto['id_producto'] ?>" class="btn btn-sm btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/artesanoDigital/artesano/productos/eliminar/<?= $producto['id_producto'] ?>" 
                                           class="btn btn-sm btn-delete" 
                                           onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nuevo producto -->
<div id="modalNuevoProducto" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Nuevo Producto</h2>
        
        <form id="formNuevoProducto" method="post" action="/artesanoDigital/artesano/productos/crear" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre del producto *</label>
                <input type="text" id="nombre" name="nombre" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-textarea" rows="3"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="precio">Precio *</label>
                    <div class="input-group">
                        <span class="input-group-text">B/.</span>
                        <input type="number" id="precio" name="precio" class="form-input" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-group col-6">
                    <label for="stock">Cantidad en stock *</label>
                    <input type="number" id="stock" name="stock" class="form-input" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen del producto</label>
                <input type="file" id="imagen" name="imagen" class="form-input" accept="image/*">
                <small class="form-help">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
            </div>
            
            <div class="form-group">
                <label>¿Aplicar descuento?</label>
                <div class="checkbox-inline">
                    <input type="checkbox" id="aplicarDescuento" name="aplicar_descuento">
                    <label for="aplicarDescuento">Sí, aplicar descuento</label>
                </div>
            </div>
            
            <div id="seccionDescuento" style="display:none;">
                <div class="form-group">
                    <label>Tipo de descuento</label>
                    <div class="radio-inline">
                        <input type="radio" id="descuentoPorcentaje" name="tipo_descuento" value="porcentaje" checked>
                        <label for="descuentoPorcentaje">Porcentaje (%)</label>
                    </div>
                    <div class="radio-inline">
                        <input type="radio" id="descuentoMonto" name="tipo_descuento" value="monto">
                        <label for="descuentoMonto">Monto fijo (B/.)</label>
                    </div>
                </div>
                
                <div class="form-row" id="camposPorcentaje">
                    <div class="form-group col-12">
                        <label for="descuento_porcentaje">Porcentaje de descuento (%)</label>
                        <input type="number" id="descuento_porcentaje" name="descuento_porcentaje" class="form-input" 
                               min="1" max="99" step="0.1" placeholder="Ej: 15.5">
                        <small class="form-help">Introduce un valor entre 1 y 99</small>
                    </div>
                </div>
                
                <div class="form-row" id="camposMonto" style="display:none;">
                    <div class="form-group col-12">
                        <label for="descuento_monto">Monto de descuento (B/.)</label>
                        <input type="number" id="descuento_monto" name="descuento_monto" class="form-input" 
                               min="0.01" step="0.01" placeholder="Ej: 5.99">
                        <small class="form-help">El monto no puede ser mayor al precio del producto</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="razon_descuento">Razón del descuento</label>
                    <input type="text" id="razon_descuento" name="razon_descuento" class="form-input" 
                           placeholder="Ej: Oferta especial, Liquidación, etc.">
                </div>
                
                <div class="form-group">
                    <label for="fecha_fin_descuento">Fecha fin del descuento (opcional)</label>
                    <input type="date" id="fecha_fin_descuento" name="fecha_fin_descuento" class="form-input">
                    <small class="form-help">Dejar en blanco para un descuento sin fecha de expiración</small>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-inline">
                    <input type="checkbox" id="activo" name="activo" checked>
                    <label for="activo">Producto activo (visible para compradores)</label>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary cerrar-modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para detalles de pedido -->
<div id="modalDetallePedido" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Detalles del Pedido <span id="pedidoId"></span></h2>
        
        <div class="detalle-pedido-info">
            <div class="detalle-seccion">
                <h3>Información del Pedido</h3>
                <div id="infoPedido" class="detalle-grid">
                    <!-- Esta información se llena dinámicamente -->
                </div>
            </div>
            
            <div class="detalle-seccion">
                <h3>Información del Cliente</h3>
                <div id="infoCliente" class="detalle-grid">
                    <!-- Esta información se llena dinámicamente -->
                </div>
            </div>
        </div>
        
        <div class="detalle-seccion">
            <h3>Productos</h3>
            <div class="table-responsive">
                <table class="table" id="tablaProductosPedido">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Esta información se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
            
            <div class="resumen-pedido">
                <div class="resumen-item">
                    <span>Subtotal:</span>
                    <span id="pedidoSubtotal">B/. 0.00</span>
                </div>
                <div class="resumen-item">
                    <span>Envío:</span>
                    <span id="pedidoEnvio">B/. 0.00</span>
                </div>
                <div class="resumen-item total">
                    <span>Total:</span>
                    <span id="pedidoTotal">B/. 0.00</span>
                </div>
            </div>
        </div>
        
        <div class="form-buttons">
            <button type="button" class="btn btn-secondary cerrar-modal">Cerrar</button>
            <button type="button" id="btnActualizarEstado" class="btn btn-primary">Actualizar Estado</button>
        </div>
    </div>
</div>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>

<style>
.dashboard-bg-white {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 16px 0 rgba(0,0,0,0.07);
    padding: 2.5rem 2rem 2.5rem 2rem;
    max-width: 1400px;
    margin: 2rem auto;
}
.resumen-cards-horizontal {
    display: flex;
    gap: 2rem;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
}
.resumen-card {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    background: #f8fafc;
    border-radius: 1rem;
    box-shadow: 0 1px 6px 0 rgba(0,0,0,0.04);
    padding: 1.5rem 2.2rem;
    min-width: 220px;
    flex: 1 1 220px;
}
.resumen-icon {
    font-size: 2.2rem;
    background: #fff;
    border-radius: 50%;
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px 0 rgba(0,0,0,0.07);
}
.resumen-label {
    font-size: 1rem;
    color: #64748b;
    margin-bottom: 0.2rem;
}
.resumen-value {
    font-size: 2rem;
    font-weight: 700;
    color: #22223b;
}
.resumen-blue .resumen-icon { color: #2563eb; }
.resumen-green .resumen-icon { color: #16a34a; }
.resumen-teal .resumen-icon { color: #14b8a6; }
.resumen-yellow .resumen-icon { color: #eab308; }
.dashboard-actions-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}
.dashboard-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    border-radius: 0.7rem;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 4px 0 rgba(0,0,0,0.04);
}
.dashboard-btn-blue { background: #2563eb; color: #fff; }
.dashboard-btn-blue:hover { background: #1d4ed8; }
.dashboard-btn-green { background: #16a34a; color: #fff; }
.dashboard-btn-green:hover { background: #15803d; }
.dashboard-btn-indigo { background: #6366f1; color: #fff; }
.dashboard-btn-indigo:hover { background: #4f46e5; }
.pedidos-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 1rem;
    overflow: hidden;
}
.pedidos-table th, .pedidos-table td {
    padding: 1rem 0.7rem;
    text-align: left;
}
.pedidos-table th {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.98rem;
    font-weight: 700;
    border-bottom: 2px solid #e5e7eb;
}
.pedidos-table tr {
    border-bottom: 1px solid #e5e7eb;
}
.pedidos-table tr:last-child {
    border-bottom: none;
}
.pedidos-table td {
    font-size: 1rem;
    color: #22223b;
}
.badge {
    display: inline-block;
    padding: 0.35em 0.8em;
    border-radius: 0.7em;
    font-size: 0.95em;
    font-weight: 600;
}
.status-nuevo { background: #e3f2fd; color: #2563eb; }
.status-confirmado { background: #e8f5e9; color: #16a34a; }
.status-enviado { background: #fff3e0; color: #eab308; }
.status-entregado { background: #e8f5e9; color: #16a34a; }
.status-cancelado { background: #ffebee; color: #b91c1c; }
/* Estilos para el modal popup */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: #fff;
    margin: 3% auto;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 700px;
    position: relative;
    animation: slideDown 0.4s ease;
    max-height: 90vh;
    overflow-y: auto;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal .close {
    position: absolute;
    right: 1.5rem;
    top: 1rem;
    color: #64748b;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.2s;
}

.modal .close:hover {
    color: #0f172a;
}

.modal h2 {
    color: #334155;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.625rem;
}

.form-row > .form-group {
    padding: 0 0.625rem;
    margin-bottom: 1.25rem;
}

.col-6 {
    flex: 0 0 50%;
    max-width: 50%;
}

.col-12 {
    flex: 0 0 100%;
    max-width: 100%;
}

.form-input, .form-textarea, .form-select {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #0f172a;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    transition: border-color 0.15s ease-in-out;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
    border-color: #3b82f6;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.input-group {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 100%;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: #475569;
    text-align: center;
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem 0 0 0.5rem;
    border-right: none;
}

.input-group .form-input {
    border-radius: 0 0.5rem 0.5rem 0;
    position: relative;
    flex: 1 1 auto;
    width: 1%;
}

.form-help {
    display: block;
    margin-top: 0.5rem;
    color: #64748b;
    font-size: 0.875rem;
}

.checkbox-inline, .radio-inline {
    display: flex;
    align-items: center;
    margin-right: 1.5rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
}

.checkbox-inline input[type="checkbox"],
.radio-inline input[type="radio"] {
    margin-right: 0.5rem;
}

.form-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.5rem;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}

.btn-primary {
    color: #fff;
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.btn-primary:hover {
    background-color: #2563eb;
    border-color: #2563eb;
}

.btn-secondary {
    color: #475569;
    background-color: #f1f5f9;
    border-color: #e2e8f0;
}

.btn-secondary:hover {
    background-color: #e2e8f0;
    border-color: #cbd5e1;
}

.precio-original {
    text-decoration: line-through;
    color: #94a3b8;
    margin-right: 0.5rem;
}

.precio-descuento {
    font-weight: 700;
    color: #ef4444;
}

.badge-descuento {
    display: inline-block;
    padding: 0.25em 0.75em;
    background-color: #fee2e2;
    color: #ef4444;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    margin-left: 0.5rem;
    text-transform: uppercase;
}

/* Estilos para modal de detalles de pedido */
.detalle-pedido-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.detalle-seccion {
    margin-bottom: 1.5rem;
}

.detalle-seccion h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.detalle-grid {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.5rem 1rem;
}

.detalle-grid .etiqueta {
    font-weight: 500;
    color: #64748b;
}

.detalle-grid .valor {
    color: #1e293b;
}

.resumen-pedido {
    margin-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.resumen-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.resumen-item.total {
    font-weight: 700;
    font-size: 1.1rem;
    color: #1e293b;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        padding: 1.5rem;
        margin: 5% auto;
    }
    
    .form-row > .form-group {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .col-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .form-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .detalle-pedido-info {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias al modal y sus elementos
    const modal = document.getElementById('modalNuevoProducto');
    const btnNuevoProducto = document.getElementById('btnNuevoProducto');
    const cerrarModalBtns = document.querySelectorAll('.close, .cerrar-modal');
    
    // Checkbox y sección de descuentos
    const aplicarDescuentoCheck = document.getElementById('aplicarDescuento');
    const seccionDescuento = document.getElementById('seccionDescuento');
    
    // Radio buttons para tipo de descuento
    const descuentoPorcentajeRadio = document.getElementById('descuentoPorcentaje');
    const descuentoMontoRadio = document.getElementById('descuentoMonto');
    
    // Campos de descuento
    const camposPorcentaje = document.getElementById('camposPorcentaje');
    const camposMonto = document.getElementById('camposMonto');
    
    // Abrir el modal al hacer clic en el botón "Nuevo Producto"
    btnNuevoProducto.addEventListener('click', function() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Evitar scroll en el fondo
    });
    
    // Cerrar el modal al hacer clic en la X o el botón Cancelar
    cerrarModalBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restaurar scroll
        });
    });
    
    // Cerrar el modal al hacer clic fuera de él
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // Mostrar/ocultar sección de descuentos
    if (aplicarDescuentoCheck) {
        aplicarDescuentoCheck.addEventListener('change', function() {
            seccionDescuento.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Cambiar entre tipos de descuento
    if (descuentoPorcentajeRadio && descuentoMontoRadio) {
        descuentoPorcentajeRadio.addEventListener('change', function() {
            if (this.checked) {
                camposPorcentaje.style.display = 'block';
                camposMonto.style.display = 'none';
            }
        });
        
        descuentoMontoRadio.addEventListener('change', function() {
            if (this.checked) {
                camposPorcentaje.style.display = 'none';
                camposMonto.style.display = 'block';
            }
        });
    }
    
    // Validar formulario antes de enviar
    const formNuevoProducto = document.getElementById('formNuevoProducto');
    if (formNuevoProducto) {
        formNuevoProducto.addEventListener('submit', function(e) {
            const precio = parseFloat(document.getElementById('precio').value);
            const stock = parseInt(document.getElementById('stock').value);
            
            if (isNaN(precio) || precio <= 0) {
                e.preventDefault();
                alert('Por favor, introduce un precio válido mayor que 0');
                return false;
            }
            
            if (isNaN(stock) || stock < 0) {
                e.preventDefault();
                alert('Por favor, introduce una cantidad en stock válida (0 o más)');
                return false;
            }
            
            if (aplicarDescuentoCheck && aplicarDescuentoCheck.checked) {
                if (descuentoPorcentajeRadio.checked) {
                    const porcentaje = parseFloat(document.getElementById('descuento_porcentaje').value);
                    if (isNaN(porcentaje) || porcentaje <= 0 || porcentaje >= 100) {
                        e.preventDefault();
                        alert('Por favor, introduce un porcentaje de descuento válido (entre 1 y 99)');
                        return false;
                    }
                } else {
                    const monto = parseFloat(document.getElementById('descuento_monto').value);
                    if (isNaN(monto) || monto <= 0 || monto >= precio) {
                        e.preventDefault();
                        alert('El monto de descuento debe ser mayor que 0 y menor que el precio del producto');
                        return false;
                    }
                }
            }
            
            // Si todo está bien, el formulario se enviará
            return true;
        });
    }
    
    // Código para el modal de detalles de pedido
    const modalPedido = document.getElementById('modalDetallePedido');
    const btnVerPedidos = document.querySelectorAll('.ver-pedido');
    const cerrarModalPedidoBtns = modalPedido?.querySelectorAll('.close, .cerrar-modal');
    const pedidoId = document.getElementById('pedidoId');
    const infoPedido = document.getElementById('infoPedido');
    const infoCliente = document.getElementById('infoCliente');
    const tablaProductosPedido = document.getElementById('tablaProductosPedido')?.querySelector('tbody');
    const pedidoSubtotal = document.getElementById('pedidoSubtotal');
    const pedidoEnvio = document.getElementById('pedidoEnvio');
    const pedidoTotal = document.getElementById('pedidoTotal');
    const btnActualizarEstado = document.getElementById('btnActualizarEstado');

    // Abrir modal de detalles de pedido
    btnVerPedidos.forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const id = btn.dataset.id;
            const pedidoInfo = JSON.parse(btn.dataset.info);
            
            // Mostrar ID del pedido
            pedidoId.textContent = `#${id}`;
            
            // Llenar información del pedido
            infoPedido.innerHTML = `
                <span class="etiqueta">Fecha:</span>
                <span class="valor">${formatearFecha(pedidoInfo.fecha_pedido)}</span>
                
                <span class="etiqueta">Estado:</span>
                <span class="valor">
                    <span class="badge status-${pedidoInfo.estado}">${pedidoInfo.estado}</span>
                </span>
                
                <span class="etiqueta">Método de Pago:</span>
                <span class="valor">${pedidoInfo.metodo_pago || 'No especificado'}</span>
                
                <span class="etiqueta">Fecha de Envío:</span>
                <span class="valor">${pedidoInfo.fecha_envio ? formatearFecha(pedidoInfo.fecha_envio) : 'Pendiente'}</span>
            `;
            
            // Llenar información del cliente
            infoCliente.innerHTML = `
                <span class="etiqueta">Nombre:</span>
                <span class="valor">${pedidoInfo.cliente_nombre}</span>
                
                <span class="etiqueta">Email:</span>
                <span class="valor">${pedidoInfo.cliente_email || 'No disponible'}</span>
                
                <span class="etiqueta">Teléfono:</span>
                <span class="valor">${pedidoInfo.cliente_telefono || 'No disponible'}</span>
                
                <span class="etiqueta">Dirección:</span>
                <span class="valor">${pedidoInfo.direccion_envio || 'No disponible'}</span>
            `;
            
            // Obtener los detalles del pedido (productos) mediante AJAX
            try {
                const response = await fetch(`/artesanoDigital/api/pedidos/${id}/detalles`);
                if (!response.ok) {
                    throw new Error('Error al obtener los detalles del pedido');
                }
                
                const detalles = await response.json();
                
                // Llenar tabla de productos
                tablaProductosPedido.innerHTML = '';
                
                if (detalles.items && detalles.items.length > 0) {
                    detalles.items.forEach(item => {
                        const subtotal = item.precio * item.cantidad;
                        tablaProductosPedido.innerHTML += `
                            <tr>
                                <td>${item.nombre}</td>
                                <td>${item.cantidad}</td>
                                <td>B/. ${formatearPrecio(item.precio)}</td>
                                <td>B/. ${formatearPrecio(subtotal)}</td>
                            </tr>
                        `;
                    });
                    
                    // Actualizar resumen
                    pedidoSubtotal.textContent = `B/. ${formatearPrecio(detalles.subtotal || 0)}`;
                    pedidoEnvio.textContent = `B/. ${formatearPrecio(detalles.costo_envio || 0)}`;
                    pedidoTotal.textContent = `B/. ${formatearPrecio(pedidoInfo.total || 0)}`;
                } else {
                    tablaProductosPedido.innerHTML = `
                        <tr>
                            <td colspan="4" class="empty-state">No hay detalles disponibles para este pedido</td>
                        </tr>
                    `;
                }
                
            } catch (error) {
                console.error('Error:', error);
                tablaProductosPedido.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">Error al cargar los detalles del pedido</td>
                    </tr>
                `;
            }
            
            // Configurar botón de actualizar estado
            btnActualizarEstado.dataset.id = id;
            btnActualizarEstado.dataset.estadoActual = pedidoInfo.estado;
            
            // Mostrar modal
            modalPedido.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    });

    // Cerrar modal de detalles de pedido
    if (cerrarModalPedidoBtns) {
        cerrarModalPedidoBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                modalPedido.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });
    }

    // Cerrar modal al hacer clic fuera de él
    window.addEventListener('click', function(event) {
        if (event.target === modalPedido) {
            modalPedido.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Botón de actualizar estado
    if (btnActualizarEstado) {
        btnActualizarEstado.addEventListener('click', async function() {
            const id = this.dataset.id;
            const estadoActual = this.dataset.estadoActual;
            
            // Aquí puedes implementar un diálogo o selector para elegir el nuevo estado
            const nuevoEstado = prompt(
                'Seleccione el nuevo estado del pedido:\n' +
                '- nuevo\n' +
                '- confirmado\n' +
                '- enviado\n' +
                '- entregado\n' +
                '- cancelado',
                estadoActual
            );
            
            if (!nuevoEstado || nuevoEstado === estadoActual) {
                return;
            }
            
            const estadosValidos = ['nuevo', 'confirmado', 'enviado', 'entregado', 'cancelado'];
            if (!estadosValidos.includes(nuevoEstado)) {
                alert('Estado no válido');
                return;
            }
            
            try {
                const response = await fetch(`/artesanoDigital/api/pedidos/${id}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ estado: nuevoEstado }),
                });
                
                if (!response.ok) {
                    throw new Error('Error al actualizar el estado del pedido');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Actualizar estado en la interfaz
                    const btnPedido = document.querySelector(`button.ver-pedido[data-id="${id}"]`);
                    if (btnPedido) {
                        const badge = btnPedido.closest('tr').querySelector('.badge');
                        if (badge) {
                            badge.className = `badge status-${nuevoEstado}`;
                            badge.textContent = nuevoEstado;
                        }
                    }
                    // Actualizar el dataset del botón y la información del modal
                    this.dataset.estadoActual = nuevoEstado;
                    const badgeModal = document.querySelector('#infoPedido .badge');
                    if (badgeModal) {
                        badgeModal.className = `badge status-${nuevoEstado}`;
                        badgeModal.textContent = nuevoEstado;
                    }
                    alert('Estado actualizado correctamente');
                } else {
                    throw new Error(result.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar el estado: ' + error.message);
            }
        });
    }

    // Funciones auxiliares
    function formatearFecha(fechaString) {
        const fecha = new Date(fechaString);
        return fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function formatearPrecio(numero) {
        return Number(numero).toFixed(2);
    }
});
</script>
