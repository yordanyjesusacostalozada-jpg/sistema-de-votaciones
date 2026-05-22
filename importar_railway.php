<?php
/**
 * Script para importar base de datos a Railway PostgreSQL
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

// Leer archivo SQL
$sql_file = __DIR__ . '/database_electoral_railway.sql';
if (!file_exists($sql_file)) {
    die("❌ No se encuentra el archivo: $sql_file\n");
}

$sql = file_get_contents($sql_file);
echo "📄 Archivo SQL leído correctamente\n";
echo "📊 Ejecutando queries...\n\n";

// Ejecutar SQL
$result = @pg_query($conn, $sql);

if ($result) {
    echo "✅ Base de datos importada exitosamente!\n\n";
    
    // Verificar tablas creadas
    $check_sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
    $tables = pg_query($conn, $check_sql);
    
    echo "📋 Tablas creadas:\n";
    while ($row = pg_fetch_assoc($tables)) {
        echo "  ✓ " . $row['table_name'] . "\n";
    }
    
    // Contar registros
    echo "\n📊 Datos insertados:\n";
    $counts = [
        'tbl_ciudadano' => 'Ciudadanos',
        'tbl_partido' => 'Partidos',
        'tbl_candidato' => 'Candidatos',
        'tbl_administrador' => 'Administradores'
    ];
    
    foreach ($counts as $table => $name) {
        $count_result = pg_query($conn, "SELECT COUNT(*) as total FROM $table");
        $count = pg_fetch_assoc($count_result);
        echo "  ✓ $name: " . $count['total'] . "\n";
    }
    
} else {
    echo "❌ Error al importar: " . pg_last_error($conn) . "\n";
}

pg_close($conn);
echo "\n🎉 Proceso completado!\n";
?>
