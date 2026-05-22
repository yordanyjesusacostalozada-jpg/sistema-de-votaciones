-- =====================================================
-- AGREGAR CAMPO nombre_corto A tbl_partido
-- Ejecutar en Supabase SQL Editor
-- =====================================================

-- Verificar si la columna ya existe
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_partido' 
        AND column_name = 'nombre_corto'
    ) THEN
        -- Agregar la columna
        ALTER TABLE tbl_partido 
        ADD COLUMN nombre_corto VARCHAR(50);
        
        -- Actualizar valores existentes con las siglas
        UPDATE tbl_partido SET nombre_corto = siglas WHERE nombre_corto IS NULL;
        
        -- Hacer la columna NOT NULL después de llenarla
        ALTER TABLE tbl_partido 
        ALTER COLUMN nombre_corto SET NOT NULL;
        
        RAISE NOTICE 'Columna nombre_corto agregada exitosamente';
    ELSE
        RAISE NOTICE 'La columna nombre_corto ya existe';
    END IF;
END $$;

-- Verificar que se agregó correctamente
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'tbl_partido' 
AND column_name = 'nombre_corto';
