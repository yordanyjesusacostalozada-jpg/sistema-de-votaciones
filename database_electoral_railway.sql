-- =====================================================
-- SISTEMA DE VOTACIÓN ELECTORAL PERÚ 2026
-- Base de Datos PostgreSQL para Railway
-- =====================================================

-- Eliminar tablas si existen
DROP TABLE IF EXISTS tbl_voto CASCADE;
DROP TABLE IF EXISTS tbl_candidato CASCADE;
DROP TABLE IF EXISTS tbl_partido CASCADE;
DROP TABLE IF EXISTS tbl_ciudadano CASCADE;
DROP TABLE IF EXISTS tbl_administrador CASCADE;

-- Eliminar vistas si existen
DROP VIEW IF EXISTS v_resultados_tiempo_real CASCADE;
DROP VIEW IF EXISTS v_estadisticas_elecciones CASCADE;

-- Eliminar funciones si existen
DROP FUNCTION IF EXISTS sp_validar_ciudadano(VARCHAR) CASCADE;
DROP FUNCTION IF EXISTS sp_obtener_cedula() CASCADE;
DROP FUNCTION IF EXISTS sp_registrar_voto(VARCHAR, INTEGER, VARCHAR, VARCHAR, INTEGER) CASCADE;

-- =====================================================
-- TABLA: Ciudadanos (Padrón Electoral)
-- =====================================================
CREATE TABLE tbl_ciudadano (
    id SERIAL PRIMARY KEY,
    dni VARCHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo CHAR(1) CHECK (sexo IN ('M', 'F')),
    direccion VARCHAR(200),
    departamento VARCHAR(50) NOT NULL,
    provincia VARCHAR(50) NOT NULL,
    distrito VARCHAR(50) NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    ha_votado BOOLEAN DEFAULT FALSE,
    fecha_voto TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_dni ON tbl_ciudadano(dni);
CREATE INDEX idx_estado ON tbl_ciudadano(estado);
CREATE INDEX idx_ha_votado ON tbl_ciudadano(ha_votado);

-- =====================================================
-- TABLA: Partidos Políticos
-- =====================================================
CREATE TABLE tbl_partido (
    id SERIAL PRIMARY KEY,
    siglas VARCHAR(20) UNIQUE NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255),
    color_primario VARCHAR(7),
    color_secundario VARCHAR(7),
    estado BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: Candidatos
-- =====================================================
CREATE TABLE tbl_candidato (
    id SERIAL PRIMARY KEY,
    partido_id INTEGER NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    tipo_candidato VARCHAR(20) CHECK (tipo_candidato IN ('PRESIDENTE', 'VICEPRESIDENTE_1', 'VICEPRESIDENTE_2')),
    dni VARCHAR(8) UNIQUE NOT NULL,
    foto_url VARCHAR(255),
    profesion VARCHAR(100),
    biografia TEXT,
    estado BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE
);

CREATE INDEX idx_partido ON tbl_candidato(partido_id);
CREATE INDEX idx_tipo ON tbl_candidato(tipo_candidato);

-- =====================================================
-- TABLA: Votos
-- =====================================================
CREATE TABLE tbl_voto (
    id SERIAL PRIMARY KEY,
    ciudadano_id INTEGER NOT NULL,
    partido_id INTEGER NOT NULL,
    tipo_voto VARCHAR(20) DEFAULT 'VALIDO' CHECK (tipo_voto IN ('VALIDO', 'BLANCO', 'NULO')),
    ip_address VARCHAR(45),
    tiempo_votacion INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ciudadano_id) REFERENCES tbl_ciudadano(id) ON DELETE CASCADE,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE
);

CREATE INDEX idx_ciudadano_voto ON tbl_voto(ciudadano_id);
CREATE INDEX idx_partido_voto ON tbl_voto(partido_id);
CREATE INDEX idx_fecha_voto ON tbl_voto(created_at);

