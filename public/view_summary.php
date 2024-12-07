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

$idSaaS = $_GET['idSaaS'];
$organizacion_id = $_SESSION['organizacion_id'];

// Consulta para obtener los detalles del SaaS seleccionado
$query_saas = "SELECT s.Nombre AS saas_nombre, s.TipoServicio, s.Estado, p.Nombre AS paas_nombre, so.Nombre AS so_nombre
               FROM saas s
               INNER JOIN paas p ON s.idPaaS = p.idPaaS
               INNER JOIN sistemaoperativo so ON p.idSO = so.idSO
               WHERE s.idSaaS = ? AND p.idPaaS IN (
                   SELECT rog.idGrupo
                   FROM r_org_grup rog
                   WHERE rog.idOrganizacion = ?
               )";

$stmt_saas = $conn->prepare($query_saas);
$stmt_saas->bind_param('ii', $idSaaS, $organizacion_id);
$stmt_saas->execute();
$result_saas = $stmt_saas->get_result();

if ($result_saas->num_rows === 0) {
    header("Location: select_saas.php?error=no_saas_found");
    exit;
}

$saas = $result_saas->fetch_assoc();

// Consulta para obtener detalles del PaaS seleccionado (CPU, RAM, almacenamiento)
$query_components = "SELECT c.Nombre AS cpu_nombre, c.Fabricante AS cpu_fabricante, c.Frecuencia AS cpu_frecuencia, c.PrecioH AS cpu_precio,
                            r.Nombre AS ram_nombre, r.Capacidad AS ram_capacidad, r.Frecuencia AS ram_frecuencia, r.PrecioH AS ram_precio,
                            a.Nombre AS almacenamiento_nombre, a.Capacidad AS almacenamiento_capacidad, a.PrecioH AS almacenamiento_precio
                     FROM cpu c
                     INNER JOIN ram r ON c.idPaaS = r.idPaaS
                     INNER JOIN almacenamiento a ON c.idPaaS = a.idPaaS
                     WHERE c.idPaaS = (SELECT idPaaS FROM saas WHERE idSaaS = ?)";

$stmt_components = $conn->prepare($query_components);
$stmt_components->bind_param('i', $idSaaS);
$stmt_components->execute();
$result_components = $stmt_components->get_result();

if ($result_components->num_rows === 0) {
    header("Location: select_paas.php?error=no_components_found");
    exit;
}

$components = $result_components->fetch_assoc();

// Calcular el precio total
$total_precio = $components['cpu_precio'] + $components['ram_precio'] + $components['almacenamiento_precio'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Configuración - TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Incluye el header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Resumen de Configuración</h2>
        <p class="text-center">Revisa los detalles de tu configuración antes de proceder.</p>

        <!-- Información del SaaS -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">SaaS Seleccionado</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($saas['saas_nombre']); ?></p>
                <p><strong>Tipo de Servicio:</strong> <?php echo htmlspecialchars($saas['TipoServicio']); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($saas['Estado']); ?></p>
                <p><strong>Sistema Operativo:</strong> <?php echo htmlspecialchars($saas['so_nombre']); ?></p>
            </div>
        </div>

        <!-- Información de CPU -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">CPU</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($components['cpu_nombre']); ?></p>
                <p><strong>Fabricante:</strong> <?php echo htmlspecialchars($components['cpu_fabricante']); ?></p>
                <p><strong>Frecuencia:</strong> <?php echo htmlspecialchars($components['cpu_frecuencia']); ?> GHz</p>
                <p><strong>Precio por Hora:</strong> $<?php echo htmlspecialchars(number_format($components['cpu_precio'], 2)); ?></p>
            </div>
        </div>

        <!-- Información de RAM -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">RAM</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($components['ram_nombre']); ?></p>
                <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($components['ram_capacidad']); ?> GB</p>
                <p><strong>Frecuencia:</strong> <?php echo htmlspecialchars($components['ram_frecuencia']); ?> MHz</p>
                <p><strong>Precio por Hora:</strong> $<?php echo htmlspecialchars(number_format($components['ram_precio'], 2)); ?></p>
            </div>
        </div>

        <!-- Información de Almacenamiento -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Almacenamiento</h5>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($components['almacenamiento_nombre']); ?></p>
                <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($components['almacenamiento_capacidad']); ?> GB</p>
                <p><strong>Precio por Hora:</strong> $<?php echo htmlspecialchars(number_format($components['almacenamiento_precio'], 2)); ?></p>
            </div>
        </div>

        <!-- Precio Total -->
        <div class="text-center mt-4">
            <h3 class="text-success">Precio Total: $<?php echo htmlspecialchars(number_format($total_precio, 2)); ?> por hora</h3>
        </div>

        <!-- Botones -->
        <div class="text-center mt-4">
            <a href="select_saas.php" class="btn btn-secondary">Regresar</a>
            <a href="confirm_configuration.php?idSaaS=<?php echo $idSaaS; ?>" class="btn btn-primary">Confirmar Configuración</a>
        </div>
    </main>

    <!-- Incluye el footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
