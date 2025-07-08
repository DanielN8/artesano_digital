/**
 * carrito.js - Gestión centralizada del carrito
 * Responsable de sincronizar localStorage, sesión PHP y UI
 */

// Carrito global
let carritoGlobal = [];

// Al cargar el documento
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrito desde localStorage
    initCarrito();
    
    // Si estamos en una página que no es checkout, sincronizar con servidor
    if (!window.location.pathname.includes('checkout')) {
        sincronizarCarritoConServidor();
    }
    
    // Event listener para añadir al carrito (delegación de eventos)
    document.body.addEventListener('click', function(e) {
        // Botones de "Añadir al carrito" en listas de productos
        const addToCartBtn = e.target.closest('.btn-add-to-cart, .add-to-cart');
        if (addToCartBtn) {
            e.preventDefault();
            
            const idProducto = parseInt(addToCartBtn.getAttribute('data-id'));
            const nombre = addToCartBtn.getAttribute('data-nombre');
            const precio = parseFloat(addToCartBtn.getAttribute('data-precio'));
            const imagen = addToCartBtn.getAttribute('data-imagen');
            const stock = parseInt(addToCartBtn.getAttribute('data-stock') || '1000');
            
            agregarAlCarrito(idProducto, nombre, precio, imagen, 1, stock);
        }
    });
});

/**
 * Inicializar carrito desde localStorage
 */
function initCarrito() {
    carritoGlobal = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Actualizar contador
    actualizarContadorCarrito(carritoGlobal.reduce((total, item) => total + item.cantidad, 0));
    
    // Actualizar minicarrito si existe
    if (typeof actualizarMiniCarrito === 'function') {
        actualizarMiniCarrito();
    }
}

/**
 * Sincronizar carrito de localStorage con el servidor
 */
function sincronizarCarritoConServidor() {
    const carritoLocal = localStorage.getItem('carrito');
    if (!carritoLocal) return; // Si no hay carrito local, no hay nada que sincronizar
    
    fetch('/artesanoDigital/controllers/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'accion=sincronizar_carrito&carrito_local=' + encodeURIComponent(carritoLocal)
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            console.log('Carrito sincronizado con el servidor');
            
            // Si el servidor devolvió un carrito actualizado, actualizamos el localStorage
            if (data.carrito) {
                // Convertir formato de servidor a formato localStorage
                const carritoLocalFormat = data.carrito.map(item => ({
                    id: parseInt(item.id_producto),
                    nombre: item.nombre,
                    precio: parseFloat(item.precio),
                    imagen: item.imagen ? '/artesanoDigital/uploads/' + item.imagen : '/artesanoDigital/public/placeholder.jpg',
                    cantidad: parseInt(item.cantidad),
                    stock: parseInt(item.stock)
                }));
                
                // Actualizar carrito global y localStorage
                carritoGlobal = carritoLocalFormat;
                localStorage.setItem('carrito', JSON.stringify(carritoLocalFormat));
                
                // Actualizar contador
                actualizarContadorCarrito(carritoLocalFormat.reduce((total, item) => total + item.cantidad, 0));
                
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
            }
        }
    })
    .catch(error => {
        console.error('Error al sincronizar carrito:', error);
    });
}

/**
 * Actualizar contador de productos en el carrito
 * @param {number} nuevoTotal - Total de productos
 */
function actualizarContadorCarrito(nuevoTotal) {
    const contador = document.getElementById('carrito-contador');
    if (contador) {
        contador.textContent = nuevoTotal;
        
        // Efecto visual para el contador
        contador.classList.add('animate-pulse');
        setTimeout(() => contador.classList.remove('animate-pulse'), 500);
    }
}

/**
 * Agregar producto al carrito
 * @param {number} idProducto - ID del producto
 * @param {string} nombre - Nombre del producto
 * @param {number} precio - Precio del producto
 * @param {string} imagen - URL de la imagen
 * @param {number} cantidad - Cantidad a agregar
 * @param {number} stock - Stock disponible
 */
