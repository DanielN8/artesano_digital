<?php include __DIR__ . '/../layouts/base.php'; ?>

<main class="main-contenido">
    <!-- Header del catálogo -->
    <section class="catalogo-header">
        <div class="contenedor">
            <div class="catalogo-intro">
                <h1 class="catalogo-titulo">Catálogo de Productos</h1>
                <p class="catalogo-descripcion">
                    Descubre productos únicos hechos a mano por talentosos artesanos de Panamá Oeste
                </p>
            </div>
            
            <!-- Filtros de búsqueda -->
            <div class="filtros-contenedor">
                <div class="filtros-grid">
                    <div class="filtro-grupo">
                        <label for="busqueda">Buscar producto</label>
                        <input type="text" id="busqueda" placeholder="Buscar por nombre..." class="input-busqueda">
                    </div>
                    <div class="filtro-grupo">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" class="select-filtro">
                            <option value="">Todas las categorías</option>
                            <option value="textiles">Textiles</option>
                            <option value="ceramica">Cerámica</option>
                            <option value="joyeria">Joyería</option>
                            <option value="madera">Madera</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="precio">Precio máximo</label>
                        <select id="precio" class="select-filtro">
                            <option value="">Sin límite</option>
                            <option value="25">Hasta $25</option>
                            <option value="50">Hasta $50</option>
                            <option value="100">Hasta $100</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <button type="button" class="btn btn-primario" onclick="aplicarFiltros()">
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Grid de productos -->
    <section class="productos-seccion">
        <div class="contenedor">
            <div class="productos-resultados">
                <p class="resultados-info">
                    Mostrando <?= count($productos ?? []) ?> productos
                </p>
            </div>
            
            <div class="productos-grid" id="productosGrid">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="producto-tarjeta" data-categoria="<?= htmlspecialchars($producto['categoria'] ?? '') ?>">
                            <div class="producto-imagen-contenedor">
                                <img src="<?= $producto['imagen'] ? '/artesanoDigital/' . htmlspecialchars($producto['imagen']) : '/artesanoDigital/public/placeholder.jpg' ?>" 
                                     alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                                     class="producto-imagen">
                                <div class="producto-overlay">
                                    <a href="/artesanoDigital/producto/<?= $producto['id_producto'] ?>" class="btn btn-ver">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                            <div class="producto-info">
                                <h3 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h3>
                                <p class="producto-descripcion"><?= htmlspecialchars(substr($producto['descripcion'], 0, 100)) ?>...</p>
                                <div class="producto-detalles">
                                    <p class="producto-precio">$<?= number_format($producto['precio'], 2) ?></p>
                                    <p class="producto-artesano">Por <?= htmlspecialchars($producto['nombre_artesano']) ?></p>
                                    <p class="producto-tienda"><?= htmlspecialchars($producto['nombre_tienda']) ?></p>
                                </div>
                                <div class="producto-acciones">
                                    <button class="btn btn-primario" onclick="agregarAlCarrito(<?= $producto['id_producto'] ?>)">
                                        Agregar al Carrito
                                    </button>
                                    <a href="/artesanoDigital/producto/<?= $producto['id_producto'] ?>" class="btn btn-outline">
                                        Ver Más
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="productos-vacio">
                        <h3>No hay productos disponibles</h3>
                        <p>Pronto tendremos productos increíbles de nuestros artesanos.</p>
                        <a href="/artesanoDigital/" class="btn btn-primario">Volver al Inicio</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <div class="paginacion">
                <button class="btn btn-outline" onclick="cargarMasProductos()">
                    Cargar Más Productos
                </button>
            </div>
        </div>
    </section>
</main>

<script>
function aplicarFiltros() {
    // Implementar filtros
    console.log('Aplicando filtros...');
}

function agregarAlCarrito(idProducto) {
    // Implementar agregar al carrito
    console.log('Agregando producto al carrito:', idProducto);
    alert('Funcionalidad de carrito próximamente disponible');
}

function cargarMasProductos() {
    // Implementar carga de más productos
    console.log('Cargando más productos...');
}
</script>
