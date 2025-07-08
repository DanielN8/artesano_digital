<?php
// Vista: Proceso de checkout en una sola página
// Esta vista contiene todos los pasos del proceso de compra

// Iniciar buffer de salida
ob_start();

// --- Patrón Strategy para métodos de pago ---
interface MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array;
}

class PagoTarjeta implements MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array {
        // Validación simple
        if (empty($datos['numero_tarjeta']) || empty($datos['nombre_titular']) || empty($datos['cvv']) || empty($datos['expiracion'])) {
            return ['exitoso' => false, 'mensaje' => 'Datos de tarjeta incompletos'];
        }
        // Simulación de éxito
        return [
            'exitoso' => true,
            'mensaje' => 'Pago con tarjeta procesado',
            'transaccion_id' => 'TXN_' . uniqid() . '_CARD'
        ];
    }
}

class PagoYappy implements MetodoPagoStrategy {
    public function procesarPago(float $monto, array $datos): array {
        if (empty($datos['telefono'])) {
            return ['exitoso' => false, 'mensaje' => 'Número de teléfono requerido para Yappy'];
        }
        // Simulación de éxito
        return [
            'exitoso' => true,
            'mensaje' => 'Pago con Yappy procesado',
            'transaccion_id' => 'TXN_' . uniqid() . '_YAPPY'
        ];
    }
}

class ProcesadorPago {
    private MetodoPagoStrategy $metodo;
    public function __construct(MetodoPagoStrategy $metodo) {
        $this->metodo = $metodo;
    }
    public function procesar(float $monto, array $datos): array {
        return $this->metodo->procesarPago($monto, $datos);
    }
}

