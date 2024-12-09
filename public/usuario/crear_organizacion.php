<?php
session_start();
include '../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$error = ''; // Inicializar el mensaje de error
$success = ''; // Inicializar el mensaje de éxito

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos del formulario
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $idCreador = $_SESSION['user_id'];

    if (empty($nombre)) {
        $error = 'El nombre de la organización es obligatorio.';
    } else {
        // Generar el próximo ID disponible para la organización
        $query_next_id = "SELECT COALESCE(MAX(idOrganizacion), 0) + 1 AS next_id FROM organizacion";
        $result_next_id = $conn->query($query_next_id);
        $next_id = $result_next_id->fetch_assoc()['next_id'];

        // Insertar la nueva organización
        $insert_query = "INSERT INTO organizacion (idOrganizacion, Nombre, Descripcion, idCreador) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('issi', $next_id, $nombre, $descripcion, $idCreador);

        if ($stmt->execute()) {
            // Vincular al usuario con la organización en la tabla r_usuario_org
            $link_query = "INSERT INTO r_usuario_org (idUsuario, idOrg) VALUES (?, ?)";
            $stmt_link = $conn->prepare($link_query);
            $stmt_link->bind_param('ii', $idCreador, $next_id);

            if ($stmt_link->execute()) {
                $_SESSION['success_message'] = 'Organización creada exitosamente.';
                header('Location: usuario.php');
                exit;
            } else {
                $error = 'Error al vincular la organización con el usuario.';
            }
        } else {
            $error = 'Error al crear la organización.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Organización - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Archivo de estilos personalizados -->
   <link href="../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Crear Organización</h1>
        </div>
        <div>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Nueva Organización</h2>

        <!-- Botón para regresar -->
        <div class="container my-3">
            <a href="usuario.php" class="btn btn-secondary">Volver</a>
        </div>

        <!-- Mostrar mensaje de error -->
        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Mostrar mensaje de éxito -->
        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para crear organización -->
        <form method="POST" action="crear_organizacion.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre de la Organización</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Crear Organización</button>
        </form>

    </main>

    <!-- Pie de página -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
