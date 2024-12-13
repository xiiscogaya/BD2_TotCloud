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

// Verificar si se ha proporcionado un ID de SO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_so.php');
    exit;
}

$idSO = intval($_GET['id']);

// Obtener los datos del SO a editar
$query_so = "SELECT * FROM sistemaoperativo WHERE idSO = ?";
$stmt = $conn->prepare($query_so);
$stmt->bind_param('i', $idSO);
$stmt->execute();
$result_so = $stmt->get_result();
$so = $result_so->fetch_assoc();

if (!$so) {
    header('Location: modificar_so.php');
    exit;
}

// Manejar el envío del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $arquitectura = trim($_POST['arquitectura']);
    $version = trim($_POST['version']);
    $tipo = trim($_POST['tipo']);
    $precio_h = floatval($_POST['precio_h']);

    if (empty($nombre) || empty($arquitectura) || empty($version) || empty($tipo) || $precio_h < 0) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Actualizar el SO en la base de datos
        $update_query = "UPDATE sistemaoperativo SET Nombre = ?, Arquitectura = ?, Version = ?, Tipo = ?, PrecioH = ? WHERE idSO = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('ssssdi', $nombre, $arquitectura, $version, $tipo, $precio_h, $idSO);

        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = 'El Sistema Operativo se actualizó correctamente.';
            header('Location: modificar_so.php');
            exit;
        } else {
            $message = 'Error al actualizar el Sistema Operativo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sistema Operativo - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Editar Sistema Operativo</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Editar Sistema Operativo</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para editar SO -->
        <form method="POST" action="editar_so.php?id=<?php echo $idSO; ?>" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($so['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="arquitectura" class="form-label">Arquitectura</label>
                <input type="text" class="form-control" id="arquitectura" name="arquitectura" value="<?php echo htmlspecialchars($so['Arquitectura']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="version" class="form-label">Versión</label>
                <input type="text" class="form-control" id="version" name="version" value="<?php echo htmlspecialchars($so['Version']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo htmlspecialchars($so['Tipo']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="precio_h" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precio_h" name="precio_h" step="0.01" min="0" value="<?php echo $so['PrecioH']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
        </form>

        <!-- Botón de volver -->
        <div class="text-center mt-4">
            <a href="modificar_so.php" class="btn btn-secondary">Volver</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
