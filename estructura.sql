-- Base de datos para Sistema Artesano Digital - Panamá Oeste
-- Creación de base de datos
CREATE DATABASE IF NOT EXISTS artesano_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE artesano_digital;

-- Tabla usuarios (clientes y artesanos)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    contrasena VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'artesano') NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_correo (correo),
    INDEX idx_tipo_usuario (tipo_usuario)
);

-- Tabla tiendas
CREATE TABLE tiendas (
    id_tienda INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_tienda VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_logo VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario)
);

-- Tabla productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_tienda INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255),
    stock INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tienda) REFERENCES tiendas(id_tienda) ON DELETE CASCADE,
    INDEX idx_tienda (id_tienda),
    INDEX idx_activo (activo),
    INDEX idx_precio (precio)
);

-- Tabla carritos
CREATE TABLE carritos (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario)
);

-- Tabla carrito_productos
CREATE TABLE carrito_productos (
    id_carrito_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    UNIQUE KEY unique_carrito_producto (id_carrito, id_producto),
    INDEX idx_carrito (id_carrito),
    INDEX idx_producto (id_producto)
);

-- Tabla pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    estado ENUM('pendiente', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    direccion_envio TEXT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pedido)
);

-- Tabla pedido_productos
CREATE TABLE pedido_productos (
    id_pedido_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto)
);

-- Tabla notificaciones
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('nuevo_pedido', 'estado_actualizado', 'stock_bajo', 'pedido_confirmado') NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_leida (leida),
    INDEX idx_fecha (fecha_creacion)
);

-- Tabla recuperaciones_contrasena
CREATE TABLE recuperaciones_contrasena (
    id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiracion DATETIME NOT NULL,
    usada BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (id_usuario)
);

-- Tabla sesiones para manejo seguro de sesiones
CREATE TABLE sesiones (
    id_sesion VARCHAR(255) PRIMARY KEY,
    id_usuario INT NOT NULL,
    datos TEXT,
    ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_actividad (ultima_actividad)
);

-- Datos de ejemplo
-- Usuarios artesanos
INSERT INTO usuarios (nombre, correo, telefono, direccion, contrasena, tipo_usuario) VALUES
('María González', 'maria.artesana@email.com', '6001-2345', 'La Chorrera, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano'),
('Carlos Mendoza', 'carlos.ceramica@email.com', '6002-3456', 'Arraiján, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano'),
('Ana Rodríguez', 'ana.textiles@email.com', '6003-4567', 'Capira, Panamá Oeste', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artesano');

-- Usuarios clientes
INSERT INTO usuarios (nombre, correo, telefono, direccion, contrasena, tipo_usuario) VALUES
('Pedro Jiménez', 'pedro.cliente@email.com', '6004-5678', 'Ciudad de Panamá', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Sofía Herrera', 'sofia.cliente@email.com', '6005-6789', 'San Miguelito', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

-- Tiendas
INSERT INTO tiendas (id_usuario, nombre_tienda, descripcion, imagen_logo) VALUES
(1, 'Artesanías María', 'Hermosas artesanías tradicionales panameñas hechas a mano', 'logos/maria_logo.jpg'),
(2, 'Cerámica Carlos', 'Cerámica artesanal de alta calidad inspirada en tradiciones locales', 'logos/carlos_logo.jpg'),
(3, 'Textiles Ana', 'Textiles únicos con diseños autóctonos de Panamá', 'logos/ana_logo.jpg');

-- Productos
INSERT INTO productos (id_tienda, nombre, descripcion, precio, imagen, stock) VALUES
(1, 'Mola Tradicional', 'Mola auténtica hecha por artesanas gunas con diseños tradicionales', 45.00, 'productos/mola1.jpg', 15),
(1, 'Sombrero Pintao', 'Sombrero pintao tejido a mano con fibras naturales', 35.00, 'productos/sombrero1.jpg', 8),
(1, 'Pulsera de Tagua', 'Pulsera elaborada con semillas de tagua, diseño único', 12.00, 'productos/pulsera1.jpg', 25),
(2, 'Vasija de Barro', 'Vasija decorativa de barro cocido con motivos precolombinos', 28.00, 'productos/vasija1.jpg', 12),
(2, 'Plato Decorativo', 'Plato de cerámica pintado a mano con diseños florales', 22.00, 'productos/plato1.jpg', 18),
(3, 'Huipil Bordado', 'Huipil tradicional con bordados coloridos hechos a mano', 65.00, 'productos/huipil1.jpg', 6),
(3, 'Bolso Tejido', 'Bolso artesanal tejido con fibras naturales y diseños étnicos', 38.00, 'productos/bolso1.jpg', 10);

-- Carritos de ejemplo
INSERT INTO carritos (id_usuario) VALUES (4), (5);

-- Productos en carrito
INSERT INTO carrito_productos (id_carrito, id_producto, cantidad) VALUES
(1, 1, 2),
(1, 3, 1),
(2, 2, 1),
(2, 4, 1);

-- Pedidos de ejemplo
INSERT INTO pedidos (id_usuario, estado, metodo_pago, total, direccion_envio) VALUES
(4, 'enviado', 'tarjeta_credito', 102.00, 'Calle 50, Ciudad de Panamá'),
(5, 'pendiente', 'yappy', 57.00, 'Vía España, San Miguelito');

-- Productos en pedidos
INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario) VALUES
(1, 1, 2, 45.00),
(1, 3, 1, 12.00),
(2, 2, 1, 35.00),
(2, 4, 1, 22.00);

-- Notificaciones de ejemplo
INSERT INTO notificaciones (id_usuario, tipo, mensaje) VALUES
(1, 'nuevo_pedido', 'Tienes un nuevo pedido #1 por $102.00'),
(2, 'nuevo_pedido', 'Tienes un nuevo pedido #2 por $57.00'),
(4, 'estado_actualizado', 'Tu pedido #1 ha sido enviado'),
(1, 'stock_bajo', 'El producto "Sombrero Pintao" tiene stock bajo (8 unidades)');
