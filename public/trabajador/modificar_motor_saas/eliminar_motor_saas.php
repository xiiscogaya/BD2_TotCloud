<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de motor
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'No se proporcionó un ID válido para el motor.';
    header('Location: modificar_motor_saas.php');
    exit;
}

$idMotor = intval($_GET['id']);

try {
    $conn->begin_transaction();

    // Eliminar el motor de la tabla principal
    $delete_motor = "DELETE FROM motor WHERE idMotor = ?";
    $stmt_motor = $conn->prepare($delete_motor);
    $stmt_motor->bind_param('i', $idMotor);
    $stmt_motor->execute();

    $conn->commit();

    // Guardar mensaje de éxito en la sesión
    $_SESSION['success_message_remove'] = 'El motor se ha eliminado correctamente.';
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = 'Ocurrió un error al intentar eliminar el motor: ' . $e->getMessage();
}

// Redirigir a la página principal de configuración de motores
header('Location: modificar_motor_saas.php');
exit;
