<?php
session_start();
include_once '../includes/db_connect.php'; // Conexión a la base de datos

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica si el usuario pertenece al grupo de administradores
if ($_SESSION['grupo_nombre'] !== 'Administradores') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Incluye el header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Panel de Administrador</h2>
        <p class="text-center">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>. Aquí puedes gestionar usuarios, grupos y servicios.</p>

        <!-- Opciones para Administradores -->
        <div class="row mt-5">
            <!-- Gestión de Usuarios -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Crear, editar o eliminar usuarios en tu organización.</p>
                        <a href="manage_users.php" class="btn btn-primary">Gestionar Usuarios</a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Grupos -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestión de Grupos</h5>
                        <p class="card-text">Gestionar los grupos y sus permisos.</p>
                        <a href="manage_groups.php" class="btn btn-primary">Gestionar Grupos</a>
                    </div>
                </div>
            </div>

            <!-- Gestión de Servicios -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestión de Servicios</h5>
                        <p class="card-text">Crear, editar o eliminar servicios disponibles.</p>
                        <a href="manage_services.php" class="btn btn-primary">Gestionar Servicios</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para volver al inicio -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </main>

    <!-- Incluye el footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
