<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de almacenamiento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_almacenamiento.php');
    exit;
}

$idAlmacenamiento = intval($_GET['id']);

// Obtener los datos del almacenamiento a editar
$query_almacenamiento = "SELECT * FROM almacenamiento WHERE idAlmacenamiento = ?";
$stmt = $conn->prepare($query_almacenamiento);
$stmt->bind_param('i', $idAlmacenamiento);
$stmt->execute();
$result_almacenamiento = $stmt->get_result();
$almacenamiento = $result_almacenamiento->fetch_assoc();

if (!$almacenamiento) {
    header('Location: modificar_almacenamiento.php');
    exit;
}

// Manejar el envío del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $velocidadLectura = floatval($_POST['velocidadLectura']);
    $velocidadEscritura = floatval($_POST['velocidadEscritura']);
    $capacidad = intval($_POST['capacidad']);
    $precioH = floatval($_POST['precioH']);
    $cantidad = intval($_POST['cantidad']);

    if (
        empty($nombre) || empty($tipo) || $velocidadLectura <= 0 || $velocidadEscritura <= 0 ||
        $capacidad <= 0 || $precioH < 0 || $cantidad < 0
    ) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Actualizar el almacenamiento en la base de datos
        $update_query = "UPDATE almacenamiento 
                         SET Nombre = ?, Tipo = ?, VelocidadLectura = ?, VelocidadEscritura = ?, Capacidad = ?, PrecioH = ?, Cantidad = ? 
                         WHERE idAlmacenamiento = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('ssddiddi', $nombre, $tipo, $velocidadLectura, $velocidadEscritura, $capacidad, $precioH, $cantidad, $idAlmacenamiento);

        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = 'El almacenamiento se actualizó correctamente.';
            header('Location: modificar_almacenamiento.php');
            exit;
        } else {
            $message = 'Error al actualizar el almacenamiento.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Almacenamiento - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Editar Almacenamiento</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Editar Almacenamiento</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para editar almacenamiento -->
        <form method="POST" action="editar_almacenamiento.php?id=<?php echo $idAlmacenamiento; ?>" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($almacenamiento['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo htmlspecialchars($almacenamiento['Tipo']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="velocidadLectura" class="form-label">Velocidad de Lectura (MB/s)</label>
                <input type="number" class="form-control" id="velocidadLectura" name="velocidadLectura" step="0.1" min="0.1" value="<?php echo $almacenamiento['VelocidadLectura']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="velocidadEscritura" class="form-label">Velocidad de Escritura (MB/s)</label>
                <input type="number" class="form-control" id="velocidadEscritura" name="velocidadEscritura" step="0.1" min="0.1" value="<?php echo $almacenamiento['VelocidadEscritura']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad (GB)</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" value="<?php echo $almacenamiento['Capacidad']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0" value="<?php echo $almacenamiento['PrecioH']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad Disponible</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" value="<?php echo $almacenamiento['Cantidad']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
        </form>

        <!-- Botón de volver -->
        <div class="text-center mt-4">
            <a href="modificar_almacenamiento.php" class="btn btn-secondary">Volver</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
