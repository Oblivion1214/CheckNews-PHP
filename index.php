<?php
// Usa /tmp para sesiones en entornos restringidos (como Railway)
session_save_path(sys_get_temp_dir());
session_start();

// Rutas destino
$target = isset($_SESSION['usuarioID']) ? 'templets/Principal.php' : 'templets/login.php';
$fullPath = __DIR__ . '/' . $target;

// Verifica existencia del archivo
if (!file_exists($fullPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error 404: Página no encontrada";
    exit();
}

// Redirige
header('Location: ' . $target);
exit();
