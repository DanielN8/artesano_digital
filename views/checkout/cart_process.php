<?php
// Vista: Proceso de checkout en una sola página
// Esta vista contiene todos los pasos del proceso de compra

// Iniciar buffer de salida
ob_start();
?>

<div class="checkout-container">
  <div class="checkout-header">
    <h1 class="checkout-title">Finalizar compra</h1>
    <p class="checkout-subtitle">Complete los siguientes pasos para realizar su pedido</p>
  </div>

  <!-- Proceso de checkout de una sola página con tabs/steps -->
  <div class="checkout-wrapper">
    <div class="checkout-steps">
      <div class="step completed" data-step="1">
        <span class="step-number">1</span>
        <span class="step-title">Carrito</span>
      </div>
      <div class="step active" data-step="2">
        <span class="step-number">2</span>
        <span class="step-title">Dirección</span>
      </div>
      <div class="step" data-step="3">
        <span class="step-number">3</span>
        <span class="step-title">Pago</span>
      </div>
      <div class="step" data-step="4">
        <span class="step-number">4</span>
        <span class="step-title">Confirmación</span>
      </div>
    </div>

    <form id="checkout-form" method="POST" action="/artesanoDigital/checkout/completado">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
      
      <!-- PASO 1: CARRITO -->
      <div class="checkout-step-content" id="step-1">
        <h2>Revisa tu carrito</h2>
        
        <div id="cart-message-container"></div>
        
        <?php if (empty($productos)): ?>
          <div class="alert alert-info">
            Tu carrito está vacío. <a href="/artesanoDigital">Continúa comprando</a>
          </div>
        <?php else: ?>
          <div class="cart-items">
            <table class="cart-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Precio</th>
                  <th>Cantidad</th>
                  <th>Subtotal</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="cart-items-container">
                <?php foreach ($productos as $producto): ?>
                <tr data-id="<?php echo $producto['id_producto']; ?>" class="cart-item-row">
                  <td class="product-info">
                    <div class="product-image">
                      <img src="/artesanoDigital/uploads/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    </div>
                    <div class="product-details">
                      <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                      <p class="product-vendor">Vendido por: <?php echo htmlspecialchars($producto['nombre_tienda'] ?? 'Artesano Digital'); ?></p>
                    </div>
                  </td>
                  <td class="product-price">
                    B/. <span class="item-price"><?php echo number_format($producto['precio'], 2); ?></span>
                  </td>
                  <td class="product-quantity">
                    <div class="quantity-selector">
                      <button type="button" class="quantity-btn minus" data-id="<?php echo $producto['id_producto']; ?>">-</button>
                      <input type="number" name="cantidad[<?php echo $producto['id_producto']; ?>]" 
                        value="<?php echo $producto['cantidad']; ?>" min="1" max="<?php echo $producto['stock']; ?>"
                        class="quantity-input" data-id="<?php echo $producto['id_producto']; ?>" readonly>
                      <button type="button" class="quantity-btn plus" data-id="<?php echo $producto['id_producto']; ?>">+</button>
                    </div>
                  </td>
                  <td class="product-subtotal">
                    B/. <span class="item-subtotal"><?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                  </td>
                  <td class="product-actions">
                    <button type="button" class="btn-remove" data-id="<?php echo $producto['id_producto']; ?>">
                      <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr class="cart-total">
                  <td colspan="3" class="text-right"><strong>Total:</strong></td>
                  <td><strong>B/. <span id="cart-total"><?php echo number_format($total, 2); ?></span></strong></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="form-group text-right mt-4">
            <button type="button" class="btn btn-primary next-step">Continuar</button>
          </div>
        <?php endif; ?>
      </div>

      <!-- PASO 2: DIRECCIÓN -->
      <div class="checkout-step-content" id="step-2" style="display: none;">
        <h2>Información de envío</h2>
        
        <!-- Resumen del carrito -->
        <div class="cart-summary mb-4">
          <h4>Resumen de tu pedido</h4>
          <div class="card">
            <div class="card-body">
              <div id="cart-summary-step2">
                <!-- Aquí se mostrarán los productos del carrito -->
              </div>
              <hr>
              <div class="d-flex justify-content-between font-weight-bold">
                <span>Total:</span>
                <span id="cart-summary-total-step2">$0.00</span>
              </div>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="nombre">Nombre completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required
                value="<?php echo $usuario['nombre'] ?? ''; ?>">
          </div>
          <div class="form-group col-md-6">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required
                value="<?php echo $usuario['telefono'] ?? ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="direccion">Dirección</label>
          <textarea class="form-control" id="direccion" name="direccion" required rows="3"><?php echo $usuario['direccion'] ?? ''; ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="ciudad">Ciudad</label>
            <select class="form-control" id="ciudad" name="ciudad" required>
              <option value="">Seleccione...</option>
              <option value="La Chorrera">La Chorrera</option>
              <option value="Arraiján">Arraiján</option>
              <option value="Capira">Capira</option>
              <option value="Chame">Chame</option>
              <option value="San Carlos">San Carlos</option>
              <option value="Ciudad de Panamá">Ciudad de Panamá</option>
            </select>
          </div>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="button" class="btn btn-primary next-step">Continuar</button>
        </div>
      </div>

      <!-- PASO 3: PAGO -->
      <div class="checkout-step-content" id="step-3" style="display: none;">
        <h2>Método de pago</h2>
        
        <div class="payment-methods">
          <div class="form-check payment-method">
            <input class="form-check-input" type="radio" name="metodo_pago" id="tarjeta" value="tarjeta" checked>
            <label class="form-check-label" for="tarjeta">
              <i class="fas fa-credit-card"></i> Tarjeta de crédito/débito
            </label>
            <div class="payment-details" id="tarjeta-details">
              <div class="form-row">
                <div class="form-group col-12">
                  <label for="nombre_tarjeta">Nombre en la tarjeta</label>
                  <input type="text" class="form-control" id="nombre_tarjeta" name="nombre_tarjeta">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-8">
                  <label for="numero_tarjeta">Número de tarjeta</label>
                  <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" 
                      placeholder="XXXX XXXX XXXX XXXX">
                </div>
                <div class="form-group col-md-2">
                  <label for="expiracion">MM/AA</label>
                  <input type="text" class="form-control" id="expiracion" name="expiracion" 
                      placeholder="MM/AA">
                </div>
                <div class="form-group col-md-2">
                  <label for="cvv">CVV</label>
                  <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-check payment-method">
            <input class="form-check-input" type="radio" name="metodo_pago" id="yappy" value="yappy">
            <label class="form-check-label" for="yappy">
              <i class="fab fa-mobile-alt"></i> Yappy
            </label>
            <div class="payment-details" id="yappy-details" style="display: none;">
              <div class="form-group">
                <label for="telefono_yappy">Número de teléfono registrado en Yappy</label>
                <input type="text" class="form-control" id="telefono_yappy" name="telefono_yappy" 
                    value="<?php echo $usuario['telefono'] ?? ''; ?>" placeholder="6XXX-XXXX">
                <small class="form-text text-muted">Recibirás una notificación en Yappy para completar el pago.</small>
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="button" class="btn btn-primary next-step">Continuar</button>
        </div>
      </div>

      <!-- PASO 4: CONFIRMACIÓN -->
      <div class="checkout-step-content" id="step-4" style="display: none;">
        <h2>Confirmar pedido</h2>
        <div class="order-summary">
          <div class="summary-section">
            <h4>Resumen de productos</h4>
            <div class="summary-products">
              <?php foreach ($productos as $producto): ?>
                <div class="summary-product">
                  <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                  <span class="product-quantity">x<?php echo $producto['cantidad']; ?></span>
                  <span class="product-price">B/. <?php echo number_format($producto['subtotal'], 2); ?></span>
                </div>
              <?php endforeach; ?>
              <div class="summary-total">
                <span>Total a pagar:</span>
                <span class="total-price">B/. <?php echo number_format($total, 2); ?></span>
              </div>
            </div>
          </div>
          
          <div class="summary-section">
            <h4>Información de envío</h4>
            <div class="shipping-info">
              <p><strong>Nombre:</strong> <span id="summary-nombre"></span></p>
              <p><strong>Teléfono:</strong> <span id="summary-telefono"></span></p>
              <p><strong>Dirección:</strong> <span id="summary-direccion"></span></p>
              <p><strong>Ciudad:</strong> <span id="summary-ciudad"></span></p>
            </div>
          </div>
          
          <div class="summary-section">
            <h4>Método de pago</h4>
            <div class="payment-info">
              <p id="summary-payment-method"></p>
            </div>
          </div>
        </div>
        
        <div class="form-check mt-4">
          <input type="checkbox" class="form-check-input" id="acepto-terminos" name="acepto_terminos" required>
          <label class="form-check-label" for="acepto-terminos">
            Acepto los términos y condiciones de compra
          </label>
        </div>
        
        <div class="form-group text-right mt-4">
          <button type="button" class="btn btn-secondary prev-step">Regresar</button>
          <button type="submit" class="btn btn-success" id="btn-completar-compra">Completar compra</button>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
