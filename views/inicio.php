<?php 
// home.php

// Variables para el layout
$titulo      = $titulo      ?? 'Artesano Digital – Panamá Oeste';
$descripcion = $descripcion ?? 'Plataforma de comercio electrónico para artesanos de Panamá Oeste';

// Iniciar captura de contenido
ob_start(); 
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-contenido">
    <h1 class="hero-titulo">Artesano Digital</h1>
    <h2 class="hero-subtitulo">Panamá Oeste</h2>
    <p class="hero-descripcion">
      Descubre las mejores artesanías locales creadas por talentosos artesanos de Panamá Oeste.
      Productos únicos con historia y tradición.
    </p>
    <div class="hero-acciones">
      <a href="/artesanoDigital/productos" class="btn btn-primario">Explorar Productos</a>
      <a href="/artesanoDigital/registro"  class="btn btn-secundario">Únete como Artesano</a>
    </div>
  </div>
</section>

<!-- Categorías Destacadas -->
<section class="categorias-destacadas seccion">
  <div class="contenedor">
    <h2 class="seccion-titulo">Categorías Populares</h2>
    <div class="categorias-grid-horizontal">
      <div class="categoria-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Textiles" class="categoria-imagen">
        <h3>Textiles</h3>
        <p>Molas, huipiles y más</p>
      </div>
      <div class="categoria-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Cerámica" class="categoria-imagen">
        <h3>Cerámica</h3>
        <p>Vasijas y decoraciones</p>
      </div>
      <div class="categoria-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Joyería" class="categoria-imagen">
        <h3>Joyería</h3>
        <p>Accesorios únicos</p>
      </div>
      <div class="categoria-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Madera" class="categoria-imagen">
        <h3>Madera</h3>
        <p>Tallados y muebles</p>
      </div>
    </div>
  </div>
</section>

<!-- Productos Destacados -->
<section class="productos-destacados seccion">
  <div class="contenedor">
    <h2 class="seccion-titulo">Productos Destacados</h2>
    <div class="productos-grid-horizontal">
      <div class="producto-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Mola Tradicional" class="producto-imagen">
        <div class="producto-info">
          <h3>Mola Tradicional</h3>
          <p class="producto-precio">$45.00</p>
          <p class="producto-artesano">Por María González</p>
          <button class="btn btn-primario" onclick="agregarAlCarrito('mola-tradicional')">
            Agregar al Carrito
          </button>
        </div>
      </div>
      <div class="producto-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Vasija de Cerámica" class="producto-imagen">
        <div class="producto-info">
          <h3>Vasija de Cerámica</h3>
          <p class="producto-precio">$35.00</p>
          <p class="producto-artesano">Por Carlos Pérez</p>
          <button class="btn btn-primario" onclick="agregarAlCarrito('vasija-ceramica')">
            Agregar al Carrito
          </button>
        </div>
      </div>
      <div class="producto-tarjeta">
        <img src="/artesanoDigital/public/placeholder.jpg" alt="Collar de Semillas" class="producto-imagen">
        <div class="producto-info">
          <h3>Collar de Semillas</h3>
          <p class="producto-precio">$25.00</p>
          <p class="producto-artesano">Por Ana López</p>
          <button class="btn btn-primario" onclick="agregarAlCarrito('collar-semillas')">
            Agregar al Carrito
          </button>
        </div>
      </div>
    </div>
    <div class="seccion-accion">
      <a href="/artesanoDigital/productos" class="btn btn-outline">Ver Todos los Productos</a>
    </div>
  </div>
</section>

