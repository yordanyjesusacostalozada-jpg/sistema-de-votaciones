-- =====================================================
-- MIGRACIÓN COMPLETA PARA SUPABASE (PostgreSQL)
-- Sistema Electoral Perú 2026
-- Fecha: 4 de Diciembre 2025
-- =====================================================

-- =====================================================
-- 1. MIGRACIÓN: Permitir votos en blanco (partido_id NULL)
-- =====================================================

-- Paso 1: Eliminar constraint de clave foránea temporalmente
ALTER TABLE tbl_voto DROP CONSTRAINT IF EXISTS fk_voto_partido;

-- Paso 2: Modificar columna partido_id para permitir NULL
ALTER TABLE tbl_voto ALTER COLUMN partido_id DROP NOT NULL;

-- Paso 3: Recrear constraint de clave foránea
ALTER TABLE tbl_voto 
ADD CONSTRAINT fk_voto_partido 
FOREIGN KEY (partido_id) 
REFERENCES tbl_partido(id) 
ON DELETE CASCADE;

-- =====================================================
-- 2. MIGRACIÓN: Agregar campo nombre_corto a tbl_partido
-- =====================================================

-- Verificar si la columna ya existe antes de agregarla
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_partido' 
        AND column_name = 'nombre_corto'
    ) THEN
        ALTER TABLE tbl_partido 
        ADD COLUMN nombre_corto VARCHAR(50) NOT NULL DEFAULT 'PARTIDO';
    END IF;
END $$;

-- Actualizar nombre_corto con las siglas existentes
UPDATE tbl_partido SET nombre_corto = siglas WHERE nombre_corto = 'PARTIDO';

-- =====================================================
-- 3. MIGRACIÓN: Agregar campos a tbl_candidato
-- =====================================================

-- Campo DNI
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_candidato' 
        AND column_name = 'dni'
    ) THEN
        ALTER TABLE tbl_candidato 
        ADD COLUMN dni VARCHAR(8) NOT NULL DEFAULT '00000000';
    END IF;
END $$;

-- Campo fecha_nacimiento
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_candidato' 
        AND column_name = 'fecha_nacimiento'
    ) THEN
        ALTER TABLE tbl_candidato 
        ADD COLUMN fecha_nacimiento DATE NOT NULL DEFAULT '1980-01-01';
    END IF;
END $$;

-- Campo tipo_candidato (cargo)
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_candidato' 
        AND column_name = 'tipo_candidato'
    ) THEN
        ALTER TABLE tbl_candidato 
        ADD COLUMN tipo_candidato VARCHAR(30) NOT NULL DEFAULT 'PRESIDENTE';
    END IF;
END $$;

-- =====================================================
-- 4. VERIFICACIÓN: Comprobar cambios aplicados
-- =====================================================

-- Verificar estructura de tbl_voto
SELECT 
    column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM information_schema.columns 
WHERE table_name = 'tbl_voto' 
AND column_name = 'partido_id';

-- Verificar estructura de tbl_partido
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'tbl_partido' 
AND column_name = 'nombre_corto';

-- Verificar estructura de tbl_candidato
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'tbl_candidato' 
AND column_name IN ('dni', 'fecha_nacimiento', 'tipo_candidato')
ORDER BY column_name;

-- =====================================================
-- 5. DATOS DE EJEMPLO (Opcional - Comentado)
-- =====================================================

/*
-- Actualizar DNI de candidatos existentes (ejemplo)
UPDATE tbl_candidato SET dni = '12345678' WHERE id = 1;
UPDATE tbl_candidato SET dni = '23456789' WHERE id = 2;

-- Actualizar fechas de nacimiento (ejemplo)
UPDATE tbl_candidato SET fecha_nacimiento = '1975-05-19' WHERE id = 1;
UPDATE tbl_candidato SET fecha_nacimiento = '1978-08-15' WHERE id = 2;

-- Actualizar tipo de candidato (ejemplo)
UPDATE tbl_candidato SET tipo_candidato = 'PRESIDENTE' WHERE id = 1;
UPDATE tbl_candidato SET tipo_candidato = 'VICEPRESIDENTE_1' WHERE id = 2;
*/

-- =====================================================
-- FIN DE LA MIGRACIÓN
-- =====================================================

-- Para ejecutar en Supabase:
-- 1. Ve al Dashboard de Supabase
-- 2. Selecciona tu proyecto
-- 3. Ve a "SQL Editor"
-- 4. Copia y pega este archivo completo
-- 5. Haz clic en "Run"
-- 6. Verifica que todas las consultas se ejecutaron correctamente
