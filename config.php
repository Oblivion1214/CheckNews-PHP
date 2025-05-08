<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "checknews_db";

$conn = new mysqli("mysql.railway.internal", "root", "MwPvMsPHvPbBPOOBYdhKSVgVMUPndinp", "railway", 3306);
if ($conn->connect_error) {
    die("Error al conectar a MySQL: " . $conn->connect_error);
}


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}