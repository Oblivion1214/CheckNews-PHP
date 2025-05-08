<?php
session_start();
// Configuración de la base de datos
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASSWORD = "";
$DB_NAME = "checknews_db";
$DB_PORT = 3306;

// Conexión a la base de datos
$conn = new mysqli("mysql.railway.internal", "root", "MwPvMsPHvPbBPOOBYdhKSVgVMUPndinp", "railway", 3306);
if ($conn->connect_error) {
    die("Error al conectar a MySQL: " . $conn->connect_error);
}


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}