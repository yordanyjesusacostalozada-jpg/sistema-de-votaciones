-- =====================================================
-- SISTEMA DE VOTACIÓN ELECTORAL PERÚ 2026
-- Base de Datos para Supabase (PostgreSQL)
-- =====================================================

-- =====================================================
-- TABLA: Ciudadanos (Padrón Electoral)
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_ciudadano (
    id SERIAL PRIMARY KEY,
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
    ha_votado BOOLEAN DEFAULT FALSE,
    fecha_voto TIMESTAMP NULL,
    ip_voto VARCHAR(45) NULL,
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ciudadano_dni ON tbl_ciudadano(dni);
CREATE INDEX IF NOT EXISTS idx_ciudadano_ha_votado ON tbl_ciudadano(ha_votado);

-- =====================================================
-- TABLA: Partidos Políticos
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_partido (
    id SERIAL PRIMARY KEY,
    nombre_corto VARCHAR(50) NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    siglas VARCHAR(20) UNIQUE NOT NULL,
    logo_url VARCHAR(500) NOT NULL,
    color_primario VARCHAR(7) DEFAULT '#333333',
    color_secundario VARCHAR(7) DEFAULT '#666666',
    fundacion_year INTEGER,
    ideologia VARCHAR(100),
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    orden_cedula INTEGER DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_partido_siglas ON tbl_partido(siglas);
CREATE INDEX IF NOT EXISTS idx_partido_orden ON tbl_partido(orden_cedula);

-- =====================================================
-- TABLA: Candidatos Presidenciales
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_candidato (
    id SERIAL PRIMARY KEY,
    partido_id INTEGER NOT NULL,
    tipo_candidato VARCHAR(20) NOT NULL CHECK (tipo_candidato IN ('PRESIDENTE', 'VICEPRESIDENTE_1', 'VICEPRESIDENTE_2')),
    dni CHAR(8) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    foto_url VARCHAR(500) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    profesion VARCHAR(100),
    biografia TEXT,
    plan_gobierno_url VARCHAR(500),
    redes_sociales JSONB,
    hojavida_url VARCHAR(500),
    estado BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_candidato_partido ON tbl_candidato(partido_id);
CREATE INDEX IF NOT EXISTS idx_candidato_tipo ON tbl_candidato(tipo_candidato);

-- =====================================================
-- TABLA: Votos (UN VOTO POR CIUDADANO)
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_voto (
    id SERIAL PRIMARY KEY,
    ciudadano_id INTEGER NOT NULL,
    partido_id INTEGER NOT NULL,
    voto_tipo VARCHAR(10) DEFAULT 'VALIDO' CHECK (voto_tipo IN ('VALIDO', 'BLANCO', 'NULO')),
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    tiempo_votacion_segundos INTEGER DEFAULT 0,
    FOREIGN KEY (ciudadano_id) REFERENCES tbl_ciudadano(id) ON DELETE CASCADE,
    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE,
    UNIQUE (ciudadano_id)
);

CREATE INDEX IF NOT EXISTS idx_voto_partido ON tbl_voto(partido_id);
CREATE INDEX IF NOT EXISTS idx_voto_fecha ON tbl_voto(fecha_voto);

-- =====================================================
-- TABLA: Administradores del Sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_administrador (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    clave VARCHAR(255) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol VARCHAR(20) DEFAULT 'MODERADOR' CHECK (rol IN ('SUPERADMIN', 'MODERADOR', 'OBSERVADOR')),
    estado BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- VISTA: Resultados en Tiempo Real
-- =====================================================
CREATE OR REPLACE VIEW v_resultados_tiempo_real AS
SELECT 
    p.id AS partido_id,
    p.nombre_corto,
    p.siglas,
    p.logo_url,
    p.color_primario,
    CONCAT(c.nombres, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS candidato_nombre,
    c.foto_url AS candidato_foto,
    COUNT(v.id) AS total_votos,
    ROUND((COUNT(v.id)::NUMERIC * 100.0 / NULLIF((SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'VALIDO'), 0)), 2) AS porcentaje,
    p.orden_cedula
FROM tbl_partido p
LEFT JOIN tbl_candidato c ON p.id = c.partido_id AND c.tipo_candidato = 'PRESIDENTE'
LEFT JOIN tbl_voto v ON p.id = v.partido_id AND v.voto_tipo = 'VALIDO'
WHERE p.estado = TRUE AND p.siglas NOT IN ('BLANCO', 'NULO')
GROUP BY p.id, p.nombre_corto, p.siglas, p.logo_url, p.color_primario, c.nombres, c.apellido_paterno, c.apellido_materno, c.foto_url, p.orden_cedula
ORDER BY total_votos DESC, p.orden_cedula ASC;

-- =====================================================
-- VISTA: Estadísticas Generales
-- =====================================================
CREATE OR REPLACE VIEW v_estadisticas_elecciones AS
SELECT 
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = TRUE) AS total_ciudadanos,
    (SELECT COUNT(*) FROM tbl_ciudadano WHERE ha_votado = TRUE) AS total_votantes,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'VALIDO') AS votos_validos,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'BLANCO') AS votos_blancos,
    (SELECT COUNT(*) FROM tbl_voto WHERE voto_tipo = 'NULO') AS votos_nulos,
    (SELECT COUNT(*) FROM tbl_partido WHERE estado = TRUE AND siglas NOT IN ('BLANCO', 'NULO')) AS total_partidos,
    ROUND(((SELECT COUNT(*) FROM tbl_ciudadano WHERE ha_votado = TRUE)::NUMERIC * 100.0 / 
           NULLIF((SELECT COUNT(*) FROM tbl_ciudadano WHERE estado = TRUE), 0)), 2) AS porcentaje_participacion;

-- =====================================================
-- FUNCIÓN: Registrar Voto (Reemplaza PROCEDURE de MySQL)
-- =====================================================
CREATE OR REPLACE FUNCTION sp_registrar_voto(
    p_dni_ciudadano CHAR(8),
    p_partido_id INTEGER,
    p_voto_tipo VARCHAR(10),
    p_ip VARCHAR(45),
    p_tiempo INTEGER
)
RETURNS TABLE(mensaje TEXT, ciudadano_id INTEGER) AS $$
DECLARE
    v_ciudadano_id INTEGER;
    v_ya_voto BOOLEAN;
BEGIN
    -- Buscar ciudadano por DNI
    SELECT id, ha_votado INTO v_ciudadano_id, v_ya_voto
    FROM tbl_ciudadano 
    WHERE dni = p_dni_ciudadano AND estado = TRUE
    LIMIT 1;
    
    -- Validar que el ciudadano existe
    IF v_ciudadano_id IS NULL THEN
        RAISE EXCEPTION 'DNI no encontrado en el padrón electoral';
    END IF;
    
    -- Validar que no haya votado antes
    IF v_ya_voto = TRUE THEN
        RAISE EXCEPTION 'Este ciudadano ya emitió su voto';
    END IF;
    
    -- Registrar el voto
    INSERT INTO tbl_voto (ciudadano_id, partido_id, voto_tipo, ip_address, tiempo_votacion_segundos)
    VALUES (v_ciudadano_id, p_partido_id, p_voto_tipo, p_ip, p_tiempo);
    
    -- Actualizar estado del ciudadano
    UPDATE tbl_ciudadano 
    SET ha_votado = TRUE, 
        fecha_voto = NOW(),
        ip_voto = p_ip
    WHERE id = v_ciudadano_id;
    
    RETURN QUERY SELECT 'Voto registrado exitosamente'::TEXT, v_ciudadano_id;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- FUNCIÓN: Obtener Cédula de Votación
-- =====================================================
CREATE OR REPLACE FUNCTION sp_obtener_cedula()
RETURNS TABLE(
    partido_id INTEGER,
    nombre_corto VARCHAR(50),
    nombre_completo VARCHAR(200),
    siglas VARCHAR(20),
    logo_url VARCHAR(500),
    color_primario VARCHAR(7),
    orden_cedula INTEGER,
    presidente TEXT,
    presidente_foto VARCHAR(500),
    presidente_profesion VARCHAR(100),
    vice1 TEXT,
    vice2 TEXT
) AS $$
BEGIN
    RETURN QUERY
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
    WHERE p.estado = TRUE AND p.siglas NOT IN ('BLANCO', 'NULO')
    ORDER BY p.orden_cedula ASC;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- FUNCIÓN: Validar Ciudadano
-- =====================================================
CREATE OR REPLACE FUNCTION sp_validar_ciudadano(p_dni CHAR(8))
RETURNS TABLE(
    id INTEGER,
    dni CHAR(8),
    nombre_completo TEXT,
    nombres VARCHAR(100),
    apellido_paterno VARCHAR(50),
    apellido_materno VARCHAR(50),
    departamento VARCHAR(50),
    provincia VARCHAR(50),
    distrito VARCHAR(50),
    ha_votado BOOLEAN,
    estado BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        c.id,
        c.dni,
        CONCAT(c.nombres, ' ', c.apellido_paterno, ' ', c.apellido_materno) AS nombre_completo,
        c.nombres,
        c.apellido_paterno,
        c.apellido_materno,
        c.departamento,
        c.provincia,
        c.distrito,
        c.ha_votado,
        c.estado
    FROM tbl_ciudadano c
    WHERE c.dni = p_dni AND c.estado = TRUE
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;

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
-- FIN DEL SCRIPT - COMPATIBLE CON SUPABASE (PostgreSQL)
-- =====================================================
