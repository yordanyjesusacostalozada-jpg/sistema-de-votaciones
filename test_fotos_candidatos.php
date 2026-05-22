<?php
/**
 * Test: Verificar que las fotos de candidatos se muestren correctamente
 */

include 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Test Fotos Candidatos</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>
.candidato-card { border: 2px solid #ddd; border-radius: 10px; padding: 15px; margin: 10px; text-align: center; }
.candidato-foto { width: 120px; height: 150px; object-fit: cover; border-radius: 8px; }
.check { color: green; font-size: 24px; }
.error { color: red; font-size: 24px; }
</style>";
echo "</head><body class='p-4'>";

echo "<div class='container'>";
echo "<h2 class='mb-4'>🖼️ Verificación de Fotos de Candidatos</h2>";

// Obtener datos de la cédula
$query = "CALL sp_obtener_cedula()";
$resultado = mysqli_query($conexion, $query);
$partidos = [];

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $partidos[] = $fila;
    }
}

echo "<div class='row'>";

foreach ($partidos as $partido) {
    echo "<div class='col-md-6 col-lg-4'>";
    echo "<div class='candidato-card'>";
    
    // Partido
    echo "<h5 class='text-primary'>" . htmlspecialchars($partido['siglas']) . "</h5>";
    echo "<p class='text-muted small'>" . htmlspecialchars($partido['nombre_completo']) . "</p>";
    echo "<hr>";
    
    // Presidente
    echo "<div class='mb-3'>";
    echo "<strong>Presidente:</strong><br>";
    echo htmlspecialchars($partido['presidente']) . "<br>";
    
    $foto_path = $partido['presidente_foto'];
    $archivo_existe = file_exists(__DIR__ . '/' . $foto_path);
    
    if ($archivo_existe) {
        echo "<span class='check'>✓</span> Archivo existe<br>";
        echo "<img src='" . htmlspecialchars($foto_path) . "' class='candidato-foto mt-2' alt='Presidente'>";
    } else {
        echo "<span class='error'>✗</span> Archivo NO existe<br>";
        echo "<small class='text-danger'>Ruta: " . htmlspecialchars($foto_path) . "</small>";
    }
    echo "</div>";
    
    // VP1
    if (!empty($partido['vice1'])) {
        echo "<div class='mt-3 small'>";
        echo "<strong>VP1:</strong> " . htmlspecialchars($partido['vice1']);
        echo "</div>";
    }
    
    // VP2
    if (!empty($partido['vice2'])) {
        echo "<div class='small'>";
        echo "<strong>VP2:</strong> " . htmlspecialchars($partido['vice2']);
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
}

echo "</div>";

mysqli_close($conexion);

echo "<hr class='my-4'>";
echo "<div class='text-center'>";
echo "<a href='cedula_votacion.php' class='btn btn-primary btn-lg'>🗳️ Ir a la Cédula de Votación</a>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