/* Estilos para el proceso de checkout */
.checkout-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.checkout-header {
  text-align: center;
  margin-bottom: 2rem;
}

.checkout-title {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.checkout-subtitle {
  color: #6c757d;
}

.checkout-wrapper {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 2rem;
}

/* Pasos del checkout */
.checkout-steps {
  display: flex;
  justify-content: space-between;
  margin-bottom: 2rem;
  position: relative;
}

.checkout-steps::before {
  content: "";
  position: absolute;
  top: 24px;
  left: 0;
  right: 0;
  height: 2px;
  background: #e9ecef;
  z-index: 1;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 2;
}

.step-number {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  color: #adb5bd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.step-title {
  color: #adb5bd;
  font-size: 0.9rem;
}

.step.active .step-number,
.step.completed .step-number {
  background: #007bff;
  border-color: #007bff;
  color: #fff;
}

.step.active .step-title,
.step.completed .step-title {
  color: #007bff;
  font-weight: bold;
}

.step.completed .step-number::after {
  content: "✓";
}

/* Contenido de los pasos */
.checkout-step-content {
  margin-bottom: 1.5rem;
}

.checkout-step-content h2 {
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 0.75rem;
  margin-bottom: 1.5rem;
}

/* Carrito */
.cart-table {
  width: 100%;
  border-collapse: collapse;
}

.cart-table th,
.cart-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.product-info {
  display: flex;
  align-items: center;
}

.product-image {
  width: 80px;
  height: 80px;
  margin-right: 1rem;
  border-radius: 4px;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-vendor {
  font-size: 0.85rem;
  color: #6c757d;
}

.quantity-selector {
  display: flex;
  align-items: center;
}

.quantity-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #dee2e6;
  background: #f8f9fa;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.quantity-input {
  width: 40px;
  height: 30px;
  text-align: center;
  border: 1px solid #dee2e6;
  border-left: none;
  border-right: none;
}

.btn-remove {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
}

.cart-total td {
  padding-top: 1.5rem;
}

/* Métodos de pago */
.payment-methods {
  margin-bottom: 1.5rem;
}

.payment-method {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.payment-method label {
  font-weight: 500;
  margin-left: 0.5rem;
  cursor: pointer;
}

.payment-details {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px dashed #dee2e6;
}

/* Confirmación */
.order-summary {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.summary-section {
  margin-bottom: 1.5rem;
}

.summary-section h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.summary-product {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px dashed #dee2e6;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  font-weight: bold;
  margin-top: 1rem;
  padding-top: 0.5rem;
  border-top: 2px solid #dee2e6;
}

.shipping-info p,
.payment-info p {
  margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Variables
  const steps = document.querySelectorAll('.step');
  const stepContents = document.querySelectorAll('.checkout-step-content');
  const nextButtons = document.querySelectorAll('.next-step');
  const prevButtons = document.querySelectorAll('.prev-step');
  const paymentMethods = document.querySelectorAll('input[name="metodo_pago"]');
  const paymentDetails = document.querySelectorAll('.payment-details');
  const form = document.getElementById('checkout-form');
  const cartMessageContainer = document.getElementById('cart-message-container');
  // Iniciar directamente en el paso de dirección (paso 2)
  let currentStep = 2;
  
  // Inicializar - mostrar el paso 2 directamente
  updateSteps();
  
  // Ocultar explícitamente el paso 1 (carrito)
  document.getElementById('step-1').style.display = 'none';
  
  // Mostrar explícitamente el paso 2 (dirección)
  document.getElementById('step-2').style.display = 'block';

  // Sincronizar carrito al cargar la página
  sincronizarCarritoCheckout();
  
  // Actualizar el resumen con los datos del carrito para que se muestre en el paso 2
  setTimeout(updateSummary, 500);

  // Event listeners para botones siguiente
  nextButtons.forEach(button => {
    button.addEventListener('click', function() {
      if (validateStep(currentStep)) {
        currentStep++;
        updateSteps();
        updateSummary();
      }
    });
  });

  // Event listeners para botones anterior
  prevButtons.forEach(button => {
    button.addEventListener('click', function() {
      currentStep--;
      updateSteps();
    });
  });

  // Event listeners para métodos de pago
  paymentMethods.forEach(method => {
    method.addEventListener('change', function() {
      // Ocultar todos los detalles
      paymentDetails.forEach(details => {
        details.style.display = 'none';
      });
      
      // Mostrar los detalles del método seleccionado
      document.getElementById(`${this.value}-details`).style.display = 'block';
    });
  });

  // Validación antes de envío del formulario
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!validateStep(4)) {
        e.preventDefault();
      }
    });
  }
  
  // Actualizar los pasos (mostrar/ocultar)
  function updateSteps() {
    steps.forEach((step, index) => {
      const stepNumber = index + 1;
      
      if (stepNumber < currentStep) {
        step.classList.add('completed');
        step.classList.remove('active');
      } else if (stepNumber === currentStep) {
        step.classList.add('active');
        step.classList.remove('completed');
      } else {
        step.classList.remove('active', 'completed');
      }
    });
    
    stepContents.forEach((content, index) => {
      const stepNumber = index + 1;
      if (stepNumber === currentStep) {
        content.style.display = 'block';
      } else {
        content.style.display = 'none';
      }
    });
  }

  // Validación de cada paso
  function validateStep(step) {
    switch(step) {
      case 1:
        // Verificar que hay productos en el carrito
        const cartRows = document.querySelectorAll('#cart-items-container .cart-item-row');
        if (!cartRows || cartRows.length === 0) {
          mostrarMensaje('Tu carrito está vacío', 'error');
          return false;
        }
        return true;
        
      case 2:
        // Validar campos de dirección
        const nombre = document.getElementById('nombre').value;
        const telefono = document.getElementById('telefono').value;
        const direccion = document.getElementById('direccion').value;
        const ciudad = document.getElementById('ciudad').value;
        
        if (!nombre || !telefono || !direccion || !ciudad) {
          mostrarMensaje('Por favor completa todos los campos obligatorios', 'error');
          return false;
        }
        return true;
        
      case 3:
        // Validar datos de pago
        const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
        
        if (metodoPago === 'tarjeta') {
          const nombreTarjeta = document.getElementById('nombre_tarjeta').value;
          const numeroTarjeta = document.getElementById('numero_tarjeta').value;
          const expiracion = document.getElementById('expiracion').value;
          const cvv = document.getElementById('cvv').value;
          
          if (!nombreTarjeta || !numeroTarjeta || !expiracion || !cvv) {
            mostrarMensaje('Por favor completa todos los campos de la tarjeta', 'error');
            return false;
          }
        } else if (metodoPago === 'yappy') {
          const telefonoYappy = document.getElementById('telefono_yappy').value;
          
          if (!telefonoYappy) {
            mostrarMensaje('Por favor ingresa el número de teléfono registrado en Yappy', 'error');
            return false;
          }
        }
        return true;
        
      case 4:
        // Validar aceptación de términos
        if (!document.getElementById('acepto-terminos').checked) {
          mostrarMensaje('Debes aceptar los términos y condiciones para continuar', 'error');
          return false;
        }
        return true;
    }
    return true;
  }

  // Actualizar resumen
  function updateSummary() {
    // Siempre actualizar el resumen del carrito para el paso 2 y para el paso 4
    // Obtener los productos del carrito y mostrarlos en el resumen
    fetch('/artesanoDigital/controllers/checkout.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'accion=obtener_carrito'
    })
    .then(res => res.json())
    .then(data => {
      if (data.exitoso) {
        // Actualizar el resumen en el paso 2
        const summaryItemsStep2 = document.querySelector('#cart-summary-step2');
        if (summaryItemsStep2) {
          let html = '';
          let total = 0;
          
          data.carrito.forEach(item => {
            const subtotal = parseFloat(item.precio) * parseInt(item.cantidad);
            total += subtotal;
            html += `<div class="summary-item">
              <span class="product-name">${item.nombre} x ${item.cantidad}</span>
              <span class="product-price">$${subtotal.toFixed(2)}</span>
            </div>`;
          });
          
          summaryItemsStep2.innerHTML = html;
          
          const totalElement = document.querySelector('#cart-summary-total-step2');
          if (totalElement) {
            totalElement.textContent = '$' + total.toFixed(2);
          }
        }
      }
    })
    .catch(error => {
      console.error('Error al actualizar resumen:', error);
    });
    
    // Si estamos en el paso de confirmación, actualizar todos los datos del resumen
    if (currentStep === 4) {
      document.getElementById('summary-nombre').textContent = document.getElementById('nombre').value;
      document.getElementById('summary-telefono').textContent = document.getElementById('telefono').value;
      document.getElementById('summary-direccion').textContent = document.getElementById('direccion').value;
      document.getElementById('summary-ciudad').textContent = document.getElementById('ciudad').value;
      
      const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
      if (metodoPago === 'tarjeta') {
        document.getElementById('summary-payment-method').textContent = 'Tarjeta que termina en ' + 
          document.getElementById('numero_tarjeta').value.slice(-4);
      } else if (metodoPago === 'yappy') {
        document.getElementById('summary-payment-method').textContent = 'Yappy al número ' + 
          document.getElementById('telefono_yappy').value;
      }
    }
  }

  // Manejo de cantidades en el carrito
  const quantityBtns = document.querySelectorAll('.quantity-btn');
  quantityBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const input = this.parentElement.querySelector('.quantity-input');
      const currentValue = parseInt(input.value);
      const productId = this.getAttribute('data-id');
      
      if (this.classList.contains('plus')) {
        const max = parseInt(input.getAttribute('max'));
        if (currentValue < max) {
          input.value = currentValue + 1;
          updateCartItemQuantity(productId, input.value);
        } else {
          mostrarMensaje('No hay más stock disponible para este producto', 'warning');
        }
      } else if (this.classList.contains('minus')) {
        if (currentValue > 1) {
          input.value = currentValue - 1;
          updateCartItemQuantity(productId, input.value);
        }
      }
    });
  });
  
  // Función para actualizar el total del carrito desde el servidor
  function refreshCartTotal() {
    fetch('/artesanoDigital/controllers/checkout.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'accion=obtener_carrito'
    })
    .then(res => res.json())
    .then(data => {
      if (data.exitoso) {
        // Actualizar el total en la interfaz
        const cartTotalElement = document.getElementById('cart-total');
        if (cartTotalElement) {
          cartTotalElement.textContent = data.total.toFixed(2);
        }
        
        // Si estamos en el paso de confirmación, actualizar también el resumen
        const summaryTotalElement = document.querySelector('.summary-total .total-price');
        if (summaryTotalElement) {
          summaryTotalElement.textContent = `B/. ${data.total.toFixed(2)}`;
        }
        
        // Actualizar el contador de productos en el header
        if (typeof actualizarContadorCarrito === 'function') {
          const totalProductos = data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0);
          actualizarContadorCarrito(totalProductos);
        }
      }
    })
    .catch(error => {
      console.error('Error al actualizar total:', error);
    });
  }

  // Función para actualizar la cantidad del producto en el carrito (AJAX)
  function updateCartItemQuantity(productId, quantity) {
    // Actualizar en el servidor mediante AJAX
    fetch('/artesanoDigital/controllers/checkout.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: `accion=actualizar_cantidad&id_producto=${productId}&cantidad=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.exitoso) {
        // Actualizar el subtotal del producto
        const productRow = document.querySelector(`tr[data-id="${productId}"]`);
        if (productRow) {
          const priceText = productRow.querySelector('.item-price').textContent.trim();
          const price = parseFloat(priceText);
          const subtotal = (price * quantity).toFixed(2);
          productRow.querySelector('.item-subtotal').textContent = subtotal;
        }
        
        // Actualizar también el carrito en localStorage para mantener sincronización
        let carritoLocal = JSON.parse(localStorage.getItem('carrito')) || [];
        carritoLocal.forEach(item => {
          if (item.id === parseInt(productId)) {
            item.cantidad = parseInt(quantity);
          }
        });
        localStorage.setItem('carrito', JSON.stringify(carritoLocal));
        
        // Actualizar el total del carrito
        updateCartTotal();
        
        // Mostrar mensaje de éxito
        mostrarMensaje('Cantidad actualizada', 'success');
      } else {
        mostrarMensaje('Error al actualizar la cantidad del producto', 'error');
      }
    })
    .catch(error => {
      console.error('Error al actualizar cantidad:', error);
      mostrarMensaje('Error al actualizar la cantidad del producto', 'error');
    });
  }

  // Función para calcular y actualizar el total del carrito desde la interfaz
  function updateCartTotal() {
    let total = 0;
    document.querySelectorAll('.item-subtotal').forEach(subtotal => {
      total += parseFloat(subtotal.textContent);
    });
    
    const cartTotalElement = document.getElementById('cart-total');
    if (cartTotalElement) {
      cartTotalElement.textContent = total.toFixed(2);
    }
    
    // Actualizar también en el resumen si estamos en ese paso
    if (currentStep === 4) {
      const summaryTotalElement = document.querySelector('.summary-total .total-price');
      if (summaryTotalElement) {
        summaryTotalElement.textContent = `B/. ${total.toFixed(2)}`;
      }
    }
    
    return total;
  }

  // Botones para eliminar productos del carrito
  const removeButtons = document.querySelectorAll('.btn-remove');
  removeButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
        const productId = this.getAttribute('data-id');
        const productRow = this.closest('tr');
        
        // Eliminar del servidor mediante AJAX
        fetch('/artesanoDigital/controllers/checkout.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: `accion=eliminar_producto&id_producto=${productId}`
        })
        .then(res => res.json())
        .then(data => {
          if (data.exitoso) {
            // Eliminar de la interfaz
            if (productRow) {
              productRow.remove();
            }
            
            // Si no quedan productos, mostrar mensaje
            if (!document.querySelectorAll('#cart-items-container .cart-item-row').length) {
              // Mostrar alerta de carrito vacío
              if (cartMessageContainer) {
                cartMessageContainer.innerHTML = `
                  <div class="alert alert-info">
                    Tu carrito está vacío. <a href="/artesanoDigital">Continúa comprando</a>
                  </div>
                `;
              }
              
              // Ocultar la tabla de carrito
              const cartTable = document.querySelector('.cart-table');
              if (cartTable) {
                cartTable.style.display = 'none';
              }
              
              // Ocultar botón de continuar
              const nextStepBtn = document.querySelector('#step-1 .next-step');
              if (nextStepBtn) {
                nextStepBtn.style.display = 'none';
              }
            }
            
            // Actualizar también el carrito en localStorage
            let carritoLocal = JSON.parse(localStorage.getItem('carrito')) || [];
            carritoLocal = carritoLocal.filter(item => item.id !== parseInt(productId));
            localStorage.setItem('carrito', JSON.stringify(carritoLocal));
            
            // Actualizar el contador y el total
            const totalProductos = data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0);
            if (typeof actualizarContadorCarrito === 'function') {
              actualizarContadorCarrito(totalProductos);
            }
            
            updateCartTotal();
            
            // Mostrar mensaje de éxito
            mostrarMensaje('Producto eliminado del carrito', 'success');
          } else {
            mostrarMensaje('Error al eliminar el producto', 'error');
          }
        })
        .catch(error => {
          console.error('Error al eliminar producto:', error);
          mostrarMensaje('Error al eliminar el producto', 'error');
        });
      }
    });
  });
  
  // Función para mostrar mensajes
  function mostrarMensaje(mensaje, tipo = 'info') {
    // Si hay un contenedor específico para mensajes del carrito, usarlo
    if (cartMessageContainer) {
      // Crear una alerta Bootstrap
      const alertClass = tipo === 'error' ? 'alert-danger' : 
                        tipo === 'success' ? 'alert-success' : 
                        tipo === 'warning' ? 'alert-warning' : 'alert-info';
      
      cartMessageContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
          ${mensaje}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      `;
      
      // Auto-cerrar después de 3 segundos
      setTimeout(() => {
        const alert = cartMessageContainer.querySelector('.alert');
        if (alert) {
          alert.classList.remove('show');
          setTimeout(() => {
            cartMessageContainer.innerHTML = '';
          }, 150);
        }
      }, 3000);
      
      return;
    }
    
    // Si no hay contenedor específico, usar un toast
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

  // Función para sincronizar el carrito entre localStorage y servidor
  function sincronizarCarritoCheckout() {
    // Si hay carrito en localStorage, sincronizarlo con el servidor
    const carritoLocal = localStorage.getItem('carrito');
    if (carritoLocal) {
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
          console.log('Carrito sincronizado en checkout');
          
          // Actualizar la interfaz si hay cambios significativos
          if (data.carrito && data.carrito.length > 0) {
            // Actualizar el contador de productos en el header
            const totalProductos = data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0);
            if (typeof actualizarContadorCarrito === 'function') {
              actualizarContadorCarrito(totalProductos);
            }
            
            // Si hay cambios en el carrito que requieren actualizar la vista
            const currentCartItems = document.querySelectorAll('#cart-items-container .cart-item-row');
            if (data.carrito.length !== currentCartItems.length) {
              // La cantidad de productos cambió, podría ser necesario recargar
              if (confirm('El contenido del carrito ha cambiado. ¿Desea actualizar la página?')) {
                window.location.reload();
                return;
              }
            }
            
            // Actualizar subtotales y total
            updateCartTotal();
          } else {
            // El carrito está vacío, mostrar mensaje si es necesario
            if (cartMessageContainer) {
              cartMessageContainer.innerHTML = `
                <div class="alert alert-info">
                  Tu carrito está vacío. <a href="/artesanoDigital">Continúa comprando</a>
                </div>
              `;
              
              // Ocultar la tabla de carrito si existe
              const cartTable = document.querySelector('.cart-table');
              if (cartTable) {
                cartTable.style.display = 'none';
              }
              
              // Ocultar botón de continuar
              const nextStepBtn = document.querySelector('#step-1 .next-step');
              if (nextStepBtn) {
                nextStepBtn.style.display = 'none';
              }
            }
          }
        }
      })
      .catch(error => console.error('Error al sincronizar carrito:', error));
    }
  }
  
  // Estilos adicionales para el mensaje de carrito vacío
  const style = document.createElement('style');
  style.textContent = `
    .alert-dismissible {
      position: relative;
      padding-right: 4rem;
    }
    .alert-dismissible .close {
      position: absolute;
      top: 0;
      right: 0;
      padding: 0.75rem 1.25rem;
      background: transparent;
      border: 0;
      font-size: 1.5rem;
      font-weight: 700;
      line-height: 1;
      color: inherit;
      cursor: pointer;
    }
    
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
    
    /* Estilo para vaciar carrito */
    .btn-vaciar-carrito {
      margin-right: 1rem;
    }
  `;
  document.head.appendChild(style);

  // Añadir botón para vaciar carrito completo
  const btnContainer = document.querySelector('#step-1 .form-group');
  if (btnContainer) {
    const btnVaciar = document.createElement('button');
    btnVaciar.type = 'button';
    btnVaciar.className = 'btn btn-outline-danger btn-vaciar-carrito';
    btnVaciar.textContent = 'Vaciar carrito';
    btnVaciar.addEventListener('click', vaciarCarrito);
    
    // Insertar antes del botón Continuar
    const btnContinuar = btnContainer.querySelector('.next-step');
    if (btnContinuar) {
      btnContainer.insertBefore(btnVaciar, btnContinuar);
    } else {
      btnContainer.appendChild(btnVaciar);
    }
  }

  // Función para vaciar completamente el carrito
  function vaciarCarrito() {
    if (confirm('¿Estás seguro de que quieres vaciar todo tu carrito?')) {
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
          // Limpiar localStorage
          localStorage.removeItem('carrito');
          
          // Actualizar UI
          if (cartMessageContainer) {
            cartMessageContainer.innerHTML = `
              <div class="alert alert-info">
                Tu carrito ha sido vaciado. <a href="/artesanoDigital">Continúa comprando</a>
              </div>
            `;
          }
          
          // Ocultar tabla y botón
          const cartTable = document.querySelector('.cart-table');
          if (cartTable) {
            cartTable.style.display = 'none';
          }
          
          const nextStepBtn = document.querySelector('#step-1 .next-step');
          if (nextStepBtn) {
            nextStepBtn.style.display = 'none';
          }
          
          // Ocultar botón vaciar
          const btnVaciar = document.querySelector('.btn-vaciar-carrito');
          if (btnVaciar) {
            btnVaciar.style.display = 'none';
          }
          
          // Actualizar contador en el header
          if (typeof actualizarContadorCarrito === 'function') {
            actualizarContadorCarrito(0);
          }
          
          mostrarMensaje('Carrito vaciado correctamente', 'success');
        } else {
          mostrarMensaje('No se pudo vaciar el carrito', 'error');
        }
      })
      .catch(error => {
        console.error('Error al vaciar carrito:', error);
        mostrarMensaje('Error al vaciar el carrito', 'error');
      });
    }
  }

  // Función para cargar el carrito dinámicamente
  function cargarCarritoDinamico() {
    fetch('/artesanoDigital/controllers/checkout.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'accion=obtener_carrito'
    })
    .then(res => res.json())
    .then(data => {
      if (data.exitoso) {
        const cartItemsContainer = document.getElementById('cart-items-container');
        const cartItems = data.carrito;
        
        if (cartItems.length === 0) {
          // Carrito vacío
          if (cartMessageContainer) {
            cartMessageContainer.innerHTML = `
              <div class="alert alert-info">
                Tu carrito está vacío. <a href="/artesanoDigital">Continúa comprando</a>
              </div>
            `;
          }
          
          // Ocultar elementos
          const cartTable = document.querySelector('.cart-table');
          if (cartTable) {
            cartTable.style.display = 'none';
          }
          
          const nextStepBtn = document.querySelector('#step-1 .next-step');
          if (nextStepBtn) {
            nextStepBtn.style.display = 'none';
          }
          
          const btnVaciar = document.querySelector('.btn-vaciar-carrito');
          if (btnVaciar) {
            btnVaciar.style.display = 'none';
          }
          
          return;
        }
        
        // Hay productos, generar HTML
        if (cartItemsContainer) {
          let html = '';
          cartItems.forEach(producto => {
            html += `
              <tr data-id="${producto.id_producto}" class="cart-item-row">
                <td class="product-info">
                  <div class="product-image">
                    <img src="/artesanoDigital/uploads/${producto.imagen}" 
                      alt="${producto.nombre}">
                  </div>
                  <div class="product-details">
                    <h4>${producto.nombre}</h4>
                    <p class="product-vendor">Vendido por: ${producto.nombre_tienda || 'Artesano Digital'}</p>
                  </div>
                </td>
                <td class="product-price">
                  B/. <span class="item-price">${parseFloat(producto.precio).toFixed(2)}</span>
                </td>
                <td class="product-quantity">
                  <div class="quantity-selector">
                    <button type="button" class="quantity-btn minus" data-id="${producto.id_producto}">-</button>
                    <input type="number" name="cantidad[${producto.id_producto}]" 
                      value="${producto.cantidad}" min="1" max="${producto.stock}"
                      class="quantity-input" data-id="${producto.id_producto}" readonly>
                    <button type="button" class="quantity-btn plus" data-id="${producto.id_producto}">+</button>
                  </div>
                </td>
                <td class="product-subtotal">
                  B/. <span class="item-subtotal">${(parseFloat(producto.precio) * parseInt(producto.cantidad)).toFixed(2)}</span>
                </td>
                <td class="product-actions">
                  <button type="button" class="btn-remove" data-id="${producto.id_producto}">
                    <i class="fas fa-trash-alt"></i> Eliminar
                  </button>
                </td>
              </tr>
            `;
          });
          
          cartItemsContainer.innerHTML = html;
          
          // Actualizar el total
          const total = cartItems.reduce((sum, item) => {
            return sum + (parseFloat(item.precio) * parseInt(item.cantidad));
          }, 0);
          
          const cartTotalElement = document.getElementById('cart-total');
          if (cartTotalElement) {
            cartTotalElement.textContent = total.toFixed(2);
          }
          
          // Actualizar contador en el header
          if (typeof actualizarContadorCarrito === 'function') {
            const totalProductos = cartItems.reduce((total, item) => total + parseInt(item.cantidad), 0);
            actualizarContadorCarrito(totalProductos);
          }
          
          // Re-adjuntar event listeners
          const newQuantityBtns = document.querySelectorAll('.quantity-btn');
          newQuantityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
              const input = this.parentElement.querySelector('.quantity-input');
              const currentValue = parseInt(input.value);
              const productId = this.getAttribute('data-id');
              
              if (this.classList.contains('plus')) {
                const max = parseInt(input.getAttribute('max'));
                if (currentValue < max) {
                  input.value = currentValue + 1;
                  updateCartItemQuantity(productId, input.value);
                } else {
                  mostrarMensaje('No hay más stock disponible para este producto', 'warning');
                }
              } else if (this.classList.contains('minus')) {
                if (currentValue > 1) {
                  input.value = currentValue - 1;
                  updateCartItemQuantity(productId, input.value);
                }
              }
            });
          });
          
          const newRemoveButtons = document.querySelectorAll('.btn-remove');
          newRemoveButtons.forEach(btn => {
            btn.addEventListener('click', function() {
              if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                const productId = this.getAttribute('data-id');
                const productRow = this.closest('tr');
                
                // Eliminar del servidor mediante AJAX
                fetch('/artesanoDigital/controllers/checkout.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                  },
                  body: `accion=eliminar_producto&id_producto=${productId}`
                })
                .then(res => res.json())
                .then(data => {
                  if (data.exitoso) {
                    // Eliminar de la interfaz
                    if (productRow) {
                      productRow.remove();
                    }
                    
                    // Si no quedan productos, mostrar mensaje
                    if (!document.querySelectorAll('#cart-items-container .cart-item-row').length) {
                      cargarCarritoDinamico(); // Recargar vista
                    }
                    
                    // Actualizar también el carrito en localStorage
                    let carritoLocal = JSON.parse(localStorage.getItem('carrito')) || [];
                    carritoLocal = carritoLocal.filter(item => item.id !== parseInt(productId));
                    localStorage.setItem('carrito', JSON.stringify(carritoLocal));
                    
                    // Actualizar el contador y el total
                    const totalProductos = data.carrito.reduce((total, item) => total + parseInt(item.cantidad), 0);
                    if (typeof actualizarContadorCarrito === 'function') {
                      actualizarContadorCarrito(totalProductos);
                    }
                    
                    updateCartTotal();
                    
                    // Mostrar mensaje de éxito
                    mostrarMensaje('Producto eliminado del carrito', 'success');
                  } else {
                    mostrarMensaje('Error al eliminar el producto', 'error');
                  }
                })
                .catch(error => {
                  console.error('Error al eliminar producto:', error);
                  mostrarMensaje('Error al eliminar el producto', 'error');
                });
              }
            });
          });
          
          // Mostrar elementos
          const cartTable = document.querySelector('.cart-table');
          if (cartTable) {
            cartTable.style.display = 'table';
          }
          
          const nextStepBtn = document.querySelector('#step-1 .next-step');
          if (nextStepBtn) {
            nextStepBtn.style.display = 'block';
          }
          
          const btnVaciar = document.querySelector('.btn-vaciar-carrito');
          if (btnVaciar) {
            btnVaciar.style.display = 'inline-block';
          }
        }
      }
    })
    .catch(error => {
      console.error('Error al cargar carrito:', error);
      mostrarMensaje('Error al cargar el carrito', 'error');
    });
  }

  // Botón para recargar el carrito dinámicamente
  const reloadButton = document.createElement('button');
  reloadButton.type = 'button';
  reloadButton.className = 'btn btn-outline-primary mb-3';
  reloadButton.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar carrito';
  reloadButton.addEventListener('click', function() {
    cargarCarritoDinamico();
    mostrarMensaje('Carrito actualizado', 'info');
  });
  
  // Insertar al principio del paso 1
  const step1 = document.getElementById('step-1');
  if (step1) {
    const firstElement = step1.querySelector('h2');
    if (firstElement) {
      step1.insertBefore(reloadButton, firstElement.nextSibling);
    }
  }
});

</script>

<?php
// Obtener el contenido del buffer y limpiarlo
$contenido = ob_get_clean();

// Incluir la plantilla base
include __DIR__ . '/../layouts/base.php';
?>
