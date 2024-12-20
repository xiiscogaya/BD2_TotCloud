<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos
include '../../../includes/check_worker.php'; // Función para verificar si el usuario es trabajador

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
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

// Obtener datos del usuario
$query = "SELECT * FROM usuario WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Componentes - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar Componentes</h1>
            <p class="mb-0">Bienvenido, <?php echo htmlspecialchars($user['Nombre']); ?> (Trabajador)</p>
        </div>
        <div>
            <a href="../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Opciones de Componentes -->
    <main class="container my-5">
        <h2 class="text-center">Selecciona el tipo de componente a modificar</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar CPUs</h5>
                        <p class="card-text">Gestiona las unidades centrales de procesamiento (CPU).</p>
                        <a href="cpu/modificar_cpu.php" class="btn btn-primary">Modificar CPUs</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar RAM</h5>
                        <p class="card-text">Gestiona los módulos de memoria RAM disponibles.</p>
                        <a href="ram/modificar_ram.php" class="btn btn-primary">Modificar RAM</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar Almacenamientos</h5>
                        <p class="card-text">Gestiona las unidades de almacenamiento disponibles.</p>
                        <a href="almacenamiento/modificar_almacenamiento.php" class="btn btn-primary">Modificar Almacenamientos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar IPs</h5>
                        <p class="card-text">Gestiona las direcciones IP asignadas o disponibles.</p>
                        <a href="ip/modificar_ips.php" class="btn btn-primary">Modificar IPs</a>
                    </div>
                </div>
            </div>
            <!-- Aquí añades la nueva tarjeta para Sistema Operativo -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Modificar Sistema Operativo</h5>
                        <p class="card-text">Gestiona las opciones de los sistemas operativos disponibles.</p>
                        <a href="sistemaoperativo/modificar_so.php" class="btn btn-primary">Modificar Sistema Operativo</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón de volver -->
        <div class="text-center mt-5">
            <a href="../trabajador.php" class="btn btn-secondary">Volver al Panel</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../includes/footer.php'; ?>
</body>
</html>
