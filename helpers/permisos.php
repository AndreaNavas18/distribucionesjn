<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario tiene un permiso específico
 */
function can($permiso) {
    return isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos']);
}

/**
 * Carga todos los permisos del usuario (por rol y por permisos directos)
 */
function cargarPermisosUsuario($usuarioId, $db) {
    // Permisos por rol
    $sqlRol = "SELECT p.nombre FROM permisos AS p ".
    "INNER JOIN rolpermiso AS rp ON (p.id = rp.idpermiso) ".
    "INNER JOIN usuariorol AS ur ON (rp.idrol = ur.idrol) ".
    "WHERE ur.idusuario = ?";
    $rsRol = $db->Execute($sqlRol, [$usuarioId]);
    $permisosRol = $rsRol ? array_column($rsRol->GetArray(), 'nombre') : [];

    // Permisos individuales
    $sqlUsuario = "SELECT p.nombre FROM permisos AS p ".
    "INNER JOIN usuariopermiso AS up ON (p.id = up.idpermiso) ".
    "WHERE up.idusuario = ?";
    $rsUsuario = $db->Execute($sqlUsuario, [$usuarioId]);
    $permisosUsuario = $rsUsuario ? array_column($rsUsuario->GetArray(), 'nombre') : [];

    // Combinar y quitar duplicados
    $_SESSION['permisos'] = array_unique(array_merge($permisosRol, $permisosUsuario));
}