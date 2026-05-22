<?php
/**
 * SISTEMA ELECTORAL PERÚ 2026
 * Cerrar sesión de administrador
 */

session_start();

// Limpiar variables de sesión de administrador
unset($_SESSION['admin_id']);
unset($_SESSION['admin_usuario']);
unset($_SESSION['admin_nombres']);
unset($_SESSION['admin_rol']);
unset($_SESSION['admin_login_time']);

// Destruir la sesión completamente
session_destroy();

// Redirigir al login de administrador
header("Location: admin/login_admin.php?msg=logout");
exit();
?>
