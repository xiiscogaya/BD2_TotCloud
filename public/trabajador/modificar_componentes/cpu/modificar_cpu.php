<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
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
    header('Location: ../../../usuario/usuario.php');
    exit;
}

// Manejar eliminación de CPU si se solicita
if (isset($_GET['eliminar'])) {
    $idCPU = intval($_GET['eliminar']);
    $delete_query = "DELETE FROM cpu WHERE idCPU = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param('i', $idCPU);
    if ($stmt_delete->execute()) {
        $_SESSION['success_message'] = 'La CPU se eliminó correctamente.';
    } else {
        $_SESSION['error_message'] = 'Error al eliminar la CPU.';
    }
    header('Location: modificar_cpu.php');
    exit;
}

// Obtener la lista de CPUs
$cpus_query = "SELECT * FROM cpu";
$cpus_result = $conn->query($cpus_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar CPUs - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar CPUs</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Mensajes -->
    <main class="container my-5">
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="container my-3">
            <a href="../modificar_componentes.php" class="btn btn-secondary">Volver</a>
        </div>

        <h2 class="text-center mb-4">Lista de CPUs</h2>

        <!-- Tabla de CPUs -->
        <div class="mb-3 text-end">
            <a href="crear_cpu.php" class="btn btn-success">Añadir Nueva CPU</a>
        </div>

        <?php if ($cpus_result && $cpus_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Núcleos</th>
                        <th>Frecuencia (GHz)</th>
                        <th>Cantidad Disponible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cpu = $cpus_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cpu['idCPU']); ?></td>
                            <td><?php echo htmlspecialchars($cpu['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cpu['Nucleos']); ?></td>
                            <td><?php echo htmlspecialchars($cpu['Frecuencia']); ?></td>
                            <td><?php echo htmlspecialchars($cpu['Cantidad']); ?></td>
                            <td>
                                <a href="editar_cpu.php?id=<?php echo $cpu['idCPU']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="modificar_cpu.php?eliminar=<?php echo $cpu['idCPU']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta CPU?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No hay CPUs registradas.</p>
        <?php endif; ?>

    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
