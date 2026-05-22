<?php
// Limpiar OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache limpiado correctamente";
} else {
    echo "OPcache no está habilitado";
}
?>
