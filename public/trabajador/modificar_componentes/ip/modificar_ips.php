<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Manejar la eliminación si se recibe un parámetro "eliminar"
if (isset($_GET['eliminar'])) {
    $idIp = intval($_GET['eliminar']);
    $query_check = "SELECT * FROM direccionip WHERE idIp = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('i', $idIp);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $query_delete = "DELETE FROM direccionip WHERE idIp = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param('i', $idIp);
        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = 'La IP fue eliminada correctamente.';
        } else {
            $_SESSION['error_message'] = 'Error al intentar eliminar la IP.';
        }
    } else {
        $_SESSION['error_message'] = 'La IP especificada no existe.';
    }
    header('Location: modificar_ips.php');
    exit;
}

// Manejar la edición del precio si se recibe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idIp']) && isset($_POST['precioH'])) {
    $idIp = intval($_POST['idIp']);
    $nuevo_precio = floatval($_POST['precioH']);

    $query_update = "UPDATE direccionip SET PrecioH = ? WHERE idIp = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param('di', $nuevo_precio, $idIp);

    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = 'El precio de la IP se actualizó correctamente.';
    } else {
        $_SESSION['error_message'] = 'Error al intentar actualizar el precio de la IP.';
    }
    header('Location: modificar_ips.php');
    exit;
}

// Obtener todas las direcciones IP
$query = "SELECT * FROM direccionip";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar IPs - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar IPs</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <!-- Mensajes de éxito o error -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4">Lista de Direcciones IP</h2>

        <div class="container my-3">
            <a href="../modificar_componentes.php" class="btn btn-secondary">Volver</a>
        </div>

        <!-- Botón para añadir nueva IP -->
        <div class="mb-3 text-end">
            <a href="crear_ip.php" class="btn btn-success">Añadir Nueva IP</a>
        </div>

        <!-- Tabla de IPs -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dirección</th>
                    <th>Precio/Hora ($)</th>
                    <th>idPaaS</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ip = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ip['idIp']); ?></td>
                        <td><?php echo htmlspecialchars($ip['Direccion']); ?></td>
                        <td>
                            <!-- Formulario para editar precio -->
                            <form method="POST" action="modificar_ips.php" class="d-flex">
                                <input type="hidden" name="idIp" value="<?php echo $ip['idIp']; ?>">
                                <input type="number" step="0.01" class="form-control me-2" name="precioH" value="<?php echo $ip['PrecioH']; ?>" required>
                                <button type="submit" class="btn btn-warning btn-sm">Actualizar</button>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($ip['idPaaS'] ?? 'Sin asignar'); ?></td>
                        <td>
                            <a href="modificar_ips.php?eliminar=<?php echo $ip['idIp']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta IP?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