function agregarAlCarrito(idProducto, nombre, precio, imagen, cantidad = 1, stock = 0) {
    // Validar datos
    idProducto = parseInt(idProducto);
    precio = parseFloat(precio);
    cantidad = parseInt(cantidad);
    stock = parseInt(stock);
    
    if (isNaN(idProducto) || isNaN(precio) || isNaN(cantidad) || !nombre) {
        mostrarMensaje('Datos de producto incorrectos', 'error');
        return;
    }
    
    // Verificar stock
    if (stock > 0 && cantidad > stock) {
        mostrarMensaje(`Solo hay ${stock} unidades disponibles`, 'warning');
        cantidad = stock;
    }
    
    // Agregar al carrito local
    let productoExistente = carritoGlobal.find(item => item.id === idProducto);
    
    if (productoExistente) {
        // Verificar que no exceda el stock
        if (stock > 0 && productoExistente.cantidad + cantidad > stock) {
            mostrarMensaje(`No se pueden agregar más unidades. Stock máximo: ${stock}`, 'warning');
            productoExistente.cantidad = stock;
        } else {
            productoExistente.cantidad += cantidad;
        }
    } else {
        carritoGlobal.push({
            id: idProducto,
            nombre: nombre,
            precio: precio,
            imagen: imagen || '/artesanoDigital/public/placeholder.jpg',
            cantidad: cantidad,
            stock: stock
        });
    }
    
    // Actualizar localStorage
    localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
    
    // Indicar que hay carrito en una cookie para detectarlo en carga inicial
    document.cookie = "has_cart=1; path=/; max-age=86400";
    
    // Sincronizar con el servidor
    fetch('/artesanoDigital/controllers/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `accion=agregar_producto&id_producto=${idProducto}&cantidad=${cantidad}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            // Actualizar contador con el valor del servidor
            actualizarContadorCarrito(data.total_productos);
            
            // Actualizar el minicarrito si existe la función
            if (typeof actualizarMiniCarrito === 'function') {
                actualizarMiniCarrito();
            }
            
            // Mensaje de éxito
            mostrarMensaje('Producto agregado al carrito', 'success');
        } else {
            mostrarMensaje(data.mensaje || 'Error al agregar al carrito', 'error');
        }
    })
    .catch(error => {
        console.error('Error al agregar producto:', error);
        mostrarMensaje('Error al agregar el producto', 'error');
    });
}

/**
 * Eliminar producto del carrito
 * @param {number} idProducto - ID del producto a eliminar
 */
function eliminarDelCarrito(idProducto) {
    idProducto = parseInt(idProducto);
    
    // Eliminar del carrito local
    carritoGlobal = carritoGlobal.filter(item => item.id !== idProducto);
    localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
    
    // Actualizar en el servidor
    fetch('/artesanoDigital/controllers/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'accion=eliminar_producto&id_producto=' + idProducto
    })
    .then(res => res.json())
    .then(data => {
        if (data.exitoso) {
            // Actualizar contador
            actualizarContadorCarrito(data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0));
            
            // Actualizar minicarrito si existe
            if (typeof actualizarMiniCarrito === 'function') {
                actualizarMiniCarrito();
            }
            
            // Mensaje
            mostrarMensaje('Producto eliminado del carrito', 'info');
        } else {
            mostrarMensaje('Error al eliminar producto', 'error');
        }
    })
    .catch(error => {
        console.error('Error al eliminar producto del servidor:', error);
        mostrarMensaje('Error al eliminar producto', 'error');
    });
}

/**
 * Actualizar cantidad de un producto en el carrito
 * @param {number} idProducto - ID del producto
 * @param {number} nuevaCantidad - Nueva cantidad
 */
