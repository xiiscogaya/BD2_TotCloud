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

// Manejar el envío del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $fabricante = trim($_POST['fabricante']);
    $frecuencia = floatval($_POST['frecuencia']);
    $capacidad = intval($_POST['capacidad']);
    $tipo = trim($_POST['tipo']);
    $precioH = floatval($_POST['precioH']);
    $cantidad = intval($_POST['cantidad']);

    if (empty($nombre) || empty($fabricante) || $frecuencia <= 0 || $capacidad <= 0 || empty($tipo) || $precioH < 0 || $cantidad < 0) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Obtener el próximo ID consecutivo
        $query_max_id = "SELECT COALESCE(MIN(a.idRAM)+1, 0) AS next_id FROM ram a LEFT JOIN ram b ON a.idRAM = b.idRAM-1 WHERE b.idRAM IS NULL";
        $result = $conn->query($query_max_id);
        $next_id = $result->fetch_assoc()['next_id'];

        // Verificar si el ID ya existe (para evitar conflictos en caso de eliminación)
        while (true) {
            $query_check_id = "SELECT idRAM FROM ram WHERE idRAM = ?";
            $stmt_check = $conn->prepare($query_check_id);
            $stmt_check->bind_param('i', $next_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows === 0) {
                break; // Si no existe, este ID es válido
            }
            $next_id++; // Incrementar ID si ya existe
        }

        // Insertar la nueva RAM con el ID generado
        $query = "INSERT INTO ram (idRAM, Nombre, Fabricante, Frecuencia, Capacidad, Tipo, PrecioH, Cantidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issdisdi', $next_id, $nombre, $fabricante, $frecuencia, $capacidad, $tipo, $precioH, $cantidad);

        if ($stmt->execute()) {
            $message = 'RAM añadida correctamente.';
            header('Location: modificar_ram.php');
            exit;
        } else {
            $message = 'Error al añadir la RAM.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir RAM - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Añadir Nueva RAM</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Agregar una Nueva RAM</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir RAM -->
        <form method="POST" action="crear_ram.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="fabricante" class="form-label">Fabricante</label>
                <input type="text" class="form-control" id="fabricante" name="fabricante" required>
            </div>
            <div class="mb-3">
                <label for="frecuencia" class="form-label">Frecuencia (MHz)</label>
                <input type="number" class="form-control" id="frecuencia" name="frecuencia" step="0.1" min="0.1" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad (GB)</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad Disponible</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Añadir RAM</button>
        </form>

        <!-- Botón de volver -->
        <div class="text-center mt-4">
            <a href="modificar_ram.php" class="btn btn-secondary">Volver</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
