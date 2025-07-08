<?php 
/**
 * Vista Dashboard del Cliente
 * Responsabilidad: Mostrar panel principal del cliente con pedidos y navegación
 */

// Variables para el layout
$titulo = $titulo ?? 'Panel de Cliente - Artesano Digital';
$descripcion = 'Panel de control para clientes de Artesano Digital';

// Iniciar captura de contenido
ob_start(); 
?>

<div class="contenedor">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
            <p>Desde aquí puedes gestionar tus pedidos y explorar productos</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Pedidos Realizados</h3>
                <p class="stat-number"><?= count($pedidos_recientes) ?></p>
            </div>
            <div class="stat-card">
                <h3>Estado de Pedidos</h3>
                <p class="stat-text">Ver detalles</p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="section">
                <h2>Pedidos Recientes</h2>
                <?php if (!empty($pedidos_recientes)): ?>
                    <div class="pedidos-lista">
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="pedido-card">
                                <div class="pedido-info">
                                    <span class="pedido-id">Pedido #<?= $pedido['id'] ?></span>
                                    <span class="pedido-fecha"><?= date('d/m/Y', strtotime($pedido['fecha'])) ?></span>
                                </div>
                                <div class="pedido-monto">$<?= number_format($pedido['total'], 2) ?></div>
                                <div class="pedido-estado estado-<?= $pedido['estado'] ?>">
                                    <?= ucfirst($pedido['estado']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="texto-vacio">No tienes pedidos realizados aún.</p>
                    <a href="/artesanoDigital/productos" class="btn btn-primary">Explorar Productos</a>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>Acciones Rápidas</h2>
                <div class="acciones-grid">
                    <a href="/artesanoDigital/productos" class="accion-card">
                        <h3>Explorar Productos</h3>
                        <p>Descubre nuevos productos artesanales</p>
                    </a>
                    <a href="/artesanoDigital/carrito" class="accion-card">
                        <h3>Ver Carrito</h3>
                        <p>Revisa los productos en tu carrito</p>
                    </a>
                    <a href="/artesanoDigital/artesanos" class="accion-card">
                        <h3>Conocer Artesanos</h3>
                        <p>Conoce a los creadores de estos productos</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: var(--espaciado-lg);
}

.dashboard-header {
    text-align: center;
    margin-bottom: var(--espaciado-xl);
}

.dashboard-header h1 {
    color: var(--color-texto-principal);
    margin-bottom: var(--espaciado-sm);
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--espaciado-lg);
    margin-bottom: var(--espaciado-xl);
}

.stat-card {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-lg);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra-md);
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: var(--peso-negrita);
    color: var(--color-tostado);
    margin: 0;
}

.section {
    margin-bottom: var(--espaciado-xl);
}

.section h2 {
    color: var(--color-texto-principal);
    margin-bottom: var(--espaciado-lg);
}

.pedidos-lista {
    display: flex;
    flex-direction: column;
    gap: var(--espaciado-md);
}

.pedido-card {
    background: var(--color-blanco-suave);
    padding: var(--espaciado-lg);
    border-radius: var(--radio-md);
    box-shadow: var(--sombra-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pedido-estado {
    padding: var(--espaciado-xs) var(--espaciado-sm);
    border-radius: var(--radio-sm);
    font-size: 0.875rem;
    font-weight: var(--peso-medio);
}

.estado-pendiente { background: var(--color-advertencia); color: white; }
.estado-enviado { background: var(--color-info); color: white; }
.estado-entregado { background: var(--color-exito); color: white; }

.acciones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--espaciado-lg);
}

.accion-card {
    background: var(--color-crema);
    padding: var(--espaciado-lg);
    border-radius: var(--radio-lg);
    text-decoration: none;
    color: var(--color-texto-principal);
    transition: var(--transicion-normal);
    border: 1px solid var(--color-beige);
}

.accion-card:hover {
    background: var(--color-beige);
    transform: translateY(-2px);
    box-shadow: var(--sombra-md);
}

.texto-vacio {
    text-align: center;
    color: var(--color-texto-secundario);
    margin-bottom: var(--espaciado-lg);
}
</style>

<?php 
// Capturar el contenido y incluir el layout
$contenido = ob_get_clean(); 
include __DIR__ . '/../layouts/base.php'; 
?>