function actualizarCantidadCarrito(idProducto, nuevaCantidad) {
    idProducto = parseInt(idProducto);
    nuevaCantidad = parseInt(nuevaCantidad);
    
    if (nuevaCantidad <= 0) {
        eliminarDelCarrito(idProducto);
        return;
    }
    
    // Actualizar en carrito local
    const producto = carritoGlobal.find(item => item.id === idProducto);
    if (producto) {
        // Verificar stock
        if (producto.stock > 0 && nuevaCantidad > producto.stock) {
            mostrarMensaje(`Stock máximo: ${producto.stock}`, 'warning');
            nuevaCantidad = producto.stock;
        }
        
        producto.cantidad = nuevaCantidad;
        localStorage.setItem('carrito', JSON.stringify(carritoGlobal));
        
        // Actualizar en servidor
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `accion=actualizar_cantidad&id_producto=${idProducto}&cantidad=${nuevaCantidad}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.exitoso) {
                // Actualizar contador
                actualizarContadorCarrito(data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0));
                
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
                
                mostrarMensaje('Cantidad actualizada', 'success');
            } else {
                mostrarMensaje('Error al actualizar cantidad', 'error');
            }
        })
        .catch(error => {
            console.error('Error al actualizar cantidad:', error);
            mostrarMensaje('Error al actualizar cantidad', 'error');
        });
    }
}

/**
 * Vaciar todo el carrito
 */
function vaciarCarrito() {
    if (confirm('¿Estás seguro de que quieres vaciar todo tu carrito?')) {
        // Vaciar carrito local
        carritoGlobal = [];
        localStorage.removeItem('carrito');
        
        // Vaciar en el servidor
        fetch('/artesanoDigital/controllers/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'accion=vaciar_carrito'
        })
        .then(res => res.json())
        .then(data => {
            if (data.exitoso) {
                // Actualizar contador
                actualizarContadorCarrito(0);
                
                // Actualizar minicarrito si existe
                if (typeof actualizarMiniCarrito === 'function') {
                    actualizarMiniCarrito();
                }
                
                // Si estamos en la página de checkout, redirigir al inicio
                if (window.location.pathname.includes('checkout')) {
                    mostrarMensaje('Carrito vaciado. Redirigiendo...', 'info');
                    setTimeout(() => {
                        window.location.href = '/artesanoDigital/';
                    }, 1500);
                } else {
                    mostrarMensaje('Carrito vaciado', 'success');
                }
            } else {
                mostrarMensaje('Error al vaciar el carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error al vaciar carrito:', error);
            mostrarMensaje('Error al vaciar el carrito', 'error');
        });
    }
}

/**
 * Mostrar un mensaje toast
 * @param {string} mensaje - Texto del mensaje
 * @param {string} tipo - Tipo de mensaje (info, success, error, warning)
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    // Si hay un contenedor específico para mensajes, usarlo
    const msgContainer = document.getElementById('cart-message-container');
    if (msgContainer) {
        // Crear una alerta Bootstrap
        const alertClass = tipo === 'error' ? 'alert-danger' : 
                        tipo === 'success' ? 'alert-success' : 
                        tipo === 'warning' ? 'alert-warning' : 'alert-info';
        
        msgContainer.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${mensaje}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        // Auto-cerrar después de 3 segundos
        setTimeout(() => {
            const alert = msgContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => {
                    msgContainer.innerHTML = '';
                }, 150);
            }
        }, 3000);
        
        return;
    }
    
    // Si no hay contenedor específico, usar un toast
    // Verificar si ya existe el contenedor de toasts, si no, crearlo
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Añadir estilos si no existen
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                }
                
                .toast {
                    max-width: 350px;
                    background-color: #fff;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    border-radius: 0.25rem;
                    margin-bottom: 0.75rem;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                
                .toast-mostrar {
                    opacity: 1;
                }
                
                .toast-contenido {
                    padding: 0.75rem 1.25rem;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                
                .toast-cerrar {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    font-weight: 700;
                    line-height: 1;
                    color: #000;
                    opacity: 0.5;
                    cursor: pointer;
                }
                
                .toast-info {
                    border-left: 4px solid #17a2b8;
                }
                
                .toast-success {
                    border-left: 4px solid #28a745;
                }
                
                .toast-error {
                    border-left: 4px solid #dc3545;
                }
                
                .toast-warning {
                    border-left: 4px solid #ffc107;
                }
                
                /* Animación para el contador */
                .animate-pulse {
                    animation: pulse 1s cubic-bezier(0, 0, 0.2, 1) infinite;
                }
                
                @keyframes pulse {
                    0%, 100% {
                        opacity: 1;
                    }
                    50% {
                        opacity: 0.5;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Crear el toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-contenido">
            <span class="toast-mensaje">${mensaje}</span>
            <button class="toast-cerrar">&times;</button>
        </div>
    `;
    
    // Añadir al contenedor
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
