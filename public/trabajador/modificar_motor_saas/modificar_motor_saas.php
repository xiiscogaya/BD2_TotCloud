<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos
include '../../../includes/check_worker.php'; // Archivo para verificar si el usuario es trabajador


// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// Verificar si el usuario es trabajador
if (!esTrabajador($conn, $user_id)) {
    // Si no es trabajador, redirigir a la página de usuario
    header('Location: ../../usuario/usuario.php');
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

// Mostrar mensaje de remove si existe
$success_message_remove = '';
if (isset($_SESSION['success_message_remove'])) {
    $success_message = $_SESSION['success_message_remove'];
    unset($_SESSION['success_message_remove']); // Eliminar mensaje para evitar que se muestre de nuevo
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
        <a href="../trabajador.php" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Mostrar mensaje de cambios realizdos -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar mensaje de eliminacion realizda -->
     <?php if (!empty($success_message_remove)): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($success_message_remove); ?>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
<main class="container my-5">
    <!-- Botón para crear nuevo motor -->
    <div class="text-end mb-3">
        <a href="crear_motor_saas.php" class="btn btn-success">Crear nuevo motor</a>
    </div>

    <!-- Lista de motores -->
    <h2 class="mb-4">Motores existentes</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Versión</th>
                    <th>Precio por Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['idMotor']); ?></td>
                        <td>
                            <!-- Botón para abrir el modal -->
                            <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#detailsModal" 
                                data-id="<?php echo $row['idMotor']; ?>"
                                data-nombre="<?php echo htmlspecialchars($row['Nombre']); ?>"
                                data-version="<?php echo htmlspecialchars($row['Version']); ?>"
                                data-precio="<?php echo htmlspecialchars($row['PrecioH']); ?>">
                                <?php echo htmlspecialchars($row['Nombre']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['Version']); ?></td>
                        <td><?php echo htmlspecialchars($row['PrecioH']); ?></td>
                        <td>
                            <a href="editar_motor_saas.php?id=<?php echo $row['idMotor']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="eliminar_motor_saas.php?id=<?php echo $row['idMotor']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este motor?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No hay motores registrados.</p>
    <?php endif; ?>
</main>

<!-- Modal para mostrar detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Detalles del Motor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6><strong>Nombre:</strong> <span id="modalNombre"></span></h6>
                <h6><strong>Versión:</strong> <span id="modalVersion"></span></h6>
                <h6><strong>Precio por Hora:</strong> $<span id="modalPrecio"></span></h6>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para actualizar el contenido del modal
    document.getElementById('detailsModal').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var nombre = button.getAttribute('data-nombre');
        var version = button.getAttribute('data-version');
        var precio = button.getAttribute('data-precio');

        document.getElementById('modalNombre').textContent = nombre;
        document.getElementById('modalVersion').textContent = version;
        document.getElementById('modalPrecio').textContent = precio;
    });
</script>
</body>
</html>