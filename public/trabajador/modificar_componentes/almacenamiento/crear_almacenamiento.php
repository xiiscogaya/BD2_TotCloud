<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
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
        // Obtener el próximo ID consecutivo
        $query_max_id = "SELECT COUNT(idAlmacenamiento) + 1 AS next_id FROM almacenamiento";
        $result = $conn->query($query_max_id);
        $next_id = $result->fetch_assoc()['next_id'];

        // Verificar si el ID ya existe (para evitar conflictos en caso de eliminación)
        while (true) {
            $query_check_id = "SELECT idAlmacenamiento FROM almacenamiento WHERE idAlmacenamiento = ?";
            $stmt_check = $conn->prepare($query_check_id);
            $stmt_check->bind_param('i', $next_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows === 0) {
                break; // Si no existe, este ID es válido
            }
            $next_id++; // Incrementar ID si ya existe
        }

        // Insertar el nuevo almacenamiento con el ID generado
        $query = "INSERT INTO almacenamiento (idAlmacenamiento, Nombre, Tipo, VelocidadLectura, VelocidadEscritura, Capacidad, PrecioH, Cantidad) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'issddidd',
            $next_id,
            $nombre,
            $tipo,
            $velocidadLectura,
            $velocidadEscritura,
            $capacidad,
            $precioH,
            $cantidad
        );

        if ($stmt->execute()) {
            $message = 'Almacenamiento añadido correctamente.';
            header('Location: modificar_almacenamiento.php');
            exit;
        } else {
            $message = 'Error al añadir el almacenamiento.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Almacenamiento - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Añadir Nuevo Almacenamiento</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Agregar Nuevo Almacenamiento</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir almacenamiento -->
        <form method="POST" action="crear_almacenamiento.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" required>
            </div>
            <div class="mb-3">
                <label for="velocidadLectura" class="form-label">Velocidad de Lectura (MB/s)</label>
                <input type="number" class="form-control" id="velocidadLectura" name="velocidadLectura" step="0.1" min="0.1" required>
            </div>
            <div class="mb-3">
                <label for="velocidadEscritura" class="form-label">Velocidad de Escritura (MB/s)</label>
                <input type="number" class="form-control" id="velocidadEscritura" name="velocidadEscritura" step="0.1" min="0.1" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad (GB)</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad Disponible</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Añadir Almacenamiento</button>
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
