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
                <div class="resumen-value">$<?= number_format($estadisticas['ingresos_totales'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="resumen-card resumen-yellow">
            <div class="resumen-icon" style="background:#fff;"><span class="material-icons">star</span></div>
            <div>
                <div class="resumen-label">Valoración</div>
                <div class="resumen-value"><?= $estadisticas['valoracion_promedio'] ?? '0.0' ?></div>
            </div>
        </div>
    </div>
    <!-- Botones de acción -->
    <div class="dashboard-actions-bar">
        <a href="/artesanoDigital/artesano/productos/nuevo" class="dashboard-btn dashboard-btn-blue"><i class="fas fa-plus-circle"></i> Nuevo Producto</a>
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
                                    <a href="/artesanoDigital/artesano/pedidos/<?= $pedido['id_pedido'] ?>" class="btn btn-sm">Ver</a>
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
@media (max-width: 900px) {
    .resumen-cards-horizontal { flex-direction: column; gap: 1.2rem; }
    .dashboard-actions-bar { flex-direction: column; gap: 0.7rem; }
}
</style>
