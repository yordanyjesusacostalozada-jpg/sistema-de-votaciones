<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Cerrar Sesión
 */

session_start();
session_unset();
session_destroy();

header("Location: index.php?logout=1");
exit();
?>
