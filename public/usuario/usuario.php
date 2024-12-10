<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar si el usuario es un trabajador
$isTrabajador = false;
$check_trabajador_query = "SELECT * FROM trabajador WHERE idUsuario = ?";
$stmt_trabajador = $conn->prepare($check_trabajador_query);
$stmt_trabajador->bind_param('i', $user_id);
$stmt_trabajador->execute();
$result_trabajador = $stmt_trabajador->get_result();
if ($result_trabajador->num_rows > 0) {
    $isTrabajador = true;
}

// Manejar la eliminación si se recibe un parámetro "eliminar"
if (isset($_GET['eliminar'])) {
    $idOrganizacion = intval($_GET['eliminar']);
    
    // Verificar que la organización pertenece al usuario como creador
    $check_query = "SELECT * FROM organizacion WHERE idOrganizacion = ? AND idCreador = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param('ii', $idOrganizacion, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Eliminar la relación entre usuario y organización
        $delete_relation_query = "DELETE FROM r_usuario_org WHERE idOrg = ?";
        $stmt_delete_relation = $conn->prepare($delete_relation_query);
        $stmt_delete_relation->bind_param('i', $idOrganizacion);
        $stmt_delete_relation->execute();

        // Eliminar la organización
        $delete_org_query = "DELETE FROM organizacion WHERE idOrganizacion = ?";
        $stmt_delete_org = $conn->prepare($delete_org_query);
        $stmt_delete_org->bind_param('i', $idOrganizacion);
        $stmt_delete_org->execute();

        $_SESSION['success_message'] = 'La organización se eliminó correctamente.';
    } else {
        $_SESSION['error_message'] = 'No tienes permiso para eliminar esta organización.';
    }

    header('Location: usuario.php');
    exit;
}

// Obtener las organizaciones asociadas al usuario
$query = "
    SELECT o.idOrganizacion, o.Nombre, o.Descripcion, o.idCreador 
    FROM organizacion o 
    JOIN r_usuario_org r ON o.idOrganizacion = r.idOrg 
    WHERE r.idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Organizaciones - TotCloud</title>
    <link href="../css/estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Mis Organizaciones</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        <div>
            <?php if ($isTrabajador): ?>
                <a href="../trabajador/trabajador.php" class="btn btn-warning">Panel de Trabajador</a>
            <?php endif; ?>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
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

        <h2 class="text-center">Lista de Organizaciones</h2>

        <!-- Botón para añadir nueva organización -->
        <div class="text-end mb-4">
            <a href="crear_organizacion.php" class="btn btn-success">Añadir Nueva Organización</a>
        </div>

        <!-- Lista de organizaciones -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($organizacion = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($organizacion['idOrganizacion']); ?></td>
                        <td><?php echo htmlspecialchars($organizacion['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($organizacion['Descripcion']); ?></td>
                        <td>
                            <a href="organizaciones/ver_organizacion.php?id=<?php echo $organizacion['idOrganizacion']; ?>" class="btn btn-primary btn-sm">Ver</a>
                            <?php if ($organizacion['idCreador'] == $user_id): ?>
                                <a href="usuario.php?eliminar=<?php echo $organizacion['idOrganizacion']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta organización?')">Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Pie de página -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
