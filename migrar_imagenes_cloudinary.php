<?php
/**
 * MIGRACIÓN DE IMÁGENES A CLOUDINARY
 * Este script sube todas las imágenes locales a Cloudinary
 * y actualiza las URLs en la base de datos de producción
 */

// Configuración de Cloudinary
define('CLOUDINARY_CLOUD_NAME', 'dwpmualag');
define('CLOUDINARY_API_KEY', '285127172321612');
define('CLOUDINARY_API_SECRET', 'jN8nbD5hhd6kMEg7uCc1UWLDTpg');

// Conectar a PostgreSQL de producción
$db_host = 'gondola.proxy.rlwy.net';
$db_port = '16689';
$db_user = 'postgres';
$db_password = 'aGYdhNjZOzgKBaboFadrLUuwMJwhMPft';
$db_name = 'railway';

$conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password sslmode=require");

if (!$conexion) {
    die("Error de conexión a Railway PostgreSQL: " . pg_last_error());
}

echo "=== MIGRACIÓN DE IMÁGENES A CLOUDINARY ===\n\n";

/**
 * Subir imagen a Cloudinary
 */
function subir_a_cloudinary($file_path, $folder, $public_id) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    // Solo permitir JPG, JPEG, PNG (no SVG)
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        echo "\n  [Formato no soportado: $extension] ";
        return false;
    }
    
    $timestamp = time();
    
    // Generar firma
    $params_to_sign = [
        'folder' => $folder,
        'public_id' => $public_id,
        'timestamp' => $timestamp
    ];
    
    ksort($params_to_sign);
    $signature_string = '';
    foreach ($params_to_sign as $key => $value) {
        $signature_string .= $key . '=' . $value . '&';
    }
    $signature_string = rtrim($signature_string, '&');
    $signature = sha1($signature_string . CLOUDINARY_API_SECRET);
    
    // Upload a Cloudinary
    $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/upload';
    
    $post_fields = [
        'file' => new CURLFile($file_path),
        'folder' => $folder,
        'public_id' => $public_id,
        'timestamp' => $timestamp,
        'api_key' => CLOUDINARY_API_KEY,
        'signature' => $signature
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        return $result['secure_url'] ?? false;
    } else {
        echo "\n  [HTTP $http_code] ";
        if ($curl_error) {
            echo "[CURL Error: $curl_error] ";
        }
        if ($response) {
            $error_data = json_decode($response, true);
            if (isset($error_data['error']['message'])) {
                echo "[" . $error_data['error']['message'] . "] ";
            }
        }
    }
    
    return false;
}

// =====================================================
// MIGRAR LOGOS DE PARTIDOS
// =====================================================
echo "--- MIGRANDO LOGOS DE PARTIDOS ---\n";

$directorio_partidos = __DIR__ . '/assets/img/partidos/';
$archivos_partidos = glob($directorio_partidos . '*.{jpg,jpeg,png,svg}', GLOB_BRACE);

$query_partidos = "SELECT id, siglas, logo_url FROM tbl_partido WHERE estado = TRUE";
$result_partidos = pg_query($conexion, $query_partidos);

$partidos_migrados = 0;
$partidos_error = 0;

while ($partido = pg_fetch_assoc($result_partidos)) {
    echo "Procesando partido: {$partido['siglas']}... ";
    
    // Buscar imagen que contenga las siglas del partido
    $imagen_encontrada = null;
    foreach ($archivos_partidos as $archivo) {
        $nombre_archivo = basename($archivo);
        // Buscar por siglas o nombre del partido
        if (stripos($nombre_archivo, $partido['siglas']) !== false) {
            $imagen_encontrada = $archivo;
            break;
        }
    }
    
    if ($imagen_encontrada && file_exists($imagen_encontrada)) {
        $public_id = strtoupper($partido['siglas']);
        $cloudinary_url = subir_a_cloudinary($imagen_encontrada, 'partidos', $public_id);
        
        if ($cloudinary_url) {
            // Actualizar URL en base de datos
            $update = "UPDATE tbl_partido SET logo_url = $1 WHERE id = $2";
            $resultado = pg_query_params($conexion, $update, [$cloudinary_url, $partido['id']]);
            
            if ($resultado) {
                echo "✓ MIGRADO\n";
                echo "  Archivo: " . basename($imagen_encontrada) . "\n";
                echo "  URL: $cloudinary_url\n";
                $partidos_migrados++;
            } else {
                echo "✗ Error al actualizar BD\n";
                $partidos_error++;
            }
        } else {
            echo "✗ Error al subir a Cloudinary\n";
            $partidos_error++;
        }
    } else {
        echo "⊘ Imagen no encontrada localmente\n";
        $partidos_error++;
    }
}

