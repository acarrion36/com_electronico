<?php


// Configuración
$db_host = '127.0.0.1';
$db_name = 'tienda';
$db_user = 'root';
$db_pass = ''; // en XAMPP normalmente no hay contraseña

// Activar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Crear conexión mysqli
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    die("Error de conexión MySQL ({$db_host}): " . $mysqli->connect_error);
}

// Charset
if (!$mysqli->set_charset('utf8mb4')) {
    die("Error configurando charset: " . $mysqli->error);
}
