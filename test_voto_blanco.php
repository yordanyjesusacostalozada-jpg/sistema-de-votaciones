<?php
/**
 * ARCHIVO DE PRUEBA - Voto en Blanco
 * Este archivo ayuda a diagnosticar problemas con el voto en blanco
 */

session_start();

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Test Voto Blanco</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>";

echo "<div class='container'>";
echo "<h2 class='mb-4'>🔍 Diagnóstico de Voto en Blanco</h2>";

// Verificar si hay una sesión activa
if (isset($_SESSION['ciudadano_dni'])) {
    echo "<div class='alert alert-info'>";
    echo "<h5>✅ Sesión Activa</h5>";
    echo "<p><strong>DNI:</strong> " . htmlspecialchars($_SESSION['ciudadano_dni']) . "</p>";
    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($_SESSION['ciudadano_nombre'] ?? 'No disponible') . "</p>";
    echo "<p><strong>Ha Votado:</strong> " . (isset($_SESSION['ha_votado']) && $_SESSION['ha_votado'] == 1 ? '✅ SÍ' : '❌ NO') . "</p>";
    
    if (isset($_SESSION['fecha_voto'])) {
        echo "<p><strong>Fecha Voto:</strong> " . htmlspecialchars($_SESSION['fecha_voto']) . "</p>";
    }
    
    if (isset($_SESSION['partido_votado'])) {
        echo "<p><strong>Partido ID Votado:</strong> " . htmlspecialchars($_SESSION['partido_votado']) . "</p>";
    }
    echo "</div>";
    
    // Si ya votó, mostrar botón para limpiar sesión y probar de nuevo
    if (isset($_SESSION['ha_votado']) && $_SESSION['ha_votado'] == 1) {
        echo "<div class='alert alert-warning'>";
        echo "<h5>⚠️ Ya has votado en esta sesión</h5>";
        echo "<p>Para probar el voto en blanco nuevamente, necesitas limpiar tu sesión:</p>";
        echo "<form method='post' action='test_voto_blanco.php'>";
        echo "<input type='hidden' name='limpiar_sesion' value='1'>";
        echo "<button type='submit' class='btn btn-danger'>🗑️ Limpiar Sesión y Probar de Nuevo</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h5>✅ Puedes votar</h5>";
        echo "<a href='cedula_votacion.php' class='btn btn-primary'>Ir a la Cédula de Votación</a>";
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>";
    echo "<h5>❌ No hay sesión activa</h5>";
    echo "<p>Debes iniciar sesión primero:</p>";
    echo "<a href='index.php' class='btn btn-primary'>Ir al Login</a>";
    echo "</div>";
}

// Procesar limpieza de sesión
if (isset($_POST['limpiar_sesion']) && $_POST['limpiar_sesion'] == '1') {
    // Limpiar solo las variables de votación, mantener el login
    unset($_SESSION['ha_votado']);
    unset($_SESSION['voto_registrado']);
    unset($_SESSION['partido_votado']);
    unset($_SESSION['fecha_voto']);
    
    echo "<div class='alert alert-success mt-3'>";
    echo "<h5>✅ Sesión limpiada</h5>";
    echo "<p>Ya puedes votar de nuevo. <a href='cedula_votacion.php'>Ir a votar</a></p>";
    echo "</div>";
}

// Mostrar datos POST si existen (para debugging)
if (!empty($_POST) && !isset($_POST['limpiar_sesion'])) {
    echo "<div class='alert alert-info mt-4'>";
    echo "<h5>📨 Datos POST recibidos:</h5>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    echo "</div>";
}

// Instrucciones
echo "<hr class='my-4'>";
echo "<div class='card'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title'>📋 Instrucciones para Probar Voto en Blanco:</h5>";
echo "<ol>";
echo "<li>Asegúrate de estar logueado con un DNI válido</li>";
echo "<li>Si ya votaste, usa el botón de limpiar sesión arriba</li>";
echo "<li>Ve a la cédula de votación</li>";
echo "<li>Selecciona 'VOTO EN BLANCO' (al final de la página)</li>";
echo "<li>Haz clic en 'CONFIRMAR VOTO EN BLANCO'</li>";
echo "<li>Confirma en el modal</li>";
echo "<li>Deberías ser redirigido a la página de confirmación</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

echo "<div class='mt-4'>";
echo "<a href='index.php' class='btn btn-secondary'>🏠 Inicio</a> ";
echo "<a href='cedula_votacion.php' class='btn btn-primary'>🗳️ Ir a Votar</a> ";
echo "<a href='confirmacion_voto.php' class='btn btn-success'>✅ Ver Confirmación</a>";
echo "</div>";

echo "</div>"; // container
echo "</body></html>";
?>
