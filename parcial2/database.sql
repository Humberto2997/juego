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

-- Usuarios de prueba
INSERT INTO usuarios (username, password_hash, rol) VALUES
('juan_perez', '$2y$10$9ohIWXvN954Kb/nEwzx5FuCfcML5kqk6N3xVR1Kln7sJK/bbqtN0W', 'aspirante'),
('maria_gomez', '$2y$10$9ohIWXvN954Kb/nEwzx5FuCfcML5kqk6N3xVR1Kln7sJK/bbqtN0W', 'aspirante'),
('carlos_ruiz', '$2y$10$9ohIWXvN954Kb/nEwzx5FuCfcML5kqk6N3xVR1Kln7sJK/bbqtN0W', 'aspirante'),
('ana_torres', '$2y$10$9ohIWXvN954Kb/nEwzx5FuCfcML5kqk6N3xVR1Kln7sJK/bbqtN0W', 'aspirante'),
('luis_mendoza', '$2y$10$9ohIWXvN954Kb/nEwzx5FuCfcML5kqk6N3xVR1Kln7sJK/bbqtN0W', 'aspirante');
-- portante: Contrasena para todos los de prueba Prueba123456789*


-- Datos de los Usuarios
INSERT INTO aspirantes 
(usuario_id, cedula_pasaporte, nombre, apellido, estado_civil, genero, tipo_sangre, fecha_nacimiento, nacionalidad, telefono, residencia, correo_electronico, estado)
VALUES

(1, '8-925-831', 'Juan', 'Perez', 'Soltero', 'masculino', 'O+', '1998-05-12', 'Panameña', '64310110', 'Panamá Oeste', 'juan@gmail.com', 'no revisado'),

(2, '8-777-222', 'Maria', 'Gomez', 'Casada', 'femenino', 'A+', '1995-08-20', 'Panameña', '65002211', 'San Miguelito', 'maria@gmail.com', 'considerado'),

(3, 'E-123-456', 'Carlos', 'Ruiz', 'Soltero', 'masculino', 'B+', '1990-11-15', 'Colombiana', '67778899', 'Ciudad de Panamá', 'carlos@gmail.com', 'no considerado'),

(4, '9-888-777', 'Ana', 'Torres', 'Divorciada', 'femenino', 'AB+', '1987-03-09', 'Panameña', '61234567', 'Chorrera', 'ana@gmail.com', 'considerado'),

(5, 'PE-998877', 'Luis', 'Mendoza', 'Casado', 'masculino', 'O-', '1993-07-01', 'Peruana', '69995544', 'David, Chiriquí', 'luis@gmail.com', 'no revisado');