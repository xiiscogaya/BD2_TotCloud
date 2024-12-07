<?php
session_start();
include_once '../includes/db_connect.php'; // Conexión a la base de datos

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtén los SaaS disponibles para la organización del usuario
$organizacion_id = $_SESSION['organizacion_id'];
$query_saas = "SELECT s.idSaaS, s.Nombre AS saas_nombre, s.TipoServicio, s.Estado
               FROM saas s
               INNER JOIN paas p ON s.idPaaS = p.idPaaS
               INNER JOIN r_org_grup rog ON p.idPaaS = rog.idGrupo
               WHERE rog.idOrganizacion = ?";
$stmt = $conn->prepare($query_saas);
$stmt->bind_param('i', $organizacion_id);
$stmt->execute();
$result = $stmt->get_result();

// Si no hay SaaS disponibles, redirige con un mensaje de error
if ($result->num_rows === 0) {
    header("Location: dashboard.php?error=no_saas_available");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar SaaS - TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Incluye el header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Seleccionar Servicio SaaS</h2>
        <p class="text-center">Selecciona un servicio SaaS para continuar con la configuración.</p>

        <!-- Muestra los SaaS disponibles -->
        <div class="row">
            <?php while ($saas = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($saas['saas_nombre']); ?></h5>
                            <p class="card-text"><strong>Tipo:</strong> <?php echo htmlspecialchars($saas['TipoServicio']); ?></p>
                            <p class="card-text"><strong>Estado:</strong> <?php echo htmlspecialchars($saas['Estado']); ?></p>
                            <!-- Botón para seleccionar SaaS -->
                            <a href="view_summary.php?idSaaS=<?php echo $saas['idSaaS']; ?>" class="btn btn-primary w-100">Seleccionar</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Botón para regresar -->
        <div class="text-center mt-4">
            <a href="select_paas.php" class="btn btn-secondary">Regresar</a>
        </div>
    </main>

    <!-- Incluye el footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