echo "\nPartidos migrados: $partidos_migrados\n";
echo "Partidos con error: $partidos_error\n\n";

// =====================================================
// MIGRAR FOTOS DE CANDIDATOS
// =====================================================
echo "--- MIGRANDO FOTOS DE CANDIDATOS ---\n";

$directorio_candidatos = __DIR__ . '/assets/img/candidatos/';
$archivos_candidatos = glob($directorio_candidatos . '*.{jpg,jpeg,png}', GLOB_BRACE);

$query_candidatos = "SELECT c.id, c.nombres, c.apellido_paterno, c.apellido_materno, c.foto_url, p.siglas 
                     FROM tbl_candidato c 
                     JOIN tbl_partido p ON c.partido_id = p.id 
                     WHERE c.estado = TRUE";
$result_candidatos = pg_query($conexion, $query_candidatos);

$candidatos_migrados = 0;
$candidatos_error = 0;

while ($candidato = pg_fetch_assoc($result_candidatos)) {
    $nombre_completo = $candidato['nombres'] . ' ' . $candidato['apellido_paterno'];
    echo "Procesando candidato: $nombre_completo... ";
    
    // Buscar imagen que contenga el apellido del candidato y siglas del partido
    $imagen_encontrada = null;
    foreach ($archivos_candidatos as $archivo) {
        $nombre_archivo = strtoupper(basename($archivo));
        $apellido_upper = strtoupper($candidato['apellido_paterno']);
        $siglas_upper = strtoupper($candidato['siglas']);
        
        // Buscar por siglas del partido Y apellido del candidato
        if (stripos($nombre_archivo, $siglas_upper) !== false && 
            stripos($nombre_archivo, $apellido_upper) !== false) {
            $imagen_encontrada = $archivo;
            break;
        }
    }
    
    if ($imagen_encontrada && file_exists($imagen_encontrada)) {
        $extension = pathinfo($imagen_encontrada, PATHINFO_EXTENSION);
        $public_id = $candidato['siglas'] . '-' . strtoupper(str_replace(' ', '_', $nombre_completo));
        $cloudinary_url = subir_a_cloudinary($imagen_encontrada, 'candidatos', $public_id);
        
        if ($cloudinary_url) {
            // Actualizar URL en base de datos
            $update = "UPDATE tbl_candidato SET foto_url = $1 WHERE id = $2";
            $resultado = pg_query_params($conexion, $update, [$cloudinary_url, $candidato['id']]);
            
            if ($resultado) {
                echo "✓ MIGRADO\n";
                echo "  Archivo: " . basename($imagen_encontrada) . "\n";
                echo "  URL: $cloudinary_url\n";
                $candidatos_migrados++;
            } else {
                echo "✗ Error al actualizar BD\n";
                $candidatos_error++;
            }
        } else {
            echo "✗ Error al subir a Cloudinary\n";
            $candidatos_error++;
        }
    } else {
        echo "⊘ Imagen no encontrada localmente\n";
        $candidatos_error++;
    }
}

echo "\nCandidatos migrados: $candidatos_migrados\n";
echo "Candidatos con error: $candidatos_error\n\n";

// =====================================================
// RESUMEN FINAL
// =====================================================
echo "=== RESUMEN DE MIGRACIÓN ===\n";
echo "Total partidos migrados: $partidos_migrados\n";
echo "Total candidatos migrados: $candidatos_migrados\n";
echo "Total imágenes subidas: " . ($partidos_migrados + $candidatos_migrados) . "\n";
echo "\n✓ Migración completada\n";
echo "Ahora todas las imágenes están en Cloudinary y visibles en producción.\n";

pg_close($conexion);
?>
