<?php
/**
 * MIGRACIÓN: Permitir votos en blanco (partido_id NULL)
 * Este script modifica la tabla tbl_voto para permitir NULL en partido_id
 */

include 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Migración - Voto en Blanco</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
.container { max-width: 800px; }
.card { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
.resultado { font-family: 'Courier New', monospace; background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<div class='card'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h3><i class='fas fa-database'></i> Migración de Base de Datos - Voto en Blanco</h3>";
echo "</div>";
echo "<div class='card-body'>";

if ($is_production) {
    echo "<div class='alert alert-warning'>";
    echo "<h5>⚠️ Entorno de Producción Detectado (PostgreSQL)</h5>";
    echo "<p>Esta migración es solo para MySQL local. PostgreSQL en Railway debe migrarse manualmente.</p>";
    echo "</div>";
    
    echo "<div class='resultado'>";
    echo "<strong>Script SQL para PostgreSQL (Railway):</strong><br><br>";
    echo "<code>";
    echo "-- Ejecutar en Railway PostgreSQL Console:<br>";
    echo "ALTER TABLE tbl_voto ALTER COLUMN partido_id DROP NOT NULL;<br>";
    echo "-- Verificar:<br>";
    echo "\\d tbl_voto;";
    echo "</code>";
    echo "</div>";
    
    pg_close($conexion);
    
} else {
    // MySQL Local
    echo "<div class='alert alert-info'>";
    echo "<h5>🔧 Ejecutando Migración en MySQL Local...</h5>";
    echo "</div>";
    
    $errores = [];
    $exitos = [];
    
    // Paso 1: Verificar estado actual
    echo "<div class='resultado'>";
    echo "<strong>📋 Paso 1: Verificar estructura actual</strong><br>";
    $describe = mysqli_query($conexion, "DESCRIBE tbl_voto");
    if ($describe) {
        while ($row = mysqli_fetch_assoc($describe)) {
            if ($row['Field'] == 'partido_id') {
                echo "partido_id: Type={$row['Type']}, Null={$row['Null']}, Key={$row['Key']}<br>";
                if ($row['Null'] == 'YES') {
                    echo "<span class='text-success'>✅ La columna YA permite NULL. No se requiere migración.</span><br>";
                    mysqli_close($conexion);
                    echo "</div></div></div></div></body></html>";
                    exit;
                }
            }
        }
        $exitos[] = "Estructura actual verificada";
    } else {
        $errores[] = "Error al verificar estructura: " . mysqli_error($conexion);
    }
    echo "</div>";
    
    // Paso 2: Obtener nombre de la clave foránea
    echo "<div class='resultado'>";
    echo "<strong>🔍 Paso 2: Buscar clave foránea de partido_id</strong><br>";
    $fk_query = "SELECT CONSTRAINT_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = 'db_elecciones_2026' 
                 AND TABLE_NAME = 'tbl_voto' 
                 AND COLUMN_NAME = 'partido_id' 
                 AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $fk_result = mysqli_query($conexion, $fk_query);
    $fk_name = null;
    
    if ($fk_result && mysqli_num_rows($fk_result) > 0) {
        $fk_row = mysqli_fetch_assoc($fk_result);
        $fk_name = $fk_row['CONSTRAINT_NAME'];
        echo "Clave foránea encontrada: <code>{$fk_name}</code><br>";
        $exitos[] = "Clave foránea identificada";
    } else {
        echo "<span class='text-warning'>⚠️ No se encontró clave foránea (puede que ya esté eliminada)</span><br>";
    }
    echo "</div>";
    
    // Paso 3: Eliminar clave foránea si existe
    if ($fk_name) {
        echo "<div class='resultado'>";
        echo "<strong>🗑️ Paso 3: Eliminar clave foránea</strong><br>";
        $drop_fk = "ALTER TABLE tbl_voto DROP FOREIGN KEY `{$fk_name}`";
        
        if (mysqli_query($conexion, $drop_fk)) {
            echo "<span class='text-success'>✅ Clave foránea eliminada exitosamente</span><br>";
            $exitos[] = "Clave foránea eliminada";
        } else {
            $error_msg = mysqli_error($conexion);
            echo "<span class='text-danger'>❌ Error: {$error_msg}</span><br>";
            $errores[] = "Error al eliminar clave foránea: {$error_msg}";
        }
        echo "</div>";
    }
    
    // Paso 4: Modificar columna para permitir NULL
    echo "<div class='resultado'>";
    echo "<strong>✏️ Paso 4: Modificar columna partido_id para permitir NULL</strong><br>";
    $modify_query = "ALTER TABLE tbl_voto MODIFY COLUMN partido_id INT NULL";
    
    if (mysqli_query($conexion, $modify_query)) {
        echo "<span class='text-success'>✅ Columna modificada exitosamente - Ahora permite NULL</span><br>";
        $exitos[] = "Columna partido_id modificada";
    } else {
        $error_msg = mysqli_error($conexion);
        echo "<span class='text-danger'>❌ Error: {$error_msg}</span><br>";
        $errores[] = "Error al modificar columna: {$error_msg}";
    }
    echo "</div>";
    
    // Paso 5: Recrear clave foránea
    echo "<div class='resultado'>";
    echo "<strong>🔗 Paso 5: Recrear clave foránea (permitiendo NULL)</strong><br>";
    $recreate_fk = "ALTER TABLE tbl_voto 
                    ADD CONSTRAINT fk_voto_partido 
                    FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE";
    
    if (mysqli_query($conexion, $recreate_fk)) {
        echo "<span class='text-success'>✅ Clave foránea recreada exitosamente</span><br>";
        $exitos[] = "Clave foránea recreada";
    } else {
        $error_msg = mysqli_error($conexion);
        echo "<span class='text-warning'>⚠️ Advertencia: {$error_msg}</span><br>";
        // No es crítico si ya existe
    }
    echo "</div>";
    
    // Paso 6: Verificar resultado final
    echo "<div class='resultado'>";
    echo "<strong>✔️ Paso 6: Verificar resultado</strong><br>";
    $describe_final = mysqli_query($conexion, "DESCRIBE tbl_voto");
    if ($describe_final) {
        while ($row = mysqli_fetch_assoc($describe_final)) {
            if ($row['Field'] == 'partido_id') {
                echo "partido_id: Type={$row['Type']}, <strong>Null={$row['Null']}</strong>, Key={$row['Key']}<br>";
                if ($row['Null'] == 'YES') {
                    echo "<span class='text-success'><strong>✅ ÉXITO: La columna ahora permite NULL</strong></span><br>";
                    $exitos[] = "Verificación exitosa";
                } else {
                    echo "<span class='text-danger'><strong>❌ ERROR: La columna aún no permite NULL</strong></span><br>";
                    $errores[] = "Verificación fallida";
                }
            }
        }
    }
    echo "</div>";
    
    mysqli_close($conexion);
    
    // Resumen
    echo "<div class='mt-4'>";
    if (empty($errores)) {
        echo "<div class='alert alert-success'>";
        echo "<h4>🎉 Migración Completada Exitosamente</h4>";
        echo "<ul class='mb-0'>";
        foreach ($exitos as $exito) {
            echo "<li>{$exito}</li>";
        }
        echo "</ul>";
        echo "<hr>";
        echo "<p class='mb-0'><strong>Ahora puedes votar en blanco sin problemas.</strong></p>";
        echo "<p class='mb-0'>La columna <code>partido_id</code> ahora acepta valores NULL.</p>";
        echo "</div>";
        
        echo "<div class='text-center mt-3'>";
        echo "<a href='test_voto_blanco.php' class='btn btn-primary btn-lg'>🧪 Probar Voto en Blanco</a> ";
        echo "<a href='index.php' class='btn btn-success btn-lg'>🗳️ Ir a Votar</a>";
        echo "</div>";
        
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<h4>❌ Migración con Errores</h4>";
        echo "<ul class='mb-0'>";
        foreach ($errores as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        if (!empty($exitos)) {
            echo "<div class='alert alert-warning'>";
            echo "<h5>Pasos completados:</h5>";
            echo "<ul class='mb-0'>";
            foreach ($exitos as $exito) {
                echo "<li>{$exito}</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    echo "</div>";
}

echo "</div></div></div>";
echo "</body></html>";
?>
