<?php
include 'conexion.php';

if (!$is_production) {
    echo "Estadísticas de Votos en BD Local:\n";
    echo "==================================\n\n";
    
    // Total de votos por tipo
    $query = "SELECT voto_tipo, COUNT(*) as total FROM tbl_voto GROUP BY voto_tipo";
    $resultado = mysqli_query($conexion, $query);
    echo "Votos por Tipo:\n";
    while ($row = mysqli_fetch_assoc($resultado)) {
        echo "  - " . $row['voto_tipo'] . ": " . $row['total'] . "\n";
    }
    
    echo "\nVotos por Partido:\n";
    $query = "SELECT p.siglas, COUNT(v.id) as votos 
              FROM tbl_partido p 
              LEFT JOIN tbl_voto v ON p.id = v.partido_id 
              WHERE p.estado = 1 
              GROUP BY p.id, p.siglas 
              ORDER BY votos DESC 
              LIMIT 5";
    $resultado = mysqli_query($conexion, $query);
    while ($row = mysqli_fetch_assoc($resultado)) {
        echo "  - " . $row['siglas'] . ": " . $row['votos'] . " votos\n";
    }
    
    mysqli_close($conexion);
}
?>
