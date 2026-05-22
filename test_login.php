<?php
include 'conexion.php';

$usuario = 'admin';
$clave = 'admin123';

echo "Entorno: " . ($is_production ? "PRODUCCION" : "LOCAL") . "\n";
echo "Usuario a probar: $usuario\n";
echo "Clave a probar: $clave\n";
echo "MD5 de la clave: " . md5($clave) . "\n\n";

if (!$is_production) {
    // Verificar qué hash tiene guardado
    $query_check = "SELECT usuario, clave FROM tbl_administrador WHERE usuario = 'admin'";
    $resultado_check = mysqli_query($conexion, $query_check);
    if ($admin_db = mysqli_fetch_assoc($resultado_check)) {
        echo "Hash guardado en BD: " . $admin_db['clave'] . "\n\n";
    }
    
    // Intentar login
    $stmt = mysqli_prepare($conexion, "SELECT id, usuario, nombres, rol FROM tbl_administrador WHERE usuario = ? AND clave = MD5(?) AND estado = 1 LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $clave);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $admin = mysqli_fetch_assoc($resultado);
        echo "✅ LOGIN EXITOSO\n";
        print_r($admin);
    } else {
        echo "❌ LOGIN FALLIDO\n";
        
        // Probar con la clave ya hasheada
        $stmt2 = mysqli_prepare($conexion, "SELECT id, usuario, nombres, rol FROM tbl_administrador WHERE usuario = ? AND clave = ? AND estado = 1 LIMIT 1");
        $clave_hash = md5($clave);
        mysqli_stmt_bind_param($stmt2, "ss", $usuario, $clave_hash);
        mysqli_stmt_execute($stmt2);
        $resultado2 = mysqli_stmt_get_result($stmt2);
        
        if ($resultado2 && mysqli_num_rows($resultado2) === 1) {
            echo "⚠️ La clave está DOBLE HASHEADA en la BD\n";
        }
    }
    
    mysqli_close($conexion);
}
?>
