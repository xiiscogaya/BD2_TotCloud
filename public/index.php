<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero, .profile {
            text-align: center;
            padding: 50px 20px;
        }
        .hero img {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Incluye el header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <!-- Mostrar mensaje de éxito si existe -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success text-center">
                <?php
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']); // Elimina el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>

        <!-- Mostrar contenido según el estado de la sesión -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Perfil del usuario -->
            <div class="profile">
                <h2 class="mt-3">Perfil del Usuario</h2>
                <p class="lead">Información de tu cuenta</p>
                <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Organización:</strong> <?php echo htmlspecialchars($_SESSION['organizacion_nombre']); ?></p>
                <p><strong>Grupo:</strong> <?php echo htmlspecialchars($_SESSION['grupo_nombre']); ?></p>
            </div>
        <?php else: ?>
            <!-- Hero Section -->
            <div class="hero">
                <img src="https://via.placeholder.com/150" alt="Logo TotCloud" class="img-fluid">
                <h2 class="mt-3">Bienvenido a TotCloud</h2>
                <p class="lead">Tu solución en gestión de servicios PaaS y SaaS.</p>
                <p>Descubre cómo TotCloud puede optimizar tus procesos, brindarte infraestructura como servicio (PaaS) y software como servicio (SaaS), todo en un solo lugar.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Incluye el footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
