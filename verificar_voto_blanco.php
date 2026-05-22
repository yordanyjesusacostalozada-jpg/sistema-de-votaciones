<?php
/**
 * Verificador de Votos en Blanco
 */

include 'conexion.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Verificar Voto en Blanco</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-5'>";

echo "<div class='container'>";
echo "<h2 class='mb-4'>🔍 Verificación de Votos en Blanco</h2>";

// Consultar votos en blanco
$query = "SELECT v.id, v.voto_tipo, v.partido_id, v.fecha_voto,
          CONCAT(c.nombres, ' ', c.apellido_paterno) as votante,
          c.dni
          FROM tbl_voto v
          INNER JOIN tbl_ciudadano c ON v.ciudadano_id = c.id
          WHERE v.voto_tipo = 'BLANCO'
          ORDER BY v.fecha_voto DESC";

$resultado = mysqli_query($conexion, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo "<div class='alert alert-success'>";
    echo "<h5>✅ Votos en Blanco Registrados: " . mysqli_num_rows($resultado) . "</h5>";
    echo "</div>";
    
    echo "<table class='table table-striped table-bordered'>";
    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th>ID Voto</th>";
    echo "<th>DNI</th>";
    echo "<th>Votante</th>";
    echo "<th>Tipo Voto</th>";
    echo "<th>Partido ID</th>";
    echo "<th>Fecha</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($row = mysqli_fetch_assoc($resultado)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['dni'] . "</td>";
        echo "<td>" . htmlspecialchars($row['votante']) . "</td>";
        echo "<td><span class='badge bg-warning'>" . $row['voto_tipo'] . "</span></td>";
        echo "<td>" . ($row['partido_id'] === null ? '<span class="badge bg-secondary">NULL ✓</span>' : $row['partido_id']) . "</td>";
        echo "<td>" . date('d/m/Y H:i:s', strtotime($row['fecha_voto'])) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    
} else {
    echo "<div class='alert alert-info'>";
    echo "<h5>ℹ️ No hay votos en blanco registrados todavía</h5>";
    echo "<p>Prueba votando en blanco con el DNI <strong>44332211</strong></p>";
    echo "</div>";
}

// Mostrar todos los votos
echo "<hr class='my-4'>";
echo "<h3>Todos los Votos Registrados</h3>";

$query_all = "SELECT v.id, v.voto_tipo, v.partido_id, 
              CONCAT(c.nombres, ' ', c.apellido_paterno) as votante,
              c.dni,
              p.siglas as partido
              FROM tbl_voto v
              INNER JOIN tbl_ciudadano c ON v.ciudadano_id = c.id
              LEFT JOIN tbl_partido p ON v.partido_id = p.id
              ORDER BY v.fecha_voto DESC
              LIMIT 10";

$resultado_all = mysqli_query($conexion, $query_all);

if ($resultado_all) {
    echo "<table class='table table-sm table-bordered'>";
    echo "<thead class='table-secondary'>";
    echo "<tr><th>ID</th><th>DNI</th><th>Votante</th><th>Tipo</th><th>Partido</th></tr>";
    echo "</thead><tbody>";
    
    while ($row = mysqli_fetch_assoc($resultado_all)) {
        $tipo_badge = $row['voto_tipo'] === 'BLANCO' ? 'bg-warning' : 'bg-success';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['dni'] . "</td>";
        echo "<td>" . htmlspecialchars($row['votante']) . "</td>";
        echo "<td><span class='badge {$tipo_badge}'>" . $row['voto_tipo'] . "</span></td>";
        echo "<td>" . ($row['partido'] ?? '<em class="text-muted">N/A</em>') . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
}

mysqli_close($conexion);

echo "<div class='mt-4'>";
echo "<a href='index.php' class='btn btn-primary'>🏠 Ir al Inicio</a> ";
echo "<a href='cedula_votacion.php' class='btn btn-success'>🗳️ Ir a Votar</a>";
echo "</div>";

echo "</div></body></html>";
?>
