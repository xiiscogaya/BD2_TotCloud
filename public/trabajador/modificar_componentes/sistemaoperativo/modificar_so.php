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

// Obtener datos del usuario
$query = "SELECT * FROM usuario WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Manejar eliminación de SO si se solicita
if (isset($_GET['eliminar'])) {
    $idSO = intval($_GET['eliminar']);
    $delete_query = "DELETE FROM sistemaoperativo WHERE idSO = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param('i', $idSO);
    if ($stmt_delete->execute()) {
        $_SESSION['success_message'] = 'El Sistema Operativo se eliminó correctamente.';
    } else {
        $_SESSION['error_message'] = 'Error al eliminar el Sistema Operativo.';
    }
    header('Location: modificar_so.php');
    exit;
}

// Obtener la lista de Sistemas Operativos
$so_query = "SELECT idSO, Nombre, Arquitectura, Version, Tipo, PrecioH FROM sistemaoperativo";
$so_result = $conn->query($so_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Sistemas Operativos - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar Sistemas Operativos</h1>
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

        <h2 class="text-center mb-4">Lista de Sistemas Operativos</h2>

        <div class="container my-3">
            <a href="../modificar_componentes.php" class="btn btn-secondary">Volver</a>
        </div>

        <!-- Botón para añadir un nuevo Sistema Operativo (se asume que crear_so.php existe) -->
        <div class="mb-3 text-end">
            <a href="crear_so.php" class="btn btn-success">Añadir Nuevo Sistema Operativo</a>
        </div>

        <?php if ($so_result && $so_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Arquitectura</th>
                        <th>Versión</th>
                        <th>Tipo</th>
                        <th>Precio/Hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($so = $so_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($so['idSO']); ?></td>
                            <td><?php echo htmlspecialchars($so['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($so['Arquitectura']); ?></td>
                            <td><?php echo htmlspecialchars($so['Version']); ?></td>
                            <td><?php echo htmlspecialchars($so['Tipo']); ?></td>
                            <td><?php echo htmlspecialchars($so['PrecioH']); ?></td>
                            <td>
                                <a href="editar_so.php?id=<?php echo $so['idSO']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="modificar_so.php?eliminar=<?php echo $so['idSO']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este Sistema Operativo?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No hay Sistemas Operativos registrados.</p>
        <?php endif; ?>

    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
