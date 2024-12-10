<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obtener datos del usuario
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM usuario WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verificar si el usuario es trabajador
$worker_query = "SELECT * FROM trabajador WHERE idUsuario = ?";
$stmt_worker = $conn->prepare($worker_query);
$stmt_worker->bind_param('i', $user_id);
$stmt_worker->execute();
$worker_result = $stmt_worker->get_result();

if ($worker_result->num_rows === 0) {
    // Si no es trabajador, redirigir a la página de usuario
    header('Location: ../usuario/usuario_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Trabajador - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Panel de Trabajador</h1>
            <p class="mb-0">Bienvenido, <?php echo htmlspecialchars($user['Nombre']); ?> (Trabajador)</p>
        </div>
        <div>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Opciones de Trabajador -->
    <main class="container my-5">
        <h2 class="text-center">Opciones disponibles</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar Lista PaaS</h5>
                        <p class="card-text">Gestiona las plataformas como servicio (PaaS) de TotCloud.</p>
                        <a href="modificar_lista_paas/modificar_paas.php" class="btn btn-primary">Modificar PaaS</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar Lista Motores Bases De Datos</h5>
                        <p class="card-text">Gestiona los servicios de bases de datos de TotCloud.</p>
                        <a href="modificar_motor_saas.php" class="btn btn-primary">Modificar SaaS</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar Componentes del PaaS</h5>
                        <p class="card-text">Configura y actualiza los componentes asociados a las plataformas PaaS.</p>
                        <a href="modificar_componentes/modificar_componentes.php" class="btn btn-primary">Modificar Componentes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ver Mis Organizaciones</h5>
                        <p class="card-text">Consulta las organizaciones asociadas a este usuario.</p>
                        <a href="../usuario/usuario.php" class="btn btn-primary">Ver Organizaciones</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Pie de página -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
