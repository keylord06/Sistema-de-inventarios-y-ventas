SET NAMES utf8mb4;

USE inventario_ventas;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo BOOLEAN DEFAULT 1,
    tkRef VARCHAR(255),
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre_completo VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    categoria_id INT NOT NULL,
    proveedor_id INT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    activo BOOLEAN DEFAULT 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);

CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    usuario_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    estado ENUM('Completada', 'Anulada') DEFAULT 'Completada',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

DELIMITER //

CREATE PROCEDURE sp_crear_venta(
    IN p_cliente_id INT,
    IN p_usuario_id INT,
    OUT p_venta_id INT
)
BEGIN
    INSERT INTO ventas (cliente_id, usuario_id) VALUES (p_cliente_id, p_usuario_id);
    SET p_venta_id = LAST_INSERT_ID();
END //

CREATE PROCEDURE sp_agregar_producto_venta(
    IN p_venta_id INT,
    IN p_producto_id INT,
    IN p_cantidad INT,
    OUT p_resultado VARCHAR(100)
)
BEGIN
    DECLARE v_stock INT;
    DECLARE v_precio DECIMAL(10,2);

    SELECT stock, precio INTO v_stock, v_precio
    FROM productos
    WHERE id = p_producto_id AND activo = 1;

    IF v_stock IS NULL THEN
        SET p_resultado = 'Error: El producto no existe.';
    ELSEIF p_cantidad <= 0 THEN
        SET p_resultado = 'Error: La cantidad debe ser mayor a 0.';
    ELSEIF v_stock < p_cantidad THEN
        SET p_resultado = 'Error: Stock insuficiente.';
    ELSE
        INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal)
        VALUES (p_venta_id, p_producto_id, p_cantidad, v_precio, v_precio * p_cantidad);

        UPDATE productos SET stock = stock - p_cantidad WHERE id = p_producto_id;
        UPDATE ventas SET total = total + (v_precio * p_cantidad) WHERE id = p_venta_id;

        SET p_resultado = 'Producto agregado exitosamente.';
    END IF;
END //

CREATE PROCEDURE sp_obtener_ventas_cliente(
    IN p_cliente_id INT
)
BEGIN
    SELECT v.id, v.fecha, v.total, v.estado, u.username AS vendedor
    FROM ventas v
    INNER JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.cliente_id = p_cliente_id
    ORDER BY v.fecha DESC;
END //

CREATE PROCEDURE sp_productos_stock_bajo()
BEGIN
    SELECT p.id, p.codigo, p.nombre, c.nombre AS categoria, p.stock, p.stock_minimo
    FROM productos p
    INNER JOIN categorias c ON p.categoria_id = c.id
    WHERE p.stock <= p.stock_minimo AND p.activo = 1
    ORDER BY p.stock;
END //

DELIMITER ;

INSERT INTO roles (nombre) VALUES ('Admin'), ('Vendedor'), ('Bodeguero');

-- Contraseña: Admin123*
INSERT INTO usuarios (username, password, rol_id) VALUES
    ('admin', '$2a$10$yBt6ZHE2CtDyshp1qHlTzevE1VJSmvfWDjj7qvA/tIQ0BrztLaHMW', 1);

INSERT INTO categorias (nombre) VALUES
    ('Electrónica'),
    ('Abarrotes'),
    ('Bebidas'),
    ('Limpieza');

INSERT INTO proveedores (nombre, telefono, email) VALUES
    ('TecnoDistribuidora', '2200-1001', 'ventas@tecno.com'),
    ('Alimentos del Valle', '2200-1002', 'pedidos@delvalle.com');

INSERT INTO productos (codigo, nombre, categoria_id, proveedor_id, precio, stock) VALUES
    ('7441000000011', 'Mouse inalámbrico', 1, 1, 7500, 20),
    ('7441000000028', 'Audífonos Bluetooth', 1, 1, 19900, 10),
    ('7441000000035', 'Arroz 1.8kg', 2, 2, 2650, 50),
    ('7441000000042', 'Café molido 500g', 2, 2, 4200, 30),
    ('7441000000059', 'Agua 600ml', 3, 2, 650, 100),
    ('7441000000066', 'Cloro 1L', 4, 2, 1100, 4);

INSERT INTO clientes (cedula, nombre_completo, telefono) VALUES
    ('1-1111-1111', 'Luis Fernández', '8888-0001'),
    ('2-2222-2222', 'Sofía Herrera', '8888-0002');
