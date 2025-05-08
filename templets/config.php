<?php
session_start();
// Configuración de la base de datos
$DB_HOST = $_ENV["DB_HOST"];
$DB_USER = $_ENV["DB_USER"];
$DB_PASSWORD = $_ENV["DB_PASSWORD"];
$DB_NAME = $_ENV["DB_NAME"];
$DB_PORT = $_ENV["DB_PORT"];

// Conexión a la base de datos
$connection = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT);
if (!$connection) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>