-- =====================================================
-- TABLA: Administradores
-- =====================================================
CREATE TABLE tbl_administrador (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol VARCHAR(20) DEFAULT 'ADMIN' CHECK (rol IN ('SUPERADMIN', 'ADMIN', 'OBSERVADOR')),
    estado BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_usuario ON tbl_administrador(usuario);
CREATE INDEX idx_rol ON tbl_administrador(rol);

-- =====================================================
-- DATOS INICIALES: Ciudadanos (Padrón Electoral)
-- =====================================================
INSERT INTO tbl_ciudadano (dni, nombres, apellido_paterno, apellido_materno, fecha_nacimiento, sexo, direccion, departamento, provincia, distrito, estado, ha_votado) VALUES
('12345678', 'Juan Carlos', 'Pérez', 'García', '1985-03-15', 'M', 'Av. La Marina 1234', 'Lima', 'Lima', 'San Miguel', TRUE, FALSE),
('87654321', 'María Teresa', 'López', 'Rodríguez', '1990-07-22', 'F', 'Jr. Ucayali 567', 'Lima', 'Lima', 'Cercado de Lima', TRUE, FALSE),
('11223344', 'Pedro Luis', 'Sánchez', 'Torres', '1978-11-30', 'M', 'Calle Las Flores 890', 'Arequipa', 'Arequipa', 'Cayma', TRUE, FALSE),
('44332211', 'Ana Lucía', 'Martínez', 'Flores', '1995-01-08', 'F', 'Av. Arequipa 2345', 'Lima', 'Lima', 'Miraflores', TRUE, FALSE),
('55667788', 'Carlos Alberto', 'Ramírez', 'Vega', '1982-06-18', 'M', 'Av. Brasil 1122', 'Lima', 'Lima', 'Breña', TRUE, FALSE);

-- =====================================================
-- DATOS INICIALES: Partidos Políticos
-- =====================================================
INSERT INTO tbl_partido (siglas, nombre_completo, logo_url, color_primario, color_secundario, estado) VALUES
('FP', 'Fuerza Popular', 'assets/img/partidos/fp.png', '#FF6600', '#000000', TRUE),
('PL', 'Perú Libre', 'assets/img/partidos/pl.png', '#FF0000', '#FFFFFF', TRUE),
('RP', 'Renovación Popular', 'assets/img/partidos/rp.png', '#00BFFF', '#FFFFFF', TRUE),
('AP', 'Acción Popular', 'assets/img/partidos/ap.png', '#DC143C', '#FFFFFF', TRUE),
('APP', 'Alianza Para el Progreso', 'assets/img/partidos/app.png', '#FF1493', '#FFFFFF', TRUE);

-- =====================================================
-- DATOS INICIALES: Candidatos
-- =====================================================
INSERT INTO tbl_candidato (partido_id, nombres, apellido_paterno, apellido_materno, tipo_candidato, dni, foto_url, profesion, biografia, estado) VALUES
-- Fuerza Popular
(1, 'Keiko', 'Fujimori', 'Higuchi', 'PRESIDENTE', '25874136', 'assets/img/candidatos/1.jpg', 'Administradora', 'Lideresa de Fuerza Popular', TRUE),
(1, 'Luis', 'Galarreta', 'Velarde', 'VICEPRESIDENTE_1', '08123456', 'assets/img/candidatos/2.jpg', 'Político', 'Congresista', TRUE),
(1, 'Patricia', 'Juárez', 'Gallegos', 'VICEPRESIDENTE_2', '09234567', 'assets/img/candidatos/3.jpg', 'Abogada', 'Congresista', TRUE),

-- Perú Libre
(2, 'Pedro', 'Castillo', 'Terrones', 'PRESIDENTE', '41779651', 'assets/img/candidatos/4.jpg', 'Profesor', 'Profesor y dirigente sindical', TRUE),
(2, 'Dina', 'Boluarte', 'Zegarra', 'VICEPRESIDENTE_1', '42345678', 'assets/img/candidatos/5.jpg', 'Abogada', 'Abogada y política', TRUE),
(2, 'Aníbal', 'Torres', 'Vásquez', 'VICEPRESIDENTE_2', '07654321', 'assets/img/candidatos/6.jpg', 'Abogado', 'Constitucionalista', TRUE),

-- Renovación Popular
(3, 'Rafael', 'López Aliaga', 'Batanero', 'PRESIDENTE', '07891234', 'assets/img/candidatos/7.jpg', 'Empresario', 'Empresario y político', TRUE),
(3, 'Adriana', 'Tudela', 'Purón', 'VICEPRESIDENTE_1', '43456789', 'assets/img/candidatos/8.jpg', 'Economista', 'Política', TRUE),
(3, 'Jorge', 'Montoya', 'Manrique', 'VICEPRESIDENTE_2', '06789012', 'assets/img/candidatos/9.jpg', 'Militar', 'Almirante en retiro', TRUE),

-- Acción Popular
(4, 'Yonhy', 'Lescano', 'Ancieta', 'PRESIDENTE', '25601234', 'assets/img/candidatos/10.jpg', 'Abogado', 'Abogado y congresista', TRUE),
(4, 'Luis', 'Valdez', 'Farías', 'VICEPRESIDENTE_1', '06543210', 'assets/img/candidatos/11.jpg', 'Empresario', 'Empresario', TRUE),
(4, 'María', 'Antonieta', 'Alva', 'VICEPRESIDENTE_2', '44567890', 'assets/img/candidatos/12.jpg', 'Economista', 'Ex ministra', TRUE),

-- Alianza Para el Progreso
(5, 'César', 'Acuña', 'Peralta', 'PRESIDENTE', '17823456', 'assets/img/candidatos/13.jpg', 'Educador', 'Empresario y político', TRUE),
(5, 'Lady', 'Camones', 'Soriano', 'VICEPRESIDENTE_1', '45678901', 'assets/img/candidatos/14.jpg', 'Política', 'Congresista', TRUE),
(5, 'Eduardo', 'Salhuana', 'Cavides', 'VICEPRESIDENTE_2', '05432109', 'assets/img/candidatos/15.jpg', 'Abogado', 'Congresista', TRUE);

-- =====================================================
-- DATOS INICIALES: Administradores
-- =====================================================
INSERT INTO tbl_administrador (usuario, clave, nombres, email, rol, estado) VALUES
('admin', MD5('admin123'), 'Administrador Principal', 'admin@onpe.gob.pe', 'SUPERADMIN', TRUE),
('observador', MD5('observador123'), 'Observador Electoral', 'observador@onpe.gob.pe', 'OBSERVADOR', TRUE);

-- =====================================================
-- VISTA: Resultados en Tiempo Real
-- =====================================================
CREATE OR REPLACE VIEW v_resultados_tiempo_real AS
SELECT 
    p.id AS partido_id,
    p.siglas,
    p.nombre_completo,
    p.logo_url,
    p.color_primario,
    COUNT(v.id) AS total_votos,
    ROUND(
        (COUNT(v.id)::DECIMAL / NULLIF((SELECT COUNT(*) FROM tbl_voto WHERE tipo_voto = 'VALIDO'), 0)) * 100, 
        2
    ) AS porcentaje,
    (SELECT nombres || ' ' || apellido_paterno || ' ' || apellido_materno 
     FROM tbl_candidato 
     WHERE partido_id = p.id AND tipo_candidato = 'PRESIDENTE' 
     LIMIT 1) AS candidato_presidente
FROM tbl_partido p
LEFT JOIN tbl_voto v ON p.id = v.partido_id AND v.tipo_voto = 'VALIDO'
WHERE p.estado = TRUE
GROUP BY p.id, p.siglas, p.nombre_completo, p.logo_url, p.color_primario
ORDER BY total_votos DESC;

-- =====================================================
-- VISTA: Estadísticas Generales
-- =====================================================
CREATE OR REPLACE VIEW v_estadisticas_elecciones AS
SELECT 
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = TRUE) AS total_ciudadanos,
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE ha_votado = TRUE) AS total_votantes,
    (SELECT COUNT(*) FROM tbl_voto WHERE tipo_voto = 'VALIDO') AS votos_validos,
    (SELECT COUNT(*) FROM tbl_voto WHERE tipo_voto = 'BLANCO') AS votos_blancos,
    (SELECT COUNT(*) FROM tbl_voto WHERE tipo_voto = 'NULO') AS votos_nulos,
    ROUND(
        (SELECT COUNT(*)::DECIMAL FROM tbl_ciudadano WHERE ha_votado = TRUE) / 
        NULLIF((SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = TRUE), 0) * 100, 
        2
    ) AS porcentaje_participacion;

