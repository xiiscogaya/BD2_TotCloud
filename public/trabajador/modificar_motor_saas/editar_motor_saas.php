<?php
session_start();
include '../../../includes/db_connect.php'; // Conexión a la base de datos
include '../../../includes/check_worker.php'; // Archivo para verificar si el usuario es trabajador


// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// Verificar si el usuario es trabajador
if (!esTrabajador($conn, $user_id)) {
    // Si no es trabajador, redirigir a la página de usuario
    header('Location: ../../usuario/usuario.php');
    exit;
}

// Verificar ID de Motor
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: modificar_motor_saas.php');
    exit;
}

$idMotor = intval($_GET['id']);

// Obtener la configuración del motor
$query_motor = "SELECT * FROM motor WHERE idMotor = ?";
$stmt = $conn->prepare($query_motor);
$stmt->bind_param('i', $idMotor);
$stmt->execute();
$result_motor = $stmt->get_result();
$motor = $result_motor->fetch_assoc();

if (!$motor) {
    header('Location: modificar_motor_saas.php');
    exit;
}

// Manejo del formulario
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $version = trim($_POST['version']);
    $precioH = floatval($_POST['precioH']);

    if (empty($nombre) || empty($version) || $precioH <= 0) {
        $message = 'Todos los campos son obligatorios.';
    } else {
        try {
            $update_query = "UPDATE motor SET Nombre = ?, Version = ?, PrecioH = ? WHERE idMotor = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param('ssdi', $nombre, $version, $precioH, $idMotor);

            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = 'El motor se ha actualizado correctamente.';
                header('Location: modificar_motor_saas.php');
                exit;
            } else {
                throw new Exception('Error al actualizar el motor.');
            }
        } catch (Exception $e) {
            $message = 'Error al actualizar el motor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Motor - TotCloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Editar Configuración de Motor</h1>
    </header>

    <main class="container my-5">
        <!-- Botón de Volver a Lista de Motores -->
        <div class="container my-3">
            <a href="modificar_motor_saas.php" class="btn btn-secondary">Volver</a>
        </div>
        <h2 class="text-center">Modificar Motor</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mx-auto" style="max-width: 600px;">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($motor['Nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="version" class="form-label">Versión</label>
                <input type="text" class="form-control" id="version" name="version" value="<?php echo htmlspecialchars($motor['Version']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="precioH" class="form-label">Precio por Hora</label>
                <input type="number" step="0.01" class="form-control" id="precioH" name="precioH" value="<?php echo htmlspecialchars($motor['PrecioH']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar Motor</button>
        </form>
    </main>
</body>
</html>
