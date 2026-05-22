<?php
/**
 * PERMITIR partido_id NULL EN tbl_voto PARA VOTOS EN BLANCO
 * Railway PostgreSQL
 */

$db_host = 'gondola.proxy.rlwy.net';
$db_port = '16689';
$db_user = 'postgres';
$db_password = 'aGYdhNjZOzgKBaboFadrLUuwMJwhMPft';
$db_name = 'railway';

$conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password sslmode=require");

if (!$conexion) {
    die("❌ Error de conexión: " . pg_last_error());
}

echo "=== PERMITIR VOTOS EN BLANCO EN RAILWAY ===\n\n";

// Paso 1: Eliminar constraint de clave foránea
echo "1. Eliminando constraint de clave foránea...\n";
$drop_fk = "ALTER TABLE tbl_voto DROP CONSTRAINT IF EXISTS tbl_voto_partido_id_fkey";
if (pg_query($conexion, $drop_fk)) {
    echo "   ✓ Constraint eliminado\n";
} else {
    echo "   ⚠ " . pg_last_error($conexion) . "\n";
}

// Paso 2: Permitir NULL en partido_id
echo "\n2. Permitiendo NULL en partido_id...\n";
$alter_null = "ALTER TABLE tbl_voto ALTER COLUMN partido_id DROP NOT NULL";
if (pg_query($conexion, $alter_null)) {
    echo "   ✓ partido_id ahora permite NULL\n";
} else {
    echo "   ❌ Error: " . pg_last_error($conexion) . "\n";
}

// Paso 3: Recrear constraint de clave foránea
echo "\n3. Recreando constraint de clave foránea...\n";
$add_fk = "ALTER TABLE tbl_voto ADD CONSTRAINT tbl_voto_partido_id_fkey FOREIGN KEY (partido_id) REFERENCES tbl_partido(id) ON DELETE CASCADE";
if (pg_query($conexion, $add_fk)) {
    echo "   ✓ Constraint recreado\n";
} else {
    echo "   ❌ Error: " . pg_last_error($conexion) . "\n";
}

// Verificación
echo "\n=== VERIFICACIÓN ===\n";
$verify = pg_query($conexion, "SELECT column_name, is_nullable FROM information_schema.columns WHERE table_name = 'tbl_voto' AND column_name = 'partido_id'");

if ($row = pg_fetch_assoc($verify)) {
    echo "partido_id is_nullable: {$row['is_nullable']}\n";
    if ($row['is_nullable'] == 'YES') {
        echo "\n✅ ¡Listo! Ahora puedes votar en blanco sin problemas.\n";
    } else {
        echo "\n❌ Aún no permite NULL\n";
    }
}

pg_close($conexion);
?>
