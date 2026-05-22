<?php
include 'conexion.php';

echo "<h2>Estructura de tbl_candidato</h2>";

if ($is_production) {
    $query = "SELECT column_name, data_type, is_nullable, column_default 
              FROM information_schema.columns 
              WHERE table_name = 'tbl_candidato' 
              ORDER BY ordinal_position";
    $result = pg_query($conexion, $query);
    while ($row = pg_fetch_assoc($result)) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    $query = "DESCRIBE tbl_candidato";
    $result = mysqli_query($conexion, $query);
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($result)) {
        print_r($row);
    }
    echo "</pre>";
}
?>
