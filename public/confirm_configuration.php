<?php
session_start();
include_once '../includes/db_connect.php'; // Conexión a la base de datos

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica si se ha recibido el idSaaS
if (!isset($_GET['idSaaS'])) {
    header("Location: select_saas.php");
    exit;
}

// Aquí puedes agregar lógica adicional si necesitas procesar algo (ejemplo: guardar en un log).

// Establece el mensaje de éxito en la sesión
$_SESSION['success_message'] = "¡Configuración confirmada exitosamente!";

// Redirige al inicio
header("Location: index.php");
exit;
?>
