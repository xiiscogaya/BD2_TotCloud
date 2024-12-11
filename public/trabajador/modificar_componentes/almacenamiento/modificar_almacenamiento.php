<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos
include '../../../../includes/check_worker.php'; // Archivo para verificar si el usuario es trabajador

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// Verificar si el usuario es trabajador
if (!esTrabajador($conn, $user_id)) {
    // Si no es trabajador, redirigir a la página de usuario
    header('Location: ../../../usuario/usuario.php');
    exit;
}


// Manejar la eliminación si se recibe un parámetro "eliminar"
if (isset($_GET['eliminar'])) {
    $idAlmacenamiento = intval($_GET['eliminar']);
    $query_check = "SELECT * FROM almacenamiento WHERE idAlmacenamiento = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('i', $idAlmacenamiento);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $query_delete = "DELETE FROM almacenamiento WHERE idAlmacenamiento = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param('i', $idAlmacenamiento);
        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = 'El almacenamiento fue eliminado correctamente.';
        } else {
            $_SESSION['error_message'] = 'Error al intentar eliminar el almacenamiento.';
        }
    } else {
        $_SESSION['error_message'] = 'El almacenamiento especificado no existe.';
    }
    header('Location: modificar_almacenamiento.php');
    exit;
}

// Obtener todos los registros de almacenamiento
$query = "SELECT * FROM almacenamiento";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Almacenamiento - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar Almacenamiento</h1>
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

        <h2 class="text-center mb-4">Lista de Almacenamiento</h2>

        <div class="container my-3">
            <a href="../modificar_componentes.php" class="btn btn-secondary">Volver</a>
        </div>

        <!-- Botón para añadir almacenamiento -->
        <div class="mb-3 text-end">
            <a href="crear_almacenamiento.php" class="btn btn-success">Añadir Nueva CPU</a>
        </div>

        <!-- Tabla de almacenamiento -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Velocidad Lectura (MB/s)</th>
                    <th>Velocidad Escritura (MB/s)</th>
                    <th>Capacidad (GB)</th>
                    <th>Precio/Hora ($)</th>
                    <th>Cantidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($almacenamiento = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($almacenamiento['idAlmacenamiento']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['Nombre']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['Tipo']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['VelocidadLectura']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['VelocidadEscritura']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['Capacidad']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['PrecioH']); ?></td>
                        <td><?php echo htmlspecialchars($almacenamiento['Cantidad']); ?></td>
                        <td>
                            <a href="editar_almacenamiento.php?id=<?php echo $almacenamiento['idAlmacenamiento']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="modificar_almacenamiento.php?eliminar=<?php echo $almacenamiento['idAlmacenamiento']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este almacenamiento?')">Eliminar</a>
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
