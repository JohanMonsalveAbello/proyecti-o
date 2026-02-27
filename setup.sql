-- ════════════════════════════════════════════════
--  SurtiPaez — Script de base de datos
--  Ejecuta esto una sola vez en phpMyAdmin o MySQL
-- ════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS surtipaez
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE surtipaez;

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150) NOT NULL,
    celular         VARCHAR(20)  NOT NULL,
    ciudad          VARCHAR(100) DEFAULT '',
    productos       TEXT         NOT NULL COMMENT 'JSON: [{nombre, cantidad, precio}]',
    estado          ENUM('solicitado','pagado','no_pagado') DEFAULT 'solicitado',
    total           DECIMAL(12,2) DEFAULT 0.00,
    fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de usuarios admin
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario         VARCHAR(60)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    nombre          VARCHAR(100) DEFAULT '',
    creado          DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuario administrador por defecto
-- Usuario: admin  |  Contraseña: surtipaez2025
INSERT IGNORE INTO usuarios (usuario, password_hash, nombre)
VALUES (
    'admin',
    '$2y$10$wkKD9JaFAmrVORy.m1.1dOKrF/Wv2R3Q6Kb7E8b0h2ZNEPqE8JnXq',
    'Administrador SurtiPaez'
);
-- Nota: el hash anterior es para "surtipaez2025"
-- Si no funciona, puedes generar un nuevo hash así:
-- <?php echo password_hash('surtipaez2025', PASSWORD_DEFAULT); ?>

-- Datos de prueba (opcional)
INSERT IGNORE INTO pedidos (nombre, celular, ciudad, productos, estado, total) VALUES
(
    'Juan Pérez',
    '3001234567',
    'Bogotá',
    '[{"nombre":"Papa pastusa","cantidad":5,"precio":0},{"nombre":"Yuca","cantidad":3,"precio":0}]',
    'solicitado',
    0
),
(
    'María López',
    '3109876543',
    'Medellín',
    '[{"nombre":"Lichi fresco","cantidad":2,"precio":0},{"nombre":"Plátano hartón","cantidad":10,"precio":0}]',
    'solicitado',
    0
);
