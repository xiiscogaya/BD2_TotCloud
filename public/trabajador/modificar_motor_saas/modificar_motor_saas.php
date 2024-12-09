<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Mostrar mensaje de éxito si existe
$success_message_añadir = '';
if (isset($_SESSION['success_message_añadir'])) {
    $success_message = $_SESSION['success_message_añadir'];
    unset($_SESSION['success_message_añadir']); // Eliminar mensaje para evitar que se muestre de nuevo
}

// Mostrar mensaje de exito si existe
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Eliminar mensaje para evitar que se muestre de nuevo
}
// Mostrar mensaje de exito al eliminar motor si existe
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Eliminar mensaje para evitar que se muestre de nuevo
}



// Obtener todas las configuraciones Motor
$query = "SELECT * FROM motor";
$result = $conn->query($query);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Lista Motor - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../css/estilos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .slider-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-lable {
            margin: 0 10px;
        }
    </style>
</head>

<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white text-center py-3">
        <h1>Modificar Lista De Motores</h1>
    </header>

    <!-- Botones de volver a Trabajador -->
    <div class="container my-3">
        <a href="trabajador.php" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Mostrar mensaje de cambios realizdos -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de cambios realizdos -->
     <?php if (!empty($success_message)): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
</body>
</html>