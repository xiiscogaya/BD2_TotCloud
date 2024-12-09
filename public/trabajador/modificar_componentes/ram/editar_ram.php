<?php
session_start();
include '../../../../includes/db_connect.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../login.php');
    exit;
}

// Verificar si se ha proporcionado un ID de RAM
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_ram.php');
    exit;
}

$idRAM = intval($_GET['id']);

// Obtener los datos de la RAM a editar
$query_ram = "SELECT * FROM ram WHERE idRAM = ?";
$stmt = $conn->prepare($query_ram);
$stmt->bind_param('i', $idRAM);
$stmt->execute();
$result_ram = $stmt->get_result();
$ram = $result_ram->fetch_assoc();

if (!$ram) {
    header('Location: modificar_ram.php');
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
        // Actualizar la RAM en la base de datos
        $update_query = "UPDATE ram SET Nombre = ?, Fabricante = ?, Frecuencia = ?, Capacidad = ?, Tipo = ?, PrecioH = ?, Cantidad = ? WHERE idRAM = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param('ssdisdii', $nombre, $fabricante, $frecuencia, $capacidad, $tipo, $precioH, $cantidad, $idRAM);

        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = 'La RAM se actualizó correctamente.';
            header('Location: modificar_ram.php');
            exit;
        } else {
            $message = 'Error al actualizar la RAM.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar RAM - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Editar RAM</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Editar RAM</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para editar RAM -->
        <form method="POST" action="editar_ram.php?id=<?php echo $idRAM; ?>" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($ram['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="fabricante" class="form-label">Fabricante</label>
                <input type="text" class="form-control" id="fabricante" name="fabricante" value="<?php echo htmlspecialchars($ram['Fabricante']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="frecuencia" class="form-label">Frecuencia (MHz)</label>
                <input type="number" class="form-control" id="frecuencia" name="frecuencia" step="0.1" min="0.1" value="<?php echo $ram['Frecuencia']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad (GB)</label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" value="<?php echo $ram['Capacidad']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo htmlspecialchars($ram['Tipo']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0" value="<?php echo $ram['PrecioH']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad Disponible</label>
                <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" value="<?php echo $ram['Cantidad']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
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
