-- =====================================================
-- SISTEMA DE VOTACIÓN ELECTORAL PERÚ 2026
-- Base de Datos Completa
-- =====================================================

DROP DATABASE IF EXISTS db_elecciones_2026;
CREATE DATABASE db_elecciones_2026;
USE db_elecciones_2026;

-- =====================================================
-- TABLA: Ciudadanos (Padrón Electoral)
-- =====================================================
CREATE TABLE tbl_ciudadano (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni CHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    departamento VARCHAR(50) DEFAULT 'LIMA',
    provincia VARCHAR(50) DEFAULT 'LIMA',
    distrito VARCHAR(50) DEFAULT 'LIMA',
    email VARCHAR(100),
    telefono VARCHAR(15),
    foto_url VARCHAR(500),
    ha_votado TINYINT(1) DEFAULT 0,
    fecha_voto DATETIME NULL,
    ip_voto VARCHAR(45) NULL,
    estado TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dni (dni),
    INDEX idx_ha_votado (ha_votado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: Partidos Políticos
-- =====================================================
CREATE TABLE tbl_partido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_corto VARCHAR(50) NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    siglas VARCHAR(20) UNIQUE NOT NULL,
    logo_url VARCHAR(500) NOT NULL,
    color_primario VARCHAR(7) DEFAULT '#333333',
    color_secundario VARCHAR(7) DEFAULT '#666666',
    fundacion_year YEAR,
    ideologia VARCHAR(100),
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1,
    orden_cedula INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_siglas (siglas),
    INDEX idx_orden (orden_cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: Candidatos Presidenciales
-- =====================================================
CREATE TABLE tbl_candidato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partido_id INT NOT NULL,
    tipo_candidato ENUM('PRESIDENTE', 'VICEPRESIDENTE_1', 'VICEPRESIDENTE_2') NOT NULL,
    dni CHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    foto_url VARCHAR(500) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    profesion VARCHAR(100),
    biografia TEXT,
    plan_gobierno_url VARCHAR(500),
    redes_sociales JSON,
    hojavida_url VARCHAR(500),
    estado TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE,
    INDEX idx_partido (partido_id),
    INDEX idx_tipo (tipo_candidato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: Votos (UN VOTO POR CIUDADANO)
-- =====================================================
CREATE TABLE tbl_voto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciudadano_id INT NOT NULL,
    partido_id INT NOT NULL,
    voto_tipo ENUM('VALIDO', 'BLANCO', 'NULO') DEFAULT 'VALIDO',
    fecha_voto DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    tiempo_votacion_segundos INT DEFAULT 0,
    FOREIGN KEY (ciudadano_id) REFERENCES tbl_ciudadano(id) ON DELETE CASCADE,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ciudadano_voto (ciudadano_id),
    INDEX idx_partido_voto (partido_id),
    INDEX idx_fecha_voto (fecha_voto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: Administradores del Sistema
-- =====================================================
CREATE TABLE tbl_administrador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol ENUM('SUPERADMIN', 'MODERADOR', 'OBSERVADOR') DEFAULT 'MODERADOR',
    estado TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISTA: Resultados en Tiempo Real
-- =====================================================
CREATE VIEW v_resultados_tiempo_real AS
SELECT 
    p.id AS partido_id,
    p.nombre_corto,
    p.siglas,
    p.logo_url,
    p.color_primario,
    CONCAT(c.nombres, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS candidato_nombre,
    c.foto_url AS candidato_foto,
    COUNT(v.id) AS total_votos,
    ROUND((COUNT(v.id) * 100.0 / NULLIF((SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'VALIDO'), 0)), 2) AS porcentaje,
    p.orden_cedula
FROM tbl_partido p
LEFT JOIN tbl_candidato c ON p.id = c.partido_id AND c.tipo_candidato = 'PRESIDENTE'
LEFT JOIN tbl_voto v ON p.id = v.partido_id AND v.voto_tipo = 'VALIDO'
WHERE p.estado = 1 AND p.id NOT IN (
    SELECT id FROM tbl_partido WHERE siglas IN ('BLANCO', 'NULO')
)
GROUP BY p.id, p.nombre_corto, p.siglas, p.logo_url, p.color_primario, c.nombres, c.apellido_paterno, c.apellido_materno, c.foto_url, p.orden_cedula
ORDER BY total_votos DESC, p.orden_cedula ASC;

-- =====================================================
-- VISTA: Estadísticas Generales
-- =====================================================
CREATE VIEW v_estadisticas_elecciones AS
SELECT 
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = 1) AS total_ciudadanos,
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE ha_votado = 1) AS total_votantes,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'VALIDO') AS votos_validos,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'BLANCO') AS votos_blancos,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'NULO') AS votos_nulos,
    (SELECT COUNT(*) FROM tbl_partido WHERE estado = 1 AND siglas NOT IN ('BLANCO', 'NULO')) AS total_partidos,
    ROUND(((SELECT COUNT(*) FROM tbl_ciudadano WHERE ha_votado = 1) * 100.0 / 
           NULLIF((SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = 1), 0)), 2) AS porcentaje_participacion;

-- =====================================================
-- PROCEDIMIENTO: Registrar Voto
-- =====================================================
DELIMITER //
CREATE PROCEDURE sp_registrar_voto(
    IN p_dni_ciudadano CHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_partido_id INT,
    IN p_voto_tipo VARCHAR(10),
    IN p_ip VARCHAR(45),
    IN p_tiempo INT
)
BEGIN
    DECLARE v_ciudadano_id INT;
    DECLARE v_ya_voto INT;
    
    -- Buscar ciudadano por DNI
    SELECT id, ha_votado INTO v_ciudadano_id, v_ya_voto
    FROM tbl_ciudadano 
    WHERE dni = p_dni_ciudadano AND estado = 1
    LIMIT 1;
    
    -- Validar que el ciudadano existe
    IF v_ciudadano_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'DNI no encontrado en el padrón electoral';
    END IF;
    
    -- Validar que no haya votado antes
    IF v_ya_voto = 1 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Este ciudadano ya emitió su voto';
    END IF;
    
    -- Registrar el voto
    INSERT INTO tbl_voto (ciudadano_id, partido_id, voto_tipo, ip_address, tiempo_votacion_segundos)
    VALUES (v_ciudadano_id, p_partido_id, p_voto_tipo, p_ip, p_tiempo);
    
    -- Actualizar estado del ciudadano
    UPDATE tbl_ciudadano 
    SET ha_votado = 1, 
        fecha_voto = NOW(),
        ip_voto = p_ip
    WHERE id = v_ciudadano_id;
    
    SELECT 'Voto registrado exitosamente' AS mensaje, v_ciudadano_id AS ciudadano_id;
END //
DELIMITER ;

-- =====================================================
-- PROCEDIMIENTO: Obtener Cédula de Votación
-- =====================================================
DELIMITER //
CREATE PROCEDURE sp_obtener_cedula()
BEGIN
    SELECT 
        p.id AS partido_id,
        p.nombre_corto,
        p.nombre_completo,
        p.siglas,
        p.logo_url,
        p.color_primario,
        p.orden_cedula,
        CONCAT(cp.nombres, ' ', cp.apellido_paterno, ' ', cp.apellido_materno) AS presidente,
        cp.foto_url AS presidente_foto,
        cp.profesion AS presidente_profesion,
        CONCAT(cv1.nombres, ' ', cv1.apellido_paterno) AS vice1,
        CONCAT(cv2.nombres, ' ', cv2.apellido_paterno) AS vice2
    FROM tbl_partido p
    LEFT JOIN tbl_candidato cp ON p.id = cp.partido_id AND cp.tipo_candidato = 'PRESIDENTE'
    LEFT JOIN tbl_candidato cv1 ON p.id = cv1.partido_id AND cv1.tipo_candidato = 'VICEPRESIDENTE_1'
    LEFT JOIN tbl_candidato cv2 ON p.id = cv2.partido_id AND cv2.tipo_candidato = 'VICEPRESIDENTE_2'
    WHERE p.estado = 1 AND p.siglas NOT IN ('BLANCO', 'NULO')
    ORDER BY p.orden_cedula ASC;
END //
DELIMITER ;

-- =====================================================
-- PROCEDIMIENTO: Validar Ciudadano
-- =====================================================
DELIMITER //
CREATE PROCEDURE sp_validar_ciudadano(IN p_dni CHAR(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)
BEGIN
    SELECT 
        id,
        dni,
        CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo,
        nombres,
        apellido_paterno,
        apellido_materno,
        departamento,
        provincia,
        distrito,
        ha_votado,
        estado
    FROM tbl_ciudadano
    WHERE dni = p_dni AND estado = 1
    LIMIT 1;
END //
DELIMITER ;

-- =====================================================
-- DATOS DE EJEMPLO: Partidos Políticos Perú 2026
-- =====================================================
INSERT INTO tbl_partido (nombre_corto, nombre_completo, siglas, logo_url, color_primario, color_secundario, orden_cedula, ideologia) VALUES
('Fuerza Popular', 'Fuerza Popular', 'FP', 'assets/img/partidos/fuerza_popular.png', '#FF6600', '#FF8C00', 1, 'Derecha'),
('Perú Libre', 'Perú Libre', 'PL', 'assets/img/partidos/peru_libre.png', '#CC0000', '#FF0000', 2, 'Izquierda'),
('Renovación Popular', 'Renovación Popular', 'RP', 'assets/img/partidos/renovacion_popular.png', '#00BFFF', '#1E90FF', 3, 'Centro Derecha'),
('Alianza para el Progreso', 'Alianza para el Progreso del Perú', 'APP', 'assets/img/partidos/app.png', '#0066CC', '#0080FF', 4, 'Centro'),
('Acción Popular', 'Acción Popular', 'AP', 'assets/img/partidos/accion_popular.png', '#DC143C', '#FF1493', 5, 'Centro Derecha'),
('Partido Morado', 'Partido Morado', 'PM', 'assets/img/partidos/partido_morado.png', '#8B008B', '#9932CC', 6, 'Centro Izquierda'),
('Avanza País', 'Avanza País - Partido de Integración Social', 'APPIS', 'assets/img/partidos/avanza_pais.png', '#FF1493', '#FF69B4', 7, 'Centro'),
('Juntos por el Perú', 'Juntos por el Perú', 'JPP', 'assets/img/partidos/juntos_peru.png', '#FF4500', '#FF6347', 8, 'Izquierda');

-- Voto en Blanco y Nulo
INSERT INTO tbl_partido (nombre_corto, nombre_completo, siglas, logo_url, color_primario, orden_cedula) VALUES
('VOTO EN BLANCO', 'Voto en Blanco', 'BLANCO', 'assets/img/partidos/voto_blanco.png', '#FFFFFF', 99),
('VOTO NULO', 'Voto Nulo o Viciado', 'NULO', 'assets/img/partidos/voto_nulo.png', '#808080', 100);

-- =====================================================
-- CANDIDATOS PRESIDENCIALES DE EJEMPLO
-- =====================================================
INSERT INTO tbl_candidato (partido_id, tipo_candidato, dni, nombres, apellido_paterno, apellido_materno, foto_url, fecha_nacimiento, profesion, biografia) VALUES
-- Fuerza Popular
(1, 'PRESIDENTE', '10203040', 'KEIKO', 'FUJIMORI', 'HIGUCHI', 'assets/img/candidatos/keiko.jpg', '1975-05-25', 'Administradora', 'Lideresa de Fuerza Popular'),
(1, 'VICEPRESIDENTE_1', '10203041', 'LUIS', 'GALARRETA', 'VELARDE', 'assets/img/candidatos/galarreta.jpg', '1966-03-15', 'Abogado', 'Congresista'),
(1, 'VICEPRESIDENTE_2', '10203042', 'MARTHA', 'CHAVEZ', 'COSSIO', 'assets/img/candidatos/chavez.jpg', '1953-07-10', 'Abogada', 'Congresista'),

-- Perú Libre
(2, 'PRESIDENTE', '20304050', 'PEDRO', 'CASTILLO', 'TERRONES', 'assets/img/candidatos/castillo.jpg', '1969-10-19', 'Profesor', 'Dirigente sindical'),
(2, 'VICEPRESIDENTE_1', '20304051', 'DINA', 'BOLUARTE', 'ZEGARRA', 'assets/img/candidatos/boluarte.jpg', '1962-05-31', 'Abogada', 'Fiscal'),

-- Renovación Popular
(3, 'PRESIDENTE', '30405060', 'RAFAEL', 'LOPEZ', 'ALIAGA', 'assets/img/candidatos/lopez_aliaga.jpg', '1961-05-11', 'Empresario', 'Empresario minero'),
(3, 'VICEPRESIDENTE_1', '30405061', 'ADRIANA', 'TUDELA', 'GUTIÉRREZ', 'assets/img/candidatos/tudela.jpg', '1977-08-22', 'Comunicadora', 'Periodista'),

-- Alianza para el Progreso
(4, 'PRESIDENTE', '40506070', 'CESAR', 'ACUÑA', 'PERALTA', 'assets/img/candidatos/acuna.jpg', '1952-11-11', 'Educador', 'Empresario educativo'),
(4, 'VICEPRESIDENTE_1', '40506071', 'LADY', 'CAMONES', 'SORIANO', 'assets/img/candidatos/camones.jpg', '1979-09-03', 'Abogada', 'Congresista'),

-- Acción Popular
(5, 'PRESIDENTE', '50607080', 'YONHY', 'LESCANO', 'ANCIETA', 'assets/img/candidatos/lescano.jpg', '1950-04-19', 'Periodista', 'Conductor de TV'),
(5, 'VICEPRESIDENTE_1', '50607081', 'MARIA', 'ISABEL', 'LEON', 'assets/img/candidatos/leon.jpg', '1968-12-05', 'Economista', 'Analista política'),

-- Partido Morado
(6, 'PRESIDENTE', '60708090', 'JULIO', 'GUZMAN', 'CACERES', 'assets/img/candidatos/guzman.jpg', '1965-03-26', 'Economista', 'Analista económico'),
(6, 'VICEPRESIDENTE_1', '60708091', 'FLOR', 'PABLO', 'MEDINA', 'assets/img/candidatos/pablo.jpg', '1975-06-14', 'Antropóloga', 'Ex Ministra'),

-- Avanza País
(7, 'PRESIDENTE', '70809010', 'HERNANDO', 'DE SOTO', 'POLAR', 'assets/img/candidatos/desoto.jpg', '1941-06-02', 'Economista', 'Economista internacional'),
(7, 'VICEPRESIDENTE_1', '70809011', 'PATRICIA', 'CHIRINOS', 'VENEGAS', 'assets/img/candidatos/chirinos.jpg', '1962-11-18', 'Empresaria', 'Congresista'),

-- Juntos por el Perú
(8, 'PRESIDENTE', '80910203', 'VERONIKA', 'MENDOZA', 'FRISCH', 'assets/img/candidatos/mendoza.jpg', '1980-12-09', 'Psicóloga', 'Política y activista'),
(8, 'VICEPRESIDENTE_1', '80910204', 'ROCIO', 'SILVA', 'SANTISTEBAN', 'assets/img/candidatos/silva.jpg', '1963-08-28', 'Socióloga', 'Analista política');

-- =====================================================
-- CIUDADANOS DE PRUEBA (Padrón Electoral)
-- =====================================================
INSERT INTO tbl_ciudadano (dni, nombres, apellido_paterno, apellido_materno, fecha_nacimiento, departamento, provincia, distrito) VALUES
('12345678', 'JUAN CARLOS', 'PEREZ', 'GARCIA', '1990-05-15', 'LIMA', 'LIMA', 'MIRAFLORES'),
('87654321', 'MARIA ELENA', 'RODRIGUEZ', 'LOPEZ', '1985-08-22', 'AREQUIPA', 'AREQUIPA', 'CERCADO'),
('11223344', 'PEDRO JOSE', 'GONZALES', 'MARTINEZ', '1995-12-10', 'CUSCO', 'CUSCO', 'WANCHAQ'),
('44332211', 'ANA LUCIA', 'FERNANDEZ', 'TORRES', '1988-03-18', 'PIURA', 'PIURA', 'CASTILLA'),
('55667788', 'CARLOS ALBERTO', 'SANCHEZ', 'DIAZ', '1992-07-25', 'LIMA', 'LIMA', 'SAN ISIDRO'),
('88776655', 'ROSA MARIA', 'VARGAS', 'MENDOZA', '1987-11-30', 'LA LIBERTAD', 'TRUJILLO', 'VICTOR LARCO'),
('99887766', 'JOSE LUIS', 'RAMIREZ', 'CASTRO', '1993-02-14', 'LAMBAYEQUE', 'CHICLAYO', 'CHICLAYO'),
('66778899', 'CARMEN ROSA', 'FLORES', 'SILVA', '1989-09-08', 'JUNIN', 'HUANCAYO', 'EL TAMBO'),
('77889900', 'MIGUEL ANGEL', 'TORRES', 'RUIZ', '1991-04-20', 'ICA', 'ICA', 'ICA'),
('00998877', 'LUCIA PATRICIA', 'CHAVEZ', 'MORALES', '1994-01-12', 'LIMA', 'LIMA', 'SURCO');

-- =====================================================
-- ADMINISTRADOR DE PRUEBA
-- =====================================================
INSERT INTO tbl_administrador (usuario, clave, nombres, email, rol) VALUES
('admin', MD5('admin123'), 'Administrador ONPE', 'admin@onpe.gob.pe', 'SUPERADMIN'),
('observador', MD5('observador123'), 'Observador Electoral', 'observador@onpe.gob.pe', 'OBSERVADOR');

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
