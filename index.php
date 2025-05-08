<?php
// Configuración para sesiones en Railway
session_save_path('/tmp');
session_start();

// Redirección con validación
$redirect = isset($_SESSION['usuarioID']) ? 
    'templets/Principal.php' : 
    'templets/login.php';

if (!file_exists($redirect)) {
    die("Error: Archivo no encontrado: " . $redirect);
}

header("Location: " . $redirect);
exit();
?>