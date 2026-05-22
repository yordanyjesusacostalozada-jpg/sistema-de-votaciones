<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Procesamiento de login con DNI
 * PROTEGIDO CON reCAPTCHA
 */

session_start();
include 'conexion.php';

// Configuración reCAPTCHA
define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

// Verificar que sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// Verificar reCAPTCHA
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

// Solo validar CAPTCHA si NO estamos en localhost (desarrollo)
$es_localhost = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

if (!$es_localhost) {
    if (empty($recaptcha_response)) {
        header("Location: index.php?error=captcha_requerido");
        exit();
    }

    // Validar CAPTCHA con Google
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($verify_url . '?secret=' . RECAPTCHA_SECRET_KEY . '&response=' . $recaptcha_response);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        header("Location: index.php?error=captcha_invalido");
        exit();
    }
}
// En localhost, el CAPTCHA se omite automáticamente para desarrollo

// Obtener y limpiar DNI
$dni = limpiar_dato($_POST['dni'] ?? '');

// Validar formato DNI
if (empty($dni) || strlen($dni) !== 8 || !ctype_digit($dni)) {
    header("Location: index.php?error=dni_invalido");
    exit();
}

// Buscar ciudadano en el padrón electoral usando procedimiento almacenado
if ($is_production) {
    // PostgreSQL: Usar función
    $query = "SELECT * FROM sp_validar_ciudadano('$dni')";
    $resultado = pg_query($conexion, $query);
    
    if ($resultado && pg_num_rows($resultado) === 1) {
        $ciudadano = pg_fetch_assoc($resultado);
        
        // Verificar que esté activo
        if ($ciudadano['estado'] != 't') {
            pg_close($conexion);
            header("Location: index.php?error=inactivo");
            exit();
        }
        
        // Verificar si ya votó
        if ($ciudadano['ha_votado'] == 't') {
            pg_close($conexion);
            header("Location: index.php?error=ya_voto");
            exit();
        }
        
        // Establecer sesión del ciudadano
        $_SESSION['ciudadano_id'] = $ciudadano['id'];
        $_SESSION['ciudadano_dni'] = $ciudadano['dni'];
        $_SESSION['ciudadano_nombres'] = $ciudadano['nombres'];
        $_SESSION['ciudadano_apellidos'] = $ciudadano['apellido_paterno'] . ' ' . $ciudadano['apellido_materno'];
        $_SESSION['ciudadano_nombre'] = $ciudadano['nombres'] . ' ' . $ciudadano['apellido_paterno'] . ' ' . $ciudadano['apellido_materno'];
        $_SESSION['ciudadano_departamento'] = $ciudadano['departamento'];
        $_SESSION['ciudadano_provincia'] = $ciudadano['provincia'];
        $_SESSION['ciudadano_distrito'] = $ciudadano['distrito'];
        $_SESSION['ha_votado'] = $ciudadano['ha_votado'];
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = obtener_ip_cliente();
        
        pg_close($conexion);
        
        // Redirigir a la cédula de votación
        header("Location: cedula_votacion.php");
        exit();
        
    } else {
        pg_close($conexion);
        header("Location: index.php?error=no_encontrado");
        exit();
    }
} else {
    // MySQL: Usar procedimiento almacenado
    $query = "CALL sp_validar_ciudadano('$dni')";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $ciudadano = mysqli_fetch_assoc($resultado);
        
        // Verificar que esté activo
        if ($ciudadano['estado'] != 1) {
            mysqli_close($conexion);
            header("Location: index.php?error=inactivo");
            exit();
        }
        
        // Verificar si ya votó
        if ($ciudadano['ha_votado'] == 1) {
            mysqli_close($conexion);
            header("Location: index.php?error=ya_voto");
            exit();
        }
        
        // Establecer sesión del ciudadano
        $_SESSION['ciudadano_id'] = $ciudadano['id'];
        $_SESSION['ciudadano_dni'] = $ciudadano['dni'];
        $_SESSION['ciudadano_nombres'] = $ciudadano['nombres'];
        $_SESSION['ciudadano_apellidos'] = $ciudadano['apellido_paterno'] . ' ' . $ciudadano['apellido_materno'];
        $_SESSION['ciudadano_nombre'] = $ciudadano['nombres'] . ' ' . $ciudadano['apellido_paterno'] . ' ' . $ciudadano['apellido_materno'];
        $_SESSION['ciudadano_departamento'] = $ciudadano['departamento'];
        $_SESSION['ciudadano_provincia'] = $ciudadano['provincia'];
        $_SESSION['ciudadano_distrito'] = $ciudadano['distrito'];
        $_SESSION['ha_votado'] = $ciudadano['ha_votado'];
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = obtener_ip_cliente();
        
        mysqli_close($conexion);
        
        // Redirigir a la cédula de votación
        header("Location: cedula_votacion.php");
        exit();
        
    } else {
        mysqli_close($conexion);
        header("Location: index.php?error=no_encontrado");
        exit();
    }
}
?>