// --- Procesamiento del pago y guardado en la base de datos ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
    // Obtener datos necesarios
    $usuario = isset($usuario) ? $usuario : $_SESSION['usuario'] ?? null;
    if (!$usuario || !isset($usuario['id_usuario'])) {
        echo '<div class="alert alert-danger">Debes iniciar sesión para completar la compra</div>';
        exit;
    }
    
    $monto = $total ?? 0;
    $metodo = $_POST['metodo_pago'] ?? '';
    $datosPago = [];
    
    if ($metodo === 'tarjeta') {
        $datosPago = [
            'nombre_titular' => $_POST['nombre_tarjeta'] ?? '',
            'numero_tarjeta' => $_POST['numero_tarjeta'] ?? '',
            'expiracion' => $_POST['expiracion'] ?? '',
            'cvv' => $_POST['cvv'] ?? ''
        ];
        $procesador = new ProcesadorPago(new PagoTarjeta());
    } elseif ($metodo === 'yappy') {
        $datosPago = [
            'telefono' => $_POST['telefono_yappy'] ?? ''
        ];
        $procesador = new ProcesadorPago(new PagoYappy());
    } else {
        echo '<div class="alert alert-danger">Método de pago no válido</div>';
        exit;
    }
    
    // Procesar el pago
    $resultado = $procesador->procesar($monto, $datosPago);
    
    if (!$resultado['exitoso']) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($resultado['mensaje']) . '</div>';
    } else {
        // Preparar datos para guardar en la base de datos
        $direccionEnvio = json_encode([
            'nombre' => $_POST['nombre'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'telefono' => $_POST['telefono'] ?? ''
        ]);
        
        // Conexión a la base de datos
        try {
            require_once __DIR__ . '/../../config/Database.php';
            $db = \Config\Database::obtenerInstancia();
            $conexion = $db->obtenerConexion();
            
            // Iniciar transacción
            $conexion->beginTransaction();
            
            // 1. Guardar pedido en la tabla pedidos
            $sqlPedido = "INSERT INTO pedidos (id_usuario, estado, metodo_pago, total, fecha_pedido, direccion_envio) 
                          VALUES (:id_usuario, 'pendiente', :metodo_pago, :total, NOW(), :direccion_envio)";
            
            $stmtPedido = $conexion->prepare($sqlPedido);
            $stmtPedido->execute([
                'id_usuario' => $usuario['id_usuario'],
                'metodo_pago' => $metodo,
                'total' => $monto,
                'direccion_envio' => $direccionEnvio
            ]);
            
            $idPedido = $conexion->lastInsertId();
            
            // 2. Guardar productos del pedido
            $productos = isset($productos) ? $productos : $_SESSION['carrito'] ?? [];
            
            foreach ($productos as $producto) {
                $sqlProducto = "INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario) 
                                VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)";
                
                $stmtProducto = $conexion->prepare($sqlProducto);
                $stmtProducto->execute([
                    'id_pedido' => $idPedido,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio']
                ]);
                
                // Actualizar stock del producto (opcional)
                $sqlStock = "UPDATE productos SET stock = stock - :cantidad 
                             WHERE id_producto = :id_producto";
                             
                $stmtStock = $conexion->prepare($sqlStock);
                $stmtStock->execute([
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id_producto']
                ]);
            }
            
            // 3. Crear notificación para el artesano
            $sqlNotificacion = "INSERT INTO notificaciones (id_usuario, tipo, mensaje) 
                                VALUES (:id_usuario, 'nuevo_pedido', :mensaje)";
            
            // Para cada producto, notificar al dueño de la tienda
            $productosAgrupados = [];
            foreach ($productos as $producto) {
                if (!isset($productosAgrupados[$producto['id_tienda']])) {
                    $productosAgrupados[$producto['id_tienda']] = [
                        'id_usuario' => $producto['id_usuario_tienda'], 
                        'total' => 0
                    ];
                }
                $productosAgrupados[$producto['id_tienda']]['total'] += $producto['precio'] * $producto['cantidad'];
            }
            
            foreach ($productosAgrupados as $idTienda => $datos) {
                $mensaje = "Tienes un nuevo pedido #" . $idPedido . " por B/. " . 
                           number_format($datos['total'], 2);
                
                $stmtNotificacion = $conexion->prepare($sqlNotificacion);
                $stmtNotificacion->execute([
                    'id_usuario' => $datos['id_usuario'],
                    'mensaje' => $mensaje
                ]);
            }
            
            // 4. Vaciar carrito del usuario
            $sqlVaciarCarrito = "DELETE FROM carrito_productos 
                                 WHERE id_carrito = (SELECT id_carrito FROM carritos WHERE id_usuario = :id_usuario)";
            
            $stmtVaciarCarrito = $conexion->prepare($sqlVaciarCarrito);
            $stmtVaciarCarrito->execute(['id_usuario' => $usuario['id_usuario']]);
            
            // Confirmar transacción
            $conexion->commit();
            
            // Guardar datos del pedido en la sesión para mostrarlos en la pantalla de finalización
            $_SESSION['pedido_completado'] = [
                'id_pedido' => $idPedido,
                'fecha' => date('Y-m-d H:i:s'),
                'total' => $monto,
                'metodo_pago' => $metodo,
                'transaccion_id' => $resultado['transaccion_id'] ?? null
            ];
            
            // Vaciar carrito de la sesión
            unset($_SESSION['carrito']);
            
            // Redireccionar al paso 4 (finalización)
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const steps = document.querySelectorAll(".step");
                    const stepContents = document.querySelectorAll(".checkout-step-content");
                    
                    // Actualizar la UI para mostrar el paso 4
                    steps.forEach((step) => {
                        const stepNumber = parseInt(step.getAttribute("data-step"));
                        if (stepNumber <= 4) {
                            step.classList.add("completed");
                        }
                    });
                    
                    // Ocultar todos los pasos
                    stepContents.forEach((content) => {
                        content.style.display = "none";
                    });
                    
                    // Mostrar el paso de finalización
                    document.getElementById("step-4").style.display = "block";
                });
            </script>';
            
        } catch (\Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            echo '<div class="alert alert-danger">Error al procesar el pedido: ' . 
                 htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>

<div class="checkout-container">
  <div class="checkout-header">
    <h1 class="checkout-title">Finalizar compra</h1>
    <p class="checkout-subtitle">Complete los siguientes pasos para realizar su pedido</p>
  </div>

  <!-- Proceso de checkout de una sola página con tabs/steps -->
  <div class="checkout-wrapper">
    <div class="checkout-steps">
      <div class="step active" data-step="1">
        <span class="step-number">1</span>
        <span class="step-title">Dirección</span>
      </div>
      <div class="step" data-step="2">
        <span class="step-number">2</span>
        <span class="step-title">Pago</span>
      </div>
      <div class="step" data-step="3">
        <span class="step-number">3</span>
        <span class="step-title">Confirmación</span>
      </div>
      <div class="step" data-step="4">
        <span class="step-number">4</span>
        <span class="step-title">Finalizado</span>
      </div>
    </div>

    <form id="checkout-form" method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
      <input type="hidden" name="finalizar_compra" value="1">
      
      <div id="cart-message-container"></div>
      
      <!-- PASO 1: DIRECCIÓN -->
      <div class="checkout-step-content" id="step-1">
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

      <!-- PASO 2: PAGO -->
      <div class="checkout-step-content" id="step-2" style="display: none;">
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

      <!-- PASO 3: CONFIRMACIÓN -->
      <div class="checkout-step-content" id="step-3" style="display: none;">
        <h2>Confirmar pedido</h2>
        <div class="order-summary">
          <div class="summary-section">
            <h4>Resumen de productos</h4>
            <div class="summary-products">
              <?php foreach ($productos as $producto): ?>
                <div class="summary-product">
                  <span class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                  <span class="product-quantity">x<?php echo $producto['cantidad']; ?></span>
                  <span class="product-price">B/. <?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
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
          <button type="submit" name="finalizar_compra" value="1" class="btn btn-success" id="btn-completar-compra">Completar compra</button>
        </div>
      </div>
      
      <!-- PASO 4: FINALIZACIÓN -->
      <div class="checkout-step-content" id="step-4" style="display: none;">
        <div class="completion-container text-center">
          <div class="completion-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h2>¡Pedido Completado con Éxito!</h2>
          <p class="lead">Gracias por tu compra. Tu pedido ha sido procesado correctamente.</p>
          
          <?php if (isset($_SESSION['pedido_completado'])): ?>
          <div class="order-details">
            <h4>Detalles del Pedido</h4>
            <p><strong>Número de Pedido:</strong> #<?php echo $_SESSION['pedido_completado']['id_pedido']; ?></p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($_SESSION['pedido_completado']['fecha'])); ?></p>
            <p><strong>Total:</strong> B/. <?php echo number_format($_SESSION['pedido_completado']['total'], 2); ?></p>
            <p><strong>Método de Pago:</strong> <?php echo $_SESSION['pedido_completado']['metodo_pago'] === 'tarjeta' ? 'Tarjeta de Crédito/Débito' : 'Yappy'; ?></p>
            <?php if (isset($_SESSION['pedido_completado']['transaccion_id'])): ?>
            <p><strong>ID de Transacción:</strong> <?php echo $_SESSION['pedido_completado']['transaccion_id']; ?></p>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          
          <p class="mt-4">Te hemos enviado un correo electrónico con la confirmación de tu pedido.</p>
          
          <div class="completion-actions mt-4">
            <a href="/artesanoDigital/cliente/pedidos" class="btn btn-outline-primary mr-3">Ver mis pedidos</a>
            <a href="/artesanoDigital" class="btn btn-primary">Seguir comprando</a>
          </div>
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

/* Estilos para el paso de finalización */
.completion-container {
  padding: 2rem 0;
}

.completion-icon {
  font-size: 5rem;
  color: #28a745;
  margin-bottom: 1.5rem;
}

.order-details {
  max-width: 500px;
  margin: 2rem auto;
  padding: 1.5rem;
  border: 1px solid #e9ecef;
  border-radius: 0.5rem;
  background-color: #f8f9fa;
}

.completion-actions .btn {
  min-width: 180px;
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
  // Iniciar en el paso de dirección (ahora paso 1)
  let currentStep = 1;
  
  // Inicializar - mostrar el paso 1 (dirección) directamente
  updateSteps();

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
      const detailsElement = document.getElementById(`${this.value}-details`);
      if (detailsElement) {
        detailsElement.style.display = 'block';
      }
    });
  });

  // Validación antes de envío del formulario
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!validateStep(3)) {
        e.preventDefault();
      }
    });
  }
  
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
      
      // Desplazar hacia arriba para mostrar el mensaje
      cartMessageContainer.scrollIntoView({behavior: 'smooth'});
      
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
    } else {
      // Alternativa: usar alert estándar
      alert(mensaje);
    }
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
        
      case 2:
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
        
      case 3:
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
    // Actualizar resumen - versión corregida
    try {
      console.log("Actualizando resumen, paso actual:", currentStep);
      
      // Siempre actualizar el resumen del carrito para el paso 2
      fetch('/artesanoDigital/controllers/checkout.php?accion=obtener_carrito', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(res => res.json())
      .catch(e => {
        console.log("Error al parsear respuesta:", e);
        return { exitoso: false, mensaje: "Error al obtener datos del carrito" };
      })
      .then(data => {
        if (data && data.exitoso) {
          // Actualizar el resumen en el paso 2
          const summaryItemsStep2 = document.querySelector('#cart-summary-step2');
          if (summaryItemsStep2) {
            let html = '';
            let total = 0;
            
            data.carrito.forEach(item => {
              const subtotal = parseFloat(item.precio) * parseInt(item.cantidad);
              total += subtotal;
              html += `<div class="summary-item d-flex justify-content-between">
                <span class="product-name">${item.nombre} x ${item.cantidad}</span>
                <span class="product-price">B/. ${subtotal.toFixed(2)}</span>
              </div>`;
            });
            
            summaryItemsStep2.innerHTML = html;
            
            const totalElement = document.querySelector('#cart-summary-total-step2');
            if (totalElement) {
              totalElement.textContent = 'B/. ' + total.toFixed(2);
            }
          }
        } else {
          console.log("Error en la respuesta del servidor:", data);
        }
      })
      .catch(error => {
        console.error('Error al actualizar resumen:', error);
      });
    } catch (e) {
      console.error("Error en updateSummary:", e);
    }
    
    // Si estamos en el paso de confirmación, actualizar todos los datos del resumen
    if (currentStep === 4) {
      try {
        const nombreElem = document.getElementById('summary-nombre');
        const telefonoElem = document.getElementById('summary-telefono');
        const direccionElem = document.getElementById('summary-direccion');
        const ciudadElem = document.getElementById('summary-ciudad');
        const paymentMethodElem = document.getElementById('summary-payment-method');
        
        if (nombreElem) nombreElem.textContent = document.getElementById('nombre').value;
        if (telefonoElem) telefonoElem.textContent = document.getElementById('telefono').value;
        if (direccionElem) direccionElem.textContent = document.getElementById('direccion').value;
        if (ciudadElem) ciudadElem.textContent = document.getElementById('ciudad').value;
        
        const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
        if (metodoPago && paymentMethodElem) {
          if (metodoPago.value === 'tarjeta') {
            const numeroTarjeta = document.getElementById('numero_tarjeta');
            if (numeroTarjeta && numeroTarjeta.value) {
              paymentMethodElem.textContent = 'Tarjeta que termina en ' + 
                numeroTarjeta.value.slice(-4);
            } else {
              paymentMethodElem.textContent = 'Tarjeta de crédito/débito';
            }
          } else if (metodoPago.value === 'yappy') {
            const telefonoYappy = document.getElementById('telefono_yappy');
            if (telefonoYappy && telefonoYappy.value) {
              paymentMethodElem.textContent = 'Yappy al número ' + telefonoYappy.value;
            } else {
              paymentMethodElem.textContent = 'Yappy';
            }
          }
        }
      } catch (e) {
        console.error("Error al actualizar resumen en paso 4:", e);
      }
    }
  }
  
  // Asegurar que el botón de completar compra envía el formulario
  document.addEventListener('DOMContentLoaded', function() {
    const btnCompletarCompra = document.getElementById('btn-completar-compra');
    if (btnCompletarCompra) {
      btnCompletarCompra.addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('checkout-form');
        if (form) {
          // Verificar que los términos están aceptados
          const aceptoTerminos = document.getElementById('acepto-terminos');
          if (aceptoTerminos && !aceptoTerminos.checked) {
            mostrarMensaje('Debes aceptar los términos y condiciones para continuar', 'error');
            return;
          }
          
          // Enviar el formulario
          form.submit();
        }
      });
    }
  });
  
  // Código para arreglar problemas de navegación
  window.addEventListener('DOMContentLoaded', function() {
    // Verificar y añadir mensajes de debug
    console.log('DOM cargado completamente');
    
    // Verificar que todos los elementos necesarios existen
    const checkElements = [
      { id: 'step-1', name: 'Paso 1 (Carrito)' },
      { id: 'step-2', name: 'Paso 2 (Dirección)' },
      { id: 'step-3', name: 'Paso 3 (Pago)' },
      { id: 'step-4', name: 'Paso 4 (Confirmación)' },
      { id: 'step-5', name: 'Paso 5 (Finalizado)' },
      { id: 'btn-completar-compra', name: 'Botón Completar Compra' }
    ];
    
    checkElements.forEach(elem => {
      const element = document.getElementById(elem.id);
      if (!element) {
        console.warn(`Elemento no encontrado: ${elem.name} (ID: ${elem.id})`);
      }
    });
    
    // Asegurarse de que los botones de navegación funcionan
    document.querySelectorAll('.next-step').forEach(button => {
      button.addEventListener('click', function() {
        console.log('Botón siguiente clickeado, paso actual:', currentStep);
      });
    });
    
    // Fix para problema de avance de paso
    const fixStepButtons = function() {
      document.querySelectorAll('.next-step').forEach(button => {
        const originalClick = button.onclick;
        button.onclick = null;
        button.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Avanzando al siguiente paso desde:', currentStep);
          if (typeof validateStep === 'function' && validateStep(currentStep)) {
            currentStep++;
            if (typeof updateSteps === 'function') updateSteps();
            if (typeof updateSummary === 'function') updateSummary();
          }
        });
      });
      
      document.querySelectorAll('.prev-step').forEach(button => {
        const originalClick = button.onclick;
        button.onclick = null;
        button.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Retrocediendo al paso anterior desde:', currentStep);
          currentStep--;
          if (typeof updateSteps === 'function') updateSteps();
        });
      });
    };
    
    // Ejecutar después de un breve retraso para asegurarse de que todo está cargado
    setTimeout(fixStepButtons, 500);
  });
</script>

<?php
// Obtener el contenido del buffer y limpiarlo
$contenido = ob_get_clean();

// Incluir la plantilla base
include __DIR__ . '/../layouts/base.php';
