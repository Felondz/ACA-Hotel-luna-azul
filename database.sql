-- SQL Schema for Hotel Luna Azul Web Management System
-- Database name: hotel_luna_azul

CREATE DATABASE IF NOT EXISTS `hotel_luna_azul` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hotel_luna_azul`;

-- Users table (Authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) UNIQUE NOT NULL,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `nombre_completo` VARCHAR(100) NOT NULL,
  `rol` VARCHAR(20) DEFAULT 'Administrador',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Guests table (Huéspedes)
CREATE TABLE IF NOT EXISTS `guests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) UNIQUE NOT NULL,
  `nombre_completo` VARCHAR(100) NOT NULL,
  `tipo_documento` VARCHAR(20) NOT NULL, -- CC, TI, CE, Pasaporte
  `numero_documento` VARCHAR(30) UNIQUE NOT NULL,
  `direccion` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `celular` VARCHAR(20) NOT NULL,
  `edad` INT NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `contacto_emergencia` VARCHAR(100) NOT NULL,
  `parentesco_contacto` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rooms table (Habitaciones)
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero_habitacion` VARCHAR(10) UNIQUE NOT NULL,
  `tipo_habitacion` VARCHAR(50) NOT NULL, -- Sencilla, Doble, Suite, Familiar
  `estado` VARCHAR(20) DEFAULT 'Disponible'
) ENGINE=InnoDB;

-- Reservations table (Reservas)
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) UNIQUE NOT NULL,
  `guest_uuid` VARCHAR(36) NOT NULL,
  `numero_habitacion` VARCHAR(10) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  `fecha_salida` DATE NOT NULL,
  `numero_huespedes` INT NOT NULL,
  `estado` VARCHAR(20) DEFAULT 'Confirmada', -- Confirmada, Cancelada
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`guest_uuid`) REFERENCES `guests`(`uuid`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`numero_habitacion`) REFERENCES `rooms`(`numero_habitacion`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Seed Data

-- Users (admin / password: admin123 - hashed using password_hash in PHP BCrypt)
INSERT INTO `users` (`uuid`, `username`, `password_hash`, `nombre_completo`, `rol`) VALUES
('e8b0dfcb-7c98-4c4f-9e7b-c0ee2d1a3be9', 'admin', '$2y$10$tMhI690C6j8n6i1Y0UaLSuw4cEqGvQzRz9QW0j1e/n0xWcW9tS9g6', 'Administrador Hotel', 'Administrador');

-- Rooms
INSERT INTO `rooms` (`numero_habitacion`, `tipo_habitacion`) VALUES
('101', 'Sencilla'),
('102', 'Sencilla'),
('103', 'Sencilla'),
('201', 'Doble'),
('202', 'Doble'),
('203', 'Doble'),
('301', 'Suite'),
('302', 'Suite'),
('401', 'Familiar');

-- Guests
INSERT INTO `guests` (`uuid`, `nombre_completo`, `tipo_documento`, `numero_documento`, `direccion`, `telefono`, `celular`, `edad`, `email`, `contacto_emergencia`, `parentesco_contacto`) VALUES
('8a1fb6be-fbc7-4d7a-8b1e-128ff4ef21ee', 'Juan Perez', 'CC', '10203040', 'Calle 10 # 5-20', '601234567', '3109876543', 28, 'juan.perez@email.com', 'Maria Gomez', 'Madre'),
('d5d7367b-1cb2-4aee-b247-a89e83cd1405', 'Sofia Rodriguez', 'CC', '98765432', 'Av. Siempre Viva 742', '601765432', '3151234567', 32, 'sofia.rod@email.com', 'Carlos Rodriguez', 'Padre');

-- Reservations
INSERT INTO `reservations` (`uuid`, `guest_uuid`, `numero_habitacion`, `fecha_ingreso`, `fecha_salida`, `numero_huespedes`, `estado`) VALUES
('3f20b335-5136-4c74-9549-06487dfb6a7a', '8a1fb6be-fbc7-4d7a-8b1e-128ff4ef21ee', '101', '2026-07-02', '2026-07-05', 1, 'Confirmada'),
('81b7e3f8-6e54-47a3-ae4a-c56782ff77df', 'd5d7367b-1cb2-4aee-b247-a89e83cd1405', '201', '2026-07-05', '2026-07-10', 2, 'Confirmada');
