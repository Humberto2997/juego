-- ============================================
-- Base de datos: rh_aspirantes
-- ============================================

CREATE DATABASE IF NOT EXISTS rh_aspirantes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rh_aspirantes;

-- 1. Tabla de credenciales
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('aspirante', 'rh') NOT NULL DEFAULT 'aspirante',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Expediente de Recursos Humanos (Género restringido a masculino/femenino)
CREATE TABLE IF NOT EXISTS aspirantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    cedula_pasaporte VARCHAR(50) NULL,
    nombre VARCHAR(100) NULL,
    apellido VARCHAR(100) NULL,
    estado_civil VARCHAR(50) NULL,
    genero ENUM('masculino', 'femenino') NULL, -- Se eliminó 'otro'
    tipo_sangre VARCHAR(10) NULL,
    fecha_nacimiento DATE NULL,
    nacionalidad VARCHAR(80) NULL,
    telefono VARCHAR(30) NULL,
    residencia TEXT NULL,
    correo_electronico VARCHAR(150) NULL,
    estado ENUM('no considerado', 'no revisado', 'considerado') NOT NULL DEFAULT 'no revisado',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Control de Fuerza Bruta
CREATE TABLE IF NOT EXISTS intentos_login (
    ip_address VARCHAR(45) NOT NULL,
    intentos INT NOT NULL DEFAULT 1,
    ultimo_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ip_address)
);

-- Cuenta de prueba de Recursos Humanos
INSERT INTO usuarios (username, password_hash, rol) VALUES 
('reclutador_rh', '$2y$10$IY8np5b6jkRwNRuS3bH6wOev8s0/ra1NhFFW3ZjSZOgg2.w2bjDo2', 'rh'),
('Humberto1', '$2y$10$IY8np5b6jkRwNRuS3bH6wOev8s0/ra1NhFFW3ZjSZOgg2.w2bjDo2', 'rh'),
('Jose', '$2y$10$IY8np5b6jkRwNRuS3bH6wOev8s0/ra1NhFFW3ZjSZOgg2.w2bjDo2', 'rh'),
('Miguel', '$2y$10$IY8np5b6jkRwNRuS3bH6wOev8s0/ra1NhFFW3ZjSZOgg2.w2bjDo2', 'rh'),
('Jhezrrel', '$2y$10$IY8np5b6jkRwNRuS3bH6wOev8s0/ra1NhFFW3ZjSZOgg2.w2bjDo2', 'rh');
-- Actualice el hash para la contrase;a. y creo nuestros usuarios si ya lo habian corrido hagan un UPDATE A LA CONTRASENA