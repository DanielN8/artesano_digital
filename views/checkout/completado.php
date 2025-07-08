<?php
// Vista: Pedido completado
ob_start();
?>
<div class="contenedor">
  <div class="checkout-card text-center">
    <h2 class="checkout-title text-success">¡Pedido completado!</h2>
    <p class="mb-6">Gracias por tu compra. Pronto recibirás un correo con los detalles de tu pedido.</p>
    <a href="/artesanoDigital" class="btn btn-primary btn-block">Volver al inicio</a>
  </div>
</div>
<style>
  .checkout-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    padding: 2.5rem 2rem 2rem 2rem;
    max-width: 420px;
    width: 100%;
    margin: 2rem auto;
  }
  .text-success {
    color: #28a745;
  }
</style>
<?php
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
