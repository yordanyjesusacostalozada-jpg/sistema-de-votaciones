<?php
/**
 * AGREGAR COLUMNA nombre_corto A RAILWAY POSTGRESQL
 * Ejecutar una sola vez desde local
 */

// Conectar a Railway PostgreSQL (producción)
$db_host = 'gondola.proxy.rlwy.net';
$db_port = '16689';
$db_user = 'postgres';
$db_password = 'aGYdhNjZOzgKBaboFadrLUuwMJwhMPft';
$db_name = 'railway';

$conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password sslmode=require");

if (!$conexion) {
    die("❌ Error de conexión: " . pg_last_error());
}

echo "=== AGREGAR COLUMNA nombre_corto A RAILWAY ===\n\n";

// Verificar si ya existe
$check = pg_query($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_partido' AND column_name = 'nombre_corto'");

if (pg_num_rows($check) > 0) {
    echo "✓ La columna 'nombre_corto' ya existe en Railway\n";
} else {
    echo "⚠ La columna 'nombre_corto' NO existe. Agregando...\n\n";
    
    // Agregar columna
    $sql1 = "ALTER TABLE tbl_partido ADD COLUMN nombre_corto VARCHAR(50)";
    $result1 = pg_query($conexion, $sql1);
    
    if ($result1) {
        echo "✓ Columna agregada exitosamente\n";
        
        // Actualizar valores existentes
        $sql2 = "UPDATE tbl_partido SET nombre_corto = siglas WHERE nombre_corto IS NULL";
        $result2 = pg_query($conexion, $sql2);
        
        if ($result2) {
            echo "✓ Valores actualizados con siglas\n";
            
            // Hacer NOT NULL
            $sql3 = "ALTER TABLE tbl_partido ALTER COLUMN nombre_corto SET NOT NULL";
            $result3 = pg_query($conexion, $sql3);
            
            if ($result3) {
                echo "✓ Columna configurada como NOT NULL\n";
            } else {
                echo "❌ Error al configurar NOT NULL: " . pg_last_error($conexion) . "\n";
            }
        } else {
            echo "❌ Error al actualizar valores: " . pg_last_error($conexion) . "\n";
        }
    } else {
        echo "❌ Error al agregar columna: " . pg_last_error($conexion) . "\n";
    }
}

echo "\n=== VERIFICACIÓN FINAL ===\n";
$verify = pg_query($conexion, "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'tbl_partido' AND column_name = 'nombre_corto'");

if ($row = pg_fetch_assoc($verify)) {
    echo "Columna: {$row['column_name']}\n";
    echo "Tipo: {$row['data_type']}\n";
    echo "NULL: {$row['is_nullable']}\n";
    echo "\n✅ ¡Listo! Ahora puedes agregar partidos en Render sin problemas.\n";
} else {
    echo "❌ La columna no existe aún\n";
}

pg_close($conexion);
?>
