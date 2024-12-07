<?php
session_start();
include_once '../includes/db_connect.php'; // Conexión a la base de datos

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtén datos del usuario desde la sesión
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$organizacion_nombre = $_SESSION['organizacion_nombre'];
$grupo_nombre = $_SESSION['grupo_nombre'];
$organizacion_id = $_SESSION['organizacion_id'];

// Consulta las configuraciones PaaS disponibles para la organización del usuario
$query = "SELECT p.idPaaS, p.Nombre, p.Tipo, p.Estado 
          FROM paas p
          INNER JOIN r_org_grup rog ON rog.idGrupo = p.idPaaS
          WHERE rog.idOrganizacion = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $organizacion_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Configuración PaaS - TotCloud</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Incluye el header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center">Seleccionar Configuración PaaS</h2>
        <p class="text-center">
            Bienvenido, <strong><?php echo htmlspecialchars($username); ?></strong>.
        </p>
        <p class="text-center">
            Organización: <strong><?php echo htmlspecialchars($organizacion_nombre); ?></strong>.
        </p>
        <p class="text-center">
            Grupo: <strong><?php echo htmlspecialchars($grupo_nombre); ?></strong>.
        </p>

        <!-- Lista de configuraciones PaaS -->
        <div class="row mt-4">
            <?php while ($paas = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($paas['Nombre']); ?></h5>
                            <p class="card-text">Tipo: <?php echo htmlspecialchars($paas['Tipo']); ?></p>
                            <p class="card-text">Estado: <?php echo htmlspecialchars($paas['Estado']); ?></p>
                            <a href="select_saas.php?idPaaS=<?php echo $paas['idPaaS']; ?>" class="btn btn-primary">Seleccionar</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Incluye el footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
