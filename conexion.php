<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Archivo de conexión a la base de datos
 */

date_default_timezone_set('America/Lima');

// =====================================================
// CONFIGURACIÓN AUTOMÁTICA: Local vs Producción
// =====================================================

// Detectar si estamos en producción (Render) o local (Windows)
$is_production = isset($_SERVER['RENDER']) || isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
                 (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false);

if ($is_production) {
    // =====================================================
    // PRODUCCIÓN: Railway PostgreSQL
    // =====================================================
    $db_host = getenv('PGHOST') ?: 'gondola.proxy.rlwy.net';
    $db_port = getenv('PGPORT') ?: '16689';
    $db_user = getenv('PGUSER') ?: 'postgres';
    $db_password = getenv('PGPASSWORD') ?: 'aGYdhNjZOzgKBaboFadrLUuwMJwhMPft';
    $db_name = getenv('PGDATABASE') ?: 'railway';
    
    $conexion = @pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password sslmode=require");
    
    if (!$conexion) {
        die("Error de conexión a Railway PostgreSQL: " . pg_last_error());
    }
    
    pg_set_client_encoding($conexion, "UTF8");
    
    // Configuración de Cloudinary para almacenamiento de imágenes
    define('CLOUDINARY_CLOUD_NAME', 'dwpmualag');
    define('CLOUDINARY_API_KEY', '285127172321612');
    define('CLOUDINARY_API_SECRET', 'jN8nbD5hhd6kMEg7uCc1UWLDTpg');
    
}  else {
    // =====================================================
    // DESARROLLO LOCAL: PostgreSQL
    // =====================================================
    $db_host = "localhost";
    $db_port = "5432"; // Puerto por defecto de PostgreSQL
    $db_user = "postgres"; // PostgreSQL no usa root
    $db_password = "lurin2025"; // <-- CAMBIA ESTO
    $db_name = "db_elecciones_2026";

    $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");

    if (!$conexion) {
        die("Error de conexión a PostgreSQL: " . pg_last_error());
    }

    pg_set_client_encoding($conexion, "UTF8");
}


// Función para limpiar datos de entrada
function limpiar_dato($dato) {
    global $conexion, $is_production;
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    
    if ($is_production) {
        $dato = pg_escape_string($conexion, $dato); // PostgreSQL
    } else {
        $dato = mysqli_real_escape_string($conexion, $dato); // MySQL
    }
    return $dato;
}

// Función para obtener IP del cliente
function obtener_ip_cliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Subir archivo a Cloudinary o sistema de archivos local
 * @param array $file Array de $_FILES['nombre_campo']
 * @param string $folder Carpeta en Cloudinary (candidatos, partidos)
 * @param string $filename Nombre del archivo
 * @return string|false URL del archivo o false si falla
 */
function subir_archivo($file, $folder, $filename) {
    global $is_production;
    
    if ($is_production) {
        // PRODUCCIÓN: Subir a Cloudinary
        $file_path = $file['tmp_name'];
        
        // Generar firma para autenticación
        $timestamp = time();
        $public_id = $folder . '/' . pathinfo($filename, PATHINFO_FILENAME);
        
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
        
        // Preparar datos para upload
        $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/upload';
        
        $post_fields = [
            'file' => new CURLFile($file_path, $file['type'], $filename),
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
        curl_close($ch);
        
        if ($http_code == 200) {
            $result = json_decode($response, true);
            // Retornar URL segura de Cloudinary
            return $result['secure_url'] ?? false;
        }
        
        return false;
    } else {
        // LOCAL: Guardar en sistema de archivos
        $ruta_local = '../assets/img/' . $folder . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $ruta_local)) {
            return 'assets/img/' . $folder . '/' . $filename;
        }
        
        return false;
    }
}
?>
