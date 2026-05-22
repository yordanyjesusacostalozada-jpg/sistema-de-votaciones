-- =====================================================
-- MIGRACIÓN: Permitir partido_id NULL para votos en blanco
-- =====================================================

-- Paso 1: Eliminar la clave foránea existente
ALTER TABLE tbl_voto DROP FOREIGN KEY tbl_voto_ibfk_2;

-- Paso 2: Modificar la columna partido_id para permitir NULL
ALTER TABLE tbl_voto MODIFY COLUMN partido_id INT NULL;

-- Paso 3: Recrear la clave foránea (opcional para NULL)
ALTER TABLE tbl_voto 
ADD CONSTRAINT fk_voto_partido 
FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE;

-- Verificar el cambio
DESCRIBE tbl_voto;

-- Mensaje de éxito
SELECT 'Migración completada: partido_id ahora permite NULL para votos en blanco' AS resultado;
