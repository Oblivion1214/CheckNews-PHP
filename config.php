<?php
session_start();
// Configuración de la base de datos
$DB_HOST = $_ENV["DB_HOST"];
$DB_USER = $_ENV["DB_USER"];
$DB_PASSWORD = $_ENV["DB_PASSWORD"];
$DB_NAME = $_ENV["DB_NAME"];
$DB_PORT = $_ENV["DB_PORT"];

// Conexión a la base de datos
$conn = new mysqli("$DB_HOST", "$DB_USER", "$DB_PASSWORD", "$DB_NAME", "$DB_PORT");
// Verificar la conexión
if ($conn->connect_error) {
    die("Error al conectar a MySQL: " . $conn->connect_error);
}


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>