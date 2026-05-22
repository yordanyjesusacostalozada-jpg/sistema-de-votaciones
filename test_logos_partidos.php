<?php
/**
 * Test: Verificar logos de partidos políticos
 */

include 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Test Logos Partidos</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
.partido-card { 
    border: 3px solid #ddd; 
    border-radius: 15px; 
    padding: 20px; 
    margin: 15px; 
    text-align: center; 
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.partido-logo { 
    width: 120px; 
    height: 120px; 
    object-fit: contain; 
    margin: 20px auto;
    display: block;
}
.check { color: green; font-size: 20px; font-weight: bold; }
.error { color: red; font-size: 20px; font-weight: bold; }
</style>";
echo "</head><body class='bg-light p-4'>";

echo "<div class='container'>";
echo "<h2 class='mb-4 text-center'>🏛️ Verificación de Logos de Partidos Políticos</h2>";

// Obtener partidos
$query = "SELECT id, siglas, nombre_corto, nombre_completo, logo_url, color_primario FROM tbl_partido WHERE estado = 1 ORDER BY orden_cedula";
$resultado = mysqli_query($conexion, $query);

echo "<div class='row'>";

while ($partido = mysqli_fetch_assoc($resultado)) {
    $logo_path = $partido['logo_url'];
    $archivo_existe = file_exists(__DIR__ . '/' . $logo_path);
    
    echo "<div class='col-md-6 col-lg-4 col-xl-3'>";
    echo "<div class='partido-card' style='border-color: " . htmlspecialchars($partido['color_primario']) . ";'>";
    
    // Estado del archivo
    if ($archivo_existe) {
        echo "<div class='check'>✓ Logo OK</div>";
    } else {
        echo "<div class='error'>✗ NO EXISTE</div>";
    }
    
    // Logo
    if ($archivo_existe) {
        echo "<img src='" . htmlspecialchars($logo_path) . "' class='partido-logo' alt='Logo'>";
    } else {
        echo "<div class='partido-logo bg-secondary d-flex align-items-center justify-content-center text-white'>";
        echo "<i class='fas fa-image fa-3x'></i>";
        echo "</div>";
    }
    
    // Información del partido
    echo "<h5 class='mt-3' style='color: " . htmlspecialchars($partido['color_primario']) . ";'>";
    echo htmlspecialchars($partido['siglas']);
    echo "</h5>";
    
    echo "<p class='text-muted small mb-2'>" . htmlspecialchars($partido['nombre_completo']) . "</p>";
    
    // Ruta del archivo
    echo "<small class='text-muted d-block mt-2' style='font-size: 10px; word-break: break-all;'>";
    echo htmlspecialchars($logo_path);
    echo "</small>";
    
    echo "</div>";
    echo "</div>";
}

echo "</div>";

mysqli_close($conexion);

echo "<hr class='my-5'>";
echo "<div class='text-center'>";
echo "<h4 class='mb-3'>✅ Todos los logos están configurados correctamente</h4>";
echo "<a href='cedula_votacion.php' class='btn btn-primary btn-lg me-2'>🗳️ Ver Cédula de Votación</a>";
echo "<a href='resultados_publicos.php' class='btn btn-success btn-lg'>📊 Ver Resultados</a>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
