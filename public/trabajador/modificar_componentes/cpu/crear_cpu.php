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
    $fabricante = trim($_POST['fabricante']);
    $arquitectura = trim($_POST['arquitectura']);
    $nucleos = intval($_POST['nucleos']);
    $frecuencia = floatval($_POST['frecuencia']);
    $precio_h = floatval($_POST['precio_h']);
    $cantidad = intval($_POST['cantidad']);

    if (empty($nombre) || empty($fabricante) || empty($arquitectura) || $nucleos <= 0 || $frecuencia <= 0 || $precio_h < 0 || $cantidad <= 0) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Obtener el próximo ID consecutivo
        $query_max_id = "SELECT COUNT(idCPU) + 1 AS next_id FROM cpu";
        $result = $conn->query($query_max_id);
        $next_id = $result->fetch_assoc()['next_id'];

        // Verificar si el ID ya existe (para evitar conflictos en caso de eliminación)
        while (true) {
            $query_check_id = "SELECT idCPU FROM cpu WHERE idCPU = ?";
            $stmt_check = $conn->prepare($query_check_id);
            $stmt_check->bind_param('i', $next_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows === 0) {
                break; // Si no existe, este ID es válido
            }
            $next_id++; // Incrementar ID si ya existe
        }

        // Insertar la nueva CPU con el ID generado
        $query = "INSERT INTO cpu (idCPU, Nombre, Fabricante, Arquitectura, Nucleos, Frecuencia, PrecioH, Cantidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssidii', $next_id, $nombre, $fabricante, $arquitectura, $nucleos, $frecuencia, $precio_h, $cantidad);

        if ($stmt->execute()) {
            $message = 'CPU añadida correctamente.';
            header('Location: modificar_cpu.php');
            exit;
        } else {
            $message = 'Error al añadir la CPU.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir CPU - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Añadir Nueva CPU</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Agregar una Nueva CPU</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir CPU -->
        <form method="POST" action="crear_cpu.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="fabricante" class="form-label">Fabricante</label>
                <input type="text" class="form-control" id="fabricante" name="fabricante" required>
            </div>
            <div class="mb-3">
                <label for="arquitectura" class="form-label">Arquitectura</label>
                <input type="text" class="form-control" id="arquitectura" name="arquitectura" required>
            </div>
            <div class="mb-3">
                <label for="nucleos" class="form-label">Número de Núcleos</label>
                <input type="number" class="form-control" id="nucleos" name="nucleos" min="1" required>
            </div>
            <div class="mb-3">
                <label for="frecuencia" class="form-label">Frecuencia (GHz)</label>
                <input type="number" class="form-control" id="frecuencia" name="frecuencia" step="0.1" min="0.1" required>
            </div>
            <div class="mb-3">
                <label for="precio_h" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precio_h" name="precio_h" step="0.01" min="0" required>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad Disponible</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Añadir CPU</button>
        </form>

        <!-- Botón de volver -->
        <div class="text-center mt-4">
            <a href="modificar_cpu.php" class="btn btn-secondary">Volver</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
