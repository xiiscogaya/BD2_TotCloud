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

// Manejar la eliminación de RAM si se especifica el parámetro 'eliminar'
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $idRAM = intval($_GET['eliminar']);

    // Verificar si la RAM existe
    $query_check = "SELECT * FROM ram WHERE idRAM = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('i', $idRAM);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Proceder a eliminar la RAM
        $query_delete = "DELETE FROM ram WHERE idRAM = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param('i', $idRAM);

        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = 'La RAM fue eliminada exitosamente.';
        } else {
            $_SESSION['error_message'] = 'Hubo un error al intentar eliminar la RAM.';
        }
    } else {
        $_SESSION['error_message'] = 'La RAM especificada no existe.';
    }

    header('Location: modificar_ram.php');
    exit;
}

// Obtener la lista de RAM
$query = "SELECT * FROM ram";
$result = $conn->query($query);

// Mensajes de éxito o error
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar RAM - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Modificar RAM</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Lista de RAM</h2>

        <!-- Mostrar mensajes -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="container my-3">
            <a href="../modificar_componentes.php" class="btn btn-secondary">Volver</a>
        </div>

        <div class="mb-3 text-end">
            <a href="crear_ram.php" class="btn btn-success">Añadir Nueva RAM</a>
        </div>

        <!-- Tabla de RAM -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fabricante</th>
                        <th>Frecuencia (MHz)</th>
                        <th>Capacidad (GB)</th>
                        <th>Tipo</th>
                        <th>Precio por Hora</th>
                        <th>Cantidad Disponible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['idRAM']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['Fabricante']); ?></td>
                            <td><?php echo htmlspecialchars($row['Frecuencia']); ?></td>
                            <td><?php echo htmlspecialchars($row['Capacidad']); ?></td>
                            <td><?php echo htmlspecialchars($row['Tipo']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['PrecioH'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['Cantidad']); ?></td>
                            <td>
                                <a href="editar_ram.php?id=<?php echo $row['idRAM']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="modificar_ram.php?eliminar=<?php echo $row['idRAM']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta RAM?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
