<?php
// Guarda sesiones en /tmp (o la ruta por defecto del sistema)
session_save_path(sys_get_temp_dir());
session_start();

// Decide adónde redirigir
if (isset($_SESSION['usuarioID'])) {
    $target = 'templets/Principal.php';
} else {
    $target = 'templets/login.php';
}

// Si el archivo no existe, manda un 404
if (!file_exists(__DIR__ . '/' . $target)) {
    http_response_code(404);
    echo "Error 404: Página no encontrada";
    exit();
}

// Redirige
header('Location: ' . $target);
exit();
?>
