<?php
/**
 * Script para corregir función sp_obtener_cedula en Railway
 */

// Credenciales de Railway
$host = "gondola.proxy.rlwy.net";
$port = "16689";
$database = "railway";
$user = "postgres";
$password = "aGYdhNjZOzgKBaboFadrLUuwMJwhMPft";

// Conectar a Railway
$conn_string = "host=$host port=$port dbname=$database user=$user password=$password sslmode=require";
$conn = @pg_connect($conn_string);

if (!$conn) {
    die("❌ Error de conexión: " . pg_last_error() . "\n");
}

echo "✅ Conectado a Railway PostgreSQL\n\n";

// Eliminar función existente
$drop_sql = "DROP FUNCTION IF EXISTS sp_obtener_cedula() CASCADE;";
$result = pg_query($conn, $drop_sql);

if ($result) {
    echo "✅ Función anterior eliminada\n";
} else {
    echo "⚠️ Error al eliminar función: " . pg_last_error($conn) . "\n";
}

// Crear nueva función con tipos correctos
$create_sql = "
CREATE OR REPLACE FUNCTION sp_obtener_cedula()
RETURNS TABLE (
    partido_id INTEGER,
    partido_siglas TEXT,
    partido_nombre TEXT,
    partido_logo TEXT,
    partido_color TEXT,
    candidato_presidente_id INTEGER,
    candidato_presidente_nombres TEXT,
    candidato_presidente_foto TEXT,
    candidato_presidente_profesion TEXT,
    candidato_vp1_nombres TEXT,
    candidato_vp2_nombres TEXT
) AS \$\$
BEGIN
    RETURN QUERY
    SELECT 
        p.id,
        p.siglas::TEXT,
        p.nombre_completo::TEXT,
        p.logo_url::TEXT,
        p.color_primario::TEXT,
        pres.id,
        (pres.nombres || ' ' || pres.apellido_paterno || ' ' || pres.apellido_materno)::TEXT,
        pres.foto_url::TEXT,
        pres.profesion::TEXT,
        (vp1.nombres || ' ' || vp1.apellido_paterno)::TEXT,
        (vp2.nombres || ' ' || vp2.apellido_paterno)::TEXT
    FROM tbl_partido p
    LEFT JOIN tbl_candidato pres ON p.id = pres.partido_id AND pres.tipo_candidato = 'PRESIDENTE'
    LEFT JOIN tbl_candidato vp1 ON p.id = vp1.partido_id AND vp1.tipo_candidato = 'VICEPRESIDENTE_1'
    LEFT JOIN tbl_candidato vp2 ON p.id = vp2.partido_id AND vp2.tipo_candidato = 'VICEPRESIDENTE_2'
    WHERE p.estado = TRUE
    ORDER BY p.siglas;
END;
\$\$ LANGUAGE plpgsql;
";

$result = pg_query($conn, $create_sql);

if ($result) {
    echo "✅ Nueva función sp_obtener_cedula() creada correctamente\n\n";
    
    // Probar la función
    echo "🧪 Probando función...\n";
    $test_result = pg_query($conn, "SELECT * FROM sp_obtener_cedula()");
    
    if ($test_result) {
        $count = pg_num_rows($test_result);
        echo "✅ Función funciona correctamente - $count partidos encontrados\n";
        
        // Mostrar primeros resultados
        echo "\n📋 Primeros partidos:\n";
        $i = 0;
        while ($row = pg_fetch_assoc($test_result) and $i < 3) {
            echo "  ✓ " . $row['partido_siglas'] . " - " . $row['candidato_presidente_nombres'] . "\n";
            $i++;
        }
    } else {
        echo "❌ Error al probar función: " . pg_last_error($conn) . "\n";
    }
    
} else {
    echo "❌ Error al crear función: " . pg_last_error($conn) . "\n";
}

pg_close($conn);
echo "\n🎉 Proceso completado!\n";
?>