-- =====================================================
-- FUNCIÓN: Validar Ciudadano
-- =====================================================
CREATE OR REPLACE FUNCTION sp_validar_ciudadano(p_dni VARCHAR)
RETURNS TABLE (
    id INTEGER,
    dni VARCHAR,
    nombres VARCHAR,
    apellido_paterno VARCHAR,
    apellido_materno VARCHAR,
    fecha_nacimiento DATE,
    sexo CHAR,
    direccion VARCHAR,
    departamento VARCHAR,
    provincia VARCHAR,
    distrito VARCHAR,
    estado BOOLEAN,
    ha_votado BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        c.id,
        c.dni,
        c.nombres,
        c.apellido_paterno,
        c.apellido_materno,
        c.fecha_nacimiento,
        c.sexo,
        c.direccion,
        c.departamento,
        c.provincia,
        c.distrito,
        c.estado,
        c.ha_votado
    FROM tbl_ciudadano c
    WHERE c.dni = p_dni AND c.estado = TRUE
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- FUNCIÓN: Obtener Cédula de Votación
-- =====================================================
CREATE OR REPLACE FUNCTION sp_obtener_cedula()
RETURNS TABLE (
    partido_id INTEGER,
    partido_siglas VARCHAR,
    partido_nombre VARCHAR,
    partido_logo VARCHAR,
    partido_color VARCHAR,
    candidato_presidente_id INTEGER,
    candidato_presidente_nombres VARCHAR,
    candidato_presidente_foto VARCHAR,
    candidato_presidente_profesion VARCHAR,
    candidato_vp1_nombres VARCHAR,
    candidato_vp2_nombres VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id,
        p.siglas,
        p.nombre_completo,
        p.logo_url,
        p.color_primario,
        pres.id,
        pres.nombres || ' ' || pres.apellido_paterno || ' ' || pres.apellido_materno,
        pres.foto_url,
        pres.profesion,
        vp1.nombres || ' ' || vp1.apellido_paterno,
        vp2.nombres || ' ' || vp2.apellido_paterno
    FROM tbl_partido p
    LEFT JOIN tbl_candidato pres ON p.id = pres.partido_id AND pres.tipo_candidato = 'PRESIDENTE'
    LEFT JOIN tbl_candidato vp1 ON p.id = vp1.partido_id AND vp1.tipo_candidato = 'VICEPRESIDENTE_1'
    LEFT JOIN tbl_candidato vp2 ON p.id = vp2.partido_id AND vp2.tipo_candidato = 'VICEPRESIDENTE_2'
    WHERE p.estado = TRUE
    ORDER BY p.siglas;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- FUNCIÓN: Registrar Voto
-- =====================================================
CREATE OR REPLACE FUNCTION sp_registrar_voto(
    p_dni VARCHAR,
    p_partido_id INTEGER,
    p_tipo_voto VARCHAR DEFAULT 'VALIDO',
    p_ip_address VARCHAR DEFAULT NULL,
    p_tiempo_votacion INTEGER DEFAULT 0
)
RETURNS TABLE (
    success BOOLEAN,
    message VARCHAR
) AS $$
DECLARE
    v_ciudadano_id INTEGER;
    v_ha_votado BOOLEAN;
BEGIN
    -- Buscar ciudadano
    SELECT id, ha_votado INTO v_ciudadano_id, v_ha_votado
    FROM tbl_ciudadano
    WHERE dni = p_dni AND estado = TRUE
    LIMIT 1;
    
    -- Validar si existe
    IF v_ciudadano_id IS NULL THEN
        RETURN QUERY SELECT FALSE, 'Ciudadano no encontrado o inactivo'::VARCHAR;
        RETURN;
    END IF;
    
    -- Validar si ya votó
    IF v_ha_votado = TRUE THEN
        RETURN QUERY SELECT FALSE, 'El ciudadano ya emitió su voto'::VARCHAR;
        RETURN;
    END IF;
    
    -- Registrar voto
    INSERT INTO tbl_voto (ciudadano_id, partido_id, tipo_voto, ip_address, tiempo_votacion)
    VALUES (v_ciudadano_id, p_partido_id, p_tipo_voto, p_ip_address, p_tiempo_votacion);
    
    -- Actualizar ciudadano
    UPDATE tbl_ciudadano
    SET ha_votado = TRUE, fecha_voto = CURRENT_TIMESTAMP
    WHERE id = v_ciudadano_id;
    
    RETURN QUERY SELECT TRUE, 'Voto registrado exitosamente'::VARCHAR;
END;
$$ LANGUAGE plpgsql;