<!-- Sobre Nosotros -->
<section class="sobre-nosotros seccion">
  <div class="contenedor sobre-contenido">
    <div class="sobre-texto">
      <h2>Conectando Tradición con Tecnología</h2>
      <p>
        Artesano Digital es más que una plataforma de ventas. Somos un puente entre 
        la rica tradición artesanal de Panamá Oeste y el mundo digital moderno.
      </p>
      <p>
        Cada producto cuenta una historia, cada artesano preserva técnicas ancestrales, 
        y cada compra apoya el desarrollo económico local.
      </p>
      <ul class="beneficios-lista">
        <li>✓ Productos auténticos y únicos</li>
        <li>✓ Apoyo directo a artesanos locales</li>
        <li>✓ Preservación de tradiciones culturales</li>
        <li>✓ Comercio justo y transparente</li>
      </ul>
    </div>
    <div class="sobre-imagen">
      <img src="/artesanoDigital/public/placeholder.jpg" alt="Artesano trabajando" class="imagen-redonda">
    </div>
  </div>
</section>

<?php 
// Capturar el contenido y llamar al layout base
$contenido = ob_get_clean(); 
include __DIR__ . '/layouts/base.php'; 
?>

<!-- Estilos específicos de esta página -->
<style>
  /* Reset muy ligero */
  body { margin:0; background:#faf8f5; color:#333; font-family:'Inter', sans-serif; }
  .contenedor { max-width:1280px; margin:0 auto; padding:0 1rem; }
  .seccion { padding:3rem 0; }
  .seccion-titulo { text-align:center; font-size:2rem; margin-bottom:2rem; }

  /* Hero full-width */
  .hero {
    width:100%;
    background: linear-gradient(135deg, #357ab8, #4a90e2);
    color: #fff;
    text-align: center;
    padding: 5rem 1rem;
  }
  .hero-contenido { max-width:800px; margin:0 auto; }
  .hero-titulo    { font-size:3rem; margin-bottom:.5rem; }
  .hero-subtitulo { font-size:2rem; margin-bottom:1rem; opacity:.9; }
  .hero-descripcion {
    font-size:1.1rem; line-height:1.5; margin-bottom:2rem;
  }
  .hero-acciones { 
    display:flex;
    gap:1rem;
    justify-content:center;
    flex-wrap:wrap;
  }

  /* Botones */
  .btn {
    display:inline-block;
    padding:.75rem 1.5rem;
    border-radius:8px;
    font-weight:500;
    text-decoration:none;
    text-align:center;
  }
  .btn-primario {
    background:#fff;
    color:#357ab8;
  }
  .btn-primario:hover {
    background:rgba(255,255,255,.8);
  }
  .btn-secundario {
    background:transparent;
    border:2px solid #fff;
    color:#fff;
  }
  .btn-secundario:hover {
    background:rgba(255,255,255,.2);
  }
  .btn-outline {
    border:2px solid #357ab8;
    color:#357ab8;
    background:transparent;
  }
  .btn-outline:hover {
    background:#357ab8;
    color:#fff;
  }

  /* Scroll horizontal */
  .categorias-grid-horizontal,
  .productos-grid-horizontal {
    display:flex;
    gap:1.5rem;
    overflow-x:auto;
    padding-bottom:1rem;
    scrollbar-width:thin;
    scrollbar-color:#ccc transparent;
  }
  .categorias-grid-horizontal::-webkit-scrollbar,
  .productos-grid-horizontal::-webkit-scrollbar {
    height:8px;
  }
  .categorias-grid-horizontal::-webkit-scrollbar-thumb,
  .productos-grid-horizontal::-webkit-scrollbar-thumb {
    background:#ccc;
    border-radius:4px;
  }
  .categoria-tarjeta,
  .producto-tarjeta {
    flex:0 0 auto;
    min-width:260px;
    max-width:320px;
    background:#fff;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
  }
  .categoria-imagen,
  .producto-imagen {
    width:100%;
    height:200px;
    object-fit:cover;
  }
  .producto-info {
    padding:1rem;
  }
  .producto-info h3    { margin:.5rem 1rem; }
  .producto-precio     { color:#357ab8; font-weight:600; margin:.5rem 1rem; }
  .producto-artesano   { margin:0 1rem 1rem; color:#666; }

  .seccion-accion { text-align:center; margin-top:2rem; }

  /* Sobre Nosotros */
  .sobre-contenido {
    display:flex;
    flex-wrap:wrap;
    gap:2rem;
    align-items:center;
  }
  .sobre-texto { flex:1 1 300px; }
  .beneficios-lista {
    list-style:none;
    padding-left:1rem;
  }
  .beneficios-lista li {
    position:relative;
    margin-bottom:.5rem;
    padding-left:1.5rem;
  }
  .beneficios-lista li::before {
    content:'✓';
    position:absolute;
    left:0;
    color:#357ab8;
  }
  .sobre-imagen img {
    width:100%;
    max-width:400px;
    border-radius:8px;
  }

  /* Responsive */
  @media (min-width:900px) {
    .categorias-grid-horizontal,
    .productos-grid-horizontal {
      justify-content:center;
      overflow-x:visible;
    }
  }
  @media (max-width:768px) {
    .sobre-contenido { flex-direction:column; }
  }
</style>

<!-- Script específico -->
<script>
  function agregarAlCarrito(productoId) {
    // Si es string (botones con ID producto-mola-tradicional), usar ID temporal
    let idProducto = productoId;
    let cantidad = 1;

    // Datos de demostración para los productos destacados
    const productosDemo = {
      'mola-tradicional': {
        id: 1,
        nombre: 'Mola Tradicional',
        precio: 45.00,
        imagen: '/artesanoDigital/public/placeholder.jpg',
        artesano: 'María González'
      },
      'vasija-ceramica': {
        id: 2,
        nombre: 'Vasija de Cerámica',
        precio: 35.00,
        imagen: '/artesanoDigital/public/placeholder.jpg',
        artesano: 'Carlos Pérez'
      },
      'collar-semillas': {
        id: 3,
        nombre: 'Collar de Semillas',
        precio: 25.00,
        imagen: '/artesanoDigital/public/placeholder.jpg',
        artesano: 'Ana López'
      }
    };

    // Obtener datos del producto
    const producto = productosDemo[productoId];
    
    // Simulamos la llamada AJAX pero trabajamos con localStorage para demostración
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Verificar si el producto ya está en el carrito
    const productoExistente = carrito.find(item => item.id === producto.id);
    
    if (productoExistente) {
      // Incrementar cantidad
      productoExistente.cantidad += cantidad;
    } else {
      // Agregar nuevo producto
      carrito.push({
        id: producto.id,
        nombre: producto.nombre,
        precio: producto.precio,
        imagen: producto.imagen,
        artesano: producto.artesano,
        cantidad: cantidad
      });
    }
    
    // Guardar en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Actualizar contador del carrito
    actualizarContadorCarrito(carrito.reduce((total, item) => total + item.cantidad, 0));
    
    // Mostrar mensaje de éxito
    mostrarMensaje(`${producto.nombre} agregado al carrito`, 'success');
    
    // Actualizar la vista del mini carrito
    actualizarMiniCarrito();
  }
  
  // Función para mostrar mensajes
  function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
      <div class="toast-contenido">
        <span class="toast-mensaje">${mensaje}</span>
        <button class="toast-cerrar">&times;</button>
      </div>
    `;
    
    // Agregar al contenedor de toasts
    const toastContainer = document.querySelector('.toast-container') || (() => {
      const container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
      return container;
    })();
    
    toastContainer.appendChild(toast);
    
    // Mostrar con animación
    setTimeout(() => toast.classList.add('toast-mostrar'), 10);
    
    // Auto cerrar después de 3 segundos
    setTimeout(() => {
      toast.classList.remove('toast-mostrar');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
    
    // Evento para cerrar manualmente
    toast.querySelector('.toast-cerrar').addEventListener('click', () => {
      toast.classList.remove('toast-mostrar');
      setTimeout(() => toast.remove(), 300);
    });
  }
</script>
