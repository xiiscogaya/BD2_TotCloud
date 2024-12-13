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
    $arquitectura = trim($_POST['arquitectura']);
    $version = trim($_POST['version']);
    $tipo = trim($_POST['tipo']);
    $precio_h = floatval($_POST['precio_h']);

    if (empty($nombre) || empty($arquitectura) || empty($version) || empty($tipo) || $precio_h < 0) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Obtener el próximo ID consecutivo para sistemaoperativo
        // La lógica es similar a la usada en CPU:
        // Buscamos el primer hueco disponible o empezamos en 1
        $query_max_id = "
            SELECT COALESCE(MIN(a.idSO)+1, 1) AS next_id
            FROM sistemaoperativo a
            LEFT JOIN sistemaoperativo b ON a.idSO = b.idSO - 1
            WHERE b.idSO IS NULL
        ";
        $result = $conn->query($query_max_id);
        $next_id = $result->fetch_assoc()['next_id'];

        // Insertar el nuevo Sistema Operativo con el ID generado
        $query = "INSERT INTO sistemaoperativo (idSO, Nombre, Arquitectura, Version, Tipo, PrecioH) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issssd', $next_id, $nombre, $arquitectura, $version, $tipo, $precio_h);

        if ($stmt->execute()) {
            $message = 'Sistema Operativo añadido correctamente.';
            header('Location: modificar_so.php');
            exit;
        } else {
            $message = 'Error al añadir el Sistema Operativo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Sistema Operativo - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Añadir Nuevo Sistema Operativo</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Agregar un Nuevo Sistema Operativo</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir SO -->
        <form method="POST" action="crear_so.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="arquitectura" class="form-label">Arquitectura</label>
                <input type="text" class="form-control" id="arquitectura" name="arquitectura" required>
            </div>
            <div class="mb-3">
                <label for="version" class="form-label">Versión</label>
                <input type="text" class="form-control" id="version" name="version" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" required>
            </div>
            <div class="mb-3">
                <label for="precio_h" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precio_h" name="precio_h" step="0.01" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Añadir Sistema Operativo</button>
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
