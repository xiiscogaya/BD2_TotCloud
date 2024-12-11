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
    $direccion = trim($_POST['direccion']);
    $precioH = floatval($_POST['precioH']);

    if (empty($direccion) || $precioH <= 0) {
        $message = 'Todos los campos son obligatorios y deben tener valores válidos.';
    } else {
        // Generar el próximo ID consecutivo
        $query_max_id = "SELECT COALESCE(MIN(a.idIp)+1, 1) AS next_id FROM direccionip a LEFT JOIN direccionip b ON a.idIp = b.idIp-1 WHERE b.idIp IS NULL";
        $result = $conn->query($query_max_id);
        $next_id = $result->fetch_assoc()['next_id'];

        // Insertar la nueva IP
        $query_insert = "INSERT INTO direccionip (idIp, Direccion, PrecioH, idPaaS) VALUES (?, ?, ?, NULL)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param('isd', $next_id, $direccion, $precioH);

        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = 'IP añadida correctamente.';
            header('Location: modificar_ips.php');
            exit;
        } else {
            $message = 'Error al añadir la IP.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir IP - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Archivo de estilos personalizados -->
    <link href="../../../css/estilos.css" rel="stylesheet">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <div>
            <h1 class="h3 mb-0">Añadir Nueva IP</h1>
        </div>
        <div>
            <a href="../../../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container my-5">
        <h2 class="text-center mb-4">Agregar una Nueva Dirección IP</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para añadir nueva IP -->
        <form method="POST" action="crear_ip.php" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección IP</label>
                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="192.168.1.1" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora ($)</label>
                <input type="number" class="form-control" id="precioH" name="precioH" step="0.01" min="0.01" placeholder="0.50" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Añadir IP</button>
        </form>

        <!-- Botón de volver -->
        <div class="text-center mt-4">
            <a href="modificar_ips.php" class="btn btn-secondary">Volver</a>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include '../../../../includes/footer.php'; ?>
</body>
</html>
