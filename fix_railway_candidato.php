<?php
/**
 * AGREGAR CAMPOS FALTANTES A tbl_candidato EN RAILWAY
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

echo "=== AGREGAR CAMPOS A tbl_candidato EN RAILWAY ===\n\n";

// Campo DNI
echo "1. Verificando campo DNI...\n";
$check_dni = pg_query($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_candidato' AND column_name = 'dni'");

if (pg_num_rows($check_dni) > 0) {
    echo "   ✓ DNI ya existe\n";
} else {
    echo "   Agregando DNI...\n";
    $sql = "ALTER TABLE tbl_candidato ADD COLUMN dni VARCHAR(8)";
    if (pg_query($conexion, $sql)) {
        echo "   ✓ DNI agregado\n";
        pg_query($conexion, "UPDATE tbl_candidato SET dni = '00000000' WHERE dni IS NULL");
        pg_query($conexion, "ALTER TABLE tbl_candidato ALTER COLUMN dni SET NOT NULL");
    } else {
        echo "   ❌ Error: " . pg_last_error($conexion) . "\n";
    }
}

// Campo fecha_nacimiento
echo "\n2. Verificando campo fecha_nacimiento...\n";
$check_fecha = pg_query($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_candidato' AND column_name = 'fecha_nacimiento'");

if (pg_num_rows($check_fecha) > 0) {
    echo "   ✓ fecha_nacimiento ya existe\n";
} else {
    echo "   Agregando fecha_nacimiento...\n";
    $sql = "ALTER TABLE tbl_candidato ADD COLUMN fecha_nacimiento DATE";
    if (pg_query($conexion, $sql)) {
        echo "   ✓ fecha_nacimiento agregado\n";
        pg_query($conexion, "UPDATE tbl_candidato SET fecha_nacimiento = '1980-01-01' WHERE fecha_nacimiento IS NULL");
        pg_query($conexion, "ALTER TABLE tbl_candidato ALTER COLUMN fecha_nacimiento SET NOT NULL");
    } else {
        echo "   ❌ Error: " . pg_last_error($conexion) . "\n";
    }
}

// Campo tipo_candidato
echo "\n3. Verificando campo tipo_candidato...\n";
$check_tipo = pg_query($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_candidato' AND column_name = 'tipo_candidato'");

if (pg_num_rows($check_tipo) > 0) {
    echo "   ✓ tipo_candidato ya existe\n";
} else {
    echo "   Agregando tipo_candidato...\n";
    $sql = "ALTER TABLE tbl_candidato ADD COLUMN tipo_candidato VARCHAR(30)";
    if (pg_query($conexion, $sql)) {
        echo "   ✓ tipo_candidato agregado\n";
        pg_query($conexion, "UPDATE tbl_candidato SET tipo_candidato = 'PRESIDENTE' WHERE tipo_candidato IS NULL");
        pg_query($conexion, "ALTER TABLE tbl_candidato ALTER COLUMN tipo_candidato SET NOT NULL");
    } else {
        echo "   ❌ Error: " . pg_last_error($conexion) . "\n";
    }
}

echo "\n=== VERIFICACIÓN FINAL ===\n";
$verify = pg_query($conexion, "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'tbl_candidato' AND column_name IN ('dni', 'fecha_nacimiento', 'tipo_candidato') ORDER BY column_name");

while ($row = pg_fetch_assoc($verify)) {
    echo "{$row['column_name']}: {$row['data_type']} (NULL: {$row['is_nullable']})\n";
}

echo "\n✅ ¡Listo! Ahora puedes editar candidatos en Render sin problemas.\n";

pg_close($conexion);
?>
