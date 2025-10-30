-- ============================================
-- BASE DE DATOS: TSJ SPORTS 2025
-- Sistema de Partidos Amistosos y Torneos
-- ============================================

CREATE DATABASE IF NOT EXISTS tsj_sports CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tsj_sports;

-- ============================================
-- TABLA: usuarios
-- Almacena la información de los jugadores
-- ============================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    posicion ENUM('Portero', 'Defensa', 'Mediocampista', 'Delantero') DEFAULT 'Delantero',
    foto_perfil VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: equipos
-- Almacena la información de los equipos
-- ============================================
CREATE TABLE equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_equipo VARCHAR(100) UNIQUE NOT NULL,
    escudo VARCHAR(255),
    id_capitan INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    ciudad VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_capitan) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_nombre (nombre_equipo),
    INDEX idx_capitan (id_capitan)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: equipo_jugadores
-- Relación entre equipos y jugadores (un jugador solo puede estar en un equipo)
-- ============================================
CREATE TABLE equipo_jugadores (
    id_relacion INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_usuario INT UNIQUE NOT NULL, -- UNIQUE asegura que un jugador solo esté en un equipo
    fecha_union TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    es_capitan BOOLEAN DEFAULT FALSE,
    numero_camiseta INT CHECK (numero_camiseta BETWEEN 1 AND 99),
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_equipo (id_equipo),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: partidos
-- Almacena información de partidos amistosos
-- ============================================
CREATE TABLE partidos (
    id_partido INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo_local INT NOT NULL,
    id_equipo_visitante INT,
    fecha_partido DATETIME NOT NULL,
    lugar VARCHAR(200) NOT NULL,
    descripcion TEXT,
    estado ENUM('pendiente', 'aceptado', 'rechazado', 'finalizado', 'cancelado') DEFAULT 'pendiente',
    goles_local INT DEFAULT 0,
    goles_visitante INT DEFAULT 0,
    id_creador INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_equipo_local) REFERENCES equipos(id_equipo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_equipo_visitante) REFERENCES equipos(id_equipo) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (id_equipo_local != id_equipo_visitante),
    INDEX idx_fecha (fecha_partido),
    INDEX idx_estado (estado),
    INDEX idx_equipos (id_equipo_local, id_equipo_visitante)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: torneos
-- Almacena información de los torneos oficiales
-- ============================================
CREATE TABLE torneos (
    id_torneo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_torneo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    costo_inscripcion DECIMAL(10,2) NOT NULL,
    max_equipos INT DEFAULT 20,
    equipos_inscritos INT DEFAULT 0,
    estado ENUM('inscripciones_abiertas', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'inscripciones_abiertas',
    premio_descripcion TEXT,
    reglas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (fecha_fin >= fecha_inicio),
    CHECK (equipos_inscritos <= max_equipos),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: torneo_inscripciones
-- Relación entre equipos y torneos (máximo 20 equipos por torneo)
-- ============================================
CREATE TABLE torneo_inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_torneo INT NOT NULL,
    id_equipo INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pagado BOOLEAN DEFAULT FALSE,
    comprobante_pago VARCHAR(255),
    fecha_pago DATETIME,
    FOREIGN KEY (id_torneo) REFERENCES torneos(id_torneo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_equipo_torneo (id_torneo, id_equipo),
    INDEX idx_torneo (id_torneo),
    INDEX idx_equipo (id_equipo),
    INDEX idx_pagado (pagado)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: partidos_torneo
-- Partidos dentro de los torneos
-- ============================================
CREATE TABLE partidos_torneo (
    id_partido_torneo INT AUTO_INCREMENT PRIMARY KEY,
    id_torneo INT NOT NULL,
    id_equipo_local INT NOT NULL,
    id_equipo_visitante INT NOT NULL,
    fecha_partido DATETIME NOT NULL,
    lugar VARCHAR(200),
    fase ENUM('grupos', 'octavos', 'cuartos', 'semifinal', 'final') DEFAULT 'grupos',
    grupo VARCHAR(10),
    goles_local INT DEFAULT 0,
    goles_visitante INT DEFAULT 0,
    estado ENUM('programado', 'en_curso', 'finalizado', 'suspendido') DEFAULT 'programado',
    FOREIGN KEY (id_torneo) REFERENCES torneos(id_torneo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_equipo_local) REFERENCES equipos(id_equipo) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_equipo_visitante) REFERENCES equipos(id_equipo) ON DELETE RESTRICT ON UPDATE CASCADE,
    CHECK (id_equipo_local != id_equipo_visitante),
    INDEX idx_torneo (id_torneo),
    INDEX idx_fecha (fecha_partido),
    INDEX idx_fase (fase)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: estadisticas_jugador
-- Estadísticas individuales por partido
-- ============================================
CREATE TABLE estadisticas_jugador (
    id_estadistica INT AUTO_INCREMENT PRIMARY KEY,
    id_partido INT,
    id_partido_torneo INT,
    id_usuario INT NOT NULL,
    goles INT DEFAULT 0,
    asistencias INT DEFAULT 0,
    tarjetas_amarillas INT DEFAULT 0,
    tarjetas_rojas INT DEFAULT 0,
    minutos_jugados INT DEFAULT 0,
    FOREIGN KEY (id_partido) REFERENCES partidos(id_partido) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_partido_torneo) REFERENCES partidos_torneo(id_partido_torneo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CHECK ((id_partido IS NOT NULL AND id_partido_torneo IS NULL) OR (id_partido IS NULL AND id_partido_torneo IS NOT NULL)),
    INDEX idx_usuario (id_usuario),
    INDEX idx_partido (id_partido),
    INDEX idx_partido_torneo (id_partido_torneo)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: notificaciones
-- Sistema de notificaciones para usuarios
-- ============================================
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('partido_invitacion', 'partido_aceptado', 'partido_rechazado', 'torneo_inscripcion', 'general') NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_usuario_leida (id_usuario, leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB;

-- ============================================
-- TRIGGERS PARA AUTOMATIZACIÓN
-- ============================================

-- Trigger: Actualizar contador de equipos inscritos en torneo
DELIMITER $$
CREATE TRIGGER after_inscripcion_torneo
AFTER INSERT ON torneo_inscripciones
FOR EACH ROW
BEGIN
    UPDATE torneos 
    SET equipos_inscritos = equipos_inscritos + 1 
    WHERE id_torneo = NEW.id_torneo;
END$$

-- Trigger: Decrementar contador si se elimina inscripción
CREATE TRIGGER after_delete_inscripcion
AFTER DELETE ON torneo_inscripciones
FOR EACH ROW
BEGIN
    UPDATE torneos 
    SET equipos_inscritos = equipos_inscritos - 1 
    WHERE id_torneo = OLD.id_torneo;
END$$

-- Trigger: Validar máximo 8 jugadores por equipo
CREATE TRIGGER before_insert_jugador
BEFORE INSERT ON equipo_jugadores
FOR EACH ROW
BEGIN
    DECLARE jugadores_count INT;
    SELECT COUNT(*) INTO jugadores_count 
    FROM equipo_jugadores 
    WHERE id_equipo = NEW.id_equipo;
    
    IF jugadores_count >= 8 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'El equipo ya tiene el máximo de 8 jugadores';
    END IF;
END$$

DELIMITER ;

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista: Equipos con cantidad de jugadores
CREATE VIEW vista_equipos_jugadores AS
SELECT 
    e.id_equipo,
    e.nombre_equipo,
    e.escudo,
    CONCAT(u.nombre, ' ', u.apellido) AS capitan,
    COUNT(ej.id_usuario) AS total_jugadores,
    e.fecha_creacion,
    e.activo
FROM equipos e
LEFT JOIN equipo_jugadores ej ON e.id_equipo = ej.id_equipo
LEFT JOIN usuarios u ON e.id_capitan = u.id_usuario
GROUP BY e.id_equipo;

-- Vista: Estadísticas generales de jugadores
CREATE VIEW vista_estadisticas_jugadores AS
SELECT 
    u.id_usuario,
    CONCAT(u.nombre, ' ', u.apellido) AS jugador,
    u.posicion,
    e.nombre_equipo AS equipo,
    COALESCE(SUM(est.goles), 0) AS total_goles,
    COALESCE(SUM(est.asistencias), 0) AS total_asistencias,
    COALESCE(SUM(est.tarjetas_amarillas), 0) AS tarjetas_amarillas,
    COALESCE(SUM(est.tarjetas_rojas), 0) AS tarjetas_rojas
FROM usuarios u
LEFT JOIN equipo_jugadores ej ON u.id_usuario = ej.id_usuario
LEFT JOIN equipos e ON ej.id_equipo = e.id_equipo
LEFT JOIN estadisticas_jugador est ON u.id_usuario = est.id_usuario
GROUP BY u.id_usuario;

-- ============================================
-- DATOS DE EJEMPLO (OPCIONAL)
-- ============================================

-- Insertar algunos usuarios de ejemplo
INSERT INTO usuarios (nombre, apellido, email, password, telefono, posicion) VALUES
('Juan', 'Pérez', 'juan@example.com', '$2y$10$example_hash', '3001234567', 'Delantero'),
('Carlos', 'Gómez', 'carlos@example.com', '$2y$10$example_hash', '3007654321', 'Portero'),
('Luis', 'Martínez', 'luis@example.com', '$2y$10$example_hash', '3009876543', 'Mediocampista');

-- ============================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================

-- Estos índices mejorarán el rendimiento de consultas comunes
ALTER TABLE partidos ADD INDEX idx_fecha_estado (fecha_partido, estado);
ALTER TABLE torneos ADD INDEX idx_inscripciones (estado, equipos_inscritos);
ALTER TABLE torneo_inscripciones ADD INDEX idx_torneo_pagado (id_torneo, pagado);